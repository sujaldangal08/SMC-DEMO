<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;

class PushNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return [FirebaseChannel::class];
    }

    public function toFirebase($notifiable)
    {
        $title = 'Hello World!';
        $body = 'This is a test notification.';

        return CloudMessage::new()
            ->withNotification([
                'title' => $title,
                'body' => $body,
            ])
            ->withData([
                'key' => 'value', // Add any additional data you want to send
            ]);
    }
}
