<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The load test needs a table's worth of real tokens; making fifty games by hand
 * is hopeless, so a command does it, and removes it again afterwards. It must
 * never run against the real database by accident.
 */
class LoadTestSeedTest extends TestCase
{
    use RefreshDatabase;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = 'storage/app/load-tokens-test-'.uniqid().'.json';
    }

    protected function tearDown(): void
    {
        File::delete(base_path($this->output));

        parent::tearDown();
    }

    public function test_it_creates_running_games_with_players_and_writes_their_tokens(): void
    {
        $this->artisan('immersion:load-seed', ['--games' => 3, '--players' => 4, '--output' => $this->output])->assertSuccessful();

        $this->assertSame(3, Game::count());
        $this->assertSame(12, Player::count());
        $this->assertSame(['running'], Game::pluck('status')->unique()->all());

        $file = json_decode(File::get(base_path($this->output)), true);

        $this->assertSame('habitacion-314', $file['case']);
        $this->assertCount(12, $file['players']);
        $this->assertCount(12, array_unique(array_column($file['players'], 'token')));

        foreach ($file['players'] as $player) {
            $this->assertTrue(\Illuminate\Support\Str::isUuid($player['token']), 'Tokens must be real-looking UUIDs: the column holds 36 characters.');
        }
    }

    public function test_the_games_are_already_part_way_through_the_case(): void
    {
        $this->artisan('immersion:load-seed', ['--games' => 1, '--players' => 2, '--minutes' => 25, '--output' => $this->output])->assertSuccessful();

        $game = Game::first();

        $this->assertGreaterThanOrEqual(25, $game->elapsedMinutes());
        $this->assertGreaterThan(0, $game->timelineEvents()->whereNotNull('sent_at')->count());
        $this->assertGreaterThan(0, $game->timelineEvents()->whereNull('sent_at')->count(), 'Later events are still to come.');
    }

    public function test_a_token_opens_the_players_inbox(): void
    {
        $this->artisan('immersion:load-seed', ['--games' => 1, '--players' => 1, '--output' => $this->output])->assertSuccessful();

        $token = json_decode(File::get(base_path($this->output)), true)['players'][0]['token'];

        $this->get(route('immersion.player.inbox', $token))->assertOk();
    }

    public function test_it_refuses_to_run_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('immersion:load-seed', ['--games' => 1, '--output' => $this->output])->assertFailed();

        $this->assertSame(0, Game::count());
    }

    public function test_purge_removes_only_the_test_games(): void
    {
        $real = Game::create(['name' => 'Mesa de verdad', 'status' => 'running']);
        $this->artisan('immersion:load-seed', ['--games' => 2, '--players' => 2, '--output' => $this->output])->assertSuccessful();

        $this->artisan('immersion:load-seed', ['--purge' => true, '--output' => $this->output])->assertSuccessful();

        $this->assertSame([$real->id], Game::pluck('id')->all());
        $this->assertFileDoesNotExist(base_path($this->output));
    }
}
