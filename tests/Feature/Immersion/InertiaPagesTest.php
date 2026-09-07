<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaPagesTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsGameMaster(): void
    {
        $this->withSession(['immersion_gm_ok' => true]);
    }

    private function makeGame(): Game
    {
        $game = Game::create(['name' => 'Test Game', 'status' => 'draft']);

        $game->players()->create([
            'name' => 'Player One',
            'email' => 'player@example.com',
            'access_token' => 'test-token-1',
        ]);

        return $game;
    }

    public function test_gm_login_page_renders(): void
    {
        $this->get(route('immersion.gm.login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('GameMaster/Login'));
    }

    public function test_gm_login_requires_middleware_for_dashboard(): void
    {
        $this->get(route('immersion.gm.dashboard'))
            ->assertRedirect(route('immersion.gm.login'));
    }

    public function test_gm_dashboard_renders(): void
    {
        $this->actingAsGameMaster();
        $this->makeGame();

        $this->get(route('immersion.gm.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/Dashboard')
                ->has('games', 1)
            );
    }

    public function test_gm_game_show_renders(): void
    {
        $this->actingAsGameMaster();
        $game = $this->makeGame();

        $this->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/Game')
                ->where('game.id', $game->id)
                ->has('game.players', 1)
            );
    }

    public function test_gm_results_renders(): void
    {
        $this->actingAsGameMaster();
        $game = $this->makeGame();

        $this->get(route('immersion.gm.game.results', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('GameMaster/Results'));
    }

    public function test_gm_interrogations_renders(): void
    {
        $this->actingAsGameMaster();
        $game = $this->makeGame();

        $this->get(route('immersion.gm.game.interrogations', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('GameMaster/Interrogations'));
    }

    public function test_player_inbox_renders(): void
    {
        $game = $this->makeGame();
        $player = $game->players()->first();

        $this->get(route('immersion.player.inbox', $player->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/Inbox')
                ->where('player.id', $player->id)
            );
    }

    public function test_player_accusation_renders(): void
    {
        $game = $this->makeGame();
        $player = $game->players()->first();

        $this->get(route('immersion.player.accusation', $player->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/Accusation')
                ->where('unlocked', false)
            );
    }

    public function test_player_interrogation_index_requires_enabled_flag(): void
    {
        $game = $this->makeGame();
        $player = $game->players()->first();

        $this->get(route('immersion.player.interrogation.index', $player->access_token))
            ->assertForbidden();
    }

    public function test_player_interrogation_index_renders_when_enabled(): void
    {
        $game = $this->makeGame();
        $game->update(['interrogation_enabled' => true]);
        $player = $game->players()->first();

        $this->get(route('immersion.player.interrogation.index', $player->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Player/InterrogationIndex'));
    }

    public function test_player_interrogation_chat_renders(): void
    {
        $game = $this->makeGame();
        $game->update(['interrogation_enabled' => true]);
        $player = $game->players()->first();

        $this->get(route('immersion.player.interrogation.show', [$player->access_token, 'elizabeth-foster']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/InterrogationChat')
                ->where('slug', 'elizabeth-foster')
            );
    }
}
