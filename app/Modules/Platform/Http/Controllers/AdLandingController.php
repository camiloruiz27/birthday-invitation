<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\GameQuota;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Http\Resources\CaseCardData;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Pricing\QuoteBuilder;
use App\Modules\Platform\Support\Attribution;
use App\Modules\Platform\Support\Market;
use App\Modules\Platform\Support\Mechanics;
use App\Modules\Platform\Support\PublishedCases;
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
        $offer = $this->inMarket($request, $code ? $promos->landingOffer($request->user(), $code) : null);
        $promo = $offer ? PromoCode::where('code', $offer['code'])->first() : null;
        $quotes = QuoteBuilder::make();

        return Inertia::render('Public/AdLanding', [
            'mode' => 'platform',
            'case' => null,
            'offer' => $offer,
            'featured' => fn () => PublishedCases::all(3)
                // thumb_url: the web-sized cover. summary() carries the
                // original 1.5-2 MB art, which three cards must not download.
                ->map(fn (MysteryCase $case) => CaseCardData::summary($case) + [
                    'thumb_url' => $case->landingCoverUrl(),
                    // What each card costs WITH the offer, worked out by the
                    // same code that charges, so the page never promises a
                    // price the checkout will not give.
                    // Quoted by the same code the checkout uses: a percent code
                    // off the USD price, anything else discounted in COP and
                    // converted once.
                    'offer_amount' => $promo && $promo->isDiscount()
                        ? $quotes->forRequest(
                            $case->price_amount,
                            $promo->discountedAmount($case->price_amount),
                            $request,
                            QuoteBuilder::percentOff($promo),
                        )->total
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
        $case = PublishedCases::find($slug) ?? abort(404);

        $landing = CaseCardData::landing($case);

        $code = Attribution::pendingPromo($request, $request->user());
        $offer = $this->inMarket($request, $code ? $promos->landingOffer($request->user(), $code, $case) : null, $case);

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

    /**
     * RedeemPromoCode::landingOffer works in COP; this puts its amounts in the
     * visitor's market, through the same quote the checkout uses.
     *
     * A fixed code is a COP amount, so for an international visitor it has no
     * single USD figure unless a case is chosen — without one the landing
     * simply shows the normal price (the code still applies at checkout),
     * which is the honest thing to do rather than print a number that can be
     * wrong.
     */
    private function inMarket(Request $request, ?array $offer, ?MysteryCase $case = null): ?array
    {
        if ($offer === null || Market::fromRequest($request) !== Market::INTL) {
            return $offer;
        }

        if ($offer['kind'] === 'fixed' && ! $case) {
            return null;
        }

        if ($case && $offer['final_amount'] !== null) {
            $quote = QuoteBuilder::make()->forRequest(
                $case->price_amount,
                $offer['final_amount'],
                $request,
                QuoteBuilder::percentOff(PromoCode::where('code', $offer['code'])->first()),
            );

            $offer['final_amount'] = $quote->total;
            $offer['discount_amount'] = $quote->discount;

            if ($offer['kind'] === 'fixed') {
                // What the code takes off, in the buyer's currency.
                $offer['value'] = $quote->discount;
            }
        }

        return $offer;
    }
}
