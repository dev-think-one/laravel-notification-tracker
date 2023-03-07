<?php

namespace NotificationTracker\Tests\Models;

use Carbon\Carbon;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\NotificationTracker;
use NotificationTracker\Tests\TestCase;

class TrackedChannelResendNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_resend_notification()
    {
        /** @var TrackedChannel $trackedChannel */
        $trackedChannel = NotificationTracker::modelClass('channel')::factory()->create();
        $this->assertEquals(1, NotificationTracker::modelClass('channel')::query()->count());

        $trackedChannel->resendNotification();

        $this->assertEquals(2, NotificationTracker::modelClass('channel')::query()->count());
    }

    /** @test */
    public function skip_notification_if_not_expired()
    {
        /** @var TrackedChannel $trackedChannel */
        $trackedChannel = NotificationTracker::modelClass('channel')::factory()->create([
            'sent_at'         => Carbon::now()->subDays(1),
        ]);
        $this->assertEquals(1, NotificationTracker::modelClass('channel')::query()->count());

        $trackedChannel->resendNotOpenedNotification(2 * 24 * 60 * 60);
        $this->assertEquals(1, NotificationTracker::modelClass('channel')::query()->count());

        $trackedChannel->resendNotOpenedNotification(0.5 * 24 * 60 * 60);
        $this->assertEquals(2, NotificationTracker::modelClass('channel')::query()->count());
    }
}
