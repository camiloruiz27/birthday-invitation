<?php

namespace App\Modules\Express\Support;

use App\Modules\Express\Ai\ExpressAiUnavailable;
use App\Modules\Express\Ai\ExpressInterrogationProvider;
use App\Modules\Express\Models\Accusation;
use App\Modules\Express\Models\Lead;
use App\Modules\Express\Models\Player;
use App\Modules\Express\Models\Question;
use App\Modules\Express\Models\Room;
use App\Modules\Express\Models\Share;
use App\Modules\Immersion\Support\AiGate;
use App\Modules\Platform\Support\Attribution;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every write of a round: opening a room, joining, starting, sharing a clue,
 * asking, accusing, revealing.
 *
 * Each move runs in a transaction that first locks the room's row, so two
 * phones acting at once (two people spending the team's last question, the
 * last two accusations arriving together) are applied one after the other and
 * the rules — budget, one accusation each, reveal exactly once — hold. After
 * the commit the room's cached snapshot is rebuilt (see RoomSnapshot): that is
 * what players read, so reading never touches the database.
 *
 * Failures a player can cause are thrown as ExpressException with a message
 * written for them.
 */
final class RoomService
{
    private const CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function __construct(
        private readonly ExpressCaseRegistry $cases,
        private readonly QuestionMatcher $matcher,
        private readonly ExpressInterrogationProvider $ai,
    ) {
    }

    // ---------------------------------------------------------------- setup

    /**
     * Opens a room and seats its host (who plays too).
     *
     * @param  array<string, mixed>  $event
     * @param  array{type: string, value: string}|null  $contact
     */
    public function createRoom(array $event, string $caseSlug, string $nickname, ?array $contact, Request $request): Player
    {
        $this->assertOpen($event);

        if (! array_key_exists($caseSlug, ExpressEvents::cases($event, $this->cases))) {
            throw new ExpressException('invalid', 'Ese caso no está disponible.');
        }

        $open = Room::query()->whereIn('status', [Room::LOBBY, Room::RUNNING])->count();
        if ($open >= (int) config('express.max_open_rooms')) {
            throw new ExpressException('room_limit', 'Hay muchas salas abiertas ahora mismo. Inténtalo en un momento.', 429);
        }

        $player = DB::transaction(function () use ($event, $caseSlug, $nickname, $contact, $request) {
            $room = Room::query()->create([
                'code' => $this->newCode(),
                'event_slug' => $event['slug'],
                'case_slug' => $caseSlug,
                'status' => Room::LOBBY,
            ]);

            $player = $this->seat($room, $nickname, $this->captureLead($event['slug'], $nickname, $contact, $request), true);
            $room->update(['gm_player_id' => $player->id]);

            return $player;
        });

        RoomSnapshot::rebuild((int) $player->room_id);

        return $player;
    }

    /**
     * Seats a visitor in an existing room.
     *
     * @param  array{type: string, value: string}|null  $contact
     */
    public function join(Room $room, string $nickname, ?array $contact, Request $request): Player
    {
        $event = ExpressEvents::find($room->event_slug) ?? ExpressEvents::find(null);
        $this->assertOpen($event);

        $player = DB::transaction(function () use ($room, $nickname, $contact, $request) {
            $locked = $this->lock($room->id);
            $this->assertJoinable($locked);

            return $this->seat($locked, $nickname, $this->captureLead($locked->event_slug, $nickname, $contact, $request), false);
        });

        $this->touch($room->id);

        return $player;
    }

    // ----------------------------------------------------------------- play

    /** The host starts the round. Needs at least two people. */
    public function start(Player $player): void
    {
        DB::transaction(function () use ($player) {
            $room = $this->lock($player->room_id);

            if (! $player->is_gm) {
                throw new ExpressException('not_gm', 'Solo el anfitrión puede iniciar la ronda.', 403);
            }

            if ($room->status !== Room::LOBBY) {
                return; // a double tap: already running
            }

            if ($room->players()->count() < (int) config('express.min_players')) {
                throw new ExpressException('not_enough_players', 'Necesitan ser al menos 2 para empezar. Invita a un amigo.');
            }

            $room->update(['status' => Room::RUNNING, 'started_at_ms' => Clock::nowMs()]);
            Room::query()->whereKey($room->id)->increment('rev');
        });

        $this->touch($player->room_id, false);
    }

