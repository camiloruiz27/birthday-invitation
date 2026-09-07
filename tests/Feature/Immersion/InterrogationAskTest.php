<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InterrogationAskTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(bool $interrogationEnabled = true): Game
    {
        $game = Game::create([
            'name' => 'Test Game',
            'status' => 'running',
            'interrogation_enabled' => $interrogationEnabled,
        ]);

        $game->players()->create([
            'name' => 'Player One',
            'email' => 'player@example.com',
            'access_token' => 'test-token-1',
        ]);

        return $game;
    }

    public function test_asking_a_question_creates_messages_and_returns_json(): void
    {
        Http::fake([
            '*' => Http::response(['reply' => 'No fui yo.'], 200),
        ]);

        $game = $this->makeGame();
        $player = $game->players()->first();

        $response = $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Donde estabas esa noche?']
        );

        $response->assertOk()->assertJson([
            'questions_used' => 1,
            'questions_remaining' => 4,
            'closed' => false,
        ]);

        $this->assertSame('Donde estabas esa noche?', $response->json('player_message.content'));

        $this->assertDatabaseCount('immersion_interrogation_messages', 2);
    }

    public function test_reaching_max_questions_closes_the_session_and_reveals_testimony(): void
    {
        Http::fake([
            '*' => Http::response(['reply' => 'Ya dije todo.'], 200),
        ]);

        $game = $this->makeGame();
        $player = $game->players()->first();

        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'elizabeth-foster',
            'started_at' => now(),
            'questions_used' => 4,
        ]);

        $response = $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Ultima pregunta']
        );

        $response->assertOk()->assertJson(['closed' => true]);
        $this->assertNotNull($response->json('original_testimony_html'));

        $this->assertTrue($session->fresh()->isClosed());
    }

    public function test_asking_when_session_already_closed_returns_422(): void
    {
        $game = $this->makeGame();
        $player = $game->players()->first();

        InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'elizabeth-foster',
            'started_at' => now(),
            'questions_used' => 5,
            'closed_at' => now(),
        ]);

        $response = $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Otra pregunta']
        );

        $response->assertStatus(422)->assertJson(['closed' => true]);
    }

    public function test_asking_when_interrogation_disabled_is_forbidden(): void
    {
        $game = $this->makeGame(interrogationEnabled: false);
        $player = $game->players()->first();

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Donde estabas?']
        )->assertForbidden();
    }

    public function test_asking_with_unknown_suspect_returns_404(): void
    {
        $game = $this->makeGame();
        $player = $game->players()->first();

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'nobody']),
            ['question' => 'Donde estabas?']
        )->assertNotFound();
    }
}
