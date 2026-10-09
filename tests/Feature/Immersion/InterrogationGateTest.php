<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Interrogations used to be reachable from minute 0, before the team knew
 * anything about the people. They open with the envelope that introduces
 * them (the timeline event flagged `cta_interrogation`).
 */
class InterrogationGateTest extends TestCase
{
    use RefreshDatabase;

    private const CASE_SLUG = 'habitacion-314';

    private function makeGame(string $status = 'running'): Game
    {
        $game = Game::create([
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'status' => $status,
            'interrogation_enabled' => true,
        ]);

        $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'access_token' => 'gate-token',
        ]);

        foreach ($game->caseDefinition()->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }

        return $game;
    }

    private function opener(Game $game)
    {
        return $game->timelineEvents()->where('cta_interrogation', true)->firstOrFail();
    }

    public function test_the_area_is_closed_until_the_introducing_envelope_is_sent(): void
    {
        $game = $this->makeGame();

        $this->assertFalse($game->interrogationsOpen());

        $this->get(route('immersion.player.interrogation.index', 'gate-token'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/InterrogationIndex')
                ->where('locked', true)
                ->has('suspects', 0)
                ->where('game.interrogation_open', false));

        $this->get(route('immersion.player.interrogation.show', ['gate-token', 'ramon-alday']))
            ->assertForbidden();

        $this->postJson(
            route('immersion.player.interrogation.ask', ['gate-token', 'ramon-alday']),
            ['question' => 'Demasiado pronto']
        )->assertForbidden();

        $this->assertDatabaseCount('immersion_interrogation_sessions', 0);
    }

    public function test_it_opens_when_that_envelope_goes_out(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'Nada que declarar.'], 200)]);

        $game = $this->makeGame();
        $this->opener($game)->update(['sent_at' => now()]);

        $this->assertTrue($game->fresh()->interrogationsOpen());

        $this->get(route('immersion.player.interrogation.index', 'gate-token'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('locked', false)
                ->has('suspects', count($game->caseDefinition()->suspects()))
                ->where('game.interrogation_open', true));

        $this->postJson(
            route('immersion.player.interrogation.ask', ['gate-token', 'ramon-alday']),
            ['question' => 'Donde estaba?']
        )->assertOk();
    }

    public function test_the_opt_out_still_keeps_it_closed_for_good(): void
    {
        $game = $this->makeGame();
        $this->opener($game)->update(['sent_at' => now()]);
        $game->update(['interrogation_enabled' => false]);

        $this->assertFalse($game->fresh()->interrogationsOpen());

        $this->get(route('immersion.player.interrogation.index', 'gate-token'))->assertForbidden();
    }

    public function test_a_case_without_an_introducing_envelope_is_not_gated(): void
    {
        $game = $this->makeGame();
        $game->timelineEvents()->update(['cta_interrogation' => false]);

        $this->assertTrue($game->fresh()->interrogationsOpen());
    }

    public function test_the_player_pages_tell_the_layout_whether_it_is_open(): void
    {
        $game = $this->makeGame();

        $this->get(route('immersion.player.inbox', 'gate-token'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('game.interrogation_enabled', true)
                ->where('game.interrogation_open', false));

        $this->opener($game)->update(['sent_at' => now()]);

        $this->get(route('immersion.player.inbox', 'gate-token'))
            ->assertInertia(fn (Assert $page) => $page->where('game.interrogation_open', true));
    }
}
