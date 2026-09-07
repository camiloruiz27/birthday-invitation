<?php

namespace Tests\Feature\Immersion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class InertiaPagesTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_gm_dashboard_requires_authentication(): void
    {
        $this->get(route('immersion.gm.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_gm_dashboard_renders_only_the_signed_in_masters_games(): void
    {
        $user = $this->gameMaster();
        $this->gameOwnedBy($user);

        // Another master's game must not appear in this listing.
        $other = $this->gameMaster(attributes: ['email' => 'other@example.com']);
        $this->gameOwnedBy($other);

        $this->actingAs($user)
            ->get(route('immersion.gm.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/Dashboard')
                ->has('games', 1)
                ->has('library', 1)
            );
    }

    public function test_gm_game_show_renders(): void
    {
        $user = $this->gameMaster();
        [$game] = $this->gameOwnedBy($user);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/Game')
                ->where('game.id', $game->id)
                ->has('game.players', 1)
            );
    }

    public function test_gm_results_renders(): void
    {
        $user = $this->gameMaster();
        [$game] = $this->gameOwnedBy($user);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.results', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('GameMaster/Results'));
    }

    public function test_gm_interrogations_renders(): void
    {
        $user = $this->gameMaster();
        [$game] = $this->gameOwnedBy($user);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.interrogations', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('GameMaster/Interrogations'));
    }

    public function test_player_inbox_renders(): void
    {
        [, $player] = $this->gameOwnedBy($this->gameMaster());

        $this->get(route('immersion.player.inbox', $player->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/Inbox')
                ->where('player.id', $player->id)
            );
    }

    public function test_player_accusation_renders(): void
    {
        [, $player] = $this->gameOwnedBy($this->gameMaster());

        $this->get(route('immersion.player.accusation', $player->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/Accusation')
                ->where('unlocked', false)
            );
    }

    public function test_player_interrogation_index_requires_enabled_flag(): void
    {
        [, $player] = $this->gameOwnedBy($this->gameMaster());

        $this->get(route('immersion.player.interrogation.index', $player->access_token))
            ->assertForbidden();
    }

    public function test_player_interrogation_index_renders_when_enabled(): void
    {
        [$game, $player] = $this->gameOwnedBy($this->gameMaster());
        $game->update(['interrogation_enabled' => true]);

        $this->get(route('immersion.player.interrogation.index', $player->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Player/InterrogationIndex'));
    }

    public function test_player_interrogation_chat_renders(): void
    {
        [$game, $player] = $this->gameOwnedBy($this->gameMaster());
        $game->update(['interrogation_enabled' => true]);

        $this->get(route('immersion.player.interrogation.show', [$player->access_token, 'elizabeth-foster']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/InterrogationChat')
                ->where('slug', 'elizabeth-foster')
            );
    }
}
