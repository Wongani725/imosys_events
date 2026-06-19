<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkBookingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $message;
    public $recipientName;
    public $batchRef;

    public function __construct($title, $message, $recipientName, $batchRef)
    {
        $this->title = $title;
        $this->message = $message;
        $this->recipientName = $recipientName;
        $this->batchRef = $batchRef;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->markdown('emails.bulk_booking_notification')
            ->with([
                'title' => $this->title,
                'message' => $this->message,
                'recipientName' => $this->recipientName,
                'batchRef' => $this->batchRef,
            ]);
    }
}
