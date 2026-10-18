<?php

namespace App\Modules\Immersion\Console\Commands;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\TimelineRelease;
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
            $dispatched += TimelineRelease::dueFor($game);
        }

        $this->info("Eventos disparados: {$dispatched}");

        return self::SUCCESS;
    }
}
