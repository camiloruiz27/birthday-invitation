<?php

namespace App\Modules\Express\Console;

use App\Modules\Express\Models\Accusation;
use App\Modules\Express\Models\Lead;
use App\Modules\Express\Models\Player;
use App\Modules\Express\Models\Question;
use App\Modules\Express\Models\Room;
use App\Modules\Express\Support\ExpressEvents;
use Illuminate\Console\Command;

/**
 * How a fair went, from the tables: rooms, completion, scores, cases played
 * and contacts left. (Clicks on the end-screen button are in the analytics,
 * tagged with the event's utm_campaign.)
 */
class ReportCommand extends Command
{
    protected $signature = 'express:report {event? : Event slug (default: the default event)}';

    protected $description = 'Summary of an Express event: rooms, completion, scores, cases and leads';

    public function handle(): int
    {
        $event = ExpressEvents::find($this->argument('event'));

        if ($event === null) {
            $this->error('Unknown event.');

            return self::FAILURE;
        }

        $slug = $event['slug'];
        $rooms = Room::query()->where('event_slug', $slug);
        $roomIds = (clone $rooms)->pluck('id');

        $byStatus = (clone $rooms)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $started = (int) $byStatus->only([Room::RUNNING, Room::FINISHED, Room::EXPIRED])->sum()
            - (int) (clone $rooms)->where('status', Room::EXPIRED)->whereNull('started_at_ms')->count();
        $finished = (int) ($byStatus[Room::FINISHED] ?? 0);

        $players = Player::query()->whereIn('room_id', $roomIds)->count();
        $accused = Accusation::query()->whereIn('room_id', $roomIds)->count();
        $perfect = Accusation::query()->whereIn('room_id', $roomIds)->where('score_total', 100)->count();
        $average = Accusation::query()->whereIn('room_id', $roomIds)->whereNotNull('score_total')->avg('score_total');

        $this->info("Express — {$event['name']} ({$slug})");
        $this->line('Salas creadas:       '.$roomIds->count());
        $this->line('Salas iniciadas:     '.$started);
        $this->line('Salas terminadas:    '.$finished.($started > 0 ? ' ('.round($finished / $started * 100).'% de las iniciadas)' : ''));
        $this->line('Jugadores:           '.$players);
        $this->line('Acusaciones:         '.$accused.' (puntaje medio '.($average !== null ? round($average, 1) : '-').', '.$perfect.' con 100)');
        $this->line('Preguntas hechas:    '.Question::query()->whereIn('room_id', $roomIds)->count());
        $this->line('Contactos dejados:   '.Lead::query()->where('event_slug', $slug)->count());

        $this->newLine();
        $this->line('Por caso (salas / terminadas):');

        $cases = (clone $rooms)
            ->selectRaw("case_slug, count(*) as total, sum(case when status = 'finished' then 1 else 0 end) as done")
            ->groupBy('case_slug')
            ->orderByDesc('total')
            ->get();

        foreach ($cases as $row) {
            $this->line(sprintf('  %-34s %3d / %3d', $row->case_slug, $row->total, $row->done));
        }

        return self::SUCCESS;
    }
}
