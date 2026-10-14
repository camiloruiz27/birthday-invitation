<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;

/**
 * What is new for a player, said inside the platform.
 *
 * The game used to tell people by email that an envelope had arrived, and the
 * email was the ONLY way to learn some things (the "accusations are open"
 * envelope is not in the inbox at all). With the email gone, the platform has
 * to say it itself on whatever screen the player is looking at — not only in the
 * inbox, which is the one place that already refreshed.
 *
 * Nothing is stored: every notice is a fact the game already holds (an event
 * that was sent, the ending that was revealed), so there is no table to keep in
 * step and nothing to clean up. Which ones a person has already seen lives in
 * their own browser (see usePlayerNotices.js), the same way the inbox already
 * remembers which envelopes they opened.
 *
 * Deliberately light: a few columns, never the body of an envelope. It rides
 * on every player page.
 */
class PlayerNotices
{
    /**
     * @return array{
     *     items: array<int, array{key: string, kind: string, title: string, event_id: int|null, sent_at: string|null}>,
     *     accusations_open: bool,
     *     accused: bool,
     *     ending_revealed: bool
     * }
     */
    public static function for(Player $player): array
    {
        $game = $player->game;
        $items = [];

        $events = $player->eventsForMeQuery()
            ->get(['id', 'type', 'title', 'sent_at', 'cta_interrogation']);

        foreach ($events as $event) {
            if (in_array($event->type, ['email', 'audio_email'], true)) {
                $items[] = self::item("e{$event->id}", 'envelope', $event->title, $event->id, $event->sent_at);
            }

            // The envelope that introduces the people is the moment the
            // suspects open: worth its own notice, with its own destination.
            if ($event->cta_interrogation && $game->interrogation_enabled) {
                $items[] = self::item("i{$event->id}", 'interrogation', 'Ya puedes interrogar a los sospechosos', $event->id, $event->sent_at);
            }

            if ($event->isUnlock()) {
                $items[] = self::item("a{$event->id}", 'accusation', 'Se habilitó la fase de acusaciones', $event->id, $event->sent_at);
            }
        }

        $accusation = $player->accusation()->first(['id', 'epilogue_status']);

        if ($game->ending_revealed_at) {
            $items[] = self::item('solution', 'solution', 'El caso se cerró: ya está la solución', null, $game->ending_revealed_at);
        }

        if ($game->ending_type === Game::ENDING_EPILOGUE && $accusation?->epilogue_status === 'ready') {
            $items[] = self::item('epilogue', 'epilogue', 'Tienes un mensaje de la persona que acusaste', null, $game->ending_revealed_at);
        }

        return [
            'items' => $items,
            'accusations_open' => $game->accusationsUnlocked(),
            'accused' => $accusation !== null,
            'ending_revealed' => $game->ending_revealed_at !== null,
        ];
    }

    /**
     * @return array{key: string, kind: string, title: string, event_id: int|null, sent_at: string|null}
     */
    private static function item(string $key, string $kind, string $title, ?int $eventId, $sentAt): array
    {
        return [
            'key' => $key,
            'kind' => $kind,
            'title' => $title,
            'event_id' => $eventId,
            'sent_at' => $sentAt?->toISOString(),
        ];
    }
}
