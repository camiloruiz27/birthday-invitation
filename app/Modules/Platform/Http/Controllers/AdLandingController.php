<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\GameQuota;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Http\Resources\CaseCardData;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Support\Attribution;
use App\Modules\Platform\Support\Mechanics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The page paid ads point at.
 *
 * One page template in two modes, so an ad can lead to a specific case
 * (/jugar/{slug}) or to the platform as a whole (/jugar) where the visitor
 * picks. Nothing about WHICH ad brought them lives here: the campaign, click
 * ids and promo code travel on the URL and are remembered by
 * CaptureAttribution for any page, so the landing needs no campaign logic.
 *
 * Link previews are the reason this controller also builds head tags. The app
 * renders in the browser, but TikTok, Meta and WhatsApp read the server's
 * HTML only, so the title, description and image of what is being shared have
 * to be in that first response (see the `seo` view data in app.blade.php).
 */
class AdLandingController extends Controller
{
    public function platform(Request $request, RedeemPromoCode $promos): Response
    {
        // The code that came with the ad (or an earlier click), if it is
        // something worth showing: an ad that promises a discount has to keep
        // the promise on the page it lands on, not only at the checkout.
        $code = Attribution::pendingPromo($request, $request->user());
        $offer = $code ? $promos->landingOffer($request->user(), $code) : null;
        $promo = $offer ? PromoCode::where('code', $offer['code'])->first() : null;

        return Inertia::render('Public/AdLanding', [
            'mode' => 'platform',
            'case' => null,
            'offer' => $offer,
            'featured' => fn () => MysteryCase::published()
                ->ordered()
                ->limit(3)
                ->get()
                // thumb_url: the web-sized cover. summary() carries the
                // original 1.5-2 MB art, which three cards must not download.
                ->map(fn (MysteryCase $case) => CaseCardData::summary($case) + [
                    'thumb_url' => $case->landingCoverUrl(),
                    // What each card costs WITH the offer, worked out by the
                    // same code that charges, so the page never promises a
                    // price the checkout will not give.
                    'offer_amount' => $promo && $promo->isDiscount()
                        ? $promo->discountedAmount($case->price_amount)
                        : null,
                ])
                ->values(),
            'mechanics' => Mechanics::list(),
            'canPurchase' => (bool) config('platform.payments.enabled'),
            'gamesPerCase' => app(GameQuota::class)->limit(),
        ])->withViewData([
            'seo' => [
                'title' => 'MisterioCode — Resuelvan un crimen en equipo',
                'description' => 'Un caso, un equipo y un reloj. El expediente llega en tiempo real, interrogan a los sospechosos y acusan. Elijan su caso.',
                'canonical' => route('home'),
                'robots' => 'noindex, follow',
                'liteFonts' => true,
            ],
            'preloadImage' => '/brand/hero-01-1200.jpg',
        ]);
    }

    public function show(Request $request, string $slug, RedeemPromoCode $promos): Response
    {
        // Same rule as the case's own page: route model binding would also
        // serve unpublished rows, and an ad must never lead to a case that
        // is not on sale.
        $case = MysteryCase::published()->where('slug', $slug)->firstOrFail();

        $landing = CaseCardData::landing($case);

        $code = Attribution::pendingPromo($request, $request->user());
        $offer = $code ? $promos->landingOffer($request->user(), $code, $case) : null;

        return Inertia::render('Public/AdLanding', [
            'mode' => 'case',
            'case' => $landing,
            'offer' => $offer,
            'owned' => (bool) $request->user()?->ownsCase($case),
            'canPurchase' => (bool) config('platform.payments.enabled'),
            'canSimulatePurchase' => (bool) config('platform.simulated_checkout'),
            'gamesPerCase' => app(GameQuota::class)->limit(),
        ])->withViewData([
            'seo' => array_filter([
                'title' => "{$case->name} — MisterioCode",
                'description' => $landing['ad']['hook'],
                'image' => $case->ogImageUrl(),
                // The case's own page is the canonical home of this content;
                // the ad landing must not compete with it in search.
                'canonical' => route('cases.show', $case->slug),
                'robots' => 'noindex, follow',
                'liteFonts' => true,
            ]),
            'preloadImage' => $landing['landing_cover_url'],
        ]);
    }
}
