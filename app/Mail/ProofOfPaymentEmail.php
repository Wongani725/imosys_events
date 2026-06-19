<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProofOfPaymentEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $attachmentPath; // NEW

    public function __construct($booking, $attachmentPath = null)
    {
        $this->booking = $booking;
        $this->attachmentPath = $attachmentPath; // NEW
    }

    public function build()
    {
        $email = $this->subject('User Proof of Payment')
            ->view('emails.proof_of_payment_email')
            ->with(['booking' => $this->booking]);

        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $email->attach($this->attachmentPath); // NEW
        }

        return $email;
    }
}
