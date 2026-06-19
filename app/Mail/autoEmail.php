<?php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ParticipantNameTagMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $filePath;

    /**
     * Create a new message instance.
     *
     * @param array  $data Participant data
     * @param string $filePath File path of the uploaded program
     */
    public function __construct(array $data, string $filePath)
    {
        $this->data = $data;
        $this->filePath = $filePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Participant Name Tag')
            ->view('email_programm')
            ->with('data', $this->data)
            ->attach($this->filePath); // Attach the program file
    }
}



?>
