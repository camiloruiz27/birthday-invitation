<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * What the Game Master is told and offered before and around starting a game:
 * the library modal's data, the case preselected on "Crear partida", and when
 * to expect the first email.
 */
class GameMasterGuidanceTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_the_library_carries_what_the_case_modal_shows(): void
    {
        $user = $this->gameMaster('habitacion-314');

        $this->actingAs($user)
            ->get(route('library'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Library')
                ->has('cases', 1, fn (Assert $case) => $case
                    ->where('slug', 'habitacion-314')
                    ->whereType('description', 'string')
                    ->whereType('uses_ai', 'boolean')
                    ->has('mechanics')
                    ->etc()));
    }

    public function test_create_preselects_the_case_it_was_opened_from(): void
    {
        $user = $this->gameMaster('habitacion-314');
        $this->grantCase($user, 'kilometro-186');

        $this->actingAs($user)
            ->get(route('immersion.gm.games.create', ['case' => 'kilometro-186']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GameMaster/CreateGame')
                ->where('initialCase', 'kilometro-186'));
    }

    public function test_create_ignores_a_case_the_account_does_not_own_or_that_does_not_exist(): void
    {
        $user = $this->gameMaster('habitacion-314');

        foreach (['kilometro-186', 'no-existe', '../../etc/passwd', ''] as $requested) {
            $this->actingAs($user)
                ->get(route('immersion.gm.games.create', ['case' => $requested]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->where('initialCase', null));
        }

        $this->actingAs($user)
            ->get(route('immersion.gm.games.create'))
            ->assertInertia(fn (Assert $page) => $page->where('initialCase', null));
    }

    public function test_the_console_says_when_the_first_email_and_the_interrogations_arrive(): void
    {
        $user = $this->gameMaster('habitacion-314');
        $game = Game::create([
            'user_id' => $user->id,
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'draft',
            'interrogation_enabled' => true,
        ]);

        foreach ($game->caseDefinition()->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }

        $expected = collect($game->caseDefinition()->timeline());

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('startGuide.first_event_minutes', (int) $expected->min('trigger_offset_minutes'))
                ->where(
                    'startGuide.interrogation_minutes',
                    (int) $expected->firstWhere('cta_interrogation', true)['trigger_offset_minutes']
                ));
    }

    public function test_the_start_guide_gives_no_titles_away_to_an_owner_who_is_playing(): void
    {
        $user = $this->gameMaster('habitacion-314');
        $game = Game::create([
            'user_id' => $user->id,
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'draft',
            'mode' => Game::MODE_AUTOMATIC,
            'interrogation_enabled' => true,
        ]);
        $game->players()->create(['name' => 'Yo', 'email' => 'yo@example.com', 'access_token' => 'owner-tok', 'is_owner' => true]);

        foreach ($game->caseDefinition()->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }

        $response = $this->actingAs($user)->get(route('immersion.gm.game.show', $game))->assertOk();

        $response->assertInertia(fn (Assert $page) => $page->has('startGuide.first_event_minutes'));

        foreach ($game->caseDefinition()->timeline() as $event) {
            $this->assertStringNotContainsString($event['title'], json_encode($response->viewData('page')['props']['startGuide']));
        }
    }
}
