<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientSupport extends Mailable
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
        $shop_name = (isset($this->data['client_details']['hasOneShop']['shop']['name'])) ? $this->data['client_details']['hasOneShop']['shop']['name'] : '';
        return $this->subject($this->data['subject'])->from($this->data['client_details']['email'], $shop_name)->view('mails.client-support');
    }
}
