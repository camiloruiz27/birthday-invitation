<?php

namespace App\Modules\Immersion\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * A change stamp per game, kept in the cache instead of the database.
 *
 * Why it exists: every open player page and Game Master console asked the
 * server "is there anything new?" every few seconds, and each of those requests
 * opened a MySQL connection. The shared host caps the database user at 500
 * new connections per hour, so a table of three players plus the Game Master
 * burned through it in minutes and every page returned 500 until the counter
 * reset.
 *
 * Anything that changes what a page shows bumps the stamp; the poll endpoint
 * (PollStateController) reads it from the cache without touching the database,
 * and the page only reloads its real data when the stamp moved.
 *
 * It is only a hint. A missing or lost stamp means "unknown", and unknown
 * always resolves to reloading — so a cleared cache (every deploy) or a failed
 * write costs one extra request, never a stale screen.
 */
class GameRevision
{
    /**
     * What a screen can be showing. Each page watches only the channels that
     * feed it: a question asked in an interrogation changes nothing in a
     * player's inbox, so the inbox must not reload because of it. With one
     * stamp per game, a busy interrogation made every open tab of the table
     * reload on every tick — the same flood of connections this exists to stop.
     */
    public const TIMELINE = 'timeline';

    public const INTERROGATION = 'interrogation';

    public const ACCUSATION = 'accusation';

    public const CHANNELS = [self::TIMELINE, self::INTERROGATION, self::ACCUSATION];

    /** Players are looked up by their token, which the poll cannot resolve without a query. */
    private const PLAYER_TTL_HOURS = 24;

    private const SESSION_KEY = 'immersion.pollable';

    /**
     * @param  string|array<int, string>  $channels  one channel, several, or all by default
     */
    public static function bump(?int $gameId, string|array $channels = self::CHANNELS): void
    {
        if (! $gameId) {
            return;
        }

        $channels = array_values(array_intersect(self::CHANNELS, (array) $channels));

        // Inside a transaction the stamp must move AFTER the commit: a client
        // that reloads in between would read the old rows, remember the new
        // stamp and never ask again.
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(fn () => self::write($gameId, $channels));

            return;
        }

        self::write($gameId, $channels);
    }

    /**
     * The stamps by channel, each created on first look. A missing one (cache
     * cleared by a deploy) must not read as "changed" on every poll forever:
     * seeding it makes the first answer after the loss stable.
     *
     * @return array<string, string>|null null when the cache cannot be read
     */
    public static function current(int $gameId): ?array
    {
        $stamps = [];

        try {
            foreach (self::CHANNELS as $channel) {
                $key = self::key($gameId, $channel);
                $value = Cache::get($key);

                if (! is_string($value)) {
                    Cache::add($key, (string) hrtime(true));
                    $value = Cache::get($key);
                }

                if (! is_string($value)) {
                    return null;
                }

                $stamps[$channel] = $value;
            }
        } catch (Throwable) {
            return null;
        }

        return $stamps;
    }

    public static function rememberPlayer(string $token, int $gameId): void
    {
        try {
            // add(): the page loads that call this are frequent, and rewriting
            // an unchanged file on each one is wasted disk work.
            Cache::add(self::playerKey($token), $gameId, now()->addHours(self::PLAYER_TTL_HOURS));
        } catch (Throwable) {
            // Best effort: without it the poll answers "unknown" and reloads.
        }
    }

    public static function gameIdForPlayer(string $token): ?int
    {
        try {
            $gameId = Cache::get(self::playerKey($token));
        } catch (Throwable) {
            return null;
        }

        return is_int($gameId) ? $gameId : null;
    }

    /**
     * The Game Master console has no token. Whoever just rendered a game's
     * page already passed the real authorization (auth + policy, with the
     * database), so the poll only has to check it was them — a session list,
     * no query.
     */
    public static function allowConsole(Request $request, int $gameId): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $allowed = (array) $request->session()->get(self::SESSION_KEY, []);
        $allowed[$gameId] = true;

        $request->session()->put(self::SESSION_KEY, $allowed);
    }

    public static function consoleAllowed(Request $request, int $gameId): bool
    {
        return $request->hasSession()
            && isset(((array) $request->session()->get(self::SESSION_KEY, []))[$gameId]);
    }

    /** @param array<int, string> $channels */
    private static function write(int $gameId, array $channels): void
    {
        try {
            foreach ($channels as $channel) {
                // Unique on every write and no read-modify-write, so two bumps
                // at once can never collapse into the same value.
                Cache::forever(self::key($gameId, $channel), (string) hrtime(true));
            }
        } catch (Throwable) {
            // A failed hint must never fail the game action that triggered it.
        }
    }

    private static function key(int $gameId, string $channel): string
    {
        return "immersion:rev:{$gameId}:{$channel}";
    }

    private static function playerKey(string $token): string
    {
        return 'immersion:pg:'.sha1($token);
    }
}
