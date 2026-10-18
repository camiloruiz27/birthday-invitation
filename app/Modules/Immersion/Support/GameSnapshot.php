<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * What every player of a game reads, kept ready so that reading it costs no
 * database connection.
 *
 * A player's pages are almost all reading: the envelopes already sent, who has
 * claimed which suspect, what the case has opened. That state is the SAME for
 * every player at the table and changes only when something happens (an event
 * goes out, someone asks a question). Yet every click of every player used to
 * rebuild it from the database, one connection each, against a host that allows
 * 500 an hour.
 *
 * So the state is kept as a few PARTS in the cache, each one validated against
 * the change stamps (GameRevision) that move when what it holds could have
 * changed, and refreshed by whoever WRITES: the request that asked the question
 * (or the tick that sent the event) already has a database connection open, so
 * after it has answered it rebuilds the part it just invalidated, and every
 * reader after it finds the part ready. The expected cost of reading is zero
 * connections; the cost of a change is the queries of one rebuild, on a
 * connection that was open anyway.
 *
 * Parts keep the RAW rows (getAttributes) and are turned back into models with
 * newFromBuilder, so the code that renders them is the very code that renders a
 * database result: casts, hidden fields and serialization behave identically.
 *
 * A part is also never trusted past SNAPSHOT_TTL, whatever its stamps say: a
 * stamp is a hint (a write that forgot to move it, a lost cache), and this bounds
 * how long a mistake could survive.
 *
 * Off by default (IMMERSION_SNAPSHOTS): everything falls back to the database.
 */
class GameSnapshot
{
    /** Seconds a part may be served before it is rebuilt regardless of its stamps. */
    public const TTL_SECONDS = 300;

    /** How long a part stays in the cache untouched (a finished game's files age out). */
    private const KEEP_HOURS = 8;

    /** @var array<int, array<string, true>> game id => channels moved during this request */
    private static array $pending = [];

    /** @var array<int, true> session ids whose chat moved during this request */
    private static array $pendingSessions = [];

    /** The application the pending work belongs to (a long-lived process may boot several). */
    private static ?object $owner = null;

    private static bool $hooked = false;

    public static function enabled(): bool
    {
        return (bool) config('immersion.snapshots.enabled');
    }

    // --- reading ------------------------------------------------------------------

    /**
     * The game, with what its pages ask the database about already answered
     * (is the interrogation open, is the accusation unlocked, which evidence
     * has been delivered, how many accusations are pending).
     */
    public static function game(int $gameId): ?Game
    {
        $meta = self::part('meta', $gameId, [GameRevision::TIMELINE, GameRevision::ACCUSATION], fn () => self::buildMeta($gameId));

        if (! is_array($meta) || ! isset($meta['attributes'])) {
            return null;
        }

        $game = (new Game)->newFromBuilder($meta['attributes']);
        $game->prime($meta['facts']);

        return $game;
    }

    /**
     * Every event already sent, light: no body. Everyone's, whatever it is
     * addressed to — the caller filters (see visibleTo).
     *
     * @return array<int, array<string, mixed>> raw attributes
     */
    public static function timeline(int $gameId): array
    {
        return (array) self::part('timeline', $gameId, [GameRevision::TIMELINE], function () use ($gameId) {
            return TimelineEvent::query()
                ->where('game_id', $gameId)
                ->whereNotNull('sent_at')
                ->orderBy('trigger_offset_minutes')
                ->orderBy('sent_at')
                ->get(['id', 'game_id', 'type', 'title', 'sent_at', 'trigger_offset_minutes', 'cta_interrogation', 'delivery_mode', 'delivered_to_player_id', 'target_role_slug', 'source_file', 'audio_path'])
                ->map(fn (TimelineEvent $event) => $event->getAttributes())
                ->values()
                ->all();
        });
    }

