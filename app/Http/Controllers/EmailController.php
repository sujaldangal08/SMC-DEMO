<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\BrevoEmail;


class EmailController extends Controller
{
    //
    public function sendEmail($email)
    {
        $subject = 'Password Reset Link';
        $message = 'Click the link below to reset your password';
        $fullname = 'Sovia';
        \Mail::to($email)->send(new BrevoEmail($subject, $message, 'email.email', ['user_name' => $fullname]));

        return response()->json([
            'status' => 'success',
            'message' => 'Email sent successfully'
        ]);
    }
}
