<?php
namespace App\Http\Controllers;

use App\Models\Bookers;
use App\Models\EventSession;
use Exception;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Models\MealCoupon;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Response;

class ReportController extends Controller
{
    /**
     * Shows the meals redeemed report for a given event.
     *
     * The event name is hardcoded for now, but in the future, this should be passed in as a parameter.
     *
     * @return \Illuminate\Http\Response
     */

    public function show($request, $eventName)
    {

        $event = Event::where('event_name', $eventName)->first();

        if (!$event) {
            // Handle the case when event is not found
        }

        $mealScans = DB::table('meal_scans_per_day')
            ->where('event_id', $event->event_id)
            ->orderBy('date')
            ->get();

        $expectedMeals = DB::table('meal_coupon')
            ->join('events', 'meal_coupon.event_id', '=', 'events.event_id')
            ->where('meal_coupon.event_id', $event->event_id)
            ->select('events.start_date', 'events.end_date', 'meal_coupon.participant_reference_code')
            ->get();

        return view('event_dashboard.meals_redeemed', [
            'event' => $event,
            'mealScans' => $mealScans,
            'expectedMeals' => $expectedMeals,
        ]);
    }



    public function eventReport(Request $request, $eventName)
    {
        // $eventName = "2025 ICAM LAKESHORE CONFERENCE";
        $event = Event::where('event_name', $eventName)->first();

        if (!$event) {
            // Handle the case when event is not found
        }

        $mealScans = DB::table('meal_scans_per_day')
            ->where('event_id', $event->event_id)
            ->orderBy('date')
            ->get();

        $expectedMeals = DB::table('meal_coupon')
            ->join('events', 'meal_coupon.event_id', '=', 'events.event_id')
            ->where('meal_coupon.event_id', $event->event_id)
            ->select('events.start_date', 'events.end_date', 'meal_coupon.participant_reference_code')
            ->get();

        return view('Reports.event-report', [
            'event' => $event,
            'mealScans' => $mealScans,
            'expectedMeals' => $expectedMeals,
        ]);
    }



    /**
     * Commentor PRINCE
     *
     * Ensures there's an event before proceeding – Redirects with an error if no event exists.
     * Handles cases where no participants are registered – Avoids unnecessary queries.
     * Optimized querying – pluck() is used where only IDs are needed.
     * Improved gender count calculation – Uses groupBy()->map->count() for cleaner grouping.
     * @return void
     */
    public function onsiteRegistrationReport()
    {
        // Get the latest event
        $event = Event::latest('start_date')->first();

        if (!$event) {
            return redirect()->back()->with('error', 'No event found.');
        }

        $registrations = DB::table('i_participant_event_registrations')
            ->where('event_id', $event->event_id)
            ->select('reference_code')
            ->get();

        $participantIds = $registrations->pluck('reference_code')->toArray();

        $registrationsWithParticipants = DB::table('i_participant_event_registrations')
            ->where('i_participant_event_registrations.event_id', $event->event_id)
            ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->select('i_participant_event_registrations.reference_code', 'event_participants.participant', 'event_participants.status', 'event_participants.company_name')
            ->get();

        $memberCount = $registrationsWithParticipants->where('status', 'Member')->count();
        $nonMemberCount = $registrationsWithParticipants->where('status', 'Non Member')->count();

        $genderCounts = $registrationsWithParticipants->groupBy('gender')->map(function ($group) {
            return count($group);
        });

        return view('event_dashboard.onsite_registration_report', compact('event', 'registrationsWithParticipants', 'memberCount', 'nonMemberCount', 'genderCounts'));
    }


    public function eventBookingReport()
    {
        // Get the latest event
        $event = Event::latest('start_date')->first();

        if (!$event) {
            return redirect()->back()->with('error', 'No event found.');
        }


        $bookers = Bookers::with(['hotel', 'attireSize'])
            ->where('event_id', $event->event_id)
            ->where('booking_status', 'Approved')
            ->get();

        $memberCount = $bookers->where('status', 'Member')->count();
        $nonMemberCount = $bookers->where('status', 'Non Member')->count();



        return view('event_dashboard.booking', compact('event', 'bookers', 'memberCount', 'nonMemberCount'));
    }




