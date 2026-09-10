<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Rules\CaptchaRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Redeeming a gift code (a free case, free credits, or the bundle of both).
 *
 * Deliberately separate from checkout: a gift has no price to apply anything
 * against, so it gets its own small screen rather than a field bolted onto
 * the buy flow. A discount code — the other kind RedeemPromoCode handles —
 * is entered on the checkout screens themselves instead; see
 * CheckoutController and CreditsController.
 */
class RedeemCodeController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Redeem');
    }

    public function store(Request $request, RedeemPromoCode $promos): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
        ] + CaptchaRule::rules($request->ip()));

        try {
            $promo = $promos->redeemGift($request->user(), $data['code']);
        } catch (PromoCodeException $exception) {
            throw ValidationException::withMessages(['code' => $exception->getMessage()]);
        }

        $pieces = array_filter([
            $promo->grants_case_slug ? 'un caso' : null,
            $promo->grants_credits ? "{$promo->grants_credits} creditos" : null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Código canjeado: recibiste '.implode(' y ', $pieces).'.');
    }
}
