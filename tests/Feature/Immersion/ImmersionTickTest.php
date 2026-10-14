<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * One process per minute for the timeline AND the queue: the scheduler gives
 * every command its own PHP process, and each one costs a MySQL connection out
 * of the host's 500-per-hour allowance.
 */
class ImmersionTickTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_tick_fires_the_events_that_are_due(): void
    {
        Bus::fake();

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

        $this->assertSame(0, Artisan::call('immersion:tick'));

        Bus::assertDispatched(
            \App\Modules\Immersion\Jobs\DispatchTimelineEvent::class,
            fn ($job) => $job->eventId === $event->id
        );
    }

    public function test_the_tick_also_drains_the_queue_when_there_is_a_real_one(): void
    {
        config(['queue.default' => 'database']);

        $this->assertSame(0, Artisan::call('immersion:tick'));
        $this->assertStringNotContainsString('rejects', Artisan::output());
    }

    public function test_a_failing_timeline_step_does_not_stop_the_queue_and_is_reported(): void
    {
        config(['queue.default' => 'database']);

        // Break the timeline half only.
        $this->app->bind(
            \App\Modules\Immersion\Console\Commands\ProcessImmersionTimeline::class,
            fn () => new class extends \Illuminate\Console\Command {
                protected $signature = 'immersion:process-timeline';

                public function handle(): int
                {
                    throw new \RuntimeException('timeline broke');
                }
            }
        );

        $this->assertSame(1, Artisan::call('immersion:tick'));
    }
}
