<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\AccusationScoreboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The ending: who did it, who got it right, and when the table is allowed to
 * find out.
 *
 * Runs against a fixture case that actually has a written solution — the
 * shipped case still carries the PENDIENTE placeholder on purpose.
 */
class EndingRevealTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const CASE_SLUG = 'caso-resuelto';

    private const CULPRIT = 'la-culpable';

    private const INNOCENT = 'el-inocente';

    /** A phrase that appears only inside the fixture's solucion.md. */
    private const SOLUTION_MARKER = 'MARCADOR-SOLUCION-SECRETA';

    protected function setUp(): void
    {
        parent::setUp();

        // Point the registry at the fixtures so games resolve a case that has
        // an ending to reveal.
        $this->app->singleton(
            CaseRegistry::class,
            fn () => new CaseRegistry(base_path('tests/Fixtures/cases'))
        );

        config(['immersion.default_case' => self::CASE_SLUG]);
    }

    /**
     * A game with the accusation phase already open and $count players.
     *
     * @return array{0: Game, 1: \Illuminate\Support\Collection<int, Player>, 2: User}
     */
    private function openGame(int $players = 2, array $attributes = []): array
    {
        $owner = $this->gameMaster(self::CASE_SLUG);

        $game = Game::create(array_merge([
            'user_id' => $owner->id,
            'name' => 'Partida de prueba',
            'case_slug' => self::CASE_SLUG,
            'status' => 'running',
            'started_at' => now(),
        ], $attributes));

        foreach ($game->caseDefinition()->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }

        // Fire the unlock event so accusations are open.
        $game->timelineEvents()->where('type', 'unlock')->update(['sent_at' => now()]);

        $roster = collect(range(1, $players))->map(fn (int $index) => $game->players()->create([
            'name' => "Jugador {$index}",
            'email' => "jugador{$index}@example.test",
            'access_token' => "token-{$index}",
        ]));

        return [$game->fresh(), $roster, $owner];
    }

    private function accuse(Player $player, string $suspectSlug = self::CULPRIT): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('immersion.player.accusation.store', $player->access_token), [
            'suspect_slug' => $suspectSlug,
            'weapon' => 'Unas pastillas',
            'motive' => 'Por dinero',
        ]);
    }

    /* ------------------------------------------------------------------
     | The gate
     |----------------------------------------------------------------- */

    public function test_the_solution_is_hidden_until_it_is_revealed(): void
    {
        [, $roster] = $this->openGame();
        $token = $roster->first()->access_token;

        $this->get(route('immersion.player.solution', $token))->assertForbidden();

        $this->accuse($roster[0]);
        $this->accuse($roster[1]);

        $this->get(route('immersion.player.solution', $token))->assertOk();
    }

    public function test_the_solution_never_leaks_into_the_player_pages_before_the_reveal(): void
    {
        [$game, $roster] = $this->openGame();
        $game->update(['interrogation_enabled' => true]);
        $token = $roster->first()->access_token;

        foreach (['inbox', 'accusation', 'interrogation.index'] as $page) {
            $response = $this->get(route("immersion.player.{$page}", $token))->assertOk();

            $response->assertDontSee(self::SOLUTION_MARKER);
            $response->assertInertia(fn (Assert $inertia) => $inertia->missing('solution'));
        }
    }

    /* ------------------------------------------------------------------
     | Triggering
     |----------------------------------------------------------------- */

    public function test_the_last_accusation_reveals_the_ending_for_everyone(): void
    {
        [$game, $roster] = $this->openGame(2);

        $this->accuse($roster[0]);
        $this->assertFalse($game->fresh()->endingRevealed());

        $this->accuse($roster[1]);
        $this->assertTrue($game->fresh()->endingRevealed());
        $this->assertSame('auto', $game->fresh()->ending_revealed_by);

        // Both players can read it, not just the one who finished.
        $this->get(route('immersion.player.solution', $roster[0]->access_token))->assertOk();
        $this->get(route('immersion.player.solution', $roster[1]->access_token))->assertOk();
    }

    public function test_a_player_who_never_accuses_does_not_block_the_game_master(): void
    {
        [$game, $roster, $owner] = $this->openGame(3);

        $this->accuse($roster[0]);
        $this->accuse($roster[1]);
        $this->assertFalse($game->fresh()->endingRevealed());

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.reveal', $game))
            ->assertRedirect();

        $this->assertTrue($game->fresh()->endingRevealed());
        $this->assertSame('gm', $game->fresh()->ending_revealed_by);
    }

    public function test_the_owner_player_counts_towards_the_reveal_in_automatic_mode(): void
    {
        [$game, $roster, $owner] = $this->openGame(1, ['mode' => Game::MODE_AUTOMATIC]);

        $ownerPlayer = $game->players()->create([
            'name' => $owner->name,
            'email' => $owner->email,
            'access_token' => 'token-owner',
            'is_owner' => true,
        ]);

        $this->accuse($roster[0]);

        // The invited player has accused, but the owner is playing too.
        $this->assertFalse($game->fresh()->endingRevealed());

        $this->accuse($ownerPlayer);
        $this->assertTrue($game->fresh()->endingRevealed());
    }

    public function test_the_reveal_only_happens_once(): void
    {
        [$game, $roster, $owner] = $this->openGame(1);

        $this->accuse($roster[0]);
        $revealedAt = $game->fresh()->ending_revealed_at;

        // The policy refuses a second reveal outright.
        $this->actingAs($owner)
            ->post(route('immersion.gm.game.reveal', $game))
            ->assertForbidden();

        $this->assertEquals($revealedAt, $game->fresh()->ending_revealed_at);
    }

    public function test_revealing_does_not_finish_the_game(): void
    {
        // The premium ending hands the Game Master an audio to play before
        // they close the case, so the two must stay separate.
        [$game, $roster] = $this->openGame(1);

        $this->accuse($roster[0]);

        $this->assertTrue($game->fresh()->endingRevealed());
        $this->assertSame('running', $game->fresh()->status);
        $this->assertNull($game->fresh()->finished_at);
    }

    /* ------------------------------------------------------------------
     | Locking
     |----------------------------------------------------------------- */

    public function test_accusations_are_locked_once_the_ending_is_revealed(): void
    {
        [, $roster] = $this->openGame(1);

        $this->accuse($roster[0], self::INNOCENT);

        // With the answer on screen, "correcting" an accusation would hand out
        // a perfect score to anyone who asked for one.
        $this->accuse($roster[0], self::CULPRIT)->assertForbidden();

        $this->assertSame(self::INNOCENT, $roster[0]->accusation()->first()->suspect_slug);
    }

    public function test_accusations_are_locked_once_the_case_is_closed(): void
    {
        [$game, $roster] = $this->openGame(2);

        $game->finish();

        $this->accuse($roster[0])->assertForbidden();
        $this->assertSame(0, $game->accusations()->count());
    }

    /* ------------------------------------------------------------------
     | Scoring
     |----------------------------------------------------------------- */

    public function test_only_the_culprit_slug_decides_a_correct_accusation(): void
    {
        [$game, $roster] = $this->openGame(2);

        $this->accuse($roster[0], self::CULPRIT);
        $this->accuse($roster[1], self::INNOCENT);

        $board = app(AccusationScoreboard::class)->for($game->fresh());

        $this->assertTrue($board[0]['correct']);
        $this->assertFalse($board[1]['correct']);
        $this->assertSame(1, app(AccusationScoreboard::class)->correctCount($game->fresh()));
    }

    public function test_an_accusation_without_a_slug_is_not_assessable(): void
    {
        [$game, $roster] = $this->openGame(1);

        // Predates suspect slugs: never scored as a miss.
        $game->accusations()->create([
            'player_id' => $roster[0]->id,
            'suspect_name' => 'Alguien que escribio a mano',
            'weapon' => 'Algo',
            'motive' => 'Por algo',
            'submitted_at' => now(),
        ]);

        $board = app(AccusationScoreboard::class)->for($game->fresh());

        $this->assertNull($board[0]['correct']);
        $this->assertSame(0, app(AccusationScoreboard::class)->correctCount($game->fresh()));
    }

    public function test_verdicts_are_frozen_when_the_ending_is_revealed(): void
    {
        [$game, $roster] = $this->openGame(1);

        $this->accuse($roster[0], self::CULPRIT);

        // Persisted rather than derived: the manifest is read from disk every
        // time, so a later edit must not rescore a finished game.
        $this->assertTrue($roster[0]->accusation()->first()->was_correct);
    }

    public function test_the_accusation_only_accepts_a_suspect_from_this_case(): void
    {
        [, $roster] = $this->openGame(2);

        $this->post(route('immersion.player.accusation.store', $roster[0]->access_token), [
            'suspect_slug' => 'no-existe',
            'weapon' => 'Algo',
            'motive' => 'Por algo',
        ])->assertSessionHasErrors('suspect_slug');

        $this->assertNull($roster[0]->accusation()->first());
    }

    /* ------------------------------------------------------------------
     | Authorisation
     |----------------------------------------------------------------- */

    public function test_a_game_master_cannot_reveal_a_case_with_no_written_solution(): void
    {
        // The shipped case still has the PENDIENTE placeholder.
        $this->app->singleton(CaseRegistry::class, fn () => new CaseRegistry());
        config(['immersion.default_case' => 'steve-jacobs']);

        $owner = $this->gameMaster();
        [$game] = $this->gameOwnedBy($owner);
        $game->timelineEvents()->create([
            'type' => 'unlock',
            'trigger_offset_minutes' => 1,
            'title' => 'Acusaciones',
            'delivery_mode' => 'all',
            'sent_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.reveal', $game))
            ->assertForbidden();
    }

    public function test_another_game_master_cannot_reveal_someone_elses_game(): void
    {
        [$game] = $this->openGame(1);

        $intruder = $this->gameMaster(self::CASE_SLUG, ['email' => 'intruder@example.com']);

        $this->actingAs($intruder)
            ->post(route('immersion.gm.game.reveal', $game))
            ->assertForbidden();
    }

    public function test_a_playing_owner_can_see_the_results_once_the_ending_is_out(): void
    {
        [$game, $roster, $owner] = $this->openGame(1, ['mode' => Game::MODE_AUTOMATIC]);

        // Automatic mode hides the results while the owner is still playing.
        $this->actingAs($owner)
            ->get(route('immersion.gm.game.results', $game))
            ->assertForbidden();

        $this->accuse($roster[0]);

        // Revealing opens them without having to close the case first.
        $this->actingAs($owner)
            ->get(route('immersion.gm.game.results', $game))
            ->assertOk();
    }

    /* ------------------------------------------------------------------
     | Presentation
     |----------------------------------------------------------------- */

    public function test_the_solution_page_shows_the_scoreboard_without_other_players_tokens(): void
    {
        [, $roster] = $this->openGame(2);

        $this->accuse($roster[0], self::CULPRIT);
        $this->accuse($roster[1], self::INNOCENT);

        $response = $this->get(route('immersion.player.solution', $roster[0]->access_token))
            ->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Player/Solution')
            ->where('solution.culprit.name', 'La Culpable')
            ->has('scoreboard', 2)
            ->where('scoreboard.0.is_you', true)
            ->where('scoreboard.0.correct', true)
            ->where('scoreboard.1.correct', false)
            ->where('correctCount', 1)
        );

        // Player 2's link must not travel to player 1.
        $response->assertDontSee('token-2');
    }
}
