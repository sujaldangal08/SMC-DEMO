<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\BrevoEmail;

class EmailController extends Controller
{
    //

public function sendEmail($email, $otp)
{
    $subject = 'OTP for verificaton';
    $message = 'this is otp';
    $fullname = 'Sovia';
    \Mail::to($email)->send(new BrevoEmail($subject, $message, 'email.email', ['user_name' => $fullname, 'otp' => $otp]));

    return response()->json([
        'status' => 'success',
        'message' => 'Email sent successfully'
    ]);
}

}
