<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\Mechanics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // How many games the owner has run of each case: it is the thing that
        // makes a library feel like yours rather than a receipt.
        $gameCounts = $user->games()
            ->selectRaw('case_slug, count(*) as total')
            ->groupBy('case_slug')
            ->pluck('total', 'case_slug');

        return Inertia::render('Library', [
            'cases' => fn () => $user->library()->map(fn (MysteryCase $case) => [
                'slug' => $case->slug,
                'name' => $case->name,
                'tagline' => $case->tagline,
                'cover_url' => $case->coverUrl(),
                'difficulty' => $case->difficulty,
                'duration_minutes' => $case->duration_minutes,
                'min_players' => $case->min_players,
                'max_players' => $case->max_players,
                'mechanics' => Mechanics::describe((array) $case->mechanics),
                'games_count' => (int) ($gameCounts[$case->slug] ?? 0),
                // A case whose manifest is not deployed cannot be played, so
                // the library must not offer to start a game with it.
                'playable' => $case->isPlayable(),
            ])->values(),
        ]);
    }
}
