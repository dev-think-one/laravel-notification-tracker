<?php

namespace NotificationTracker\Tests\Http;

use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\NotificationTracker;
use NotificationTracker\Tests\TestCase;

class ClickControllerTest extends TestCase
{
    /** @test */
    public function success_click_increment()
    {
        $urlToRedirect = 'https://test.foo.bar';

        /** @var TrackedChannel $tracker */
        $tracker = TrackedChannel::factory()->create();

        $tracker->refresh();
        $this->assertNull($tracker->first_click_at);
        $this->assertNull($tracker->last_click_at);
        $this->assertEquals(0, $tracker->click_count);

        $response = $this->get($tracker->getClickTrackerUrl($urlToRedirect));
        $response->assertRedirect($urlToRedirect);

        $tracker->refresh();
        $this->assertNotNull($tracker->first_click_at);
        $this->assertNotNull($tracker->last_click_at);
        $this->assertEquals($tracker->first_click_at, $tracker->last_click_at);
        $this->assertEquals(1, $tracker->click_count);

        // ad-hoc for first_open_at and last_open_at to be not equals
        sleep(1);
        $response = $this->get($tracker->getClickTrackerUrl($urlToRedirect));
        $response->assertRedirect($urlToRedirect);

        $tracker->refresh();
        $this->assertNotNull($tracker->first_click_at);
        $this->assertNotNull($tracker->last_click_at);
        $this->assertNotEquals($tracker->first_click_at, $tracker->last_click_at);
        $this->assertEquals(2, $tracker->click_count);
    }

    /** @test */
    public function fake_uuid_return_success()
    {
        $urlToRedirect = 'https://test.bar.ba<';
        $this->assertEquals(0, TrackedChannel::query()->count());

        $response = $this->get(route('notification-tracker.click', [
            'fake',
            NotificationTracker::clickTrackerUrlParameterName() => $urlToRedirect ?: url('/'),
        ]));
        $response->assertRedirect($urlToRedirect);

        $this->assertEquals(0, TrackedChannel::query()->count());
    }
}
