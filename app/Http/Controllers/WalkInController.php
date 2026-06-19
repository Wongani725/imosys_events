<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Hotel;
use App\Models\MealCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class WalkInController extends Controller
{
    public function create()
    {
        $events = Event::where('event_status', 'active')->orderBy('start_date', 'desc')->get();
        $hotels = Hotel::where('available_count', '>', 0)->get();
        return view('admin.walkin.create', compact('events', 'hotels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,event_id',
            'participant' => 'required|string|max:255',
            'email_address' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:Member,Non-Member',
            'accommodation' => 'nullable|boolean',
            'hotel_id' => 'nullable|exists:hotel,id',
            'meals' => 'required|integer|min:0',
            'generate_name_tag' => 'nullable|boolean',
        ]);

        $referenceCode = 'WKI-' . strtoupper(uniqid());

        DB::beginTransaction();
        try {
            $participant = Participant::create([
                'event_id' => $request->event_id,
                'reference_code' => $referenceCode,
                'participant' => $request->participant,
                'email_address' => $request->email_address,
                'phone_number' => $request->phone_number,
                'company_name' => $request->company_name,
                'status' => $request->status ?? 'Non-Member',
                'accommodation' => $request->boolean('accommodation'),
                'hotel_id' => $request->hotel_id,
                'is_walkin' => true,
                'walkin_added_by' => auth()->id(),
                'meals' => $request->meals,
                'event_selection' => $request->event_selection ?? 'main',
            ]);

            MealCoupon::create([
                'event_id' => $request->event_id,
                'participant_reference_code' => $referenceCode,
                'unique_code' => $referenceCode,
                'total_meals' => $request->meals,
                'status' => 'main',
            ]);

            if ($request->hotel_id) {
                Hotel::where('id', $request->hotel_id)->decrement('available_count', 1);
                Hotel::where('id', $request->hotel_id)->increment('booked_count', 1);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to register walk-in: ' . $e->getMessage())->withInput();
        }

        if ($request->boolean('generate_name_tag')) {
            $event = Event::where('event_id', $request->event_id)->first();
            $pdf = Pdf::loadView('pdf.nametags', [
                'participants' => collect([$participant]),
                'backgroundImage' => $event->background_image ?? 'images/placeholder.png',
                'eventName' => $event->event_name,
            ]);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->download('nametag_' . $referenceCode . '.pdf');
        }

        return redirect()->route('admin.walkin.create')
            ->with('success', "Walk-in registered successfully. Reference: {$referenceCode}");
    }
}
