<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Models\CreditHold;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\AiCredits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The AI credit economy: reserve on start, spend as you go, release on close.
 *
 * The rule these tests exist to protect is that a game can never consume
 * capacity it did not freeze. Everything else — the numbers, the packages, the
 * copy — is configuration; this is the part that must not drift.
 */
class AiCreditsTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    /** steve-jacobs: 9 interrogable people x 5 questions each. */
    private const MAX_QUESTIONS = 45;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        config([
            'immersion.credits.enabled' => true,
            'immersion.credits.costs.question' => 1,
            'immersion.credits.costs.ending.classic' => 0,
            'immersion.credits.costs.ending.confession_audio' => 25,
            'immersion.credits.costs.ending.epilogue' => 40,

            // Granted explicitly per test, so what a case includes does not
            // silently rewrite every expectation below.
            'immersion.credits.included_with_case' => false,
        ]);
    }

    private function credits(): AiCredits
    {
        return app(AiCredits::class);
    }

    /**
     * A Game Master with a balance and a game ready to start.
     *
     * @return array{0: User, 1: Game}
     */
    private function readyToStart(int $balance, array $attributes = []): array
    {
        $owner = $this->gameMaster();

        $game = Game::create(array_merge([
            'user_id' => $owner->id,
            'name' => 'Mesa de prueba',
            'status' => 'draft',
            'interrogation_enabled' => true,
        ], $attributes));

        $this->credits()->grant($owner, $balance);

        return [$owner, $game];
    }

    /* ------------------------------------------------------------------
     | Reserving
     |----------------------------------------------------------------- */

    public function test_starting_a_game_freezes_its_whole_ceiling(): void
    {
        [$owner, $game] = $this->readyToStart(100);

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.start', $game))
            ->assertRedirect();

        $this->assertTrue($game->fresh()->isRunning());

        $wallet = $this->credits()->walletFor($owner)->fresh();

        // The whole question budget of the case, whatever the table ends up
        // asking.
        $this->assertSame(self::MAX_QUESTIONS, $wallet->reserved);
        $this->assertSame(100 - self::MAX_QUESTIONS, $wallet->available());
        $this->assertSame(100, $wallet->total(), 'Reserving must not destroy credits.');

        $hold = $this->credits()->holdFor($game);
        $this->assertNotNull($hold);
        $this->assertSame(self::MAX_QUESTIONS, $hold->amount);
        $this->assertSame(0, $hold->spent);
    }

    public function test_the_ending_is_reserved_on_top_of_the_interrogation(): void
    {
        [$owner, $game] = $this->readyToStart(100, [
            'ending_type' => Game::ENDING_CONFESSION_AUDIO,
        ]);

        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $this->assertSame(
            self::MAX_QUESTIONS + 25,
            $this->credits()->holdFor($game)->amount
        );
    }

    public function test_a_game_that_cannot_afford_its_ceiling_does_not_start(): void
    {
        [$owner, $game] = $this->readyToStart(self::MAX_QUESTIONS - 1);

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.start', $game))
            ->assertSessionHasErrors('credits');

        // The clock must not be running: a started case cannot be taken back
        // from a table that already got its first envelope.
        $game = $game->fresh();
        $this->assertSame('draft', $game->status);
        $this->assertNull($game->started_at);

        $this->assertNull($this->credits()->holdFor($game));
        $this->assertSame(self::MAX_QUESTIONS - 1, $this->credits()->walletFor($owner)->fresh()->available());
    }

    public function test_a_game_with_no_ai_starts_on_an_empty_wallet(): void
    {
        [$owner, $game] = $this->readyToStart(0, [
            'interrogation_enabled' => false,
            'ending_type' => Game::ENDING_CLASSIC,
        ]);

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.start', $game))
            ->assertSessionHasNoErrors();

        $this->assertTrue($game->fresh()->isRunning());

        // Nothing was owed, so nothing was frozen and no hold exists to leak.
        $this->assertNull($this->credits()->holdFor($game));
    }

    public function test_starting_twice_reserves_once(): void
    {
        [$owner, $game] = $this->readyToStart(100);

        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $this->assertSame(1, CreditHold::where('game_id', $game->id)->count());
        $this->assertSame(self::MAX_QUESTIONS, $this->credits()->walletFor($owner)->fresh()->reserved);
    }

    /* ------------------------------------------------------------------
     | Spending
     |----------------------------------------------------------------- */

    public function test_a_question_draws_from_the_games_reservation(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'access_token' => 'token-ana',
        ]);

        $this->post(route('logout'));

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'rachel-miller']),
            ['question' => '¿Donde estaba esa noche?']
        )->assertOk();

        $this->assertSame(1, $this->credits()->holdFor($game)->spent);

        $wallet = $this->credits()->walletFor($owner)->fresh();

        // The credit came out of the frozen pile, not the available one.
        $this->assertSame(self::MAX_QUESTIONS - 1, $wallet->reserved);
        $this->assertSame(100 - self::MAX_QUESTIONS, $wallet->available());
        $this->assertSame(99, $wallet->total());
    }

    public function test_a_released_hold_stops_the_interrogation_without_costing_a_question(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'access_token' => 'token-ana',
        ]);

        // As the stale-hold sweeper would leave it.
        $this->credits()->release($game);

        $this->post(route('logout'));

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'rachel-miller']),
            ['question' => '¿Donde estaba esa noche?']
        )->assertStatus(402)->assertJson(['out_of_credits' => true]);

        // The question never happened, so it must not count against the
        // player's five.
        $session = InterrogationSession::where('game_id', $game->id)->first();
        $this->assertSame(0, $session->questions_used);
    }

    public function test_a_game_that_never_had_a_hold_runs_free(): void
    {
        // Games already in progress when credits shipped have no reservation
        // and must stay playable to the end.
        $owner = $this->gameMaster();

        $game = Game::create([
            'user_id' => $owner->id,
            'name' => 'Partida vieja',
            'status' => 'running',
            'started_at' => now(),
            'interrogation_enabled' => true,
        ]);

        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'access_token' => 'token-vieja',
        ]);

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'rachel-miller']),
            ['question' => '¿Donde estaba esa noche?']
        )->assertOk();
    }

    /* ------------------------------------------------------------------
     | Releasing
     |----------------------------------------------------------------- */

    public function test_closing_the_case_gives_back_what_was_not_used(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $this->credits()->spend($game->fresh(), 5);

        $this->actingAs($owner)->post(route('immersion.gm.game.finish', $game));

        $wallet = $this->credits()->walletFor($owner)->fresh();

        $this->assertSame(0, $wallet->reserved);
        $this->assertSame(95, $wallet->available(), 'Only the five spent credits are gone.');
        $this->assertTrue($this->credits()->holdFor($game)->isReleased());
    }

    public function test_closing_a_case_twice_does_not_mint_credits(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $this->actingAs($owner)->post(route('immersion.gm.game.finish', $game));
        $this->actingAs($owner)->post(route('immersion.gm.game.finish', $game));

        $this->assertSame(100, $this->credits()->walletFor($owner)->fresh()->total());
    }

    public function test_deleting_a_game_returns_its_reservation(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        // The hold row is cascaded away with the game, so releasing has to
        // happen first or the credits are frozen with nothing left to unfreeze.
        $this->actingAs($owner)->delete(route('immersion.gm.game.destroy', $game));

        $wallet = $this->credits()->walletFor($owner)->fresh();

        $this->assertSame(0, $wallet->reserved);
        $this->assertSame(100, $wallet->available());
    }

    public function test_the_sweeper_releases_holds_from_abandoned_games(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        CreditHold::where('game_id', $game->id)->update([
            'updated_at' => now()->subHours(72),
        ]);

        Artisan::call('immersion:release-stale-holds', ['--hours' => 48]);

        $this->assertSame(100, $this->credits()->walletFor($owner)->fresh()->available());

        // Idempotent: a second sweep finds nothing left to give back.
        Artisan::call('immersion:release-stale-holds', ['--hours' => 48]);
        $this->assertSame(100, $this->credits()->walletFor($owner)->fresh()->total());
    }

    public function test_the_sweeper_leaves_active_games_alone(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        Artisan::call('immersion:release-stale-holds', ['--hours' => 48]);

        $this->assertSame(self::MAX_QUESTIONS, $this->credits()->walletFor($owner)->fresh()->reserved);
    }

    /* ------------------------------------------------------------------
     | Playing a case across two sessions
     |----------------------------------------------------------------- */

    public function test_pausing_returns_the_capacity_and_resuming_freezes_what_is_left(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        // Saturday: the table asks twelve questions and stops.
        $this->credits()->spend($game->fresh(), 12);

        $this->actingAs($owner)->post(route('immersion.gm.game.pause', $game));

        $wallet = $this->credits()->walletFor($owner)->fresh();
        $this->assertSame(0, $wallet->reserved, 'A paused game freezes nothing.');
        $this->assertSame(88, $wallet->available());

        // Next weekend.
        $this->actingAs($owner)
            ->post(route('immersion.gm.game.resume', $game))
            ->assertSessionHasNoErrors();

        $this->assertTrue($game->fresh()->isRunning());

        $wallet = $this->credits()->walletFor($owner)->fresh();

        // Only the 33 questions still available are frozen again — the twelve
        // already asked are not charged a second time.
        $this->assertSame(self::MAX_QUESTIONS - 12, $wallet->reserved);
        $this->assertSame(88 - (self::MAX_QUESTIONS - 12), $wallet->available());
        $this->assertSame(88, $wallet->total());
    }

    public function test_a_resumed_game_can_interrogate_again(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'access_token' => 'token-ana',
        ]);

        $this->actingAs($owner)->post(route('immersion.gm.game.pause', $game));
        $this->actingAs($owner)->post(route('immersion.gm.game.resume', $game));

        $this->post(route('logout'));

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'rachel-miller']),
            ['question' => '¿Donde estaba esa noche?']
        )->assertOk();
    }

    public function test_a_resume_that_cannot_be_paid_for_leaves_the_game_paused(): void
    {
        [$owner, $game] = $this->readyToStart(self::MAX_QUESTIONS);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));
        $this->actingAs($owner)->post(route('immersion.gm.game.pause', $game));

        // The returned credits went into another table in the meantime.
        $this->credits()->grant($owner, 0);
        $second = Game::create([
            'user_id' => $owner->id,
            'name' => 'Otra mesa',
            'status' => 'draft',
            'interrogation_enabled' => true,
        ]);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $second));

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.resume', $game))
            ->assertSessionHasErrors('credits');

        // Still paused, and still saying why — better than a running case whose
        // suspects silently refuse to answer.
        $this->assertTrue($game->fresh()->isPaused());
    }

    public function test_a_swept_game_can_be_re_armed_from_its_console(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));
        $this->credits()->spend($game->fresh(), 12);

        // Left running for over a week, then swept.
        CreditHold::where('game_id', $game->id)->update(['updated_at' => now()->subDays(8)]);
        Artisan::call('immersion:release-stale-holds');

        $this->assertSame(88, $this->credits()->walletFor($owner)->fresh()->available());

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.rearm-credits', $game))
            ->assertSessionHasNoErrors();

        $wallet = $this->credits()->walletFor($owner)->fresh();
        $this->assertSame(self::MAX_QUESTIONS - 12, $wallet->reserved);
        $this->assertSame(88, $wallet->total(), 'Re-arming must not charge for the twelve already asked.');
    }

    public function test_re_arming_an_already_armed_game_changes_nothing(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $this->actingAs($owner)->post(route('immersion.gm.game.rearm-credits', $game));

        $wallet = $this->credits()->walletFor($owner)->fresh();
        $this->assertSame(self::MAX_QUESTIONS, $wallet->reserved);
        $this->assertSame(100, $wallet->total());
    }

    public function test_a_weekend_pause_survives_the_stale_sweeper(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));
        $this->actingAs($owner)->post(route('immersion.gm.game.pause', $game));

        // A week goes by. The sweeper has nothing to do: the hold is already
        // released, so it must not double-refund.
        Artisan::call('immersion:release-stale-holds');

        $this->assertSame(100, $this->credits()->walletFor($owner)->fresh()->total());

        $this->actingAs($owner)->post(route('immersion.gm.game.resume', $game));
        $this->assertSame(self::MAX_QUESTIONS, $this->credits()->walletFor($owner)->fresh()->reserved);
    }

    /* ------------------------------------------------------------------
     | The ledger
     |----------------------------------------------------------------- */

    public function test_every_movement_lands_in_the_ledger(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));
        $this->credits()->spend($game->fresh(), 3);
        $this->actingAs($owner)->post(route('immersion.gm.game.finish', $game));

        $reasons = CreditLedgerEntry::where('user_id', $owner->id)
            ->orderBy('id')
            ->pluck('reason')
            ->all();

        $this->assertSame(['grant', 'reserve', 'spend', 'release'], $reasons);

        // Freezing and unfreezing move nothing in or out of the account; only
        // spending does.
        $this->assertSame(
            -3,
            (int) CreditLedgerEntry::where('user_id', $owner->id)->sum('delta') - 100
        );
    }

    /* ------------------------------------------------------------------
     | Getting credits
     |----------------------------------------------------------------- */

    public function test_a_case_arrives_sized_for_one_full_game_with_either_ending(): void
    {
        config([
            'immersion.credits.included_with_case' => true,
            'immersion.credits.included_games' => 1,
        ]);

        $owner = $this->gameMaster();

        // steve-jacobs: 45 questions + the priciest ending it can deliver
        // (the epilogue, 40) = 85. Derived from the case, so a bigger roster
        // arrives with more without anyone changing a number.
        $this->assertSame(85, $this->credits()->walletFor($owner)->available());
    }

    /**
     * The point of quoting the PRICIEST ending: the buyer's choice has to be
     * real. Included credits sized for the confession would leave someone who
     * wanted the epilogue short on their very first table.
     */
    public function test_the_included_credits_cover_whichever_ending_the_buyer_picks(): void
    {
        config(['immersion.credits.included_with_case' => true]);

        foreach ([Game::ENDING_CONFESSION_AUDIO, Game::ENDING_EPILOGUE] as $index => $ending) {
            $owner = $this->gameMaster(null, ['email' => "elige{$index}@example.test"]);

            $game = Game::create([
                'user_id' => $owner->id,
                'name' => "Mesa {$index}",
                'status' => 'draft',
                'interrogation_enabled' => true,
                'ending_type' => $ending,
            ]);

            $this->actingAs($owner)
                ->post(route('immersion.gm.game.start', $game))
                ->assertSessionHasNoErrors();
        }
    }

    public function test_a_second_full_game_is_not_included(): void
    {
        config(['immersion.credits.included_with_case' => true]);

        $owner = $this->gameMaster();

        foreach ([1, 2] as $index) {
            $game = Game::create([
                'user_id' => $owner->id,
                'name' => "Mesa {$index}",
                'status' => 'draft',
                'interrogation_enabled' => true,
                'ending_type' => Game::ENDING_EPILOGUE,
            ]);

            $response = $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

            $index === 1
                ? $response->assertSessionHasNoErrors()
                : $response->assertSessionHasErrors('credits');
        }
    }

    /**
     * What keeps a case worth owning once the included credits are gone: the
     * classic experience costs nothing and can be replayed forever.
     */
    public function test_the_classic_experience_stays_free_after_the_credits_run_out(): void
    {
        config(['immersion.credits.included_with_case' => true]);

        $owner = $this->gameMaster();
        $this->credits()->reserve(Game::create([
            'user_id' => $owner->id,
            'name' => 'Gasta todo',
            'status' => 'draft',
            'interrogation_enabled' => true,
            'ending_type' => Game::ENDING_EPILOGUE,
        ]));

        $this->assertSame(0, $this->credits()->walletFor($owner)->fresh()->available());

        $classic = Game::create([
            'user_id' => $owner->id,
            'name' => 'A la antigua',
            'status' => 'draft',
            'interrogation_enabled' => false,
            'ending_type' => Game::ENDING_CLASSIC,
        ]);

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.start', $classic))
            ->assertSessionHasNoErrors();

        $this->assertTrue($classic->fresh()->isRunning());
    }

    public function test_re_granting_a_case_does_not_mint_more_credits(): void
    {
        config(['immersion.credits.included_with_case' => true]);

        $owner = $this->gameMaster();
        $included = $this->credits()->walletFor($owner)->available();

        // A replayed purchase webhook would otherwise be free money.
        $this->grantCase($owner, (string) config('immersion.default_case'));

        $this->assertSame($included, $this->credits()->walletFor($owner)->fresh()->available());
    }

    public function test_the_simulated_top_up_adds_the_packages_credits(): void
    {
        config(['platform.simulated_checkout' => true]);

        $owner = $this->gameMaster();
        $package = config('platform.credit_packages')[0];

        $this->actingAs($owner)
            ->post(route('credits.purchase'), ['package' => $package['id']])
            ->assertRedirect();

        $this->assertSame(
            (int) $package['credits'],
            $this->credits()->walletFor($owner)->fresh()->available()
        );
    }

    public function test_the_top_up_rejects_an_unknown_package(): void
    {
        config(['platform.simulated_checkout' => true]);

        $this->actingAs($this->gameMaster())
            ->post(route('credits.purchase'), ['package' => 'no-existe'])
            ->assertSessionHasErrors('package');
    }

    public function test_the_top_up_is_unreachable_when_the_simulation_is_off(): void
    {
        config(['platform.simulated_checkout' => false]);

        $this->actingAs($this->gameMaster())
            ->post(route('credits.purchase'), ['package' => 'starter'])
            ->assertNotFound();
    }

    /* ------------------------------------------------------------------
     | Switched off
     |----------------------------------------------------------------- */

    public function test_with_credits_disabled_nothing_is_reserved_or_charged(): void
    {
        config(['immersion.credits.enabled' => false]);

        [$owner, $game] = $this->readyToStart(0);

        $this->actingAs($owner)
            ->post(route('immersion.gm.game.start', $game))
            ->assertSessionHasNoErrors();

        $this->assertTrue($game->fresh()->isRunning());
        $this->assertNull($this->credits()->holdFor($game));

        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'access_token' => 'token-libre',
        ]);

        $this->post(route('logout'));

        $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'rachel-miller']),
            ['question' => '¿Donde estaba?']
        )->assertOk();
    }

    /* ------------------------------------------------------------------
     | The wallet page
     |----------------------------------------------------------------- */

    public function test_the_credits_page_reports_where_the_frozen_credits_are(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $this->actingAs($owner)
            ->get(route('credits'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Credits')
                ->where('wallet.available', 100 - self::MAX_QUESTIONS)
                ->where('wallet.reserved', self::MAX_QUESTIONS)
                ->has('holds', 1)
                ->where('holds.0.name', 'Mesa de prueba')
            );
    }

    public function test_the_wallet_is_private_to_its_owner(): void
    {
        $this->get(route('credits'))->assertRedirect(route('login'));
    }

    /* ------------------------------------------------------------------
     | Overdrawing
     |----------------------------------------------------------------- */

    public function test_a_hold_cannot_be_overdrawn(): void
    {
        [$owner, $game] = $this->readyToStart(100);
        $this->actingAs($owner)->post(route('immersion.gm.game.start', $game));

        $game = $game->fresh();

        $this->assertTrue($this->credits()->spend($game, self::MAX_QUESTIONS));
        $this->assertFalse(
            $this->credits()->spend($game, 1),
            'A spent-out hold must refuse, not go negative.'
        );

        $this->assertSame(0, $this->credits()->walletFor($owner)->fresh()->reserved);
    }
}
