<?php

namespace App\Http\Controllers;

use App\Models\MealCoupon;
use App\Models\User;
use App\Mail\OTPMail;
use App\Models\Event;
use App\Models\Member;
use App\Helpers\Helper;
use App\Models\Option2;
use App\Models\Speaker;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Mail\EmailCertificates;
use App\Models\OneTimePassword;
use App\Models\EvaluationQuestion;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\ParticipantEventRegistration;
use App\Models\Bookers;
use App\Models\SponsorAd;
use App\Models\MealSelection;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class OfflineScanningController extends Controller
{
    public function getParticipants(Request $request)
    {
        $event = Event::latest()->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $perPage = $request->input('per_page', 50);

        // Paginate participants
        $participants = Participant::where('event_id', $event->event_id)
            ->paginate($perPage);

        if ($participants->isEmpty()) {
            return Helper::APIResponse(1, 'No participants found for the latest event.', HTTP_NOT_FOUND, []);
        }

        // Return paginated data including metadata
        return Helper::APIResponse(1, 'Participants retrieved successfully.', HTTP_SUCCESS, [

            'total' => $participants->total(),
            'per_page' => $participants->perPage(),
            'last_page' => $participants->lastPage(),
            'current_page' => $participants->currentPage(),
            'participants' => $participants->items(),

        ]);
    }

    public function getMealCoupons(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $perPage = $request->input('per_page', 50);


        $meal_coupons = MealCoupon::where('event_id', $event->event_id)->paginate($perPage);

        if ($meal_coupons->isEmpty()) {
            return Helper::APIResponse(1, 'No meal coupons found for the latest event.', HTTP_NOT_FOUND, []);
        }

        return Helper::APIResponse(1, 'Meal coupons retrieved successfully.', HTTP_SUCCESS,
            [
                'total' => $meal_coupons->total(),
                'per_page' => $meal_coupons->perPage(),
                'last_page' => $meal_coupons->lastPage(),
                'current_page' => $meal_coupons->currentPage(),
                'meal_coupons' => $meal_coupons->items(),

            ]);
    }

    public function getMealScans(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $perPage = $request->input('per_page', 50);


        $meal_scans = DB::table('meal_scans_per_day')->where('event_id', $event->event_id)->paginate($perPage);

        if ($meal_scans->isEmpty()) {
            return Helper::APIResponse(1, 'No meal scans found for the latest event.', HTTP_NOT_FOUND, []);
        }

        return Helper::APIResponse(1, 'Meal scans retrieved successfully.', HTTP_SUCCESS,
            [
                'total' => $meal_scans->total(),
                'per_page' => $meal_scans->perPage(),
                'last_page' => $meal_scans->lastPage(),
                'current_page' => $meal_scans->currentPage(),
                'meal_scans' => $meal_scans->items(),

            ]);
    }

    public function getInitialRegistrations(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $perPage = $request->input('per_page', 50);

        $initial_registrations = DB::table('i_participant_event_registrations')->where('event_id', $event->event_id)->paginate($perPage);
        Log::info('Participants fetched.');

        if ($initial_registrations->isEmpty()) {
            return Helper::APIResponse(1, 'No initially registered participants found for the latest event.', HTTP_NOT_FOUND, []);
        }
        Log::info('Participants fetched.');


        return Helper::APIResponse(1, 'Registered participants retrieved successfully.', HTTP_SUCCESS,
            [
                'total' => $initial_registrations->total(),
                'per_page' => $initial_registrations->perPage(),
                'last_page' => $initial_registrations->lastPage(),
                'current_page' => $initial_registrations->currentPage(),
                'initial_registrations' => $initial_registrations->items(),


            ]);
    }

    public function getConferenceAttendance(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $perPage = $request->input('per_page', 50);

        $conference_attendants = DB::table('attendance_registration')->where('event_id', $event->event_id)->paginate($perPage);

        if ($conference_attendants->isEmpty()) {
            return Helper::APIResponse(1, 'No conference attendants found for the latest event.', HTTP_NOT_FOUND, []);
        }

        return Helper::APIResponse(1, 'Conference attendants retrieved successfully.', HTTP_SUCCESS,
            [
                'total' => $conference_attendants->total(),
                'per_page' => $conference_attendants->perPage(),
                'last_page' => $conference_attendants->lastPage(),
                'current_page' => $conference_attendants->currentPage(),
                'conference_attendants' => $conference_attendants->items(),


            ]);
    }

    public function getSessions(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $event_sessions = DB::table('event_sessions')->where('event_id', $event->event_id)->get();

        if ($event_sessions->isEmpty()) {
            return Helper::APIResponse(1, 'No sessions found for the latest event.', HTTP_NOT_FOUND, []);
        }

        return Helper::APIResponse(1, 'Event sessions retrieved successfully.', HTTP_SUCCESS, $event_sessions->toArray());
    }

    public function hotelMealReport()
    {
        $event = \App\Models\Event::latest()->first();

        if (!$event) {
            return back()->with('error', 'No active event found.');
        }

        $event_id = $event->event_id;

        $start = Carbon::parse($event->start_date);
        $end = Carbon::parse($event->end_date);

        $datesMeals = collect();

        // Generate meals for Day 1 (Supper), Day 2 & 3 (Lunch + Supper)
        $dayIndex = 1;
        for ($date = $start->copy(); $date->lte($end); $date->addDay(), $dayIndex++) {
            if ($dayIndex === 1) {
                $datesMeals->push([
                    'date' => $date->toDateString(),
                    'meal' => 'Supper'
                ]);
            } elseif (in_array($dayIndex, [2, 3])) {
                $datesMeals->push([
                    'date' => $date->toDateString(),
                    'meal' => 'Lunch'
                ]);
                $datesMeals->push([
                    'date' => $date->toDateString(),
                    'meal' => 'Supper'
                ]);
            }
        }

        $reportData = [];

        foreach ($datesMeals as $dm) {
            $date = $dm['date'];
            $meal = $dm['meal'];

            // -------------------------------
            // 1. Extras
            $extrasCount = DB::table('meal_coupon')
                ->where('event_id', $event_id)
                ->where('unique_code', 'like', '%-EXTRA-%')
                ->count();


            $reportData[] = [
                'hotel_name' => 'Extras',
                'meal' => $meal,
                'day' => Carbon::parse($date)->format('l'),
                'date' => $date,
                'category' => 'Extras',
                'total' => $extrasCount
            ];

            // -------------------------------
            // 2. Selected Hotel
            $selectedHotels = DB::table('meal_selections')
                ->where('event_id', $event_id)
                ->where('day', $date)
                ->where('meal', $meal)
                ->select('hotel_name', DB::raw('count(*) as total'))
                ->groupBy('hotel_name')
                ->get();

            foreach ($selectedHotels as $row) {
                $reportData[] = [
                    'hotel_name' => $row->hotel_name,
                    'meal' => $meal,
                    'day' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'category' => 'Selected Hotel',
                    'total' => $row->total
                ];
            }

            // -------------------------------
            // 3. Default Hotel (No Meal Selected)
            $defaultHotelParticipants = DB::table('event_participants as ep')
                ->leftJoin('meal_selections as ms', function ($join) use ($date, $meal, $event_id) {
                    $join->on('ep.reference_code', '=', 'ms.reference_code')
                        ->where('ms.day', $date)
                        ->where('ms.meal', $meal)
                        ->where('ms.event_id', $event_id);
                })
                ->where('ep.event_id', $event_id)
                ->where(function ($query) {
                    $query->whereNotNull('ep.hotel')
                        ->where('ep.hotel', '!=', '');
                })
                ->whereNull('ms.reference_code')
                ->select('ep.hotel', DB::raw('count(*) as total'))
                ->groupBy('ep.hotel')
                ->get();

            foreach ($defaultHotelParticipants as $row) {
                $reportData[] = [
                    'hotel_name' => $row->hotel,
                    'meal' => $meal,
                    'day' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'category' => 'Default Hotel (No Meal Selected)',
                    'total' => $row->total
                ];
            }

            // -------------------------------
            // 4. Defaulted to Sun n Sand
            $sunSandCount = DB::table('event_participants as ep')
                ->leftJoin('meal_selections as ms', function ($join) use ($date, $meal, $event_id) {
                    $join->on('ep.reference_code', '=', 'ms.reference_code')
                        ->where('ms.day', $date)
                        ->where('ms.meal', $meal)
                        ->where('ms.event_id', $event_id);
                })
                ->where('ep.event_id', $event_id)
                ->where(function ($query) {
                    $query->whereNull('ep.hotel')
                        ->orWhere('ep.hotel', '');
                })
                ->whereNull('ms.reference_code')
                ->count();

            if ($sunSandCount > 0) {
                $reportData[] = [
                    'hotel_name' => 'Sun n Sand Holiday Resort',
                    'meal' => $meal,
                    'day' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'category' => 'Defaulted to Sun n Sand',
                    'total' => $sunSandCount
                ];
            }
        }
        return view('Reports.hotel_meal_report', compact('reportData'));
    }

    public function exportHotelMealReport()
    {
        $event = \App\Models\Event::latest()->first();

        if (!$event) {
            return back()->with('error', 'No active event found.');
        }

        $event_id = $event->event_id;
        $start = Carbon::parse($event->start_date);
        $end = Carbon::parse($event->end_date);

        $datesMeals = collect();

        $dayIndex = 1;
        for ($date = $start->copy(); $date->lte($end); $date->addDay(), $dayIndex++) {
            if ($dayIndex === 1) {
                $datesMeals->push(['date' => $date->toDateString(), 'meal' => 'Supper']);
            } elseif (in_array($dayIndex, [2, 3])) {
                $datesMeals->push(['date' => $date->toDateString(), 'meal' => 'Lunch']);
                $datesMeals->push(['date' => $date->toDateString(), 'meal' => 'Supper']);
            }
        }

        $reportData = [];

        foreach ($datesMeals as $dm) {
            $date = $dm['date'];
            $meal = $dm['meal'];

            // 1. Extras
            $extrasCount = DB::table('meal_coupon')
                ->where('event_id', $event_id)
                ->where('unique_code', 'like', '%-EXTRA-%')
                ->count();

            $reportData[] = [
                'hotel_name' => 'Extras',
                'meal' => $meal,
                'day' => Carbon::parse($date)->format('l'),
                'date' => $date,
                'category' => 'Extras',
                'total' => $extrasCount
            ];

            // 2. Selected Hotels
            $selectedHotels = DB::table('meal_selections')
                ->where('event_id', $event_id)
                ->where('day', $date)
                ->where('meal', $meal)
                ->select('hotel_name', DB::raw('count(*) as total'))
                ->groupBy('hotel_name')
                ->get();

            foreach ($selectedHotels as $row) {
                $reportData[] = [
                    'hotel_name' => $row->hotel_name,
                    'meal' => $meal,
                    'day' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'category' => 'Selected Hotel',
                    'total' => $row->total
                ];
            }

            // 3. Default Hotel (No Meal Selected)
            $defaultHotelParticipants = DB::table('event_participants as ep')
                ->leftJoin('meal_selections as ms', function ($join) use ($date, $meal, $event_id) {
                    $join->on('ep.reference_code', '=', 'ms.reference_code')
                        ->where('ms.day', $date)
                        ->where('ms.meal', $meal)
                        ->where('ms.event_id', $event_id);
                })
                ->where('ep.event_id', $event_id)
                ->where(function ($query) {
                    $query->whereNotNull('ep.hotel')->where('ep.hotel', '!=', '');
                })
                ->whereNull('ms.reference_code')
                ->select('ep.hotel', DB::raw('count(*) as total'))
                ->groupBy('ep.hotel')
                ->get();

            foreach ($defaultHotelParticipants as $row) {
                $reportData[] = [
                    'hotel_name' => $row->hotel,
                    'meal' => $meal,
                    'day' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'category' => 'Default Hotel (No Meal Selected)',
                    'total' => $row->total
                ];
            }

            // 4. Defaulted to Sun n Sand
            $sunSandCount = DB::table('event_participants as ep')
                ->leftJoin('meal_selections as ms', function ($join) use ($date, $meal, $event_id) {
                    $join->on('ep.reference_code', '=', 'ms.reference_code')
                        ->where('ms.day', $date)
                        ->where('ms.meal', $meal)
                        ->where('ms.event_id', $event_id);
                })
                ->where('ep.event_id', $event_id)
                ->where(function ($query) {
                    $query->whereNull('ep.hotel')->orWhere('ep.hotel', '');
                })
                ->whereNull('ms.reference_code')
                ->count();

            if ($sunSandCount > 0) {
                $reportData[] = [
                    'hotel_name' => 'Sun n Sand Holiday Resort',
                    'meal' => $meal,
                    'day' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'category' => 'Defaulted to Sun n Sand',
                    'total' => $sunSandCount
                ];
            }
        }

        // Prepare HTML table for Excel
        $output = '
    <table border="1">
        <thead>
            <tr>
                <th>Hotel</th>
                <th>Meal</th>
                <th>Day</th>
                <th>Date</th>
                <th>Category</th>
                <th>Expected Count</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($reportData as $row) {
            $output .= '<tr>
            <td>' . htmlspecialchars($row['hotel_name']) . '</td>
            <td>' . htmlspecialchars($row['meal']) . '</td>
            <td>' . htmlspecialchars($row['day']) . '</td>
            <td>' . htmlspecialchars($row['date']) . '</td>
            <td>' . htmlspecialchars($row['category']) . '</td>
            <td>' . htmlspecialchars($row['total']) . '</td>
        </tr>';
        }

        $output .= '</tbody></table>';

        return response($output)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="hotel_meal_report.xls"');
    }

    public function getUserMealScans(Request $request)
    {
        // Get the currently authenticated user
        $user = $request->user();

        // Check if user is authenticated (you can also check with $user->id or $user->is_admin for additional checks)
        if (!$user) {
            return Helper::APIResponse(0, 'User not authenticated.', HTTP_UNAUTHORIZED, []);
        }

        // Get the latest event
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        // Get meal scans for the latest event
        $meal_selections = MealSelection::where('event_id', $event->event_id)
            ->where('reference_code', $user->reference_code)
            ->get();

        if ($meal_selections->isEmpty()) {
            return Helper::APIResponse(1, 'No meal selections found for the latest event for the logged-in user.', HTTP_NOT_FOUND, [
                'meal_selections' => []
            ]);
        }


        // Include user details in the response
        $responseData = [
            'meal_selections' => $meal_selections->toArray()
        ];

        return Helper::APIResponse(1, 'Meal selections retrieved successfully along with user details.', HTTP_SUCCESS, $responseData);
    }

    public function getMealSelections(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $meal_selections = MealSelection::where('event_id', $event->event_id)->get();

        if ($meal_selections->isEmpty()) {
            return Helper::APIResponse(1, 'No meal selections found for the latest event.', HTTP_NOT_FOUND, []);
        }

        return Helper::APIResponse(1, 'Meal selections retrieved successfully.', HTTP_SUCCESS, $meal_selections->toArray());
    }

    public function showMealSelectionPage(Request $request)
    {
        $referenceCode = $request->user()->reference_code;

        // Get latest event
        $event = Event::orderBy('created_at', 'desc')->first();
        if (!$event) {
            return back()->with('error', 'No active event found.');
        }

        // Get hotels for the current event
        $hotels = DB::table('hotel')
            ->where('event_id', $event->event_id)
            ->get();

        // Fetch existing meal selection (if any)
        $mealSelections = MealSelection::where('reference_code', $referenceCode)
            ->where('event_id', $event->event_id)
            ->get();

        return view('web_booking.web_auth.meal_selection', [
            'hotels' => $hotels,
            'meal_selections' => $mealSelections
        ]);
    }

    public function chooseHotelAndRedeemMeal(Request $request)
    {
        $referenceCode = $request->user()->reference_code;
        $hotelName     = $request->input('hotel_name');

        // Get participant
        $user = DB::table('event_participants')
            ->where('reference_code', $referenceCode)
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Participant not found.');
        }

        // Get latest event
        $event = Event::orderBy('created_at', 'desc')->first();
        if (!$event) {
            return redirect()->back()->with('error', 'No active event found.');
        }

        // Get participant’s meal coupon
        $mealCoupon = DB::table('meal_coupon')
            ->where('unique_code', $referenceCode)
            ->first();

        if (!$mealCoupon) {
            return redirect()->back()->with('error', 'No meal coupon found.');
        }

        $currentDateTime = now();
        $currentHour     = $currentDateTime->hour;
        $currentDay      = $currentDateTime->toDateString();

        // Determine meal period
        if ($currentHour >= 6 && $currentHour < 11) {
            $mealPeriod = 'Lunch';
        } elseif ($currentHour >= 12 && $currentHour < 15) {
            $mealPeriod = 'Supper';
        } else {
            return redirect()->back()->with('error', 'Meal selection time has passed. Please wait for the next meal period.');
        }

        // Get the hotel
        $hotel = DB::table('hotel')
            ->where('event_id', $event->event_id)
            ->where('name', $hotelName)
            ->first();

        if (!$hotel) {
            return redirect()->back()->with('error', 'Selected hotel is not valid for this event.');
        }

        // Check existing meal selection
        $existing = \App\Models\MealSelection::where([
            ['reference_code', $referenceCode],
            ['meal', $mealPeriod],
            ['day', $currentDay],
            ['event_id', $event->event_id],
        ])->first();

        if ($existing) {
            $existing->update([
                'hotel_id'   => $hotel->id,
                'hotel_name' => $hotel->name,
            ]);
        } else {
            \App\Models\MealSelection::create([
                'event_id'       => $event->event_id,
                'reference_code' => $referenceCode,
                'hotel_id'       => $hotel->id,
                'hotel_name'     => $hotel->name,
                'day'            => $currentDay,
                'meal'           => $mealPeriod,
            ]);
        }

        return redirect()->back()->with('success', "Successfully selected {$hotel->name} for {$mealPeriod} for {$user->participant}.");
    }


}
