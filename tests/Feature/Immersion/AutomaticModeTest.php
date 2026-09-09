<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * In automatic mode the owner plays too, so the console must stop handing them
 * the answers. Both modes run on the same engine — what changes is who is
 * allowed to see and touch what.
 */
class AutomaticModeTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function createGame(User $user, string $mode): Game
    {
        $this->actingAs($user)->post(route('immersion.gm.games.store'), [
            'name' => 'Partida '.$mode,
            'mode' => $mode,
            'players' => [
                ['name' => 'Ana', 'email' => 'ana@example.com'],
                ['name' => 'Beto', 'email' => 'beto@example.com'],
            ],
        ])->assertRedirect();

        return Game::firstWhere('name', 'Partida '.$mode);
    }

    public function test_games_are_game_master_led_unless_asked_otherwise(): void
    {
        $game = $this->createGame($this->gameMaster(), Game::MODE_GM_LED);

        $this->assertFalse($game->isAutomatic());
        $this->assertNull($game->ownerPlayer());
        $this->assertSame(2, $game->players()->count());
    }

    public function test_an_automatic_game_gives_the_owner_a_player_of_their_own(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_AUTOMATIC);

        $this->assertTrue($game->isAutomatic());
        $this->assertSame(3, $game->players()->count());

        $ownerPlayer = $game->ownerPlayer();
        $this->assertNotNull($ownerPlayer);
        $this->assertSame($user->name, $ownerPlayer->name);
        $this->assertSame($user->email, $ownerPlayer->email);

        // The owner's inbox works like anyone else's.
        $this->get(route('immersion.player.inbox', $ownerPlayer->access_token))->assertOk();
    }

    public function test_a_playing_owner_cannot_read_interrogations_or_accusations(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_AUTOMATIC);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.interrogations', $game))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('immersion.gm.game.results', $game))
            ->assertForbidden();
    }

    public function test_a_playing_owner_cannot_reach_into_the_timeline(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_AUTOMATIC);
        $game->update(['status' => 'running', 'started_at' => now()]);

        // Forcing an event or unlocking a mechanic by hand would be rewriting
        // their own game.
        $this->actingAs($user)
            ->post(route('immersion.gm.game.force-next', $game))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('immersion.gm.game.toggle-interrogation', $game))
            ->assertForbidden();
    }

    public function test_a_directing_game_master_keeps_every_control(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_GM_LED);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.interrogations', $game))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('immersion.gm.game.toggle-interrogation', $game))
            ->assertRedirect();

        $this->assertTrue($game->fresh()->interrogation_enabled);
    }

    public function test_the_console_withholds_the_timeline_from_a_playing_owner(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_AUTOMATIC);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/Game')
                ->where('can.viewSpoilers', false)
                ->where('can.direct', false)
                // Counts, not contents: event titles are spoilers themselves.
                ->where('timelineSummary.total', 8)
                ->where('timelineSummary.sent', 0)
                ->missing('game.timeline_events')
                ->has('ownerPlayerToken')
            );
    }

    public function test_the_console_shows_the_timeline_to_a_directing_game_master(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_GM_LED);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('can.viewSpoilers', true)
                ->where('can.direct', true)
                ->has('game.timeline_events', 8)
                ->where('ownerPlayerToken', null)
            );
    }

    public function test_finishing_the_case_reveals_the_spoilers_to_the_owner(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_AUTOMATIC);
        $game->update(['status' => 'running', 'started_at' => now()]);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.results', $game))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('immersion.gm.game.finish', $game))
            ->assertRedirect();

        $this->assertTrue($game->fresh()->isFinished());

        $this->actingAs($user)
            ->get(route('immersion.gm.game.results', $game))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('immersion.gm.game.interrogations', $game))
            ->assertOk();
    }

    public function test_an_automatic_game_unlocks_interrogation_from_the_timeline(): void
    {
        Mail::fake();

        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_AUTOMATIC);
        $game->update(['status' => 'running', 'started_at' => now()]);

        $this->assertFalse($game->interrogation_enabled);

        // The envelope that points players at the suspects is what opens the
        // mechanic; nobody has to press a button.
        $event = $game->timelineEvents()->where('cta_interrogation', true)->firstOrFail();
        DispatchTimelineEvent::dispatchSync($event->id);

        $this->assertTrue($game->fresh()->interrogation_enabled);
    }

    public function test_a_game_master_led_game_never_unlocks_by_itself(): void
    {
        Mail::fake();

        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_GM_LED);
        $game->update(['status' => 'running', 'started_at' => now()]);

        $event = $game->timelineEvents()->where('cta_interrogation', true)->firstOrFail();
        DispatchTimelineEvent::dispatchSync($event->id);

        // Unlocking stays the directing Game Master's call.
        $this->assertFalse($game->fresh()->interrogation_enabled);
    }

    public function test_the_clock_freezes_when_the_case_is_closed(): void
    {
        $user = $this->gameMaster();
        $game = $this->createGame($user, Game::MODE_GM_LED);

        $game->update(['status' => 'running', 'started_at' => now()->subMinutes(30)]);
        $this->assertSame(30, $game->fresh()->elapsedMinutes());

        $game->fresh()->finish();
        $frozen = $game->fresh()->elapsedMinutes();

        $this->travel(20)->minutes();

        $this->assertSame($frozen, $game->fresh()->elapsedMinutes());
    }

    public function test_an_unknown_mode_is_rejected(): void
    {
        $this->actingAs($this->gameMaster())->post(route('immersion.gm.games.store'), [
            'name' => 'Partida rara',
            'mode' => 'anarchy',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ])->assertSessionHasErrors('mode');

        $this->assertDatabaseMissing('immersion_games', ['name' => 'Partida rara']);
    }
}
