<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Database\Seeders\ImmersionDemoSeeder;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The demo seeder is the fastest way to get a playable game locally, so it has
 * to produce one that is actually reachable — an unowned game is denied by
 * GamePolicy and would be useless.
 */
class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_game_that_can_actually_be_opened(): void
    {
        $this->seed(ImmersionDemoSeeder::class);

        $game = Game::firstOrFail();
        $owner = $game->owner;

        $this->assertNotNull($owner, 'The demo game must have an owner.');
        $this->assertTrue($owner->ownsCase($game->case_slug));
        $this->assertSame(6, $game->players()->count());
        $this->assertSame(8, $game->timelineEvents()->count());

        // The whole point: the Game Master can reach their own console.
        $this->actingAs($owner)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk();

        // And an invited player can reach their inbox.
        $this->get(route('immersion.player.inbox', $game->players()->first()->access_token))
            ->assertOk();
    }

    public function test_running_it_twice_reuses_the_demo_account(): void
    {
        $this->seed(ImmersionDemoSeeder::class);
        $this->seed(ImmersionDemoSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame(2, Game::count());
    }
}
