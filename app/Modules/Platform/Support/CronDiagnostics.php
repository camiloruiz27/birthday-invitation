<?php

namespace App\Modules\Platform\Support;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Platform\PlatformServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The numbers behind `platform:cron-status` and the diagnostic email.
 *
 * Pulled into one place so the command a person reads on the server and the
 * email that lands in an inbox never compute "overdue" or "stale" two
 * different ways and quietly disagree with each other.
 */
class CronDiagnostics
{
    public static function gather(): array
    {
        $heartbeat = Cache::get(PlatformServiceProvider::HEARTBEAT_KEY);
        $heartbeatAt = $heartbeat ? CarbonImmutable::parse($heartbeat) : null;

        [$overdueEvents, $overdueGames] = self::overdueTimeline();

        $queueDriver = (string) config('queue.default');

        return [
            'heartbeat_at' => $heartbeatAt,
            'heartbeat_ago_minutes' => $heartbeatAt ? (int) $heartbeatAt->diffInMinutes(now()) : null,
            'overdue_events' => $overdueEvents,
            'overdue_games' => $overdueGames,
            'queue_driver' => $queueDriver,
            // Only meaningful once the queue is real: on `sync` nothing ever
            // waits in a table, so "0 trabajos" there would read as healthy
            // when it is actually just not measured.
            'queue_waiting' => $queueDriver !== 'sync' && Schema::hasTable('jobs')
                ? DB::table('jobs')->count()
                : null,
            'queue_failed' => $queueDriver !== 'sync' && Schema::hasTable('failed_jobs')
                ? DB::table('failed_jobs')->count()
                : null,
            'environment' => (string) config('app.env'),
            'debug' => (bool) config('app.debug'),
        ];
    }

    /** @return array{0: int, 1: int} [events overdue, games affected] */
    private static function overdueTimeline(): array
    {
        $overdue = 0;
        $games = 0;

        foreach (Game::query()->where('status', 'running')->get() as $game) {
            $due = TimelineEvent::query()
                ->where('game_id', $game->id)
                ->due($game->elapsedMinutes())
                ->count();

            if ($due > 0) {
                $overdue += $due;
                $games++;
            }
        }

        return [$overdue, $games];
    }
}
