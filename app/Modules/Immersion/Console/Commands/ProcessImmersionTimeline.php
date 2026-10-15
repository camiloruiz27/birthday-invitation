<?php

namespace App\Modules\Immersion\Console\Commands;

use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Console\Command;

class ProcessImmersionTimeline extends Command
{
    protected $signature = 'immersion:process-timeline';

    protected $description = 'Revisa las partidas en curso y dispara los eventos de linea de tiempo que ya vencieron.';

    public function handle(): int
    {
        $runningGames = Game::query()->where('status', 'running')->get();

        $dispatched = 0;

        foreach ($runningGames as $game) {
            $elapsedMinutes = $game->elapsedMinutes();

            $dueEvents = TimelineEvent::query()
                ->where('game_id', $game->id)
                ->due($elapsedMinutes)
                ->get();

            foreach ($dueEvents as $event) {
                // Inside this run, not through the queue: an ordinary envelope is
                // a few writes, and going through the table meant another
                // connection and a worker that had to be started to pick it up.
                // Only the ones that still have to be voiced take the slow queue.
                DispatchTimelineEvent::run($event);
                $dispatched++;
            }
        }

        $this->info("Eventos disparados: {$dispatched}");

        return self::SUCCESS;
    }
}