    /** Shows one of the player's private clues to the whole group. */
    public function share(Player $player, string $evidenceId): void
    {
        DB::transaction(function () use ($player, $evidenceId) {
            $room = $this->running($player->room_id);
            $case = $this->caseOf($room);
            $elapsed = Clock::elapsed((int) $room->started_at_ms);

            $clue = collect($case->evidence())->firstWhere('id', $evidenceId);

            if ($clue === null || $clue['audience'] !== $player->audience || $elapsed < $clue['unlock_second']) {
                throw new ExpressException('invalid', 'Esa pista no es tuya para compartir.');
            }

            Share::query()->firstOrCreate(
                ['room_id' => $room->id, 'evidence_id' => $evidenceId],
                ['player_id' => $player->id],
            );
            Room::query()->whereKey($room->id)->increment('rev');
        });

        $this->touch($player->room_id, false);
    }

    /**
     * One question to one suspect.
     *
     * Opening a sheet costs nothing; a question costs one of the team's three.
     *
     * With the AI on, the suspect answers in character (the author's own
     * statements are what it must stay faithful to) and EVERY question costs
     * one: each is a distinct call. The slot is taken and committed BEFORE the
     * call, so two phones cannot spend the same last question while one waits
     * for the gateway, and the room's row lock is never held during the wait.
     * If the AI cannot answer, a question that matches one of the author's
     * topics is answered with the author's text (it still counts); one that
     * matches nothing is handed back free with a "try again".
     *
     * With the AI off, the author's text answers: "no entendí" is free, and so
     * is asking again about something the team already has.
     *
     * @return array{status: string, topic_id: ?string, answer: string, source: string}
     */
    public function ask(Player $player, string $suspectId, string $text, ?string $topicId): array
    {
        $useAi = $this->ai->available();

        $plan = DB::transaction(function () use ($player, $suspectId, $text, $topicId, $useAi) {
            $room = $this->running($player->room_id);
            $case = $this->caseOf($room);
            $schedule = (array) config('express.schedule');
            $elapsed = Clock::elapsed((int) $room->started_at_ms);

            if ($elapsed < $schedule['interrogation_opens_s']) {
                throw new ExpressException('too_early', 'Los interrogatorios todavía no están abiertos.');
            }

            $suspect = $case->suspect($suspectId)
                ?? throw new ExpressException('invalid', 'Ese sospechoso no existe en este caso.');

            $topic = $topicId !== null
                ? collect($suspect['topics'])->firstWhere('id', $topicId)
                : null;

            if ($topicId !== null && $topic === null) {
                throw new ExpressException('invalid', 'Esa pregunta no corresponde a este sospechoso.');
            }

            $topic ??= $this->understood($suspect, $text);

            if (! $useAi) {
                if ($topic === null) {
                    throw new ExpressException('unclear', 'No entendí la pregunta. Prueba con otras palabras o elige una de las sugeridas.');
                }

                // A topic the team already has: answer again, spend nothing.
                $known = Question::query()
                    ->where(['room_id' => $room->id, 'suspect_id' => $suspectId, 'topic_id' => $topic['id']])
                    ->whereNotNull('answer')
                    ->first();

                if ($known) {
                    return ['done' => ['status' => 'repeat', 'topic_id' => $topic['id'], 'answer' => $known->answer, 'source' => Question::AUTHORED]];
                }
            }

            // A question whose answer never came back (the request died) stops
            // holding a place after a while.
            Question::query()
                ->where('room_id', $room->id)
                ->whereNull('answer')
                ->where('created_at', '<', now()->subSeconds(45))
                ->delete();

            // The first to ask a suspect keeps them for the round.
            $claimant = Question::query()->where(['room_id' => $room->id, 'suspect_id' => $suspectId])->orderBy('id')->value('player_id');
            if ($claimant !== null && (int) $claimant !== (int) $player->id) {
                throw new ExpressException('suspect_locked', 'Otro de tu grupo ya está interrogando a esta persona.');
            }

            $spent = Question::query()->where('room_id', $room->id)->count();
            if ($spent >= (int) config('express.questions.team_budget')) {
                throw new ExpressException('budget', 'Ya gastaron las 3 preguntas del equipo.');
            }

            $perSuspect = Question::query()->where(['room_id' => $room->id, 'suspect_id' => $suspectId])->count();
            if ($perSuspect >= (int) config('express.questions.per_suspect')) {
                throw new ExpressException('budget', 'Ya le hicieron todas las preguntas posibles a esta persona.');
            }

            $question = Question::query()->create([
                'room_id' => $room->id,
                'player_id' => $player->id,
                'suspect_id' => $suspectId,
                'topic_id' => $topic['id'] ?? null,
                'question' => Str::limit(trim($text), (int) config('express.questions.max_length'), ''),
                // Authored mode answers now; AI mode leaves it open until the call returns.
                'answer' => $useAi ? null : $topic['answer'],
                'source' => $useAi ? Question::AI : Question::AUTHORED,
            ]);
            Room::query()->whereKey($room->id)->increment('rev');

            return [
                'question' => $question,
                'case' => $case,
                'suspect' => $suspect,
                'topic' => $topic,
                'text' => $question->question,
                'history' => $this->turnsWith($room->id, $suspectId, $question->id),
                'evidence' => $this->tableEvidence($room, $case, $elapsed),
            ];
        });

        if (isset($plan['done'])) {
            return $plan['done'];
        }

        $this->touch($player->room_id, false);

        /** @var Question $question */
        $question = $plan['question'];

        if (! $useAi) {
            return ['status' => 'answered', 'topic_id' => $question->topic_id, 'answer' => (string) $question->answer, 'source' => Question::AUTHORED];
        }

        [$answer, $source] = $this->answerWithAi($plan);

        if ($answer === null) {
            // Nothing to say and nothing authored to say instead: hand the
            // question back, free.
            $question->delete();
            $this->touch($player->room_id);

            throw new ExpressException('ai_unavailable', 'Esa persona no responde ahora mismo. Inténtalo de nuevo o elige una pregunta sugerida; no gastaste ninguna.');
        }

        $question->update(['answer' => $answer, 'source' => $source]);
        $this->touch($player->room_id);

        return ['status' => 'answered', 'topic_id' => $question->topic_id, 'answer' => $answer, 'source' => $source];
    }

