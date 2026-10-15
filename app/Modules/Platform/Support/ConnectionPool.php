<?php

namespace App\Modules\Platform\Support;

use Illuminate\Support\Facades\Config;

/**
 * Spreads the player-facing traffic over several MySQL users that share one
 * database, so each user's hourly allowance of new connections is spent in
 * parallel instead of one after another.
 *
 * It exists for one situation: the shared host caps NEW connections per MySQL
 * USER per hour, and the cap is what stops a table from playing. If (and only
 * if) the host counts per user, a second user on the same database is a second
 * allowance. Nothing here is on unless database.pool.users has an entry, and
 * until the host has confirmed how it counts, nobody should add one.
 *
 * Why a connection per REQUEST and not per model: a request that spends a
 * credit, saves a message and claims a suspect does it inside one
 * DB::transaction, with row locks. Putting some models on another connection
 * would silently take their writes out of that transaction. So the whole
 * request runs on one connection — the default one is switched before anything
 * touches the database (UseConnectionPool) — and the rest of the code does not
 * know the pool exists. The requests of one table still spread, because the
 * choice is made from each player's own token.
 *
 * Everything shares one database, so ids, foreign keys, cache keys and joins
 * mean the same thing whichever user a request used.
 */
class ConnectionPool
{
    /** The prefix of the connections this class defines. */
    private const PREFIX = 'mysql_pool_';

    /**
     * Defines one connection per configured pool user, cloned from the main
     * one. Called once from the platform provider; calling it again (a test
     * changes the config) redefines them.
     */
    public static function register(): void
    {
        // The default connection is switched per request, so remember which one
        // is the main one before anything switches it.
        if (! Config::has('database.pool.base')) {
            Config::set('database.pool.base', Config::get('database.default'));
        }

        $base = self::baseName();
        $template = (array) Config::get("database.connections.{$base}", []);

        foreach ((array) Config::get('database.pool.users', []) as $index => $user) {
            if (! filled($user['username'] ?? null)) {
                continue;
            }

            Config::set('database.connections.'.self::PREFIX.($index + 1), array_merge($template, [
                // A URL would carry the main user's credentials over these.
                'url' => null,
                'username' => $user['username'],
                'password' => $user['password'] ?? '',
            ]));
        }
    }

    public static function enabled(): bool
    {
        return count(self::names()) > 1;
    }

    /**
     * The connection a request keyed by $key should use. The same key always
     * maps to the same connection, so one player's requests do not hop between
     * users (which would spoil connection reuse when persistence is on).
     */
    public static function pick(string $key): string
    {
        $names = self::names();

        if (count($names) < 2) {
            return self::baseName();
        }

        return $names[crc32($key) % count($names)];
    }

    /**
     * What a request is keyed by, or null when it is not player traffic.
     *
     * Only the pages a player opens: they are the volume (every click and every
     * question), while the Game Master, payments and the cron are few and stay
     * on the main user, whose allowance is kept for them.
     */
    public static function keyFor(string $path): ?string
    {
        if (preg_match('#^jugador/([^/]+)#', ltrim($path, '/'), $match)) {
            return $match[1];
        }

        return null;
    }

    /**
     * The connections in the rotation, in a stable order.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        $names = [];

        foreach (array_keys((array) Config::get('database.pool.users', [])) as $index) {
            $name = self::PREFIX.($index + 1);

            if (Config::has("database.connections.{$name}")) {
                $names[] = $name;
            }
        }

        if (! Config::get('database.pool.spare_default') || $names === []) {
            array_unshift($names, self::baseName());
        }

        return $names;
    }

    /** The main connection, whichever one the request has switched to. */
    public static function baseName(): string
    {
        return (string) Config::get('database.pool.base', Config::get('database.default'));
    }
}
