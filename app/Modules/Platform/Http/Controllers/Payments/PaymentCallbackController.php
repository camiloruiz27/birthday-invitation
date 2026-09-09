<?php

namespace App\Modules\Platform\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Actions\SettleOrder;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Where Bold sends the buyer back after checkout.
 *
 * Bold's redirect carries its own query params (bold-order-id, bold-tx-status)
 * saying how it went — deliberately ignored here. Trusting them would let
 * anyone grant themselves a case by editing the URL; a webhook's signature is
 * what actually proves a payment happened.
 *
 * But a webhook is a best-effort push that can be slow, misconfigured, or
 * never arrive, and this page is the buyer's only window into what happened
 * to their money — so instead of only ever waiting, it actively re-asks Bold
 * every time it loads (first load, and every poll while pending; see
 * SettleOrder's docblock for why both paths are safe to run at once).
 */
class PaymentCallbackController extends Controller
{
    public function show(
        Request $request,
        Order $order,
        PaymentProvider $payments,
        SettleOrder $settle,
        AiCredits $credits,
    ): Response {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->isPending()) {
            try {
                $settle->apply($order, $payments->checkStatus($order));
            } catch (\Throwable $exception) {
                // The webhook remains the fallback for this attempt; the next
                // poll tries the active check again. A hiccup here must never
                // break rendering the page the buyer is staring at.
                Log::warning('platform_payment_status_check_exception', [
                    'order_id' => $order->id,
                    'message' => $exception->getMessage(),
                ]);
            }

            $order->refresh();
        }

        return Inertia::render('Payments/Confirming', [
            'order' => fn () => [
                'id' => $order->id,
                'status' => $order->status,
                'type' => $order->type,
                'case_slug' => $order->mysteryCase?->slug,
                'credits_granted' => $order->credits_granted,
                'wallet_available' => $order->type === Order::TYPE_CREDIT_PACKAGE
                    ? $credits->walletFor($order->user)->available()
                    : null,
            ],
        ]);
    }
}
