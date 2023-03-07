<?php

namespace NotificationTracker\Notification;

interface Trackable
{
    public function tracker(): Tracker;

    public function getClassAlias(): string;

    public function trackerMeta(string|\Closure $key = null, mixed $value = null): static;

    public function notificationMeta(string|\Closure $key = null, mixed $value = null): static;
}
