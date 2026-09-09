<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * A game belongs to the account that created it. These tests go at the routes
 * directly, because that is what an attacker does — hiding a link in React is
 * not a restriction.
 */
class GameOwnershipTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public static function gameRoutes(): array
    {
        return [
            'show' => ['get', 'immersion.gm.game.show'],
            'results' => ['get', 'immersion.gm.game.results'],
            'interrogations' => ['get', 'immersion.gm.game.interrogations'],
            'start' => ['post', 'immersion.gm.game.start'],
            'pause' => ['post', 'immersion.gm.game.pause'],
            'resume' => ['post', 'immersion.gm.game.resume'],
            'force next' => ['post', 'immersion.gm.game.force-next'],
            'load timeline' => ['post', 'immersion.gm.game.load-default-timeline'],
            'toggle interrogation' => ['post', 'immersion.gm.game.toggle-interrogation'],
        ];
    }

    /**
     * @dataProvider gameRoutes
     */
    public function test_another_game_master_cannot_reach_a_game(string $verb, string $routeName): void
    {
        [$game] = $this->gameOwnedBy($this->gameMaster());

        $intruder = $this->gameMaster(attributes: ['email' => 'intruder@example.com']);

        $this->actingAs($intruder)
            ->{$verb}(route($routeName, $game))
            ->assertForbidden();
    }

    /**
     * @dataProvider gameRoutes
     */
    public function test_a_guest_cannot_reach_a_game(string $verb, string $routeName): void
    {
        [$game] = $this->gameOwnedBy($this->gameMaster());

        $this->{$verb}(route($routeName, $game))
            ->assertRedirect(route('login'));
    }

    /**
     * @dataProvider gameRoutes
     */
    public function test_the_owner_can_reach_their_own_game(string $verb, string $routeName): void
    {
        $owner = $this->gameMaster();
        [$game] = $this->gameOwnedBy($owner);

        $response = $this->actingAs($owner)->{$verb}(route($routeName, $game));

        // GETs render, POSTs redirect back — either way, not a 403.
        $this->assertNotSame(403, $response->getStatusCode());
    }

    public function test_games_from_before_accounts_existed_are_unreachable_until_claimed(): void
    {
        // A game created under the shared-password regime: no owner.
        $orphan = Game::create(['name' => 'Partida antigua', 'status' => 'draft']);
        $orphan->forceFill(['user_id' => null])->save();

        $user = $this->gameMaster();

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $orphan))
            ->assertForbidden();

        // Claiming is the deliberate step that hands it over.
        $this->artisan('platform:claim-games', ['email' => $user->email])
            ->assertSuccessful();

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $orphan))
            ->assertOk();
    }

    public function test_claim_games_dry_run_writes_nothing(): void
    {
        $orphan = Game::create(['name' => 'Partida antigua', 'status' => 'draft']);
        $orphan->forceFill(['user_id' => null])->save();

        $user = $this->gameMaster();

        $this->artisan('platform:claim-games', ['email' => $user->email, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertNull($orphan->fresh()->user_id);
    }

    public function test_player_routes_stay_open_to_guests(): void
    {
        [, $player] = $this->gameOwnedBy($this->gameMaster());

        // Players hold no account: the token in the URL is their credential.
        $this->get(route('immersion.player.inbox', $player->access_token))->assertOk();
    }
}
