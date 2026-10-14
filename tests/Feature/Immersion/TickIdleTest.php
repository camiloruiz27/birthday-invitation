<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Jobs\SendTimelineMail;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\TickActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The scheduler runs the tick as its own PHP process every minute, and each run
 * that touches the database spends one of the host's 500 connections per hour.
 * An idle platform used to spend 60 an hour doing nothing; these pin down that
 * it now opens none, and that "idle" can never mean "a running game's timeline
 * stops".
 */
class TickIdleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['immersion.tick.idle_skip' => true]);
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @return array<int, array<string, mixed>> */
    private function queriesDuringTick(): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        Artisan::call('immersion:tick');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        return $queries;
    }

    public function test_an_idle_tick_opens_no_database_connection(): void
    {
        Carbon::setTestNow('2026-10-14 10:03:00');

        $this->assertSame([], $this->queriesDuringTick());
    }

    public function test_an_idle_platform_still_looks_every_five_minutes_as_a_safety_net(): void
    {
        Carbon::setTestNow('2026-10-14 10:05:00');

        $this->assertNotSame([], $this->queriesDuringTick());
    }

    public function test_a_running_game_keeps_the_tick_awake_every_minute(): void
    {
        Carbon::setTestNow('2026-10-14 10:03:00');

        Game::create(['name' => 'Mesa', 'case_slug' => 'habitacion-314', 'status' => 'running', 'started_at' => now()]);

        $this->assertTrue(TickActivity::isActive());
        $this->assertNotSame([], $this->queriesDuringTick());
    }

    public function test_a_draft_game_does_not_wake_it(): void
    {
        Carbon::setTestNow('2026-10-14 10:03:00');

        Game::create(['name' => 'Mesa', 'case_slug' => 'habitacion-314', 'status' => 'draft']);

        $this->assertFalse(TickActivity::isActive());
    }

    public function test_a_queued_job_wakes_it(): void
    {
        Carbon::setTestNow('2026-10-14 10:03:00');
        config(['queue.default' => 'database']);

        $this->assertFalse(TickActivity::isActive());

        SendTimelineMail::dispatch(1, 1);

        $this->assertTrue(TickActivity::isActive());
    }

    public function test_the_hint_expires_and_a_longer_window_is_never_shortened(): void
    {
        Carbon::setTestNow('2026-10-14 10:03:00');

        TickActivity::touch(30);
        TickActivity::touch(5);

        Carbon::setTestNow('2026-10-14 10:20:00');
        $this->assertTrue(TickActivity::isActive(), 'The longer window must survive a shorter touch.');

        Carbon::setTestNow('2026-10-14 10:40:00');
        $this->assertFalse(TickActivity::isActive());
    }

    public function test_the_guard_can_be_switched_off(): void
    {
        config(['immersion.tick.idle_skip' => false]);
        Carbon::setTestNow('2026-10-14 10:03:00');

        $this->assertNotSame([], $this->queriesDuringTick());
    }

    public function test_a_busy_worker_does_not_stop_the_timeline(): void
    {
        Bus::fake([DispatchTimelineEvent::class]);
        config(['queue.default' => 'database']);

        $game = Game::create([
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'running',
            'started_at' => now()->subHour(),
            'paused_seconds_total' => 0,
        ]);
        $event = $game->timelineEvents()->create([
            'type' => 'email',
            'trigger_offset_minutes' => 0,
            'title' => 'Sobre 1',
            'body_markdown' => 'x',
        ]);

        // A previous tick is still inside a long text-to-speech job.
        $workerLock = Cache::lock('immersion:tick:worker', 600);
        $this->assertTrue($workerLock->get());

        $this->assertSame(0, Artisan::call('immersion:tick'));

        Bus::assertDispatched(DispatchTimelineEvent::class, fn ($job) => $job->eventId === $event->id);
    }

    public function test_two_overlapping_ticks_do_not_both_scan_the_timeline(): void
    {
        Bus::fake([DispatchTimelineEvent::class]);

        $game = Game::create([
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'running',
            'started_at' => now()->subHour(),
            'paused_seconds_total' => 0,
        ]);
        $game->timelineEvents()->create([
            'type' => 'email',
            'trigger_offset_minutes' => 0,
            'title' => 'Sobre 1',
            'body_markdown' => 'x',
        ]);

        $timelineLock = Cache::lock('immersion:tick:timeline', 120);
        $this->assertTrue($timelineLock->get());

        Artisan::call('immersion:tick');

        Bus::assertNothingDispatched();
    }
}
