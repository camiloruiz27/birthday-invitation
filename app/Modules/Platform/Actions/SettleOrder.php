<?php

namespace App\Modules\Platform\Actions;

use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\PaymentWebhookEvent;
use Illuminate\Support\Facades\Log;

/**
 * The single place an Order actually changes state and, if approved, hands
 * over a case or credits.
 *
 * Two independent callers feed this the exact same normalised event shape:
 * BoldWebhookController (the push path) and PaymentCallbackController /
 * platform:reconcile-order (the pull path, asking Bold directly — a webhook
 * is a best-effort delivery, not a guarantee, so the buyer's own return trip
 * must be able to resolve an order on its own). Both go through the same
 * conditional UPDATE, so whichever arrives first wins and the other is a
 * harmless no-op — the same idiom as Game::start(), RevealEnding and
 * InterrogationSession::reserveQuestion().
 */
class SettleOrder
{
    public function __construct(private GrantCaseAccess $access, private AiCredits $credits)
    {
    }

    public function apply(Order $order, PaymentWebhookEvent $event): void
    {
        match ($event->status) {
            Order::STATUS_APPROVED => $this->approve($order, $event),
            Order::STATUS_REJECTED => $this->transitionOnly($order, Order::STATUS_PENDING, Order::STATUS_REJECTED, $event),
            Order::STATUS_EXPIRED => $this->transitionOnly($order, Order::STATUS_PENDING, Order::STATUS_EXPIRED, $event),
            Order::STATUS_VOIDED => $this->void($order, $event),
            // 'unknown': Bold has nothing new to report (still
            // ACTIVE/PROCESSING, or the check itself failed) — leave the
            // order exactly as it is and let the next attempt try again.
            default => null,
        };
    }

    private function approve(Order $order, PaymentWebhookEvent $event): void
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
        // earlier delivery (webhook or a previous reconcile) — the
        // exactly-once guarantee. Nothing left to do.
        if ($updated !== 1) {
            return;
        }

        $order->refresh();

        if ($order->type === Order::TYPE_CASE) {
            $this->access->grant($order->user, $order->mysteryCase, Entitlement::SOURCE_PURCHASE);

            return;
        }

        $this->credits->grant(
            $order->user,
            (int) $order->credits_granted,
            CreditLedgerEntry::REASON_TOPUP,
            "Recarga: {$order->credit_package_id}"
        );
    }

    private function transitionOnly(Order $order, string $from, string $to, PaymentWebhookEvent $event): void
    {
        Order::where('id', $order->id)
            ->where('status', $from)
            ->update([
                'status' => $to,
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
        $updated = Order::where('id', $order->id)
            ->where('status', Order::STATUS_APPROVED)
            ->update([
                'status' => Order::STATUS_VOIDED,
                'raw_webhook' => $event->raw,
            ]);

        if ($updated === 1) {
            Log::warning('platform_payment_voided_needs_review', ['order_id' => $order->id]);
        }
    }
}
