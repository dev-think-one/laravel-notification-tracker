<?php

namespace NotificationTracker\Tests\Notifications;

use NotificationTracker\Tests\Fixtures\Notifications\NotCertifiedNotification;
use NotificationTracker\Tests\TestCase;

class NotificationsClassMapTest extends TestCase
{
    /** @test */
    public function class_map()
    {
        $this->assertEquals(
            NotCertifiedNotification::class,
            \NotificationTracker\NotificationTracker::getMappedClass('not_certified_notification')
        );

        $this->assertEquals(
            'not_certified_notification',
            \NotificationTracker\NotificationTracker::getMapAlias(NotCertifiedNotification::class)
        );
    }

}
