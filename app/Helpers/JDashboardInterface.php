<?php


namespace App\Helpers;


use App\Models\AttendanceRegistration;
use App\Models\Event;
use App\Models\EventSession;
use Illuminate\Support\Facades\DB;

class JDashboardInterface
{
    /**
     * @param $eventID string accepts conference hall unique event ID
     * @return string[]
     * @author Jones Blackwell
     */
    public static function RetrieveConferenceHallAttendanceBreakDown(string $eventID) {
        $event = Event::where("event_id", $eventID)->first();
        $startDate = $currentDate = $event->start_date;
        $endDate = $event->end_date;
        $day = 1;
        $data = [];

        while (true) {
            $totalAttendees = AttendanceRegistration::selectRaw('COUNT(DISTINCT attendance_registration.reference_code)')
                ->whereColumn('attendance_registration.session_id', 'event_sessions.session_id')
                ->getQuery();

            $daySessions = EventSession::select('event_sessions.*')
                ->selectSub($totalAttendees, 'attendees')
                ->where([["event_id", "{$eventID}"], ["event_sessions.session_date", "{$currentDate}"]])
                ->get()->toArray();

            $breakdownText = "";
            foreach ($daySessions as $key => $daySession) {
                $daySession = (object) $daySession;
                $breakdownText .= "{$daySession->description}: {$daySession->attendees}";

                if(($key !== array_key_last($daySessions) &&  count($daySessions) > 1)) {
                    $breakdownText .= " | ";
                }
            }

            $data["day{$day}Attendance"] = $breakdownText;
            $currentDate = Helper::AddDaysToDate($currentDate, 1);

            if(Helper::DateGreaterThanDate($currentDate, $endDate)) {
                break;
            }
            $day++;
        }

        return $data;
    }
}
