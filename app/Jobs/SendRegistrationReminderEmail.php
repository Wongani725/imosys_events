<?php
namespace App\Jobs;

use App\Mail\RegistrationReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRegistrationReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $member;
    protected $event_id;

    public function __construct($member, $event_id)
    {
        $this->member   = $member;
        $this->event_id = $event_id;
    }

    public function handle()
    {
        Mail::to($this->member->email_address)
            ->send(new RegistrationReminder($this->member, $this->event_id));
    }
}
