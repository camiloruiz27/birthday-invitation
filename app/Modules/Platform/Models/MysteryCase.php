<?php

namespace App\Modules\Platform\Models;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A mystery case as a catalog product.
 *
 * This is the platform's view of a case: what it costs, whether it is on sale,
 * and how it is presented to a shopper. The playable content lives in the
 * engine's case manifest and is reached through definition().
 */
class MysteryCase extends Model
{
    protected $table = 'mystery_cases';

    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'description',
        'ad',
        'cover_path',
        'difficulty',
        'duration_minutes',
        'min_players',
        'max_players',
        'mechanics',
        'content_version',
        'price_amount',
        'currency',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'mechanics' => 'array',
        'ad' => 'array',
        'duration_minutes' => 'integer',
        'min_players' => 'integer',
        'max_players' => 'integer',
        'price_amount' => 'integer',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Games played from this case. Joined by slug, not by foreign key, so the
     * engine never depends on the catalog existing.
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'case_slug', 'slug');
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    /**
     * The playable case behind this product. Throws when the catalog row
     * points at a manifest that is not deployed — that is a deployment error,
     * not something to render around.
     */
    public function definition(): CaseDefinition
    {
        return app(CaseRegistry::class)->get($this->slug);
    }

    /**
     * Is the playable content for this product actually installed? Guards the
     * catalog against showing a product nobody could play.
     */
    public function isPlayable(): bool
    {
        return app(CaseRegistry::class)->has($this->slug);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && ! $this->published_at->isFuture();
    }

    public function isFree(): bool
    {
        return $this->price_amount === 0;
    }

    /**
     * Cover art for the catalog, falling back to the victim's portrait so a
     * case without dedicated cover art still renders.
     */
    public function coverUrl(): ?string
    {
        if ($this->cover_path) {
            return $this->cover_path;
        }

        if (! $this->isPlayable()) {
            return null;
        }

        return $this->definition()->victim()['photo_url'] ?: null;
    }

    /**
     * A web-sized cover (~1000 px wide) for the ad landing, where the
     * original 1.4-2 MB art would be most of what a phone on a TikTok or
     * Instagram in-app browser has to download. Made by
     * platform:build-case-images next to the cover; falls back to the
     * original when it has not been made.
     */
    public function landingCoverUrl(): ?string
    {
        return $this->derivedCoverUrl('landing.jpg') ?? $this->coverUrl();
    }

    /**
     * The 1200x630 share image for link previews (WhatsApp, TikTok, Meta),
     * or null when none has been made — the caller then uses the site-wide
     * default rather than a 2 MB, wrongly-cropped original.
     */
    public function ogImageUrl(): ?string
    {
        return $this->derivedCoverUrl('og.jpg');
    }

    /**
     * A derived image that lives beside the cover art, if it exists on disk.
     */
    private function derivedCoverUrl(string $file): ?string
    {
        if (! $this->cover_path) {
            return null;
        }

        $url = rtrim(dirname($this->cover_path), '/').'/'.$file;

        return is_file(public_path(ltrim($url, '/'))) ? $url : null;
    }

    public function hasMechanic(string $mechanic): bool
    {
        return in_array($mechanic, (array) $this->mechanics, true);
    }
}