    /**
     * What the "ya lei toda la informacion" button needs, the same for the whole
     * table (see EarlyRelease::buildFacts). It moves with the timeline (a new
     * envelope resets everybody) and with the readiness stamp (somebody pressed,
     * or accepted the privacy notice and so started to count).
     *
     * @return array{point: int|null, next_minute: int|null, next_has_unlock: bool, joined: array<int, array{id: int, ready: int|null}>}
     */
    public static function readiness(int $gameId): array
    {
        return (array) self::part('readiness', $gameId, [GameRevision::TIMELINE, GameRevision::READINESS], fn () => EarlyRelease::buildFacts($gameId));
    }

    /**
     * What the inbox shows of each envelope: the rendered body and the gallery.
     * Heavy (the whole text of every sent envelope), so it is its own part and
     * only the inbox reads it.
     *
     * @return array<int, array{body_html: mixed, gallery: mixed}> by event id
     */
    public static function inbox(Game $game): array
    {
        return (array) self::part('inbox', (int) $game->id, [GameRevision::TIMELINE], function () use ($game) {
            $case = $game->caseDefinition();
            $rendered = [];

            TimelineEvent::query()
                ->where('game_id', $game->id)
                ->whereNotNull('sent_at')
                ->whereIn('type', ['email', 'audio_email'])
                ->get()
                ->each(function (TimelineEvent $event) use ($case, &$rendered) {
                    $rendered[$event->id] = [
                        'body_html' => $case->renderEventBody($event->source_file, $event->body_markdown, (int) $event->trigger_offset_minutes),
                        'gallery' => $case->galleryFor($event->source_file),
                    ];
                });

            return $rendered;
        });
    }

    /**
     * Who has claimed which suspect and how far each one has got.
     *
     * @return array<int, array{session: array<string, mixed>, player: array<string, mixed>|null}>
     */
    public static function board(int $gameId): array
    {
        return (array) self::part('board', $gameId, [GameRevision::INTERROGATION], function () use ($gameId) {
            return InterrogationSession::query()
                ->where('game_id', $gameId)
                ->with('player:id,name')
                ->get()
                ->map(fn (InterrogationSession $session) => [
                    'session' => $session->getAttributes(),
                    'player' => $session->player?->getAttributes(),
                ])
                ->values()
                ->all();
        });
    }

    /**
     * One suspect's transcript, valid for as long as that session's own stamp
     * does not move (a question elsewhere does not invalidate it).
     *
     * @return array<int, array<string, mixed>> raw message rows
     */
    public static function chat(int $sessionId, int $gameId): array
    {
        $stamp = GameRevision::sessionStamp($sessionId);

        return (array) self::partWithStamp("chat:{$sessionId}", $gameId, $stamp, function () use ($sessionId) {
            return InterrogationMessage::query()
                ->where('session_id', $sessionId)
                ->orderBy('created_at')
                ->get()
                ->map(fn (InterrogationMessage $message) => $message->getAttributes())
                ->values()
                ->all();
        });
    }

    /**
     * Every accusation of the game, by player.
     *
     * @return array<int, array<string, mixed>> raw rows by player id
     */
    public static function accusations(int $gameId): array
    {
        return (array) self::part('accusations', $gameId, [GameRevision::ACCUSATION], function () use ($gameId) {
            return Accusation::query()
                ->where('game_id', $gameId)
                ->get()
                ->mapWithKeys(fn (Accusation $accusation) => [$accusation->player_id => $accusation->getAttributes()])
                ->all();
        });
    }

    // --- shaping ------------------------------------------------------------------

    /**
     * The sent events addressed to this player, as models, in inbox order. The
     * same rule as Player::eventsForMeQuery(), applied to the cached list.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, TimelineEvent>
     */
    public static function visibleTo(Player $player, array $rows): array
    {
        $visible = [];

        foreach ($rows as $row) {
            $mode = $row['delivery_mode'] ?? 'all';

            $mine = $mode === 'all'
                || ($mode === 'random_player' && (int) ($row['delivered_to_player_id'] ?? 0) === (int) $player->id)
                || ($mode === 'role_slug' && ($row['target_role_slug'] ?? null) === $player->role_slug);

            if ($mine) {
                $visible[] = (new TimelineEvent)->newFromBuilder($row);
            }
        }

        return $visible;
    }

