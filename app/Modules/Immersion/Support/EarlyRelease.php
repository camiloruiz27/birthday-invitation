<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Support\Facades\DB;

/**
 * "Ya lei toda la informacion": lets a table with fast readers skip the wait.
 *
 * After an envelope arrives every player can confirm they read it. When all the
 * players who have joined have confirmed, the next envelope is sent at once and
 * the game clock moves up by the time that was saved, so the gaps between the
 * rest of the envelopes stay what the case was written with.
 *
 * What a confirmation is worth. The marker is the id of the latest sent
 * envelope when the player pressed (players.ready_event_id). A player counts as
 * ready while that is still the latest one, so the next envelope turns the
 * whole table back to "not ready" without anything to reset.
 *
 * What is never skipped. The group that opens accusations (`unlock`) keeps its
 * minute: it ends the investigation and the ending depends on it. Nothing is
 * offered while the game is paused, finished or not started.
 *
 * The "next envelope" is the whole group of events that share the earliest
 * pending minute, as the cron would send them (several private clues can sit at
 * the same minute).
 *
 * What the screens read are "facts" (see facts()): the same for the whole table,
 * so with snapshots on they come from the cache like the rest of a player's page
 * and cost no query. What decides a release is always read from the database.
 */
class EarlyRelease
{
    public static function enabled(): bool
    {
        return (bool) config('immersion.early_release.enabled');
    }

    /**
     * What a player's screen needs: whether the button is offered, whether they
     * already pressed it, and how many of the table have.
     *
     * @return array{available: bool, confirmed: bool, ready: int, total: int}
     */
    public static function state(Player $player): array
    {
        return self::stateFor($player->game, $player);
    }

    /** Counts only, for the Game Master console (no titles: they are spoilers). */
    public static function tally(Game $game): array
    {
        $state = self::stateFor($game);

        return ['available' => $state['available'], 'ready' => $state['ready'], 'total' => $state['total']];
    }

    /**
     * What the screens need to know about a game, the same for every player:
     * the envelope everybody is asked to have read, whether the next group can be
     * brought forward, and who has joined and who confirmed.
     *
     * @return array{point: int|null, next_minute: int|null, next_has_unlock: bool, joined: array<int, array{id: int, ready: int|null}>}
     */
    public static function buildFacts(int $gameId): array
    {
        $pending = TimelineEvent::query()
            ->where('game_id', $gameId)
            ->pending()
            ->get(['id', 'type', 'trigger_offset_minutes']);

        $minute = $pending->min('trigger_offset_minutes');
        $next = $pending->where('trigger_offset_minutes', $minute);

        // The latest envelope sent is what everybody is asked to have read.
        $point = TimelineEvent::query()
            ->where('game_id', $gameId)
            ->whereNotNull('sent_at')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->value('id');

        // The players who count: the ones who have joined. In automatic mode the
        // owner plays from their own inbox and never sees the privacy notice, so
        // they count from the start; everybody else, once they accepted it.
        // Someone who never opens their link must not hold the table back.
        $joined = Player::query()
            ->where('game_id', $gameId)
            ->where(fn ($query) => $query->where('is_owner', true)->orWhereNotNull('privacy_accepted_at'))
            ->get(['id', 'ready_event_id'])
            ->map(fn (Player $player) => ['id' => (int) $player->id, 'ready' => $player->ready_event_id ? (int) $player->ready_event_id : null])
            ->values()
            ->all();

        return [
            'point' => $point ? (int) $point : null,
            'next_minute' => $pending->isEmpty() ? null : (int) $minute,
            'next_has_unlock' => $next->contains(fn (TimelineEvent $event) => $event->isUnlock()),
            'joined' => $joined,
        ];
    }

