<?php

namespace NotificationTracker\Tests\Models;

use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\NotificationTracker;
use NotificationTracker\Tests\TestCase;

class TrackedChannelTest extends TestCase
{
    /** @test */
    public function new_model_fill_uuid_automatically_form_call()
    {
        $model = new TrackedChannel();

        $this->assertNull($model->uuid);
        $this->assertNotNull($model->getTrackerId());
        $this->assertNotNull($model->uuid);
    }

    /** @test */
    public function on_saving_id_and_date_autofilled()
    {
        $model = new TrackedChannel([
            'channel' => 'foo',
        ]);

        $model->refresh();
        $this->assertNull($model->uuid);
        $this->assertNull($model->sent_at);

        $model->save();

        $model->refresh();
        $this->assertNotNull($model->uuid);
        $this->assertNotNull($model->sent_at);
    }

    /** @test */
    public function on_delete_notification_channel_deleted()
    {
        /** @var TrackedChannel $tracker */
        $model = NotificationTracker::modelClass('channel')::factory()->create();

        $this->assertEquals(1, NotificationTracker::modelClass('channel')::query()->count());
        $this->assertNotNull(NotificationTracker::modelClass('channel')::query()->find($model->getKey()));
        $this->assertInstanceOf(NotificationTracker::modelClass('notification'), $model->notification);

        $model->notification->delete();

        $this->assertNull(NotificationTracker::modelClass('channel')::query()->find($model->getKey()));
        $this->assertEquals(0, NotificationTracker::modelClass('channel')::query()->count());
    }
}
