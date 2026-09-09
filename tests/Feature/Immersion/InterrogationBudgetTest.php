<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InterrogationBudgetTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(): Game
    {
        $game = Game::create([
            'name' => 'Test Game',
            'status' => 'running',
            'interrogation_enabled' => true,
        ]);

        $game->players()->create([
            'name' => 'Player One',
            'email' => 'player@example.com',
            'access_token' => 'test-token-1',
        ]);

        return $game;
    }

    public function test_session_anchors_the_budget_declared_by_its_case(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        $game = $this->makeGame();
        $player = $game->players()->first();

        // steve-jacobs declares interrogation_questions = 5 in its manifest.
        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Primera']
        )->assertOk()->assertJson(['max_questions' => 5, 'questions_remaining' => 4]);

        $session = InterrogationSession::first();
        $this->assertSame(5, $session->max_questions);
    }

    public function test_a_case_budget_wins_over_the_module_default(): void
    {
        config(['immersion.interrogation.max_questions' => 99]);

        $case = new CaseDefinition('with-limit', '', [
            'limits' => ['interrogation_questions' => 3],
        ]);

        $this->assertSame(3, $case->interrogationQuestions());
    }

    public function test_a_case_without_a_declared_budget_falls_back_to_config(): void
    {
        config(['immersion.interrogation.max_questions' => 7]);

        $case = new CaseDefinition('no-limit', '', []);

        $this->assertSame(7, $case->interrogationQuestions());
    }

    public function test_budget_is_enforced_atomically_and_cannot_be_exceeded(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        $game = $this->makeGame();
        $player = $game->players()->first();

        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'elizabeth-foster',
            'started_at' => now(),
            'max_questions' => 2,
        ]);

        $this->assertTrue($session->reserveQuestion());
        $this->assertTrue($session->reserveQuestion());

        // Third reservation finds no slot left and writes nothing.
        $this->assertFalse($session->reserveQuestion());
        $this->assertSame(2, $session->fresh()->questions_used);
    }

    public function test_reserving_a_question_on_a_closed_session_fails(): void
    {
        $game = $this->makeGame();
        $player = $game->players()->first();

        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'elizabeth-foster',
            'started_at' => now(),
            'max_questions' => 5,
            'questions_used' => 1,
            'closed_at' => now(),
        ]);

        $this->assertFalse($session->reserveQuestion());
        $this->assertSame(1, $session->fresh()->questions_used);
    }

    public function test_session_closes_when_the_last_question_is_spent(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'Ya dije todo.'], 200)]);

        $game = $this->makeGame();
        $player = $game->players()->first();

        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'elizabeth-foster',
            'started_at' => now(),
            'max_questions' => 2,
            'questions_used' => 1,
        ]);

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Ultima']
        )->assertOk()->assertJson(['closed' => true, 'questions_remaining' => 0]);

        $this->assertTrue($session->fresh()->isClosed());
    }

    public function test_no_ai_call_is_made_once_the_budget_is_spent(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No deberia llamarse.'], 200)]);

        $game = $this->makeGame();
        $player = $game->players()->first();

        InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'elizabeth-foster',
            'started_at' => now(),
            'max_questions' => 5,
            'questions_used' => 5,
        ]);

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => 'Una mas']
        )->assertStatus(422)->assertJson(['closed' => true]);

        Http::assertNothingSent();
        $this->assertDatabaseCount('immersion_interrogation_messages', 0);
    }
}
