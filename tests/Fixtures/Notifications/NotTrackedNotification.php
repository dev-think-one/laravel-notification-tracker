<?php

namespace NotificationTracker\Tests\Fixtures\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotTrackedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable = null)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)->subject('New Notification');

        $message->line('Thank you!');

        return $message;
    }
}
