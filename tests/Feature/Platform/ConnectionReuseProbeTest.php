<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Support\ConnectionReuseProbe;
use App\Modules\Platform\Support\Ops;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * DB_PERSISTENT only helps if the web server keeps its PHP processes alive, and
 * nothing reports whether it does. The probe measures it: same process, same
 * MySQL connection id as last time = reused.
 */
class ConnectionReuseProbeTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['database.reuse_probe_every' => 1]);
    }

    public function test_it_does_nothing_when_persistent_connections_are_off(): void
    {
        config(['database.persistent' => false]);

        ConnectionReuseProbe::sample();
        ConnectionReuseProbe::sample();

        $this->assertSame(['enabled' => false, 'reused' => 0, 'fresh' => 0, 'ratio' => null], ConnectionReuseProbe::summary());
    }

    public function test_the_first_sight_of_a_process_is_a_new_connection_and_the_next_one_a_reuse(): void
    {
        config(['database.persistent' => true]);

        ConnectionReuseProbe::sample();
        ConnectionReuseProbe::sample();
        ConnectionReuseProbe::sample();

        $summary = ConnectionReuseProbe::summary();

        $this->assertSame(1, $summary['fresh']);
        $this->assertSame(2, $summary['reused']);
        $this->assertSame(0.667, $summary['ratio']);
    }

    public function test_a_process_whose_connection_changed_counts_as_new(): void
    {
        config(['database.persistent' => true]);

        ConnectionReuseProbe::sample();
        // The process is the same but MySQL gave it another connection.
        Ops::store()->put('platform:db-reuse:pid:'.getmypid(), 999999, 600);
        ConnectionReuseProbe::sample();

        $this->assertSame(2, ConnectionReuseProbe::summary()['fresh']);
        $this->assertSame(0, ConnectionReuseProbe::summary()['reused']);
    }

    public function test_it_warns_once_an_hour_when_persistence_is_not_working(): void
    {
        config(['database.persistent' => true]);
        Log::spy();

        foreach (range(1, 60) as $i) {
            // Every sample finds a different connection than the last.
            Ops::store()->put('platform:db-reuse:pid:'.getmypid(), 1_000_000 + $i, 600);
            ConnectionReuseProbe::sample();
        }

        Log::shouldHaveReceived('warning')->with('db_persistent_reuse_low', \Mockery::any())->once();
    }

    public function test_a_healthy_ratio_never_warns(): void
    {
        config(['database.persistent' => true]);
        Log::spy();

        foreach (range(1, 80) as $i) {
            ConnectionReuseProbe::sample();
        }

        Log::shouldNotHaveReceived('warning', ['db_persistent_reuse_low', \Mockery::any()]);
        $this->assertGreaterThan(0.9, ConnectionReuseProbe::summary()['ratio']);
    }

    public function test_a_request_is_sampled_after_the_response(): void
    {
        config(['database.persistent' => true]);

        $this->get(route('cases.index'))->assertOk();
        $this->get(route('cases.index'))->assertOk();

        $summary = ConnectionReuseProbe::summary();

        $this->assertSame(2, $summary['fresh'] + $summary['reused']);
    }

    public function test_the_admin_sees_how_well_it_is_working(): void
    {
        config(['database.persistent' => true]);
        ConnectionReuseProbe::sample();
        ConnectionReuseProbe::sample();

        $admin = $this->gameMaster(attributes: ['email' => 'admin@example.com']);
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin->fresh())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('limits.connections.reuse.this_hour.enabled', true)
                ->has('limits.connections.reuse.this_hour.ratio')
                ->has('limits.connections.reuse.last_hour'));
    }
}