    /* * This function is used to fetch the attendance registration records for a given event or for all events.
     *   If the event parameter is provided, it will fetch the records for the given event.
     *   If the event parameter is not provided, it will fetch the records for all events.
     *   The records are grouped by date and the count of registrations for each date is provided in the $registrationDates array.
     *   The function returns a view with the data.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     */
    public function conferenceHallRegistration1()
    {
        $event = Event::latest('start_date')->first();


        if (!$event) {
            // Handle the case when the event is not found
        }

        $events = DB::table('events')
            ->where('event_id', $event->event_id)
            ->select('event_id', 'start_date', 'end_date')
            ->get();

        $participantData = [];

        foreach ($events as $event) {
            $startDate = $event->start_date;
            $endDate = $event->end_date;

            $registrations = DB::table('attendance_registration')
                ->join('event_participants', 'attendance_registration.reference_code', '=', 'event_participants.reference_code')
                ->select('attendance_registration.reference_code', 'event_participants.participant', 'event_participants.gender', 'event_participants.status', 'event_participants.company_name', 'attendance_registration.created_at', 'attendance_registration.session_id')
                ->whereDate('attendance_registration.created_at', '>=', $startDate)
                ->whereDate('attendance_registration.created_at', '<=', $endDate)
                ->where('event_participants.company_name', 'not like', '%media%')
                ->get();

            // Fetch the session descriptions, start_time, and end_time and add them to the registrations
            foreach ($registrations as $registration) {
                $sessionId = $registration->session_id;
                $session = DB::table('event_sessions')->where('session_id', $sessionId)->first();

                // If a matching session is found, add the 'description', 'start_time', and 'end_time' attributes to the $registration object
                if ($session) {
                    $registration->description = $session->description;
                    $registration->start_time = $session->start_time;
                    $registration->end_time = $session->end_time;
                    $registration->session_date = $session->session_date;
                } else {
                    $registration->description = 'N/A';
                    $registration->start_time = 'N/A';
                    $registration->end_time = 'N/A';
                    $registration->session_date = 'N/A';
                }
            }
            $registrationDates = $registrations->groupBy(function ($registration) {
                $registration = json_decode(json_encode($registration), true);
                return Carbon::parse($registration['created_at'])->format('Y-m-d');
            });

            $participantData[] = [
                'event' => $event,
                'registrationDates' => $registrationDates,
            ];
        }
        return view('Reports.conference_hall_registration', compact('participantData'));
    }

    public function conferenceHallRegistration(Request $request)
    {

        if(!empty($request->event)) {
            $event = Event::where('event_name', $request->event)->first();
        }
        else {
            $event = Event::orderBy('end_date', 'DESC')->first();
        }


        if (!$event) {
            return redirect()->route('dashboard')->withErrors(['exception'=> "Event not found"]);
        }

        $allEvents = Event::all();

        foreach ($allEvents as &$event) {
            $eSession = EventSession::where("event_id", $event->event_id)->get();
            $event->sessions = []; // Create an empty array attribute for sessions
            if ($eSession->isNotEmpty()) {
                $event->sessions = $eSession; // Store the sessions data in the temporary array
            }
        }


        $events = DB::table('events')
            ->where('event_id', $event->event_id)
            ->select('event_id', 'event_name','start_date', 'end_date')
            ->get();

        $participantData = [];

        $eventSession = "";
        foreach ($events as $event) {
            $startDate = $event->start_date;
            $endDate = $event->end_date;

            $registrations = DB::table('attendance_registration')
                ->join('event_participants', 'attendance_registration.reference_code', '=', 'event_participants.reference_code')
                ->select('attendance_registration.reference_code', 'event_participants.participant', 'event_participants.gender', 'event_participants.status', 'event_participants.company_name', 'attendance_registration.created_at', 'attendance_registration.session_id');

            if(!empty($request->eventSession)) {
                $session = EventSession::where('session_id', trim($request->eventSession))->first();
                if(!empty($session)) {
                    $eventSession = "{$session->description} | {$session->session_date} {$session->start_time} - {$session->end_time}";
                    $registrations = $registrations->where("attendance_registration.session_id", $session->session_id);
                }
            }
            else{
                $registrations = $registrations->whereDate('attendance_registration.created_at', '>=', $startDate)
                    ->whereDate('attendance_registration.created_at', '<=', $endDate);
            }

            $registrations = $registrations->orderBy("event_participants.participant",'ASC')->get();

            // Fetch the session descriptions, start_time, and end_time and add them to the registrations
            foreach ($registrations as $registration) {
                $sessionId = $registration->session_id;
                $session = DB::table('event_sessions')->where('session_id', $sessionId)->first();

                // If a matching session is found, add the 'description', 'start_time', and 'end_time' attributes to the $registration object
                if ($session) {
                    $registration->description = $session->description;
                    $registration->start_time = $session->start_time;
                    $registration->end_time = $session->end_time;
                    $registration->session_date = $session->session_date;
                } else {
                    $registration->description = 'N/A';
                    $registration->start_time = 'N/A';
                    $registration->end_time = 'N/A';
                    $registration->session_date = 'N/A';
                }
            }
            $registrationDates = $registrations->groupBy(function ($registration) {
                $registration = json_decode(json_encode($registration), true);
                return Carbon::parse($registration['created_at'])->format('Y-m-d');
            });

            $totalAttended = count($registrations);
            $participantData[] = [
                'event' => $event,
                'registrationDates' => $registrationDates,
            ];
        }
        return view('Reports.conference_hall_registration', compact('participantData', 'allEvents', 'totalAttended', 'eventSession'));
    }




