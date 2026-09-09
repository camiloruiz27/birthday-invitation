<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Actions\StartCheckout;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
 * Bold confirms the card actually charged.
 */
class CheckoutController extends Controller
{
    public function store(Request $request, string $slug, GrantCaseAccess $access, StartCheckout $checkout): Response
    {
        $case = MysteryCase::published()->where('slug', $slug)->firstOrFail();

        if ($request->user()->ownsCase($case)) {
            return redirect()
                ->route('cases.show', $case->slug)
                ->with('status', 'Ya tienes este caso en tu biblioteca.');
        }

        if (config('platform.payments.enabled')) {
            $order = $checkout->forCase($request->user(), $case);

            // Inertia::location(), not redirect(): this leaves the app
            // entirely for checkout.bold.co, and a plain Inertia redirect
            // would try to follow it as an XHR visit instead of navigating
            // the browser away.
            return Inertia::location($order->checkout_url);
        }

        abort_unless(config('platform.simulated_checkout'), 404);

        $access->grant($request->user(), $case, Entitlement::SOURCE_GRANT);

        return redirect()
            ->route('dashboard')
            ->with('status', "\"{$case->name}\" está en tu biblioteca. Ya puedes crear una partida.");
    }
}
