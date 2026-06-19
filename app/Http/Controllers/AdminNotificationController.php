<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Member;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('creator')->latest()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $events = Event::where('event_status', 'active')->get();
        $memberCount = Member::count();
        $nonMemberCount = Member::where('status', '!=', 'Member')->count();
        return view('admin.notifications.create', compact('events', 'memberCount', 'nonMemberCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience_type' => 'required|in:all,members,non_members,pending_payment,confirmed,governance,main',
        ]);

        DB::beginTransaction();
        try {
            $notification = Notification::create([
                'title' => $request->title,
                'message' => $request->message,
                'audience_type' => $request->audience_type,
                'created_by' => auth()->id(),
            ]);

            $query = Member::query();

            switch ($request->audience_type) {
                case 'members':
                    $query->where('status', 'Member');
                    break;
                case 'non_members':
                    $query->where('status', '!=', 'Member');
                    break;
                case 'pending_payment':
                    $query->whereHas('bookers', function ($q) {
                        $q->where('booking_status', 'Pending Payment');
                    });
                    break;
                case 'confirmed':
                    $query->whereHas('bookers', function ($q) {
                        $q->where('booking_status', 'Confirmed');
                    });
                    break;
                case 'governance':
                    $query->whereHas('bookers', function ($q) {
                        $q->where('event_selection', 'governance');
                    });
                    break;
                case 'main':
                    $query->whereHas('bookers', function ($q) {
                        $q->where('event_selection', 'main');
                    });
                    break;
                case 'all':
                default:
                    break;
            }

            $members = $query->get();
            $recipients = [];

            foreach ($members as $member) {
                $recipients[] = [
                    'notification_id' => $notification->id,
                    'member_id' => $member->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($recipients)) {
                NotificationRecipient::insert($recipients);
            }

            DB::commit();

            $count = count($recipients);
            return redirect()->route('admin.notifications.index')
                ->with('success', "Notification sent to {$count} recipients.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to send notification: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Notification $notification)
    {
        $notification->load('creator', 'recipients.member');
        $readCount = $notification->recipients()->whereNotNull('read_at')->count();
        return view('admin.notifications.show', compact('notification', 'readCount'));
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }
}
