<?php
namespace App\Jobs;

use App\Mail\PaymentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booker;

    public function __construct($booker)
    {
        $this->booker = $booker;
    }

    public function handle()
    {
        Mail::to($this->booker->email)
            ->send(new PaymentReminder($this->booker));
    }
}
