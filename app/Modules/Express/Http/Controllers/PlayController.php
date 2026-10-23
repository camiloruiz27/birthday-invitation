<?php

namespace App\Modules\Express\Http\Controllers;

use App\Modules\Express\Models\Player;
use App\Modules\Express\Support\ExpressException;
use App\Modules\Express\Support\PublicState;
use App\Modules\Express\Support\RoomService;
use App\Modules\Express\Support\RoomSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A player's screen and the moves they can make from it.
 *
 * The page itself is served from the room's cached snapshot (no queries). The
 * moves are JSON: they answer `{ok: true, state}` with the fresh state, or
 * `{ok: false, code, message}` — a refused move is an ordinary outcome of a
 * game, not an error page.
 */
class PlayController
{
    public function __construct(private readonly RoomService $rooms)
    {
    }

    public function show(string $token): Response
    {
        $state = $this->state($token) ?? abort(404);

        return Inertia::render('Express/Room', [
            'state' => $state,
            'token' => $token,
        ]);
    }

    public function start(string $token): JsonResponse
    {
        return $this->act($token, fn (Player $player) => $this->rooms->start($player));
    }

    public function share(Request $request, string $token): JsonResponse
    {
        $input = $this->input($request, ['evidence_id' => ['required', 'string', 'max:8']]);

        return $input instanceof JsonResponse
            ? $input
            : $this->act($token, fn (Player $player) => $this->rooms->share($player, $input['evidence_id']));
    }

    public function ask(Request $request, string $token): JsonResponse
    {
        $input = $this->input($request, [
            'suspect_id' => ['required', 'string', 'max:40'],
            'question' => ['required', 'string', 'max:'.(int) config('express.questions.max_length')],
            'topic_id' => ['nullable', 'string', 'max:40'],
        ]);

        if ($input instanceof JsonResponse) {
            return $input;
        }

        $result = null;

        $response = $this->act($token, function (Player $player) use ($input, &$result) {
            $result = $this->rooms->ask($player, $input['suspect_id'], $input['question'], $input['topic_id'] ?? null);
        });

        if ($result !== null) {
            $payload = $response->getData(true);
            $payload['result'] = $result;
            $response->setData($payload);
        }

        return $response;
    }

    public function accuse(Request $request, string $token): JsonResponse
    {
        $input = $this->input($request, [
            'who' => ['required', 'string', 'max:40'],
            'how' => ['required', 'string', 'max:40'],
            'why' => ['required', 'string', 'max:40'],
        ]);

        return $input instanceof JsonResponse
            ? $input
            : $this->act($token, fn (Player $player) => $this->rooms->accuse($player, $input));
    }

    /** Opens the next round for the group (the first to tap creates it). */
    public function nextCreate(Request $request, string $token): JsonResponse
    {
        $input = $this->input($request, ['case_slug' => ['nullable', 'string', 'max:80']]);

        return $input instanceof JsonResponse
            ? $input
            : $this->act($token, fn (Player $player) => $this->rooms->createNext($player, $input['case_slug'] ?? null));
    }

    /** Moves the player into the next round and says where to go. */
    public function nextJoin(string $token): JsonResponse
    {
        try {
            $player = Player::query()->where('token', $token)->firstOrFail();
            $next = $this->rooms->joinNext($player);
        } catch (ExpressException $e) {
            return $this->refused($e);
        }

        return response()->json([
            'ok' => true,
            'redirect' => route('express.room', $next->token),
        ]);
    }

    /**
     * Runs a move for the player behind the token and answers with their
     * refreshed state.
     */
    private function act(string $token, callable $move): JsonResponse
    {
        try {
            $player = Player::query()->where('token', $token)->firstOrFail();
            $move($player);
        } catch (ExpressException $e) {
            return $this->refused($e);
        }

        return response()->json(['ok' => true, 'state' => $this->state($token)])
            ->header('Cache-Control', 'no-store');
    }

    private function refused(ExpressException $e): JsonResponse
    {
        return response()->json(['ok' => false, 'code' => $e->key, 'message' => $e->getMessage()], $e->status);
    }

    /**
     * @param  array<string, array<int, string>>  $rules
     * @return array<string, mixed>|JsonResponse
     */
    private function input(Request $request, array $rules): array|JsonResponse
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'code' => 'invalid',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        return $validator->validated();
    }

    /** @return array<string, mixed>|null */
    private function state(string $token): ?array
    {
        $resolved = RoomSnapshot::forToken($token);

        if ($resolved === null) {
            return null;
        }

        $snapshot = $this->rooms->settle($resolved['snapshot']);

        return PublicState::build($snapshot, $resolved['player_id']);
    }
}
