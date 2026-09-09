<?php

namespace App\Modules\Platform\Support;

use App\Models\User;
use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Support\Carbon;

/**
 * Platform-wide numbers for the administrator.
 *
 * Aggregates only. The administrator sees how much the platform is used, never
 * the contents of somebody's game — no transcripts, no accusations, no inbox.
 * Those belong to the people playing, and an admin screen is not a reason to
 * hand them over.
 *
 * Query-builder aggregates throughout so the same code runs on MySQL and on
 * the SQLite the tests use.
 */
class PlatformMetrics
{
    private const RECENT_DAYS = 30;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'people' => $this->people(),
            'catalog' => $this->catalog(),
            'usage' => $this->usage(),
            'ai' => $this->ai(),
            'health' => $this->health(),
            'topCases' => $this->topCases(),
            'recentGames' => $this->recentGames(),
        ];
    }

    /**
     * Accounts versus invited players — two different populations that are
     * easy to conflate. A player has no account and never will: they are
     * someone a Game Master invited by email.
     *
     * @return array<string, int>
     */
    private function people(): array
    {
        $since = Carbon::now()->subDays(self::RECENT_DAYS);

        return [
            'users' => User::count(),
            'users_recent' => User::where('created_at', '>=', $since)->count(),
            'admins' => User::where('is_admin', true)->count(),

            // A registered account only becomes a Game Master once it actually
            // runs something.
            'game_masters' => Game::query()->whereNotNull('user_id')->distinct()->count('user_id'),

            'players' => Player::count(),
            // The same person invited to three games is three rows; distinct
            // addresses is closer to "how many humans have played".
            'players_unique' => Player::query()->distinct()->count('email'),
            'players_recent' => Player::where('created_at', '>=', $since)->count(),
        ];
    }

    /**
     * @return array<string, int|array>
     */
    private function catalog(): array
    {
        return [
            'cases' => MysteryCase::count(),
            'cases_published' => MysteryCase::published()->count(),
            'entitlements' => Entitlement::count(),

            // Split by origin rather than reporting revenue: checkout is still
            // simulated, so any money figure would be fiction.
            'entitlements_by_source' => Entitlement::query()
                ->selectRaw('source, count(*) as total')
                ->groupBy('source')
                ->pluck('total', 'source')
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function usage(): array
    {
        $byStatus = Game::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $games = Game::count();

        return [
            'games' => $games,
            'games_by_status' => $byStatus,
            'games_by_mode' => Game::query()
                ->selectRaw('mode, count(*) as total')
                ->groupBy('mode')
                ->pluck('total', 'mode')
                ->all(),
            'average_players' => $games > 0
                ? round(Player::count() / $games, 1)
                : 0,
            'average_minutes' => $this->averageFinishedMinutes(),
            'accusation_rate' => $this->accusationRate(),
        ];
    }

    /**
     * Mean length of the games that actually finished.
     *
     * Computed in PHP because the real duration subtracts paused time, which
     * lives in the model. Finished games are the small set here, and this is
     * the honest number rather than a SQL approximation that ignores pauses.
     */
    private function averageFinishedMinutes(): ?int
    {
        $finished = Game::query()
            ->where('status', 'finished')
            ->whereNotNull('started_at')
            ->get();

        if ($finished->isEmpty()) {
            return null;
        }

        return (int) round($finished->avg(fn (Game $game) => $game->elapsedMinutes()));
    }

    /**
     * Of the players who reached a finished game, how many actually accused
     * someone. A low number means people are dropping out before the end.
     */
    private function accusationRate(): ?float
    {
        $finishedGameIds = Game::query()->where('status', 'finished')->pluck('id');

        if ($finishedGameIds->isEmpty()) {
            return null;
        }

        $players = Player::whereIn('game_id', $finishedGameIds)->count();

        if ($players === 0) {
            return null;
        }

        $accusations = Accusation::whereIn('game_id', $finishedGameIds)->count();

        return round(($accusations / $players) * 100, 1);
    }

    /**
     * What the AI is actually being asked to do — this is the cost driver.
     *
     * @return array<string, int>
     */
    private function ai(): array
    {
        return [
            'interrogation_sessions' => InterrogationSession::count(),
            // One question is one gateway call.
            'questions_asked' => InterrogationMessage::where('role', 'player')->count(),
            'audio_events' => TimelineEvent::where('type', 'audio_email')->count(),
            'audio_generated' => TimelineEvent::where('type', 'audio_email')
                ->whereNotNull('audio_path')
                ->count(),
        ];
    }

    /**
     * Things that need someone to do something about them.
     *
     * @return array<string, int>
     */
    private function health(): array
    {
        return [
            // Emails that went out without their recording.
            'audio_failed' => TimelineEvent::where('audio_status', TimelineEvent::AUDIO_FAILED)->count(),

            // Games whose owner never claimed them (pre-accounts era): they are
            // unreachable through the web until someone runs platform:claim-games.
            'orphan_games' => Game::whereNull('user_id')->count(),

            // Catalog rows whose case content is not installed on this server.
            'unplayable_cases' => MysteryCase::all()
                ->filter(fn (MysteryCase $case) => ! $case->isPlayable())
                ->count(),
        ];
    }

    /**
     * Most played cases, joined by slug — the engine does not carry a foreign
     * key into the catalog on purpose.
     *
     * @return array<int, array<string, mixed>>
     */
    private function topCases(): array
    {
        $counts = Game::query()
            ->selectRaw('case_slug, count(*) as total')
            ->groupBy('case_slug')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'case_slug');

        $names = MysteryCase::whereIn('slug', $counts->keys())->pluck('name', 'slug');

        return $counts->map(fn (int $total, string $slug) => [
            'slug' => $slug,
            'name' => $names[$slug] ?? $slug,
            'games' => $total,
        ])->values()->all();
    }

    /**
     * Recent activity. Names and counts only: no case contents.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentGames(): array
    {
        return Game::query()
            ->with('owner:id,name')
            ->withCount('players')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Game $game) => [
                'id' => $game->id,
                'name' => $game->name,
                'status' => $game->status,
                'mode' => $game->mode,
                'case_slug' => $game->case_slug,
                'players_count' => $game->players_count,
                'owner' => $game->owner?->name,
                'created_at' => $game->created_at,
            ])
            ->all();
    }
}
