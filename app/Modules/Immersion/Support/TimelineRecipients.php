<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Support\Collection;

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

    private static function resolveRandomPlayer(TimelineEvent $event, Collection $players): Collection
    {
        if ($event->delivered_to_player_id) {
            $chosen = $players->firstWhere('id', $event->delivered_to_player_id);

            return $chosen ? collect([$chosen]) : collect();
        }

        if ($players->isEmpty()) {
            return collect();
        }

        $chosen = $players->random();
        $event->delivered_to_player_id = $chosen->id;

        return collect([$chosen]);
    }
}
