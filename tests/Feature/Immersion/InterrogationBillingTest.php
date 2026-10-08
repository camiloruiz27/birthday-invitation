<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Ai\GatewayInterrogationProvider;
use App\Modules\Immersion\Ai\InterrogationUnavailable;
use App\Modules\Immersion\Ai\NullInterrogationProvider;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\AiCredits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * A question is paid for when the suspect actually answers it.
 *
 * Before, the credit left the wallet first and the gateway was called second,
 * so a gateway that failed still cost the player a credit and a question. These
 * tests pin the opposite: a failed call is free and gives the question back.
 * The suspect covers for the first failures in character; once they keep
 * happening the player is told there is a problem.
 */
class InterrogationBillingTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const SUSPECT = 'rachel-miller';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            // A configured gateway, so the real provider is bound rather than
            // the null one — the null one answers without ever calling out.
            'immersion.ai.base_url' => 'https://gateway.test',
            'immersion.ai.api_key' => 'test-key',
            'immersion.ai.interrogation_enabled' => true,

            'immersion.credits.enabled' => true,
            'immersion.credits.costs.question' => 1,
            'immersion.credits.costs.ending.classic' => 0,
            'immersion.credits.included_with_case' => false,
        ]);
    }

    /**
     * A started game with a live reservation and one player at the table.
     *
     * @return array{0: User, 1: Game, 2: Player}
     */
    private function runningGame(): array
    {
        $owner = $this->gameMaster();

        $game = Game::create([
            'user_id' => $owner->id,
            'name' => 'Mesa de prueba',
            'status' => 'draft',
            'interrogation_enabled' => true,
        ]);

        app(AiCredits::class)->grant($owner, 100);

        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'access_token' => 'token-ana',
        ]);

        $this->post(route('logout'));

        return [$owner, $game, $player];
    }

    private function ask(Player $player, string $question = '¿Donde estaba esa noche?'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, self::SUSPECT]),
            ['question' => $question]
        );
    }

    private function spent(Game $game): int
    {
        return (int) app(AiCredits::class)->holdFor($game)->spent;
    }

    private function assertNothingCharged(Game $game): void
    {
        $this->assertSame(0, $this->spent($game), 'A failed answer must not cost a credit.');
        $this->assertSame(
            0,
            CreditLedgerEntry::where('game_id', $game->id)->where('reason', CreditLedgerEntry::REASON_SPEND)->count()
        );

        $session = InterrogationSession::where('game_id', $game->id)->first();
        $this->assertSame(0, $session->questions_used, 'The question slot must be given back.');
        $this->assertSame(0, InterrogationMessage::count(), 'Nothing is stored for an unanswered question.');
    }

    public function test_an_answered_question_is_charged_exactly_once(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        [, $game, $player] = $this->runningGame();

        $this->ask($player)
            ->assertOk()
            ->assertJsonPath('suspect_message.content', 'No fui yo.')
            ->assertJsonPath('questions_used', 1);

        $this->assertSame(1, $this->spent($game));
        $this->assertSame(
            1,
            CreditLedgerEntry::where('game_id', $game->id)->where('reason', CreditLedgerEntry::REASON_SPEND)->count()
        );
        $this->assertSame(2, InterrogationMessage::count());
    }

    public function test_a_gateway_error_is_covered_in_character_and_costs_nothing(): void
    {
        Http::fake(['*' => Http::response(['error' => 'down', 'code' => 'model_overloaded'], 503)]);

        [, $game, $player] = $this->runningGame();

        $this->ask($player)
            ->assertOk()
            ->assertJsonPath('ai_degraded', true)
            ->assertJsonPath('suspect_message.content', NullInterrogationProvider::REPLY)
            ->assertJsonPath('questions_used', 0)
            ->assertJsonPath('questions_remaining', 5);

        $this->assertNothingCharged($game);
    }

    public function test_a_gateway_that_cannot_be_reached_is_covered_in_character_and_costs_nothing(): void
    {
        Http::fake(fn () => throw new ConnectionException('timed out'));

        [, $game, $player] = $this->runningGame();

        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);

        $this->assertNothingCharged($game);
    }

    public function test_an_empty_reply_is_covered_in_character_and_costs_nothing(): void
    {
        Http::fake(['*' => Http::response(['reply' => '   '], 200)]);

        [, $game, $player] = $this->runningGame();

        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);

        $this->assertNothingCharged($game);
    }

    public function test_after_two_deflections_the_player_is_told_there_is_a_problem(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        [, $game, $player] = $this->runningGame();

        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);
        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);

        // The third failure in a row is no longer a hiccup.
        $this->ask($player)
            ->assertStatus(503)
            ->assertJson(['ai_unavailable' => true])
            ->assertJsonMissingPath('suspect_message');

        // And it stays an error until the gateway recovers.
        $this->ask($player)->assertStatus(503);

        $this->assertNothingCharged($game);
    }

    public function test_a_real_answer_resets_the_count_of_failures(): void
    {
        [, $game, $player] = $this->runningGame();

        Http::fake(['*' => Http::sequence()
            ->push('boom', 500)
            ->push('boom', 500)
            ->push(['reply' => 'Estaba en casa.'], 200)
            ->push('boom', 500)
            ->push('boom', 500),
        ]);

        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);
        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);
        $this->ask($player)->assertOk()->assertJsonMissingPath('ai_degraded');

        // Two more failures are two more deflections, not an error: the count
        // started over when the suspect answered.
        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);
        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);

        $this->assertSame(1, $this->spent($game));
    }

    public function test_how_many_deflections_come_before_the_error_is_configurable(): void
    {
        config(['immersion.ai.evasions_before_error' => 0]);
        Http::fake(['*' => Http::response('boom', 500)]);

        [, , $player] = $this->runningGame();

        $this->ask($player)->assertStatus(503)->assertJson(['ai_unavailable' => true]);
    }

    public function test_a_deflection_is_not_kept_in_the_transcript_or_the_history(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push('boom', 500)
            ->push(['reply' => 'Estaba en casa.'], 200),
        ]);

        [, $game, $player] = $this->runningGame();

        $this->ask($player, 'Primera')->assertOk()->assertJsonPath('ai_degraded', true);
        $this->ask($player, 'Segunda')->assertOk()->assertJsonPath('questions_used', 1);

        $this->assertSame(['Segunda', 'Estaba en casa.'], InterrogationMessage::orderBy('id')->pluck('content')->all());

        $sent = Http::recorded()->map(fn ($pair) => $pair[0]->data())->values();
        $this->assertSame([], $sent[1]['history'], 'The deflection never reaches the model as something the suspect said.');
        $this->assertSame(1, $this->spent($game));
    }

    public function test_a_failed_question_can_be_asked_again_and_is_then_charged_once(): void
    {
        [, $game, $player] = $this->runningGame();

        // Stubs registered first win, so a failure then a success has to be one
        // sequence rather than two fakes.
        Http::fake(['*' => Http::sequence()
            ->push('boom', 500)
            ->push(['reply' => 'Estaba en casa.'], 200),
        ]);

        $this->ask($player)->assertOk()->assertJsonPath('ai_degraded', true);
        $this->assertNothingCharged($game);

        $this->ask($player)->assertOk()->assertJsonPath('questions_used', 1);

        $this->assertSame(1, $this->spent($game));
        $this->assertSame(2, InterrogationMessage::count());
    }

    public function test_the_question_is_not_sent_twice_to_the_gateway(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        [, , $player] = $this->runningGame();

        $this->ask($player, 'Primera pregunta')->assertOk();
        $this->ask($player, 'Segunda pregunta')->assertOk();

        $sent = Http::recorded()->map(fn ($pair) => $pair[0]->data())->values();

        // The current question travels as `question`, never also as the last
        // turn of the history.
        $this->assertSame([], $sent[0]['history']);
        $this->assertSame('Segunda pregunta', $sent[1]['question']);
        $this->assertSame(
            ['Primera pregunta', 'No fui yo.'],
            array_column($sent[1]['history'], 'content')
        );
    }

    public function test_a_released_hold_still_stops_the_question_before_calling_the_gateway(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        [, $game, $player] = $this->runningGame();

        app(AiCredits::class)->release($game);

        $this->ask($player)->assertStatus(402)->assertJson(['out_of_credits' => true]);

        Http::assertNothingSent();
        $this->assertSame(0, InterrogationSession::where('game_id', $game->id)->first()->questions_used);
    }

    public function test_an_answer_that_exists_is_delivered_even_if_the_hold_was_released_meanwhile(): void
    {
        [, $game, $player] = $this->runningGame();

        // The sweeper gives the reservation back while the model is thinking.
        Http::fake(function () use ($game) {
            app(AiCredits::class)->release($game);

            return Http::response(['reply' => 'No fui yo.'], 200);
        });

        $this->ask($player)
            ->assertOk()
            ->assertJsonPath('suspect_message.content', 'No fui yo.');

        // Delivered, but unbilled: the player did nothing wrong.
        $this->assertSame(0, $this->spent($game));
        $this->assertSame(2, InterrogationMessage::count());
    }

    public function test_with_the_ai_switched_off_the_question_is_still_charged(): void
    {
        config(['immersion.ai.interrogation_enabled' => false]);
        Http::fake();

        [, $game, $player] = $this->runningGame();

        $this->ask($player)->assertOk();

        Http::assertNothingSent();
        $this->assertSame(1, $this->spent($game));
    }

    public function test_an_unconfigured_gateway_raises_instead_of_faking_a_reply(): void
    {
        Http::fake();

        [, $game] = $this->runningGame();

        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $game->players()->first()->id,
            'suspect_slug' => self::SUSPECT,
            'started_at' => now(),
            'max_questions' => 5,
        ]);

        config(['immersion.ai.api_key' => 'CHANGE_ME']);

        $this->expectException(InterrogationUnavailable::class);

        (new GatewayInterrogationProvider)->ask($session, 'Hola');
    }
}
