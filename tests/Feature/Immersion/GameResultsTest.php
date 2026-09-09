<?php

namespace Tests\Feature\Immersion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The Game Master's accusations table.
 *
 * It reads each accusation through its player (`player.accusation`), so the
 * controller has to load the relation that way round — loading the sibling
 * `accusations.player` leaves every row looking unanswered.
 */
class GameResultsTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_it_shows_each_players_accusation(): void
    {
        $user = $this->gameMaster();
        [$game, $player] = $this->gameOwnedBy($user);

        $game->accusations()->create([
            'player_id' => $player->id,
            'suspect_name' => 'Daniel Blake',
            'weapon' => 'Suplementos manipulados',
            'motive' => 'Desacuerdos laborales',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.results', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/Results')
                ->has('game.players', 1)
                // The page counts submissions off this relation.
                ->has('game.players.0.accusation')
                ->where('game.players.0.accusation.suspect_name', 'Daniel Blake')
            );
    }

    public function test_a_player_without_an_accusation_is_reported_as_pending(): void
    {
        $user = $this->gameMaster();
        [$game] = $this->gameOwnedBy($user);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.results', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('game.players.0.accusation', null)
            );
    }

    public function test_it_does_not_leak_player_credentials(): void
    {
        $user = $this->gameMaster();
        [$game] = $this->gameOwnedBy($user);

        $response = $this->actingAs($user)
            ->get(route('immersion.gm.game.results', $game))
            ->assertOk();

        // This page only compares accusations; it has no reason to hand out
        // the tokens that let someone play as another person.
        $this->assertStringNotContainsString('test-token-', $response->content());
    }
}
