<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $booker;
    public $nameTagLink;
    public $eventNames;

    public function __construct($booker, $nameTagLink, $eventNames = null)
    {
        $this->booker = $booker;
        $this->nameTagLink = $nameTagLink;
        $this->eventNames = $eventNames;
    }

    public function build()
    {
        $events = $this->eventNames ?? $this->booker->event->event_name ?? $this->booker->event_id;
        return $this->subject('Your Booking Has Been Approved — ' . $events)
            ->view('emails.booking_approved')
            ->with([
                'booker' => $this->booker,
                'nameTagLink' => $this->nameTagLink,
                'eventNames' => $events,
            ]);
    }
}
