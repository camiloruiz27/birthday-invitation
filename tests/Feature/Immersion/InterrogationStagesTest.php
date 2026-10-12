<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Ai\Contracts\InterrogationProvider;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Interrogation questions that open in stages as the case delivers evidence.
 *
 * Against the caso-calificado fixture, where La Culpable has 3 questions from
 * the start, 2 more once V9 is delivered and a confrontation round of 3 once
 * V10 is: 8 in all, reserved up front.
 */
class InterrogationStagesTest extends TestCase
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
            'immersion.credits.enabled' => false,
        ]);

        // The questions are not what is under test: a fixed answer keeps the
        // gateway out of it.
        $this->app->bind(InterrogationProvider::class, fn () => new class implements InterrogationProvider
        {
            public function ask(InterrogationSession $session, string $question): string
            {
                return 'Una respuesta.';
            }
        });
    }

    /** @return array{0: Game, 1: \Illuminate\Support\Collection<int, Player>} */
    private function openGame(int $players = 2): array
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

        // The interrogation opens with the first event, as it would live.
        $game->timelineEvents()->where('cta_interrogation', true)->update(['sent_at' => now()]);

        $roster = collect(range(1, $players))->map(fn (int $i) => $game->players()->create([
            'name' => "Jugador {$i}",
            'email' => "j{$i}@example.test",
            'access_token' => "etapas-{$game->id}-{$i}",
        ]));

        return [$game->fresh(), $roster];
    }

    /** Delivers the timeline event that carries the given title. */
    private function deliver(Game $game, string $title): void
    {
        $game->timelineEvents()->where('title', $title)->update(['sent_at' => now()]);
    }

    private function ask(Player $player, string $slug = 'la-culpable')
    {
        return $this->postJson(route('immersion.player.interrogation.ask', [$player->access_token, $slug]), [
            'question' => '¿Dónde estaba?',
        ]);
    }

    public function test_questions_open_with_the_evidence_that_unlocks_them(): void
    {
        $case = app(CaseRegistry::class)->get(self::CASE_SLUG);

        $this->assertSame(8, $case->totalQuestionsFor('la-culpable'));
        $this->assertSame(3, $case->availableQuestionsFor('la-culpable', []));
        $this->assertSame(5, $case->availableQuestionsFor('la-culpable', ['V9']));
        $this->assertSame(8, $case->availableQuestionsFor('la-culpable', ['V9', 'V10']));

        // Each stage waits on its own codes, so V10 alone opens its round.
        $this->assertSame(6, $case->availableQuestionsFor('la-culpable', ['V10']));
    }

    public function test_spending_a_stage_leaves_the_interrogation_waiting_not_closed(): void
    {
        [$game, $roster] = $this->openGame();
        $player = $roster->first();

        foreach (range(1, 3) as $_) {
            $this->ask($player)->assertOk();
        }

        // The fourth is refused as "waiting", and costs nothing.
        $this->ask($player)
            ->assertStatus(422)
            ->assertJson(['waiting' => true, 'questions_remaining' => 5, 'later' => 5]);

        $session = InterrogationSession::first();
        $this->assertSame(3, $session->questions_used);
        $this->assertNull($session->closed_at);
        $this->assertSame(8, $session->max_questions);
    }

    public function test_the_owner_comes_back_when_the_next_stage_opens(): void
    {
        [$game, $roster] = $this->openGame();
        [$owner, $other] = [$roster[0], $roster[1]];

        foreach (range(1, 3) as $_) {
            $this->ask($owner)->assertOk();
        }

        $this->deliver($game, 'Pista A'); // V9
        $this->ask($owner)->assertOk()->assertJson(['waiting' => false]);
        $this->ask($owner)->assertOk()->assertJson(['waiting' => true, 'later' => 3]);

        // Still the first player's interrogation: nobody else can take over.
        $this->ask($other)->assertStatus(403)->assertJson(['locked' => true]);

        $this->deliver($game, 'Pista D'); // V10: the confrontation round
        foreach (range(1, 3) as $index) {
            $this->ask($owner)->assertOk()->assertJson(['closed' => $index === 3]);
        }

        $this->assertNotNull(InterrogationSession::first()->closed_at);
        $this->ask($owner)->assertStatus(422)->assertJson(['closed' => true]);
    }

    public function test_the_board_and_the_chat_say_what_is_open_and_what_is_to_come(): void
    {
        [$game, $roster] = $this->openGame(1);
        $token = $roster->first()->access_token;

        $this->get(route('immersion.player.interrogation.index', $token))
            ->assertInertia(fn (Assert $page) => $page
                ->where('availability.la-culpable.open', 3)
                ->where('availability.la-culpable.later', 5)
                ->where('availability.la-culpable.total', 8)
                ->where('availability.la-culpable.confrontation_later', true)
                ->where('availability.el-inocente.later', 0));

        foreach (range(1, 3) as $_) {
            $this->ask($roster->first())->assertOk();
        }

        // Waiting: the table gets the ficha for what it already learned.
        $this->get(route('immersion.player.interrogation.show', [$token, 'la-culpable']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('availability.open', 3)
                ->where('session.questions_used', 3)
                ->whereNot('ficha', null));
    }

    public function test_the_event_that_opens_questions_says_so(): void
    {
        $case = app(CaseRegistry::class)->get(self::CASE_SLUG);

        // The very first event opens nothing.
        $this->assertNull($case->questionsUnlockedNotice(1));

        $withV9 = $case->renderEventBody(null, 'Pista', 2);
        $this->assertStringContainsString('Nuevas preguntas en el interrogatorio', $withV9);
        $this->assertStringContainsString('La Culpable', $withV9);
        $this->assertStringContainsString('2 preguntas más', $withV9);
        $this->assertStringContainsString('sigue siendo tu interrogatorio', $withV9);

        $withV10 = $case->renderEventBody(null, 'Pista', 3);
        $this->assertStringContainsString('para confrontarla con lo nuevo', $withV10);

        // Ordinary events are left alone.
        $this->assertStringNotContainsString('Nuevas preguntas', $case->renderEventBody(null, 'Expediente', 1));
    }
}
