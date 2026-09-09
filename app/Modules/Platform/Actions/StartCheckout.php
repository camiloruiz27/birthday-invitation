<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use Illuminate\Support\Facades\URL;

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
    public function __construct(private PaymentProvider $payments)
    {
    }

    public function forCase(User $user, MysteryCase $case): Order
    {
        return $this->create([
            'user_id' => $user->id,
            'type' => Order::TYPE_CASE,
            'mystery_case_id' => $case->id,
            'amount' => $case->price_amount,
            'currency' => $case->currency,
        ]);
    }

    /**
     * @param  array{id: string, credits: int, price_amount: int, currency: string}  $package
     */
    public function forCreditPackage(User $user, array $package): Order
    {
        return $this->create([
            'user_id' => $user->id,
            'type' => Order::TYPE_CREDIT_PACKAGE,
            'credit_package_id' => $package['id'],
            'credits_granted' => $package['credits'],
            'amount' => $package['price_amount'],
            'currency' => $package['currency'],
        ]);
    }

    private function create(array $attributes): Order
    {
        // Never the auto-incrementing id: this is what leaves the platform
        // (sent to Bold, printed in a checkout URL), so it must not reveal
        // how many orders exist.
        $order = Order::create($attributes + [
            'reference' => strtoupper(bin2hex(random_bytes(12))),
            'status' => Order::STATUS_PENDING,
        ]);

        $callbackUrl = URL::route('payments.confirm', $order);

        $order->update([
            'checkout_url' => $this->payments->createCheckoutLink($order, $callbackUrl),
        ]);

        return $order;
    }
}
