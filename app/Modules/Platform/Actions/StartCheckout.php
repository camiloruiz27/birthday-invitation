<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Ads\AdEvents;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use App\Modules\Platform\Support\Attribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        private AdEvents $ads,
        private Request $request,
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
        ], $promoCode, $case);
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

    private function create(User $user, array $attributes, ?string $promoCode, ?MysteryCase $case = null): Order
    {
        $redemption = null;

        // The price before any code, kept because `amount` is about to be
        // overwritten with what is actually charged.
        $attributes['list_amount'] = $attributes['amount'];

        if ($promoCode) {
            // Computed and claimed BEFORE the Order exists — Bold needs the
            // already-discounted amount. If the checkout below never
            // completes, the redemption is released so a capped code does
            // not lose a use to a sale that never happened.
            //
            // $case lets a gift covering this very case count as a 100%
            // discount on it (see RedeemPromoCode::applyDiscount); a credit
            // package passes none, so a gift there is still turned away.
            $discount = $this->promos->applyDiscount($user, $promoCode, $attributes['amount'], $case);
            $attributes['amount'] = $discount->amount;
            $attributes['discount_amount'] = $attributes['list_amount'] - $discount->amount;
            $attributes['promo_code_id'] = $discount->redemption->promo_code_id;
            $redemption = $discount->redemption;
        }

        // Where the buyer came from and whether they allowed advertising
        // measurement, taken now while there is still a browser to ask — the
        // webhook that settles this order will have none.
        $attributes += AdEvents::orderContext($this->request, $user);

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

        // They got where they were going; confirming their email later must
        // not drop them back on this checkout.
        Attribution::consumeIntent($this->request, $user);

        // A code that discounted this all the way to zero: nothing to
        // actually charge, so there is nothing for Bold to do. Deliver
        // directly — Order still ends up the single source of truth for
        // "how did this access come to exist", even for a free one.
        if ($redemption && $order->amount === 0) {
            $this->deliverDirectly($order, $redemption->promoCode);

            // A free order never reaches SettleOrder, which is where a paid
            // one is reported. Reported after the transaction, not inside it.
            $this->ads->purchase($order->refresh());
            $this->forgetUsedPromo($user, $redemption->promoCode);

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

        if ($redemption) {
            $this->forgetUsedPromo($user, $redemption->promoCode);
        }

        return $order;
    }

    /**
     * The code that arrived with an ad click has now been used: stop offering
     * it on every later purchase.
     */
    private function forgetUsedPromo(User $user, PromoCode $promo): void
    {
        Attribution::consumePromo($this->request, $user, $promo->code);
    }

    /**
     * One transaction: the order is only approved if what it promised was
     * actually delivered. A failure halfway leaves it pending (nothing
     * granted, nothing half-approved) and platform:expire-stale-orders later
     * closes it and gives the code's use back.
     */
    private function deliverDirectly(Order $order, PromoCode $promo): void
    {
        DB::transaction(function () use ($order, $promo) {
            $order->update([
                'status' => Order::STATUS_APPROVED,
                'provider' => 'promo',
                'paid_at' => now(),
            ]);

            if ($order->type === Order::TYPE_CASE) {
                $this->access->grant($order->user, $order->mysteryCase, Entitlement::SOURCE_PROMO);

                // A gift bundle (case + credits) covering this case: the
                // credits ride along with the case.
                if ($promo->grants_credits) {
                    $this->credits->grant(
                        $order->user,
                        $promo->grants_credits,
                        CreditLedgerEntry::REASON_PROMO,
                        "Codigo: {$promo->code}"
                    );
                }

                return;
            }

            $this->credits->grant(
                $order->user,
                (int) $order->credits_granted,
                CreditLedgerEntry::REASON_PROMO,
                "Codigo aplicado a la orden #{$order->id}"
            );
        });
    }
}
