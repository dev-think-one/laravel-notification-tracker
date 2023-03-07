<?php

namespace NotificationTracker\Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\Models\TrackedNotification;

class TrackedChannelFactory extends Factory
{
    protected $model = TrackedChannel::class;

    public function definition(): array
    {
        return [
            'notification_id' => TrackedNotification::factory(),
            'channel'         => 'mail',
            'route'           => serialize('dev@example.com'),
            'sent_at'         => Carbon::now()->subDay(),
        ];
    }
}
