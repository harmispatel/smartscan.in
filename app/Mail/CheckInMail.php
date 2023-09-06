<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckInMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $firstname = (isset($this->data['firstname'])) ? $this->data['firstname'] : '';
        $lastname = (isset($this->data['lastname'])) ? $this->data['lastname'] : '';
        $full_name = "$firstname $lastname";
        return $this->subject($this->data['subject'])->from($this->data['from_mail'], $full_name)->view('mails.check-in-mail');
    }
}
