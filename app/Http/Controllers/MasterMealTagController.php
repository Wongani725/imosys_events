<?php

namespace App\Http\Controllers;

use App\Models\MasterMealTag;
use App\Models\Event;
use App\Models\Member;
use App\Helpers\Helper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterMealTagController extends Controller
{
    public function index()
    {
        $tags = MasterMealTag::with(['event', 'member', 'creator'])->latest()->paginate(20);
        $events = Event::where('event_status', 'active')->get();
        return view('admin.master-meal-tags.index', compact('tags', 'events'));
    }

    public function create()
    {
        $events = Event::where('event_status', 'active')->get();
        $members = Member::whereIn('company_name', ['Institute of Internal Auditors', 'iMoSyS'])->orderBy('participant')->get();
        return view('admin.master-meal-tags.create', compact('events', 'members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,event_id',
            'member_id' => 'required|exists:members,id',
            'total_meals' => 'required|integer|min:1',
        ]);

        $event = Event::where('event_id', $request->event_id)->firstOrFail();
        $member = Member::findOrFail($request->member_id);

        $uniqueCode = 'MST-' . $member->member_id . '-' . strtoupper(\Illuminate\Support\Str::random(6));

        MasterMealTag::create([
            'event_id' => $request->event_id,
            'member_id' => $request->member_id,
            'total_meals' => $request->total_meals,
            'unique_code' => $uniqueCode,
            'created_by' => auth()->id(),
        ]);

        DB::table('meal_coupon')->insert([
            'unique_code' => $uniqueCode,
            'participant_reference_code' => $uniqueCode,
            'total_meals' => $request->total_meals,
            'event_id' => $request->event_id,
            'status' => 'master',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.master-meal-tags.index')
            ->with('success', "Master meal tag '{$uniqueCode}' created for {$member->participant}.");
    }

    public function destroy(MasterMealTag $masterMealTag)
    {
        DB::table('meal_coupon')->where('unique_code', $masterMealTag->unique_code)->delete();
        $masterMealTag->delete();
        return back()->with('success', 'Master meal tag deleted.');
    }

    public function getMasterTags(Request $request)
    {
        $request->validate(['event_id' => 'required|string']);

        $eventId = $request->event_id;

        $masterTags = MasterMealTag::with('member')
            ->where('event_id', $eventId)
            ->get();

        $mealCoupons = DB::table('meal_coupon')
            ->where('event_id', $eventId)
            ->whereIn('unique_code', $masterTags->pluck('unique_code'))
            ->get();

        $mealScans = DB::table('meal_scans_per_day')
            ->where('event_id', $eventId)
            ->whereIn('unique_code', $masterTags->pluck('unique_code'))
            ->get();

        $data = [
            'master_tags' => $masterTags,
            'meal_coupons' => $mealCoupons,
            'meal_scans_per_day' => $mealScans,
        ];

        return Helper::APIResponse(1, 'Master meal tags retrieved successfully', HTTP_SUCCESS, $data);
    }

    public function downloadSingleNameTag(MasterMealTag $masterMealTag)
    {
        $tag = $masterMealTag->load('event');
        $event = $tag->event;

        $referenceCode = $tag->unique_code;
        $backgroundImage = $event->background_image
            ? asset($event->background_image)
            : asset('images/default_bg.png');
        $eventName = $event->event_name;
        $eventId = $event->event_id;

        return view('admin.name-tags.master_single', compact(
            'referenceCode', 'backgroundImage', 'eventName', 'eventId'
        ));
    }

    public function downloadNameTags($event_id)
    {
        set_time_limit(0);
        $event = Event::where('event_id', $event_id)->firstOrFail();

        $masterTags = MasterMealTag::where('event_id', $event_id)
            ->get()
            ->map(function ($tag) {
                return (object) [
                    'reference_code' => $tag->unique_code,
                    'participant' => 'Mastertag',
                    'company_name' => 'IIA',
                    'is_master_tag' => true,
                ];
            });

        if ($masterTags->isEmpty()) {
            return back()->with('error', 'No master tags found for this event.');
        }

        $backgroundImage = $event->background_image
            ? asset($event->background_image)
            : asset('images/default_bg.png');

        $eventName = $event->event_name;

        $pdf = Pdf::loadView('pdf.nametags', [
            'participants' => $masterTags,
            'backgroundImage' => $backgroundImage,
            'eventName' => $eventName,
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('master_nametags_' . $event_id . '.pdf');
    }
}
