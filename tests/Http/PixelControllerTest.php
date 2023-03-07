<?php

namespace NotificationTracker\Tests\Http;

use Carbon\Carbon;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\Tests\TestCase;

class PixelControllerTest extends TestCase
{
    /** @test */
    public function success_open_increment()
    {
        /** @var TrackedChannel $tracker */
        $tracker = TrackedChannel::factory()->create();

        $tracker->refresh();
        $this->assertNull($tracker->first_open_at);
        $this->assertNull($tracker->last_open_at);
        $this->assertEquals(0, $tracker->open_count);

        $response = $this->get($tracker->getPixelImageUrl());
        $response->assertSuccessful();

        $tracker->refresh();
        $this->assertNotNull($tracker->first_open_at);
        $this->assertNotNull($tracker->last_open_at);
        $this->assertEquals($tracker->first_open_at, Carbon::now()->setMicrosecond(0));
        $this->assertEquals($tracker->first_open_at, $tracker->last_open_at);
        $this->assertEquals(1, $tracker->open_count);

        // ad-hoc for first_open_at and last_open_at to be not equals
        sleep(1);
        $response = $this->get($tracker->getPixelImageUrl());
        $response->assertSuccessful();

        $tracker->refresh();
        $this->assertNotNull($tracker->first_open_at);
        $this->assertNotNull($tracker->last_open_at);
        $this->assertNotEquals($tracker->first_open_at, $tracker->last_open_at);
        $this->assertEquals(2, $tracker->open_count);

        $response->assertHeader('content-type', 'image/gif');
    }

    /** @test */
    public function fake_uuid_return_success()
    {
        $this->assertEquals(0, TrackedChannel::query()->count());

        $response = $this->get(route('notification-tracker.pixel', 'fake'));
        $response->assertSuccessful();
        $response->assertHeader('content-type', 'image/gif');

        $this->assertEquals(0, TrackedChannel::query()->count());
    }
}
