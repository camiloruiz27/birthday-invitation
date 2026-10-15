<?php

namespace App\Http\Middleware;

use App\Modules\Platform\Support\ConnectionPool;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pins a player's request to one of the MySQL users in the pool (see
 * ConnectionPool for why, and for when the pool exists at all).
 *
 * It only changes which connection is the DEFAULT for this request, before
 * anything has touched the database, so every query, transaction, lock, queued
 * job and relation of the request uses the same one and nothing else in the
 * code has to know. It does nothing unless extra users are configured.
 *
 * Listed LAST among the global middleware on purpose: terminable middleware
 * terminate in order, and the ones that look at the request's connection after
 * the response (the connection counter, the reuse probe, the transaction reset)
 * must still see the pool connection the request used, not the main one.
 */
class UseConnectionPool
{
    /**
     * The default connection to put back after the response. Static because the
     * kernel builds a fresh middleware object for terminate(), so an instance
     * property set in handle() would be gone by then.
     */
    private static ?string $original = null;

    public function handle(Request $request, Closure $next)
    {
        self::$original = null;

        if (ConnectionPool::enabled() && ($key = ConnectionPool::keyFor($request->path())) !== null) {
            $chosen = ConnectionPool::pick($key);

            if ($chosen !== DB::getDefaultConnection()) {
                self::$original = DB::getDefaultConnection();

                DB::setDefaultConnection($chosen);
            }
        }

        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (self::$original !== null) {
            DB::setDefaultConnection(self::$original);

            self::$original = null;
        }
    }
}
