<?php

namespace NotificationTracker\Tests\Notifications;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use NotificationTracker\Mail\MailTracker;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\NotificationTracker;
use NotificationTracker\Tests\Fixtures\Notifications\CertifiedNotification;
use NotificationTracker\Tests\Fixtures\Notifications\NotTrackedNotification;
use NotificationTracker\Tests\TestCase;

class OnDemandMailNotificationTest extends TestCase
{
    /** @test */
    public function success_created_tracker()
    {
        Event::fake([
            MessageSending::class,
        ]);

        $this->assertEquals(0, NotificationTracker::modelClass('notification')::query()->count());
        $this->assertEquals(0, NotificationTracker::modelClass('channel')::query()->count());

        Notification::route('mail', 'test@foo.quix')->notify(new CertifiedNotification());

        $this->assertEquals(1, NotificationTracker::modelClass('notification')::query()->count());
        $this->assertEquals(1, NotificationTracker::modelClass('channel')::query()->count());

        Event::assertDispatched(MessageSending::class);

        /** @var TrackedChannel $channel */
        $channel = NotificationTracker::modelClass('channel')::query()->first();

        $this->assertEquals(CertifiedNotification::class, $channel->notification->class);
    }

    /** @test */
    public function success_mail_formatted()
    {
        Event::fake([
            MessageSending::class,
        ]);
        Notification::route('mail', 'test@foo.quix')->notify(new CertifiedNotification());

        /** @var TrackedChannel $channel */
        $channel = NotificationTracker::modelClass('channel')::query()->first();

        $this->assertEquals(CertifiedNotification::class, $channel->notification->class);

        Event::assertDispatched(function (MessageSending $event) use ($channel) {
            $this->assertStringNotContainsString($channel->getTrackerId(), $event->message->getBody()->bodyToString());
            MailTracker::make()->onSending($event);
            $this->assertStringContainsString($channel->getTrackerId(), $event->message->getBody()->bodyToString());

            return true;
        });
    }

    /** @test */
    public function not_tracked_notification_do_do_not_process()
    {
        Event::fake([
            MessageSending::class,
        ]);
        $this->assertEquals(0, NotificationTracker::modelClass('notification')::query()->count());
        $this->assertEquals(0, NotificationTracker::modelClass('channel')::query()->count());

        Notification::route('mail', 'test@foo.quix')->notify(new NotTrackedNotification());

        $this->assertEquals(0, NotificationTracker::modelClass('notification')::query()->count());
        $this->assertEquals(0, NotificationTracker::modelClass('channel')::query()->count());

        Event::assertDispatched(MessageSending::class);
    }
}
