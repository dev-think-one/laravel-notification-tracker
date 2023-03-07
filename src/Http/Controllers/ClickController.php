<?php

namespace NotificationTracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use NotificationTracker\Models\TrackedChannel;
use NotificationTracker\NotificationTracker;

class ClickController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($uuid = $request->route('uuid', '')) {
            // TODO: move to queue job to speed up updating?
            /** @var TrackedChannel $tracker */
            $tracker = NotificationTracker::modelClass('channel')::query()->uuid($uuid)->first();
            if ($tracker) {
                $tracker->incrementClick()->save();
            }
        }

        return Response::redirectTo($request->input(NotificationTracker::clickTrackerUrlParameterName(), '/'));
    }
}
