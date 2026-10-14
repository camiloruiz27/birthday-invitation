<?php

namespace App\Modules\Immersion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\GameRevision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Has anything changed?" for the pages that keep themselves up to date.
 *
 * It must never touch the database: the whole point is that a table of players
 * watching their screens costs no MySQL connections (the host caps them per
 * hour). That is why the routes take the raw token / id instead of a bound
 * model and sit outside the `auth` middleware — both would run a query. A test
 * asserts the zero-query guarantee.
 *
 * `r` holds the game's change stamps by channel (timeline, interrogation,
 * accusation; see GameRevision), or null when they cannot be told (a cleared
 * cache, a token it has not seen): the page treats null as "reload".
 */
class PollStateController extends Controller
{
    public function player(string $token): JsonResponse
    {
        $gameId = GameRevision::gameIdForPlayer($token);

        return $this->stamp($gameId ? GameRevision::current($gameId) : null);
    }

    public function console(Request $request, int $game): JsonResponse
    {
        // The console was authorized (with the database) when its page was
        // rendered; this only checks it is the same browser session.
        if (! GameRevision::consoleAllowed($request, $game)) {
            return $this->stamp(null);
        }

        return $this->stamp(GameRevision::current($game));
    }

    /** @param array<string, string>|null $revision */
    private function stamp(?array $revision): JsonResponse
    {
        return response()->json(['r' => $revision])
            ->header('Cache-Control', 'no-store, private');
    }
}
