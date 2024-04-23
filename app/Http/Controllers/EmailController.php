<?php

namespace App\Http\Controllers;

use App\Mail\EmailTemplate;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    //

    public function sendEmail()
    {
        // Fetch the email template from the database
        $emailTemplate = \App\Models\EmailTemplate::where('template_type', 'otp')->first(); // Replace 1 with the ID of the email template you want to fetch

        $username = 'John Doe'; // Replace 'John Doe' with the actual username
        $subject = $emailTemplate->subject; // Retrieve the subject from the emailTemplate model
        $otp = 1234;
        $template_type = $emailTemplate->template_type; // Retrieve the template type from the emailTemplate model

        // Create a new instance of the mailable and pass the email template to it
        $mailable = new EmailTemplate($username, $subject, $template_type, $otp);

        // Send the email
        Mail::to('soviamdr@gmail.com')->send($mailable); // Replace 'recipient@example.com' with the recipient's email address

        return response()->json([
            'status' => 'success',
            'message' => 'Email sent successfully',
            'data' => null,
        ], 200);
    }
}
