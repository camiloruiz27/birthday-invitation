<?php

namespace App\Modules\Express\Http\Controllers;

use App\Modules\Express\Support\ExpressEvents;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * /express — where the fair's QR lands: "create a room" or "I have a code".
 */
class LandingController
{
    public function index(Request $request): Response
    {
        $event = ExpressEvents::resolve($request);
        $status = ExpressEvents::status($event);

        return Inertia::render('Express/Landing', [
            'event' => [
                'slug' => $event['slug'],
                'name' => $event['name'],
                'tagline' => $event['tagline'],
            ],
            'enabled' => $status['open'],
            'closed_reason' => $status['reason'],
        ]);
    }
}