    /**
     * Records that this player read everything so far, and releases the next
     * envelope when they were the last one.
     *
     * @return string one of: confirmed, released, already, unavailable, not_joined
     */
    public static function confirm(Player $player): string
    {
        $game = $player->game;

        if (! $player->is_owner && $player->privacy_accepted_at === null) {
            return 'not_joined';
        }

        // From the database: this is a write, and it must act on the truth.
        $facts = self::facts($game, cached: false);

        if (! self::offered($game, $facts)) {
            return 'unavailable';
        }

        $point = $facts['point'];

        // Conditional update, and not a save(): a model event would raise every
        // revision stamp of the game for what is one bit of state.
        $changed = Player::query()
            ->whereKey($player->getKey())
            ->where(fn ($query) => $query->whereNull('ready_event_id')->orWhere('ready_event_id', '!=', $point))
            ->update(['ready_event_id' => $point]);

        if (! $changed) {
            return 'already';
        }

        GameRevision::bump((int) $game->id, GameRevision::READINESS);

        return self::release($game, $point) ? 'released' : 'confirmed';
    }

    /**
     * Moves the clock to the next envelope and sends what is due, if everybody
     * confirmed.
     *
     * The game row is locked and everything is read again inside: two players
     * pressing at the same instant must release one envelope, not two. Events
     * are claimed by DispatchTimelineEvent, so even a race with the cron sends
     * each one once.
     */
    private static function release(Game $game, int $point): bool
    {
        $released = DB::transaction(function () use ($game, $point) {
            $locked = Game::query()->lockForUpdate()->find($game->id);

            if (! $locked) {
                return null;
            }

            $facts = self::facts($locked, cached: false);

            if (! self::offered($locked, $facts) || $facts['point'] !== $point || ! self::everyoneReady($facts)) {
                return null;
            }

            $elapsedSeconds = $locked->started_at->diffInSeconds(now()) - (int) $locked->paused_seconds_total;
            $saved = max(0, $facts['next_minute'] * 60 - $elapsedSeconds);

            if ($saved > 0) {
                // The clock is `now - started_at - paused`: starting earlier is
                // the same as time having passed. Saving through the model lets
                // its hook replan the cron and raise the stamps.
                $locked->started_at = $locked->started_at->copy()->subSeconds($saved);
                $locked->save();
            }

            return $locked;
        });

        if (! $released) {
            return false;
        }

        TimelineRelease::dueFor($released);

        return true;
    }

    /** The button is offered: the game is running and there is a next group that may be brought forward. */
    private static function offered(?Game $game, array $facts): bool
    {
        return self::enabled()
            && $game !== null
            && $game->isRunning()
            && $game->started_at !== null
            && $facts['point'] !== null
            && $facts['next_minute'] !== null
            && ! $facts['next_has_unlock'];
    }

    private static function everyoneReady(array $facts): bool
    {
        $joined = collect($facts['joined']);

        return $joined->isNotEmpty() && $joined->where('ready', $facts['point'])->count() === $joined->count();
    }

    /**
     * @param  bool  $cached  read it the way a page does (from the snapshot when
     *                        snapshots are on); false always asks the database
     */
    private static function facts(Game $game, bool $cached): array
    {
        return $cached && GameSnapshot::enabled()
            ? GameSnapshot::readiness((int) $game->id)
            : self::buildFacts((int) $game->id);
    }

    /** @return array{available: bool, confirmed: bool, ready: int, total: int} */
    private static function stateFor(?Game $game, ?Player $player = null): array
    {
        $none = ['available' => false, 'confirmed' => false, 'ready' => 0, 'total' => 0];

        // Checked before the facts: a switched-off feature or a game that is not
        // running must not cost a single query.
        if (! self::enabled() || ! $game || ! $game->isRunning() || ! $game->started_at) {
            return $none;
        }

        $facts = self::facts($game, cached: true);

        if (! self::offered($game, $facts)) {
            return $none;
        }

        $ready = collect($facts['joined'])->where('ready', $facts['point']);

        return [
            'available' => true,
            'confirmed' => $player !== null && $ready->contains('id', (int) $player->id),
            'ready' => $ready->count(),
            'total' => count($facts['joined']),
        ];
    }
}
