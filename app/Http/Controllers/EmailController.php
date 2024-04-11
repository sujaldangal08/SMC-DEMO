<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\BrevoEmail;
use Illuminate\Support\Facades\Mail; // Add this line

class EmailController extends Controller
{
    public function sendPasswordResetLink($email)
    {
        $subject = 'Password Reset Link';
        $message = "Email not found";
        $fullname = 'shailendra';

        Mail::to($email)->send(new BrevoEmail($subject, $message, 'admin.email', ['user_name' => $fullname]));

        return response()->json([
            "message" => "Email Sent Successfully!"
        ]);
    }
}
