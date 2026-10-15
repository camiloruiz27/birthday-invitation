<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * A table has at most twelve players, the Game Master included when they play,
 * and the new-game form opens with three empty seats. The number is the
 * server's: the form only mirrors it.
 */
class PlayerLimitsTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    /** @return array<int, array{name: string, email: string}> */
    private function others(int $count): array
    {
        return array_map(
            fn (int $i) => ['name' => "Jugador {$i}", 'email' => "jugador{$i}@example.com"],
            range(1, $count)
        );
    }

    private function create(User $user, string $mode, int $count)
    {
        return $this->actingAs($user)->post(route('immersion.gm.games.store'), [
            'name' => 'Mesa grande',
            'mode' => $mode,
            'players' => $this->others($count),
        ]);
    }

    public function test_the_form_is_told_the_ceiling_and_how_many_seats_to_open(): void
    {
        $this->actingAs($this->gameMaster())
            ->get(route('immersion.gm.games.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/CreateGame')
                ->where('playerLimits.max', 12)
                ->where('playerLimits.initial_rows', 3));
    }

    public function test_twelve_players_are_allowed_when_the_game_master_leads(): void
    {
        $this->create($this->gameMaster(), Game::MODE_GM_LED, 12)->assertRedirect();

        $this->assertSame(12, Game::firstWhere('name', 'Mesa grande')->players()->count());
    }

    public function test_a_thirteenth_player_is_refused(): void
    {
        $this->create($this->gameMaster(), Game::MODE_GM_LED, 13)
            ->assertSessionHasErrors('players');

        $this->assertSame(0, Game::count(), 'Nothing is created when the table is too big.');
    }

    public function test_a_playing_owner_takes_one_of_the_twelve_seats(): void
    {
        $user = $this->gameMaster();

        $this->create($user, Game::MODE_AUTOMATIC, 12)->assertSessionHasErrors('players');
        $this->assertSame(0, Game::count());

        $this->create($user, Game::MODE_AUTOMATIC, 11)->assertRedirect();
        $this->assertSame(12, Game::firstWhere('name', 'Mesa grande')->players()->count());
    }

    public function test_the_ceiling_can_be_changed_in_one_place(): void
    {
        config(['immersion.players.max' => 5]);

        $this->create($this->gameMaster(), Game::MODE_GM_LED, 6)->assertSessionHasErrors('players');
        $this->create($this->gameMaster(), Game::MODE_GM_LED, 5)->assertRedirect();
    }

    public function test_every_case_recommends_a_sane_range_starting_at_three_at_least(): void
    {
        foreach (app(CaseRegistry::class)->all() as $definition) {
            $manifest = $definition->catalog();
            $min = (int) ($manifest['min_players'] ?? 0);
            $max = (int) ($manifest['max_players'] ?? 0);

            $this->assertGreaterThanOrEqual(3, $min, "{$definition->slug} recommends fewer than 3 players.");
            $this->assertLessThanOrEqual($max, $min, "{$definition->slug} has min above max.");
            $this->assertLessThanOrEqual(12, $max, "{$definition->slug} recommends more than the table ceiling.");
        }
    }
}
