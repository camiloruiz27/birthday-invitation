<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\Game;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class PrivateAreaTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_the_dashboard_separates_running_games_from_drafts(): void
    {
        $user = $this->gameMaster();

        [$running] = $this->gameOwnedBy($user, ['status' => 'running', 'started_at' => now()]);
        $this->gameOwnedBy($user, ['status' => 'draft']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('activeGames', 1)
                ->where('activeGames.0.id', $running->id)
                ->has('draftGames', 1)
                ->where('stats.games', 2)
                ->where('stats.running', 1)
                ->where('stats.cases', 1)
            );
    }

    public function test_the_dashboard_shows_only_your_own_games(): void
    {
        $user = $this->gameMaster();
        $this->gameOwnedBy($user, ['status' => 'running', 'started_at' => now()]);

        $other = $this->gameMaster(attributes: ['email' => 'other@example.com']);
        $this->gameOwnedBy($other, ['status' => 'running', 'started_at' => now()]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('activeGames', 1)
                ->where('stats.games', 1)
            );
    }

    public function test_a_new_account_sees_an_empty_dashboard_rather_than_an_error(): void
    {
        $this->actingAs($this->userWithoutAccess())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('library', 0)
                ->where('stats.cases', 0)
                ->where('stats.games', 0)
            );
    }

    public function test_the_library_lists_owned_cases_with_their_game_counts(): void
    {
        $user = $this->gameMaster();
        $this->gameOwnedBy($user);
        $this->gameOwnedBy($user);

        MysteryCase::create(['slug' => 'not-owned', 'name' => 'No comprado']);

        $this->actingAs($user)
            ->get(route('library'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Library')
                ->has('cases', 1)
                ->where('cases.0.slug', 'steve-jacobs')
                ->where('cases.0.games_count', 2)
                ->where('cases.0.playable', true)
            );
    }

    public function test_the_library_flags_a_case_whose_content_is_not_installed(): void
    {
        $user = $this->userWithoutAccess();
        $this->grantCase($user, 'ghost-case');

        $this->actingAs($user)
            ->get(route('library'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cases.0.slug', 'ghost-case')
                // Owned, but there is no manifest to play, so the UI must not
                // offer to start a game with it.
                ->where('cases.0.playable', false)
            );
    }

    public function test_the_games_section_lists_your_games_with_player_counts(): void
    {
        $user = $this->gameMaster();
        $this->gameOwnedBy($user);

        $this->actingAs($user)
            ->get(route('immersion.gm.games.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/Games')
                ->has('games', 1)
                ->where('games.0.players_count', 1)
                ->where('hasLibrary', true)
            );
    }

    public function test_the_create_game_page_offers_only_owned_cases(): void
    {
        MysteryCase::create(['slug' => 'not-owned', 'name' => 'No comprado', 'published_at' => now()]);

        $this->actingAs($this->gameMaster())
            ->get(route('immersion.gm.games.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/CreateGame')
                ->has('library', 1)
                ->where('library.0.slug', 'steve-jacobs')
            );
    }

    public function test_creating_a_game_lands_on_its_console(): void
    {
        $user = $this->gameMaster();

        $this->actingAs($user)->post(route('immersion.gm.games.store'), [
            'name' => 'Mesa del sábado',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ]);

        $game = Game::firstWhere('name', 'Mesa del sábado');

        // Creating a game is a step towards running it, so it hands you the
        // console and the invitation links.
        $this->assertNotNull($game);
        $this->get(route('immersion.gm.game.show', $game->id))->assertOk();
    }

    public function test_the_create_route_is_not_swallowed_by_the_game_id_route(): void
    {
        // /partidas/crear and /partidas/{game} share a shape; whereNumber is
        // what keeps the literal segment reachable.
        $this->actingAs($this->gameMaster())
            ->get(route('immersion.gm.games.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('GameMaster/CreateGame'));
    }
}
