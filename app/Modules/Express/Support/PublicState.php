<?php

namespace App\Modules\Express\Support;

use App\Modules\Express\Models\Room;

/**
 * What ONE player may see of their room right now.
 *
 * A pure function of the cached snapshot, the case and the clock: no queries.
 * Everything a player receives passes through here, and every field is listed
 * explicitly — the case file's secrets (`author_only`, what a clue "really
 * means", each suspect's lie and secret) are never copied, only the solution
 * once the round is revealed and only the answers the player is entitled to.
 *
 * The shape is the contract in app/Modules/Express/README.md.
 */
final class PublicState
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public static function build(array $snapshot, int $playerId, ?int $nowMs = null): array
    {
        $nowMs ??= Clock::nowMs();

        /** @var ExpressCase $case */
        $case = app(ExpressCaseRegistry::class)->find($snapshot['case_slug']);
        $event = ExpressEvents::find($snapshot['event_slug']) ?? ExpressEvents::find(null);
        $schedule = (array) config('express.schedule');
        $assets = $case->assets();

        $players = $snapshot['players'];
        $me = collect($players)->firstWhere('id', $playerId);
        $nicknames = collect($players)->pluck('nickname', 'id')->all();

        $started = $snapshot['started_at_ms'];
        $elapsed = $started !== null && $snapshot['status'] !== Room::LOBBY
            ? Clock::elapsed($started, $nowMs)
            : null;

        $status = self::status($snapshot, $elapsed, $schedule);
        $playing = $snapshot['status'] === Room::RUNNING && $elapsed !== null;
        $finished = $snapshot['status'] === Room::FINISHED;

        return [
            'rev' => $snapshot['rev'],
            'server_now_ms' => $nowMs,
            'room' => [
                'code' => $snapshot['code'],
                'status' => $status,
                'started_at_ms' => $started,
                'elapsed_s' => $elapsed !== null ? (int) floor($elapsed) : null,
                'join_url' => url('/express/unirse/'.$snapshot['code']),
                'joinable' => self::joinable($snapshot, $elapsed, $schedule),
                'min_players' => (int) config('express.min_players'),
                'max_players' => (int) config('express.max_players'),
                'case' => [
                    'slug' => $case->slug(),
                    'title' => $case->title(),
                    'tagline' => $case->tagline(),
                    'genre' => $case->genre(),
                    'scenario' => $case->scenario(),
                    'incident' => $case->incident(),
                    'hook' => $case->hook(),
                    'hero' => $assets['hero'],
                ],
                'schedule' => [
                    'interrogation_opens_s' => (int) $schedule['interrogation_opens_s'],
                    'accusations_open_s' => (int) $schedule['accusations_open_s'],
                    'window_end_s' => (int) $schedule['window_end_s'],
                    'hard_close_s' => (int) $schedule['hard_close_s'],
                    'join_cutoff_s' => (int) $schedule['join_cutoff_s'],
                ],
                'event' => ['slug' => $event['slug'], 'name' => $event['name']],
            ],
            'me' => [
                'id' => $playerId,
                'nickname' => $me['nickname'],
                'audience' => $me['audience'],
                'is_gm' => $me['is_gm'],
                'can_start' => $me['is_gm']
                    && $snapshot['status'] === Room::LOBBY
                    && count($players) >= (int) config('express.min_players'),
                'accused' => isset($snapshot['accusations'][$playerId]),
            ],
            'players' => array_map(fn (array $p) => [
                'id' => $p['id'],
                'nickname' => $p['nickname'],
                'is_gm' => $p['is_gm'],
                'accused' => isset($snapshot['accusations'][$p['id']]),
                'is_me' => $p['id'] === $playerId,
            ], $players),
            'evidence' => self::evidence($snapshot, $case, $me, $elapsed, $assets, $nicknames, $playing),
            'next_unlock' => $playing ? self::nextUnlock($case, $elapsed, $schedule) : null,
            'suspects' => self::suspects($snapshot, $case, $playerId, $nicknames, $assets),
            'interrogation' => self::interrogation($snapshot, $nicknames, $elapsed, $playing, $schedule),
            'accusation' => self::accusation($snapshot, $case, $me, $elapsed, $playing, $finished, $nicknames, $schedule),
            'reveal' => $finished ? self::reveal($snapshot, $case, $playerId, $event) : null,
        ];
    }

    /**
     * lobby -> playing -> accusing -> reveal (or expired for a room nobody
     * finished).
     *
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>  $schedule
     */
    private static function status(array $snapshot, ?float $elapsed, array $schedule): string
    {
        return match ($snapshot['status']) {
            Room::LOBBY => 'lobby',
            Room::FINISHED => 'reveal',
            Room::EXPIRED => 'expired',
            default => $elapsed !== null && $elapsed >= $schedule['accusations_open_s'] ? 'accusing' : 'playing',
        };
    }

    /**
     * Whether someone new can still come in: room for another player and, once
     * the round has started, only inside the first seconds.
     *
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>  $schedule
     */
    public static function joinable(array $snapshot, ?float $elapsed, array $schedule): bool
    {
        if (count($snapshot['players']) >= (int) config('express.max_players')) {
            return false;
        }

        return match ($snapshot['status']) {
            Room::LOBBY => true,
            Room::RUNNING => $elapsed !== null && $elapsed <= $schedule['join_cutoff_s'],
            default => false,
        };
    }

    /**
     * The clues this player can read: the ones already unlocked that are for
     * everyone, for their own side (A or B), or that someone chose to share.
     *
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>  $me
     * @param  array<string, mixed>  $assets
     * @param  array<int, string>  $nicknames
     * @return array<int, array<string, mixed>>
     */
    private static function evidence(array $snapshot, ExpressCase $case, array $me, ?float $elapsed, array $assets, array $nicknames, bool $playing): array
    {
        if ($elapsed === null) {
            return [];
        }

        $visible = [];

        foreach ($case->evidence() as $clue) {
            if ($elapsed < $clue['unlock_second']) {
                continue;
            }

            $sharedBy = $snapshot['shares'][$clue['id']] ?? null;
            $mine = $clue['audience'] === $me['audience'];

            if ($clue['audience'] !== 'global' && ! $mine && $sharedBy === null) {
                continue;
            }

            $visible[] = [
                'id' => $clue['id'],
                'title' => $clue['title'],
                'type' => $clue['type'],
                'lines' => $clue['lines'],
                'audience' => $clue['audience'],
                'unlocked_at_s' => $clue['unlock_second'],
                'mine' => $mine && $clue['audience'] !== 'global',
                'shared' => $sharedBy !== null,
                'shared_by' => $sharedBy !== null ? ($nicknames[$sharedBy] ?? null) : null,
                'can_share' => $playing && $mine && $clue['audience'] !== 'global' && $sharedBy === null,
                'image' => $assets['images'][$clue['id']] ?? null,
                'audio' => $assets['audio'][$clue['id']] ?? null,
            ];
        }

        return $visible;
    }

    /**
     * The next thing that will change by itself — a clue, the interrogations,
     * the accusations, the end — so the page can say "next clue in 12 s".
     *
     * @param  array<string, mixed>  $schedule
     * @return array{at_s: int, in_s: int}|null
     */
    private static function nextUnlock(ExpressCase $case, float $elapsed, array $schedule): ?array
    {
        $moments = array_merge(
            array_column($case->evidence(), 'unlock_second'),
            [$schedule['interrogation_opens_s'], $schedule['accusations_open_s'], $schedule['hard_close_s']],
        );

        $upcoming = array_filter($moments, fn ($at) => $at > $elapsed);

        if ($upcoming === []) {
            return null;
        }

        $at = (int) min($upcoming);

        return ['at_s' => $at, 'in_s' => (int) ceil($at - $elapsed)];
    }

    /**
     * Who can be questioned, who has already been claimed by whom, and the
     * ready-made questions (the first paraphrase of each topic).
     *
     * @param  array<string, mixed>  $snapshot
     * @param  array<int, string>  $nicknames
     * @param  array<string, mixed>  $assets
     * @return array<int, array<string, mixed>>
     */
    private static function suspects(array $snapshot, ExpressCase $case, int $playerId, array $nicknames, array $assets): array
    {
        $claims = [];
        foreach ($snapshot['questions'] as $question) {
            $claims[$question['suspect_id']] ??= $question['player_id'];
        }

        return array_map(function (array $suspect) use ($snapshot, $claims, $playerId, $nicknames, $assets) {
            $claimant = $claims[$suspect['id']] ?? null;

            return [
                'id' => $suspect['id'],
                'name' => $suspect['name'],
                'role' => $suspect['role'],
                'relation' => $suspect['relation'],
                'portrait' => $assets['portraits'][$suspect['id']] ?? null,
                'claimed_by' => $claimant !== null
                    ? ['id' => $claimant, 'nickname' => $nicknames[$claimant] ?? '']
                    : null,
                'locked_for_me' => $claimant !== null && $claimant !== $playerId,
                'chips' => array_map(fn (array $topic) => [
                    'topic_id' => $topic['id'],
                    'text' => $topic['paraphrases'][0],
                ], $suspect['topics']),
                'answered_topics' => array_values(array_filter(array_map(
                    fn ($q) => $q['topic_id'],
                    array_filter($snapshot['questions'], fn ($q) => $q['suspect_id'] === $suspect['id'] && $q['answer'] !== null),
                ))),
            ];
        }, $case->suspects());
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array<int, string>  $nicknames
     * @param  array<string, mixed>  $schedule
     * @return array<string, mixed>
     */
    private static function interrogation(array $snapshot, array $nicknames, ?float $elapsed, bool $playing, array $schedule): array
    {
        $budget = (int) config('express.questions.team_budget');
        // Every question counts against the budget the moment it is taken, but
        // one still waiting for the AI (no answer yet) is not shown to anyone.
        $used = count($snapshot['questions']);

        return [
            'open' => $playing && $elapsed >= $schedule['interrogation_opens_s'] && $elapsed < $schedule['hard_close_s'],
            'budget_total' => $budget,
            'budget_used' => $used,
            'budget_left' => max(0, $budget - $used),
            'log' => array_values(array_map(fn (array $q) => [
                'id' => $q['id'],
                'suspect_id' => $q['suspect_id'],
                'nickname' => $nicknames[$q['player_id']] ?? '',
                'question' => $q['question'],
                'answer' => $q['answer'],
                'topic_id' => $q['topic_id'],
            ], array_filter($snapshot['questions'], fn ($q) => $q['answer'] !== null))),
        ];
    }

    /**
     * The accusation form and the player's own answer. Nothing here says what
     * is right: the options arrive opaque and shuffled, and results only exist
     * in the reveal block.
     *
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>  $me
     * @param  array<int, string>  $nicknames
     * @param  array<string, mixed>  $schedule
     * @return array<string, mixed>
     */
    private static function accusation(array $snapshot, ExpressCase $case, array $me, ?float $elapsed, bool $playing, bool $finished, array $nicknames, array $schedule): array
    {
        $mineStored = $snapshot['accusations'][$me['id']] ?? null;
        $open = $playing && $elapsed >= $schedule['accusations_open_s'] && $elapsed < $schedule['hard_close_s'];

        $options = ($open || ($mineStored !== null && ! $finished))
            ? AccusationOptions::forPlayer($case, $snapshot['code'], $me['token'])
            : null;

        return [
            'open' => $open && $mineStored === null,
            'submitted' => $mineStored !== null,
            'closes_in_s' => $playing ? max(0, (int) ceil($schedule['hard_close_s'] - $elapsed)) : null,
            'options' => $options,
            'mine' => $mineStored !== null && ! $finished ? [
                'who' => AccusationOptions::idFor($snapshot['code'], 'who', $mineStored['who']),
                'how' => AccusationOptions::idFor($snapshot['code'], 'how', $mineStored['how']),
                'why' => AccusationOptions::idFor($snapshot['code'], 'why', $mineStored['why']),
            ] : null,
            'waiting_for' => array_values(array_map(
                fn (array $p) => $p['nickname'],
                array_filter($snapshot['players'], fn (array $p) => $p['id'] !== $me['id'] && ! isset($snapshot['accusations'][$p['id']])),
            )),
        ];
    }

    /**
     * Scores, ranking and the author's resolution. Only for a finished round.
     *
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private static function reveal(array $snapshot, ExpressCase $case, int $playerId, array $event): array
    {
        $points = (array) config('express.scoring');
        $mine = $snapshot['accusations'][$playerId] ?? null;
        $correct = $case->correct();
        $whoLabel = collect($case->whoOptions())->pluck('label', 'id');

        $part = function (string $key, ?string $label) use ($mine, $points) {
            $earned = (int) ($mine['scores'][$key] ?? 0);

            return [
                'label' => $label,
                'correct' => $earned > 0,
                'points' => $earned,
                'out_of' => (int) $points[$key],
            ];
        };

        $ranking = collect($snapshot['players'])->map(function (array $p) use ($snapshot, $playerId) {
            $accusation = $snapshot['accusations'][$p['id']] ?? null;

            return [
                'nickname' => $p['nickname'],
                'total' => (int) ($accusation['scores']['total'] ?? 0),
                'is_me' => $p['id'] === $playerId,
                '_at' => $accusation['submitted_ms'] ?? PHP_INT_MAX,
            ];
        })->sort(fn ($a, $b) => [$b['total'], $a['_at']] <=> [$a['total'], $b['_at']])
            ->map(fn ($row) => collect($row)->except('_at')->all())
            ->values()
            ->all();

        $solution = $case->solution();

        return [
            'my' => [
                'who' => $part('who', $mine ? $whoLabel[$mine['who']] ?? null : null),
                'how' => $part('how', $mine ? $case->howOptions()[$mine['how']] ?? null : null),
                'why' => $part('why', $mine ? $case->whyOptions()[$mine['why']] ?? null : null),
                'total' => (int) ($mine['scores']['total'] ?? 0),
            ],
            'ranking' => $ranking,
            'solution' => [
                'headline' => $solution['headline'],
                'truth' => $solution['truth'],
                'reconstruction' => $solution['reconstruction'],
                'correct' => [
                    'who' => $whoLabel[$correct['who']] ?? '',
                    'how' => $case->howOptions()[$correct['how']] ?? '',
                    'why' => $case->whyOptions()[$correct['why']] ?? '',
                ],
                'nodes' => $solution['nodes'],
            ],
            'next_round' => [
                'code' => $snapshot['next_code'],
                'join_url' => $snapshot['next_code'] ? url('/express/unirse/'.$snapshot['next_code']) : null,
            ],
            'cta' => [
                'label' => 'Ver los casos completos',
                'url' => ExpressEvents::ctaUrl($event),
                'promo_code' => $event['promo_code'],
                'promo_note' => $event['promo_note'],
            ],
        ];
    }
}
