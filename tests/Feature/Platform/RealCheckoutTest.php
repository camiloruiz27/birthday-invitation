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

    /**
     * The GET status stub reads $this->boldStatus on every call rather than
     * baking in a fixed response, so a test can flip it between two requests
     * with setBoldStatus() — calling Http::fake() a second time does NOT
     * override the first stub for the same URL, it stacks on top of it
     * (Factory::fake() merges callbacks; the first one registered that
     * matches wins), which silently made an "ACTIVE then PAID" test always
     * see ACTIVE.
     */
    private string $boldStatus = 'ACTIVE';

    private function fakeBoldLinkAndStatus(string $status = 'ACTIVE'): void
    {
        $this->boldStatus = $status;

        Http::fake([
            '*/online/link/v1/LNK_TEST123' => function () {
                return Http::response([
                    'id' => 'LNK_TEST123',
                    'status' => $this->boldStatus,
                    'transaction_id' => $this->boldStatus === 'PAID' ? 'BOLD-PAY-RECONCILED' : null,
                    'reference' => null,
                ], 200);
            },
            '*/online/link/v1' => Http::response([
                'payload' => [
                    'payment_link' => 'LNK_TEST123',
                    'url' => 'https://checkout.bold.co/LNK_TEST123',
                ],
                'errors' => [],
            ], 200),
        ]);
    }

    private function setBoldStatus(string $status): void
    {
        $this->boldStatus = $status;
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
        $this->fakeBoldLinkAndStatus();
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
        $this->fakeBoldLinkAndStatus();
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
        $this->fakeBoldLinkAndStatus();
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
        $this->fakeBoldLinkAndStatus();
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
        $this->fakeBoldLinkAndStatus();
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
        $this->fakeBoldLinkAndStatus();
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
        $this->fakeBoldLinkAndStatus();
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

    /**
     * The gap that motivated all of this: a real webhook that never arrived,
     * confirmed live against production, where the confirmation page span
     * forever on "Confirmando tu pago" even though Bold had approved the
     * sale. This is the fix — the page itself asks Bold directly.
     */
    public function test_visiting_the_confirmation_page_settles_a_pending_order_by_asking_bold_directly(): void
    {
        $this->fakeBoldLinkAndStatus('ACTIVE');
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        // While Bold is still processing, the page must not invent an answer.
        $this->actingAs($user)->get(route('payments.confirm', $order))->assertOk();
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);

        // No webhook ever arrives in this test — only the page's own check.
        $this->setBoldStatus('PAID');

        $this->actingAs($user)->get(route('payments.confirm', $order))->assertOk();

        $this->assertSame(Order::STATUS_APPROVED, $order->fresh()->status);
        $this->assertSame('BOLD-PAY-RECONCILED', $order->fresh()->provider_payment_id);
        $this->assertTrue($user->fresh()->ownsCase($case));
    }

    public function test_the_confirmation_page_reports_the_credit_balance_after_a_credit_purchase_settles(): void
    {
        $user = $this->gameMaster();
        $package = collect((array) config('platform.credit_packages'))->first();
        $before = app(\App\Modules\Immersion\Support\AiCredits::class)->walletFor($user)->available();

        $this->fakeBoldLinkAndStatus('PAID');
        $this->actingAs($user)->post(route('credits.purchase'), ['package' => $package['id']]);
        $order = Order::where('type', Order::TYPE_CREDIT_PACKAGE)->sole();

        $response = $this->actingAs($user)->get(route('payments.confirm', $order));

        $response->assertOk();
        $this->assertSame(Order::STATUS_APPROVED, $order->fresh()->status);
        $this->assertSame(
            $before + (int) $package['credits'],
            app(\App\Modules\Immersion\Support\AiCredits::class)->walletFor($user->fresh())->available()
        );
    }

    public function test_reconcile_order_command_settles_a_stuck_pending_order(): void
    {
        $this->fakeBoldLinkAndStatus('ACTIVE');
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        $this->setBoldStatus('PAID');

        $this->artisan('platform:reconcile-order', ['order' => $order->id])->assertSuccessful();

        $this->assertSame(Order::STATUS_APPROVED, $order->fresh()->status);
        $this->assertTrue($user->fresh()->ownsCase($case));
    }

    public function test_reconcile_pending_orders_skips_orders_still_inside_the_grace_window(): void
    {
        $this->fakeBoldLinkAndStatus('PAID');
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        // Created "now" by the checkout above — well inside the default
        // 2-minute grace window that belongs to the confirmation page, not
        // this sweep.
        $this->artisan('platform:reconcile-pending-orders')->assertSuccessful();

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_reconcile_pending_orders_settles_an_order_past_the_grace_window(): void
    {
        $this->fakeBoldLinkAndStatus('ACTIVE');
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();
        $order->forceFill(['created_at' => now()->subMinutes(10)])->save();

        $this->setBoldStatus('PAID');

        $this->artisan('platform:reconcile-pending-orders')->assertSuccessful();

        $this->assertSame(Order::STATUS_APPROVED, $order->fresh()->status);
        $this->assertTrue($user->fresh()->ownsCase($case));
    }
}