    /**
     * Commenter PRINCE
     * Displays the onsite registration report for a given event.
     *
     * This report will show the total number of registrations, the number of members and non-members, and the number of male and female participants.
     * The report will also display a table with the following columns:
     * - Participant ID
     * - Participant Name
     * - Status (Member or Non Member)
     * - Gender (Male or Female)
     *
     * @param Request $request
     * @param string $eventName The name of the event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function onsiteRegistrationReport2(Request $request, $eventName)
    {
        $event = Event::where('event_name', $eventName)->first();
        // dd($event);

        if (!$event) {
            // Handle the case when the event is not found
        }

        $registrations = DB::table('i_participant_event_registrations')
            ->where('event_id', $event->event_id)
            ->select('reference_code')
            ->get();

        $participantIds = $registrations->pluck('reference_code')->toArray();

        $participants = DB::table('event_participants')
            ->whereIn('reference_code', $participantIds)
            ->select('reference_code', 'participant', 'status', 'gender')
            ->get();

        $registrationsWithParticipants = $registrations->map(function ($registration) use ($participants) {
            $participant = $participants->firstWhere('reference_code', $registration->reference_code);
            $registration->participant = $participant ? $participant->participant : 'N/A';
            $registration->status = $participant ? $participant->status : 'N/A';
            $registration->gender = $participant ? $participant->gender : 'N/A';
            return $registration;
        });

        $memberCount = $registrationsWithParticipants->where('status', 'Member')->count();
        $nonMemberCount = $registrationsWithParticipants->where('status', 'Non Member')->count();

        $genderCounts = $registrationsWithParticipants->groupBy('gender')->map(function ($group) {
            return count($group);
        });

        return view('Reports.onsite-registration2', compact('event', 'registrationsWithParticipants', 'memberCount', 'nonMemberCount', 'genderCounts'));
    }

    public function eventReport2(Request $request, $eventName)
    {
        $event = Event::where('event_name', $eventName)->first();

        if (!$event) {
            // Handle the case when event is not found
        }

        $mealScans = DB::table('meal_scans_per_day')
            ->where('event_id', $event->event_id)
            ->orderBy('date')
            ->get();

        return view('Reports.event-report2', [
            'event' => $event,
            'mealScans' => $mealScans,
        ]);
    }





    public function eventReport3(Request $request, $eventName)
    {
        $event = Event::where('event_name', $eventName)->first();

        if (!$event) {
            // Handle the case when event is not found
        }

        $mealScans = DB::table('meal_coupon')
            ->join('event_participants', 'event_participants.reference_code', '=', 'meal_coupon.participant_reference_code')
            ->where('meal_coupon.event_id', $event->event_id)
            // ->orderBy('date')
            ->get(['meal_coupon.*', 'event_participants.participant']);

        return view('Reports.event-report3', [
            'event' => $event,
            'mealScans' => $mealScans,
        ]);
    }


//    public function eventReport3(Request $request, $eventName)
//    {
//        $event = Event::where('event_name', $eventName)->first();
//
//        if (!$event) {
//            // Handle the case when event is not found
//        }
//
//        $mealScans = DB::table('meal_coupon')
//            ->where('event_id', $event->event_id)
//           // ->orderBy('date')
//            ->get();
//
//        return view('Reports.event-report3', [
//            'event' => $event,
//            'mealScans' => $mealScans,
//        ]);
//    }






    /**
     * Generate Report for Event
     *
     * @param string $eventName Name of the event
     * @return \Illuminate\Http\Response
     */
    public function eventReport5($eventName)
    {

        $event = Event::where('event_name', $eventName)->first();
        $eventId = $event->event_id;

        $participantsAttended = DB::table('i_participant_event_registrations')
            ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('i_participant_event_registrations.event_id', $eventId)
            ->select(
                DB::raw('COUNT(i_participant_event_registrations.reference_code) as total_participants_attended'),
                DB::raw('(SELECT COUNT(*) FROM i_participant_event_registrations INNER JOIN event_participants ON i_participant_event_registrations.reference_code = event_participants.reference_code WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '") as total_members_attended'),
                DB::raw('(SELECT COUNT(*) FROM i_participant_event_registrations INNER JOIN event_participants ON i_participant_event_registrations.reference_code = event_participants.reference_code WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '") as total_non_members_attended')
            )
            ->get();

        $hotelMealsRedeemed = DB::table('meal_scans_per_day')
            ->select('hotel_name',
                DB::raw('SUM(CASE WHEN meal_scans_per_day.participant_reference_code = meal_scans_per_day.unique_code THEN 1 ELSE 0 END) AS premium_scans'),
                DB::raw('SUM(CASE WHEN meal_scans_per_day.participant_reference_code != meal_scans_per_day.unique_code THEN 1 ELSE 0 END) AS extras_scans'),
                DB::raw('SUM(CASE WHEN meal_scans_per_day.participant_reference_code = meal_scans_per_day.unique_code THEN 1 ELSE 0 END) + SUM(CASE WHEN meal_scans_per_day.participant_reference_code != meal_scans_per_day.unique_code THEN 1 ELSE 0 END) AS total_meals_redeemed')
            )
            ->groupBy('hotel_name')
            ->get();


//        $hotelMealsRedeemed = DB::table('meal_scans_per_day')
//            ->select('hotel_name', DB::raw('COUNT(*) as hotel_meals_redeemed'))
//            ->groupBy('hotel_name')
//            ->get();

//        $totalParticipantsAttended = $participantsAttended[0]->total_participants_attended;
//        $totalMembersAttended = $participantsAttended[0]->total_members_attended;
//        $totalNonMembersAttended = $participantsAttended[0]->total_non_members_attended;

// Use the retrieved values as needed


        $initialRegistrations = DB::table('event_participants')
            // ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('event_participants.event_id', $eventId)
            ->select(
            // DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.balance > 0 AND event_participants.event_id = "' . $eventId . '") as total_registrations_with_balances'),
                DB::raw('COUNT(event_participants.reference_code) as total_registrations'),
                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '") as total_members'),
                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '") as total_non_members'),
            // DB::raw('DATE(i_participant_event_registrations.registration_date_time) as registration_date')
            )
            // ->groupBy('registration_date')
            ->get();

        $walkinParticipants = DB::table('event_participants')
            // ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('event_participants.event_id', $eventId)
            ->select(
            // DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.balance > 0 AND event_participants.event_id = "' . $eventId . '") as total_registrations_with_balances'),
//                DB::raw('COUNT(event_participants.reference_code) as total_walkins'),
                DB::raw('COUNT(CASE WHEN event_participants.type = "walkin" THEN event_participants.reference_code END) as total_walkins'),
//                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '") as total_members'),
//                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '") as total_non_members'),
//            // DB::raw('DATE(i_participant_event_registrations.registration_date_time) as registration_date')

                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '" AND event_participants.type = "walkin") as total_walkin_members'),
                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '" AND event_participants.type = "walkin") as total_walkin_non_members'),

            )
            // ->groupBy('registration_date')
            ->get();


        $mealCoupon = DB::table('meal_coupon')
            ->join('event_participants', 'meal_coupon.participant_reference_code', '=', 'event_participants.reference_code')
            ->where('meal_coupon.event_id', $eventId)
            // ->where('i_participant_event_registrations.conference_pack_redeemed', 1)
            ->select(
            // DB::raw('(SELECT SUM(*) FROM meal_coupon WHERE meal_coupon.total_meals AND meal_coupon.event_id = "' . $eventId . '") as total_meals'),
                DB::raw('(SELECT SUM(total_meals) FROM meal_coupon WHERE meal_coupon.event_id = "' . $eventId . '") as total_meals'),
                DB::raw('(SELECT SUM(redeemed) FROM meal_coupon WHERE meal_coupon.event_id = "' . $eventId . '") as total_redeemed'),

                DB::raw('(SELECT SUM(CASE WHEN subquery.meal_coupon_count = 1 THEN meal_coupon.total_meals ELSE meal_coupon.total_meals END) FROM (SELECT meal_coupon.participant_reference_code, COUNT(*) AS meal_coupon_count FROM meal_coupon INNER JOIN event_participants ON meal_coupon.participant_reference_code = event_participants.reference_code WHERE event_participants.event_id = "' . $eventId . '" GROUP BY meal_coupon.participant_reference_code) AS subquery) as total_members_meals'),

                DB::raw('(SELECT (SELECT SUM(total_meals) FROM meal_coupon WHERE meal_coupon.event_id = "' . $eventId . '") - (SELECT SUM(CASE WHEN subquery.meal_coupon_count = 1 THEN meal_coupon.total_meals ELSE meal_coupon.total_meals END) FROM (SELECT meal_coupon.participant_reference_code, COUNT(*) AS meal_coupon_count, SUM(meal_coupon.total_meals) AS total_meals FROM meal_coupon INNER JOIN event_participants ON meal_coupon.participant_reference_code = event_participants.reference_code WHERE event_participants.event_id = "' . $eventId . '" GROUP BY meal_coupon.participant_reference_code) AS subquery)) as total_non_members_meals'),
                //DB::raw('COUNT(meal_coupon.total_meals) as total_redeemed'),
                DB::raw('COUNT(meal_coupon.redeemed) as redeem_date')
            )
            //->groupBy('redeem_date')
            ->get();
