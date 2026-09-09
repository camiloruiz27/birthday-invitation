<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\GameQuota;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\Mechanics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function __invoke(Request $request, GameQuota $quota): Response
    {
        $user = $request->user();
        $library = $user->library();

        // The per-case game quota belongs here more than anywhere else: this
        // is the page where a case is a thing you own and can run.
        $quotas = $quota->forCases($user, $library->pluck('slug'));

        return Inertia::render('Library', [
            'cases' => fn () => $library->map(fn (MysteryCase $case) => [
                'slug' => $case->slug,
                'name' => $case->name,
                'tagline' => $case->tagline,
                'cover_url' => $case->coverUrl(),
                'difficulty' => $case->difficulty,
                'duration_minutes' => $case->duration_minutes,
                'min_players' => $case->min_players,
                'max_players' => $case->max_players,
                'mechanics' => Mechanics::describe((array) $case->mechanics),
                'quota' => $quotas[$case->slug],
                // A case whose manifest is not deployed cannot be played, so
                // the library must not offer to start a game with it.
                'playable' => $case->isPlayable(),
            ])->values(),
        ]);
    }
}
