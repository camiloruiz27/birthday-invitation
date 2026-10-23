<?php

namespace App\Modules\Express\Support;

/**
 * The accusation form, without letting its shape give the answer away.
 *
 * In every case file the right option is listed first. So the server never
 * sends the file's order or its indices: each option gets an opaque id (an
 * HMAC of the room, the question and the option), and each player sees their
 * own shuffle. What gets stored is the position in the file, found again by
 * matching the id the player picked.
 */
final class AccusationOptions
{
    /**
     * @return array{who: array<int, array{id: string, label: string}>, how: array<int, array{id: string, label: string}>, why: array<int, array{id: string, label: string}>}
     */
    public static function forPlayer(ExpressCase $case, string $roomCode, string $playerToken): array
    {
        $correct = $case->correct();

        $who = array_map(fn (array $o) => [
            'id' => self::id($roomCode, 'who', $o['id']),
            'label' => $o['label'],
        ], $case->whoOptions());

        $how = [];
        foreach ($case->howOptions() as $index => $label) {
            $how[] = ['id' => self::id($roomCode, 'how', $index), 'label' => $label];
        }

        $why = [];
        foreach ($case->whyOptions() as $index => $label) {
            $why[] = ['id' => self::id($roomCode, 'why', $index), 'label' => $label];
        }

        return [
            'who' => self::shuffle($who, $playerToken, 'who', self::id($roomCode, 'who', $correct['who'])),
            'how' => self::shuffle($how, $playerToken, 'how', self::id($roomCode, 'how', $correct['how'])),
            'why' => self::shuffle($why, $playerToken, 'why', self::id($roomCode, 'why', $correct['why'])),
        ];
    }

    /**
     * The three ids a player picked, turned back into what is stored.
     *
     * @param  array{who: string, how: string, why: string}  $ids
     * @return array{who: string, how: int, why: int}|null null when any id is not one of ours
     */
    public static function resolve(ExpressCase $case, string $roomCode, array $ids): ?array
    {
        $who = null;
        foreach ($case->whoOptions() as $option) {
            if (hash_equals(self::id($roomCode, 'who', $option['id']), (string) $ids['who'])) {
                $who = $option['id'];
            }
        }

        $how = self::indexOf($case->howOptions(), $roomCode, 'how', (string) $ids['how']);
        $why = self::indexOf($case->whyOptions(), $roomCode, 'why', (string) $ids['why']);

        return $who === null || $how === null || $why === null
            ? null
            : ['who' => $who, 'how' => $how, 'why' => $why];
    }

    /**
     * The id a stored choice had, so a player's own answer can be shown back
     * to them in the same opaque terms the form used.
     */
    public static function idFor(string $roomCode, string $kind, string|int $key): string
    {
        return self::id($roomCode, $kind, $key);
    }

    /**
     * Points for one stored accusation.
     *
     * @param  array{who: string, how: int, why: int}  $answer
     * @return array{who: int, how: int, why: int, total: int}
     */
    public static function score(ExpressCase $case, array $answer): array
    {
        $correct = $case->correct();
        $points = (array) config('express.scoring');

        $who = $answer['who'] === $correct['who'] ? (int) $points['who'] : 0;
        $how = $answer['how'] === $correct['how'] ? (int) $points['how'] : 0;
        $why = $answer['why'] === $correct['why'] ? (int) $points['why'] : 0;

        return ['who' => $who, 'how' => $how, 'why' => $why, 'total' => $who + $how + $why];
    }

    private static function id(string $roomCode, string $kind, string|int $key): string
    {
        return substr(hash_hmac('sha256', "{$roomCode}|{$kind}|{$key}", (string) config('app.key')), 0, 12);
    }

    /** @param  array<int, string>  $options */
    private static function indexOf(array $options, string $roomCode, string $kind, string $id): ?int
    {
        foreach (array_keys($options) as $index) {
            if (hash_equals(self::id($roomCode, $kind, $index), $id)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * A shuffle that is different for every player but the same every time
     * the same player asks (so the form does not rearrange itself on a poll).
     *
     * @param  array<int, array{id: string, label: string}>  $options
     * @return array<int, array{id: string, label: string}>
     */
    private static function shuffle(array $options, string $playerToken, string $kind, string $correctId): array
    {
        usort($options, fn ($a, $b) => strcmp(
            hash_hmac('sha256', "{$playerToken}|{$kind}|{$a['id']}", (string) config('app.key')),
            hash_hmac('sha256', "{$playerToken}|{$kind}|{$b['id']}", (string) config('app.key')),
        ));

        // A valid shuffle can still put the first file option first by chance.
        // That is unacceptable here: every case file keeps the correct answer
        // first for authors, so keep it away from the leading wire position.
        if (count($options) > 1 && $options[0]['id'] === $correctId) {
            [$options[0], $options[1]] = [$options[1], $options[0]];
        }

        return $options;
    }
}
