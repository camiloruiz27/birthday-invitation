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
        $event = $this->find('immersion:process-timeline');

        $this->assertNotNull($event, 'The case timeline is not scheduled at all.');
        $this->assertSame('* * * * *', $event->expression);
    }

    /**
     * A tick can outlast its minute — an event may carry text-to-speech and
     * one email per player. The per-event row lock would still stop a double
     * send, but overlapping ticks would pile up behind it.
     */
    public function test_the_timeline_cannot_overlap_itself(): void
    {
        $this->assertTrue($this->find('immersion:process-timeline')->withoutOverlapping);
    }

    /**
     * Jobs are dispatched rather than run inline because they do slow things.
     * On a host with no daemon the scheduler is what drains them — but
     * `queue:work` rejects the sync driver, so it is registered only when
     * there is a real queue to work.
     */
    public function test_the_queue_worker_follows_the_queue_driver(): void
    {
        $worker = $this->find('queue:work');

        if (config('queue.default') === 'sync') {
            $this->assertNull($worker, 'queue:work cannot run on the sync driver.');

            return;
        }

        $this->assertNotNull($worker, 'Dispatched jobs would never be picked up.');
        $this->assertStringContainsString('--stop-when-empty', $worker->command);
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
