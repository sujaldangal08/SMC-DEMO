<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BrevoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $template;
    public $user_name;


    public function __construct($subject, $body, $template = 'email.email', $user_name)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->template = $template;
        $this->user_name = $user_name;
    }

    public function build()
    {
        return $this->view($this->template)
        ->with('subject', $this->subject)
        ->with('body', $this->body)
        ->with('user_name', $this->user_name);

    }
}
