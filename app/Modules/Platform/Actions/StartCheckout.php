<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use Illuminate\Support\Facades\URL;
use Throwable;

/**
 * The single door to a real-money checkout.
 *
 * Mirrors GrantCaseAccess and AiCredits::grant: exactly one place creates an
 * Order and asks the provider for a checkout link, so there is one place to
 * look when asking "how did this order come to exist?". Nothing else writes
 * an Order — BoldWebhookController only ever transitions one that already
 * exists.
 */
class StartCheckout
{
    public function __construct(
        private PaymentProvider $payments,
        private RedeemPromoCode $promos,
        private GrantCaseAccess $access,
        private AiCredits $credits,
    ) {
    }

    public function forCase(User $user, MysteryCase $case, ?string $promoCode = null): Order
    {
        return $this->create($user, [
            'user_id' => $user->id,
            'type' => Order::TYPE_CASE,
            'mystery_case_id' => $case->id,
            'amount' => $case->price_amount,
            'currency' => $case->currency,
        ], $promoCode);
    }

    /**
     * @param  array{id: string, credits: int, price_amount: int, currency: string}  $package
     */
    public function forCreditPackage(User $user, array $package, ?string $promoCode = null): Order
    {
        return $this->create($user, [
            'user_id' => $user->id,
            'type' => Order::TYPE_CREDIT_PACKAGE,
            'credit_package_id' => $package['id'],
            'credits_granted' => $package['credits'],
            'amount' => $package['price_amount'],
            'currency' => $package['currency'],
        ], $promoCode);
    }

    private function create(User $user, array $attributes, ?string $promoCode): Order
    {
        $redemption = null;

        if ($promoCode) {
            // Computed and claimed BEFORE the Order exists — Bold needs the
            // already-discounted amount. If the checkout below never
            // completes, the redemption is released so a capped code does
            // not lose a use to a sale that never happened.
            $discount = $this->promos->applyDiscount($user, $promoCode, $attributes['amount']);
            $attributes['amount'] = $discount->amount;
            $redemption = $discount->redemption;
        }

        // Never the auto-incrementing id: this is what leaves the platform
        // (sent to Bold, printed in a checkout URL), so it must not reveal
        // how many orders exist.
        $order = Order::create($attributes + [
            'reference' => strtoupper(bin2hex(random_bytes(12))),
            'status' => Order::STATUS_PENDING,
        ]);

        if ($redemption) {
            $redemption->update(['order_id' => $order->id]);
        }

        // A code that discounted this all the way to zero: nothing to
        // actually charge, so there is nothing for Bold to do. Deliver
        // directly — Order still ends up the single source of truth for
        // "how did this access come to exist", even for a free one.
        if ($redemption && $order->amount === 0) {
            $this->deliverDirectly($order);

            return $order;
        }

        try {
            $link = $this->payments->createCheckoutLink($order, URL::route('payments.confirm', $order));
        } catch (Throwable $exception) {
            if ($redemption) {
                $this->promos->release($redemption);
            }

            throw $exception;
        }

        $order->update([
            'checkout_url' => $link->url,
            'provider_link_id' => $link->providerLinkId,
        ]);

        return $order;
    }

    private function deliverDirectly(Order $order): void
    {
        $order->update([
            'status' => Order::STATUS_APPROVED,
            'provider' => 'promo',
            'paid_at' => now(),
        ]);

        if ($order->type === Order::TYPE_CASE) {
            $this->access->grant($order->user, $order->mysteryCase, Entitlement::SOURCE_PROMO);

            return;
        }

        $this->credits->grant(
            $order->user,
            (int) $order->credits_granted,
            CreditLedgerEntry::REASON_PROMO,
            "Codigo aplicado a la orden #{$order->id}"
        );
    }
}
