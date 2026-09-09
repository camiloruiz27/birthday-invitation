<?php

namespace App\Modules\Platform\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Where Bold sends the buyer back after they finish (or abandon) checkout.
 *
 * The webhook is the source of truth for whether the order actually went
 * through — this controller only ever reads what the webhook has already
 * decided. There is no ordering guarantee between "Bold redirects the browser
 * back" and "Bold's webhook reaches our server", so the buyer can land here
 * slightly before the order is resolved — the page polls this same route
 * (Inertia partial reload of `order`, see hooks/usePoll.js) until it isn't
 * pending anymore.
 */
class PaymentCallbackController extends Controller
{
    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return Inertia::render('Payments/Confirming', [
            'order' => fn () => [
                'id' => $order->id,
                'status' => $order->fresh()->status,
                'type' => $order->type,
                'case_slug' => $order->mysteryCase?->slug,
            ],
        ]);
    }
}
