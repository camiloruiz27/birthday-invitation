<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Resuelve a quien le corresponde un evento de la linea de tiempo segun su
 * delivery_mode. Compartido entre el job que despacha el evento por primera
 * vez y las acciones de reintento del GM, para que ambos usen exactamente
 * la misma logica.
 */
class TimelineRecipients
{
    /**
     * @return Collection<int, \App\Modules\Immersion\Models\Player>
     */
    public static function resolve(TimelineEvent $event): Collection
    {
        $players = $event->game->players;

        return match ($event->delivery_mode) {
            'random_player' => self::resolveRandomPlayer($event, $players),
            'role_slug' => $players->where('role_slug', $event->target_role_slug)->values(),
            default => $players,
        };
    }

    /**
     * A private clue goes to one player at random — but a case usually has
     * several, and they are meant to be spread across the table, not stacked
     * on whoever the dice favour. So the draw is among the players who have
     * received the FEWEST private clues so far: everyone gets one before
     * anyone gets a second.
     *
     * The game row is locked while choosing, so two private clues firing in
     * the same minute (two queued jobs at once) cannot both read "nobody has
     * one yet" and land on the same person.
     */
    private static function resolveRandomPlayer(TimelineEvent $event, Collection $players): Collection
    {
        if ($event->delivered_to_player_id) {
            $chosen = $players->firstWhere('id', $event->delivered_to_player_id);

            return $chosen ? collect([$chosen]) : collect();
        }

        if ($players->isEmpty()) {
            return collect();
        }

        $chosen = DB::transaction(function () use ($event, $players) {
            Game::query()->whereKey($event->game_id)->lockForUpdate()->first();

            $received = TimelineEvent::query()
                ->where('game_id', $event->game_id)
                ->where('delivery_mode', 'random_player')
                ->whereNotNull('delivered_to_player_id')
                ->whereKeyNot($event->getKey())
                ->pluck('delivered_to_player_id')
                ->countBy();

            $fewest = $players->min(fn ($player) => $received->get($player->id, 0));

            return $players
                ->filter(fn ($player) => $received->get($player->id, 0) === $fewest)
                ->random();
        });

        $event->delivered_to_player_id = $chosen->id;

        return collect([$chosen]);
    }
}
