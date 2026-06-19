<?php

namespace App\Jobs;

use App\Mail\Evaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class sendEvaluationEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $participant;
    public $timeout = 36800;

    public function __construct($participant)
    {
        $this->participant = $participant;
    }

    public function handle()
    {
        $data = [
//            'Name' => "Wongani",
//            'Reference_Code' => "IIM_EXEC_RET_130",
//            'Event_Id' => $this->participant->event_id,

//            'Event_Id' => "IIM-LK-2024",


            'Name' => $this->participant->participant,
            'Reference_Code' => $this->participant->reference_code,
            'Event_Id' => "MLS-LK-2025",
        ];

        if ($this->participant->balance <= 0) {
            try {
                Mail::to($this->participant->email_address)->send(new Evaluation($data));
//                Mail::to("wongani087@gmail.com")->send(new Evaluation($data));
            } catch (\Exception $exception) {
                Log::error('Error sending email to ' . $this->participant->email_address . ': ' . $exception->getMessage());
            }
        }
    }

    public function failed(\Exception $exception)
    {
        Log::error("Failed to send email to " . $this->participant->email_address . ": " . $exception->getMessage());
    }
}
