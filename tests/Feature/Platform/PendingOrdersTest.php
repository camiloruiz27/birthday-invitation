<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Support\PendingOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The scheduler re-checks pending orders against Bold every five minutes, in case
 * a webhook never arrived. With none pending that is a connection spent to learn
 * nothing, twelve times an hour, all day. It now stays quiet until an order is
 * actually waiting.
 */
class PendingOrdersTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function order(array $attributes = []): Order
    {
        return Order::create($attributes + [
            'user_id' => $this->userWithoutAccess()->id,
            'type' => Order::TYPE_CREDIT_PACKAGE,
            'amount' => 9900,
            'currency' => 'COP',
            'status' => Order::STATUS_PENDING,
            'reference' => 'MC-'.uniqid(),
            'provider_link_id' => 'LNK-'.uniqid(),
        ]);
    }

    public function test_unknown_means_maybe_so_the_first_run_happens(): void
    {
        $this->assertTrue(PendingOrders::mayExist());
    }

    public function test_a_run_that_finds_nothing_silences_it(): void
    {
        $this->artisan('platform:reconcile-pending-orders')->assertSuccessful();

        $this->assertFalse(PendingOrders::mayExist());
    }

    public function test_a_new_pending_order_wakes_it_again(): void
    {
        $this->artisan('platform:reconcile-pending-orders')->assertSuccessful();
        $this->assertFalse(PendingOrders::mayExist());

        $this->order();

        $this->assertTrue(PendingOrders::mayExist());
    }

    public function test_an_order_still_without_a_payment_link_does_not_count(): void
    {
        PendingOrders::none();

        $this->order(['provider_link_id' => null]);

        $this->assertFalse(PendingOrders::mayExist(), 'Nothing at Bold to ask about yet.');
    }

    public function test_an_order_too_young_to_check_keeps_it_awake(): void
    {
        // Younger than the sweep's grace window: not looked at now, but it will be.
        $this->order();

        $this->artisan('platform:reconcile-pending-orders')->assertSuccessful();

        $this->assertTrue(PendingOrders::mayExist(), 'Silencing it now would forget the order.');
    }

    public function test_it_goes_quiet_once_the_last_order_is_settled(): void
    {
        $order = $this->order();
        $order->forceFill(['created_at' => now()->subMinutes(10)])->save();

        // Settled by the webhook, say: nothing is pending any more.
        $order->forceFill(['status' => Order::STATUS_APPROVED])->save();

        $this->artisan('platform:reconcile-pending-orders')->assertSuccessful();

        $this->assertFalse(PendingOrders::mayExist());
    }

    public function test_the_command_counts_under_its_own_label(): void
    {
        $this->artisan('platform:reconcile-pending-orders')->assertSuccessful();

        $this->assertArrayHasKey('cmd:reconcile-pending-orders', ConnectionBudget::top());
    }

    public function test_the_command_is_not_scheduled_hourly_chores_are_daily(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        $holds = $events->first(fn ($event) => str_contains((string) $event->command, 'immersion:release-stale-holds'));

        if (config('immersion.credits.enabled')) {
            $this->assertSame('10 4 * * *', $holds->expression, 'Once a day: its window is measured in days.');
        } else {
            $this->assertNull($holds);
        }

        Artisan::call('list');
    }
}
