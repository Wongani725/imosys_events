<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingDeclined extends Mailable
{
    use Queueable, SerializesModels;

    public $booker;

    public function __construct($booker)
    {
        $this->booker = $booker;
    }

    public function build()
    {
        return $this->subject('Your Booking Has Been Declined')
            ->view('emails.booking_declined');
    }
}
