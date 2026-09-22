<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\MysteryCase;
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
    public function show(Request $request): Response
    {
        return Inertia::render('Redeem', [
            // Only cases the visitor doesn't already own: an "any case"
            // code should never let them pick one that would burn the
            // redemption for nothing (see RedeemPromoCode::resolveGiftCase).
            'cases' => MysteryCase::published()
                ->get(['slug', 'name'])
                ->reject(fn (MysteryCase $case) => $request->user()->ownsCase($case))
                ->values(),
        ]);
    }

    public function store(Request $request, RedeemPromoCode $promos): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'case_slug' => ['nullable', 'string'],
        ] + CaptchaRule::rules($request->ip()));

        $caseSlug = $data['case_slug'] ?? null;

        if (! $caseSlug && $promos->needsCaseChoice($request->user(), $data['code'])) {
            throw ValidationException::withMessages([
                'case_slug' => 'Este código te deja elegir el caso: escoge uno de la lista.',
            ]);
        }

        try {
            $promo = $promos->redeemGift($request->user(), $data['code'], $caseSlug);
        } catch (PromoCodeException $exception) {
            throw ValidationException::withMessages(['code' => $exception->getMessage()]);
        }

        $pieces = array_filter([
            ($promo->grants_case_slug || $promo->grants_any_case) ? 'un caso' : null,
            $promo->grants_credits ? "{$promo->grants_credits} creditos" : null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Código canjeado: recibiste '.implode(' y ', $pieces).'.');
    }
}
