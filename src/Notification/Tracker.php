<?php

namespace NotificationTracker\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\Models\TrackedNotification;
use NotificationTracker\NotificationTracker;
use Symfony\Component\Mime\Email;

class Tracker
{
    protected TrackedNotification $trackedNotification;

    /**
     * @param Notification $notification
     * @throws \Exception
     */
    public function __construct(protected Notification $notification)
    {
        $this->trackedNotification = NotificationTracker::model('notification', [
            'class' => substr((($notification instanceof Trackable) ? $notification->getClassAlias() : get_class($notification)), -255),
            'data'  => serialize($notification),
        ]);
    }

    public function track($channel, $notifiable): TrackedChannel
    {
        if (!$this->trackedNotification->exists) {
            $this->trackedNotification->save();
        }

        $meta = [];
        if ($notifiable instanceof Model) {
            $meta['receiver'] = [
                'class' => $notifiable->getMorphClass(),
                'id'    => $notifiable->getKey(),
            ];
        }

        return $this->trackedNotification->channels()->create([
            'channel' => (string)$channel,
            'route'   => serialize($notifiable->routeNotificationFor('mail', $this->notification)),
            'meta'    => $meta,
        ]);
    }

    public function trackMailMessage(MailMessage $mailMessageRaw, $notifiable, $channel = 'mail'): MailMessage
    {
        $trackedChannel = $this->track($channel, $notifiable);

        return $mailMessageRaw
            ->withSymfonyMessage(
                fn (Email $message) => $message
                    ->getHeaders()
                    ->addTextHeader(NotificationTracker::trackHeaderName(), $trackedChannel->getTrackerId())
            );
    }
}
