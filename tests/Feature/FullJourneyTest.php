<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Walks the product end to end, the way a person actually would: land on the
 * site, browse, register, acquire a case, run a game, play it as an invited
 * player, close it and delete it.
 *
 * The per-feature tests prove each piece in isolation. This one proves the
 * pieces are connected — that the funnel has no dead end and no step needs a
 * URL nobody links to.
 */
class FullJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Http::fake(['*' => Http::response(['reply' => 'No fui yo, se lo juro.'], 200)]);

        config(['platform.simulated_checkout' => true]);

        // The catalog is derived from the installed case manifests, exactly as
        // on a real deploy.
        Artisan::call('platform:sync-cases');
    }

    public function test_a_visitor_can_go_from_the_landing_page_to_running_a_finished_game(): void
    {
        /* ---------------------------------------------------------------
         | 1. Public site
         |-------------------------------------------------------------- */

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/Landing')->has('featured', 1));

        $this->get(route('cases.index'))->assertOk();
        $this->get(route('mechanics'))->assertOk();
        $this->get(route('ai'))->assertOk();
        $this->get(route('pricing'))->assertOk();

        $this->get(route('cases.show', 'steve-jacobs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('owned', false));

        /* ---------------------------------------------------------------
         | 2. Register
         |-------------------------------------------------------------- */

        $this->get(route('register'))->assertOk();

        $this->post(route('register'), [
            'name' => 'Isabella Figueroa',
            'email' => 'gm@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertRedirect(route('dashboard'));

        $gameMaster = User::firstWhere('email', 'gm@example.com');
        $this->assertAuthenticatedAs($gameMaster);

        // A brand new account owns nothing and has nowhere to fall over.
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('library', 0));

        $this->get(route('library'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();

        /* ---------------------------------------------------------------
         | 3. Acquire a case
         |-------------------------------------------------------------- */

        $this->post(route('cases.acquire', 'steve-jacobs'))->assertRedirect(route('dashboard'));

        $this->assertTrue($gameMaster->fresh()->ownsCase('steve-jacobs'));

        $this->get(route('library'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('cases', 1)
                ->where('cases.0.quota.used', 0)
                ->where('cases.0.playable', true)
            );

        /* ---------------------------------------------------------------
         | 4. Create a game
         |-------------------------------------------------------------- */

        $this->get(route('immersion.gm.games.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('library', 1));

        $this->post(route('immersion.gm.games.store'), [
            'name' => 'Mesa del sábado',
            'case_slug' => 'steve-jacobs',
            'mode' => Game::MODE_GM_LED,
            'ending_type' => Game::ENDING_CLASSIC,
            // Chosen up front: it can no longer be switched on mid-run.
            'interrogation_enabled' => true,
            'players' => [
                ['name' => 'Ana', 'email' => 'ana@example.com'],
                ['name' => 'Beto', 'email' => 'beto@example.com'],
            ],
        ])->assertRedirect();

        $game = Game::firstWhere('name', 'Mesa del sábado');
        $this->assertNotNull($game);
        $this->assertSame(8, $game->timelineEvents()->count());

        $this->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('can.direct', true)
                ->where('can.viewSpoilers', true)
                ->has('game.players', 2)
            );

        /* ---------------------------------------------------------------
         | 5. Run it
         |-------------------------------------------------------------- */

        $this->post(route('immersion.gm.game.start', $game))->assertRedirect();
        $this->assertTrue($game->fresh()->isRunning());

        $this->post(route('immersion.gm.game.pause', $game))->assertRedirect();
        $this->assertTrue($game->fresh()->isPaused());

        $this->post(route('immersion.gm.game.resume', $game))->assertRedirect();

        // Force the first envelope out.
        $this->post(route('immersion.gm.game.force-next', $game))->assertRedirect();
        $this->assertSame(1, $game->timelineEvents()->whereNotNull('sent_at')->count());

        // Interrogation came on with the game's settings, not a mid-run toggle.
        $this->assertTrue($game->fresh()->interrogation_enabled);
        $this->post(route('immersion.gm.game.toggle-interrogation', $game))->assertRedirect();
        $this->assertTrue($game->fresh()->interrogation_enabled, 'A started game ignores the toggle.');

        /* ---------------------------------------------------------------
         | 6. Play as an invited player (no account)
         |-------------------------------------------------------------- */

        $ana = Player::firstWhere('email', 'ana@example.com');
        $token = $ana->access_token;

        // A player is a guest: nothing here may require signing in.
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();

        $this->get(route('immersion.player.inbox', $token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Player/Inbox')->has('items', 1));

        $this->get(route('immersion.player.interrogation.index', $token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('suspects', 9));

        $this->get(route('immersion.player.interrogation.show', [$token, 'elizabeth-foster']))
            ->assertOk();

        $this->postJson(
            route('immersion.player.interrogation.ask', [$token, 'elizabeth-foster']),
            ['question' => '¿Dónde estaba usted esa noche?']
        )->assertOk()->assertJson(['questions_used' => 1]);

        // Accusations are still locked: no unlock event has fired.
        $this->get(route('immersion.player.accusation', $token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('unlocked', false));

        $this->post(route('immersion.player.accusation.store', $token), [
            'suspect_slug' => 'daniel-blake',
            'weapon' => 'Algo',
            'motive' => 'Por algo',
        ])->assertForbidden();

        /* ---------------------------------------------------------------
         | 7. Unlock the accusation phase and submit one
         |-------------------------------------------------------------- */

        $this->actingAs($gameMaster);

        // Push the timeline to the unlock event.
        while ($game->fresh()->timelineEvents()->pending()->exists()) {
            $this->post(route('immersion.gm.game.force-next', $game));
        }

        $this->assertTrue($game->fresh()->accusationsUnlocked());

        $this->post(route('logout'));

        $this->get(route('immersion.player.accusation', $token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('unlocked', true));

        $this->post(route('immersion.player.accusation.store', $token), [
            'suspect_slug' => 'daniel-blake',
            'weapon' => 'Suplementos manipulados',
            'motive' => 'Desacuerdos laborales',
        ])->assertRedirect(route('immersion.player.accusation', $token));

        // The name is denormalised from the manifest, so the row stays
        // readable without re-resolving the case.
        $this->assertDatabaseHas('immersion_accusations', [
            'suspect_slug' => 'daniel-blake',
            'suspect_name' => 'Daniel Blake',
        ]);

        // One of two players has accused, so the ending stays shut. 403, not
        // 404: the case does have a written solution, it is just not yours yet.
        $this->assertFalse($game->fresh()->endingRevealed());
        $this->get(route('immersion.player.solution', $token))->assertForbidden();

        /* ---------------------------------------------------------------
         | 8. The last accusation ends the game by itself
         |-------------------------------------------------------------- */

        $betoToken = Player::firstWhere('email', 'beto@example.com')->access_token;

        $this->post(route('immersion.player.accusation.store', $betoToken), [
            'suspect_slug' => 'rachel-miller',
            'weapon' => 'Cápsulas con digitoxina',
            'motive' => 'Justicia por su cuenta',
        ])->assertRedirect(route('immersion.player.solution', $betoToken));

        $game = $game->fresh();
        $this->assertTrue($game->endingRevealed());
        $this->assertSame('auto', $game->ending_revealed_by);

        // Revealing is not finishing: the game is still running.
        $this->assertTrue($game->isRunning());

        // Both players get in, and the verdicts are frozen onto the rows.
        $this->get(route('immersion.player.solution', $token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/Solution')
                ->where('solution.culprit.name', 'Rachel Miller')
                ->where('correctCount', 1)
                ->has('scoreboard', 2)
            );

        $this->get(route('immersion.player.solution', $betoToken))->assertOk();

        $this->assertDatabaseHas('immersion_accusations', [
            'suspect_slug' => 'rachel-miller',
            'was_correct' => true,
        ]);
        $this->assertDatabaseHas('immersion_accusations', [
            'suspect_slug' => 'daniel-blake',
            'was_correct' => false,
        ]);

        // With the answer on screen, nobody gets to change their mind.
        $this->post(route('immersion.player.accusation.store', $token), [
            'suspect_slug' => 'rachel-miller',
            'weapon' => 'Ahora sí',
            'motive' => 'Ahora sí',
        ])->assertForbidden();

        /* ---------------------------------------------------------------
         | 9. Close the case and review it
         |-------------------------------------------------------------- */

        $this->actingAs($gameMaster);

        $this->get(route('immersion.gm.game.interrogations', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('sessions', 1));

        $this->get(route('immersion.gm.game.results', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('game.players', 2)
                ->where('scoreboard.0.correct', false)
                ->where('scoreboard.1.correct', true)
                ->where('solution.culprit_name', 'Rachel Miller')
            );

        $this->post(route('immersion.gm.game.finish', $game))->assertRedirect();
        $this->assertTrue($game->fresh()->isFinished());

        /* ---------------------------------------------------------------
         | 10. Delete it, which frees the slot
         |-------------------------------------------------------------- */

        $this->delete(route('immersion.gm.game.destroy', $game))
            ->assertRedirect(route('immersion.gm.games.index'));

        $this->assertDatabaseCount('immersion_games', 0);

        // The player's link dies with the game.
        $this->get(route('immersion.player.inbox', $token))->assertNotFound();

        $this->get(route('immersion.gm.games.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('games', 0)->where('canCreate', true));
    }

    public function test_an_automatic_game_lets_the_owner_play_without_spoiling_it(): void
    {
        $gameMaster = User::factory()->create();
        $this->actingAs($gameMaster);

        app(\App\Modules\Platform\Actions\GrantCaseAccess::class)->grant(
            $gameMaster,
            \App\Modules\Platform\Models\MysteryCase::firstWhere('slug', 'steve-jacobs')
        );

        $this->post(route('immersion.gm.games.store'), [
            'name' => 'Jugamos todos',
            'case_slug' => 'steve-jacobs',
            'mode' => Game::MODE_AUTOMATIC,
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ])->assertRedirect();

        $game = Game::firstWhere('name', 'Jugamos todos');

        // The owner has an inbox of their own and reaches it from the console.
        $response = $this->get(route('immersion.gm.game.show', $game))->assertOk();
        $ownerToken = $response->viewData('page')['props']['ownerPlayerToken'];

        $this->assertNotNull($ownerToken);
        $this->get(route('immersion.player.inbox', $ownerToken))->assertOk();

        // And the console withholds everything that would give the case away.
        $this->get(route('immersion.gm.game.results', $game))->assertForbidden();
        $this->get(route('immersion.gm.game.interrogations', $game))->assertForbidden();

        $this->post(route('immersion.gm.game.start', $game))->assertRedirect();
        $this->post(route('immersion.gm.game.finish', $game))->assertRedirect();

        // Once the case is closed, the owner may finally look.
        $this->get(route('immersion.gm.game.results', $game))->assertOk();
    }

    public function test_the_administrator_can_see_the_platform_after_all_of_it(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->forceFill(['is_admin' => true])->save();

        Artisan::call('platform:make-admin', ['email' => 'admin@example.com']);

        $this->actingAs($admin->fresh())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('metrics.catalog.cases_published', 1)
            );
    }
}
