<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Http\Request;

/**
 * "This visitor is signing in (or registering) in order to buy THAT."
 *
 * A case page or an ad points at /registro?case={slug} or /ingresar?case={slug}.
 * Turning it into where to land afterwards goes through the session's own
 * `url.intended` — the same slot a login that bounced off a protected page
 * uses — so every existing redirect (`redirect()->intended(...)`) honours it
 * with no extra code.
 *
 * Only for a case that exists and is PUBLISHED, and the destination is a route
 * built from that validated slug, never anything taken from the request, so the
 * parameter cannot be used to send someone to an arbitrary address. The one
 * place this rule lives, so registering and signing in cannot drift apart.
 */
final class PurchaseIntent
{
    /**
     * Remembers the destination for `?case=` and returns the slug when it was
     * valid, null otherwise (the screens pass it on to their cross links).
     */
    public static function remember(Request $request): ?string
    {
        $slug = $request->query('case');

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        if (! MysteryCase::published()->where('slug', $slug)->exists()) {
            return null;
        }

        $request->session()->put(
            'url.intended',
            config('platform.payments.enabled')
                ? route('cases.checkout.review', $slug)
                : route('cases.show', $slug)
        );

        return $slug;
    }
}
