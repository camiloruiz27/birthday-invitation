<?php

namespace App\Modules\Express\Support;

use App\Modules\Express\Models\Player;
use App\Modules\Express\Models\Room;
use App\Modules\Platform\Support\Ops;

/**
 * Everything the players of a room read, kept in cache.
 *
 * Hostinger allows 500 database connections an hour for the whole site, and a
 * fair crowd polling every few seconds would spend that in minutes. So the
 * database is the source of truth for WRITES only (a join, a question, an
 * accusation), and every read — the poll, the page — is served from this
 * snapshot with no query at all. A writer rebuilds it after committing; if it
 * is missing (a fresh deploy, an evicted file) the first reader rebuilds it
 * from the database once.
 *
 * It lives in the "ops" cache store, which `cache:clear` and deploys leave
 * alone: wiping it mid-fair would send every phone to the database at once.
 *
 * The snapshot holds secrets (player tokens, everyone's answers): it is only
 * ever read by PublicState, which filters it down to what one player may see.
 */
final class RoomSnapshot
{
    private const TOKEN_TTL = 86400;

    /**
     * The room and the player a token belongs to, or null for an unknown token.
     *
     * @return array{snapshot: array<string, mixed>, player_id: int}|null
     */
    public static function forToken(string $token): ?array
    {
        $roomId = Ops::store()->get(self::tokenKey($token));

        if ($roomId === null) {
            $player = Player::query()->where('token', $token)->first(['id', 'room_id']);

            if ($player === null) {
                return null;
            }

            $roomId = (int) $player->room_id;
            Ops::store()->put(self::tokenKey($token), $roomId, self::TOKEN_TTL);
        }

        $snapshot = self::find((int) $roomId);

        if ($snapshot === null) {
            return null;
        }

        foreach ($snapshot['players'] as $player) {
            if ($player['token'] === $token) {
                return ['snapshot' => $snapshot, 'player_id' => (int) $player['id']];
            }
        }

        // The token was cached for a room that no longer lists it.
        Ops::store()->forget(self::tokenKey($token));

        return null;
    }

    /** @return array<string, mixed>|null */
    public static function find(int $roomId): ?array
    {
        $cached = Ops::store()->get(self::key($roomId));

        return is_array($cached) ? $cached : self::rebuild($roomId);
    }

    /**
     * Reads the room from the database and caches it. Called by every writer
     * right after its transaction commits.
     *
     * @return array<string, mixed>|null
     */
    public static function rebuild(int $roomId): ?array
    {
        $room = Room::query()->find($roomId);

        if ($room === null) {
            return null;
        }

        $players = $room->players()->get();
        $next = $room->next_room_id ? Room::query()->whereKey($room->next_room_id)->value('code') : null;

        $snapshot = [
            'id' => (int) $room->id,
            'rev' => (int) $room->rev,
            'code' => (string) $room->code,
            'event_slug' => (string) $room->event_slug,
            'case_slug' => (string) $room->case_slug,
            'status' => (string) $room->status,
            'gm_player_id' => $room->gm_player_id !== null ? (int) $room->gm_player_id : null,
            'started_at_ms' => $room->started_at_ms !== null ? (int) $room->started_at_ms : null,
            'next_code' => $next,
            'players' => $players->map(fn (Player $p) => [
                'id' => (int) $p->id,
                'token' => (string) $p->token,
                'nickname' => (string) $p->nickname,
                'audience' => (string) $p->audience,
                'is_gm' => (bool) $p->is_gm,
            ])->all(),
            'shares' => $room->shares()->get(['evidence_id', 'player_id'])
                ->mapWithKeys(fn ($s) => [(string) $s->evidence_id => (int) $s->player_id])->all(),
            'questions' => $room->questions()->get()->map(fn ($q) => [
                'id' => (int) $q->id,
                'player_id' => (int) $q->player_id,
                'suspect_id' => (string) $q->suspect_id,
                'topic_id' => $q->topic_id !== null ? (string) $q->topic_id : null,
                'question' => (string) $q->question,
                'answer' => $q->answer !== null ? (string) $q->answer : null,
            ])->all(),
            'accusations' => $room->accusations()->get()->mapWithKeys(fn ($a) => [(int) $a->player_id => [
                'who' => (string) $a->who_id,
                'how' => (int) $a->how_index,
                'why' => (int) $a->why_index,
                'submitted_ms' => (int) $a->submitted_ms,
                'scores' => $a->score_total === null ? null : [
                    'who' => (int) $a->score_who,
                    'how' => (int) $a->score_how,
                    'why' => (int) $a->score_why,
                    'total' => (int) $a->score_total,
                ],
            ]])->all(),
        ];

        $cached = Ops::store()->get(self::key($roomId));

        // A slower rebuild must not overwrite a newer one.
        if (! is_array($cached) || $cached['rev'] <= $snapshot['rev']) {
            Ops::store()->put(self::key($roomId), $snapshot, (int) config('express.snapshot_ttl'));
        }

        foreach ($snapshot['players'] as $player) {
            Ops::store()->put(self::tokenKey($player['token']), $roomId, self::TOKEN_TTL);
        }

        return $snapshot;
    }

    public static function forget(int $roomId): void
    {
        Ops::store()->forget(self::key($roomId));
    }

    private static function key(int $roomId): string
    {
        return "express:room:{$roomId}";
    }

    private static function tokenKey(string $token): string
    {
        return 'express:pg:'.sha1($token);
    }
}