    /**
     * The sessions as the board needs them: keyed by suspect, each with the
     * claiming player's id and name.
     *
     * @return \Illuminate\Support\Collection<string, InterrogationSession>
     */
    public static function sessionsBySuspect(int $gameId)
    {
        return collect(self::board($gameId))
            ->map(function (array $entry) {
                $session = (new InterrogationSession)->newFromBuilder($entry['session']);

                if ($entry['player'] !== null) {
                    $session->setRelation('player', (new Player)->newFromBuilder($entry['player']));
                }

                return $session;
            })
            ->keyBy('suspect_slug');
    }

    /** One session with its transcript, or null if nobody has claimed that suspect. */
    public static function sessionWithMessages(int $gameId, string $suspectSlug): ?InterrogationSession
    {
        $session = self::sessionsBySuspect($gameId)->get($suspectSlug);

        if (! $session) {
            return null;
        }

        $session->setRelation('messages', collect(self::chat((int) $session->id, $gameId))
            ->map(fn (array $row) => (new InterrogationMessage)->newFromBuilder($row))
            ->values());

        return $session;
    }

    public static function accusationOf(Player $player): ?Accusation
    {
        $row = self::accusations((int) $player->game_id)[$player->id] ?? null;

        return $row === null ? null : (new Accusation)->newFromBuilder($row);
    }

    // --- writing ------------------------------------------------------------------

    /**
     * Something this game's readers can see just changed. Rebuild what it
     * invalidated once the response has gone out, on the connection the writer
     * already holds. Several writes in one request make one rebuild.
     *
     * @param  array<int, string>  $channels
     */
    public static function scheduleRefresh(int $gameId, array $channels): void
    {
        if (! self::enabled()) {
            return;
        }

        self::own();

        foreach ($channels as $channel) {
            self::$pending[$gameId][$channel] = true;
        }

        self::hook();
    }

    public static function scheduleSessionRefresh(int $sessionId): void
    {
        if (! self::enabled()) {
            return;
        }

        self::own();

        self::$pendingSessions[$sessionId] = true;

        self::hook();
    }

