<?php

namespace NotificationTracker\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use JsonFieldCast\Casts\SimpleJsonField;
use NotificationTracker\Database\Factories\TrackedChannelFactory;
use NotificationTracker\NotificationTracker;

/**
 * @property \JsonFieldCast\Json\SimpleJsonField $stats
 * @property \JsonFieldCast\Json\SimpleJsonField $meta
 */
class TrackedChannel extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sent_at'        => 'datetime',
        'first_open_at'  => 'datetime',
        'last_open_at'   => 'datetime',
        'open_count'     => 'integer',
        'first_click_at' => 'datetime',
        'last_click_at'  => 'datetime',
        'click_count'    => 'integer',
        'stats'          => SimpleJsonField::class,
        'meta'           => SimpleJsonField::class,
    ];

    public function getTable(): string
    {
        return config('notification-tracker.tables.channels');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string)Str::uuid();
            }
            if (!$model->sent_at) {
                $model->sent_at = Carbon::now();
            }
        });
    }

    protected static function newFactory(): TrackedChannelFactory
    {
        return TrackedChannelFactory::new();
    }

    public function getTrackerId(): string
    {
        if (!$this->uuid) {
            $this->uuid = Str::uuid();
        }

        return (string)$this->uuid;
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(NotificationTracker::modelClass('notification'), 'notification_id', 'id');
    }

    public function scopeUuid($query, string $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    public function getPixelImageUrl(): string
    {
        return route('notification-tracker.pixel', $this->getTrackerId());
    }

    public function getClickTrackerUrl(?string $url = null): string
    {
        return route('notification-tracker.click', [
            $this->getTrackerId(),
            NotificationTracker::clickTrackerUrlParameterName() => $url ?: url('/'),
        ]);
    }

    public function getPixelImageHtml(): string
    {
        return sprintf(
            '<img src="%s" style="display:block;height:0px;width:0px;max-width:0px;max-height:0px;overflow:hidden" width="1" height="1" border="0" alt="">',
            $this->getPixelImageUrl(),
        );
    }

    public function incrementOpen(?Carbon $date = null): static
    {
        $date = $date ?? Carbon::now();
        if (!$this->first_open_at) {
            $this->first_open_at = $date;
        }

        return $this->fill([
            'last_open_at' => $date,
            'open_count'   => $this->open_count + 1,
        ]);
    }

    public function incrementClick(?Carbon $date = null): static
    {
        $date = $date ?? Carbon::now();
        if (!$this->first_click_at) {
            $this->first_click_at = $date;
        }

        return $this->fill([
            'last_click_at' => $date,
            'click_count'   => $this->click_count + 1,
        ]);
    }
}
