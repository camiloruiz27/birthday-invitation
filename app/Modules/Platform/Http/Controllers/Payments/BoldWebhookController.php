<?php

namespace App\Modules\Platform\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\SettleOrder;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use App\Modules\Platform\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Where Bold tells us what happened to a payment link.
 *
 * Not behind `auth` (Bold is not a logged-in user) and excluded from CSRF
 * verification (see VerifyCsrfToken::$except) — the signature check below is
 * this route's actual authentication.
 *
 * This is the "push" path: Bold sends this on its own, with up to 5 retries
 * over 24 hours if it fails. It is not the only path an order gets settled
 * through — see SettleOrder's docblock for the "pull" path a webhook that
 * never arrives still leaves open.
 */
class BoldWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentProvider $payments, SettleOrder $settle): Response
    {
        if (! $payments->verifyWebhookSignature($request->getContent(), $request->header('x-bold-signature'))) {
            Log::warning('platform_payment_webhook_bad_signature', [
                'ip' => $request->ip(),
            ]);

            return response('', 401);
        }

        $event = $payments->parseWebhookEvent((array) $request->json()->all());

        if (! $event->reference) {
            Log::error('platform_payment_webhook_missing_reference', ['payload' => $event->raw]);

            return response('', 200);
        }

        $order = Order::where('reference', $event->reference)->first();

        if (! $order) {
            // A webhook for an order we have no record of is worth
            // investigating, but it is never going to resolve itself on
            // retry: acknowledging it stops Bold from resending it uselessly
            // for the next 24 hours.
            Log::error('platform_payment_webhook_unknown_reference', ['reference' => $event->reference]);

            return response('', 200);
        }

        $this->auditAmount($order, $event->raw);

        $settle->apply($order, $event);

        return response('', 200);
    }

    /**
     * Compares what Bold says it charged with what the order asked for, and
     * only ever logs.
     *
     * It does not block, on purpose: the signature already proves the event is
     * Bold's, and the link's amount was fixed when it was created, so a buyer
     * cannot underpay. What can legitimately differ is the currency — Bold
     * processes in COP at the day's rate unless the card is charged in USD
     * through DCC — and the unit of `total` for USD is not documented. Until a
     * real USD payload has been seen, a hard check here could refuse a real
     * sale; the log is how that payload gets looked at.
     */
    private function auditAmount(Order $order, array $payload): void
    {
        $amount = $payload['data']['amount'] ?? null;

        if (! is_array($amount) || ! isset($amount['total'])) {
            return;
        }

        $currency = strtoupper((string) ($amount['currency'] ?? ''));
        $total = (float) $amount['total'];

        if ($currency !== $order->currency) {
            Log::warning('platform_payment_currency_differs', [
                'order_id' => $order->id,
                'order_currency' => $order->currency,
                'order_amount' => $order->amount,
                'bold_currency' => $currency,
                'bold_total' => $total,
            ]);

            return;
        }

        // Either unit counts as a match for USD: dollars (28) or cents (2800).
        $matches = abs($total - $order->amount) < 0.01
            || abs($total - Money::toMajor($order->amount, $order->currency)) < 0.01;

        if (! $matches) {
            Log::warning('platform_payment_amount_differs', [
                'order_id' => $order->id,
                'currency' => $currency,
                'order_amount' => $order->amount,
                'bold_total' => $total,
            ]);
        }
    }
}
