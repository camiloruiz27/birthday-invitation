<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Actions\StartCheckout;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Acquiring a case: either a real Bold checkout, or — while there is no
 * payment provider configured — a simulation that takes no money.
 *
 * The simulated path writes an entitlement marked `grant` through the same
 * action a real purchase uses, so it is always distinguishable from a paid
 * one in the data, and is disabled outside local/dev by default
 * (platform.simulated_checkout) so a deployed site cannot give cases away.
 *
 * The real path never touches Entitlement directly: it only starts a Bold
 * checkout. The entitlement is granted later, by BoldWebhookController, once
 * Bold confirms the card actually charged — unless a promo code discounted it
 * to zero, in which case StartCheckout delivers it immediately itself.
 *
 * A real purchase never posts straight to store() from the catalog: the
 * buyer sees review() first (Payments/Review) — what they are buying, any
 * discount applied, the actual total — and store() is only ever reached from
 * that screen's own confirm button. The simulated path skips review()
 * entirely; there is nothing to confirm about a purchase that costs nothing.
 */
class CheckoutController extends Controller
{
    public function review(Request $request, string $slug, RedeemPromoCode $promos): InertiaResponse
    {
        abort_unless(config('platform.payments.enabled'), 404);

        $case = MysteryCase::published()->where('slug', $slug)->firstOrFail();

        if ($request->user()->ownsCase($case)) {
            return Inertia::render('Payments/Review', [
                'alreadyOwned' => true,
                'case_slug' => $case->slug,
            ]);
        }

        $promoCode = $request->query('promo_code');
        $finalAmount = $case->price_amount;
        $discountAmount = null;
        $promoError = null;

        if ($promoCode) {
            try {
                $preview = $promos->preview($request->user(), $promoCode, $case->price_amount);
                $finalAmount = $preview->discountedAmount;
                $discountAmount = $case->price_amount - $finalAmount;
                // The stored, normalized code — not whatever case the buyer
                // happened to type it in, or paste from a mixed-case URL.
                $promoCode = $preview->promoCode->code;
            } catch (PromoCodeException $exception) {
                $promoError = $exception->getMessage();
                $promoCode = null;
            }
        }

        return Inertia::render('Payments/Review', [
            'type' => 'case',
            'case_slug' => $case->slug,
            'title' => $case->name,
            'subtitle' => $case->tagline,
            'original_amount' => $case->price_amount,
            'currency' => $case->currency,
            'promo_code' => $promoCode,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'promo_error' => $promoError,
        ]);
    }

    public function store(Request $request, string $slug, GrantCaseAccess $access, StartCheckout $checkout): Response
    {
        $case = MysteryCase::published()->where('slug', $slug)->firstOrFail();

        if ($request->user()->ownsCase($case)) {
            return redirect()
                ->route('cases.show', $case->slug)
                ->with('status', 'Ya tienes este caso en tu biblioteca.');
        }

        if (config('platform.payments.enabled')) {
            $promoCode = $request->validate(['promo_code' => ['nullable', 'string', 'max:60']])['promo_code'] ?? null;

            try {
                $order = $checkout->forCase($request->user(), $case, $promoCode);
            } catch (PromoCodeException $exception) {
                throw ValidationException::withMessages(['promo_code' => $exception->getMessage()]);
            }

            // A code that discounted this to zero never got a checkout_url —
            // StartCheckout delivered it directly and there is nothing to
            // send the buyer to Bold for. Either way, payments.confirm shows
            // the right thing (it already handles an order that is approved
            // the instant it loads). Inertia::location() rather than a plain
            // redirect for the Bold case: it leaves the app entirely for
            // checkout.bold.co, and a plain Inertia redirect would try to
            // follow it as an XHR visit instead of navigating the browser away.
            if ($order->checkout_url) {
                return Inertia::location($order->checkout_url);
            }

            return redirect()->route('payments.confirm', $order);
        }

        abort_unless(config('platform.simulated_checkout'), 404);

        $access->grant($request->user(), $case, Entitlement::SOURCE_GRANT);

        return redirect()
            ->route('dashboard')
            ->with('status', "\"{$case->name}\" está en tu biblioteca. Ya puedes crear una partida.");
    }
}
