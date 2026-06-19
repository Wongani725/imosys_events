<?php

namespace App\Http\Controllers;

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


class MobileAppRetrieveController extends Controller
{
//    GETTING EVENTS ATTENDED COUNT
    public function getEventsAttendedCount(Request $request)
    {
        $user = $request->user();

        // Get all reference codes associated with the user's email
        $referenceCodes = Participant::where('email_address', $user->email_address)
            ->pluck('reference_code');

        // Count all registrations that match any of the user's reference codes
        $eventsAttendedCount = ParticipantEventRegistration::whereIn('participant_id', $referenceCodes)->count();

        $accumulatedCPDHours = $eventsAttendedCount * 20;

        return Helper::APIResponse(1, 'Successfully retrieved events attended count and CPD hours.', HTTP_SUCCESS, [
            'events_attended_count' => $eventsAttendedCount,
            'accumulatedCPDHours' => $accumulatedCPDHours
        ]);
    }

//GET UPCOMING EVENT
    public function getUpcomingEvent(Request $request)
    {
        $user = $request->user();
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No upcoming event found.', HTTP_NOT_FOUND, null);
        }

        $bookedParticipant = Bookers::where('email', $user->email)
            ->where('event_id', $event->event_id)
            ->first();

        $participant = Participant::where('email_address', $user->email)
            ->where('event_id', $event->event_id)
            ->first();

        $status = null;
        if ($participant) {
            $status = 'Approved';
        } elseif ($bookedParticipant) {
            switch ($bookedParticipant->booking_status) {
                case 'Pending':
                    $status = 'Booked but pending payment';
                    break;
                case 'Payment Awaiting Receipting':
                    $status = 'Payment made, awaiting confirmation';
                    break;
                case 'Declined':
                    $status = 'Booking declined';
                    break;
                case 'Cancelled':
                    $status = 'Booking cancelled';
                    break;
                default:
                    $status = 'Booked';
            }
        }

        $eventData = $event->toArray();
        $eventData['status'] = $status;

        // Determine booking status
        $now = now(); // or Carbon::now();
        if ($event->booking_start_time <= $now && $event->booking_end_time >= $now) {
            $eventData['booking_status'] = 'Open';
        } else {
            $eventData['booking_status'] = 'Closed';
        }

        return Helper::APIResponse(1, 'Successfully retrieved upcoming event.', HTTP_SUCCESS, [$eventData]);
    }

