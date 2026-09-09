<?php

namespace App\Modules\Platform\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\SettleOrder;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
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

        $settle->apply($order, $event);

        return response('', 200);
    }
}
