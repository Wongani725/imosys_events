<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeletionRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $member;

    public function __construct($member)
    {
        $this->member = $member;
    }

    public function build()
    {
        return $this->subject('Account Deletion Requested')
            ->view('emails.account_deletion_confirmation')
            ->with([
                'name' => $this->member->participant,
            ]);
    }

}
