<?php
//
//namespace App\Mail;
//
//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Mail\Mailable;
//use Illuminate\Queue\SerializesModels;
//
//class EmailCertificates extends Mailable
//{
//    use Queueable, SerializesModels;
//
//    public $data;
//
//    /**
//     * Create a new message instance.
//     *
//     * @param array $data Participant data
//     */
//    public function __construct(array $data)
//    {
//        $this->data = $data;
//    }
//
//    /**
//     * Build the message.
//     *
//     * @return $this
//     */
//    public function build()
//    {
//        return $this->subject('MEI Certificate')
//            ->view('emails.email_certificates')
//            ->with('data', $this->data);
//    }
//}
//
