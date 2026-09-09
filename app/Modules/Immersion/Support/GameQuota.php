<?php

namespace App\Modules\Immersion\Support;

use App\Models\User;

/**
 * How many games an account may keep of ONE case.
 *
 * The quota is per case, not per account: someone who owns four cases can have
 * six games of each. Running out of room for one case says nothing about the
 * others.
 *
 * Every game counts, whatever its state — a finished one still occupies a
 * slot. The only way to free one is to delete a game of that same case.
 */
class GameQuota
{
    public function limit(): int
    {
        return (int) config('immersion.games.max_per_case', 6);
    }

    public function used(User $user, string $caseSlug): int
    {
        return $user->games()->where('case_slug', $caseSlug)->count();
    }

    public function isFull(User $user, string $caseSlug): bool
    {
        return $this->used($user, $caseSlug) >= $this->limit();
    }

    /**
     * The shape the front end needs to explain the quota for one case without
     * doing its own arithmetic.
     *
     * @return array{used: int, limit: int, remaining: int, full: bool}
     */
    public function summary(User $user, string $caseSlug): array
    {
        return $this->shape($this->used($user, $caseSlug));
    }

    /**
     * Quota for several cases at once, keyed by slug.
     *
     * One grouped query rather than one per case: the create page and the
     * library both need the whole library's worth.
     *
     * @param  iterable<string>  $caseSlugs
     * @return array<string, array{used: int, limit: int, remaining: int, full: bool}>
     */
    public function forCases(User $user, iterable $caseSlugs): array
    {
        $counts = $user->games()
            ->selectRaw('case_slug, count(*) as total')
            ->groupBy('case_slug')
            ->pluck('total', 'case_slug');

        $summaries = [];

        foreach ($caseSlugs as $slug) {
            $summaries[$slug] = $this->shape((int) ($counts[$slug] ?? 0));
        }

        return $summaries;
    }

    /**
     * Is there room to create a game of at least one of these cases? Drives
     * whether "new game" is offered at all.
     *
     * @param  iterable<string>  $caseSlugs
     */
    public function hasRoomForAny(User $user, iterable $caseSlugs): bool
    {
        foreach ($this->forCases($user, $caseSlugs) as $summary) {
            if (! $summary['full']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{used: int, limit: int, remaining: int, full: bool}
     */
    private function shape(int $used): array
    {
        $limit = $this->limit();

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => max(0, $limit - $used),
            'full' => $used >= $limit,
        ];
    }
}
