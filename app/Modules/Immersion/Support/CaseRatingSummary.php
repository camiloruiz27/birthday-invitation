<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\CaseRating;
use Illuminate\Support\Collection;

/** Small, reusable public aggregates for a group of catalog case slugs. */
class CaseRatingSummary
{
    /** @return Collection<string, array{average: float, count: int}> */
    public static function forCases(iterable $slugs): Collection
    {
        $slugs = collect($slugs)->filter()->unique()->values();

        if ($slugs->isEmpty()) {
            return collect();
        }

        return CaseRating::query()
            ->selectRaw('case_slug, COUNT(*) as ratings_count, AVG(rating_half_steps) / 2 as ratings_average')
            ->whereIn('case_slug', $slugs)
            ->groupBy('case_slug')
            ->get()
            ->mapWithKeys(fn (CaseRating $rating) => [
                $rating->case_slug => [
                    'average' => round((float) $rating->ratings_average, 1),
                    'count' => (int) $rating->ratings_count,
                ],
            ]);
    }
}
