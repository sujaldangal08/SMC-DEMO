<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FirebaseChannel
{
    protected $messaging;

    public function __construct(\Kreait\Firebase\Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toFirebase($notifiable);

        // You should have a method in your notifiable model to get the Firebase token
        $deviceToken = $notifiable->routeNotificationFor('firebase', $notification);

        $messaging = $this->messaging;

        $notification = FirebaseNotification::fromArray([
            'title' => $message->notification()->title(),
            'body' => $message->notification()->body(),
        ]);

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification) // optional
            ->withData($message->data()); // optional

        $messaging->send($message);
    }
}
