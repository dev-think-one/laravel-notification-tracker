<?php

namespace NotificationTracker\Tests\Notifications;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use JsonFieldCast\Json\SimpleJsonField;
use NotificationTracker\Mail\MailTracker;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\Models\TrackedNotification;
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

    /** @test */
    public function send_notification_with_meta()
    {
        Event::fake([
            MessageSending::class,
        ]);

        Notification::route('mail', 'test@foo.quix')
            ->notify(
                (new CertifiedNotification())
                ->trackerMeta('foo', 'foo_val')
                ->trackerMeta(function (SimpleJsonField $meta, TrackedChannel $trackedChannel) {
                    $meta->setAttribute('bar', 'bar_val');
                    $trackedChannel->meta->setAttribute('baz', 'baz_val');
                })
                ->notificationMeta('quix_foo', 'quix_foo_val')
                ->notificationMeta(function (SimpleJsonField $meta, TrackedNotification $trackedNotification) {
                    $meta->setAttribute('quix_bar', 'quix_bar_val');
                    $trackedNotification->meta->setAttribute('quix_baz', 'quix_baz_val');
                })
            );

        Event::assertDispatched(MessageSending::class);

        /** @var TrackedChannel $channel */
        $channel = NotificationTracker::modelClass('channel')::query()->first();

        $this->assertEquals('foo_val', $channel->meta->getAttribute('foo'));
        $this->assertEquals('bar_val', $channel->meta->getAttribute('bar'));
        $this->assertEquals('baz_val', $channel->meta->getAttribute('baz'));

        $this->assertEquals('quix_foo_val', $channel->notification->meta->getAttribute('quix_foo'));
        $this->assertEquals('quix_bar_val', $channel->notification->meta->getAttribute('quix_bar'));
        $this->assertEquals('quix_baz_val', $channel->notification->meta->getAttribute('quix_baz'));
    }
}
