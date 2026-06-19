<?php

namespace App\Http\Controllers;


use App\Mail\ParticipantEmail;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Log;
DB::connection()->enableQueryLog();
class MealCouponController extends Controller
{
    public function redeemMeals(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
            'meal_type' => 'required|in:lunch,dinner',
            'event_id' => 'required|string',
        ]);

        $date = $request->input('date');
        $time = $request->input('time');
        $currentDateTime = ($date && $time) ? Carbon::parse("$date $time") : now();

        $uniqueCode = $request->input('unique_code');
        $createdBy = $request->user()->id;
        $hotelName = $request->input('hotel_name');
        $mealType = $request->input('meal_type');
        $eventId = $request->input('event_id');

        $coupon = DB::table('meal_coupon')
            ->where('unique_code', $uniqueCode)
            ->where('event_id', $eventId)
            ->first();

        if (!$coupon) {
            return Helper::APIResponse(0, 'Meal coupon does not exist', HTTP_BAD_REQUEST, [
                'unique_code' => $uniqueCode
            ]);
        }

        // Get event details
        $event = DB::table('events')
            ->where('event_id', $eventId)
            ->where('start_date', '<=', $currentDateTime)
            ->whereDate('end_date', '>=', $currentDateTime)
            ->first();

        if (!$event) {
            return Helper::APIResponse(0, 'Event has ended or has not started yet', HTTP_BAD_REQUEST, [
                'unique_code' => $uniqueCode
            ]);
        }

        // Check if master tag — skip all restrictions
        $isMasterTag = DB::table('master_meal_tags')
            ->where('unique_code', $uniqueCode)
            ->where('event_id', $eventId)
            ->exists();

        if (!$isMasterTag) {
            // Special case: max 100 redemptions per day
            if ($uniqueCode === 'MLS-HWU-JNA-65') {
                $dailyRedemptions = DB::table('meal_scans_per_day')
                    ->where('unique_code', 'MLS-HWU-JNA-65')
                    ->whereDate('date', $currentDateTime->toDateString())
                    ->count();

                if ($dailyRedemptions >= 100) {
                    return Helper::APIResponse(0, 'Maximum of 100 redemptions reached for today for this special code.', HTTP_BAD_REQUEST, [
                        'unique_code' => $uniqueCode,
                        'participant_reference_code' => $coupon->participant_reference_code
                    ]);
                }
            }

            // Get participant
            $participant = DB::table('event_participants')
                ->where('reference_code', $coupon->participant_reference_code)
                ->first();

            // Location validation
            if ($mealType === 'lunch') {
                $eventVenue = $event->event_venue ?: $event->venue;
                if (strcasecmp(trim($hotelName), trim($eventVenue)) !== 0) {
                    return Helper::APIResponse(0, "Lunch is served at the event venue ({$eventVenue}) only", HTTP_BAD_REQUEST, [
                        'unique_code' => $uniqueCode
                    ]);
                }
            } else {
                if (!$participant || !$participant->accommodation) {
                    return Helper::APIResponse(0, 'Dinner is only for participants with accommodation', HTTP_BAD_REQUEST, [
                        'unique_code' => $uniqueCode
                    ]);
                }

                $assignedHotel = DB::table('hotel')->where('id', $participant->hotel_id)->value('name');
                if ($assignedHotel && strcasecmp(trim($hotelName), trim($assignedHotel)) !== 0) {
                    return Helper::APIResponse(0, "Dinner is served at your accommodation hotel ({$assignedHotel})", HTTP_BAD_REQUEST, [
                        'unique_code' => $uniqueCode
                    ]);
                }
            }

            // Day and meal limits
            $startDate = Carbon::parse($event->start_date);
            $day = $currentDateTime->diffInDays($startDate) + 1;

            if ($uniqueCode !== 'MLS-HWU-JNA-65') {
                $maxPerDay = ($participant && $participant->accommodation) ? 2 : 1;

                $alreadyRedeemed = DB::table('meal_scans_per_day')
                    ->where('unique_code', $uniqueCode)
                    ->where('day', $day)
                    ->where('meal_type', $mealType)
                    ->exists();

                if ($alreadyRedeemed) {
                    return Helper::APIResponse(0, ucfirst($mealType) . ' already redeemed for today', HTTP_BAD_REQUEST, [
                        'unique_code' => $uniqueCode
                    ]);
                }

                $redeemedToday = DB::table('meal_scans_per_day')
                    ->where('unique_code', $uniqueCode)
                    ->where('day', $day)
                    ->count();

                if ($redeemedToday >= $maxPerDay) {
                    return Helper::APIResponse(0, 'You have used all of your meals for today', HTTP_BAD_REQUEST, [
                        'unique_code' => $uniqueCode
                    ]);
                }
            }
        }

        // Calculate day
        $startDate = Carbon::parse($event->start_date);
        $day = $currentDateTime->diffInDays($startDate) + 1;

        // Update coupon and record scan atomically
        DB::transaction(function () use ($uniqueCode, $coupon, $day, $eventId, $currentDateTime, $hotelName, $mealType, $createdBy) {
            DB::table('meal_coupon')
                ->where('unique_code', $uniqueCode)
                ->where('event_id', $eventId)
                ->update([
                    'meals_redeemed' => DB::raw('IFNULL(meals_redeemed, 0) + 1'),
                ]);

            DB::table('meal_scans_per_day')->insert([
                'unique_code' => $uniqueCode,
                'participant_reference_code' => $coupon->participant_reference_code,
                'day' => $day,
                'event_id' => $eventId,
                'date' => $currentDateTime->toDateString(),
                'time' => $currentDateTime->toTimeString(),
                'hotel_name' => $hotelName,
                'meal_type' => $mealType,
                'created_by' => $createdBy,
            ]);
        });

        $participant = DB::table('event_participants')
            ->where('reference_code', $coupon->participant_reference_code)
            ->first();
        $participantName = $participant ? $participant->participant : 'Participant Not Found';

        $mealScan = DB::table('meal_scans_per_day')
            ->where('unique_code', $uniqueCode)
            ->where('date', $currentDateTime->toDateString())
            ->where('time', $currentDateTime->toTimeString())
            ->orderBy('id', 'desc')
            ->first();

        return Helper::APIResponse(1, 'Meal coupon redeemed successfully at ' . $hotelName . '.', HTTP_SUCCESS, [
            'participant_name' => $participantName,
            'meal_scan' => $mealScan,
        ]);
    }

    public function getMealCoupons(Request $request)
    {
        $event_id = $request->input('event_id');

        $participants = DB::table('meal_coupon')
            ->where('event_id', '=', $event_id)
           // ->where('event_id', $event_id)
            ->select('participant_reference_code', 'event_id', 'total_meals', 'unique_code', 'redeemed')
            ->get();

        if ($participants->isEmpty()) {
            $status = 0;
            $message = "Event ID not found.";
            $data = null;
        } else {
            $status = 1;
            $message = "Meals retrieved successfully.";
            $data = $participants;
        }

        $response = [
            'status' => $status,
            'data' => $data,
            'msg' => $message
        ];



        return response()->json(['msg' => $response], Response::HTTP_OK);

    }

    public function getMealScans(Request $request)
    {
        $event_id = $request->input('event_id');

        $participants = DB::table('meal_scans_per_day')
            ->where('event_id', '=', $event_id)
            // ->where('event_id', $event_id)
            ->select('participant_reference_code', 'event_id', 'day', 'redeemed', 'date', 'time')
            ->get();

        if ($participants->isEmpty()) {
            $status = 0;
            $message = "Event ID not found.";
            $data = null;
        } else {
            $status = 1;
            $message = "Meal scans retrieved successfully.";
            $data = $participants;
        }

        $response = [
            'status' => $status,
            'data' => $data,
            'msg' => $message
        ];



        return response()->json(['msg' => $response], Response::HTTP_OK);

    }

    public function syncMealCoupon(Request $request)
    {
        try {
            $currentTime = Carbon::now();
            $currentDate = Carbon::now()->toDateString();
            $createdBy = $request->user()->id;

            // Retrieve and check meal_data
            $rawData = $request->meal_data;

            if (is_string($rawData)) {
                $requestData = json_decode($rawData, true);
                if ($requestData === null) {
                    return response()->json([
                        'message' => 'Invalid JSON data'
                    ], 400);
                }
            } elseif (is_array($rawData)) {
                $requestData = $rawData;
            } else {
                return response()->json([
                    'message' => 'Invalid data format'
                ], 400);
            }

            // Check if meal_data is empty
            if (empty($requestData)) {
                return response()->json([
                    'message' => 'Meal data is empty'
                ], 400);
            }

            // Continue processing
            $data = $requestData;

            // Loop through the mealData array
            for ($i = 0; $i < sizeof($data); $i++) {
                if (
                    isset($data[$i]['participant_reference_code']) &&
                    isset($data[$i]['unique_code']) &&
                    isset($data[$i]['day']) &&
                    isset($data[$i]['redeemed']) &&
                    isset($data[$i]['hotel_name'])
                ) {
                    $participantReferenceCode = $data[$i]['participant_reference_code'];
                    $uniqueCode = $data[$i]['unique_code'];
                    $day = $data[$i]['day'];
                    $newRedeemedCount = $data[$i]['redeemed'];
                    $hotelName = $data[$i]['hotel_name'];
                    $event_id = $data[$i]['event_id'];

                    // Check if the unique_code exists in the meal_coupon table
                    $coupon = DB::table('meal_coupon')
                        ->where('unique_code', $uniqueCode)
                        ->first();

                    if ($coupon) {
                        try {
                            // Insert into meal_scans_per_day table
                            DB::table('meal_scans_per_day')->insert([
                                'participant_reference_code' => $participantReferenceCode,
                                'unique_code' => $uniqueCode,
                                'day' => $day,
                                'date' => $currentDate,
                                'time' => $currentTime->toTimeString(),
                                'redeemed' => $newRedeemedCount,
                                'hotel_name' => $hotelName,
                                'created_at' => now(),
                                'updated_at' => now(),
                                'created_by' => $createdBy,
                                'event_id' => $event_id
                            ]);
                            $updated_results = DB::table('meal_scans_per_day')->where('event_id', $event_id)->get();

                            // Get the participant's email address
                            $email = DB::table('event_participants')
                                ->where('reference_code', $participantReferenceCode)
                                ->value('email_address');
                            $this->sendEmailToParticipant($email, $participantReferenceCode, $newRedeemedCount, $uniqueCode);
                        } catch (\Exception $e) {
                            Log::info('Data failed to sync');
                            return response()->json([
                                'message' => $e->getMessage()
                            ], 500);
                        }
                    } else {
                        try {
                            // Insert into meal_scans_per_day_logs table
                            DB::table('meal_scans_per_day_logs')->insert([
                                'participant_reference_code' => $participantReferenceCode,
                                'unique_code' => $uniqueCode,
                                'day' => $day,
                                'date' => $currentDate,
                                'time' => $currentTime->toTimeString(),
                                'redeemed' => $newRedeemedCount,
                                'hotel_name' => $hotelName,
                                'created_at' => now(),
                                'updated_at' => now(),
                                'event_id' => $event_id,
                                'created_by' => $createdBy
                            ]);
                        } catch (\Exception $e) {
                            Log::info('Data failed to sync');
                            return response()->json([
                                'message' => $e->getMessage()
                            ], 500);
                        }
                    }
                } else {
                    return response()->json([
                        'message' => 'Required keys are missing in the data'
                    ], 400);
                }
            }

            $updated_results = DB::table('meal_scans_per_day')->where('event_id', $data[0]['event_id'])->get();
            return response()->json([
                'message' => 'Data synced and inserted successfully',
                'records' => $updated_results
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while syncing meal coupon data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendEmailToParticipant2($email, $participantReferenceCode, $uniqueCode)
    {
        try {
            $participant = DB::table('event_participants')
                ->where('reference_code', $participantReferenceCode)
                ->select('participant')
                ->first();
            // If the participant is found, get the participant's name
            $participantName = $participant ? $participant->participant : 'Participant'; // Default to 'Participant' if not found

            // Query the 'meal_coupon' table to get the redeemed count
            $redeemedCount = DB::table('meal_coupon')
                ->where('unique_code', $uniqueCode)
                ->value('redeemed');

            if ($redeemedCount === null) {
                $redeemedCount = 0; // Set to 0 if 'redeemed' is null
            }

            // Calculate the remaining meals
            $totalMeals = 5; // Replace with your actual total meals count
            $remainingMeals = $totalMeals - $redeemedCount;

            // Prepare the data for the email
            $data = [
                'email' => $email,
                'participantName' => $participantName,
                'referenceCode' => $participantReferenceCode,
                'redeemed' => $redeemedCount,
                'unique_code' => $uniqueCode,
                'remainingMeals' => $remainingMeals,
                // Add any other necessary data for the email
            ];

            // Send the email using the Mail facade
//            Mail::to($email)->send(new ParticipantEmail($data));
            // Mail::to($participant['email_address'])->send(new ParticipantNameTagMail($data));
            // Log a success message
            Log::info('Email sent to participant: ' . $email);

            return response()->json([
                'message' => 'Email sent successfully',
                'remaining_meals' => $remainingMeals,
                'participant_reference_code' => $participantReferenceCode,
            ]);

        } catch (\Exception $e) {
            // Log an error message if the email sending fails
            Log::error('Failed to send email to participant: ' . $email);
            Log::error($e->getMessage());
            // Handle the error as needed (e.g., show an error message to the user, retry sending the email, etc.)
            return response()->json([
                'message' => 'Email sending failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendEmailToParticipant($email, $participantReferenceCode,$newRedeemedCount, $uniqueCode)
    {
        try {

            $participant = DB::table('event_participants')
                ->where('reference_code', $participantReferenceCode)
                ->select('participant')
                ->first();

            // If the participant is found, get the participant's name
            $participantName = $participant ? $participant->participant : 'Participant'; // Default to 'Participant' if not found
$remainingMeals= 5 - $newRedeemedCount;
            // Prepare the data for the email
            $data = [
                'email' => $email,
                'participantName' => $participantName,
                'referenceCode' => $participantReferenceCode,
                'redeemed' => $newRedeemedCount,
                'unique_code' => $uniqueCode,
                'remainingMeals' => $remainingMeals,
                // Add any other necessary data for the email
            ];
            // Prepare the data for the email
//            $data = [
//                'email' => $email,
//                'referenceCode' => $participantReferenceCode,
//                'redeemed' => $newRedeemedCount
//                // Add any other necessary data for the email
//            ];

            // Send the email using the Mail facade
//            Mail::to($email)->send(new ParticipantEmail($data));
           // Mail::to($participant['email_address'])->send(new ParticipantNameTagMail($data));
            // Log a success message
            Log::info('Email sent to participant: ' . $email);
        } catch (\Exception $e) {
            // Log an error message if the email sending fails
            Log::error('Failed to send email to participant: ' . $email);
            Log::error($e->getMessage());
            // Handle the error as needed (e.g., show an error message to the user, retry sending the email, etc.)
        }
    }

    public function showMealInformation(Request $request)
    {
        $uniqueCode = $request->input('unique_code');
        $event_id = 'ICAM-LK_2023';
        $currentDateTime = now();
        $hotelName = $request->input('hotel_name');
        // Replace 'meal_coupon' with your actual table name and 'unique_code' with your actual column name
        $coupon = DB::table('meal_coupon')
            ->where('unique_code', $uniqueCode)
            ->first();

        // Retrieve the 'day' based on the current date and event dates
        $currentDate = now();
        $event = DB::table('events')
            ->where('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->first();

        if (!$event) {
            return response()->json([
                'message' => 'Event has ended',
                'unique_code' => $uniqueCode
            ]);
        }

        if ($coupon) {
            // Calculate the 'day' based on the current date and event dates
            $startDate = \Carbon\Carbon::parse($event->start_date);
            $day = $currentDate->diffInDays($startDate) + 1;

            $participant = DB::table('event_participants')
                ->where('reference_code', $coupon->participant_reference_code)
                ->first(); // Assuming you expect only one matching participant

            if ($participant) {
                $participantName = $participant->participant;
            } else {
                $participantName = 'Participant Not Found'; // You can set a default value if the participant is not found
            }
            $participant2 = DB::table('i_participant_event_registrations')
                ->where('participant_id', $coupon->participant_reference_code)
                ->first(); // Assuming you expect only one matching participant

            if ($participant2) {
                $participantName = $participant->participant;
                $redeemedCount = DB::table('meal_coupon')
                    ->where('unique_code', $uniqueCode)
                    ->value('redeemed');
                if ($redeemedCount === null) {
                    $redeemedCount = 0; // Set to 0 if 'redeemed' is null
                }
                // Calculate the remaining meals
                $totalMeals = 5; // Replace with your actual total meals count
                $remainingMeals = $totalMeals - $redeemedCount;

                if($remainingMeals == '0'){

                    return response()->json([
                        'message' => 'you have used all your meals'
                    ],202);

                }


                else{

                    return response()->json([
                        'message' => 'participant details',
                        'unique_code' => $uniqueCode,
                        'participant_reference_code' => $coupon->participant_reference_code,
                        'participant_name' => $participantName,
                        'hotel_name' => $hotelName,
                        'remaining_meals' => $remainingMeals,
                        'date' => $currentDateTime->toDateString(),
                        'time' => $currentDateTime->toTimeString(),

                    ],202);

                }


            } else {
                return response()->json([
                    'message' => 'Initial registration not done',

                ],201);

                //$participantName = 'Initial registration not done'; // You can set a default value if the participant is not found
            }


        } else {
            return response()->json([
                'message' => 'Meal coupon does not exist',
                'unique_code' => $uniqueCode
            ],206);
        }
    }

    private function calculateMaxRedemptions($uniqueCode, $day)
    {
        // Calculate the maximum redemptions for a given 'unique_code' and 'day' based on 'meal_scans_per_day' table
        $maxRedemptions = DB::table('meal_scans_per_day')
            ->where('unique_code', $uniqueCode)
            ->where('day', $day)
            ->count();
        return ($day === 1) ? 1 : 2;
    }

    public function ScanMealCoupon(Request $request)
    {
        $createdBy = $request->user()->id;
        $uniqueCode = $request->input('unique_code');
        $hotelName = $request->input('hotel_name');

        // Check if unique_code exists in meal_coupon table
        $mealCoupon = DB::table('meal_coupon')
            ->where('unique_code', $uniqueCode)
            ->first();

        if (!$mealCoupon) {
            return response()->json(['message' => 'Invalid unique code'], 404);
        }

        // Get the participant_reference_code from meal_coupon table
        $participantReferenceCode = $mealCoupon->participant_reference_code;

        // Check if participant_reference_code exists in event_participants table
        $eventParticipant = DB::table('event_participants')
            ->where('reference_code', $participantReferenceCode)
            ->first();

        if (!$eventParticipant) {
            return response()->json(['message' => 'Invalid participant reference code'], 404);
        }

        // Get the event_id from event_participants table
        $eventId = $eventParticipant->event_id;

        // Check if event_id exists in events table
        $event = DB::table('events')
            ->where('event_id', $eventId)
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Invalid event'], 404);
        }

        $today = Carbon::now();
        $startDate = Carbon::parse($event->start_date);
        $endDate = Carbon::parse($event->end_date);

        if ($today->lessThan($startDate)) {
            return response()->json(['message' => 'Event will start on ' . $startDate->toDateString()]);
        }

        if ($today->greaterThan($endDate)) {
            return response()->json(['message' => 'Event already happened or dates expired']);
        }

        // Process the unique_code
        $redeemed = $mealCoupon->redeemed;
        $totalMeals = $mealCoupon->total_meals;
        $lastScannedDate = $mealCoupon->last_scanned_date;
        $lastScannedTime = $mealCoupon->last_scanned_time;

        if ($redeemed >= $totalMeals) {
            return response()->json(['message' => 'All meals already redeemed'], 403);
        }

        if ($lastScannedDate && $lastScannedTime) {
            $lastScanDateTime = Carbon::parse($lastScannedDate . ' ' . $lastScannedTime);
            if ($today->diffInHours($lastScanDateTime) < 5) {
                return response()->json(['message' => 'Scanning is only allowed once every 5 hours'], 403);
            }
        }

        $scanCount = 0;
        if ($lastScannedDate) {
            $lastScannedDate = Carbon::parse($lastScannedDate)->toDateString();
            if ($lastScannedDate == $today->toDateString()) {
                // Get the current scan count for today
                $scanCount = $mealCoupon->scan_count;
            }
        }

        // Check scan count based on the day
        if ($today->equalTo($startDate)) {
            // On the first day, allow scanning once
            if ($scanCount >= 1) {
                return response()->json(['message' => 'Maximum scans reached for today'], 403);
            }
        } else {
            // On subsequent days, allow scanning twice
            if ($scanCount >= 2) {
                return response()->json(['message' => 'Maximum scans reached for today'], 403);
            }
        }

        // Increment the redeemed and scan count columns in meal_coupon table
        $updatedRedeemed = $redeemed + 1;
        $updatedScanCount = $scanCount + 1;

        // Insert into meal_scans_per_day table
        $data = [
            'event_id' => $eventId,
            'participant_reference_code' => $participantReferenceCode,
            'unique_code' => $uniqueCode,
            'day' => $today->diffInDays($startDate) + 1,
            'date' => $today->toDateString(),
            'time' => $today->toTimeString(),
            'redeemed' => $updatedRedeemed,
            'hotel_name' => $hotelName,
            'created_by' => $createdBy // Replace with the appropriate value
        ];

        DB::table('meal_scans_per_day')->insert($data);

        DB::table('meal_coupon')
            ->where('unique_code', $uniqueCode)
            ->update([
                'redeemed' => $updatedRedeemed,
                'scan_count' => $updatedScanCount,
                'last_scanned_date' => $today->toDateString(),
                'last_scanned_time' => $today->toTimeString()
            ]);

        return response()->json(['message' => 'Meal coupon scanned successfully', 'redeemed' => $updatedRedeemed]);
    }

    public function download_meal_coupon(Request $request)
    {
        $reference_code = $request->id;
        $mealCoupons = DB::table('meal_coupon')->where('participant_reference_code', $reference_code)->skip(1)->take(5)->get();

        if ($mealCoupons->isNotEmpty()) {
            return view('meal_coupon', ['mealCoupons' => $mealCoupons]);
        } else {
            // Handle the case when no matching meal coupons are found
            // For example, display an error message or redirect back with a flash message
            return redirect()->back()->with('error', 'No meal coupons found for the provided reference code.');
        }
    }


//    public function syncMealCoupon(Request $request)
//    {
//        try {
//            // Retrieve the data from the request
//            $currentTime = Carbon::now();
//            $currentDate = Carbon::now()->toDateString();
//            $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user
//
//            // Get the raw JSON data from the request and decode it into an array
//            //$rawData = $request->getContent();
//            //var_dump(($rawData));
//            $rawData = $request->meal_data;
//            // echo(gettype($request->meal_data));
//            // echo(sizeof($request->meal_data));
//            $requestData = json_decode($rawData, true);
//
//            error_log('Raw Data Type:'.$rawData);
//            //var_dump($requestData);
//            Log::info('Payload'.$rawData);
//            // error_log('Decoded Data:'.print_r($mealData));
//            // Check if the decoding was successful
//            if ($requestData === null) {
//                return response()->json([
//                    'message' => 'Invalid JSON data'
//                ], 400);
//            }
//
//            // Check if the meal_data is present
//            if (!isset($requestData)) {
//                return response()->json([
//                    'message' => 'No meal data provided'
//                ], 400);
//            }
//
//            // Retrieve the meal_data
//            $mealData = $requestData;
//
//            //var_dump($mealData['meal_data']);
//
//            // Check if the meal_data is empty
//            if (empty($mealData)) {
//                return response()->json([
//                    'message' => 'Meal data is empty'
//                ], 400);
//            }
//
//            $data = $mealData['meal_data'];
//            // Loop through the mealData array
//            for ($i = 0; $i<(sizeof($data)); $i++)
//                {
//                // Check if the required keys exist in the data
//
//                //var_dump($data[0]);
//                //echo($i);
//                if (
//                    isset($data[$i]['participant_reference_code']) &&
//                    isset($data[$i]['unique_code']) &&
//                    isset($data[$i]['day']) &&
//                    isset($data[$i]['redeemed']) &&
//                    isset($data[$i]['hotel_name'])
//                ) {
//                    // Retrieve data from the current iteration
//                    $participantReferenceCode = $data[$i]['participant_reference_code'];
//                    $uniqueCode = $data[$i]['unique_code'];
//                    $day = $data[$i]['day'];
//                    $newRedeemedCount = $data[$i]['redeemed'];
//                    $hotelName = $data[$i]['hotel_name'];
//                    $event_id = $data[$i]['event_id'];
//                    // Check if the unique_code exists in the meal_coupon table
//                    $coupon = DB::table('meal_coupon')
//                        ->where('unique_code', $uniqueCode)
//                        ->first();
//                    if ($coupon) {
//                        try {
//                            // Insert the data into the meal_scans_per_day table
//                            DB::table('meal_scans_per_day')->insert([
//                                'participant_reference_code' => $participantReferenceCode,
//                                'unique_code' => $uniqueCode,
//                                'day' => $day,
//                                'date' => $currentDate,
//                                'time' => $currentTime->toTimeString(),
//                                'redeemed' => $newRedeemedCount,
//                                'hotel_name' => $hotelName,
//                                'created_at' => now(),
//                                'updated_at' => now(),
//                                'created_by' => $createdBy,
//                                'event_id'=>$event_id
//                            ]);
//                            $updated_results = DB::table('meal_scans_per_day')->where('event_id',$event_id)->get();
//                        } catch (\Exception $e) {
//
//                            Log::info('Data failed to sync');
//                            return response()->json([
//                                'message' => $e
//                            ], 500);
//                        }
//                    } else {
//                        try {
//                            // Insert the data into the meal_scans_per_day_logs table
//                            DB::table('meal_scans_per_day_logs')->insert([
//                                'participant_reference_code' => $participantReferenceCode,
//                                'unique_code' => $uniqueCode,
//                                'day' => $day,
//                                'date' => $currentDate,
//                                'time' => $currentTime->toTimeString(),
//                                'redeemed' => $newRedeemedCount,
//                                'hotel_name' => $hotelName,
//                                'created_at' => now(),
//                                'updated_at' => now(),
//                                'event_id'=>$event_id,
//                                'created_by' => $createdBy
//                            ]);
//                        } catch (\Exception $e) {
//
//                            Log::info('Data failed to sync');
//                            return response()->json([
//                                'message' => $e
//                            ], 500);
//                        }
//                    }
//                } else {
//                    return response()->json([
//                        'message' => 'Required keys are missing in the data'
//                    ], 400);
//                }
//            }
//            $updated_results = DB::table('meal_scans_per_day')->where('event_id',$data[0]['event_id'])->get();
//            return response()->json([
//                'message' => 'Data synced and inserted successfully',
//                'records'=>$updated_results
//            ], 200);
//
//        } catch (\Exception $e) {
//            // Handle any exceptions that occurred during the process
//            return response()->json([
//                'message' => 'Error occurred while syncing meal coupon data',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
}
