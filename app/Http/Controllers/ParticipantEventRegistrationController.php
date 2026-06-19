<?php

namespace App\Http\Controllers;

use App\Models\MealCouponPrintQueue;
use App\Models\Participant;
use App\Models\ParticipantEventRegistration;
use Illuminate\Http\Response;
use Exception;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;
DB::connection()->enableQueryLog();


class ParticipantEventRegistrationController extends PrintJobController
{

    public function initialRegistrations(Request $request)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'reference_code' => 'required|string',
                'registration_date_time' => 'required|date_format:Y-m-d H:i:s',
            ]);

            // Get the latest event
            $event = Event::latest()->first();

            if (!$event) {
                return Helper::APIResponse(0, 'No active event found', HTTP_BAD_REQUEST, []);
            }

            $eventId = $event->event_id;
            $referenceCode = $validatedData['reference_code'];
            $registrationDateTime = $validatedData['registration_date_time'];

            // Get participant data
            $participant = DB::table('event_participants')
                ->where('reference_code', $referenceCode)
                ->first();

            if (!$participant) {
                return Helper::APIResponse(0, 'Participant not found', HTTP_NOT_FOUND, []);
            }

            // Check if already registered
            $existingRegistration = DB::table('i_participant_event_registrations')
                ->where('reference_code', $referenceCode)
                ->where('event_id', $eventId)
                ->first();

            if ($existingRegistration) {
                return Helper::APIResponse(0, 'Participant is already registered', HTTP_SUCCESS, [
                    $existingRegistration
                ]);
            }

            // Insert new registration
            $newRegistrationId = DB::table('i_participant_event_registrations')->insertGetId([
                'reference_code' => $referenceCode,
                'participant_name' => $participant->participant,
                'event_id' => $eventId,
                'registration_date_time' => $registrationDateTime,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
//            dd($newRegistrationId);

            $newRegistration = DB::table('i_participant_event_registrations')->where('id', $newRegistrationId)->first();

            return Helper::APIResponse(1, 'Participant registered successfully', HTTP_SUCCESS, [
                $newRegistration
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $validationError = implode(', ', array_map(function ($error) {
                return implode(', ', $error);
            }, $e->errors()));

            return Helper::APIResponse(0, "Invalid data, {$validationError}", HTTP_FAILED, [
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            return Helper::APIResponse(0, 'An error occurred', HTTP_INTERNAL_SERVER_ERROR, [
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function scanQRCode(Request $request) {
        $validationRules = [
            'qr_code'  => 'required|string|exists:event_participants,reference_code',
            'event_id'  => 'required|string|exists:events,event_id',
        ];


        $validator = Validator::make($request->all(), $validationRules);
        if($validator->fails()) {
            $validation_error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "{$validation_error}",HTTP_UNPROCESSABLE, ['errors'=> $validator->errors()]);
        }

        try{
            $user = $request->user();

            # Check if user is eligible to register participants at the selected event
            $event = Event::where("event_id", $request->event_id)->first();

            $userEvent = $user->attendantEvents()->wherePivot("event_id", $event->id)->first();

            if(empty($userEvent)) {
                throw new Exception("Sorry, you are not permitted to register participants");
            }

            # Check if participant is a participant for this event.
            $participant = Participant::where([["reference_code", $request->qr_code], ["event_id", $event->event_id]])->first();

            if(empty($participant->pending_status != 'approved')) {
                throw new Exception("The scanned person is pending approval");
            }

            if(empty($participant)) {
                throw new Exception("The scanned person is not a participant for {$event->name}");
            }

            if((float)$participant->balance > 0) {
                throw new Exception("The scanned person has not completed payment for {$event->name}");
            }

            $registration = ParticipantEventRegistration::where([
                ["reference_code", $participant->reference_code], ["event_id", $event->event_id]
            ])->first();

            if(!empty($registration)) {
                throw new Exception("Participant already registered at {$registration->registration_date_time}");
            }
        }
        catch (Exception $exception) {
            return Helper::APIResponse(0, $exception->getMessage() , HTTP_FAILED, [
                'error' => $exception->getMessage()
            ]);
        }

        $data = compact('participant');
        return Helper::APIResponse(1, "Participant Data Retrieved", HTTP_SUCCESS, $data);
    }

    public function registerParticipant(Request $request) {
        $validationRules = [
            'qr_code'  => 'required|string|exists:event_participants,reference_code',
            'event_id'  => 'required|string|exists:events,event_id',
            'redeemed_conference_pack'  => 'required|string|in:yes,no',
            'print_coupons'=> 'required|string|in:yes,no',
            "coupons_printer_device"=> 'required|string|in:desktop,mobile',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if($validator->fails()) {
            $validation_error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "{$validation_error}",HTTP_UNPROCESSABLE, ['errors'=> $validator->errors()]);
        }

        DB::beginTransaction();
        try{
            $user = $request->user();

            # Check if user is eligible to register participants at the selected event
            $event = Event::where("event_id", $request->event_id)->first();

            $userEvent = $user->attendantEvents()->wherePivot("event_id", $event->id)->first();

            if(empty($userEvent)) {
                throw new Exception("Sorry, you are not permitted to register participants");
            }

            # Check if participant is a participant for this event.
            $participant = Participant::where([["reference_code", $request->qr_code], ["event_id", $event->event_id]])->first();

            if(empty($participant)) {
                throw new Exception("The scanned person is not a participant for {$event->name}");
            }

            if((float)$participant->balance > 0) {
                throw new Exception("The scanned person has not completed payment for {$event->name}");
            }

            $registration_data = ParticipantEventRegistration::where([
                    ["reference_code", $participant->reference_code], ["event_id", $event->event_id]
            ])->first();

            $now = Helper::Now();
            $printDevice = strtolower(trim($request->coupons_printer_device));
            if(empty($registration_data)) {
                $registration_data = ParticipantEventRegistration::create([
                    "event_id"=> $event->event_id,
                    "reference_code"=> $participant->reference_code,
                    "registration_date_time"=> $now,
                    "conference_pack_redeemed"=> strtolower(trim($request->redeemed_conference_pack)) === "yes" ? 1 :0,
                    "meal_coupons_printer_device"=> $printDevice,
                    "created_by"=> $user->id,
                ]);

                if($printDevice === "desktop" && trim(strtolower($request->print_coupons))  === "yes") {
                    $print_queue = MealCouponPrintQueue::create([
                        "event_id"=> $event->event_id,
                        "participant_reference"=> $participant->reference_code,
                        "participant_name"=> $participant->participant,
                        "total_meal_coupons"=> ((int)$participant->meals + $participant->extra_meals),
                        "created_by"=> $user->id,
                    ]);
                }
            }

            DB::commit();
        }
        catch (Exception $exception) {
            DB::rollBack();
            return Helper::APIResponse(0, $exception->getMessage() , HTTP_FAILED, [
                'error' => $exception->getMessage()
            ]);
        }

        $data = compact('participant', 'registration_data', 'print_queue');
        return Helper::APIResponse(1, "Participant Registered Successfully", HTTP_SUCCESS, $data);
    }


    public function getConferenceHallRegistrations(Request $request)
    {
        $event_id = $request->input('event_id');
        $hotels = DB::table('attendance_registration')
            ->where('event_id', '=', $event_id)
            // ->where('event_id', $event_id)
            ->select('reference_code', 'event_id', 'session_id')
            ->get();

        if ($hotels->isEmpty()) {
            $status = 0;
            $message = "No participants found for the provided event ID.";
            $data = null;
        } else {
            $status = 1;
            $message = "Participants names retrieved successfully.";
            $data = $hotels;
        }

        $response = [
            'status' => $status,
            'data' => $data,
            'msg' => $message
        ];

        return response()->json($response, Response::HTTP_OK);
    }


    public function getInitialRegistrations(Request $request)
    {
        $event_id = $request->input('event_id');

        DB::enableQueryLog(); // Enable query logging

        $registrations = DB::table('i_participant_event_registrations')
            ->where('event_id', '=', $event_id)
            ->select('reference_code', 'registration_date_time', 'conference_pack_redeemed', 'conference_pack_redeem_date_time', 'event_id')
            ->get();

        if ($registrations->count() > 0) {
            $status = 1;
            $message = "Participant names retrieved successfully.";
            $data = $registrations;
        } else {
            $status = 0;
            $message = "No participants found for the provided event ID.";
            $data = null;
        }

        $response = [
            'status' => $status,
            'data' => $data,
            'msg' => $message
        ];

        return response()->json($response, 200);
    }

    public function syncConferenceHallRegistrations(Request $request)
    {
        // echo('hello');
        //var_dump($request->meal_data['total_meals']);
        try {


            // Retrieve the data from the request
            $currentTime = Carbon::now();
            $currentDate = Carbon::now()->toDateString();
            $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user

            // Get the raw JSON data from the request and decode it into an array
            //$rawData = $request->getContent();
            //var_dump(($rawData));
            $rawData = $request->conference_hall_registration_data;
            // echo(gettype($request->meal_data));
            // echo(sizeof($request->meal_data));
            $requestData = json_decode($rawData, true);

            error_log('Raw Data Type:'.$rawData);
            //var_dump($requestData);
            Log::info('Payload'.$rawData);
            // error_log('Decoded Data:'.print_r($mealData));
            // Check if the decoding was successful
            if ($requestData === null) {
                return response()->json([
                    'message' => 'Invalid JSON data'
                ], 400);
            }

            // Check if the meal_data is present
            if (!isset($requestData)) {
                return response()->json([
                    'message' => 'No data provided'
                ], 400);
            }

            // Retrieve the meal_data
            $conference_hall_registration_data = $requestData;

            //var_dump($mealData['meal_data']);

            // Check if the meal_data is empty
            if (empty($conference_hall_registration_data)) {
                return response()->json([
                    'message' => ' data is empty'
                ], 400);
            }

            $data = $conference_hall_registration_data['conference_hall_registration_data'];
            // Loop through the mealData array
            for ($i = 0; $i<(sizeof($data)); $i++)
            {
                // Check if the required keys exist in the data

                //var_dump($data[0]);
                //echo($i);
                if (
                    isset($data[$i]['reference_code']) &&
                    isset($data[$i]['event_id']) &&
                    isset($data[$i]['session_id'])
                    //isset($data[$i]['conference_pack_redeemed'])
                    // isset($data[$i]['conference_pack_redeem_date_time'])
                ) {
                    // Retrieve data from the current iteration
                    $reference_code= $data[$i]['reference_code'];
                    $event_id = $data[$i]['event_id'];
                    $session_id = $data[$i]['session_id'];
                    //$conference_pack_redeemed = $data[$i]['conference_pack_redeemed'];
                    //$conference_pack_redeem_date_time = $data[$i]['conference_pack_redeem_date_time'];
                    // Check if the person is already registered for the session_id
                    $existingRegistration = DB::table('attendance_registration')
                        ->where('reference_code', $reference_code)
                        ->where('event_id', $event_id)
                        ->where('session_id', $session_id)
                        ->first();

                    if ($existingRegistration) {
                        return response()->json([
                            'message' => 'Person is already registered for this session',
                        ], 400);
                    }

                    // Check if the unique_code exists in the meal_coupon table
                    $registration = DB::table('event_participants')
                        ->where('reference_code', $reference_code)
                        ->first();

                    if ($registration) {

                        try {
                            // Insert the data into the meal_scans_per_day table
                            DB::table('attendance_registration')->insert([
                                'reference_code' => $reference_code,
                                'event_id' => $event_id,
                                'session_id' => $session_id,
                                //'conference_pack_redeemed' => $conference_pack_redeemed,
                                //          'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                                //'created_by' => $createdBy,
                            ]);

//                            DB::table('attendance_registration')->upsert(
//                                [
//                                    'reference_code' => $reference_code,
//                                    'event_id' => $event_id,
//                                    'session_id' => $session_id,
//                                    //'conference_pack_redeemed' => $conference_pack_redeemed,
//                                    //          'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
//                                    //'created_by' => $createdBy,
//                                ],
//                                ['reference_code', 'event_id'],
//                                [
//                                    'reference_code' => $reference_code,
//                                    'event_id' => $event_id,
//                                    'session_id' => $session_id,
//                                    //'conference_pack_redeemed' => $conference_pack_redeemed,
//                                    //          'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
//                                    //'created_by' => $createdBy,
//                                ]
//                            );


                            $updated_results = DB::table('attendance_registration')->where('event_id',$event_id)->get();
                            //var_dump($updated_results);
                            /* Log::info('Data synced and inserted successfully');*/
                            /*return response()->json([
                                'message' => 'Data synced and inserted successfully',
                                'records'=>$updated_results
                            ], 200);*/

                        } catch (\Exception $e) {

                            Log::info('Data failed to sync');
                            return response()->json([
                                'message' => $e
                            ], 500);

                        }
                    } else {
                        try {
                            // Insert the data into the meal_scans_per_day_logs table
                            DB::table('attendance_registration_logs')->insert([
                                'reference_code' => $reference_code,
                                'event_id' => $event_id,
                                'session_id' => $session_id,
                                // 'conference_pack_redeemed' => $conference_pack_redeemed,
                                //      'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                                //'created_by' => $createdBy,
                            ]);



                            /*  $updated_results = DB::table('meal_scans_per_day')->where('event_id',$data[0]['event_id'])->get();
                              //var_dump($updated_results);
                              /* Log::info('Data synced and inserted successfully');
                              return response()->json([
                                  'message' => 'Data synced and inserted successfully',
                                  'records'=>$updated_results
                              ], 200);*/

                            /* Log::info('Data synced and inserted successfully');
                            return response()->json([
                                'message' => 'Data synced and inserted successfully'
                            ], 200);
                            */
                        } catch (\Exception $e) {

                            Log::info('Data failed to sync');
                            return response()->json([
                                'message' => $e
                            ], 500);

                        }


                    }
                } else {
                    return response()->json([
                        'message' => 'Required keys are missing in the data'
                    ], 400);
                }
            }


            $updated_results = DB::table('attendance_registration')->where('event_id',$data[0]['event_id'])->get();
            //var_dump($updated_results);
            /* Log::info('Data synced and inserted successfully');*/
            return response()->json([
                'message' => 'Data synced and inserted successfully',
                'records'=>$updated_results
            ], 200);

        } catch (\Exception $e) {
            // Handle any exceptions that occurred during the process
            return response()->json([
                'message' => 'Error occurred while syncing meal coupon data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /*public function syncConferenceHallRegistrations(Request $request)
    {
        // Retrieve the data from the request
        $currentTime = Carbon::now();
        $currentDate = Carbon::now()->toDateString();
        $reference_code = $request->input('reference_code');
        $session_id = $request->input('session_id');
        $event_id = $request->input('event_id');
        // $currentDate = $request->input('date');
        // $currentTime = $request->input('time');

        $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user

        // Check if the unique_code exists in the meal_coupon table
        $coupon = DB::table('event_participants')
            ->where('reference_code', $reference_code)
            ->first();

        if ($coupon) {
            // Insert the data into the meal_coupon table
            DB::table('attendance_registration')->insert([
                'reference_code' => $reference_code,
                'session_id' => $session_id,
                'event_id' => $event_id,
            ]);
        } else {
            // Insert the data into the meal_scans_per_day_logs table
            DB::table('attendance_registration_logs')->insert([
                'reference_code' => $reference_code,
                'session_id' => $session_id,
                'event_id' => $event_id,
            ]);
        }

        return response()->json([
            'message' => 'Data synced and inserted successfully'
        ], 200);
    }*/

//    public function syncConferenceHallRegistrations(Request $request)
//    {
//        // echo('hello');
//        //var_dump($request->meal_data['total_meals']);
//        try {
//
//
//            // Retrieve the data from the request
//            $currentTime = Carbon::now();
//            $currentDate = Carbon::now()->toDateString();
//            $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user
//
//            // Get the raw JSON data from the request and decode it into an array
//            //$rawData = $request->getContent();
//            //var_dump(($rawData));
//            $rawData = $request->conference_hall_registration_data;
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
//                    'message' => 'No data provided'
//                ], 400);
//            }
//
//            // Retrieve the meal_data
//            $conference_hall_registration_data = $requestData;
//
//            // Check if the meal_data is empty
//            if (empty($conference_hall_registration_data)) {
//                return response()->json([
//                    'message' => 'Registration data is empty'
//                ], 400);
//            }
//
//            $i=0;
//
//            // Loop through the mealData array
//            foreach ($conference_hall_registration_data as $data) {
//                // Check if the required keys exist in the data
//
//                //echo($mealData[0]['participant_reference_code']);
//                //echo($i);
//                if (
//                    isset($data[$i]['reference_code']) &&
//                    isset($data[$i]['event_id']) &&
//                    isset($data[$i]['session_id'])
//                    //isset($data[$i]['conference_pack_redeemed'])
//                    // isset($data[$i]['conference_pack_redeem_date_time'])
//                ) {
//                    // Retrieve data from the current iteration
//                    $reference_code= $data[$i]['reference_code'];
//                    $event_id = $data[$i]['event_id'];
//                    $session_id = $data[$i]['session_id'];
//                    //$conference_pack_redeemed = $data[$i]['conference_pack_redeemed'];
//                    //$conference_pack_redeem_date_time = $data[$i]['conference_pack_redeem_date_time'];
//
//                    // Check if the unique_code exists in the meal_coupon table
//                    $registration = DB::table('event_participants')
//                        ->where('reference_code', $reference_code)
//                        ->first();
//
//                    if ($registration) {
//                        try {
//                            // Insert the data into the meal_scans_per_day table
//                            DB::table('attendance_registration')->insert([
//                                'reference_code' => $reference_code,
//                                'event_id' => $event_id,
//                                'session_id' => $session_id,
//                                //'conference_pack_redeemed' => $conference_pack_redeemed,
//                                //          'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
//                                //'created_by' => $createdBy,
//
//                            ]);
//
//                            $updated_results = DB::table('attendance_registration')->where('event_id',$event_id)->get();
//                            //var_dump($updated_results);
//                            /* Log::info('Data synced and inserted successfully');*/
//                            return response()->json([
//                                'message' => 'Data synced and inserted successfully',
//                                'records'=>$updated_results
//                            ], 200);
//
//                        } catch (\Exception $e) {
//
//                            Log::info('Data failed to sync');
//                            return response()->json([
//                                'message' => $e
//                            ], 500);
//
//                        }
//                    } else {
//                        try {
//                            // Insert the data into the meal_scans_per_day_logs table
//                            DB::table('attendance_registration')->insert([
//                                'reference_code' => $reference_code,
//                                'event_id' => $event_id,
//                                'session_id' => $session_id,
//                               // 'conference_pack_redeemed' => $conference_pack_redeemed,
//                                //      'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
//                                //'created_by' => $createdBy,
//                            ]);
//
//
//
//                            $updated_results = DB::table('attendance_registration')->where('event_id',$event_id)->get();
//                            //var_dump($updated_results);
//                            /* Log::info('Data synced and inserted successfully');*/
//                            return response()->json([
//                                'message' => 'Data synced and inserted successfully',
//                                'records'=>$updated_results
//                            ], 200);
//
//                            /* Log::info('Data synced and inserted successfully');
//                            return response()->json([
//                                'message' => 'Data synced and inserted successfully'
//                            ], 200);
//                            */
//                        } catch (\Exception $e) {
//
//                            Log::info('Data failed to sync');
//                            return response()->json([
//                                'message' => $e
//                            ], 500);
//
//                        }
//                    }
//                } else {
//                    return response()->json([
//                        'message' => 'Required keys are missing in the data'
//                    ], 400);
//                }
//                $i++;
//            }
//        } catch (\Exception $e) {
//            // Handle any exceptions that occurred during the process
//            return response()->json([
//                'message' => 'Error occurred while syncing meal coupon data',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//
//    }

    public function syncConferenceHallRegistrationss(Request $request)
    {
        try {
            // Retrieve the data from the request
            $currentTime = Carbon::now();
            $currentDate = Carbon::now()->toDateString();
            $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user

            // Get the array of data from the request
            $registrationData = $request->input('registration_data');

            // Check if the registration_data is empty
            if (empty($registrationData)) {
                return response()->json([
                    'message' => 'No registration data provided'
                ], 400);
            }

            foreach ($registrationData as $data) {
                // Retrieve data from the current iteration
                $referenceCode = $data['reference_code'];
                $sessionId = $data['session_id'];
                $eventId = $data['event_id'];

                // Check if the reference_code exists in the event_participants table
                $participant = DB::table('event_participants')
                    ->where('reference_code', $referenceCode)
                    ->first();

                if ($participant) {
                    // Insert the data into the attendance_registration table
                    DB::table('attendance_registration')->insert([
                        'reference_code' => $referenceCode,
                        'session_id' => $sessionId,
                        'event_id' => $eventId,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $createdBy
                    ]);
                } else {
                    // Insert the data into the attendance_registration_logs table
                    DB::table('attendance_registration_logs')->insert([
                        'reference_code' => $referenceCode,
                        'session_id' => $sessionId,
                        'event_id' => $eventId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            return response()->json([
                'message' => 'Data synced and inserted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occurred during the process
            return response()->json([
                'message' => 'Error occurred while syncing conference hall registrations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function syncInitialRegistrations(Request $request)
    {
        // echo('hello');
        //var_dump($request->meal_data['total_meals']);
        try {


            // Retrieve the data from the request
            $currentTime = Carbon::now();
            $currentDate = Carbon::now()->toDateString();
            $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user

            // Get the raw JSON data from the request and decode it into an array
            //$rawData = $request->getContent();
            //var_dump(($rawData));
            $rawData = $request->initial_registration_data;
            // echo(gettype($request->meal_data));
            // echo(sizeof($request->meal_data));
            $requestData = json_decode($rawData, true);

            error_log('Raw Data Type:'.$rawData);
            //var_dump($requestData);
            Log::info('Payload'.$rawData);
            // error_log('Decoded Data:'.print_r($mealData));
            // Check if the decoding was successful
            if ($requestData === null) {
                return response()->json([
                    'message' => 'Invalid JSON data'
                ], 400);
            }

            // Check if the data is present
            if (!isset($requestData)) {
                return response()->json([
                    'message' => 'No data provided'
                ], 400);
            }

            // Retrieve data
            $initial_registration_data = $requestData;

            //var_dump($mealData['meal_data']);

            // Check if the meal_data is empty
            if (empty($initial_registration_data)) {
                return response()->json([
                    'message' => 'registration data is empty'
                ], 400);
            }

            $data = $initial_registration_data['initial_registration_data'];
            // Loop through the mealData array
            for ($i = 0; $i<(sizeof($data)); $i++)
            {
                // Check if the required keys exist in the data

                //var_dump($data[0]);
                //echo($i);
                if (
                      isset($data[$i]['participant_id']) &&
                    isset($data[$i]['event_id']) &&
                   isset($data[$i]['registration_date_time']) &&
                   isset($data[$i]['conference_pack_redeemed'])
                ) {
                    // Retrieve data from the current iteration
                    $participant_id = $data[$i]['participant_id'];
                   $event_id = $data[$i]['event_id'];
                   $registration_date_time = $data[$i]['registration_date_time'];
                   $conference_pack_redeemed = $data[$i]['conference_pack_redeemed'];

                    $participants = DB::table('event_participants')
                        ->where('reference_code', $participant_id)
                        ->first();

                    if ($participants) {

                        try {

                            DB::table('i_participant_event_registrations')->insert([
                                'reference_code' => $participant_id,
                            'event_id' => $event_id,
                             'registration_date_time' => $registration_date_time,
                                'conference_pack_redeemed' => $conference_pack_redeemed,
                            //'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                               //'created_by' => $createdBy,

                            ]);
                             DB::table('i_participant_event_registrations')->upsert(
                                [
                                   'reference_code' => $participant_id,
                                    'event_id' => $event_id,
                                    'registration_date_time' => $registration_date_time,
                                    'conference_pack_redeemed' => $conference_pack_redeemed,
                                    //'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                                    //'created_by' => $createdBy,
                                ],
                                ['reference_code', 'event_id'],
                                [
                                    'reference_code' => $participant_id,
                                    'event_id' => $event_id,
                                    'registration_date_time' => $registration_date_time,
                                    'conference_pack_redeemed' => $conference_pack_redeemed,
                                    //'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                                    //'created_by' => $createdBy,
                                ]
                            );
                            $updated_results = DB::table('i_participant_event_registrations')->where('event_id',$event_id)->get();
                            //var_dump($updated_results);
                            /* Log::info('Data synced and inserted successfully');*/
                            /*return response()->json([
                                'message' => 'Data synced and inserted successfully',
                                'records'=>$updated_results
                            ], 200);*/

                        } catch (\Exception $e) {

                            Log::info('Data failed to sync');
                            return response()->json([
                                'message' => $e
                            ], 500);

                        }
                    } else {
                        try {
                            // Insert the data
                            DB::table('i_participant_event_registrations_logs')->insert([
                                'reference_code' => $participant_id,
                              'event_id' => $event_id,
                               'registration_date_time' => $registration_date_time,
                               'conference_pack_redeemed' => $conference_pack_redeemed,
                       //      'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                               //'created_by' => $createdBy,
                            ]);



                            /*  $updated_results = DB::table('meal_scans_per_day')->where('event_id',$data[0]['event_id'])->get();
                              //var_dump($updated_results);
                              /* Log::info('Data synced and inserted successfully');
                              return response()->json([
                                  'message' => 'Data synced and inserted successfully',
                                  'records'=>$updated_results
                              ], 200);*/

                            /* Log::info('Data synced and inserted successfully');
                            return response()->json([
                                'message' => 'Data synced and inserted successfully'
                            ], 200);
                            */
                        } catch (\Exception $e) {

                            Log::info('Data failed to sync');
                            return response()->json([
                                'message' => $e
                            ], 500);

                        }


                    }
                } else {
                    return response()->json([
                        'message' => 'Required keys are missing in the data'
                    ], 400);
                }
            }


            $updated_results = DB::table('i_participant_event_registrations')->where('event_id',$data[0]['event_id'])->get();
            //var_dump($updated_results);
            /* Log::info('Data synced and inserted successfully');*/
//            return Helper::APIResponse(1, 'Data synced and inserted successfully', Response::HTTP_OK, [
//                              'records' => $updated_results
//                           ]);
            return response()->json([
                'message' => 'Data synced and inserted successfully',
                'records'=>$updated_results
            ], 200);

        } catch (\Exception $e) {
            // Handle any exceptions that occurred during the process
            return response()->json([
                'message' => 'Error occurred while syncing meal coupon data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


//    public function syncInitialRegistrations(Request $request)
//    {
//        // echo('hello');
//        //var_dump($request->meal_data['total_meals']);
//        try {
//
//
//            // Retrieve the data from the request
//            $currentTime = Carbon::now();
//            $currentDate = Carbon::now()->toDateString();
//            $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user
//
//            // Get the raw JSON data from the request and decode it into an array
//            //$rawData = $request->getContent();
//            //var_dump(($rawData));
//            $rawData = $request->initial_registration_data;
//            // echo(gettype($request->meal_data));
//            // echo(sizeof($request->meal_data));
//            $requestData = json_decode($rawData, true);
//
//            error_log('Raw Data Type:'.$rawData);
//            //var_dump($requestData);
//            Log::info('Payload'.$rawData);
//            // error_log('Decoded Data:'.print_r($mealData));
//
//            // Check if the decoding was successful
//            if ($requestData === null || empty($requestData)) {
//                $updated_results = DB::table('i_participant_event_registrations')->where('event_id',$request->event_id)->get();
//
//                return Helper::APIResponse(1, 'Updated Data Retrieved', Response::HTTP_OK, [
//                    'records' => $updated_results
//                ]);
//
//
////                return response()->json([
////                    'message' => 'Updated Data Retrieved',
////                    'records'=>$updated_results,
////                ]);
//            }
//
////            // Check if the meal_data is present
////            if (!isset($requestData)) {
////                return response()->json([
////                    'message' => 'No particioant data provided'
////                ], 400);
////            }
//
//            // Retrieve the meal_data
//            $initial_registration_data = $requestData;
//
//            $i=0;
//
//            // Loop through the mealData array
//            foreach ($initial_registration_data as $data) {
//                // Check if the required keys exist in the data
//
//                //echo($mealData[0]['participant_reference_code']);
//                //echo($i);
//                if (
//                    isset($data[$i]['participant_id']) &&
//                    isset($data[$i]['event_id']) &&
//                    isset($data[$i]['registration_date_time']) &&
//                    isset($data[$i]['conference_pack_redeemed'])
//                   // isset($data[$i]['conference_pack_redeem_date_time'])
//                ) {
//                    // Retrieve data from the current iteration
//                    $participant_id = $data[$i]['participant_id'];
//                    $event_id = $data[$i]['event_id'];
//                    $registration_date_time = $data[$i]['registration_date_time'];
//                    $conference_pack_redeemed = $data[$i]['conference_pack_redeemed'];
//                    //$conference_pack_redeem_date_time = $data[$i]['conference_pack_redeem_date_time'];
//
//
//                    $participants = DB::table('event_participants')
//                        ->where('reference_code', $participant_id)
//                        ->first();
//
//                    if ($participants) {
//                        try {
////                            // Insert the data into the meal_scans_per_day table
////                            DB::table('i_participant_event_registrations')->insert([
////                                'participant_id' => $participant_id,
////                                'event_id' => $event_id,
////                                'registration_date_time' => $registration_date_time,
////                                'conference_pack_redeemed' => $conference_pack_redeemed,
////                      //          'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
////                                //'created_by' => $createdBy,
////
////                            ]);
//
//                            DB::table('i_participant_event_registrations')->upsert(
//                                [
//                                    'participant_id' => $participant_id,
//                                    'event_id' => $event_id,
//                                    'registration_date_time' => $registration_date_time,
//                                    'conference_pack_redeemed' => $conference_pack_redeemed,
//                                    //'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
//                                    //'created_by' => $createdBy,
//                                ],
//                                ['participant_id', 'event_id'],
//                                [
//                                    'participant_id' => $participant_id,
//                                    'event_id' => $event_id,
//                                    'registration_date_time' => $registration_date_time,
//                                    'conference_pack_redeemed' => $conference_pack_redeemed,
//                                    //'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
//                                    //'created_by' => $createdBy,
//                                ]
//                            );
//                            $updated_results = DB::table('i_participant_event_registrations')->where('event_id',$event_id)->get();
//                            //var_dump($updated_results);
//                            /* Log::info('Data synced and inserted successfully');*/
//
//                            return Helper::APIResponse(1, 'Data synced and inserted successfully', Response::HTTP_OK, [
//                                'records' => $updated_results
//                            ]);
//
//
////                            return response()->json([
////                                'msg' => 'Data synced and inserted successfully',
////                                'records'=>$updated_results
////                            ], 200);
//
//                        } catch (\Exception $e) {
//
//                            Log::info('Data failed to sync');
//                            return response()->json([
//                                'message' => $e
//                            ], 500);
//
//                        }
//                    } else {
//                        try {
//                            // Insert the data into the meal_scans_per_day_logs table
//                            DB::table('i_participant_event_registrations_logs')->insert([
//                                'participant_id' => $participant_id,
//                                'event_id' => $event_id,
//                                'registration_date_time' => $registration_date_time,
//                                'conference_pack_redeemed' => $conference_pack_redeemed,
//                        //      'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
//                                //'created_by' => $createdBy,
//                            ]);
//
//
//
//                            $updated_results = DB::table('i_participant_event_registrations_logs')->where('event_id',$event_id)->get();
//                            //var_dump($updated_results);
//                            /* Log::info('Data synced and inserted successfully');*/
//                            return response()->json([
//                                'message' => 'Data synced and inserted successfully',
//                                'records'=>$updated_results
//                            ], 200);
//
//                            /* Log::info('Data synced and inserted successfully');
//                            return response()->json([
//                                'message' => 'Data synced and inserted successfully'
//                            ], 200);
//                            */
//                        } catch (\Exception $e) {
//
//                            Log::info('Data failed to sync');
//                            return response()->json([
//                                'message' => $e
//                            ], 500);
//
//                        }
//                    }
//                } else {
//
//                    $updated_results = DB::table('i_participant_event_registrations')->where('event_id',$request->event_id)->get();
//
//                    return response()->json([
//                        'message' => 'Updated Data Retrieved',
//                        'records'=>$updated_results,
//                    ]);
////                    return response()->json([
////
////                        'message' => 'Required keys are missing in the data'
////                    ], 400);
//                }
//                $i++;
//            }
//        } catch (\Exception $e) {
//            // Handle any exceptions that occurred during the process
//            return response()->json([
//                'message' => 'Error occurred while syncing meal coupon data',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }


    public function syncInitialRegistrationss(Request $request)
    {
        try {
            // Retrieve the data from the request
            $currentTime = Carbon::now();
            $currentDate = Carbon::now()->toDateString();
            $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user
            $rawData = $request->registration_data;
            // Get the array of data from the request
            //$registrationData = $request->input('registration_data');

            // Check if the registration_data is empty
            if (empty($registrationData)) {
                return response()->json([
                    'message' => 'No registration data provided'
                ], 400);
            }

            foreach ($registrationData as $data) {
                // Retrieve data from the current iteration
                $participantId = $data['reference_code'];
                $conferencePackRedeemed = $data['conference_pack_redeemed'];
                $event_id = $data['event_id'];
                $conferencePackRedeemDateTime = $data['conference_pack_redeem_date_time'];

                // Check if the reference_code exists in the event_participants table
                $participant = DB::table('event_participants')
                    ->where('reference_code', $participantId)
                    ->first();

                if ($participant) {
                    // Insert the data into the i_participant_event_registrations table
                    DB::table('i_participant_event_registrations')->insert([
                        'reference_code' => $participantId,
                        'conference_pack_redeemed' => $conferencePackRedeemed,
                        'conference_pack_redeem_date_time' => $conferencePackRedeemDateTime,
                        'event_id' => $event_id,
                        'created_by' => $createdBy,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Insert the data into the i_participant_event_registrations_logs table
                    DB::table('i_participant_event_registrations_logs')->insert([
                        'reference_code' => $participantId,
                        'conference_pack_redeemed' => $conferencePackRedeemed,
                        'conference_pack_redeem_date_time' => $conferencePackRedeemDateTime,
                        'event_id' => $event_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'message' => 'Data synced and inserted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occurred during the process
            return response()->json([
                'message' => 'Error occurred while syncing initial registrations data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /*public function syncInitialRegistrations(Request $request)
    {
        // Retrieve the data from the request
        $currentTime = Carbon::now();
        $currentDate = Carbon::now()->toDateString();
        $participant_id = $request->input('reference_code');
        $conference_pack_redeemed = $request->input('conference_pack_redeemed');
        $event_id = $request->input('event_id');
         $conference_pack_redeem_date_time = $request->input('conference_pack_redeem_date_time');
        // $currentTime = $request->input('time');

        $createdBy = $request->user()->id; // Assuming you have authentication and retrieve the user

        // Check if the unique_code exists in the meal_coupon table
        $coupon = DB::table('event_participants')
            ->where('reference_code', $participant_id)
            ->first();

        if ($coupon) {
            // Insert the data into the meal_coupon table
            DB::table('i_participant_event_registrations')->insert([
                'participant_id' => $participant_id,
                'conference_pack_redeemed' => $conference_pack_redeemed,
                'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                'event_id' => $event_id,
                'created_by' => $createdBy,
            ]);
        } else {
            // Insert the data into the meal_scans_per_day_logs table
            DB::table('i_participant_event_registrations_logs')->insert([
                'participant_id' => $participant_id,
                'conference_pack_redeemed' => $conference_pack_redeemed,
                'conference_pack_redeem_date_time' => $conference_pack_redeem_date_time,
                'event_id' => $event_id,
            ]);
        }

        return response()->json([
            'message' => 'Data synced and inserted successfully'
        ], 200);
    }*/
}
