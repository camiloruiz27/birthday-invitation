<?php

namespace App\Modules\Platform\Http\Resources;

use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\Mechanics;

/**
 * Shapes a catalog row for the front end.
 *
 * Deliberately explicit rather than handing Inertia the Eloquent model: the
 * catalog is public, so what crosses to the browser is listed here once
 * instead of being whatever columns the table happens to have.
 */
class CaseCardData
{
    /**
     * Summary for a listing card.
     *
     * @return array<string, mixed>
     */
    public static function summary(MysteryCase $case): array
    {
        return [
            'slug' => $case->slug,
            'name' => $case->name,
            'tagline' => $case->tagline,
            'cover_url' => $case->coverUrl(),
            'difficulty' => $case->difficulty,
            'duration_minutes' => $case->duration_minutes,
            'min_players' => $case->min_players,
            'max_players' => $case->max_players,
            'price_amount' => $case->price_amount,
            'currency' => $case->currency,
            'mechanics' => Mechanics::describe((array) $case->mechanics),
        ];
    }

    /**
     * Everything the case's own page shows.
     *
     * @return array<string, mixed>
     */
    public static function detail(MysteryCase $case): array
    {
        return self::summary($case) + [
            'description' => $case->description,
            'uses_ai' => collect(Mechanics::describe((array) $case->mechanics))
                ->contains(fn (array $mechanic) => $mechanic['ai']),
        ];
    }
}
