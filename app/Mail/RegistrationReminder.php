<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationReminder extends Mailable
{
    public $member;
    public $event_id;

    public function __construct($member, $event_id)
    {
        $this->member   = $member;
        $this->event_id = $event_id;
    }

    public function build()
    {
        return $this->subject('Reminder to Register for Upcoming Conference')
            ->view('emails.registration_reminder');
    }
}
