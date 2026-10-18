<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\TimelineEvent;

/**
 * Sends the envelopes of a game whose minute has come.
 *
 * The cron (`immersion:process-timeline`) and the early release (every player
 * confirmed they read) both end up here, so "what is due" means the same thing
 * in both.
 */
class TimelineRelease
{
    /**
     * Every unsent event whose minute is at or before the game's clock.
     *
     * Inside this run, not through the queue: an ordinary envelope is a few
     * writes, and going through the table meant another connection and a worker
     * that had to be started to pick it up. Only the ones that still have to be
     * voiced take the slow queue (see DispatchTimelineEvent::run).
     *
     * @return int how many events were sent
     */
    public static function dueFor(Game $game): int
    {
        $dueEvents = TimelineEvent::query()
            ->where('game_id', $game->id)
            ->due($game->elapsedMinutes())
            ->get();

        foreach ($dueEvents as $event) {
            DispatchTimelineEvent::run($event);
        }

        return $dueEvents->count();
    }
}
