<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Support\ConnectionPool;
use App\Modules\Platform\Support\ConnectionReuseProbe;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Extra MySQL users on the same database, so each user's hourly allowance of
 * new connections is spent in parallel (only if the host counts per user).
 *
 * Nothing here talks to MySQL: the point is WHICH connection a request is
 * pinned to, that it is pinned for the whole request, and that it is off unless
 * users are configured. The extra connections are clones of the test database's.
 */
class ConnectionPoolTest extends TestCase
{
    private string $base;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->base = ConnectionPool::baseName();
    }

    private function withPool(int $users, bool $spareDefault = false): void
    {
        config([
            'database.pool.users' => array_map(
                fn (int $n) => ['username' => "pool-user-{$n}", 'password' => 'secret'],
                range(1, $users)
            ),
            'database.pool.spare_default' => $spareDefault,
        ]);

        ConnectionPool::register();
    }

    public function test_it_is_off_unless_extra_users_are_configured(): void
    {
        $this->assertFalse(ConnectionPool::enabled());
        $this->assertSame([$this->base], ConnectionPool::names());
        $this->assertSame($this->base, ConnectionPool::pick('any-token'));
    }

    public function test_each_user_becomes_a_clone_of_the_main_connection(): void
    {
        $this->withPool(2);

        $main = config("database.connections.{$this->base}");

        foreach ([1, 2] as $n) {
            $clone = config("database.connections.mysql_pool_{$n}");

            $this->assertSame($main['driver'], $clone['driver']);
            $this->assertSame("pool-user-{$n}", $clone['username']);
            $this->assertSame('secret', $clone['password']);
            $this->assertNull($clone['url'], 'A DATABASE_URL must not carry the main user over the pool users.');
        }

        $this->assertSame([$this->base, 'mysql_pool_1', 'mysql_pool_2'], ConnectionPool::names());
        $this->assertTrue(ConnectionPool::enabled());
    }

    public function test_a_blank_username_is_skipped_and_the_numbering_holds(): void
    {
        config(['database.pool.users' => [
            ['username' => '', 'password' => ''],
            ['username' => 'second', 'password' => 'x'],
        ]]);
        ConnectionPool::register();

        $this->assertSame([$this->base, 'mysql_pool_2'], ConnectionPool::names());
    }

    public function test_the_main_user_can_be_kept_out_of_the_rotation(): void
    {
        $this->withPool(2, spareDefault: true);

        $this->assertSame(['mysql_pool_1', 'mysql_pool_2'], ConnectionPool::names());

        foreach (range(1, 200) as $i) {
            $this->assertNotSame($this->base, ConnectionPool::pick("token-{$i}"));
        }
    }

    public function test_sparing_the_main_user_with_no_extras_falls_back_to_it(): void
    {
        config(['database.pool.users' => [], 'database.pool.spare_default' => true]);

        $this->assertSame([$this->base], ConnectionPool::names());
        $this->assertFalse(ConnectionPool::enabled());
    }

    public function test_a_key_always_lands_on_the_same_connection_and_keys_spread_evenly(): void
    {
        $this->withPool(2);

        $counts = [];

        foreach (range(1, 3000) as $i) {
            $token = "token-{$i}";
            $first = ConnectionPool::pick($token);

            $this->assertSame($first, ConnectionPool::pick($token), 'The choice must be stable per key.');
            $counts[$first] = ($counts[$first] ?? 0) + 1;
        }

        $this->assertCount(3, $counts, 'All three connections get traffic.');

        foreach ($counts as $name => $count) {
            $this->assertGreaterThan(800, $count, "{$name} is starved.");
            $this->assertLessThan(1200, $count, "{$name} takes too much.");
        }
    }

    public function test_only_player_pages_are_keyed(): void
    {
        $this->assertSame('abc123', ConnectionPool::keyFor('jugador/abc123'));
        $this->assertSame('abc123', ConnectionPool::keyFor('jugador/abc123/interrogatorio/ramon/preguntar'));
        $this->assertSame('abc123', ConnectionPool::keyFor('/jugador/abc123/estado'));

        foreach (['partidas/12', 'admin', 'creditos', 'webhooks/bold', 'jugadores/x', 'jugador', 'jugador/'] as $path) {
            $this->assertNull(ConnectionPool::keyFor($path), "{$path} is not player traffic.");
        }
    }

    // --- the request ------------------------------------------------------------------------

    private function pingRoutes(): void
    {
        Route::get('jugador/{token}/ping', fn () => response()->json(['connection' => DB::getDefaultConnection()]));
        Route::get('creditos-ping', fn () => response()->json(['connection' => DB::getDefaultConnection()]));
    }

    /** A token the pool sends to an extra user, so the test says something. */
    private function tokenOnAnExtraUser(): string
    {
        foreach (range(1, 500) as $i) {
            if (ConnectionPool::pick("probe-{$i}") !== $this->base) {
                return "probe-{$i}";
            }
        }

        $this->fail('No token landed on an extra connection.');
    }

    public function test_a_players_request_runs_on_its_own_connection_and_gives_the_default_back(): void
    {
        $this->withPool(2);
        $this->pingRoutes();
        $token = $this->tokenOnAnExtraUser();

        $this->getJson("/jugador/{$token}/ping")
            ->assertOk()
            ->assertJson(['connection' => ConnectionPool::pick($token)]);

        $this->assertSame($this->base, DB::getDefaultConnection(), 'The next request must start on the main connection.');
    }

    public function test_the_same_player_always_gets_the_same_connection(): void
    {
        $this->withPool(2);
        $this->pingRoutes();
        $token = $this->tokenOnAnExtraUser();

        foreach (range(1, 3) as $i) {
            $this->getJson("/jugador/{$token}/ping")->assertJson(['connection' => ConnectionPool::pick($token)]);
        }
    }

    public function test_everything_that_is_not_a_player_page_stays_on_the_main_connection(): void
    {
        $this->withPool(2);
        $this->pingRoutes();

        $this->getJson('/creditos-ping')->assertJson(['connection' => $this->base]);
    }

    public function test_with_no_pool_nothing_changes(): void
    {
        $this->pingRoutes();

        $this->getJson('/jugador/anything/ping')->assertJson(['connection' => $this->base]);
    }

    // --- counting ---------------------------------------------------------------------------------

    public function test_the_budget_counts_and_warns_for_each_user_separately(): void
    {
        config(['platform.connection_budget.per_hour' => 10]);
        Log::spy();

        foreach (range(1, 8) as $i) {
            ConnectionBudget::hit('player.inbox', 'mysql');
        }

        Log::shouldHaveReceived('warning')->with('db_connection_budget_high', \Mockery::on(fn ($c) => $c['connection'] === 'mysql'))->once();

        // Eight on another user is its own 80 %: the total (16) says nothing about it.
        foreach (range(1, 8) as $i) {
            ConnectionBudget::hit('player.inbox', 'mysql_pool_1');
        }

        Log::shouldHaveReceived('warning')->with('db_connection_budget_high', \Mockery::on(fn ($c) => $c['connection'] === 'mysql_pool_1'))->once();

        $summary = ConnectionBudget::summary();

        $this->assertSame(16, $summary['this_hour']);
        $this->assertSame(['mysql' => 8, 'mysql_pool_1' => 8], $summary['by_connection']);
    }

    public function test_the_allowance_is_spent_per_user(): void
    {
        config(['platform.connection_budget.per_hour' => 5]);
        Log::spy();

        foreach (range(1, 5) as $i) {
            ConnectionBudget::hit('x', 'mysql_pool_1');
        }
        ConnectionBudget::hit('x', 'mysql');

        Log::shouldHaveReceived('error')->with('db_connection_budget_spent', \Mockery::on(fn ($c) => $c['connection'] === 'mysql_pool_1'))->once();
        Log::shouldNotHaveReceived('error', ['db_connection_budget_spent', \Mockery::on(fn ($c) => $c['connection'] === 'mysql')]);
    }

    public function test_without_a_connection_the_budget_uses_the_default_one(): void
    {
        ConnectionBudget::hit('cmd:something');

        $this->assertSame([$this->base => 1], ConnectionBudget::summary()['by_connection']);
    }

    public function test_alternating_between_users_does_not_look_like_a_new_connection_every_time(): void
    {
        $this->withPool(1);
        config(['database.persistent' => true, 'database.reuse_probe_every' => 1]);

        ConnectionReuseProbe::sample(); // main: first sight
        ConnectionReuseProbe::sample(); // main: reused

        DB::setDefaultConnection('mysql_pool_1');
        ConnectionReuseProbe::sample(); // the other user: first sight

        DB::setDefaultConnection($this->base);
        ConnectionReuseProbe::sample(); // main again: still the same connection

        DB::setDefaultConnection('mysql_pool_1');
        ConnectionReuseProbe::sample(); // the other user again: reused

        DB::setDefaultConnection($this->base);

        $summary = ConnectionReuseProbe::summary();

        $this->assertSame(2, $summary['fresh']);
        $this->assertSame(3, $summary['reused']);
    }
}