    /**
     * The suspect's reply from the AI, or — when it cannot answer — the
     * author's text for the topic the question matched, or null.
     *
     * @param  array<string, mixed>  $plan
     * @return array{0: ?string, 1: string}
     */
    private function answerWithAi(array $plan): array
    {
        $topic = $plan['topic'];
        $slot = AiGate::acquire();

        if ($slot === null) {
            // Every gateway slot is busy: not worth making the player wait.
            return [$topic['answer'] ?? null, Question::AUTHORED];
        }

        try {
            return [
                $this->ai->ask($plan['case'], $plan['suspect'], $plan['history'], $plan['text'], $topic['id'] ?? null, $plan['evidence']),
                Question::AI,
            ];
        } catch (ExpressAiUnavailable) {
            return [$topic['answer'] ?? null, Question::AUTHORED];
        } finally {
            AiGate::release($slot);
        }
    }

    /**
     * What the player and this suspect have already said to each other, as the
     * turns the gateway expects (the question being asked now is not in it).
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function turnsWith(int $roomId, string $suspectId, int $exceptId): array
    {
        $turns = [];

        foreach (Question::query()->where(['room_id' => $roomId, 'suspect_id' => $suspectId])->whereNotNull('answer')->where('id', '!=', $exceptId)->orderBy('id')->get() as $past) {
            $turns[] = ['role' => 'player', 'content' => $past->question];
            $turns[] = ['role' => 'suspect', 'content' => (string) $past->answer];
        }

        return $turns;
    }

    /**
     * The clues the WHOLE table has seen by now — everything for everyone that
     * has unlocked, plus whatever someone chose to share. A suspect should
     * react to what the group holds, not to a clue only one phone has.
     *
     * @return array<int, array{id: string, title: string}>
     */
    private function tableEvidence(Room $room, ExpressCase $case, float $elapsed): array
    {
        $shared = Share::query()->where('room_id', $room->id)->pluck('evidence_id')->all();

        return collect($case->evidence())
            ->filter(fn (array $clue) => $elapsed >= $clue['unlock_second']
                && ($clue['audience'] === 'global' || in_array($clue['id'], $shared, true)))
            ->map(fn (array $clue) => ['id' => $clue['id'], 'title' => $clue['title']])
            ->values()
            ->all();
    }

