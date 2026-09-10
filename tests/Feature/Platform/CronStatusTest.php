<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\PlatformServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The command exists to give a straight answer about the server, so the one
 * thing it must never do is answer "todo en orden" while the cron is dead.
 */
class CronStatusTest extends TestCase
{
    use RefreshDatabase;

    private function beat(?string $at): void
    {
        if ($at === null) {
            Cache::forget(PlatformServiceProvider::HEARTBEAT_KEY);

            return;
        }

        Cache::put(PlatformServiceProvider::HEARTBEAT_KEY, $at, now()->addDay());
    }

    public function test_it_fails_when_the_scheduler_has_never_run(): void
    {
        $this->beat(null);

        $this->artisan('platform:cron-status')
            ->expectsOutputToContain('nunca')
            ->assertExitCode(1);
    }

    public function test_it_fails_when_the_heartbeat_is_stale(): void
    {
        // Twenty minutes with no tick: the cron stopped, whatever the panel
        // still says it has configured.
        $this->beat(now()->subMinutes(20)->toIso8601String());

        $this->artisan('platform:cron-status')->assertExitCode(1);
    }

    public function test_a_fresh_heartbeat_is_accepted(): void
    {
        $this->beat(now()->toIso8601String());

        // Still exits 1 here because the test environment runs the sync
        // queue, which the command reports as a real problem — the point of
        // this case is that the heartbeat itself is not what fails.
        $this->artisan('platform:cron-status')
            ->expectsOutputToContain('Último latido')
            ->doesntExpectOutputToContain('nunca');
    }

    /**
     * A tolerance below two minutes would make the check flap: the scheduler
     * ticks once a minute, so a reading taken a minute later is normal.
     */
    public function test_the_tolerance_cannot_be_set_below_two_minutes(): void
    {
        $this->beat(now()->subMinutes(2)->toIso8601String());

        $this->artisan('platform:cron-status', ['--tolerance' => 0])
            ->doesntExpectOutputToContain('lleva 2 minutos sin correr');
    }

    public function test_it_reports_the_sync_queue_as_a_problem(): void
    {
        $this->beat(now()->toIso8601String());

        config(['queue.default' => 'sync']);

        $this->artisan('platform:cron-status')
            ->expectsOutputToContain('sync')
            ->assertExitCode(1);
    }
}
