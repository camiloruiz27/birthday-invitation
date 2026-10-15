<?php

namespace App\Http\Middleware;

use App\Modules\Platform\Support\ConnectionBudget;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Counts the web requests that opened a database connection, so the log warns
 * before the host's hourly allowance runs out (see ConnectionBudget).
 *
 * Only requests that ran at least one query count: that is what opens a
 * connection, and a request answered from the cache (the change-polling
 * endpoints, a cached public page) costs none. It reads the answer in
 * terminate(), after the response has gone out, and writes only to the cache.
 */
class TrackConnectionBudget
{
    /** Set by the query listener below for the request in flight. */
    private static bool $queried = false;

    /**
     * The application the query listener was registered on. Tracked by
     * instance rather than with a plain flag: a long-lived process that boots
     * a fresh application (every test does) would otherwise believe it was
     * already listening, and the new application has no listener at all.
     */
    private static ?object $listeningOn = null;

    public function handle(Request $request, Closure $next)
    {
        if (self::$listeningOn !== app()) {
            self::$listeningOn = app();

            DB::listen(function () {
                self::$queried = true;
            });
        }

        self::$queried = false;

        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (self::$queried) {
            ConnectionBudget::hit(self::label($request));
        }

        self::$queried = false;
    }

    /**
     * What to blame: the route's name, or its URI template for the ones without
     * a name (never the real URL, which carries a player's token and would be
     * a different label for every player).
     */
    private static function label(Request $request): string
    {
        $route = $request->route();

        if (! $route) {
            return 'unrouted';
        }

        return $route->getName() ?: $route->uri();
    }
}
