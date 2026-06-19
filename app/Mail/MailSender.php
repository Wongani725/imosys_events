<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailSender extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $salutation, $message, $view)
    {
        $this->subject = $subject;
        $this->salutation = $salutation;
        $this->message = $message;
        $this->view = $view;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("{$this->subject}")
            ->markdown("{$this->view}", [
                "salutation"=> $this->salutation,
                "message"=> $this->message,
            ]);
    }
}
