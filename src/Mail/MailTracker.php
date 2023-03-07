<?php

namespace NotificationTracker\Mail;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Str;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\NotificationTracker;

class MailTracker
{
    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function onSending(MessageSending $event)
    {
        $header = $event->message->getHeaders()->get(NotificationTracker::trackHeaderName());
        if (!$header || !Str::isUuid($header->getBodyAsString())) {
            return;
        }

        /** @var TrackedChannel $tracker */
        $tracker = NotificationTracker::modelClass('channel')::query()->uuid($header->getBodyAsString())->first();

        (new MailConverter($tracker, $event->message))->format();
    }

    public function onSent(MessageSent $event)
    {
        // TODO: maybe get mail ID?
    }
}
