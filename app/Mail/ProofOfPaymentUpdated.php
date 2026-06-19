<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProofOfPaymentUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Your Proof of Payment Has Been Received')
            ->view('emails.proof_of_payment_updated')
            ->with([
                'booking' => $this->booking,
            ]);
    }
}
