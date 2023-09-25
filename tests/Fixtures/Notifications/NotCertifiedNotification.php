<?php

namespace NotificationTracker\Tests\Fixtures\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationTracker\Notification\HasTracker;
use NotificationTracker\Notification\Trackable;

class NotCertifiedNotification extends Notification implements ShouldQueue, Trackable
{
    use Queueable, HasTracker;

    public function via($notifiable = null)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)->subject('You have no certificates.');

        $message->line('Thank you!');

        return $this->tracker()->trackMailMessage($message, $notifiable);
    }
}
