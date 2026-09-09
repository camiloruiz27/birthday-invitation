<?php

namespace App\Modules\Platform\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use App\Modules\Platform\Payments\PaymentWebhookEvent;
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
 * Bold retries a failed delivery up to 5 times over 24 hours, so this must be
 * safe to receive the same event any number of times. The guard is the same
 * conditional UPDATE idiom the rest of the codebase already uses for
 * exactly-once effects — see Game::start(), RevealEnding and
 * InterrogationSession::reserveQuestion(): only the request that actually
 * flips pending -> approved goes on to grant anything, so no matter how many
 * times Bold resends the same event, the case or the credits are handed over
 * exactly once.
 */
class BoldWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PaymentProvider $payments,
        GrantCaseAccess $access,
        AiCredits $credits,
    ): Response {
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

        match ($event->status) {
            Order::STATUS_APPROVED => $this->approve($order, $event, $access, $credits),
            Order::STATUS_REJECTED => $this->reject($order, $event),
            Order::STATUS_VOIDED => $this->void($order, $event),
            default => Log::info('platform_payment_webhook_ignored', [
                'order_id' => $order->id,
                'payload' => $event->raw,
            ]),
        };

        return response('', 200);
    }

    private function approve(Order $order, PaymentWebhookEvent $event, GrantCaseAccess $access, AiCredits $credits): void
    {
        $updated = Order::where('id', $order->id)
            ->where('status', Order::STATUS_PENDING)
            ->update([
                'status' => Order::STATUS_APPROVED,
                'provider_payment_id' => $event->providerPaymentId,
                'raw_webhook' => $event->raw,
                'paid_at' => now(),
            ]);

        // Zero rows affected means this order was already resolved by an
        // earlier delivery of the same event — the exactly-once guarantee.
        // Nothing left to do; the grant already happened the first time.
        if ($updated !== 1) {
            return;
        }

        $order->refresh();

        if ($order->type === Order::TYPE_CASE) {
            $access->grant($order->user, $order->mysteryCase, Entitlement::SOURCE_PURCHASE);

            return;
        }

        $credits->grant(
            $order->user,
            (int) $order->credits_granted,
            CreditLedgerEntry::REASON_TOPUP,
            "Recarga: {$order->credit_package_id}"
        );
    }

    private function reject(Order $order, PaymentWebhookEvent $event): void
    {
        Order::where('id', $order->id)
            ->where('status', Order::STATUS_PENDING)
            ->update([
                'status' => Order::STATUS_REJECTED,
                'provider_payment_id' => $event->providerPaymentId,
                'raw_webhook' => $event->raw,
            ]);
    }

    /**
     * A void (refund) is recorded, not acted on. Reversing an already-granted
     * case or spent credits is a decision for a person to make, the same way
     * nothing else in this codebase destroys a user's access without someone
     * confirming it first.
     */
    private function void(Order $order, PaymentWebhookEvent $event): void
    {
        Order::where('id', $order->id)
            ->where('status', Order::STATUS_APPROVED)
            ->update([
                'status' => Order::STATUS_VOIDED,
                'raw_webhook' => $event->raw,
            ]);

        Log::warning('platform_payment_voided_needs_review', ['order_id' => $order->id]);
    }
}
