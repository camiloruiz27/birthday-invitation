<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Support\AiGate;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PDOException;
use Tests\TestCase;

/**
 * The small guards that keep the platform answering when the hosting plan's
 * limits bite: how many questions wait on the AI at once (each one holds a PHP
 * worker), a log that says an outage once instead of every minute, and the
 * disk cleanup that stops the file allowance running out.
 */
class OpsGuardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    // --- AiGate ------------------------------------------------------------------------

    public function test_only_so_many_questions_run_at_once(): void
    {
        config(['immersion.ai.max_concurrent' => 2]);

        $first = AiGate::acquire();
        $second = AiGate::acquire();
        $third = AiGate::acquire();

        $this->assertInstanceOf(Lock::class, $first);
        $this->assertInstanceOf(Lock::class, $second);
        $this->assertNull($third, 'Every slot is busy.');

        AiGate::release($first);

        $this->assertInstanceOf(Lock::class, AiGate::acquire(), 'A released slot is free again.');
    }

    public function test_no_limit_means_nothing_to_hold_or_release(): void
    {
        config(['immersion.ai.max_concurrent' => 0]);

        $this->assertFalse(AiGate::acquire());
        AiGate::release(false);
        AiGate::release(null);
        $this->assertTrue(true);
    }

    private function playerWithSuspect(): array
    {
        $game = Game::create(['name' => 'Mesa', 'status' => 'running', 'interrogation_enabled' => true]);
        $player = $game->players()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-ana']);

        return [$game, $player];
    }

    public function test_a_busy_gate_turns_the_question_away_unspent(): void
    {
        config(['immersion.ai.max_concurrent' => 1]);
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);
        [, $player] = $this->playerWithSuspect();

        $held = AiGate::acquire();

        $response = $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Donde estabas?']
        );

        $response->assertStatus(429)
            ->assertHeader('Retry-After', '5')
            ->assertJson(['busy' => true, 'ai_unavailable' => true]);

        Http::assertNothingSent();
        $this->assertSame(0, InterrogationSession::first()->questions_used, 'The player lost nothing.');

        AiGate::release($held);
    }

    public function test_the_slot_is_given_back_after_a_question_whether_it_worked_or_not(): void
    {
        config(['immersion.ai.max_concurrent' => 1]);
        [, $player] = $this->playerWithSuspect();
        $url = route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']);

        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);
        $this->postJson($url, ['question' => 'Uno'])->assertOk();

        Http::fake(['*' => Http::response('boom', 500)]);
        $this->postJson($url, ['question' => 'Dos'])->assertOk(); // an evasion, not an error

        $this->assertInstanceOf(Lock::class, AiGate::acquire(), 'Both questions finished: the only slot is free.');
    }

    // --- the tick's log during an outage ---------------------------------------------------

    public function test_an_outage_is_logged_once_by_the_tick_not_once_a_minute(): void
    {
        config(['immersion.tick.idle_skip' => false]);
        Log::spy();

        $this->app->bind(
            \App\Modules\Immersion\Console\Commands\ProcessImmersionTimeline::class,
            fn () => new class extends \Illuminate\Console\Command
            {
                protected $signature = 'immersion:process-timeline';

                public function handle(): int
                {
                    $pdo = new PDOException('max_connections_per_hour');
                    $pdo->errorInfo = ['HY000', 1226, 'exceeded'];

                    throw new QueryException('select 1', [], $pdo);
                }
            }
        );

        foreach (range(1, 5) as $i) {
            $this->assertSame(1, Artisan::call('immersion:tick'));
        }

        Log::shouldHaveReceived('error')->with('immersion_tick_step_failed', \Mockery::any())->once();
    }

    public function test_any_other_failure_of_the_tick_is_still_logged_every_time(): void
    {
        config(['immersion.tick.idle_skip' => false]);
        Log::spy();

        $this->app->bind(
            \App\Modules\Immersion\Console\Commands\ProcessImmersionTimeline::class,
            fn () => new class extends \Illuminate\Console\Command
            {
                protected $signature = 'immersion:process-timeline';

                public function handle(): int
                {
                    throw new \RuntimeException('a real bug');
                }
            }
        );

        foreach (range(1, 3) as $i) {
            Artisan::call('immersion:tick');
        }

        Log::shouldHaveReceived('error')->with('immersion_tick_step_failed', \Mockery::any())->times(3);
    }

    // --- disk cleanup ----------------------------------------------------------------------------

    /** @return array{0: string, 1: string, 2: string} */
    private function scratch(): array
    {
        $base = sys_get_temp_dir().'/prune-'.uniqid();
        $dirs = ["{$base}/cache", "{$base}/ops", "{$base}/sessions"];

        foreach ($dirs as $dir) {
            File::makeDirectory($dir, 0777, true);
        }

        config([
            'cache.stores.file.path' => $dirs[0],
            'cache.stores.ops.path' => $dirs[1],
            'session.files' => $dirs[2],
            'session.lifetime' => 120,
        ]);

        return [$base, ...$dirs];
    }

    public function test_it_deletes_what_has_expired_and_keeps_the_rest(): void
    {
        [$base, $cache, $ops, $sessions] = $this->scratch();

        try {
            File::put("{$cache}/expired", '0000000001'.serialize('old'));
            File::put("{$cache}/alive", '9999999999'.serialize('new'));
            File::makeDirectory("{$cache}/ab/cd", 0777, true);
            File::put("{$cache}/ab/cd/nested-expired", '0000000005'.serialize('old'));
            File::put("{$ops}/expired", '0000000001'.serialize('old'));
            File::put("{$ops}/alive", '9999999999'.serialize('new'));

            File::put("{$sessions}/stale", 'x');
            touch("{$sessions}/stale", time() - 3 * 86400);
            File::put("{$sessions}/fresh", 'x');

            $this->artisan('ops:prune-cache')->assertSuccessful();

            $this->assertFileDoesNotExist("{$cache}/expired");
            $this->assertFileDoesNotExist("{$cache}/ab/cd/nested-expired");
            $this->assertFileExists("{$cache}/alive");
            $this->assertFileDoesNotExist("{$ops}/expired");
            $this->assertFileExists("{$ops}/alive");
            $this->assertFileDoesNotExist("{$sessions}/stale");
            $this->assertFileExists("{$sessions}/fresh");
        } finally {
            File::deleteDirectory($base);
        }
    }

    public function test_a_dry_run_only_counts(): void
    {
        [$base, $cache] = $this->scratch();

        try {
            File::put("{$cache}/expired", '0000000001'.serialize('old'));

            $this->artisan('ops:prune-cache', ['--dry-run' => true])
                ->expectsOutputToContain('se borrarían')
                ->assertSuccessful();

            $this->assertFileExists("{$cache}/expired");
        } finally {
            File::deleteDirectory($base);
        }
    }

    public function test_it_never_touches_a_gitignore_or_a_file_it_cannot_read_as_cache(): void
    {
        [$base, $cache] = $this->scratch();

        try {
            File::put("{$cache}/.gitignore", "*\n");
            File::put("{$cache}/not-a-cache-file", 'hello world');

            $this->artisan('ops:prune-cache')->assertSuccessful();

            $this->assertFileExists("{$cache}/.gitignore");
            $this->assertFileExists("{$cache}/not-a-cache-file", 'Without a valid expiry it is not ours to delete.');
        } finally {
            File::deleteDirectory($base);
        }
    }

    public function test_a_missing_directory_is_not_an_error(): void
    {
        config(['cache.stores.ops.path' => '/no/such/dir', 'session.files' => '/no/such/dir']);

        $this->artisan('ops:prune-cache')->assertSuccessful();
    }

    public function test_it_is_scheduled_hourly_and_costs_no_connection(): void
    {
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'ops:prune-cache'));

        $this->assertNotNull($event);
        $this->assertSame('25 * * * *', $event->expression);

        // It does not use the database: no connection is counted for it.
        $this->artisan('ops:prune-cache')->assertSuccessful();
        $this->assertArrayNotHasKey('cmd:prune-cache', \App\Modules\Platform\Support\ConnectionBudget::top());
    }
}