    /**
     * The player's final answer, as the three ids the form offered. When this
     * was the last one, the round is revealed.
     *
     * @param  array{who: string, how: string, why: string}  $ids
     */
    public function accuse(Player $player, array $ids): void
    {
        DB::transaction(function () use ($player, $ids) {
            $room = $this->running($player->room_id);
            $case = $this->caseOf($room);
            $schedule = (array) config('express.schedule');
            $elapsed = Clock::elapsed((int) $room->started_at_ms);

            if ($elapsed < $schedule['accusations_open_s']) {
                throw new ExpressException('too_early', 'Todavía no se puede acusar.');
            }

            if ($elapsed >= $schedule['hard_close_s']) {
                throw new ExpressException('closed', 'Se acabó el tiempo para acusar.');
            }

            if (Accusation::query()->where('player_id', $player->id)->exists()) {
                throw new ExpressException('already_accused', 'Ya enviaste tu acusación.');
            }

            $answer = AccusationOptions::resolve($case, $room->code, $ids)
                ?? throw new ExpressException('invalid', 'Elige una opción en cada pregunta.');

            Accusation::query()->create([
                'room_id' => $room->id,
                'player_id' => $player->id,
                'who_id' => $answer['who'],
                'how_index' => $answer['how'],
                'why_index' => $answer['why'],
                'submitted_ms' => Clock::nowMs(),
            ]);
            Room::query()->whereKey($room->id)->increment('rev');

            $everyone = $room->players()->count();
            if (Accusation::query()->where('room_id', $room->id)->count() >= $everyone) {
                $this->finish($room, $case);
            }
        });

        $this->touch($player->room_id, false);
    }

    // --------------------------------------------------------------- reveal

