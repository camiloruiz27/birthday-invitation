<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The cases on sale, read through a one-minute cache.
 *
 * The public pages (landing, catalog, pricing, an ad's landing) are what a
 * campaign sends strangers to, and every anonymous visit used to run the same
 * query on its own MySQL connection — out of the host's 500-per-hour allowance
 * that the players of a live game also need. The rows change when a case is
 * published or repriced, which happens a few times a year, so a minute of
 * staleness costs nothing.
 *
 * Only the ROWS are cached. What a card shows depends on the visitor (the price
 * in their market, whether they own the case), so cards are still built per
 * request from these rows.
 */
class PublishedCases
{
    private const TTL_SECONDS = 60;

    /**
     * Published cases in catalog order.
     *
     * @return Collection<int, MysteryCase>
     */
    public static function all(?int $limit = null): Collection
    {
        return Cache::remember(
            'platform:cases:published:'.($limit ?? 'all'),
            self::TTL_SECONDS,
            fn () => MysteryCase::published()->ordered()->when($limit, fn ($query) => $query->limit($limit))->get()
        );
    }

    /**
     * Forgets what is cached, so a case published, repriced or taken down
     * through the application is reflected at once instead of a minute later.
     * (A change made straight in the database cannot announce itself, and
     * takes up to a minute to show.)
     */
    public static function forget(?string $slug = null): void
    {
        Cache::forget('platform:cases:published:all');
        Cache::forget('platform:cases:published:3');

        if ($slug !== null) {
            Cache::forget('platform:cases:slug:'.$slug);
        }
    }

    /**
     * One published case, or null. Route model binding would also serve
     * unpublished rows; the catalog must only ever show what is on sale.
     */
    public static function find(string $slug): ?MysteryCase
    {
        // A slug is lowercase words and dashes. Anything else cannot match a
        // case, and a bot walking random addresses must not be able to fill the
        // cache directory with one file per guess.
        if (! preg_match('/^[a-z0-9-]{1,80}$/', $slug)) {
            return null;
        }

        return Cache::remember(
            'platform:cases:slug:'.$slug,
            self::TTL_SECONDS,
            fn () => MysteryCase::published()->where('slug', $slug)->first()
        );
    }
}