//    GET USERS LODGING DETAILS
    public function getUserLodgingDetails(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();
        $referenceCode = $user->reference_code;

        $event = Event::orderBy('created_at', 'desc')->first();

        $participantDetails = Participant::where('reference_code', $referenceCode)
            ->where('event_id', $event->event_id)
            ->first();

        if (!$participantDetails) {
            return Helper::APIResponse(0, 'Participant not found.', HTTP_NOT_FOUND, []);
        }

        $delegation = $participantDetails->delegation;
        $accommodation = $participantDetails->hotel;
        $roomNumber = $participantDetails->room_number;

        $data = [
            'delegation' => $delegation ?? 'N/A',
            'accommodation' => $accommodation ?? 'N/A',
            'roomNumber' => $roomNumber ?? 'N/A',
        ];

        return Helper::APIResponse(1, 'Successfully retrieved member details.', HTTP_SUCCESS, [$data]);
    }

    //
    /**
     * Re Written to Include a push Notification -- By Prince Thawani
     * Commentor : Wongani Msumba
     * Updates the authenticated user's participant details.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */



    /**
     * Re Written to Include a push Notification -- By Prince Thawani
     * Commentor : Wongani
     * Register a participant for the event.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function initialRegistrations(Request $request)
    {
        $userId = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        $validatedData = $request->validate([
            'user_lat' => 'required|numeric',
            'user_lng' => 'required|numeric',
            'reference_code' => 'required|string',
        ]);

        $scannedReferenceCode = $validatedData['reference_code'];


        if ($scannedReferenceCode !== $userId) {
            return Helper::APIResponse(0, 'You are not allowed to scan other participants.', HTTP_BAD_REQUEST, []);
        }

        $participant = DB::table('event_participants')
            ->where('reference_code', $userId)
            ->where('event_id', $event->event_id)
            ->select('participant', 'attire_type', 'attire_size')
            ->first();
//                dd($participant);

        if (!$participant) {
            return Helper::APIResponse(0, 'Participant not found.', HTTP_NOT_FOUND, []);
        }

        $event_lat = -15.814137;
        $event_lng = 35.076279;

        $user_lat = $validatedData['user_lat'];
        $user_lng = $validatedData['user_lng'];

        $distance = $this->haversineDistance($user_lat, $user_lng, $event_lat, $event_lng);

        if ($distance > 8.04672) {
            return Helper::APIResponse(0, 'You are not in the vicinity of the event.', HTTP_BAD_REQUEST, []);
        }

        $event = Event::orderBy('created_at', 'desc')->first();
        $event_id = $event ? $event->event_id : null;

        $existingRegistration = DB::table('i_participant_event_registrations')
            ->where('participant_id', $userId)
            ->where('event_id', $event_id)
            ->first();

        if ($existingRegistration) {
            return Helper::APIResponse(0, 'Participant is already registered for the event.', HTTP_BAD_REQUEST, []);
        }

        DB::table('i_participant_event_registrations')->insert([
            'participant_id' => $userId,
            'event_id' => $event_id,
        ]);

        $participant_data = [
            'name' => $participant->participant,
        ];

        // Send Push Notification to this participant
        $member = \App\Models\Member::where('reference_code', $userId)->first();

        if ($member) {
            NotificationHelper::sendPushNotification(
                $member->id,
                $participant->participant, // Participant name
                'Registration Successful',
                'You have successfully registered for the event.',
                []
            );
        }

        return Helper::APIResponse(1, 'Initial Registration successful', HTTP_SUCCESS, [$participant_data]);
    }







    /**
     * Calculate the distance between two points on a sphere using the Haversine formula.
     *

     * @param float $lng1 Longitude of the first point in [deg decimal]
     * @param float $lat2 Latitude of the second point in [deg decimal]
     * @param float $lng2 Longitude of the second point in [deg decimal]
     * @return float Distance between the two points in [km]
     */

    private function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6371; // Radius of the earth in kilometers

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat / 2) * sin($dlat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dlng / 2) * sin($dlng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c; // Distance in kilometers
    }

//    MEAL COUNT
    public function getMealCount(Request $request)
    {
        // Get the logged-in user ID
        $userId = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        $mealCouponData = DB::table('meal_coupon')
            ->where('unique_code', $userId)
            ->where('event_id', $event->event_id)
            ->first();
//        dd($mealCouponData);

        if (!$mealCouponData) {
            return Helper::APIResponse(0, 'No meal coupon data found.', HTTP_NOT_FOUND, []);
        }

        $redeemedMeals = $mealCouponData->redeemed ?? 0;

        $totalMeals = $mealCouponData->total_meals ?? 0;

        // Calculate the remaining meals
        $remainingMeals = $totalMeals - $redeemedMeals;

        $participant_data = [
            'redeemed_meals' => $redeemedMeals,
            'remaining_meals' => $remainingMeals,
            'total_meals' => $totalMeals,
        ];

        // Return the response using Helper::APIResponse
        return Helper::APIResponse(1, 'Meal count retrieved successfully', HTTP_SUCCESS, [$participant_data]);
    }

//    SESSION ATTENDANCE
    public function getSessionAttendance(Request $request)
    {
        $userId = $request->user()->reference_code;

        $event = Event::orderBy('created_at', 'desc')->first();

        $total_sessions = DB::table('event_sessions')
            ->where('event_id', $event->event_id)
            ->count();

        $attended_sessions = DB::table('attendance_registration')
            ->where('reference_code', $userId)
            ->where('event_id', $event->event_id)
            ->count();

        $sessions_absent_count = $total_sessions - $attended_sessions;
//        dd($sessions_absent_count);

        $participant_data = [
            'sessions_attended_count' => $attended_sessions,
            'sessions_absent_count' => $sessions_absent_count,
            'total_sessions' => $total_sessions,
        ];

        return Helper::APIResponse(1, 'Session attendance history retrieved successfully', HTTP_SUCCESS, [$participant_data]);
    }

