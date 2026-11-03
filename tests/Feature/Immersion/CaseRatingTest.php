<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Models\CaseRating;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Platform\Support\PublishedCases;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class CaseRatingTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    /** @return array{0: Game, 1: Player, 2: Player} */
    private function revealedGame(): array
    {
        $owner = User::factory()->create();
        $case = $this->catalogCase('steve-jacobs');
        $game = Game::create([
            'user_id' => $owner->id,
            'name' => 'Mesa de valoración',
            'case_slug' => $case->slug,
            'status' => 'finished',
            'finished_at' => now(),
            'ending_revealed_at' => now(),
        ]);

        $first = $game->players()->create([
            'name' => 'Ana', 'email' => 'ana@example.test', 'access_token' => 'rating-ana',
        ]);
        $second = $game->players()->create([
            'name' => 'Luis', 'email' => 'luis@example.test', 'access_token' => 'rating-luis',
        ]);

        return [$game, $first, $second];
    }

    public function test_unregistered_players_can_rate_once_each_after_the_ending(): void
    {
        [$game, $ana, $luis] = $this->revealedGame();

        $this->post(route('immersion.player.rating.store', $ana->access_token), [
            'rating_half_steps' => 7,
            'tags' => ['story', 'clues'],
        ])->assertSessionHas('status');

        $this->post(route('immersion.player.rating.store', $luis->access_token), [
            'rating_half_steps' => 9,
            'tags' => ['pacing'],
        ])->assertSessionHas('status');

        $this->assertSame(2, CaseRating::query()->where('game_id', $game->id)->count());
        $this->assertDatabaseHas('case_ratings', [
            'game_id' => $game->id,
            'player_id' => $ana->id,
            'case_slug' => $game->case_slug,
            'rating_half_steps' => 7,
        ]);
    }

    public function test_a_second_submission_is_idempotent_and_does_not_change_the_first_rating(): void
    {
        [, $player] = $this->revealedGame();

        $this->post(route('immersion.player.rating.store', $player->access_token), ['rating_half_steps' => 6]);
        $this->post(route('immersion.player.rating.store', $player->access_token), ['rating_half_steps' => 10])
            ->assertSessionHas('status', 'Tu valoración ya fue registrada.');

        $this->assertSame(1, CaseRating::query()->count());
        $this->assertSame(6, CaseRating::first()->rating_half_steps);
    }

    public function test_ratings_are_rejected_before_the_game_ends_or_the_ending_is_revealed(): void
    {
        [$game, $player] = $this->revealedGame();
        $game->update(['status' => 'running', 'finished_at' => null, 'ending_revealed_at' => null]);

        $this->post(route('immersion.player.rating.store', $player->access_token), ['rating_half_steps' => 8])
            ->assertForbidden();
        $this->assertSame(0, CaseRating::query()->count());
    }

    public function test_only_whole_or_half_star_values_and_known_tags_are_accepted(): void
    {
        [, $player] = $this->revealedGame();

        $this->post(route('immersion.player.rating.store', $player->access_token), ['rating_half_steps' => 1])
            ->assertSessionHasErrors('rating_half_steps');
        $this->post(route('immersion.player.rating.store', $player->access_token), ['rating_half_steps' => 2.5])
            ->assertSessionHasErrors('rating_half_steps');
        $this->post(route('immersion.player.rating.store', $player->access_token), ['rating_half_steps' => 11])
            ->assertSessionHasErrors('rating_half_steps');
        $this->post(route('immersion.player.rating.store', $player->access_token), [
            'rating_half_steps' => 8,
            'tags' => ['not-a-tag'],
        ])->assertSessionHasErrors('tags.0');
    }

    public function test_solution_only_exposes_the_current_players_own_rating_state(): void
    {
        [, $ana, $luis] = $this->revealedGame();
        CaseRating::create([
            'case_slug' => $ana->game->case_slug,
            'game_id' => $ana->game_id,
            'player_id' => $luis->id,
            'rating_half_steps' => 10,
            'tags' => ['recommend'],
        ]);

        $this->get(route('immersion.player.solution', $ana->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/Solution')
                ->where('rating.submitted', false)
                ->where('rating.rating_half_steps', null)
                ->where('rating.tags', [])
            );
    }

    public function test_public_catalog_shows_a_one_decimal_average_and_number_of_ratings(): void
    {
        [$game, $ana, $luis] = $this->revealedGame();
        CaseRating::create(['case_slug' => $game->case_slug, 'game_id' => $game->id, 'player_id' => $ana->id, 'rating_half_steps' => 7, 'tags' => []]);
        CaseRating::create(['case_slug' => $game->case_slug, 'game_id' => $game->id, 'player_id' => $luis->id, 'rating_half_steps' => 10, 'tags' => []]);
        PublishedCases::forget();

        $this->get(route('cases.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cases.0.rating.average', 4.3)
                ->where('cases.0.rating.count', 2)
            );
    }

    public function test_admin_can_inspect_anonymous_case_rating_metrics(): void
    {
        [$game, $player] = $this->revealedGame();
        CaseRating::create([
            'case_slug' => $game->case_slug,
            'game_id' => $game->id,
            'player_id' => $player->id,
            'rating_half_steps' => 8,
            'tags' => ['story', 'recommend'],
        ]);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->get(route('admin.ratings'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Ratings')
                ->where('ratings.0.count', 1)
                ->where('ratings.0.average', 4)
                ->where('ratings.0.tags.0.label', 'Historia excelente')
            );
    }
}
