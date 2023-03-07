<?php

namespace NotificationTracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\NotificationTracker;

class PixelController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($uuid = $request->route('uuid', '')) {
            // TODO: move to queue job to speed up updating?
            /** @var TrackedChannel $tracker */
            $tracker = NotificationTracker::modelClass('channel')::query()->uuid($uuid)->first();
            if ($tracker) {
                $tracker->incrementOpen()->save();
            }
        }

        return Response::file(NotificationTracker::pixelFilePath(), [
            'Cache-Control' => 'private, no-cache, no-cache=Set-Cookie, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }
}
