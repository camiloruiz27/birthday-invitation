<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * A player's access_token is their whole credential: whoever holds it can
 * read that player's inbox, interrogations and accusation. Players are shown
 * each other's names (a claimed suspect names its claimer), so these tests
 * pin down that the name is all that crosses between players.
 */
class PlayerCredentialExposureTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function makeGameWithTwoPlayers(): array
    {
        $game = Game::create([
            'user_id' => $this->gameMaster()->id,
            'name' => 'Test Game',
            'status' => 'running',
            'interrogation_enabled' => true,
        ]);

        $playerA = $game->players()->create([
            'name' => 'Jugador A',
            'email' => 'a@example.com',
            'access_token' => 'token-a',
        ]);

        $playerB = $game->players()->create([
            'name' => 'Jugador B',
            'email' => 'b@example.com',
            'access_token' => 'token-b',
        ]);

        return [$game, $playerA, $playerB];
    }

    private function claimSuspect(string $token): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        $this->postJson(
            route('immersion.player.interrogation.ask', [$token, 'elizabeth-foster']),
            ['question' => 'Donde estabas?']
        )->assertOk();
    }

    public function test_interrogation_index_does_not_leak_another_players_token(): void
    {
        [, $playerA, $playerB] = $this->makeGameWithTwoPlayers();

        $this->claimSuspect($playerA->access_token);

        $response = $this->get(route('immersion.player.interrogation.index', $playerB->access_token))
            ->assertOk();

        // Player B legitimately needs their own token for navigation links.
        $this->assertStringContainsString('token-b', $response->content());

        // Player A's credentials must not be anywhere in the payload, even
        // though their name is shown as the claimer of the suspect.
        $this->assertStringNotContainsString('token-a', $response->content());
        $this->assertStringNotContainsString('a@example.com', $response->content());
        $this->assertStringContainsString('Jugador A', $response->content());
    }

    public function test_interrogation_chat_does_not_leak_the_claiming_players_token(): void
    {
        [, $playerA, $playerB] = $this->makeGameWithTwoPlayers();

        $this->claimSuspect($playerA->access_token);

        $response = $this->get(route('immersion.player.interrogation.show', [$playerB->access_token, 'elizabeth-foster']))
            ->assertOk();

        $this->assertStringNotContainsString('token-a', $response->content());
        $this->assertStringNotContainsString('a@example.com', $response->content());
    }

    public function test_game_master_still_sees_player_tokens_and_emails(): void
    {
        [$game] = $this->makeGameWithTwoPlayers();

        $response = $this->actingAs($game->owner)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk();

        // The Game Master panel is what hands out the per-player inbox links.
        $this->assertStringContainsString('token-a', $response->content());
        $this->assertStringContainsString('token-b', $response->content());
        $this->assertStringContainsString('a@example.com', $response->content());
    }
}
