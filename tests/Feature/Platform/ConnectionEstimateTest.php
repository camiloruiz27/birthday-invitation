<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Support\Ops;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * With persistent connections on, most requests that run a query reuse a
 * connection and cost the host's hourly allowance nothing. A 200-player test on
 * the test site counted 1,075 requests with queries against a cap of 500 and not
 * one of them failed: the count was the wrong thing to compare to the cap. Once
 * the reuse probe has enough samples, the budget speaks in estimated NEW
 * connections instead.
 */
class ConnectionEstimateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['platform.connection_budget.per_hour' => 100]);
    }

    private function probeSaw(int $reused, int $fresh): void
    {
        $hour = now()->format('YmdH');

        Ops::store()->put("platform:db-reuse:{$hour}:reused", $reused, 600);
        Ops::store()->put("platform:db-reuse:{$hour}:fresh", $fresh, 600);
    }

    public function test_without_persistence_the_estimate_is_unknown_and_the_raw_count_rules(): void
    {
        config(['database.persistent' => false]);
        Log::spy();

        foreach (range(1, 100) as $i) {
            ConnectionBudget::hit('player.inbox', 'mysql');
        }

        $this->assertNull(ConnectionBudget::summary()['estimated_new_this_hour']);
        Log::shouldHaveReceived('error')->with('db_connection_budget_spent', \Mockery::any())->once();
    }

    public function test_with_too_few_samples_it_stays_on_the_safe_side(): void
    {
        config(['database.persistent' => true]);
        $this->probeSaw(reused: 10, fresh: 1); // 11 samples: not enough to trust
        Log::spy();

        foreach (range(1, 100) as $i) {
            ConnectionBudget::hit('player.inbox', 'mysql');
        }

        $this->assertNull(ConnectionBudget::summary()['estimated_new_this_hour']);
        Log::shouldHaveReceived('error')->with('db_connection_budget_spent', \Mockery::any())->once();
    }

    public function test_when_most_requests_reuse_a_connection_the_cap_is_judged_by_the_new_ones(): void
    {
        config(['database.persistent' => true]);
        $this->probeSaw(reused: 92, fresh: 8); // 100 samples, 8 % new
        Log::spy();

        // 400 requests with a query against a cap of 100: far past it by the raw
        // count, but only ~32 of them opened a connection.
        foreach (range(1, 400) as $i) {
            ConnectionBudget::hit('player.inbox', 'mysql');
        }

        $summary = ConnectionBudget::summary();

        $this->assertSame(400, $summary['this_hour']);
        $this->assertSame(32, $summary['estimated_new_this_hour']);
        Log::shouldNotHaveReceived('error', ['db_connection_budget_spent', \Mockery::any()]);
        Log::shouldNotHaveReceived('warning', ['db_connection_budget_high', \Mockery::any()]);
    }

    public function test_a_warning_still_fires_once_when_the_new_connections_really_approach_the_cap(): void
    {
        config(['database.persistent' => true]);
        $this->probeSaw(reused: 50, fresh: 50); // half of them are new
        Log::spy();

        // 200 requests = ~100 new connections: at the cap.
        foreach (range(1, 200) as $i) {
            ConnectionBudget::hit('player.inbox', 'mysql');
        }

        Log::shouldHaveReceived('warning')->with('db_connection_budget_high', \Mockery::on(fn ($c) => $c['estimated_new_connections'] >= 80))->once();
        Log::shouldHaveReceived('error')->with('db_connection_budget_spent', \Mockery::any())->once();
    }
}