//table

        $event = Event::where('event_name', $eventName)->first();
        $eventId = $event->event_id;

// Retrieve data for Members Session
        $membersSessions = DB::table('event_sessions')
            ->where('event_id', $eventId)
            ->where('description', 'Member')
            ->get();

        foreach ($membersSessions as $session) {
            $session->total_registrations = DB::table('attendance_registration')
                ->where('session_id', $session->session_id)
                ->count();
        }
        $eventSessions = DB::table('event_sessions')
            ->where('event_id', $eventId)
            ->select('description as event_description', 'session_date as event_session_date')
//            ->select('description as event_description')
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM attendance_registration WHERE attendance_registration.session_id = event_sessions.session_id) as session_total_attendees'))
            ->get();

//        $eventSessions = DB::table('event_sessions')
//            ->where('event_id', $eventId)
//            ->pluck('description as event_description');

//        foreach ($eventSessions as $eventSession) {
//            $description = $eventSession->description;
//            // Use the $description data as needed
//        }
//        foreach ($eventSessions as $description) {
//            $totalAttendees = DB::table('attendance_registration')
//                ->where('session_id', $description)
//                ->count();
//
//        }

// Retrieve data for Non Members Session
        $nonMembersSessions = DB::table('event_sessions')
            ->where('event_id', $eventId)
            ->where('description', 'Non Member')
            ->get();

        foreach ($nonMembersSessions as $session) {
            $session->total_registrations = DB::table('attendance_registration')
                ->where('session_id', $session->session_id)
                ->count();
        }

