<?php

namespace NotificationTracker\Tests\Fixtures;

use NotificationTracker\Tests\Fixtures\Notifications\NotCertifiedNotification;

class TestServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        \NotificationTracker\NotificationTracker::classMap([
            'not_certified_notification' => NotCertifiedNotification::class,
        ]);
    }


}
