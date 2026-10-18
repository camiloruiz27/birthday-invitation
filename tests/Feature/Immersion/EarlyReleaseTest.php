<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\GameRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * "Ya lei toda la informacion": when every player who has joined says they read
 * what arrived, the next envelope comes without waiting for its minute and the
 * clock moves up by what was saved.
 *
 * The table: a running game five minutes in, envelope A already sent (minute 1),
 * then B and C together at minute 20 (a group, like private clues), D at 40 and
 * the accusations unlock at 60. Three players who have joined.
 */
class EarlyReleaseTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private Game $game;

    private User $gm;

    /** @var array<string, Player> */
    private array $players = [];

    /** @var array<string, TimelineEvent> */
    private array $events = [];

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['immersion.credits.enabled' => false]);

        $this->game = Game::create([
            'user_id' => ($this->gm = $this->gameMaster())->id,
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'running',
            'started_at' => now()->subMinutes(5),
            'paused_seconds_total' => 0,
        ]);

        foreach (['ana', 'beto', 'cata'] as $name) {
            $this->players[$name] = $this->game->players()->create([
                'name' => ucfirst($name),
                'email' => "{$name}@example.com",
                'access_token' => "ready-{$this->game->id}-{$name}",
                'privacy_accepted_at' => now(),
            ]);
        }

        $this->events['a'] = $this->event('A', 1, sent: true);
        $this->events['b'] = $this->event('B', 20);
        $this->events['c'] = $this->event('C', 20);
        $this->events['d'] = $this->event('D', 40);
        $this->events['unlock'] = $this->event('Acusaciones', 60, type: 'unlock');
    }

    private function event(string $title, int $minute, bool $sent = false, string $type = 'email'): TimelineEvent
    {
        return $this->game->timelineEvents()->create([
            'type' => $type,
            'trigger_offset_minutes' => $minute,
            'title' => $title,
            'body_markdown' => 'Cuerpo',
            'delivery_mode' => 'all',
            'sent_at' => $sent ? now()->subMinutes(4) : null,
        ]);
    }

    private function press(string $name)
    {
        return $this->post(route('immersion.player.envelope.ready', $this->players[$name]->access_token));
    }

    private function sent(string $key): bool
    {
        return $this->events[$key]->fresh()->sent_at !== null;
    }

    private function readiness(string $name): array
    {
        $response = $this->get(route('immersion.player.inbox', $this->players[$name]->access_token));

        return $response->viewData('page')['props']['readiness'];
    }

    public function test_an_unknown_token_cannot_confirm(): void
    {
        $this->post(route('immersion.player.envelope.ready', 'no-such-token'))->assertNotFound();
    }

    public function test_the_button_is_offered_with_nobody_ready_yet(): void
    {
        $this->assertSame(
            ['available' => true, 'confirmed' => false, 'ready' => 0, 'total' => 3],
            $this->readiness('ana')
        );
    }

    public function test_one_confirmation_does_not_release_anything(): void
    {
        $this->press('ana')->assertRedirect();

        $this->assertFalse($this->sent('b'));
        $this->assertSame(
            ['available' => true, 'confirmed' => true, 'ready' => 1, 'total' => 3],
            $this->readiness('ana')
        );
        $this->assertFalse($this->readiness('beto')['confirmed']);
    }

    public function test_pressing_twice_counts_once(): void
    {
        $this->press('ana');
        $this->press('ana')->assertRedirect();

        $this->assertSame(1, $this->readiness('ana')['ready']);
    }

    public function test_the_last_confirmation_releases_the_whole_next_group(): void
    {
        $this->press('ana');
        $this->press('beto');

        $this->assertFalse($this->sent('b'));

        $this->press('cata')->assertRedirect();

        $this->assertTrue($this->sent('b'));
        $this->assertTrue($this->sent('c'));
        $this->assertFalse($this->sent('d'));
        $this->assertFalse($this->sent('unlock'));
    }

    public function test_the_clock_moves_up_so_the_rest_keeps_its_spacing(): void
    {
        $this->assertSame(5, $this->game->fresh()->elapsedMinutes());

        $this->press('ana');
        $this->press('beto');
        $this->press('cata');

        // The group was due at minute 20: the clock is there now, and D is still
        // twenty minutes away rather than fifteen.
        $this->assertSame(20, $this->game->fresh()->elapsedMinutes());
        $this->assertFalse($this->sent('d'));

        $this->travel(19)->minutes();
        $this->artisan('immersion:process-timeline');
        $this->assertFalse($this->sent('d'));

        $this->travel(2)->minutes();
        $this->artisan('immersion:process-timeline');
        $this->assertTrue($this->sent('d'));
    }

    public function test_a_new_envelope_turns_everyone_back_to_not_ready(): void
    {
        foreach (['ana', 'beto', 'cata'] as $name) {
            $this->press($name);
        }

        $this->assertSame(
            ['available' => true, 'confirmed' => false, 'ready' => 0, 'total' => 3],
            $this->readiness('ana')
        );
    }

    public function test_a_player_who_never_joined_does_not_hold_the_table_back(): void
    {
        $this->players['cata']->forceFill(['privacy_accepted_at' => null])->save();

        $this->assertSame(2, $this->readiness('ana')['total']);

        $this->press('ana');
        $this->press('beto');

        $this->assertTrue($this->sent('b'));
    }

    public function test_someone_who_has_not_joined_cannot_confirm(): void
    {
        $this->players['cata']->forceFill(['privacy_accepted_at' => null])->save();

        $this->press('cata')->assertForbidden();
    }

    public function test_the_owner_of_an_automatic_game_counts_without_the_notice(): void
    {
        $this->players['cata']->forceFill(['privacy_accepted_at' => null, 'is_owner' => true])->save();

        $this->assertSame(3, $this->readiness('ana')['total']);

        $this->press('ana');
        $this->press('beto');
        $this->assertFalse($this->sent('b'));

        $this->press('cata');
        $this->assertTrue($this->sent('b'));
    }

    public function test_nothing_is_offered_while_the_game_is_paused_or_finished(): void
    {
        $this->game->update(['status' => 'paused', 'paused_at' => now()]);

        $this->assertFalse($this->readiness('ana')['available']);
        $this->press('ana')->assertForbidden();

        $this->game->update(['status' => 'finished', 'finished_at' => now(), 'paused_at' => null]);

        $this->assertFalse($this->readiness('ana')['available']);
        $this->press('ana')->assertForbidden();
    }

    public function test_nothing_is_offered_before_any_envelope_has_arrived(): void
    {
        $this->events['a']->update(['sent_at' => null]);

        $this->assertFalse($this->readiness('ana')['available']);
        $this->press('ana')->assertForbidden();
    }

    public function test_the_accusations_unlock_is_never_skipped(): void
    {
        foreach (['b', 'c', 'd'] as $key) {
            $this->events[$key]->update(['sent_at' => now()]);
        }

        $this->assertFalse($this->readiness('ana')['available']);
        $this->press('ana')->assertForbidden();
        $this->assertFalse($this->sent('unlock'));
    }

    public function test_nothing_is_offered_when_there_is_no_next_envelope(): void
    {
        $this->game->timelineEvents()->whereNull('sent_at')->update(['sent_at' => now()]);

        $this->assertFalse($this->readiness('ana')['available']);
    }

    public function test_it_can_be_switched_off(): void
    {
        config(['immersion.early_release.enabled' => false]);

        $this->assertFalse($this->readiness('ana')['available']);
        $this->press('ana')->assertNotFound();
    }

    public function test_confirming_moves_the_readiness_stamp_the_pages_watch(): void
    {
        $before = GameRevision::current($this->game->id)['readiness'];

        $this->press('ana');

        $this->assertNotSame($before, GameRevision::current($this->game->id)['readiness']);
    }

    public function test_with_snapshots_on_the_same_answer_costs_no_queries_once_ready(): void
    {
        config(['immersion.snapshots.enabled' => true]);
        GameRevision::bump($this->game->id);

        $url = route('immersion.player.inbox', $this->players['ana']->access_token);

        // The first read builds what the table shares; whoever comes next pays nothing.
        $this->get($url)->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        $this->assertSame([], $queries);
        $this->assertSame(
            ['available' => true, 'confirmed' => false, 'ready' => 0, 'total' => 3],
            $this->readiness('ana')
        );
    }

    public function test_with_snapshots_on_a_confirmation_shows_to_the_whole_table(): void
    {
        config(['immersion.snapshots.enabled' => true]);
        GameRevision::bump($this->game->id);

        $this->readiness('beto');
        $this->press('ana');

        $this->assertSame(['available' => true, 'confirmed' => false, 'ready' => 1, 'total' => 3], $this->readiness('beto'));
        $this->assertTrue($this->readiness('ana')['confirmed']);

        $this->press('beto');
        $this->press('cata');

        $this->assertTrue($this->sent('b'));
        $this->assertSame(0, $this->readiness('beto')['ready']);
    }

    public function test_accepting_the_privacy_notice_makes_a_player_count_for_the_table(): void
    {
        config(['immersion.snapshots.enabled' => true]);
        $this->players['cata']->forceFill(['privacy_accepted_at' => null])->save();
        GameRevision::bump($this->game->id);

        $this->assertSame(2, $this->readiness('ana')['total']);

        $this->post(route('immersion.player.privacy.accept', $this->players['cata']->access_token));

        $this->assertSame(3, $this->readiness('ana')['total']);
    }

    public function test_the_game_master_sees_how_many_have_confirmed(): void
    {
        $this->press('ana');
        $this->press('beto');

        $this->actingAs($this->gm)
            ->get(route('immersion.gm.game.show', $this->game))
            ->assertInertia(fn (Assert $page) => $page
                ->where('readers', ['available' => true, 'ready' => 2, 'total' => 3])
            );
    }

    public function test_the_game_master_can_still_force_the_next_envelope(): void
    {
        $this->actingAs($this->gm)
            ->post(route('immersion.gm.game.force-next', $this->game))
            ->assertRedirect();

        $this->assertTrue($this->sent('b') || $this->sent('c'));
    }
}
