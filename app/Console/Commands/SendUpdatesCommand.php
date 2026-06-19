<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Log;
use App\Mail\ParticipantNameTagMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Monolog\Logger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;


use Illuminate\Support\Facades\Artisan;

class SendUpdatesCommand extends Command
{

    protected $signature = 'send:updates';
    protected $description = 'Send updates to users based on database changes';

    public function sendEmails()
    {
        while (true) {
            $newJobsCount = DB::table('jobs')->count();

            if ($newJobsCount > 0) {
                Artisan::call('queue:work', ['--daemon' => true]);
            } else {
                $this->info('No new jobs to process.');
            }
        }
    }
    public function handle()
    {
        Log::info('SendUpdatesCommand started');

        // Retrieve all the updated records from the "events" table
        $participantUpdates = DB::table('event_participants')->where('updated_at', '>', now()->subMinutes(30))->get();

        $deletedParticipantUpdates = DB::table('deleted_event_participants')->where('created_at', '>', now()->subMinutes(30))->get();

        // Retrieve all the updated records from the "events" table
        $initialRegistrationUpdates = DB::table('i_participant_event_registrations')->where('updated_at', '>', now()->subMinutes(30))->get();

        $deletedInitialRegistrationUpdates = DB::table('deletedInitialRegistrationUpdates')->where('created_at', '>', now()->subMinutes(30))->get();
        // Retrieve all the updated records from the "event_participants" table

        $attendanceRegistrationUpdates = DB::table('attendance_registration')->where('updated_at', '>', now()->subMinutes(15))->get();
        $deletedAttendanceRegistrationUpdates = DB::table('deleted_attendance_registration')->where('created_at', '>', now()->subMinutes(30))->get();

        // Retrieve all the updated records from the "meal_scans_per_day" table
        $mealUpdates = DB::table('meal_coupon')->where('updated_at', '>', now()->subMinutes(15))->get();
        $deletedMealCoupons = DB::table('deleted_meal_coupons')->where('created_at', '>', now()->subMinutes(30))->get();

        // Retrieve all the updated records from the "meal_scans_per_day" table
        $mealScansPerDayUpdates = DB::table('meal_scans_per_day')->where('updated_at', '>', now()->subMinutes(15))->get();
        $deletedMealScansPerDay = DB::table('deleted_meal_scans_per_day')->where('created_at', '>', now()->subMinutes(30))->get();


        // Retrieve all the updated records from the "meal_scans_per_day" table
        $event_sessions = DB::table('event_sessions')->where('updated_at', '>', now()->subMinutes(15))->get();
        $deletedEventSessions = DB::table('deleted_event_sessions')->where('created_at', '>', now()->subMinutes(30))->get();


        // Get the unique Firebase tokens of the users
        $firebaseTokens = DB::table('users')->distinct()->pluck('firebase_token')->toArray();

        foreach ($deletedEventSessions as $deletedEventSession) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $deletedEventSession->event_id,
                'session_id' => $deletedEventSession->session_id,
                'description' => $deletedEventSession->description,
            ];
            Log::info('An event session has been deleted ' );

