<?php

namespace NotificationTracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JsonFieldCast\Casts\SimpleJsonField;
use NotificationTracker\Database\Factories\TrackedNotificationFactory;
use NotificationTracker\NotificationTracker;

/**
 * @property \JsonFieldCast\Json\SimpleJsonField $meta
 */
class TrackedNotification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta' => SimpleJsonField::class,
    ];

    public function getTable(): string
    {
        return config('notification-tracker.tables.notifications');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->channels->each(fn ($channel) => $channel->delete());
        });
    }

    protected static function newFactory(): TrackedNotificationFactory
    {
        return TrackedNotificationFactory::new();
    }

    public function channels(): HasMany
    {
        return $this->hasMany(NotificationTracker::modelClass('channel'), 'notification_id', 'id');
    }
}
