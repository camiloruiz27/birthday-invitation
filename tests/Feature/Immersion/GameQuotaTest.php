<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The game quota is PER CASE: someone who owns four cases can keep six games
 * of each. Every game counts whatever its state, and the only way to free a
 * slot is to delete a game of that same case.
 */
class GameQuotaTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const OTHER_CASE = 'otro-caso';

    /**
     * Games of an arbitrary case, created directly: only steve-jacobs has a
     * manifest installed, and what is under test here is the counting.
     */
    private function fillCase(User $user, string $caseSlug, int $count): void
    {
        for ($index = 0; $index < $count; $index++) {
            Game::create([
                'user_id' => $user->id,
                'name' => "{$caseSlug} {$index}",
                'case_slug' => $caseSlug,
                'status' => 'draft',
            ]);
        }
    }

    private function createGame(User $user, string $name = 'Nueva'): TestResponse
    {
        return $this->actingAs($user)->post(route('immersion.gm.games.store'), [
            'name' => $name,
            'case_slug' => 'steve-jacobs',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ]);
    }

    public function test_a_game_master_can_create_up_to_the_limit_for_a_case(): void
    {
        config(['immersion.games.max_per_case' => 6]);
        $user = $this->gameMaster();

        $this->fillCase($user, 'steve-jacobs', 5);
        $this->createGame($user, 'La sexta')->assertRedirect();

        $this->assertSame(6, $user->games()->where('case_slug', 'steve-jacobs')->count());
    }

    public function test_creating_past_the_limit_for_that_case_is_rejected(): void
    {
        config(['immersion.games.max_per_case' => 6]);
        $user = $this->gameMaster();
        $this->fillCase($user, 'steve-jacobs', 6);

        $this->createGame($user, 'La septima')->assertSessionHasErrors('case_slug');

        $this->assertDatabaseMissing('immersion_games', ['name' => 'La septima']);
    }

    public function test_filling_one_case_does_not_block_another(): void
    {
        config(['immersion.games.max_per_case' => 2]);
        $user = $this->gameMaster();

        // Full on a different case entirely.
        $this->fillCase($user, self::OTHER_CASE, 2);

        // steve-jacobs still has all its room: the quota is per case.
        $this->createGame($user, 'De steve 1')->assertRedirect();
        $this->createGame($user, 'De steve 2')->assertRedirect();

        $this->assertSame(4, $user->games()->count());
        $this->assertSame(2, $user->games()->where('case_slug', 'steve-jacobs')->count());

        // And now steve-jacobs is full on its own account.
        $this->createGame($user, 'De steve 3')->assertSessionHasErrors('case_slug');
    }

    public function test_a_rejected_creation_leaves_nothing_behind(): void
    {
        config(['immersion.games.max_per_case' => 2]);
        $user = $this->gameMaster();
        $this->fillCase($user, 'steve-jacobs', 2);

        $playersBefore = Player::count();

        $this->createGame($user, 'Rechazada')->assertSessionHasErrors('case_slug');

        // The whole creation is one transaction, so no orphan players or
        // timeline events survive the rejection.
        $this->assertSame($playersBefore, Player::count());
        $this->assertSame(2, $user->games()->count());
    }

    public function test_finished_games_still_occupy_a_slot(): void
    {
        config(['immersion.games.max_per_case' => 2]);
        $user = $this->gameMaster();

        [$first] = $this->gameOwnedBy($user, ['status' => 'running', 'started_at' => now()]);
        $first->finish();
        $this->gameOwnedBy($user);

        // Closing a case does not free a slot; only deleting does.
        $this->createGame($user, 'Tercera')->assertSessionHasErrors('case_slug');
    }

    public function test_the_limit_is_per_account(): void
    {
        config(['immersion.games.max_per_case' => 2]);

        $user = $this->gameMaster();
        $this->fillCase($user, 'steve-jacobs', 2);

        // Another Game Master's games do not eat into this one's quota.
        $other = $this->gameMaster(attributes: ['email' => 'other@example.com']);
        $this->createGame($other, 'De otro')->assertRedirect();

        $this->assertSame(1, $other->games()->count());
    }

    public function test_deleting_a_game_of_that_case_frees_a_slot(): void
    {
        config(['immersion.games.max_per_case' => 2]);
        $user = $this->gameMaster();

        [$first] = $this->gameOwnedBy($user);
        $this->gameOwnedBy($user);

        $this->createGame($user, 'Bloqueada')->assertSessionHasErrors('case_slug');

        $this->actingAs($user)
            ->delete(route('immersion.gm.game.destroy', $first))
            ->assertRedirect(route('immersion.gm.games.index'));

        $this->createGame($user, 'Ahora si')->assertRedirect();
        $this->assertDatabaseHas('immersion_games', ['name' => 'Ahora si']);
    }

    public function test_deleting_a_game_of_another_case_does_not_free_this_ones_slot(): void
    {
        config(['immersion.games.max_per_case' => 2]);
        $user = $this->gameMaster();

        $this->fillCase($user, 'steve-jacobs', 2);
        $this->fillCase($user, self::OTHER_CASE, 1);

        $otherGame = Game::where('case_slug', self::OTHER_CASE)->firstOrFail();
        $this->actingAs($user)->delete(route('immersion.gm.game.destroy', $otherGame));

        // steve-jacobs is still full: slots do not transfer between cases.
        $this->createGame($user, 'Sigue bloqueada')->assertSessionHasErrors('case_slug');
    }

    public function test_deleting_a_game_removes_everything_under_it(): void
    {
        $user = $this->gameMaster();
        [$game, $player] = $this->gameOwnedBy($user);

        $game->timelineEvents()->create([
            'type' => 'email',
            'trigger_offset_minutes' => 5,
            'title' => 'Sobre',
            'delivery_mode' => 'all',
        ]);

        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'elizabeth-foster',
            'started_at' => now(),
            'max_questions' => 5,
        ]);
        $session->messages()->create(['role' => 'player', 'content' => '¿Dónde estabas?']);

        $game->accusations()->create([
            'player_id' => $player->id,
            'suspect_name' => 'Alguien',
            'motive' => 'Un motivo',
            'weapon' => 'Un arma',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)->delete(route('immersion.gm.game.destroy', $game));

        $this->assertDatabaseCount('immersion_games', 0);
        $this->assertDatabaseCount('immersion_players', 0);
        $this->assertDatabaseCount('immersion_timeline_events', 0);
        $this->assertDatabaseCount('immersion_interrogation_sessions', 0);
        $this->assertDatabaseCount('immersion_interrogation_messages', 0);
        $this->assertDatabaseCount('immersion_accusations', 0);
    }

    public function test_deleting_a_game_cleans_up_its_audio_files(): void
    {
        Storage::fake('local');

        $user = $this->gameMaster();
        [$game] = $this->gameOwnedBy($user);

        $event = $game->timelineEvents()->create([
            'type' => 'audio_email',
            'trigger_offset_minutes' => 5,
            'title' => 'Audio',
            'audio_script' => 'Un guion',
            'audio_path' => 'audio/99.wav',
            'delivery_mode' => 'all',
        ]);

        Storage::disk('local')->put($event->audio_path, 'bytes');

        $this->actingAs($user)->delete(route('immersion.gm.game.destroy', $game));

        // Audio lives on disk, not in a table, so a cascade would not reach it.
        Storage::disk('local')->assertMissing('audio/99.wav');
    }

    public function test_a_players_link_stops_working_once_the_game_is_deleted(): void
    {
        $user = $this->gameMaster();
        [$game, $player] = $this->gameOwnedBy($user);
        $token = $player->access_token;

        $this->get(route('immersion.player.inbox', $token))->assertOk();

        $this->actingAs($user)->delete(route('immersion.gm.game.destroy', $game));

        $this->get(route('immersion.player.inbox', $token))->assertNotFound();
    }

    public function test_only_the_owner_can_delete_a_game(): void
    {
        $user = $this->gameMaster();
        [$game] = $this->gameOwnedBy($user);

        $intruder = $this->gameMaster(attributes: ['email' => 'intruder@example.com']);

        $this->actingAs($intruder)
            ->delete(route('immersion.gm.game.destroy', $game))
            ->assertForbidden();

        // A guest is refused outright rather than sent to a login page: this
        // is a destructive verb, not a page someone navigated to by mistake.
        $this->delete(route('immersion.gm.game.destroy', $game))
            ->assertForbidden();

        $this->assertDatabaseCount('immersion_games', 1);
    }

    public function test_the_create_page_reports_the_quota_of_each_case(): void
    {
        config(['immersion.games.max_per_case' => 6]);
        $user = $this->gameMaster();
        $this->fillCase($user, 'steve-jacobs', 4);

        $this->actingAs($user)
            ->get(route('immersion.gm.games.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('library.0.slug', 'steve-jacobs')
                ->where('library.0.quota.used', 4)
                ->where('library.0.quota.limit', 6)
                ->where('library.0.quota.remaining', 2)
                ->where('library.0.quota.full', false)
            );
    }

    public function test_the_library_reports_the_quota_of_each_case(): void
    {
        config(['immersion.games.max_per_case' => 2]);
        $user = $this->gameMaster();
        $this->fillCase($user, 'steve-jacobs', 2);

        $this->actingAs($user)
            ->get(route('library'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cases.0.quota.used', 2)
                ->where('cases.0.quota.full', true)
            );
    }

    public function test_creating_is_offered_while_any_case_has_room(): void
    {
        config(['immersion.games.max_per_case' => 2]);
        $user = $this->gameMaster();

        $this->actingAs($user)
            ->get(route('immersion.gm.games.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canCreate', true));

        $this->fillCase($user, 'steve-jacobs', 2);

        // The only owned case is full, so there is nothing left to create.
        $this->actingAs($user)
            ->get(route('immersion.gm.games.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canCreate', false));
    }

    public function test_lowering_the_limit_never_deletes_anything(): void
    {
        config(['immersion.games.max_per_case' => 6]);
        $user = $this->gameMaster();
        $this->fillCase($user, 'steve-jacobs', 6);

        config(['immersion.games.max_per_case' => 3]);

        // Over the new limit: cannot create, but keeps everything.
        $this->createGame($user, 'Nueva')->assertSessionHasErrors('case_slug');
        $this->assertSame(6, Game::where('user_id', $user->id)->count());
    }
}
