<?php

namespace NotificationTracker;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use NotificationTracker\Mail\MailTracker;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->registerRoutes();
        $this->listenMailHooks();

        if ($this->app->runningInConsole()) {
            $this->registerMigrations();

            $this->publishes([
                __DIR__.'/../config/notification-tracker.php' => config_path('notification-tracker.php'),
            ], 'config');


            $this->commands([
                //
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/notification-tracker.php', 'notification-tracker');
    }

    protected function registerMigrations()
    {
        if (NotificationTracker::$runsMigrations) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    protected function registerRoutes()
    {
        if (NotificationTracker::$registersRoutes) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }

    protected function listenMailHooks()
    {
        Event::listen(MessageSending::class, fn (MessageSending $event) => MailTracker::make()->onSending($event));
        Event::listen(MessageSent::class, fn (MessageSent $event) => MailTracker::make()->onSent($event));
    }
}
