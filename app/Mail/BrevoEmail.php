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

    public $body;
    public $template;
    public $data;

    public function __construct($subject, $body, $template = 'email.email', $data)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->template = $template;
        $this->data = $data;
    }

    public function build()
    {
        return $this->view($this->template)
            ->with('subject', $this->subject)
            ->with('body', $this->body)
            ->with($this->data);
    }
}
