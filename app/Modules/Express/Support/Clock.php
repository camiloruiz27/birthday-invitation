<?php

namespace App\Modules\Express\Support;

/**
 * The one clock Express runs on.
 *
 * A round is 300 seconds long, so everything is measured in milliseconds since
 * the host pressed "Iniciar" and nothing is scheduled: the state a player
 * sees is simply a function of "now". No cron, no timeline rows, nothing to
 * fall behind.
 */
final class Clock
{
    public static function nowMs(): int
    {
        return (int) now()->getPreciseTimestamp(3);
    }

    /** Seconds (with decimals) since the round started. */
    public static function elapsed(int $startedAtMs, ?int $nowMs = null): float
    {
        return max(0, (($nowMs ?? self::nowMs()) - $startedAtMs) / 1000);
    }
}
