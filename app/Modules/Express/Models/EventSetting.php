<?php

namespace App\Modules\Express\Models;

use App\Modules\Platform\Support\Ops;
use Illuminate\Database\Eloquent\Model;

/**
 * Administrative overrides for a configured Express event.
 *
 * The public room poll calls ExpressEvents::find(), so this lookup has its
 * own Ops-cache entry. That preserves Express' no-database-read polling
 * guarantee even while the promotion remains editable from administration.
 */
class EventSetting extends Model
{
    protected $table = 'express_event_settings';

    protected $fillable = [
        'event_slug', 'promo_code', 'updated_by', 'display_name', 'tagline',
        'landing_title', 'landing_emphasis', 'closed_title', 'closed_reason',
        'active', 'ends_at', 'ends_at_overridden', 'utm_medium', 'utm_campaign',
    ];

    protected $casts = [
        'active' => 'boolean',
        'ends_at' => 'datetime',
        'ends_at_overridden' => 'boolean',
    ];

    private const CACHE_PREFIX = 'express:event-settings:';

    private const CACHE_TTL = 10800;

    public static function promoCode(string $eventSlug, ?string $fallback): ?string
    {
        $values = self::forEvent($eventSlug);

        return $values !== null
            ? ($values['promo_code'] ?: null)
            : $fallback;
    }

    /** @return array<string, mixed>|null */
    public static function forEvent(string $eventSlug): ?array
    {
        $cached = Ops::store()->remember(
            self::CACHE_PREFIX.$eventSlug,
            self::CACHE_TTL,
            function () use ($eventSlug): array {
                $setting = self::query()->where('event_slug', $eventSlug)->first();

                return $setting === null
                    ? ['exists' => false]
                    : ['exists' => true, 'values' => $setting->toArray()];
            },
        );

        return is_array($cached) && ($cached['exists'] ?? false) && is_array($cached['values'] ?? null)
            ? $cached['values']
            : null;
    }

    public static function forgetCached(string $eventSlug): void
    {
        Ops::store()->forget(self::CACHE_PREFIX.$eventSlug);
    }
}
