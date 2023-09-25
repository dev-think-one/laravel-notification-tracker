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
    protected TrackedChannel $trackedChannel;

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

        // we need initialise value to allow update meta
        $this->trackedChannel = NotificationTracker::model('channel', []);
    }

    public function track($channel, $notifiable): TrackedChannel
    {
        if (!$this->trackedNotification->exists || $this->trackedNotification->isDirty()) {
            $this->trackedNotification->save();
        }

        if ($notifiable instanceof Model) {
            $this->trackedChannel->meta->toMorph('receiver', $notifiable);
        }

        $this->trackedChannel->fill([
            'channel' => (string)$channel,
            'route'   => serialize($notifiable->routeNotificationFor('mail', $this->notification)),
        ]);

        $this->trackedNotification->channels()->save($this->trackedChannel);

        return $this->trackedChannel;
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

    public function trackerMeta(string|\Closure $key = null, mixed $value = null): static
    {
        if (is_callable($key)) {
            call_user_func($key, $this->trackedChannel->meta, $this->trackedChannel);
        }

        if (is_string($key)) {
            $this->trackedChannel->meta->setAttribute($key, $value);
        }

        return $this;
    }

    public function notificationMeta(string|\Closure $key = null, mixed $value = null): static
    {
        if (is_callable($key)) {
            call_user_func($key, $this->trackedNotification->meta, $this->trackedNotification);
        }

        if (is_string($key)) {
            $this->trackedNotification->meta->setAttribute($key, $value);
        }

        return $this;
    }
}
