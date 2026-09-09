<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Immersion\Support\GameCost;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Actions\StartCheckout;
use App\Modules\Platform\Exceptions\PromoCodeException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * The AI credit wallet: what you have, what it goes on, and how to get more.
 *
 * Lives in the platform because buying is commerce, while the wallet itself
 * belongs to the engine — the platform reads the engine, never the other way
 * round.
 */
class CreditsController extends Controller
{
    public function index(Request $request, AiCredits $credits, GameCost $cost): Response
    {
        $user = $request->user();
        $wallet = $credits->walletFor($user);

        return Inertia::render('Credits', [
            'wallet' => [
                'available' => $wallet->available(),
                'reserved' => $wallet->reserved,
                'total' => $wallet->total(),
            ],

            // What a credit actually buys, so the balance means something
            // instead of being an abstract number.
            'costs' => [
                'question' => $cost->questionCost(),
                'endings' => $cost->endingPrices(),
            ],

            'packages' => array_values((array) config('platform.credit_packages', [])),
            'simulated' => (bool) config('platform.simulated_checkout'),
            'canPurchase' => (bool) config('platform.payments.enabled'),

            // Recent history first: "where did my credits go" is the only
            // question this page really has to answer.
            'ledger' => fn () => CreditLedgerEntry::where('user_id', $user->id)
                ->with('game:id,name')
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (CreditLedgerEntry $entry) => [
                    'id' => $entry->id,
                    'reason' => $entry->reason,
                    'delta' => $entry->delta,
                    'balance_after' => $entry->balance_after,
                    'reserved_after' => $entry->reserved_after,
                    'note' => $entry->note,
                    'game' => $entry->game?->name,
                    'created_at' => $entry->created_at,
                ]),

            // Games currently holding capacity, so a Game Master who cannot
            // start a new one can see exactly which case is holding what.
            'holds' => fn () => $user->games()
                ->whereHas('creditHold', fn ($hold) => $hold->whereNull('released_at'))
                ->with('creditHold')
                ->get()
                ->map(fn ($game) => [
                    'game_id' => $game->id,
                    'name' => $game->name,
                    'status' => $game->status,
                    'amount' => $game->creditHold->amount,
                    'spent' => $game->creditHold->spent,
                    'remaining' => $game->creditHold->remaining(),
                ])
                ->values(),
        ]);
    }

    /**
     * The review screen a real purchase always goes through first — package,
     * any discount applied, the actual total — before purchase() below ever
     * runs. The simulated top-up skips this entirely; there is nothing to
     * confirm about a purchase that costs nothing.
     */
    public function review(Request $request, RedeemPromoCode $promos): Response
    {
        abort_unless(config('platform.payments.enabled'), 404);

        $package = collect((array) config('platform.credit_packages', []))
            ->firstWhere('id', $request->query('package'));

        if (! $package) {
            abort(404);
        }

        $promoCode = $request->query('promo_code');
        $finalAmount = (int) $package['price_amount'];
        $discountAmount = null;
        $promoError = null;

        if ($promoCode) {
            try {
                $preview = $promos->preview($request->user(), $promoCode, (int) $package['price_amount']);
                $finalAmount = $preview->discountedAmount;
                $discountAmount = (int) $package['price_amount'] - $finalAmount;
                // The stored, normalized code — not whatever case the buyer
                // happened to type it in, or paste from a mixed-case URL.
                $promoCode = $preview->promoCode->code;
            } catch (PromoCodeException $exception) {
                $promoError = $exception->getMessage();
                $promoCode = null;
            }
        }

        return Inertia::render('Payments/Review', [
            'type' => 'credit_package',
            'package_id' => $package['id'],
            'title' => $package['name'],
            'subtitle' => "{$package['credits']} créditos — {$package['summary']}",
            'original_amount' => (int) $package['price_amount'],
            'currency' => $package['currency'],
            'promo_code' => $promoCode,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'promo_error' => $promoError,
        ]);
    }

    /**
     * A real Bold checkout for a credit package, or — while there is no
     * payment provider configured — a simulated top-up that takes no money
     * and lands through the same single writer (AiCredits::grant) a real
     * purchase uses.
     */
    public function purchase(Request $request, AiCredits $credits, StartCheckout $checkout): HttpResponse
    {
        $data = $request->validate([
            'package' => ['required', 'string'],
            'promo_code' => ['nullable', 'string', 'max:60'],
        ]);

        $package = collect((array) config('platform.credit_packages', []))
            ->firstWhere('id', $data['package']);

        if (! $package) {
            throw ValidationException::withMessages([
                'package' => 'Ese paquete de creditos no existe.',
            ]);
        }

        if (config('platform.payments.enabled')) {
            try {
                $order = $checkout->forCreditPackage($request->user(), $package, $data['promo_code'] ?? null);
            } catch (PromoCodeException $exception) {
                throw ValidationException::withMessages(['promo_code' => $exception->getMessage()]);
            }

            // A 100%-off code delivers directly (see StartCheckout) and never
            // gets a checkout_url. See CheckoutController::store for why the
            // Bold case is Inertia::location() and not a plain redirect.
            if ($order->checkout_url) {
                return Inertia::location($order->checkout_url);
            }

            return redirect()->route('payments.confirm', $order);
        }

        abort_unless(config('platform.simulated_checkout'), 404);

        $credits->grant(
            $request->user(),
            (int) $package['credits'],
            CreditLedgerEntry::REASON_TOPUP,
            "Recarga simulada: {$package['name']}"
        );

        return back()->with('status', "Se anadieron {$package['credits']} creditos a tu cuenta.");
    }
}
