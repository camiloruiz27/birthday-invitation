<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InterrogationExclusivityTest extends TestCase
{
    use RefreshDatabase;

    private function makeGameWithTwoPlayers(): array
    {
        $game = Game::create([
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

    public function test_second_player_cannot_ask_a_suspect_already_claimed(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        [, $playerA, $playerB] = $this->makeGameWithTwoPlayers();

        $this->postJson(
            route('immersion.player.interrogation.ask', [$playerA->access_token, 'elizabeth-foster']),
            ['question' => 'Donde estabas?']
        )->assertOk();

        $response = $this->postJson(
            route('immersion.player.interrogation.ask', [$playerB->access_token, 'elizabeth-foster']),
            ['question' => 'Y tu donde estabas?']
        );

        $response->assertStatus(403)->assertJson(['locked' => true, 'locked_by' => 'Jugador A']);

        $this->assertSame(1, InterrogationSession::where('suspect_slug', 'elizabeth-foster')->count());
    }

    public function test_second_player_sees_the_readonly_chat_but_not_the_ficha_until_it_ends(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        [, $playerA, $playerB] = $this->makeGameWithTwoPlayers();

        $this->postJson(
            route('immersion.player.interrogation.ask', [$playerA->access_token, 'elizabeth-foster']),
            ['question' => 'Donde estabas?']
        )->assertOk();

        // Solo 1 pregunta usada (de 5): el interrogatorio sigue abierto, asi
        // que la ficha aun no se muestra a nadie.
        $this->get(route('immersion.player.interrogation.show', [$playerB->access_token, 'elizabeth-foster']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/InterrogationChat')
                ->where('lockedBy', 'Jugador A')
                ->has('session.messages', 2)
                ->where('ficha', null)
            );

        // Once the interrogation is over, everyone at the table gets it.
        InterrogationSession::where('suspect_slug', 'elizabeth-foster')
            ->update(['closed_at' => now()]);

        $this->get(route('immersion.player.interrogation.show', [$playerB->access_token, 'elizabeth-foster']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('lockedBy', 'Jugador A')
                ->has('ficha.profile'));
    }

    public function test_first_player_can_keep_asking_their_own_claimed_session(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        [, $playerA] = $this->makeGameWithTwoPlayers();

        $this->postJson(
            route('immersion.player.interrogation.ask', [$playerA->access_token, 'elizabeth-foster']),
            ['question' => 'Pregunta 1']
        )->assertOk();

        $this->postJson(
            route('immersion.player.interrogation.ask', [$playerA->access_token, 'elizabeth-foster']),
            ['question' => 'Pregunta 2']
        )->assertOk()->assertJson(['questions_used' => 2]);
    }

    public function test_viewing_before_anyone_asks_does_not_claim_the_suspect(): void
    {
        [, $playerA, $playerB] = $this->makeGameWithTwoPlayers();

        $this->get(route('immersion.player.interrogation.show', [$playerA->access_token, 'elizabeth-foster']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('lockedBy', null));

        $this->assertDatabaseCount('immersion_interrogation_sessions', 0);

        // Player B (otro jugador que ni siquiera abrio el chat antes) sigue
        // pudiendo ser el primero en preguntar de verdad.
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);
        $this->postJson(
            route('immersion.player.interrogation.ask', [$playerB->access_token, 'elizabeth-foster']),
            ['question' => 'Primera pregunta']
        )->assertOk();
    }
}
