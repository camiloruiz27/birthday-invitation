<?php

namespace Tests\Feature\Platform;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * What the server's one cron entry actually sets in motion.
 *
 * The schedule is not in App\Console\Kernel — each module registers its own
 * in its service provider, which is why `Kernel::schedule()` looks empty and
 * is not. Nothing here fails loudly when it breaks: a command that stops
 * being scheduled just silently never runs again, and the first symptom is a
 * table waiting for an envelope that is not coming.
 */
class ScheduleTest extends TestCase
{
    /** @return array<string, \Illuminate\Console\Scheduling\Event> */
    private function events(): array
    {
        $found = [];

        foreach (app(Schedule::class)->events() as $event) {
            if ($event->command) {
                $found[$event->command] = $event;
            }
        }

        return $found;
    }

    private function find(string $needle): ?\Illuminate\Console\Scheduling\Event
    {
        foreach ($this->events() as $command => $event) {
            if (str_contains($command, $needle)) {
                return $event;
            }
        }

        return null;
    }

    /**
     * The heartbeat of a live case. Anything less frequent shows up as
     * envelopes arriving late to a table that is sitting there waiting.
     */
    public function test_the_case_timeline_runs_every_minute(): void
    {
        $event = $this->find('immersion:tick');

        $this->assertNotNull($event, 'The case timeline is not scheduled at all.');
        $this->assertSame('* * * * *', $event->expression);
    }

    /**
     * The scheduler runs every command as its own PHP process, and each one
     * opens a MySQL connection out of the host's 500-per-hour allowance. The
     * timeline and the queue worker share ONE process (immersion:tick); either
     * coming back as its own entry would silently double that cost.
     */
    public function test_the_timeline_and_the_worker_share_one_process(): void
    {
        $this->assertNull($this->find('immersion:process-timeline'));
        $this->assertNull($this->find('queue:work'));
    }

    /**
     * The scheduler's overlap guard skips the WHOLE tick while the previous one
     * runs. One text-to-speech job keeps the worker busy for minutes, and with
     * the guard no game would receive an envelope in the meantime. The tick
     * guards its timeline and worker halves with their own locks instead
     * (ImmersionTickTest), so an overlapping tick still moves the timeline.
     */
    public function test_the_tick_is_not_guarded_by_the_scheduler_overlap_lock(): void
    {
        $this->assertFalse($this->find('immersion:tick')->withoutOverlapping);
    }

    /** Frozen credits come back on their own, wherever credits are in play. */
    public function test_stale_credit_holds_are_released_when_credits_are_enabled(): void
    {
        $event = $this->find('immersion:release-stale-holds');

        if (! config('immersion.credits.enabled')) {
            $this->assertNull($event);

            return;
        }

        $this->assertNotNull($event);
        $this->assertTrue($event->withoutOverlapping);
    }

    /**
     * Off unless switched on: it mails people, and the privacy text that
     * covers it has to be live first. When on, it runs every second day and
     * never overlaps itself (a slow SMTP run must not start a second sender).
     */
    public function test_the_first_game_offer_is_scheduled_only_when_enabled(): void
    {
        $event = $this->find('platform:send-first-game-offers');

        if (! config('platform.first_game_offer.enabled')) {
            $this->assertNull($event);

            return;
        }

        $this->assertNotNull($event);
        $this->assertSame('40 10 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }

    /**
     * Enabled, it is on the schedule once a day but only passes its filter on
     * alternate days — across a month end too, where "*\/2" on the day of the
     * month would run two days in a row.
     */
    public function test_the_first_game_offer_runs_every_second_day(): void
    {
        $runs = [];
        $start = \Illuminate\Support\Carbon::parse('2026-12-20 10:40');

        // Across a month end and a year end (2026 has 365 days, an odd number,
        // so a day-of-the-year parity would skip a day at New Year).
        for ($i = 0; $i < 40; $i++) {
            $this->travelTo($start->copy()->addDays($i));
            $runs[] = \App\Modules\Platform\Console\Commands\SendFirstGameOffers::runsToday();
        }

        foreach (range(1, count($runs) - 1) as $i) {
            $this->assertNotSame($runs[$i - 1], $runs[$i], 'Two consecutive days behaved the same: '.json_encode($runs));
        }
    }

    /** Abandoned checkouts never granted anything, so daily is plenty. */
    public function test_stale_orders_are_expired(): void
    {
        $this->assertNotNull($this->find('platform:expire-stale-orders'));
    }

    /**
     * The safety net for a payment whose webhook never arrived — the
     * difference between an order settling itself and a buyer writing in to
     * ask where their case went. Only meaningful once payments are live.
     */
    public function test_pending_orders_are_reconciled_when_payments_are_live(): void
    {
        $event = $this->find('platform:reconcile-pending-orders');

        if (! config('platform.payments.enabled')) {
            $this->assertNull($event);

            return;
        }

        $this->assertNotNull($event);
        $this->assertTrue($event->withoutOverlapping);
    }
}
