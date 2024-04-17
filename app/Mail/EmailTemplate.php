<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class EmailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $subject;

    public $username;


    public function __construct($username, $subject, $template_type, $otp = null)
    {
        $this->username = $username;
        $this->subject = $subject;
        $this->template_type = $template_type;
        $this->otp = $otp;
    }




    /**
     * Get the message envelope.
     */

    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: $this->emailTemplate->subject, // Retrieve the subject from the emailTemplate model
    //     );
    // }


    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: '.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

public function build()
{
    // Fetch the email template from the database
    $emailTemplate = \App\Models\EmailTemplate::where('template_type', $this->template_type)->first();

    // Use the data from the email template to build the email
    return $this->view('email.email') // Replace 'emails.template' with the actual view for your email template
        ->with('subject', $this->subject)
        ->with([
            'logo' => $emailTemplate->logo,
            'top_link' => $emailTemplate->top_link,
            'top_text' => $emailTemplate->top_text,
            'title' => $emailTemplate->title,
            'emessage' => $emailTemplate->emessage,
            'icon' => $emailTemplate->icon,
            'buttons' => $emailTemplate->buttons,
            'button_link' => $emailTemplate->button_link,
            'footer_address' => $emailTemplate->footer_address,
            'footer_message' => $emailTemplate->footer_message,
            'footer_link' => $emailTemplate->footer_link,
            'footer_text' => $emailTemplate->footer_text,
            'color' => $emailTemplate->color,
            'username' => $this->username,
            'template_type' => $this->template_type,
            'otp' => $this->otp,
        ]);



}
}
