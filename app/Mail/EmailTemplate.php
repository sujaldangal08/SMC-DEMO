<?php

namespace App\Mail;

use AllowDynamicProperties;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

#[AllowDynamicProperties] class EmailTemplate extends Mailable
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
        return $this->view('email.email')
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
