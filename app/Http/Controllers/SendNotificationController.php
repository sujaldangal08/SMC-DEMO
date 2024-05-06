<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SendNotificationController extends Controller
{
    public function sendNotification()
    {
        $user = User::find(1); // Example: Get a user to send notification
        Notification::send($user, new PushNotification());
    }
}
