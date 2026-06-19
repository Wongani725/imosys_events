<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminder extends Mailable
{
    public $booker;

    public function __construct($booker)
    {
        $this->booker = $booker;
    }

    public function build()
    {
        return $this->subject('Payment Reminder – Conference Registration')
            ->view('emails.payment_reminder');
    }
}
