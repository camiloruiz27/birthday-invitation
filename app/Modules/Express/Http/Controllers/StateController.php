<?php

namespace App\Modules\Express\Http\Controllers;

use App\Modules\Express\Support\PublicState;
use App\Modules\Express\Support\RoomService;
use App\Modules\Express\Support\RoomSnapshot;
use Illuminate\Http\JsonResponse;

/**
 * The poll: what one player's screen should show right now.
 *
 * Served from the room's cached snapshot with no database query (the only
 * exception is the single request that ends a round). It is registered
 * outside the `web` middleware group on purpose: no session is opened or
 * touched, so a fair full of phones polling every few seconds costs no session
 * files and no connections.
 */
class StateController
{
    public function show(string $token, RoomService $rooms): JsonResponse
    {
        $resolved = RoomSnapshot::forToken($token);

        abort_if($resolved === null, 404);

        $snapshot = $rooms->settle($resolved['snapshot']);

        return response()
            ->json(PublicState::build($snapshot, $resolved['player_id']))
            ->header('Cache-Control', 'no-store');
    }
}
