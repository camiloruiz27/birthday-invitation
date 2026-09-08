<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Immersion\Support\GameCost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

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
     * Stand-in for a real top-up, mirroring CheckoutController: no money, no
     * payment details, and the credits land through the same single writer a
     * paid top-up will use.
     */
    public function purchase(Request $request, AiCredits $credits): RedirectResponse
    {
        abort_unless(config('platform.simulated_checkout'), 404);

        $data = $request->validate([
            'package' => ['required', 'string'],
        ]);

        $package = collect((array) config('platform.credit_packages', []))
            ->firstWhere('id', $data['package']);

        if (! $package) {
            throw ValidationException::withMessages([
                'package' => 'Ese paquete de creditos no existe.',
            ]);
        }

        $credits->grant(
            $request->user(),
            (int) $package['credits'],
            CreditLedgerEntry::REASON_TOPUP,
            "Recarga simulada: {$package['name']}"
        );

        return back()->with('status', "Se anadieron {$package['credits']} creditos a tu cuenta.");
    }
}
