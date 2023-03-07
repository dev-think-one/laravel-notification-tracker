<?php

namespace NotificationTracker\Database\Factories;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationTracker\Models\TrackedNotification;

class TrackedNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrackedNotification::class;

    public function definition(): array
    {
        $resetPasswordNotification = new ResetPassword(Str::random());

        return [
            'class' => $resetPasswordNotification::class,
            'data'  => serialize($resetPasswordNotification),
        ];
    }

    public function forNotification(Notification $notification = null): static
    {
        return $this->state([
            'class' => $notification::class,
            'data'  => serialize($notification),
        ]);
    }
}
