<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Support\GameRevision;
use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Support\Ops;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use PDOException;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * When the host's hourly allowance runs out the question is never "did it run
 * out" but "who spent it". Connections are counted under a label (the route or
 * the command), the log names the heaviest, the admin dashboard shows them, and
 * none of it is lost on a deploy.
 */
class ConnectionAttributionTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['platform.connection_budget.per_hour' => 500]);
    }

    public function test_connections_are_counted_per_label_heaviest_first(): void
    {
        foreach (['a', 'b', 'b', 'c', 'c', 'c'] as $label) {
            ConnectionBudget::hit($label);
        }

        $this->assertSame(['c' => 3, 'b' => 2, 'a' => 1], ConnectionBudget::top(5));
        $this->assertSame(6, ConnectionBudget::summary()['this_hour']);
        $this->assertSame(['c' => 3, 'b' => 2], ConnectionBudget::top(2));
    }

    public function test_a_request_is_counted_under_its_route_name_not_its_url(): void
    {
        // The unknown-case page is never cached, so each visit runs its query.
        $this->get(route('cases.show', 'no-existe-uno'))->assertNotFound();
        $this->get(route('cases.show', 'no-existe-dos'))->assertNotFound();

        $top = ConnectionBudget::top();

        $this->assertSame(2, $top['cases.show'] ?? null, 'Different URLs of one route are one label.');
        $this->assertArrayNotHasKey('casos/no-existe-uno', $top);
    }

    public function test_a_route_without_a_name_is_counted_by_its_template_never_its_real_url(): void
    {
        Route::middleware('web')->get('/__token/{secret}', function () {
            \Illuminate\Support\Facades\DB::select('select 1');

            return 'ok';
        });

        $this->get('/__token/abc-token-123');
        $this->get('/__token/other-token-456');

        $top = ConnectionBudget::top();

        $this->assertSame(2, $top['__token/{secret}'] ?? null);
        $this->assertStringNotContainsString('abc-token-123', json_encode($top));
    }

    public function test_the_number_of_labels_is_bounded(): void
    {
        foreach (range(1, 90) as $i) {
            ConnectionBudget::hit("label-{$i}");
        }

        $this->assertLessThanOrEqual(61, count(ConnectionBudget::top(500)));
        $this->assertSame(90, ConnectionBudget::summary()['this_hour']);
    }

    public function test_the_log_names_the_heaviest_at_eighty_percent_and_when_it_is_spent(): void
    {
        config(['platform.connection_budget.per_hour' => 10]);
        Log::spy();

        foreach (range(1, 10) as $i) {
            ConnectionBudget::hit($i <= 6 ? 'player.inbox' : 'cmd:tick');
        }

        Log::shouldHaveReceived('warning')
            ->with('db_connection_budget_high', \Mockery::on(fn ($context) => array_key_exists('player.inbox', $context['heaviest'])))
            ->once();
        Log::shouldHaveReceived('error')
            ->with('db_connection_budget_spent', \Mockery::on(fn ($context) => $context['heaviest']['player.inbox'] === 6))
            ->once();
    }

    public function test_the_outage_log_line_says_who_spent_the_hour(): void
    {
        ConnectionBudget::hit('immersion.player.interrogation.ask');
        Log::spy();

        Route::middleware('web')->get('/__db-limit', function () {
            $pdo = new PDOException('max_connections_per_hour');
            $pdo->errorInfo = ['HY000', 1226, 'exceeded'];

            throw new QueryException('select 1', [], $pdo);
        });

        $this->get('/__db-limit')->assertStatus(503);

        Log::shouldHaveReceived('critical')
            ->with('db_connection_limit_reached', \Mockery::on(
                fn ($context) => array_key_exists('immersion.player.interrogation.ask', $context['heaviest'])
            ))
            ->once();
    }

    public function test_commands_count_under_their_own_label(): void
    {
        Artisan::call('platform:reconcile-pending-orders');
        Artisan::call('immersion:release-stale-holds');
        Artisan::call('platform:expire-stale-orders');

        $top = ConnectionBudget::top(10);

        $this->assertArrayHasKey('cmd:reconcile-pending-orders', $top);
        $this->assertArrayHasKey('cmd:release-stale-holds', $top);
        $this->assertArrayHasKey('cmd:expire-stale-orders', $top);
    }

    public function test_the_admin_dashboard_lists_them(): void
    {
        ConnectionBudget::hit('immersion.player.inbox');
        ConnectionBudget::hit('immersion.player.inbox');

        $admin = $this->gameMaster(attributes: ['email' => 'admin@example.com']);
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin->fresh())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('limits.connections.top_this_hour', fn ($top) => (collect($top)->all()['immersion.player.inbox'] ?? null) === 2)
                ->has('limits.connections.top_last_hour'));
    }

    /**
     * The point of the ops store: every deploy runs optimize:clear, and neither
     * the counters nor the change stamps may go with it.
     */
    public function test_the_counters_and_stamps_survive_a_cache_clear(): void
    {
        $path = sys_get_temp_dir().'/ops-test-'.uniqid();

        // Point the tests' ops store at a real, separate directory (they usually
        // share the default array store, which cache:clear would empty).
        config([
            'cache.ops_store' => 'ops',
            'cache.stores.ops' => ['driver' => 'file', 'path' => $path],
        ]);
        Cache::purge('ops');

        try {
            ConnectionBudget::hit('immersion.player.inbox');
            GameRevision::bump(7);
            $stamp = GameRevision::current(7);

            Cache::put('page:cache', 'x', 600);

            Artisan::call('cache:clear');

            $this->assertNull(Cache::get('page:cache'), 'The default cache is what cache:clear empties.');
            $this->assertSame(1, ConnectionBudget::summary()['this_hour']);
            $this->assertSame(['immersion.player.inbox' => 1], ConnectionBudget::top());
            $this->assertSame($stamp, GameRevision::current(7), 'A deploy must not make every open tab reload.');
        } finally {
            Cache::purge('ops');
            File::deleteDirectory($path);
        }
    }

    public function test_the_ops_store_is_a_different_directory_from_the_default_one(): void
    {
        config(['cache.ops_store' => 'ops']);

        $this->assertNotSame(
            config('cache.stores.file.path'),
            config('cache.stores.ops.path')
        );
        $this->assertSame('file', config('cache.stores.ops.driver'));
        $this->assertNotNull(Ops::store());
    }
}
