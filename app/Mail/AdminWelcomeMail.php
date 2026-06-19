<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $password;
    public $role;
    public $loginUrl;

    public function __construct($name, $email, $password, $role)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->loginUrl = env('APP_URL', url('/login'));
    }

    public function build()
    {
        return $this->subject('Welcome to IIA Malawi – Admin Account Created')
                    ->view('emails.admin_welcome');
    }
}
