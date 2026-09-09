<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Platform\Console\Commands\ExpireStaleOrders;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The real Bold checkout: starting one, and the webhook that settles it.
 *
 * The webhook tests build the exact raw JSON body themselves and sign it by
 * hand (BoldPaymentProvider::verifyWebhookSignature checks the raw request
 * bytes, not a re-encoded array), then POST it with Symfony's low-level
 * call() so the body reaching the controller is byte-for-byte what was
 * signed — the same thing postJson() would produce internally, made explicit
 * so the test cannot silently drift from what the signature covers.
 */
class RealCheckoutTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const SECRET = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.payments.enabled' => true,
            'platform.payments.identity_key' => 'test-identity',
            'platform.payments.secret_key' => self::SECRET,
            'platform.simulated_checkout' => false,
        ]);
    }

    private function fakeBoldLink(): void
    {
        Http::fake([
            '*/online/link/v1' => Http::response([
                'payload' => [
                    'payment_link' => 'LNK_TEST123',
                    'url' => 'https://checkout.bold.co/LNK_TEST123',
                ],
                'errors' => [],
            ], 200),
        ]);
    }

    private function sign(string $rawBody): string
    {
        return hash_hmac('sha256', base64_encode($rawBody), self::SECRET);
    }

    private function postWebhook(array $payload, ?string $signature = null): \Illuminate\Testing\TestResponse
    {
        $raw = json_encode($payload);
        $signature ??= $this->sign($raw);

        return $this->call(
            'POST',
            route('payments.webhook.bold'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_BOLD_SIGNATURE' => $signature,
            ],
            $raw
        );
    }

    private function approvedSalePayload(string $reference): array
    {
        return [
            'id' => 'evt-1',
            'type' => 'SALE_APPROVED',
            'data' => [
                'payment_id' => 'BOLD-PAY-1',
                'metadata' => ['reference' => $reference],
                'amount' => ['currency' => 'COP', 'total' => 89000],
            ],
        ];
    }

    private function rejectedSalePayload(string $reference): array
    {
        $payload = $this->approvedSalePayload($reference);
        $payload['type'] = 'SALE_REJECTED';

        return $payload;
    }

    public function test_starting_a_case_checkout_creates_a_pending_order_and_redirects_to_bold(): void
    {
        $this->fakeBoldLink();
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'))
            ->assertRedirect('https://checkout.bold.co/LNK_TEST123');

        $order = Order::sole();
        $this->assertSame(Order::TYPE_CASE, $order->type);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame($user->id, $order->user_id);
        $this->assertFalse($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_starting_a_credit_package_checkout_creates_a_pending_order(): void
    {
        $this->fakeBoldLink();
        $user = $this->gameMaster();
        $package = collect((array) config('platform.credit_packages'))->first();

        $this->actingAs($user)
            ->post(route('credits.purchase'), ['package' => $package['id']])
            ->assertRedirect('https://checkout.bold.co/LNK_TEST123');

        $order = Order::where('type', Order::TYPE_CREDIT_PACKAGE)->sole();
        $this->assertSame($package['id'], $order->credit_package_id);
        $this->assertSame((int) $package['credits'], $order->credits_granted);
    }

    public function test_an_approved_webhook_delivers_the_case_and_marks_the_order_approved(): void
    {
        $this->fakeBoldLink();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        $this->postWebhook($this->approvedSalePayload($order->reference))
            ->assertOk();

        $order->refresh();
        $this->assertSame(Order::STATUS_APPROVED, $order->status);
        $this->assertSame('BOLD-PAY-1', $order->provider_payment_id);
        $this->assertNotNull($order->paid_at);
        $this->assertTrue($user->fresh()->ownsCase($case));
        $this->assertSame(
            Entitlement::SOURCE_PURCHASE,
            Entitlement::where('user_id', $user->id)->where('mystery_case_id', $case->id)->first()->source
        );
    }

    public function test_an_approved_webhook_for_a_credit_package_grants_credits_exactly_once_even_if_bold_retries(): void
    {
        $this->fakeBoldLink();
        $user = $this->gameMaster();
        $package = collect((array) config('platform.credit_packages'))->first();
        $this->actingAs($user)->post(route('credits.purchase'), ['package' => $package['id']]);
        $order = Order::where('type', Order::TYPE_CREDIT_PACKAGE)->sole();

        $before = app(\App\Modules\Immersion\Support\AiCredits::class)->walletFor($user)->available();

        $payload = $this->approvedSalePayload($order->reference);

        // Bold's own retry policy resends the same event up to 5 times; this
        // is the test that actually matters — the second (and any later)
        // delivery must be a no-op, not a second grant.
        $this->postWebhook($payload)->assertOk();
        $this->postWebhook($payload)->assertOk();
        $this->postWebhook($payload)->assertOk();

        $after = app(\App\Modules\Immersion\Support\AiCredits::class)->walletFor($user->fresh())->available();

        $this->assertSame((int) $package['credits'], $after - $before);
        $this->assertSame(
            1,
            CreditLedgerEntry::where('user_id', $user->id)
                ->where('reason', CreditLedgerEntry::REASON_TOPUP)
                ->count()
        );
    }

    public function test_a_webhook_with_an_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        $this->fakeBoldLink();
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        $this->postWebhook($this->approvedSalePayload($order->reference), 'not-the-right-signature')
            ->assertStatus(401);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertFalse($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_a_webhook_for_an_unknown_reference_is_acknowledged_without_error(): void
    {
        $this->postWebhook($this->approvedSalePayload('NO-SUCH-ORDER-EXISTS'))
            ->assertOk();
    }

    public function test_a_rejected_sale_leaves_the_order_rejected_and_grants_nothing(): void
    {
        $this->fakeBoldLink();
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        $this->postWebhook($this->rejectedSalePayload($order->reference))->assertOk();

        $this->assertSame(Order::STATUS_REJECTED, $order->fresh()->status);
        $this->assertFalse($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_the_real_checkout_route_is_unreachable_when_payments_are_disabled_and_simulation_is_off(): void
    {
        config(['platform.payments.enabled' => false, 'platform.simulated_checkout' => false]);
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'))
            ->assertNotFound();
    }

    public function test_expiring_stale_orders_only_touches_old_pending_ones(): void
    {
        $this->fakeBoldLink();
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $stale = Order::sole();
        $stale->forceFill(['created_at' => now()->subDays(2)])->save();

        $fresh = Order::create([
            'user_id' => $user->id,
            'type' => Order::TYPE_CASE,
            'amount' => 1000,
            'currency' => 'COP',
            'status' => Order::STATUS_PENDING,
            'reference' => 'FRESH-ORDER-REF',
        ]);

        $this->artisan(ExpireStaleOrders::class)->assertSuccessful();

        $this->assertSame(Order::STATUS_EXPIRED, $stale->fresh()->status);
        $this->assertSame(Order::STATUS_PENDING, $fresh->fresh()->status);
    }
}
