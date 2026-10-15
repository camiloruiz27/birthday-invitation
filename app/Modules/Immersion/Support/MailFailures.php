<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Platform\Support\Ops;
use Throwable;

/**
 * The envelopes that could not be emailed, by game, for the Game Master's
 * console.
 *
 * A mail that never left used to vanish: the event was already marked sent, the
 * failure was only reported, and a table waited on an email that was not coming
 * with nobody told. The envelope itself is never lost — it is in the player's
 * inbox on the site — so what the Game Master needs is to know the email did not
 * go and to be able to send it again.
 *
 * Kept in the cache, not the database: it is a notice, it only has to outlive
 * the game night, and reading it must not cost a connection.
 */
class MailFailures
{
    private const TTL_DAYS = 3;

    public static function add(int $gameId, int $eventId, int $playerId, string $label): void
    {
        try {
            $all = self::all($gameId);
            $all["{$eventId}:{$playerId}"] = ['event_id' => $eventId, 'player_id' => $playerId, 'label' => $label];

            Ops::store()->put(self::key($gameId), $all, now()->addDays(self::TTL_DAYS));
        } catch (Throwable) {
            // A notice that cannot be stored must not hide the failure itself,
            // which is logged by the caller.
        }
    }

    /** Forget one failure, or every failure of the game when no event is given. */
    public static function clear(int $gameId, ?int $eventId = null, ?int $playerId = null): void
    {
        try {
            if ($eventId === null) {
                Ops::store()->forget(self::key($gameId));

                return;
            }

            $all = self::all($gameId);
            unset($all["{$eventId}:{$playerId}"]);

            $all === []
                ? Ops::store()->forget(self::key($gameId))
                : Ops::store()->put(self::key($gameId), $all, now()->addDays(self::TTL_DAYS));
        } catch (Throwable) {
            // Same as add(): a notice, never a reason to fail.
        }
    }

    /** @return array<string, array{event_id: int, player_id: int, label: string}> */
    public static function all(int $gameId): array
    {
        try {
            return (array) Ops::store()->get(self::key($gameId), []);
        } catch (Throwable) {
            return [];
        }
    }

    private static function key(int $gameId): string
    {
        return "immersion:mail:failed:{$gameId}";
    }
}