//    CHOOSE WHERE TO EAT
    /**
     * This function is used to choose a hotel and redeem a meal coupon.
     *
     * It takes a hotel name as an input and validates if the hotel name exists in the hotel table for the given event_id.
     * It then updates the meal_coupon table with the chosen hotel name and the current time.
     *
     * It returns a response with the meal period (Lunch or Supper) and the hotel name chosen.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chooseHotelAndRedeemMeal(Request $request)
    {
        $uniqueCode = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        $hotelName = $request->input('hotel_name');
        $currentDateTime = now();
        $currentHour = $currentDateTime->hour;

        $participantHotel = DB::table('event_participants')
            ->where('reference_code', $uniqueCode)
            ->value('hotel');

        // Define meal period
        $mealPeriod = '';

        if ($currentHour >= 6 && $currentHour < 10) {
            $mealPeriod = 'Lunch';
            if (!$hotelName) {
                $hotelName = $participantHotel;
            }
        } elseif ($currentHour >= 12 && $currentHour < 15) {
            $mealPeriod = 'Supper';
            if (!$hotelName) {
                $hotelName = $participantHotel;
            }
        } else {
            return Helper::APIResponse(0, 'Meal selection time has passed. Please wait for the next meal period.', HTTP_BAD_REQUEST, []);
        }

        // Validate hotel name
        $validHotel = DB::table('hotel')
            ->where('event_id', $event->event_id)
            ->where('name', $hotelName)
            ->first();

        if (!$validHotel) {
            return Helper::APIResponse(0, 'Invalid hotel name for this event.', HTTP_BAD_REQUEST, []);
        }

        $coupon = DB::table('meal_coupon')
            ->where('unique_code', $uniqueCode)
            ->first();

        if ($coupon) {
            DB::table('meal_coupon')
                ->where('unique_code', $uniqueCode)
                ->update([
                    'hotel_name' => $hotelName,
                    'updated_at' => $currentDateTime
                ]);

            // Send Push Notification
            $member = Member::where('reference_code', $uniqueCode)->first();
            if ($member) {
                NotificationHelper::sendPushNotification(
                    $member->id,
                    $member->participant ?? 'Participant',
                    "$mealPeriod Redeemed",
                    "Your $mealPeriod has been successfully redeemed at $hotelName.",
                    []
                );
            }

            return Helper::APIResponse(1, "{$mealPeriod} chosen successfully.", HTTP_SUCCESS, [
                'unique_code' => $uniqueCode,
                'hotel_name' => $hotelName,
                'meal_period' => $mealPeriod
            ]);
        } else {
            return Helper::APIResponse(0, 'Meal coupon does not exist.', HTTP_BAD_REQUEST, []);
        }
    }


//    GET HOTELS
    public function getHotels(Request $request)
    {
        $uniqueCode = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        $allHotels = DB::table('hotel')
            ->where('event_id', $event->event_id)
            ->get();
//        dd($allHotels);

        return Helper::APIResponse(1, 'Hotels for event id ' . $event->event_id .' retrieved successfully!', HTTP_SUCCESS, $allHotels->toArray());

    }

//    NAME TAG
    public function getNameTag(Request $request)
    {
        $uniqueCode = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        $image = $event->image;
//        dd($image);

        $participantData = DB::table('event_participants')
            ->where('event_id', $event->event_id)
            ->where('reference_code', $uniqueCode)
            ->first();


        if (!$participantData) {
            return Helper::APIResponse(0, 'Participant not found for this event.', HTTP_NOT_FOUND, []);
        }

        $background_image = asset($image);


        $participant_data = [
            'name' => $participantData->participant,
            'reference_code' => $participantData->reference_code,
            'company_name' => $participantData->company_name,
            'position' => $participantData->position,
            'background_image' => $background_image,
        ];

        return Helper::APIResponse(1, 'Participant data for ' . $participantData->participant .' retrieved successfully!', HTTP_SUCCESS, $participant_data);

    }

//    GET PROGRAM AND PRESENTATION
    public function getProgram(Request $request)
    {
        $uniqueCode = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        $program = asset('background_images/' . $event->event_id . '_programme.png');
//        dd($program);

        $event_program = [
            'program' => $program,
        ];

        return Helper::APIResponse(1, 'Program for ' . $event->event_id .' retrieved successfully!', HTTP_SUCCESS, $event_program);
    }

//    GET EVALUATION QUESTIONS
    public function getQuestionsForEvent(Request $request)
    {
        $uniqueCode = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();
//        dd($event->event_id);

        $questions = EvaluationQuestion::where('Event_id', $event->event_id)
            ->with(['options', 'speakers'])
            ->get();

        $formattedQuestions = $questions->map(function ($question) {
            $questionData = [
                'id' => $question->id,
                'question' => $question->Question,
                'section' => $question->Section,
                'type' => $question->Type,
            ];

            // If the type is 'radio', include options
            if ($question->Type === 'radio') {
                $questionData['options'] = $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'value' => $option->value,
                    ];
                });
            }

            // If the section is 'Speakers', include speaker names
            if ($question->Section === 'SPEAKERS') {
                $questionData['speakers'] = $question->speakers->map(function ($speaker) {
                    return [
                        'speaker_name' => $speaker->speaker_name,
                    ];
                });
            }

            return $questionData;
        });

        return Helper::APIResponse(1, 'Evaluation questions for ' . $event->event_id . ' retrieved successfully!', HTTP_SUCCESS, [$formattedQuestions]);
    }

//    SUBMIT EVALUATION
    public function storeEvaluationData(Request $request)
    {
        $reference_code = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        // Check if the evaluation already exists for this rparticipant
        $evaluation = DB::table('evaluations')
            ->where('reference_code', $reference_code)
            ->where('event_id', $event->event_id)
            ->first();

        $status = $evaluation ? true : false;

        // If an evaluation already exists do not submit another evaluation
        if ($status) {
            return Helper::APIResponse(0, 'Evaluation already submitted for this event.', HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'Name' => 'required|string|max:255',
            'Email' => 'required|email|max:255',
            'answers' => 'array',
            'answers.*' => 'required|string',
            'ratings' => 'array',
            'ratings.*' => 'array',
            'ratings.*.*' => 'required|numeric|min:1|max:5',
            'text_answer' => 'array',
            'text_answer.*' => 'nullable|string',
        ]);

        $data = [
            'name' => $validated['Name'],
            'email' => $validated['Email'],
            'reference_code' => $reference_code,
            'event_id' => $event->event_id,
        ];

        $evaluationId = DB::table('evaluations')->insertGetId([
            'name' => $validated['Name'],
            'email' => $validated['Email'],
            'reference_code' => $reference_code,
            'event_id' => $event->event_id,
        ]);

        foreach ($validated['answers'] as $questionId => $answer) {
            DB::table('evaluation_answers')->insert([
                'evaluation_id' => $evaluationId,
                'question_id' => $questionId,
                'answer' => $answer,
            ]);
        }

        foreach ($validated['ratings'] as $questionId => $speakerData) {
            foreach ($speakerData as $speakerId => $rating) {
                DB::table('evaluation_to_speakers')->insert([
                    'evaluation_id' => $evaluationId,
                    'question_id' => $questionId,
                    'speaker_id' => $speakerId,
                    'rating' => $rating,
                ]);
            }
        }

        // Store text answers (optional) in the evaluation_answers table
        foreach ($validated['text_answer'] as $questionId => $textAnswer) {
            DB::table('evaluation_answers')->insert([
                'evaluation_id' => $evaluationId,
                'question_id' => $questionId,
                'text_answer' => $textAnswer,
            ]);
        }

        // Send the email with the certificate (if needed)
//    Mail::to($validated['Email'])->send(new EmailCertificates($data));

        return Helper::APIResponse(1, 'Evaluation submitted successfully.', HTTP_SUCCESS, $data);
    }


//  VIEW CERTIFICATE
    public function getCertificate(Request $request)
    {
        $uniqueCode = $request->user()->reference_code;
        $participant = $request->user()->participant;
        $event = Event::orderBy('created_at', 'desc')->first();

        $evaluation = DB::table('evaluations')
            ->where('reference_code', $uniqueCode)
            ->where('event_id', $event->event_id)
            ->first();

        $certificate = asset('certificate/2024-Certificate.png');

        // Determine the status based on the presence of the reference code in the evaluations table
        $status = $evaluation ? true : false;

        $participant_data = [
            'certificate' => $certificate,
            'name' => $participant,
            'reference_code' => $uniqueCode,
            'status' => $status,
        ];

        return Helper::APIResponse(1, 'Certificate for ' . $participant .' retrieved successfully!', HTTP_SUCCESS, $participant_data);
    }

    //  VIEW PROFILE
    public function getUserProfile(Request $request)
    {
        $uniqueCode = $request->user()->reference_code;

        $participant_data = [
            'name' => $request->user()->participant,
            'reference_code' => $uniqueCode,
            'email' => $request->user()->email_address,
            'phone' => $request->user()->phone_number,
            'company' => $request->user()->company_name,
            'position' => $request->user()->position,
            'gender' => $request->user()->gender,
            'status' => $request->user()->status,
        ];

        return Helper::APIResponse(1, 'Profile for ' . $request->user()->participant .' retrieved successfully!', HTTP_SUCCESS, $participant_data);
    }

    //  GET EXTRA MEAL COUPONS
    public function getExtraMealCoupons(Request $request)
    {
        $reference_code = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        $mealCoupons = DB::table('meal_coupon')
            ->where('participant_reference_code', $reference_code)
            ->where('event_id', $event->event_id)
            ->skip(1)
            ->take(5)
            ->get();

        $coupons = [];
        foreach ($mealCoupons as $index => $coupon) {
            $couponNumber = 'Coupon ' . ($index + 1);
            $coupons[$couponNumber] = route('qrcode', $coupon->unique_code);
        }

        return Helper::APIResponse(1, 'Extra meal coupons for ' . $request->user()->participant . ' retrieved successfully!', HTTP_SUCCESS, $coupons);
    }

//    EXTRA MEAL COUPON COUNT AND HISTORY
    public function getExtraMealCouponsHistory(Request $request)
    {
        $userId = $request->user()->reference_code;
        $event = Event::orderBy('created_at', 'desc')->first();

        // Fetch main user's meal coupon data
        $mealCouponData = DB::table('meal_coupon')
            ->where('unique_code', $userId)
            ->where('event_id', $event->event_id)
            ->first();

        if (!$mealCouponData) {
            return Helper::APIResponse(0, 'No meal coupon data found.', HTTP_NOT_FOUND, []);
        }

        // Main user's meal counts
        $redeemedMeals = $mealCouponData->redeemed ?? 0;
        $totalMeals = $mealCouponData->total_meals ?? 0;

        // Calculate the remaining meals for the main user
        $remainingMeals = $totalMeals - $redeemedMeals;

        // Meal history data for extra coupons
        $extraMealCoupons = DB::table('meal_coupon')
            ->where('participant_reference_code', $userId)
            ->where('event_id', $event->event_id)
            ->skip(1)
            ->take(5)
            ->get();

        $extraMealHistory = [];

        foreach ($extraMealCoupons as $index => $extraCoupon) {
            $couponNumber = 'Coupon ' . ($index + 1);
            $extraMealHistory[$couponNumber] = [
                'qrcode_url' => route('qrcode', $extraCoupon->unique_code),
                'redeemed_meals' => $extraCoupon->redeemed ?? 0,
                'total_meals' => $extraCoupon->total_meals ?? 0,
                'remaining_meals' => ($extraCoupon->total_meals ?? 0) - ($extraCoupon->redeemed ?? 0),
            ];
        }

        $response = [
            'main_user' => [
                'redeemed_meals' => $redeemedMeals,
                'remaining_meals' => $remainingMeals,
                'total_meals' => $totalMeals,
            ],
            'extra_meal_history' => $extraMealHistory,
        ];

        return Helper::APIResponse(1, 'Meal count and history retrieved successfully.', HTTP_SUCCESS, $response);
    }

    public function getAds()
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        // Get the current date and time
        $currentDateTime = now();

        $sponsor_ads = SponsorAd::select('id', 'sponsor', 'image')
            ->where('start_date', '<=', $currentDateTime)
            ->where('end_date', '>=', $currentDateTime)
            ->where('event_id', $event->event_id)
            ->get();

        // Get the APP_URL from the environment
        $appUrl = env('APP_URL');

        // Append the APP_URL to each video's URL
        foreach ($sponsor_ads as $advertisement) {
            $advertisement->image = rtrim($appUrl,'/') . '/' . ltrim($advertisement->image, '/');
        }

        return Helper::APIResponse(1, 'Sponsor ads retrieved successfully', HTTP_SUCCESS, $sponsor_ads->toArray());

    }

}

