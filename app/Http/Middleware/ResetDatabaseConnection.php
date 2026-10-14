<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Makes persistent database connections safe to turn on (DB_PERSISTENT).
 *
 * A persistent connection outlives the request, and PDO does not reset it: a
 * request killed in the middle of a transaction (a timeout, a fatal error)
 * would leave that transaction — and its row locks — open for whichever request
 * the same PHP worker serves next. After every response this checks for one,
 * rolls it back and drops the connection so the next request starts clean.
 *
 * With ordinary connections it never finds anything to do and costs nothing:
 * transactionLevel() is a counter, not a query.
 */
class ResetDatabaseConnection
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        // Only when connections outlive the request. Otherwise PHP closes the
        // connection at the end of the request anyway, and a test that wraps
        // itself in a transaction would have it rolled back from under it.
        if (! config('database.persistent')) {
            return;
        }

        try {
            if (DB::transactionLevel() > 0) {
                Log::warning('db_transaction_left_open', [
                    'path' => $request->path(),
                    'level' => DB::transactionLevel(),
                    'hint' => 'Rolled back and disconnected so a persistent connection cannot hand it to the next request.',
                ]);

                DB::rollBack(0);
                DB::disconnect();
            }
        } catch (Throwable) {
            // Best effort: the goal is a clean connection, and disconnecting
            // is what gets it.
            try {
                DB::disconnect();
            } catch (Throwable) {
            }
        }
    }
}