// Retrieve data for Session for All
        $allSessions = DB::table('event_sessions')
            ->where('event_id', $eventId)
            ->where('description', 'All')
            ->get();

        foreach ($allSessions as $session) {
            $session->total_registrations = DB::table('attendance_registration')
                ->where('session_id', $session->session_id)
                ->count();
        }

        $conferencePackRedeemed = DB::table('i_participant_event_registrations')
            ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('i_participant_event_registrations.event_id', $eventId)
            ->where('i_participant_event_registrations.conference_pack_redeemed', 1)
            ->select(
                DB::raw('COUNT(i_participant_event_registrations.reference_code) as total_redeemed'),
                DB::raw('DATE(i_participant_event_registrations.conference_pack_redeem_date_time) as redeem_date')
            )
            ->groupBy('redeem_date')
            ->get();

        return view('Reports.eventt-report4', compact('event','walkinParticipants','eventSessions','hotelMealsRedeemed','participantsAttended','initialRegistrations','mealCoupon','membersSessions', 'nonMembersSessions', 'allSessions', 'conferencePackRedeemed'));
    }





    /**
     * Generate a report for an event, including data about the number of participants, meals, and sessions.
     *
     * @param Request $request The request object
     * @param string $eventName The name of the event
     *
     * @return Illuminate\View\View The view for the report
     */

    public function eventReport4(Request $request, $eventName)
    {
        $event = Event::where('event_name', $eventName)->first();
        $eventId = $event->event_id;

        $participantsAttended = DB::table('i_participant_event_registrations')
            ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('i_participant_event_registrations.event_id', $eventId)
            ->select(
                DB::raw('COUNT(i_participant_event_registrations.reference_code) as total_participants_attended'),
                DB::raw('(SELECT COUNT(*) FROM i_participant_event_registrations INNER JOIN event_participants ON i_participant_event_registrations.reference_code = event_participants.reference_code WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '") as total_members_attended'),
                DB::raw('(SELECT COUNT(*) FROM i_participant_event_registrations INNER JOIN event_participants ON i_participant_event_registrations.reference_code = event_participants.reference_code WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '") as total_non_members_attended')
            )
            ->get();

        $hotelMealsRedeemed = DB::table('meal_scans_per_day')
            ->select('hotel_name',
                DB::raw('SUM(CASE WHEN meal_scans_per_day.participant_reference_code = meal_scans_per_day.unique_code THEN 1 ELSE 0 END) AS premium_scans'),
                DB::raw('SUM(CASE WHEN meal_scans_per_day.participant_reference_code != meal_scans_per_day.unique_code THEN 1 ELSE 0 END) AS extras_scans'),
                DB::raw('SUM(CASE WHEN meal_scans_per_day.participant_reference_code = meal_scans_per_day.unique_code THEN 1 ELSE 0 END) + SUM(CASE WHEN meal_scans_per_day.participant_reference_code != meal_scans_per_day.unique_code THEN 1 ELSE 0 END) AS total_meals_redeemed')
            )
            ->groupBy('hotel_name')
            ->get();


//        $hotelMealsRedeemed = DB::table('meal_scans_per_day')
//            ->select('hotel_name', DB::raw('COUNT(*) as hotel_meals_redeemed'))
//            ->groupBy('hotel_name')
//            ->get();

//        $totalParticipantsAttended = $participantsAttended[0]->total_participants_attended;
//        $totalMembersAttended = $participantsAttended[0]->total_members_attended;
//        $totalNonMembersAttended = $participantsAttended[0]->total_non_members_attended;

// Use the retrieved values as needed


        $initialRegistrations = DB::table('event_participants')
            // ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('event_participants.event_id', $eventId)
            ->select(
            // DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.balance > 0 AND event_participants.event_id = "' . $eventId . '") as total_registrations_with_balances'),
                DB::raw('COUNT(event_participants.reference_code) as total_registrations'),
                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '") as total_members'),
                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '") as total_non_members'),
            // DB::raw('DATE(i_participant_event_registrations.registration_date_time) as registration_date')
            )
            // ->groupBy('registration_date')
            ->get();

        $walkinParticipants = DB::table('event_participants')
            // ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('event_participants.event_id', $eventId)
            ->select(
            // DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.balance > 0 AND event_participants.event_id = "' . $eventId . '") as total_registrations_with_balances'),
//                DB::raw('COUNT(event_participants.reference_code) as total_walkins'),
                DB::raw('COUNT(CASE WHEN event_participants.type = "walkin" THEN event_participants.reference_code END) as total_walkins'),
//                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '") as total_members'),
//                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '") as total_non_members'),
//            // DB::raw('DATE(i_participant_event_registrations.registration_date_time) as registration_date')

                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '" AND event_participants.type = "walkin") as total_walkin_members'),
                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '" AND event_participants.type = "walkin") as total_walkin_non_members'),

            )
            // ->groupBy('registration_date')
            ->get();


        $mealCoupon = DB::table('meal_coupon')
            ->join('event_participants', 'meal_coupon.participant_reference_code', '=', 'event_participants.reference_code')
            ->where('meal_coupon.event_id', $eventId)
            // ->where('i_participant_event_registrations.conference_pack_redeemed', 1)
            ->select(
            // DB::raw('(SELECT SUM(*) FROM meal_coupon WHERE meal_coupon.total_meals AND meal_coupon.event_id = "' . $eventId . '") as total_meals'),
                DB::raw('(SELECT SUM(total_meals) FROM meal_coupon WHERE meal_coupon.event_id = "' . $eventId . '") as total_meals'),
                DB::raw('(SELECT SUM(redeemed) FROM meal_coupon WHERE meal_coupon.event_id = "' . $eventId . '") as total_redeemed'),

                DB::raw('(SELECT SUM(CASE WHEN subquery.meal_coupon_count = 1 THEN meal_coupon.total_meals ELSE meal_coupon.total_meals END) FROM (SELECT meal_coupon.participant_reference_code, COUNT(*) AS meal_coupon_count FROM meal_coupon INNER JOIN event_participants ON meal_coupon.participant_reference_code = event_participants.reference_code WHERE event_participants.event_id = "' . $eventId . '" GROUP BY meal_coupon.participant_reference_code) AS subquery) as total_members_meals'),

                DB::raw('(SELECT (SELECT SUM(total_meals) FROM meal_coupon WHERE meal_coupon.event_id = "' . $eventId . '") - (SELECT SUM(CASE WHEN subquery.meal_coupon_count = 1 THEN meal_coupon.total_meals ELSE meal_coupon.total_meals END) FROM (SELECT meal_coupon.participant_reference_code, COUNT(*) AS meal_coupon_count, SUM(meal_coupon.total_meals) AS total_meals FROM meal_coupon INNER JOIN event_participants ON meal_coupon.participant_reference_code = event_participants.reference_code WHERE event_participants.event_id = "' . $eventId . '" GROUP BY meal_coupon.participant_reference_code) AS subquery)) as total_non_members_meals'),
                //DB::raw('COUNT(meal_coupon.total_meals) as total_redeemed'),
                DB::raw('COUNT(meal_coupon.redeemed) as redeem_date')
            )
            //->groupBy('redeem_date')
            ->get();
