<?php

namespace App\Modules\Express\Http\Controllers;

use App\Modules\Express\Support\ExpressEvents;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The stand's printable poster: a big QR to the event, tagged so visits that
 * come from it can be told apart in the analytics.
 */
class PosterController
{
    public function show(string $event): Response
    {
        $found = ExpressEvents::find($event) ?? abort(404);

        return Inertia::render('Express/Poster', [
            'event' => ['slug' => $found['slug'], 'name' => $found['name']],
            'url' => ExpressEvents::posterUrl($found),
            'title' => 'Resuelve un caso en 3 minutos',
        ]);
    }
}
