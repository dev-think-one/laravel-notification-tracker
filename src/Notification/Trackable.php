<?php

namespace NotificationTracker\Notification;

interface Trackable
{
    public function tracker(): Tracker;

    public function getClassAlias(): string;
}