//table

        $event = Event::where('event_name', $eventName)->first();
        $eventId = $event->event_id;

// Retrieve data for Members Session
        $membersSessions = DB::table('event_sessions')
            ->where('event_id', $eventId)
            ->where('description', 'Member')
            ->get();

        foreach ($membersSessions as $session) {
            $session->total_registrations = DB::table('attendance_registration')
                ->where('session_id', $session->session_id)
                ->count();
        }
//        $eventSessions = DB::table('event_sessions')
//            ->where('event_id', $eventId)
//            ->select('description as event_description', 'session_date as event_session_date')
////            ->select('description as event_description')
//            ->addSelect(DB::raw('(SELECT COUNT(*) FROM attendance_registration WHERE attendance_registration.session_id = event_sessions.session_id) as session_total_attendees'))
//            ->get();

        $eventSessions = DB::table('event_sessions')
            ->join('attendance_registration', 'attendance_registration.session_id', '=', 'event_sessions.session_id')
            ->where('event_sessions.event_id', $eventId)
            ->select(
                'event_sessions.description as event_description',
                'event_sessions.session_date as event_session_date',
                'event_sessions.start_time',
                'event_sessions.end_time',
                DB::raw('COUNT(*) as session_total_attendees')
            )
            ->groupBy('event_sessions.session_id')
            ->get();




/// Retrieve the session data and assign aliases
        // Retrieve the session data and assign aliases
        // Retrieve the session data and assign aliases
        $totals = [
            'Members' => 0,
            'NonMembers' => 0,
            'All' => 0,
        ];

// Create associative arrays to store the registrations per date and time for each line
        $membersDataByDateTime = [];
        $nonMembersDataByDateTime = [];
        $allDataByDateTime = [];

        foreach ($eventSessions as $session) {
            if ($session->event_description == 'Member') {
                $totals['Members'] += $session->session_total_attendees;

                // Store the registrations per date and time for Members
                $membersDataByDateTime[$session->event_session_date][] = [
                    'datetime' => $session->event_session_date . ' ' . $session->start_time,
                    'registrations' => $session->session_total_attendees,
                ];
            } elseif ($session->event_description == 'Non members') {
                $totals['NonMembers'] += $session->session_total_attendees;

                // Store the registrations per date and time for Non-members
                $nonMembersDataByDateTime[$session->event_session_date][] = [
                    'datetime' => $session->event_session_date . ' ' . $session->start_time,
                    'registrations' => $session->session_total_attendees,
                ];
            } elseif ($session->event_description == 'All') {
                $totals['All'] += $session->session_total_attendees;

                // Store the registrations per date and time for All
                $allDataByDateTime[$session->event_session_date][] = [
                    'datetime' => $session->event_session_date . ' ' . $session->start_time,
                    'registrations' => $session->session_total_attendees,
                ];
            }
        }
//        $eventSessions = DB::table('event_participants')
//            // ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
//            ->where('event_participants.event_id', $eventId)
//            ->select(
//            // DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.balance > 0 AND event_participants.event_id = "' . $eventId . '") as total_registrations_with_balances'),
//                DB::raw('COUNT(event_participants.reference_code) as total_registrations'),
//                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Member" AND event_participants.event_id = "' . $eventId . '") as total_members'),
//                DB::raw('(SELECT COUNT(*) FROM event_participants WHERE event_participants.status = "Non member" AND event_participants.event_id = "' . $eventId . '") as total_non_members'),
//            // DB::raw('DATE(i_participant_event_registrations.registration_date_time) as registration_date')
//            )
//            // ->groupBy('registration_date')
//            ->get();


