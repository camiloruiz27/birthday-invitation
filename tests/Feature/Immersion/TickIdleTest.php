<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Jobs\SendTimelineMail;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\TickActivity;
use App\Modules\Immersion\Support\TickPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The scheduler runs the tick as its own PHP process every minute, and each run
 * that touches the database spends one of the host's 500 connections per hour.
 * A case has a new moment about every eleven minutes, so on a running game most
 * of those runs have nothing to do. These pin down that they now cost nothing
 * (the next event is planned from the clock, not looked up), and that "nothing
 * to do" can never mean "an envelope arrives late".
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

    private function runningGame(int $startedMinutesAgo = 0, array $offsets = [10, 20]): Game
    {
        $game = Game::create([
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'running',
            'started_at' => now()->subMinutes($startedMinutesAgo),
            'paused_seconds_total' => 0,
        ]);

        foreach ($offsets as $i => $offset) {
            $game->timelineEvents()->create([
                'type' => 'email',
                'trigger_offset_minutes' => $offset,
                'title' => "Sobre {$i}",
                'body_markdown' => 'x',
                'delivery_mode' => 'all',
            ]);
        }

        return $game;
    }

    // --- nothing running ---------------------------------------------------------

    public function test_an_idle_tick_opens_no_database_connection(): void
    {
        // The very first look has no plan and so looks once (and finds nothing).
        Carbon::setTestNow('2026-10-14 10:02:00');
        Artisan::call('immersion:tick');

        foreach (['10:03', '10:04', '10:05', '10:07', '10:11'] as $time) {
            Carbon::setTestNow("2026-10-14 {$time}:00");
            $this->assertSame([], $this->queriesDuringTick(), $time);
        }
    }

    public function test_an_idle_platform_still_looks_every_fifteen_minutes_as_a_safety_net(): void
    {
        Carbon::setTestNow('2026-10-14 10:02:00');
        Artisan::call('immersion:tick');

        Carbon::setTestNow('2026-10-14 10:10:00');
        $this->assertSame([], $this->queriesDuringTick(), 'Not every five minutes any more.');

        Carbon::setTestNow('2026-10-14 10:15:00');
        $this->assertNotSame([], $this->queriesDuringTick());
    }

    public function test_the_guard_can_be_switched_off(): void
    {
        config(['immersion.tick.idle_skip' => false]);
        Carbon::setTestNow('2026-10-14 10:03:00');

        $this->assertNotSame([], $this->queriesDuringTick());
    }

    // --- a running game: the next event is planned, not looked up -------------------

    public function test_between_events_the_tick_costs_no_connection(): void
    {
        Carbon::setTestNow('2026-10-14 10:03:00');
        $this->runningGame(startedMinutesAgo: 3, offsets: [10, 20]);

        // The first wake after the game started finds no plan, looks once and
        // plans what is due next.
        Artisan::call('immersion:tick');

        Carbon::setTestNow('2026-10-14 10:04:00');
        $this->assertSame([], $this->queriesDuringTick(), 'Minute 4 of a game whose next event is at minute 10.');

        Carbon::setTestNow('2026-10-14 10:09:00');
        $this->assertSame([], $this->queriesDuringTick());
    }

    public function test_the_tick_wakes_exactly_when_the_event_is_due_and_sends_it(): void
    {
        Bus::fake([SendTimelineMail::class]);
        Carbon::setTestNow('2026-10-14 10:03:00');
        $game = $this->runningGame(startedMinutesAgo: 3, offsets: [10, 20]);
        Artisan::call('immersion:tick');

        // Minute 10 of the game: 7 minutes after "now".
        Carbon::setTestNow('2026-10-14 10:10:00');

        $this->assertNotSame([], $this->queriesDuringTick());
        $this->assertSame(1, $game->timelineEvents()->whereNotNull('sent_at')->count());
    }

    public function test_the_next_event_is_planned_after_each_run(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $this->runningGame(startedMinutesAgo: 0, offsets: [10, 25]);

        Artisan::call('immersion:tick');
        $this->assertSame(now()->addMinutes(10)->getTimestamp(), TickPlanner::nextDue());

        Carbon::setTestNow('2026-10-14 10:10:00');
        Artisan::call('immersion:tick');
        $this->assertSame(now()->addMinutes(15)->getTimestamp(), TickPlanner::nextDue(), 'The second event, at minute 25.');
    }

    public function test_pauses_move_the_plan(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $game = $this->runningGame(startedMinutesAgo: 0, offsets: [10]);
        Artisan::call('immersion:tick');
        $this->assertSame(now()->addMinutes(10)->getTimestamp(), TickPlanner::nextDue());

        // Paused for five minutes then resumed: the clock lost five minutes.
        $game->update(['status' => 'paused', 'paused_at' => now()->addMinutes(2)]);
        $this->assertNull(TickPlanner::nextDue(), 'A change to the game throws the plan away.');

        Carbon::setTestNow('2026-10-14 10:07:00');
        $game->update(['status' => 'running', 'paused_at' => null, 'paused_seconds_total' => 300]);
        Artisan::call('immersion:tick');

        $this->assertSame(
            $game->started_at->getTimestamp() + 300 + 600,
            TickPlanner::nextDue(),
            'Start + pause + offset.'
        );
    }

    public function test_a_paused_game_schedules_nothing(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $game = $this->runningGame(startedMinutesAgo: 0, offsets: [10]);
        $game->update(['status' => 'paused', 'paused_at' => now()]);

        Artisan::call('immersion:tick');

        $this->assertGreaterThan(now()->addYear()->getTimestamp(), TickPlanner::nextDue());
    }

    public function test_the_earliest_event_across_games_wins(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $this->runningGame(startedMinutesAgo: 0, offsets: [30]);
        $this->runningGame(startedMinutesAgo: 5, offsets: [12]);

        Artisan::call('immersion:tick');

        $this->assertSame(now()->addMinutes(7)->getTimestamp(), TickPlanner::nextDue());
    }

    public function test_a_lost_plan_means_run_not_skip(): void
    {
        Carbon::setTestNow('2026-10-14 10:03:00');
        $this->runningGame(startedMinutesAgo: 3, offsets: [10]);
        Artisan::call('immersion:tick');
        $this->assertSame([], $this->queriesDuringTick());

        // A cleared cache (or a restart that lost it): the tick looks again.
        Cache::flush();

        $this->assertNotSame([], $this->queriesDuringTick());
        $this->assertNotNull(TickPlanner::nextDue(), 'And plans again.');
    }

    public function test_a_game_ending_clears_the_plan(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $game = $this->runningGame(startedMinutesAgo: 0, offsets: [10]);
        Artisan::call('immersion:tick');

        $game->finish();

        $this->assertNull(TickPlanner::nextDue());
    }

    public function test_creating_or_sending_an_event_clears_the_plan(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $game = $this->runningGame(startedMinutesAgo: 0, offsets: [10]);
        Artisan::call('immersion:tick');
        $this->assertNotNull(TickPlanner::nextDue());

        $game->timelineEvents()->create([
            'type' => 'email', 'trigger_offset_minutes' => 3, 'title' => 'Nuevo', 'body_markdown' => 'x', 'delivery_mode' => 'all',
        ]);

        $this->assertNull(TickPlanner::nextDue());
    }

    public function test_writes_that_cannot_move_an_event_leave_the_plan_alone(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $game = $this->runningGame(startedMinutesAgo: 0, offsets: [10]);
        Artisan::call('immersion:tick');
        $plan = TickPlanner::nextDue();

        // A fresh instance: the one that created the game still remembers being
        // created, which is itself a reason to replan.
        Game::find($game->id)->update(['interrogation_enabled' => false]);

        $this->assertSame($plan, TickPlanner::nextDue());
    }

    // --- the queue --------------------------------------------------------------------

    public function test_the_worker_only_starts_when_a_job_is_waiting(): void
    {
        config(['queue.default' => 'database']);
        Carbon::setTestNow('2026-10-14 10:03:00');

        $this->assertFalse(TickActivity::workerDue(now()));

        SendTimelineMail::dispatch(1, 1);

        $this->assertTrue(TickActivity::workerDue(now()));
    }

    public function test_a_retry_is_not_due_until_its_moment_and_an_earlier_one_is_never_pushed_back(): void
    {
        config(['queue.default' => 'database']);
        Carbon::setTestNow('2026-10-14 10:03:00');

        TickActivity::touch(now()->addMinutes(15)->getTimestamp());
        $this->assertFalse(TickActivity::workerDue(now()));

        TickActivity::touch(now()->addMinutes(2)->getTimestamp());
        TickActivity::touch(now()->addMinutes(20)->getTimestamp());

        $this->assertSame(now()->addMinutes(2)->getTimestamp(), TickActivity::wakeAt());

        Carbon::setTestNow('2026-10-14 10:05:00');
        $this->assertTrue(TickActivity::workerDue(now()));
    }

    public function test_the_worker_note_is_cleared_when_the_worker_starts(): void
    {
        config(['queue.default' => 'database']);
        Carbon::setTestNow('2026-10-14 10:03:00');
        TickActivity::touch();

        Artisan::call('immersion:tick');

        $this->assertNull(TickActivity::wakeAt());
    }

    public function test_an_ordinary_event_runs_in_the_tick_not_through_the_queue(): void
    {
        config(['queue.default' => 'database']);
        Carbon::setTestNow('2026-10-14 10:10:00');
        $game = $this->runningGame(startedMinutesAgo: 10, offsets: [10]);

        Artisan::call('immersion:tick');

        // Run in place: sent, and nothing was queued for a worker to pick up.
        $this->assertSame(1, $game->timelineEvents()->whereNotNull('sent_at')->count());
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_an_event_still_to_be_voiced_takes_the_slow_queue(): void
    {
        config(['queue.default' => 'database']);
        Carbon::setTestNow('2026-10-14 10:10:00');
        $game = $this->runningGame(startedMinutesAgo: 10, offsets: []);
        $game->timelineEvents()->create([
            'type' => 'audio_email',
            'trigger_offset_minutes' => 10,
            'title' => 'Audio',
            'body_markdown' => 'x',
            'audio_script' => 'Hola',
            'delivery_mode' => 'all',
        ]);

        // Only the timeline half, so the worker does not pick it up here.
        Artisan::call('immersion:process-timeline');

        $this->assertSame(1, DB::table('jobs')->where('queue', 'slow')->count());
    }

    // --- the file the cron script reads before it starts PHP -----------------------------

    public function test_the_next_wake_is_written_for_the_cron_script(): void
    {
        $path = sys_get_temp_dir().'/next-run-'.uniqid();
        config(['immersion.tick.next_run_file' => $path]);
        Carbon::setTestNow('2026-10-14 10:00:00');

        try {
            $this->runningGame(startedMinutesAgo: 0, offsets: [10]);
            Artisan::call('immersion:tick');

            $this->assertSame((string) now()->addMinutes(10)->getTimestamp(), trim(file_get_contents($path)));

            // Anything that can move the plan tells the script to wake PHP now.
            Game::query()->first()->update(['status' => 'paused', 'paused_at' => now()]);
            $this->assertSame('0', trim(file_get_contents($path)));
        } finally {
            File::delete($path);
        }
    }

    public function test_a_queued_job_pulls_the_wake_forward(): void
    {
        config(['queue.default' => 'database']);
        $path = sys_get_temp_dir().'/next-run-'.uniqid();
        config(['immersion.tick.next_run_file' => $path]);
        Carbon::setTestNow('2026-10-14 10:00:00');

        try {
            $this->runningGame(startedMinutesAgo: 0, offsets: [60]);
            Artisan::call('immersion:tick');
            $this->assertSame((string) now()->addMinutes(60)->getTimestamp(), trim(file_get_contents($path)));

            SendTimelineMail::dispatch(1, 1);

            $this->assertSame((string) now()->getTimestamp(), trim(file_get_contents($path)));
        } finally {
            File::delete($path);
        }
    }

    public function test_an_unwritable_location_is_not_an_error(): void
    {
        config(['immersion.tick.next_run_file' => '/no/such/directory/next-run']);
        Carbon::setTestNow('2026-10-14 10:00:00');
        $this->runningGame(startedMinutesAgo: 0, offsets: [10]);

        $this->assertSame(0, Artisan::call('immersion:tick'));
    }

    // --- the old guarantees, still true ---------------------------------------------------

    public function test_a_busy_worker_does_not_stop_the_timeline(): void
    {
        config(['queue.default' => 'database']);
        Carbon::setTestNow('2026-10-14 10:10:00');
        $game = $this->runningGame(startedMinutesAgo: 10, offsets: [10]);
        TickActivity::touch();

        // A previous tick is still inside a long text-to-speech job.
        $workerLock = Cache::lock('immersion:tick:worker', 600);
        $this->assertTrue($workerLock->get());

        $this->assertSame(0, Artisan::call('immersion:tick'));

        $this->assertSame(1, $game->timelineEvents()->whereNotNull('sent_at')->count());
    }

    public function test_two_overlapping_ticks_do_not_both_scan_the_timeline(): void
    {
        Carbon::setTestNow('2026-10-14 10:10:00');
        $game = $this->runningGame(startedMinutesAgo: 10, offsets: [10]);

        $timelineLock = Cache::lock('immersion:tick:timeline', 120);
        $this->assertTrue($timelineLock->get());

        Artisan::call('immersion:tick');

        $this->assertSame(0, $game->timelineEvents()->whereNotNull('sent_at')->count());
    }
}