    /**
     * Closes the round if its time is up or everyone has accused. Cheap to
     * call on every read: the answer comes from the snapshot, and the database
     * is only touched on the one request that actually ends the round.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed> the snapshot to use from now on
     */
    public function settle(array $snapshot): array
    {
        if (! $this->due($snapshot)) {
            return $snapshot;
        }

        DB::transaction(function () use ($snapshot) {
            $room = $this->lock($snapshot['id']);

            if ($room->status === Room::RUNNING) {
                $this->finish($room, $this->caseOf($room));
            }
        });

        return $this->touch($snapshot['id'], false) ?? $snapshot;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function due(array $snapshot): bool
    {
        if ($snapshot['status'] !== Room::RUNNING || $snapshot['started_at_ms'] === null) {
            return false;
        }

        $schedule = (array) config('express.schedule');
        $elapsed = Clock::elapsed($snapshot['started_at_ms']);

        if ($elapsed >= $schedule['hard_close_s']) {
            return true;
        }

        return $elapsed >= $schedule['accusations_open_s']
            && count($snapshot['accusations']) >= count($snapshot['players']);
    }

    /** Scores every accusation and marks the room finished. Inside a transaction. */
    private function finish(Room $room, ExpressCase $case): void
    {
        foreach (Accusation::query()->where('room_id', $room->id)->get() as $accusation) {
            $score = AccusationOptions::score($case, [
                'who' => $accusation->who_id,
                'how' => $accusation->how_index,
                'why' => $accusation->why_index,
            ]);

            $accusation->update([
                'score_who' => $score['who'],
                'score_how' => $score['how'],
                'score_why' => $score['why'],
                'score_total' => $score['total'],
            ]);
        }

        $room->update(['status' => Room::FINISHED, 'revealed_at' => now()]);
        Room::query()->whereKey($room->id)->increment('rev');
    }

    // ------------------------------------------------------------ next round

    /**
     * Opens the next round for the same group. Whoever taps first creates it;
     * everyone else finds it already there.
     */
    public function createNext(Player $player, ?string $caseSlug): Room
    {
        $room = Room::query()->findOrFail($player->room_id);
        $event = ExpressEvents::find($room->event_slug) ?? ExpressEvents::find(null);
        $this->assertOpen($event);

        $next = DB::transaction(function () use ($player, $caseSlug, $event) {
            $current = $this->lock($player->room_id);

            if ($current->status !== Room::FINISHED) {
                throw new ExpressException('invalid', 'La ronda todavía no ha terminado.');
            }

            if ($current->next_room_id !== null) {
                return Room::query()->findOrFail($current->next_room_id);
            }

            $slug = $caseSlug ?? $current->case_slug;
            if (! array_key_exists($slug, ExpressEvents::cases($event, $this->cases))) {
                throw new ExpressException('invalid', 'Ese caso no está disponible.');
            }

            $next = Room::query()->create([
                'code' => $this->newCode(),
                'event_slug' => $current->event_slug,
                'case_slug' => $slug,
                'status' => Room::LOBBY,
            ]);

            // The one who asked hosts the new round, keeping their contact.
            $host = $this->seat($next, $player->nickname, $player->lead_id ? Lead::query()->find($player->lead_id) : null, true);
            $next->update(['gm_player_id' => $host->id]);
            $current->update(['next_room_id' => $next->id]);
            Room::query()->whereKey($current->id)->increment('rev');

            return $next;
        });

        $this->touch($player->room_id, false);
        RoomSnapshot::rebuild((int) $next->id);

        return $next;
    }

    /** Moves a player of a finished round into its successor, keeping nickname and contact. */
    public function joinNext(Player $player): Player
    {
        $current = Room::query()->findOrFail($player->room_id);

        if ($current->next_room_id === null) {
            throw new ExpressException('invalid', 'Todavía no hay una siguiente ronda.');
        }

        $existing = Player::query()
            ->where('room_id', $current->next_room_id)
            ->where('nickname', $player->nickname)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $next = DB::transaction(function () use ($player, $current) {
            $room = $this->lock($current->next_room_id);
            $this->assertJoinable($room);

            return $this->seat($room, $player->nickname, $player->lead_id ? Lead::query()->find($player->lead_id) : null, false);
        });

        $this->touch($current->next_room_id);

        return $next;
    }

    // --------------------------------------------------------------- helpers

    /** @param  array<string, mixed>  $event */
    private function assertOpen(array $event): void
    {
        $status = ExpressEvents::status($event);

        if (! $status['open']) {
            throw new ExpressException('closed', (string) $status['reason'], 403);
        }
    }

    private function assertJoinable(Room $room): void
    {
        $snapshot = [
            'status' => $room->status,
            'players' => $room->players()->get(['id'])->all(),
        ];

        $elapsed = $room->started_at_ms !== null ? Clock::elapsed((int) $room->started_at_ms) : null;

        if (! PublicState::joinable($snapshot, $elapsed, (array) config('express.schedule'))) {
            $full = count($snapshot['players']) >= (int) config('express.max_players');

            throw new ExpressException(
                $full ? 'room_full' : 'join_closed',
                $full ? 'La sala ya está llena.' : 'La ronda ya empezó. Pídanle al anfitrión una nueva.',
            );
        }
    }

    /** A player takes the next seat: audience alternates A, B, A, B... */
    private function seat(Room $room, string $nickname, ?Lead $lead, bool $host): Player
    {
        $order = $room->players()->count() + 1;

        // Nicknames are how the group tells each other apart (the ranking, "X
        // already has this suspect") and how a player is matched into the next
        // round, so two "Ana" in one room get "Ana" and "Ana 2".
        $taken = $room->players()->pluck('nickname')->map(fn ($n) => mb_strtolower($n))->all();
        $base = $nickname;
        for ($suffix = 2; in_array(mb_strtolower($nickname), $taken, true); $suffix++) {
            $nickname = mb_substr($base, 0, 24 - strlen(" {$suffix}")).' '.$suffix;
        }

        return Player::query()->create([
            'room_id' => $room->id,
            'lead_id' => $lead?->id,
            'token' => (string) Str::uuid(),
            'nickname' => $nickname,
            'audience' => $order % 2 === 1 ? 'A' : 'B',
            'is_gm' => $host,
            'joined_order' => $order,
        ]);
    }

    private function lock(int $roomId): Room
    {
        return Room::query()->whereKey($roomId)->lockForUpdate()->firstOrFail();
    }

    private function running(int $roomId): Room
    {
        $room = $this->lock($roomId);

        if ($room->status === Room::LOBBY) {
            throw new ExpressException('not_started', 'La ronda todavía no ha empezado.');
        }

        if ($room->status !== Room::RUNNING) {
            throw new ExpressException('closed', 'La ronda ya terminó.');
        }

        if (Clock::elapsed((int) $room->started_at_ms) >= (int) config('express.schedule.hard_close_s')) {
            throw new ExpressException('closed', 'Se acabó el tiempo.');
        }

        return $room;
    }

    private function caseOf(Room $room): ExpressCase
    {
        return $this->cases->find($room->case_slug)
            ?? throw new ExpressException('invalid', 'El caso de esta sala ya no existe.', 404);
    }

    /**
     * @param  array<string, mixed>  $suspect
     * @return array<string, mixed>|null
     */
    private function understood(array $suspect, string $text): ?array
    {
        $id = $this->matcher->match($text, $suspect['topics']);

        return $id === null ? null : collect($suspect['topics'])->firstWhere('id', $id);
    }

    /** Bumps the room's stamp, rebuilds the cached snapshot and returns it. */
    private function touch(int $roomId, bool $bump = true): ?array
    {
        if ($bump) {
            Room::query()->whereKey($roomId)->increment('rev');
        }

        return RoomSnapshot::rebuild($roomId);
    }

    private function newCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 5; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (Room::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * Stores the contact of someone who gave one and authorised its use. The
     * same person at the same event (same contact) is one lead, not many.
     *
     * @param  array{type: string, value: string}|null  $contact
     */
    private function captureLead(string $eventSlug, string $nickname, ?array $contact, Request $request): ?Lead
    {
        if ($contact === null) {
            return null;
        }

        $hash = hash_hmac('sha256', $contact['type'].':'.$contact['value'], (string) config('app.key'));

        try {
            return Lead::query()->updateOrCreate(
                ['event_slug' => $eventSlug, 'contact_hash' => $hash],
                [
                    'nickname' => $nickname,
                    'contact_type' => $contact['type'],
                    'contact' => $contact['value'],
                    'consent_at' => now(),
                    'consent_version' => (string) config('express.consent.version'),
                    'attribution' => Attribution::touches($request) ?: null,
                ],
            );
        } catch (QueryException) {
            // Two taps at once on the same contact: the other request won.
            return Lead::query()->where(['event_slug' => $eventSlug, 'contact_hash' => $hash])->first();
        }
    }
}
