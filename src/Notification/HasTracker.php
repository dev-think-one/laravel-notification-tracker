<?php

namespace NotificationTracker\Notification;

use NotificationTracker\NotificationTracker;

trait HasTracker
{
    protected ?Tracker $_tracker = null;

    public function tracker(): Tracker
    {
        if ($this->_tracker) {
            return $this->_tracker;
        }

        return $this->_tracker = new Tracker($this);
    }

    public function getClassAlias(): string
    {
        $morphMap = NotificationTracker::classMap();

        if (!empty($morphMap) && in_array(static::class, $morphMap)) {
            return array_search(static::class, $morphMap, true);
        }

        return static::class;
    }

    public function trackerMeta(string|\Closure $key = null, mixed $value = null): static
    {
        $this->tracker()->trackerMeta($key, $value);

        return $this;
    }

    public function notificationMeta(string|\Closure $key = null, mixed $value = null): static
    {
        $this->tracker()->notificationMeta($key, $value);

        return $this;
    }
}
