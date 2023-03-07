<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('notification-tracker.routes.middleware', []))
    ->prefix(config('notification-tracker.routes.prefix', ''))
    ->group(function () {
        Route::get('p/{uuid}', \NotificationTracker\Http\Controllers\PixelController::class)
            ->name('notification-tracker.pixel');
        Route::get('c/{uuid}', \NotificationTracker\Http\Controllers\ClickController::class)
            ->name('notification-tracker.click');
    });
