# Laravel notification tracker

![Packagist License](https://img.shields.io/packagist/l/think.studio/laravel-notification-tracker?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/think.studio/laravel-notification-tracker)](https://packagist.org/packages/think.studio/laravel-notification-tracker)
[![Total Downloads](https://img.shields.io/packagist/dt/think.studio/laravel-notification-tracker)](https://packagist.org/packages/think.studio/laravel-notification-tracker)
[![Build Status](https://scrutinizer-ci.com/g/dev-think-one/laravel-notification-tracker/badges/build.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-notification-tracker/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/dev-think-one/laravel-notification-tracker/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-notification-tracker/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dev-think-one/laravel-notification-tracker/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-notification-tracker/?branch=main)

Track status of notifications sent by application.

## Installation

Install the package via composer:

```bash
composer require think.studio/laravel-notification-tracker
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="NotificationTracker\ServiceProvider" --tag="config"
```

## Configuration

```php
public function register()
{
    // cancel default migrations files
    \NotificationTracker\NotificationTracker::ignoreMigrations();
    // cancel default web routes implementation
    \NotificationTracker\NotificationTracker::ignoreRoutes();
    // change class names what stored in database
    \NotificationTracker\NotificationTracker::classMap([
        'registration_confirmation' => \App\Notifications\RegistrationNotification::class,
    ]);
}
```

## Usage

For your notification please implement Interface `Trackable`, use trait `HasTracker`. 

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationTracker\Notification\HasTracker;
use NotificationTracker\Notification\Trackable;

class CertifiedNotification extends Notification implements ShouldQueue, Trackable
{
    use Queueable, HasTracker;

    public function via($notifiable = null)
    {
        return ['mail', 'custom'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)->subject('Certificate created');

        $message->line('Thank you!');

        return $this->tracker()->trackMailMessage($message, $notifiable);
    }

    public function toCustom($notifiable)
    {
        /** @var \NotificationTracker\Models\TrackedChannel $trackedChannel */
        $trackedChannel = $this->tracker()->track('custom', $notifiable);

        return [
            'subject' => 'Foo',
            'body' => "Foo {$trackedChannel->getClickTrackerUrl('https://test.com')} {$trackedChannel->getPixelImageHtml()}",
        ];
    }
}
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