    /** Rebuilds everything that was invalidated during this request. */
    public static function flushPending(): void
    {
        $games = self::$pending;
        $sessions = array_keys(self::$pendingSessions);

        self::$pending = [];
        self::$pendingSessions = [];

        foreach ($games as $gameId => $channels) {
            try {
                self::refresh((int) $gameId, array_keys($channels));
            } catch (Throwable $exception) {
                // The readers will rebuild on their own: this only saves them the trip.
                report($exception);
            }
        }

        foreach ($sessions as $sessionId) {
            try {
                $gameId = InterrogationSession::query()->whereKey($sessionId)->value('game_id');

                if ($gameId) {
                    // Forget it, then read it: reading is what rebuilds and stores.
                    self::forgetPart("chat:{$sessionId}", (int) $gameId);
                    self::chat((int) $sessionId, (int) $gameId);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    /**
     * @param  array<int, string>  $channels
     */
    public static function refresh(int $gameId, array $channels): void
    {
        $game = null;

        if (in_array(GameRevision::TIMELINE, $channels, true) || in_array(GameRevision::ACCUSATION, $channels, true)) {
            self::forgetPart('meta', $gameId);
            $game = self::game($gameId);
        }

        if (in_array(GameRevision::TIMELINE, $channels, true)) {
            self::forgetPart('timeline', $gameId);
            self::timeline($gameId);

            if ($game) {
                self::forgetPart('inbox', $gameId);
                self::inbox($game);
            }
        }

        if (in_array(GameRevision::TIMELINE, $channels, true) || in_array(GameRevision::READINESS, $channels, true)) {
            self::forgetPart('readiness', $gameId);
            self::readiness($gameId);
        }

        if (in_array(GameRevision::INTERROGATION, $channels, true)) {
            self::forgetPart('board', $gameId);
            self::board($gameId);
        }

        if (in_array(GameRevision::ACCUSATION, $channels, true)) {
            self::forgetPart('accusations', $gameId);
            self::accusations($gameId);
        }
    }

    /** Drops every part of a game (it was deleted, or an operator wants a clean slate). */
    public static function forget(int $gameId): void
    {
        foreach (['meta', 'timeline', 'inbox', 'readiness', 'board', 'accusations'] as $name) {
            self::forgetPart($name, $gameId);
        }
    }

    // --- internals ----------------------------------------------------------------

    /**
     * @param  array<int, string>  $channels
     */
    private static function part(string $name, int $gameId, array $channels, callable $build): mixed
    {
        return self::partWithStamp($name, $gameId, self::stampOf($gameId, $channels), $build);
    }

    private static function partWithStamp(string $name, int $gameId, ?string $stamp, callable $build): mixed
    {
        // Stamps unreadable: nothing to validate against, so build and do not keep.
        if ($stamp === null) {
            return $build();
        }

        $key = self::key($name, $gameId);

        try {
            $cached = Cache::get($key);

            if (is_array($cached)
                && ($cached['stamp'] ?? null) === $stamp
                && (now()->getTimestamp() - (int) ($cached['built_at'] ?? 0)) < self::TTL_SECONDS) {
                return $cached['data'];
            }
        } catch (Throwable) {
            // A cache that cannot be read is a cache that is empty.
        }

        $data = $build();

        try {
            // A fixed key, overwritten in place: the number of files is bounded by
            // games x parts, never by how often things change.
            Cache::put($key, ['stamp' => $stamp, 'built_at' => now()->getTimestamp(), 'data' => $data], now()->addHours(self::KEEP_HOURS));
        } catch (Throwable) {
        }

        return $data;
    }

    /**
     * @param  array<int, string>  $channels
     */
    private static function stampOf(int $gameId, array $channels): ?string
    {
        $stamps = GameRevision::current($gameId);

        if ($stamps === null) {
            return null;
        }

        return implode('|', array_map(fn (string $channel) => $stamps[$channel], $channels));
    }

    /** @return array{attributes: array<string, mixed>, facts: array<string, mixed>}|null */
    private static function buildMeta(int $gameId): ?array
    {
        $game = Game::query()->find($gameId);

        if (! $game) {
            return null;
        }

        // The facts are computed from the database, on a game that is NOT primed.
        $facts = [
            'interrogation_open' => $game->interrogationsOpen(),
            'accusations_unlocked' => $game->accusationsUnlocked(),
            'delivered_evidence' => $game->deliveredEvidenceCodes(),
            'pending_accusations' => $game->pendingAccusationsCount(),
        ];

        $attributes = $game->getAttributes();

        // Never needed by a player and never shown to one: not worth keeping on disk.
        $attributes['ending_audio_script'] = null;
        $attributes['ending_audio_path'] = null;

        return ['attributes' => $attributes, 'facts' => $facts];
    }

    private static function forgetPart(string $name, int $gameId): void
    {
        try {
            Cache::forget(self::key($name, $gameId));
        } catch (Throwable) {
        }
    }

    private static function key(string $name, int $gameId): string
    {
        return "immersion:snap:{$gameId}:{$name}";
    }

    /**
     * Pending work belongs to ONE application instance. A test boots a fresh one
     * each time, and work that never got to run on the previous one must not leak
     * into it.
     */
    private static function own(): void
    {
        if (self::$owner !== app()) {
            self::$owner = app();
            self::$pending = [];
            self::$pendingSessions = [];
            self::$hooked = false;
        }
    }

    private static function hook(): void
    {
        if (self::$hooked) {
            return;
        }

        self::$hooked = true;

        // After the response has gone out (and, in a console command, after it
        // has finished), on the connection the write already opened.
        app()->terminating(function () {
            self::$hooked = false;
            self::flushPending();
        });
    }
}
