<?php
namespace App\Mail;

use App\Models\Bookers;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booker;

    public function __construct(Bookers $booker)
    {
        $this->booker = $booker;
    }

    public function build()
    {
        return $this->subject('Payment Reminder for Your Booking')
            ->view('emails.reminder');
    }
}