//        $eventSessions = DB::table('event_sessions')
//            ->where('event_id', $eventId)
//            ->pluck('description as event_description');

//        foreach ($eventSessions as $eventSession) {
//            $description = $eventSession->description;
//            // Use the $description data as needed
//        }
//        foreach ($eventSessions as $description) {
//            $totalAttendees = DB::table('attendance_registration')
//                ->where('session_id', $description)
//                ->count();
//
//        }

// Retrieve data for Non Members Session
        $nonMembersSessions = DB::table('event_sessions')
            ->where('event_id', $eventId)
            ->where('description', 'Non Member')
            ->get();

        foreach ($nonMembersSessions as $session) {
            $session->total_registrations = DB::table('attendance_registration')
                ->where('session_id', $session->session_id)
                ->count();
        }

// Retrieve data for Session for All
        $allSessions = DB::table('event_sessions')
            ->where('event_id', $eventId)
            ->where('description', 'All')
            ->get();

        foreach ($allSessions as $session) {
            $session->total_registrations = DB::table('attendance_registration')
                ->where('session_id', $session->session_id)
                ->count();
        }

        $conferencePackRedeemed = DB::table('i_participant_event_registrations')
            ->join('event_participants', 'i_participant_event_registrations.reference_code', '=', 'event_participants.reference_code')
            ->where('i_participant_event_registrations.event_id', $eventId)
            ->where('i_participant_event_registrations.conference_pack_redeemed', 1)
            ->select(
                DB::raw('COUNT(i_participant_event_registrations.reference_code) as total_redeemed'),
                DB::raw('DATE(i_participant_event_registrations.conference_pack_redeem_date_time) as redeem_date')
            )
            ->groupBy('redeem_date')
            ->get();

        return view('Reports.eventt-report4', compact('membersDataByDateTime', 'nonMembersDataByDateTime', 'allDataByDateTime', 'totals','event','walkinParticipants','eventSessions','hotelMealsRedeemed','participantsAttended','initialRegistrations','mealCoupon','membersSessions', 'nonMembersSessions', 'allSessions', 'conferencePackRedeemed'));
    }


    public function getParticipantsByHotel(Request $request, $hotelName)
    {
        // Query the "meal_scans_per_day" table to get participants for the selected hotel
        $participants = DB::table('meal_scans_per_day')
            ->where('hotel_name', $hotelName)
            ->select('day', 'date', 'time', 'redeemed', 'participant_reference_code', 'unique_code')
            ->get();

        // Fetch the names of participants from the "event_participants" table
        $participantNames = DB::table('event_participants')
            ->whereIn('reference_code', $participants->pluck('participant_reference_code')->toArray())
            ->pluck('participant', 'reference_code');

        // Return the participants data and names as a view
        return view('participants', [
            'participants' => $participants,
            'hotelName' => $hotelName,
            'participantNames' => $participantNames,
        ]);
    }





    public function getParticipantsByHotelLunch(Request $request, $hotelName)
    {
        // Define the time range for lunch (10 am to 3 pm)
        $startTime = Carbon::createFromTime(01, 0, 0);
        $endTime = Carbon::createFromTime(16, 0, 0);

        // Query the "meal_scans_per_day" table to get lunch participants for the selected hotel
        $participants = DB::table('meal_scans_per_day')
            ->where('hotel_name', $hotelName)
            ->whereTime('time', '>=', $startTime)
            ->whereTime('time', '<=', $endTime)
            ->select('day', 'date', 'time', 'redeemed', 'participant_reference_code', 'unique_code')
            ->get();

        // Fetch the names of participants from the "event_participants" table
        $participantNames = DB::table('event_participants')
            ->whereIn('reference_code', $participants->pluck('participant_reference_code')->toArray())
            ->pluck('participant', 'reference_code');

        // Return the participants data and names as a view
        return view('participants', [
            'participants' => $participants,
            'hotelName' => $hotelName,
            'participantNames' => $participantNames,
        ]);
    }


    public function getParticipantsByHotelSupper(Request $request, $hotelName)
    {
        // Define the time range for supper (3 pm to 10 pm)
        $startTime = Carbon::createFromTime(16, 0, 0);
        $endTime = Carbon::createFromTime(23, 0, 0);

        // Query the "meal_scans_per_day" table to get supper participants for the selected hotel
        $participants = DB::table('meal_scans_per_day')
            ->where('hotel_name', $hotelName)
            ->whereTime('time', '>=', $startTime)
            ->whereTime('time', '<=', $endTime)
            ->select('day', 'date', 'time', 'redeemed', 'participant_reference_code', 'unique_code')
            ->get();

        // Fetch the names of participants from the "event_participants" table
        $participantNames = DB::table('event_participants')
            ->whereIn('reference_code', $participants->pluck('participant_reference_code')->toArray())
            ->pluck('participant', 'reference_code');

        // Return the participants data and names as a view
        return view('participantsSupper', [
            'participants' => $participants,
            'hotelName' => $hotelName,
            'participantNames' => $participantNames,
        ]);
    }






    /**
     * Commenter PRINCE
     * Generates a report on meal scans by hotel for the latest event
     *
     * @return \Illuminate\Http\Response
     */
    public function hotelMealReport()
    {

        $event = Event::latest()->first();

        if (!$event) {
            return response()->json(['error' => 'No event found']. 404);
        }

        $mealScans = DB::table('meal_scans_per_day')
            ->where('event_id', $event->event_id)
            ->orderBy('date')
            ->get();

        return view('event_dashboard.hotel_meal_report', [
            'event' => $event,
            'mealScans' => $mealScans,
        ]);
    }


    /**
     * Commentor PRINCE
     *
     *
     * @return void
     */
    public function participantMealReport() {
        try {
            // Fetch the most recent event (or modify as needed)
            $event = Event::orderBy('created_at', 'desc')->first();

            if (!$event) {
                throw new Exception("No event record found");
            }

            // Get participants for the event
            $participants = Participant::where("event_id", $event->event_id)->get();

            if ($participants->isEmpty()) {
                throw new Exception("No participant records found");
            }

            $report = [];
            $totalMealsSum = 0;

            foreach ($participants as $participant) {
                $participantMeals = DB::table('meal_coupon')
                    ->where("participant_reference_code", $participant->reference_code)
                    ->get();

                if ($participantMeals->isEmpty()) {
                    continue; // Skip participants with no meal coupons
                }

                $totalMeals = $participantMeals->sum('total_meals'); // Get total meals per participant
                $hasAnyMealScanned = false;

                foreach ($participantMeals as $participantMeal) {
                    $totalMealsScanPerDay = DB::table("meal_scans_per_day")
                        ->where("unique_code", $participantMeal->unique_code)
                        ->get();

                    if ($totalMealsScanPerDay->isNotEmpty()) {
                        $scans = 0;
                        $hasAnyMealScanned = true;

                        foreach ($totalMealsScanPerDay as $scannedCoupon) {
                            $scans++;

                            $reportRowRecord = new \stdClass();
                            $reportRowRecord->unique_code = $scannedCoupon->unique_code;
                            $reportRowRecord->name = $participant->participant;
                            $reportRowRecord->time = $scannedCoupon->time;
                            $reportRowRecord->day = $scannedCoupon->day;
                            $reportRowRecord->totalMeals = $totalMeals;
                            $reportRowRecord->totalMealsScanned = $scans;
                            $reportRowRecord->totalMealsRemaining = max(0, $totalMeals - $scans);

                            $report[] = $reportRowRecord;
                        }
                    }
                }

                // If no meal scan was found, add default entry
                if (!$hasAnyMealScanned) {
                    $reportRowRecord = new \stdClass();
                    $reportRowRecord->unique_code = optional($participantMeals->first())->unique_code ?? "N/A";
                    $reportRowRecord->name = $participant->participant;
                    $reportRowRecord->time = "N/A";
                    $reportRowRecord->day = "N/A";
                    $reportRowRecord->totalMeals = $totalMeals;
                    $reportRowRecord->totalMealsScanned = 0;
                    $reportRowRecord->totalMealsRemaining = $totalMeals;

                    $report[] = $reportRowRecord;
                }

                $totalMealsSum += $totalMeals;
            }
        } catch (Exception $exception) {
            return redirect()->back()->withInput()->withErrors(["exception" => $exception->getMessage()]);
        }

        return view('event_dashboard.participant_meal_report', [
            'event' => $event,
            'report' => $report,
            'totalMealsSum' => $totalMealsSum,
        ]);
    }



    public function participationReport()
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return redirect()->back()->withErrors(['error' => 'No event selected.']);
        }

        $participants = Bookers::with([
            'event',
            'attireSize',
        ])
            ->where('event_id', $event->event_id)
            ->get();

        // Count attendance types
        $physicalCount = $participants->where('mode_of_attendance', 'Physical')->count();
        $virtualCount = $participants->where('mode_of_attendance', 'Virtual')->count();

        return view('Reports.participation_report', [
            'bookers' => $participants,
            'event_id' => $event->event_id,
            'physicalCount' => $physicalCount,
            'virtualCount' => $virtualCount
        ]);
    }

    public function exportParticipationAttires()
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        $event_id = $event->event_id;

        $bookers = Bookers::where('event_id', $event_id)
//            ->whereNotNull('amount_paid')
            ->orderBy('name')
            ->get();

        $output = '
        <table border="1">
            <thead>
                <tr>
                    <th>Participant Name</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Attire Color</th>
                    <th>Attire Size</th>
                    <th>Gender</th>
                    <th>Mode of Attendance</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($bookers as $booker) {
            $output .= '<tr>
            <td>' . htmlspecialchars($booker->name) . '</td>
            <td>' . htmlspecialchars($booker->company ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($booker->email) . '</td>
            <td>' . htmlspecialchars($booker->phone_number ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($booker->attireColor->color ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($booker->attireSize->attire_size ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($booker->gender ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($booker->mode_of_attendance ?? 'N/A') . '</td>

        </tr>';
        }

        $output .= '</tbody></table>';

        return response($output)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="participation_attires.xls"');
    }


}