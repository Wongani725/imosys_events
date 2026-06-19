<?php
//
//namespace App\Jobs;
//
//use App\Mail\AutoMail;
//use App\Mail\Evaluation;
//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Foundation\Bus\Dispatchable;
//use Illuminate\Queue\InteractsWithQueue;
//use Illuminate\Queue\SerializesModels;
//use Illuminate\Support\Facades\Mail;
//use App\Mail\ParticipantNameTagMail;
//use Illuminate\Support\Facades\Log;
//
//class sendProgrammeEmails implements ShouldQueue
//{
//    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
//    protected $participant;
//    public $timeout = 36800;
//
//
//    /**
//     * Create a new job instance.
//     *
//     * @param  array  $participant
//     * @return void
//     */
//
//    //public $tries = 2; // Set the maximum number of attempts
//    //public $timeout = 3600; // Set the maximum execution time (in seconds)
//
//    public function __construct($participant)
//    {
//        $this->participant = $participant;
//    }
//    /**
//     * Execute the job.
//     *
//     * @return void
//     */
//    public function handle()
//    {
//        $data = [
////            'Name' => 'Sarah',
////            'Reference_Code' => 'icam8',
////          'Event_Id' => '8',
//            'participant' => $this->participant->participant,
//         //   'Reference_Code' => $this->participant->reference_code,
//            'event_id' => $this->participant->event_id,
//        ];
//        if ($this->participant->balance <= 0) {
//            try {
////                set_time_limit(10000);
//               // Mail::to($this->participant->email_address)->send(new Evaluation($data));
//                $filePath = public_path('background_images/icam_programme.png');
//                Mail::to($this->participant->email_address)->send(new AutoMail($data, $filePath));
//                //Mail::to("sarahleemsosa@gmail.com")->send(new Evaluation($data));
//            } catch (\Exception $exception) {
//                // Handle email sending exception
//                // You can log the exception or perform any necessary action
//                Log::error('Error sending email to ' . $this->participant->email_address . ': ' . $exception->getMessage());
//            }
//        }
//    }
//    public function hasFailed( $exception)
//    {
//        // Log the failure or perform any other actions
//        Log::error("Failed to send email to " . $exception);
//    }
//}
//
//
////foreach ($participants as $participant) {
////$participantData = [
////'Event_Id' => $event_id,
////'Name' => $participant->participant,
////'Reference_Code' => $participant->reference_code,
////'Email' => $participant->email_address,
////];
