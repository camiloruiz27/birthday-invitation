<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Support\GameRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The shared host lets the database user open 500 connections per hour, and a
 * page that polls for news used to open one every few seconds per player. The
 * polling now goes through endpoints that read the cache only; these tests pin
 * the promises that make that safe: the endpoints cost ZERO queries, every
 * change a page can show moves a stamp, and a change only moves the stamps of
 * the screens it can show up on (otherwise a busy interrogation would make every
 * open tab of the table reload on every tick).
 */
class PollStateTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const CASE_SLUG = 'habitacion-314';

    private function makeGame(array $attributes = []): Game
    {
        $game = Game::create($attributes + [
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'status' => 'running',
            'interrogation_enabled' => true,
        ]);

        $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'access_token' => 'poll-token',
        ]);

        return $game;
    }

    /** A game whose token the poll endpoint already knows (a page was loaded). */
    private function knownGame(): Game
    {
        $game = $this->makeGame();
        $this->get(route('immersion.player.inbox', 'poll-token'))->assertOk();

        return $game;
    }

    /** @return array<int, array<string, mixed>> */
    private function queriesDuring(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        return $queries;
    }

    /** @return array<string, string>|null */
    private function pollPlayer(string $token = 'poll-token'): ?array
    {
        return $this->getJson(route('immersion.poll.player', $token))->json('r');
    }

    /** @param array<string, string> $before @param array<string, string> $after @return array<int, string> */
    private function moved(array $before, array $after): array
    {
        return array_values(array_keys(array_filter(
            $after,
            fn ($stamp, $channel) => $stamp !== ($before[$channel] ?? null),
            ARRAY_FILTER_USE_BOTH
        )));
    }

    public function test_a_player_page_hands_over_the_poll_endpoint_and_its_starting_stamps(): void
    {
        $game = $this->makeGame();

        $this->get(route('immersion.player.inbox', 'poll-token'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('pollState.url', route('immersion.poll.player', 'poll-token', false))
                ->whereType('pollState.rev', 'array')
                ->has('pollState.rev.timeline')
                ->has('pollState.rev.interrogation')
                ->has('pollState.rev.accusation'));

        $this->assertSame(GameRevision::current($game->id), $this->pollPlayer());
    }

    public function test_the_player_poll_costs_no_database_queries(): void
    {
        $this->knownGame();

        $queries = $this->queriesDuring(function () {
            $this->getJson(route('immersion.poll.player', 'poll-token'))
                ->assertOk()
                ->assertJsonStructure(['r' => ['timeline', 'interrogation', 'accusation']]);
        });

        $this->assertSame([], $queries, 'The poll must not open a database connection.');
    }

    public function test_an_unknown_token_answers_null_without_touching_the_database(): void
    {
        $queries = $this->queriesDuring(function () {
            $this->getJson(route('immersion.poll.player', 'nobody-has-this'))
                ->assertOk()
                ->assertExactJson(['r' => null]);
        });

        $this->assertSame([], $queries);
    }

    public function test_a_token_the_cache_has_forgotten_asks_for_a_reload_then_recovers(): void
    {
        $this->makeGame();

        // A deploy runs optimize:clear, which empties the cache: the endpoint
        // knows nothing until a real page load teaches it again.
        $this->getJson(route('immersion.poll.player', 'poll-token'))->assertExactJson(['r' => null]);

        $this->get(route('immersion.player.inbox', 'poll-token'))->assertOk();

        $this->assertNotNull($this->pollPlayer());
    }

    public function test_the_stamps_are_stable_until_something_changes(): void
    {
        $this->knownGame();

        $this->assertSame($this->pollPlayer(), $this->pollPlayer());
    }

    public function test_a_timeline_event_being_sent_moves_the_timeline_and_not_the_accusation(): void
    {
        $game = $this->knownGame();
        $event = $game->timelineEvents()->create([
            'type' => 'email',
            'trigger_offset_minutes' => 0,
            'title' => 'Sobre 1',
            'body_markdown' => 'x',
        ]);
        $before = $this->pollPlayer();

        $event->update(['sent_at' => now()]);

        $moved = $this->moved($before, $this->pollPlayer());

        $this->assertContains('timeline', $moved);
        // What the case has opened is what the interrogation board shows.
        $this->assertContains('interrogation', $moved);
        $this->assertNotContains('accusation', $moved);
    }

    public function test_a_question_moves_only_the_interrogation(): void
    {
        $game = $this->knownGame();
        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $game->players()->first()->id,
            'suspect_slug' => 'x',
            'started_at' => now(),
            'max_questions' => 5,
        ]);
        $before = $this->pollPlayer();

        // The query builder skips model events; the stamp is moved explicitly.
        $this->assertTrue($session->reserveQuestion());
        $afterReserve = $this->pollPlayer();

        // The inbox watches 'timeline': a table asking questions must not make
        // it reload.
        $this->assertSame(['interrogation'], $this->moved($before, $afterReserve));

        $session->releaseQuestion();
        $this->assertSame(['interrogation'], $this->moved($afterReserve, $this->pollPlayer()));
    }

    public function test_an_accusation_moves_only_the_accusation(): void
    {
        $game = $this->knownGame();
        $before = $this->pollPlayer();

        Accusation::create([
            'game_id' => $game->id,
            'player_id' => $game->players()->first()->id,
            'suspect_slug' => 'x',
            'suspect_name' => 'X',
            'motive' => 'm',
            'weapon' => 'w',
            'submitted_at' => now(),
        ]);

        $this->assertSame(['accusation'], $this->moved($before, $this->pollPlayer()));
    }

    public function test_the_game_changing_state_moves_every_screen(): void
    {
        $game = $this->knownGame();
        $before = $this->pollPlayer();

        $game->update(['status' => 'paused']);

        $this->assertEqualsCanonicalizing(GameRevision::CHANNELS, $this->moved($before, $this->pollPlayer()));
    }

    public function test_a_change_inside_a_transaction_is_only_announced_after_it_commits(): void
    {
        $game = $this->knownGame();
        $before = $this->pollPlayer();

        // RefreshDatabase already wraps the test in one transaction, so open a
        // nested one the way DispatchTimelineEvent does.
        DB::transaction(function () use ($game, $before) {
            $game->update(['status' => 'paused']);

            $this->assertSame($before, GameRevision::current($game->id), 'Announced before the commit.');
        });
    }

    public function test_the_console_poll_needs_the_session_that_rendered_the_console(): void
    {
        $user = $this->gameMaster(self::CASE_SLUG);
        $game = $this->makeGame(['user_id' => $user->id]);

        // A different browser: it never rendered this console.
        $this->getJson(route('immersion.poll.console', $game))->assertExactJson(['r' => null]);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('pollState.url', route('immersion.poll.console', $game->id, false))
                ->whereType('pollState.rev', 'array'));

        $queries = $this->queriesDuring(function () use ($game) {
            $this->getJson(route('immersion.poll.console', $game))
                ->assertOk()
                ->assertJsonStructure(['r' => ['timeline', 'interrogation', 'accusation']]);
        });

        $this->assertSame([], $queries, 'The console poll must not open a database connection.');
    }

    public function test_the_console_poll_does_not_serve_another_game(): void
    {
        $user = $this->gameMaster(self::CASE_SLUG);
        $mine = $this->makeGame(['user_id' => $user->id]);
        $other = Game::create(['name' => 'Otra', 'case_slug' => self::CASE_SLUG, 'status' => 'running']);

        $this->actingAs($user)->get(route('immersion.gm.game.show', $mine))->assertOk();

        $this->getJson(route('immersion.poll.console', $other->id))->assertExactJson(['r' => null]);
    }
}
