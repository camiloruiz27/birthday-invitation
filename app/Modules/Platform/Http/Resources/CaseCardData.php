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
     * What the ad landing shows: the detail, a web-sized cover, and the ad
     * copy resolved field by field — whatever the manifest wrote, and the
     * case's own facts for whatever it did not, so a case nobody has written
     * ad copy for still gets a complete page.
     *
     * Kept apart from summary() on purpose: that shape is the public catalog
     * contract and is pinned by a test.
     *
     * @return array<string, mixed>
     */
    public static function landing(MysteryCase $case): array
    {
        $ad = (array) $case->ad;

        $bullets = array_values(array_filter((array) ($ad['bullets'] ?? []), 'is_string'));

        return self::detail($case) + [
            'landing_cover_url' => $case->landingCoverUrl(),
            'ad' => [
                'hook' => $ad['hook'] ?? $case->tagline ?? $case->name,
                'bullets' => $bullets !== [] ? array_slice($bullets, 0, 3) : self::factBullets($case),
                'cta' => $ad['cta'] ?? 'Quiero este caso',
            ],
        ];
    }

    /**
     * Selling points taken from the case's own data rather than invented.
     *
     * @return array<int, string>
     */
    private static function factBullets(MysteryCase $case): array
    {
        $bullets = [];

        if ($case->min_players && $case->max_players) {
            $bullets[] = $case->min_players === $case->max_players
                ? "Para {$case->min_players} jugadores, juntos o a distancia"
                : "De {$case->min_players} a {$case->max_players} jugadores, juntos o a distancia";
        }

        if ($case->duration_minutes) {
            $bullets[] = "{$case->duration_minutes} minutos con el expediente llegando en tiempo real";
        }

        $bullets[] = 'Pago único: el caso es tuyo para siempre';

        return array_slice($bullets, 0, 3);
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
