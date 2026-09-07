<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The operations centre: what is running right now, what is ready to run, and
 * what you own.
 *
 * Deliberately not a list of everything — it answers "what should I do next?"
 * and hands off to the sections for the full listings.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'stats' => fn () => [
                'cases' => $user->entitlements()->active()->count(),
                'games' => $user->games()->count(),
                'running' => $user->games()->whereIn('status', ['running', 'paused'])->count(),
            ],

            // Games in progress come first: if one is running, that is what
            // the Game Master opened this page for.
            'activeGames' => fn () => $user->games()
                ->whereIn('status', ['running', 'paused'])
                ->withCount('players')
                ->latest('started_at')
                ->get()
                ->map(fn (Game $game) => $this->gameSummary($game))
                ->values(),

            'draftGames' => fn () => $user->games()
                ->where('status', 'draft')
                ->withCount('players')
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn (Game $game) => $this->gameSummary($game))
                ->values(),

            'library' => fn () => $user->library()->map(fn ($case) => [
                'slug' => $case->slug,
                'name' => $case->name,
                'cover_url' => $case->coverUrl(),
            ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function gameSummary(Game $game): array
    {
        return [
            'id' => $game->id,
            'name' => $game->name,
            'status' => $game->status,
            'case_slug' => $game->case_slug,
            'players_count' => $game->players_count,
            'started_at' => $game->started_at,
            'elapsed_minutes' => $game->elapsed_minutes,
        ];
    }
}
