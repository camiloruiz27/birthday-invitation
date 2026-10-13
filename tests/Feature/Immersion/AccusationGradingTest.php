<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Ai\Contracts\AccusationGrader;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Jobs\GradeAccusations;
use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\AccusationScoreboard;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Immersion\Support\GameCost;
use App\Modules\Immersion\Support\TimelineRecipients;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Engine features added on 2026-10-12, against the caso-calificado fixture:
 * the victim's label, the accused-but-not-questioned suspect, private clues
 * spread across the table, and the graded accusation that breaks ties.
 */
class AccusationGradingTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const CASE_SLUG = 'caso-calificado';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Bus::fake();

        $this->app->singleton(
            CaseRegistry::class,
            fn () => new CaseRegistry(base_path('tests/Fixtures/cases'))
        );

        config([
            'immersion.default_case' => self::CASE_SLUG,
            'immersion.ai.base_url' => 'https://gateway.test',
            'immersion.ai.api_key' => 'test-key',
            'immersion.ai.grading_enabled' => true,
            'immersion.credits.enabled' => false,
        ]);
    }

    /**
     * @return array{0: Game, 1: \Illuminate\Support\Collection<int, Player>}
     */
    private function gameWithPlayers(int $players = 3): array
    {
        $owner = $this->gameMaster(self::CASE_SLUG);

        $game = Game::create([
            'user_id' => $owner->id,
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'ending_type' => Game::ENDING_CLASSIC,
            'status' => 'draft',
            'interrogation_enabled' => true,
        ]);

        foreach ($game->caseDefinition()->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }

        $roster = collect(range(1, $players))->map(fn (int $index) => $game->players()->create([
            'name' => "Jugador {$index}",
            'email' => "jugador{$index}@example.test",
            'access_token' => "calificado-{$game->id}-{$index}",
        ]));

        return [$game->fresh(), $roster];
    }

    /* ------------------------------------------------------------------
     | Victim label
     |----------------------------------------------------------------- */

    public function test_the_victim_label_comes_from_the_case_and_defaults_to_victima(): void
    {
        $registry = app(CaseRegistry::class);

        $this->assertSame('Desaparecida', $registry->get(self::CASE_SLUG)->victim()['label']);
        $this->assertSame('Víctima', $registry->get('caso-resuelto')->victim()['label']);
    }

    /* ------------------------------------------------------------------
     | Accusable but not interrogable
     |----------------------------------------------------------------- */

    public function test_a_suspect_who_cannot_be_questioned_is_off_the_board_but_still_accusable(): void
    {
        $case = app(CaseRegistry::class)->get(self::CASE_SLUG);

        $this->assertArrayNotHasKey('el-ausente', $case->suspectsForPlayer());
        $this->assertNull($case->suspectForPlayer('el-ausente'));
        $this->assertArrayHasKey('el-ausente', $case->accusableSuspects());

        // La Culpable has 8 across her stages, El Inocente the base 2:
        // nothing is reserved for the one nobody can reach.
        $this->assertSame(10, app(GameCost::class)->maxQuestions($case));
    }

    public function test_questioning_a_suspect_who_cannot_be_reached_is_a_404(): void
    {
        [$game, $roster] = $this->gameWithPlayers(1);
        $game->timelineEvents()->where('cta_interrogation', true)->update(['sent_at' => now()]);

        $token = $roster->first()->access_token;

        $this->get(route('immersion.player.interrogation.show', [$token, 'el-ausente']))->assertNotFound();
        $this->postJson(route('immersion.player.interrogation.ask', [$token, 'el-ausente']), ['question' => 'Hola'])
            ->assertNotFound();

        $this->get(route('immersion.player.interrogation.index', $token))
            ->assertInertia(fn (Assert $page) => $page
                ->has('suspects', 2)
                ->missing('suspects.el-ausente')
                ->where('victim.label', 'Desaparecida'));
    }

    public function test_the_stage_without_a_block_is_the_cases_base_budget(): void
    {
        $case = app(CaseRegistry::class)->get(self::CASE_SLUG);

        $this->assertSame([['questions' => 2, 'after' => [], 'confrontation' => false, 'note' => null]], $case->interrogationStages('el-inocente'));
        $this->assertSame(2, $case->totalQuestionsFor('el-inocente'));
    }

    /* ------------------------------------------------------------------
     | Private clues spread across the table
     |----------------------------------------------------------------- */

    public function test_private_clues_go_to_different_players_before_anyone_gets_a_second(): void
    {
        [$game] = $this->gameWithPlayers(3);

        $clues = $game->timelineEvents()->where('delivery_mode', 'random_player')->orderBy('id')->get();
        $this->assertCount(4, $clues);

        $chosen = $clues->map(function (TimelineEvent $event) {
            $recipient = TimelineRecipients::resolve($event)->sole();
            $event->save();

            return $recipient->id;
        });

        // Three players, three clues: one each.
        $this->assertCount(3, $chosen->take(3)->unique());

        // The fourth repeats someone, but only because everyone has one.
        $this->assertSame([1, 1, 2], $chosen->countBy()->sort()->values()->all());
    }

    /* ------------------------------------------------------------------
     | The accusation form
     |----------------------------------------------------------------- */

    public function test_the_form_shows_what_each_part_is_worth_but_never_the_answers(): void
    {
        [$game, $roster] = $this->gameWithPlayers(1);
        $game->timelineEvents()->where('type', 'unlock')->update(['sent_at' => now()]);

        $this->get(route('immersion.player.accusation', $roster->first()->access_token))
            ->assertInertia(fn (Assert $page) => $page
                ->where('form.weapon.label', '¿Cómo lo hizo?')
                ->where('form.culprit_points', 30)
                ->where('form.weapon.points', 15)
                ->where('form.motive.points', 20)
                ->where('form.max', 75)
                ->where('form.questions.0.key', 'complice')
                ->where('form.questions.0.points', 10)
                ->missing('form.questions.0.answer')
                ->missing('form.questions.0.guidance'));
    }

    public function test_the_basics_are_required_but_the_extra_questions_are_optional(): void
    {
        [$game, $roster] = $this->gameWithPlayers(2);
        $game->timelineEvents()->where('type', 'unlock')->update(['sent_at' => now()]);
        $token = $roster->first()->access_token;

        // The three basics still cannot be skipped.
        $this->post(route('immersion.player.accusation.store', $token), ['suspect_slug' => 'la-culpable'])
            ->assertSessionHasErrors(['weapon', 'motive']);

        // Without the extra question the accusation is accepted, and nothing
        // is stored for it.
        $accusation = ['suspect_slug' => 'la-culpable', 'weapon' => 'Las pastillas', 'motive' => 'El seguro'];

        $this->post(route('immersion.player.accusation.store', $token), $accusation)
            ->assertSessionHasNoErrors();
        $this->assertNull(Accusation::first()->answers);

        // Answered, only the keys the case asks for are kept.
        $this->post(route('immersion.player.accusation.store', $token), $accusation + [
            'answers' => ['complice' => 'Nadie', 'inventada' => 'no se guarda'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['complice' => 'Nadie'], Accusation::first()->fresh()->answers);
    }

    /* ------------------------------------------------------------------
     | Grading and the tie-break
     |----------------------------------------------------------------- */

    /**
     * Two players on the culprit, one on an innocent, graded and ready.
     *
     * @return array{0: Game, 1: array<int, Accusation>}
     */
    private function gradableGame(): array
    {
        [$game, $roster] = $this->gameWithPlayers(3);

        $accusations = $roster->map(fn (Player $player, int $index) => $game->accusations()->create([
            'player_id' => $player->id,
            'suspect_slug' => $index < 2 ? 'la-culpable' : 'el-inocente',
            'suspect_name' => $index < 2 ? 'La Culpable' : 'El Inocente',
            'was_correct' => $index < 2,
            'weapon' => 'Respuesta '.$index,
            'motive' => 'Motivo '.$index,
            'answers' => ['complice' => 'Nadie'],
            'grading_status' => Accusation::GRADING_PENDING,
            'submitted_at' => now(),
        ]))->all();

        return [$game, $accusations];
    }

    private function runGrading(Game $game): void
    {
        (new GradeAccusations($game->id))->handle(
            app(AccusationGrader::class),
            app(AiCredits::class),
            app(GameCost::class)
        );
    }

    public function test_grading_scores_each_answer_and_the_score_breaks_the_tie(): void
    {
        [$game, [$first, $second, $wrong]] = $this->gradableGame();

        Http::fake(['*/grade' => Http::response(['results' => [
            // Asked to give 99 out of 15: clamped to 15.
            ['id' => $first->id, 'scores' => [
                ['key' => 'weapon', 'points' => 5, 'feedback' => 'A medias.'],
                ['key' => 'motive', 'points' => 10, 'feedback' => 'Parcial.'],
                ['key' => 'complice', 'points' => 10, 'feedback' => 'Bien.'],
            ]],
            ['id' => $second->id, 'scores' => [
                ['key' => 'weapon', 'points' => 99, 'feedback' => 'Exacto.'],
                ['key' => 'motive', 'points' => 20, 'feedback' => 'Exacto.'],
                ['key' => 'complice', 'points' => 10, 'feedback' => 'Bien.'],
            ]],
            ['id' => $wrong->id, 'scores' => [
                ['key' => 'weapon', 'points' => 15, 'feedback' => 'Bien.'],
                ['key' => 'motive', 'points' => 20, 'feedback' => 'Bien.'],
                ['key' => 'complice', 'points' => 10, 'feedback' => 'Bien.'],
            ]],
        ]])]);

        $this->runGrading($game);

        Http::assertSent(function ($request) {
            $criteria = collect($request->data()['criteria']);

            // The reference answers travel to the grader; the culprit is never
            // a criterion — it is scored exactly here.
            return $criteria->pluck('key')->all() === ['weapon', 'motive', 'complice']
                && $criteria->firstWhere('key', 'motive')['answer'] === 'Se quedaba con el seguro.';
        });

        $this->assertSame(30 + 5 + 10 + 10, $first->fresh()->score);
        $this->assertSame(30 + 15 + 20 + 10, $second->fresh()->score);
        $this->assertSame(0 + 15 + 20 + 10, $wrong->fresh()->score);
        $this->assertSame(Accusation::GRADING_READY, $first->fresh()->grading_status);
        $this->assertSame('A medias.', collect($first->fresh()->grading)->firstWhere('key', 'weapon')['feedback']);

        // Both named the culprit; the better explanation wins. The perfect
        // answers on the wrong culprit still come last.
        $ranked = app(AccusationScoreboard::class)->ranked($game->fresh());

        $this->assertSame(
            [$second->player_id, $first->player_id, $wrong->player_id],
            array_column($ranked, 'player_id')
        );
        $this->assertSame([1, 2, 3], array_column($ranked, 'rank'));
    }

    public function test_a_failed_grading_still_ranks_the_table_on_the_culprit(): void
    {
        [$game, [$first, , $wrong]] = $this->gradableGame();

        Http::fake(['*/grade' => Http::response(['error' => 'down'], 503)]);

        $this->runGrading($game);

        $this->assertSame(Accusation::GRADING_FAILED, $first->fresh()->grading_status);
        $this->assertSame(30, $first->fresh()->score);
        $this->assertSame(0, $wrong->fresh()->score);
    }

    public function test_revealing_queues_the_grading_once(): void
    {
        [$game] = $this->gradableGame();
        $game->accusations()->update(['grading_status' => null, 'was_correct' => null]);
        $game->timelineEvents()->where('type', 'unlock')->update(['sent_at' => now()]);

        $owner = User::find($game->user_id);

        $this->actingAs($owner)->post(route('immersion.gm.game.reveal', $game));
        $this->actingAs($owner)->post(route('immersion.gm.game.reveal', $game));

        Bus::assertDispatchedTimes(GradeAccusations::class, 1);
        $this->assertSame(3, $game->accusations()->where('grading_status', Accusation::GRADING_PENDING)->count());
    }
}