            // Define the title and body for event updates
            $title = 'Deleted event session Update';
            $body = 'An event session has been deleted';
            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }


        foreach ($deletedMealScansPerDay as $deletedMealScansPerDa) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $deletedMealScansPerDa->event_id,
                'participant_reference_code' => $deletedMealScansPerDa->participant_reference_code,
                'unique_code' => $deletedMealScansPerDa->unique_code,
            ];
            Log::info('Sending update for deleted meal scans per day ' );

            // Define the title and body for event updates
            $title = 'Deleted meal coupons per day Update';
            $body = 'A daily meal coupon has been deleted';
            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }

        foreach ($deletedMealCoupons as $deletedMealCoupon) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $deletedMealCoupon->event_id,
                'participant_reference_code' => $deletedMealCoupon->participant_reference_code,
                'unique_code' => $deletedMealCoupon->unique_code,
            ];
            Log::info('Sending update for deleted meal coupon: ' );

            // Define the title and body for event updates
            $title = 'Deleted meal coupons Update';
            $body = 'A meal coupon has been deleted';
            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }


        foreach ($deletedAttendanceRegistrationUpdates as $deletedAttendanceRegistrationUpdate) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $deletedAttendanceRegistrationUpdate->event_id,
                'reference_code' => $deletedAttendanceRegistrationUpdate->reference_code,
                'session_id' => $deletedAttendanceRegistrationUpdate->session_id,
            ];
            Log::info('Sending update for deleted conference hall registrations: ' );

            // Define the title and body for event updates
            $title = 'Deleted conference hall registrations Update';
            $body = 'A participant has been deleted for a session: ';
            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }


        foreach ($deletedInitialRegistrationUpdates as $deletedInitialRegistrationUpdate) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $deletedInitialRegistrationUpdates->event_id,
                'participant_id' => $deletedInitialRegistrationUpdates->participant_id,
            ];
            Log::info('Sending update for deleted initial registrations: ' );

            // Define the title and body for event updates
            $title = 'Deleted Initial registrations Update';
            $body = 'A participant has been deleted: ';
            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }

        foreach ($deletedParticipantUpdates as $deletedParticipantUpdate) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $deletedParticipantUpdate->event_id,
                'reference_code' => $deletedParticipantUpdate->reference_code,
                'participant' => $deletedParticipantUpdate->participant,

            ];
            Log::info('Sending update for deleted participants: ' );

            // Define the title and body for event updates
            $title = 'Deleted participant Update';
            $body = 'A participant has been deleted: ';
            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }

        foreach ($event_sessions as $event_session) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $event_session->event_id,
                'session_date' => $event_session->session_date,
                'start_time' => $event_session->start_time,
                'end_time' => $event_session->end_time,
                'description' => $event_session->description,
            ];
            Log::info('Sending update for event session: ' );

            // Define the title and body for event updates
            $title = 'Session Update';
            $body = 'A session has been created: ';

            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }

        foreach ($participantUpdates as $participantUpdate) {
            // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $participantUpdate->event_id,
                'reference_code' => $participantUpdate->reference_code,
                'participant' => $participantUpdate->participant,
                'email_address' => $participantUpdate->email_address,
                'meals' => $participantUpdate->meals,
                'extra_meals' => $participantUpdate->extra_meals,
                'company_name' => $participantUpdate->company_name,
                'position' => $participantUpdate->position,
                'gender' => $participantUpdate->gender,
                'balance' => $participantUpdate->balance,
                // Add more fields as needed
            ];

            Log::info('Sending update for event: ' . $participantUpdate->participant);
            // Define the title and body for event updates
            $title = 'Participant Update';
            $body = 'A participant has been updated: ' . $participantUpdate->participant;

            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }

        foreach ($initialRegistrationUpdates as $initialRegistrationUpdate) {
           // $eventName = $initialRegistrationUpdate->event_name;
            // Prepare the event update data (modify this according to your table structure)
            $data = [
                'event_id' => $initialRegistrationUpdate->event_id,
                'participant_id' => $initialRegistrationUpdate->participant_id,
                'registration_date_time' => $initialRegistrationUpdate->registration_date_time,
                'conference_pack_redeemed' => $initialRegistrationUpdate->conference_pack_redeemed,
                // Add more fields as needed
            ];


            // Define the title and body for event updates
            $title = 'Initial Registration Update';
            $body = 'A participant has been updated: ' . $initialRegistrationUpdates->participant_id;

            // Send the event update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }
        Log::info('SendUpdatesCommand completed');

        foreach ($attendanceRegistrationUpdates as $attendanceRegistrationUpdate) {
            // Prepare the participant update data (modify this according to your table structure)
            $data = [

                'reference_code' => $attendanceRegistrationUpdate->reference_code,
                'event_id' => $attendanceRegistrationUpdate->event_id,
                'session_id' => $attendanceRegistrationUpdate->session_id,

                // Add more fields as needed
            ];
            $title = 'Conference Hall Attendance Update';
            $body = 'A participant has been updated: ' . $attendanceRegistrationUpdate->reference_code;

            Log::info('Conference hall attendance: ' . $attendanceRegistrationUpdate->reference_code);
            // Send the participant update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }

        foreach ($mealUpdates as $mealUpdate) {
            // Prepare the meal update data (modify this according to your table structure)
            $data = [
                'participant_reference_code' => $mealUpdate->participant_reference_code,
                'unique_code' => $mealUpdate->unique_code,
                'event_id' => $mealUpdate->event_id,
                'total_meals' => $mealUpdate->total_meals,
                // Add more fields as needed
            ];

            // Define the title and body for meal updates
            $title = 'Meal Coupons Update';
            $body = 'Meal coupons have been updated' . $mealUpdate->participant_reference_code;

            // Send the meal update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);

        }

        foreach ($mealScansPerDayUpdates as $mealScansPerDayUpdate) {
            // Prepare the meal update data (modify this according to your table structure)
            $data = [
                'event_id' => $mealScansPerDayUpdate->event_id,
                'participant_reference_code' => $mealScansPerDayUpdate->participant_reference_code,
                'unique_code' => $mealScansPerDayUpdate->unique_code,
                'day' => $mealScansPerDayUpdates->day,
                'date' => $mealScansPerDayUpdates->date,
                'time' => $mealScansPerDayUpdates->time,
                'redeemed' => $mealScansPerDayUpdates->redeemed,
                'hotel_name' => $mealScansPerDayUpdates->hotel_name,
                // Add more fields as needed
            ];
            // Define the title and body for meal updates
            $title = 'Meal Scans per day Update';
            $body = 'A meal has been updated' . $mealUpdate->hotel_name;
            // Send the meal update to each user
            $this->sendUpdateToUsers($firebaseTokens, $data, $title, $body);
        }
    }

    private function sendUpdateToUsers(array $firebaseTokens, array $data, string $title, string $body)
    {

//        $serverKey = env('AAAAPAU4Sp0:APA91bH9LpDQXndt4pTT9boH8lTmEIlj5fddxIamJ2jtcTGvtnxHJW_-Oq-55XpzpTxhbENL-lVrp1vZS8TI3JpTxIKFyVjc8SlFe9QUGbgQfSDYTbc6FLBnTlA00j7sOyaFy4cd2rWd'); // Get the server key from .env file
        $serverKey = env('FIREBASE_SERVER_KEY'); // Assuming 'FIREBASE_SERVER_KEY' is the key in your .env file


        foreach ($firebaseTokens as $token) {
            // Send the update using Firebase Cloud Messaging (FCM)
            $response = Http::withHeaders([
                'Authorization' => "Bearer $serverKey",
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'data' => $data,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ]);

//        foreach ($firebaseTokens as $token) {
//            // Send the update using Firebase Cloud Messaging (FCM)
//            $response = Http::withHeaders([
//                'Authorization' => "Bearer $serverKey",
//            ])->post('https://fcm.googleapis.com/fcm/send', [
//                'to' => $token,
//                'data' => $data,
//                'title' => $title,
//                'body' => $body,
//            ]);

            $responseStatus = $response->json('success'); // Replace 'success' with the appropriate key in the response
            if ($responseStatus === 1) {
                // Push notification sent successfully
                // You can log this or perform any other action
                Log::info('Push notification sent successfully to token: ' . $token);


            } else {
                // Push notification failed to send
                // You can log this or perform any other action
                Log::error('Failed to send push notification to token: ' . $token);

            }
        }
    }

//    private function sendUpdateToUsers(array $firebaseTokens, array $data)
//    {
//        $serverKey = "AAAAPAU4Sp0:APA91bH9LpDQXndt4pTT9boH8lTmEIlj5fddxIamJ2jtcTGvtnxHJW_-Oq-55XpzpTxhbENL-lVrp1vZS8TI3JpTxIKFyVjc8SlFe9QUGbgQfSDYTbc6FLBnTlA00j7sOyaFy4cd2rWd";
//        foreach ($firebaseTokens as $token) {
//           //  Send the update using Firebase Cloud Messaging (FCM)
//            $response = Http::withHeaders([
//                'Authorization' => "Bearer $serverKey",
//            ])->post('https://fcm.googleapis.com/fcm/send', [
//                'to' => $token,
//                'data' => $data,
//            ]);
//
////            $data = [
////                'data'=>$data,
////                'participant' => "Sarah",
////                'reference_code' => $token,
////                'qrcode_path' => $token,
////                'event_id' => "dhdhd",
////                // Add other participant data as needed
////            ];
////            Log::info('Sending update for event: ' . 'data');
//
//
////            Mail::to('sarahleemsosa@gmail.com')->send(new ParticipantNameTagMail($data));
//
//            // Handle the response as per your requirements
//            // For example, you can check if the request was successful
////            if ($response->successful()) {
////                $this->info('Update sent to user with token: ' . $token);
////            } else {
////                $this->error('Failed to send update to user with token: ' . $token);
////            }
//        }
//    }
}
