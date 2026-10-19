<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\GameQuota;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Pricing\QuoteBuilder;
use App\Modules\Platform\Support\Mechanics;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function __invoke(Request $request, GameQuota $quota): Response
    {
        $user = $request->user();
        $library = $user->library();

        // The library page filters and orders in the browser, so it ships the
        // whole (small) list once and never asks for it again: each filter the
        // owner tries costs no request, and so no database connection.
        $quotes = QuoteBuilder::make();

        // The per-case game quota belongs here more than anywhere else: this
        // is the page where a case is a thing you own and can run.
        $quotas = $quota->forCases($user, $library->pluck('slug'));

        return Inertia::render('Library', [
            'cases' => fn () => $library->map(fn (MysteryCase $case) => [
                'slug' => $case->slug,
                'name' => $case->name,
                'tagline' => $case->tagline,
                // For the "Ver el caso" modal: the library shows a case without
                // sending the owner to the public page (which also 404s for a
                // case that is theirs but not published).
                'description' => $case->description,
                'cover_url' => $case->coverUrl(),
                'difficulty' => $case->difficulty,
                'duration_minutes' => $case->duration_minutes,
                'min_players' => $case->min_players,
                'max_players' => $case->max_players,
                'mechanics' => Mechanics::describe((array) $case->mechanics),
                'acquired_at' => $case->acquired_at ? Carbon::parse($case->acquired_at)->toIso8601String() : null,
                // Today's price in the owner's market, not what they paid: it
                // makes the library's price filter and ordering meaningful.
                ...$quotes->display((int) $case->price_amount),
                'uses_ai' => collect(Mechanics::describe((array) $case->mechanics))->contains(fn (array $mechanic) => $mechanic['ai']),
                'quota' => $quotas[$case->slug],
                // A case whose manifest is not deployed cannot be played, so
                // the library must not offer to start a game with it.
                'playable' => $case->isPlayable(),
            ])->values(),
        ]);
    }
}
