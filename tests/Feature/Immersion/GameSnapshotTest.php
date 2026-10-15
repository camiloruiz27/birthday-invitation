<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\GameRevision;
use App\Modules\Immersion\Support\GameSnapshot;
use App\Modules\Immersion\Support\PlayerNotices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * What a table of players reads is kept ready in the cache so that reading it
 * costs no database connection (the host allows 500 an hour). These pin down the
 * two things that matter about that:
 *
 *  - it is INVISIBLE: every page a player can open shows exactly what it shows
 *    from the database, through every kind of change, for every player;
 *  - it is CHEAP: a page that was just rebuilt costs no queries, and whoever
 *    writes leaves the readers' copy ready.
 */
class GameSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private const CASE_SLUG = 'habitacion-314';

    private Game $game;

    /** @var array<string, Player> */
    private array $players = [];

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['immersion.credits.enabled' => false]);
    }

    private function build(): void
    {
        $this->game = Game::create([
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'status' => 'running',
            'interrogation_enabled' => true,
            'started_at' => now()->subMinutes(30),
            'paused_seconds_total' => 0,
        ]);

        foreach (['ana' => 'forense', 'beto' => 'analista', 'cata' => 'testigo'] as $name => $role) {
            $this->players[$name] = $this->game->players()->create([
                'name' => ucfirst($name),
                'email' => "{$name}@example.com",
                'access_token' => "tok-{$name}",
                'role_slug' => $role,
            ]);
        }

        foreach ($this->game->caseDefinition()->timeline() as $event) {
            $this->game->timelineEvents()->create($event);
        }

        // The first five envelopes are out; the private clues were drawn for two players.
        $this->game->timelineEvents()->orderBy('trigger_offset_minutes')->orderBy('id')->take(5)->get()
            ->each(function ($event, $index) {
                $event->update(['sent_at' => now()->subMinutes(20 - $index)]);
            });

        $privates = $this->game->timelineEvents()->where('delivery_mode', 'random_player')->orderBy('id')->get();
        $privates[0]->update(['delivered_to_player_id' => $this->players['ana']->id]);
        $privates[1]->update(['delivered_to_player_id' => $this->players['beto']->id]);
    }

    /** What the player sees, as the browser would get it, minus what is not about the page itself. */
    private function props(string $url): string
    {
        $props = $this->get($url)->assertOk()->viewData('page')['props'];

        // The stamps are read at different moments by the two runs; they are not content.
        unset($props['pollState']);

        return json_encode(self::canonical($props), JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * The order of an object's keys carries no meaning (the browser reads them by
     * name), and two loads of the same relations may list them differently; the
     * order of a LIST does, and is kept.
     */
    private static function canonical(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(fn ($item) => self::canonical($item), $value);

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    /** @return array<string, string> */
    private function pages(): array
    {
        $pages = [];

        foreach ($this->players as $name => $player) {
            $token = $player->access_token;

            $pages["{$name}:inbox"] = route('immersion.player.inbox', $token);
            $pages["{$name}:interrogation"] = route('immersion.player.interrogation.index', $token);
            $pages["{$name}:accusation"] = route('immersion.player.accusation', $token);

            foreach (['ramon-alday', 'daniel-prieto', 'sofia-reguera'] as $suspect) {
                $pages["{$name}:chat:{$suspect}"] = route('immersion.player.interrogation.show', [$token, $suspect]);
            }
        }

        return $pages;
    }

    /**
     * Every page, for every player, from the database and then from the
     * snapshots; they must say the same thing.
     */
    private function assertSnapshotsChangeNothing(string $moment): void
    {
        $fromDatabase = [];
        config(['immersion.snapshots.enabled' => false]);
        Cache::flush();

        foreach ($this->pages() as $label => $url) {
            $fromDatabase[$label] = $this->props($url);
        }

        config(['immersion.snapshots.enabled' => true]);
        Cache::flush();

        // Twice: the first read builds, the second is the one a player gets.
        foreach ([1, 2] as $pass) {
            foreach ($this->pages() as $label => $url) {
                $this->assertSame($fromDatabase[$label], $this->props($url), "{$moment} — {$label} (read {$pass})");
            }
        }
    }

    private function claim(string $suspect, string $player, int $used, bool $closed = false): InterrogationSession
    {
        $session = InterrogationSession::create([
            'game_id' => $this->game->id,
            'player_id' => $this->players[$player]->id,
            'suspect_slug' => $suspect,
            'started_at' => now()->subMinutes(10),
            'questions_used' => $used,
            'max_questions' => 5,
            'closed_at' => $closed ? now()->subMinute() : null,
        ]);

        foreach (range(1, $used) as $i) {
            InterrogationMessage::create(['session_id' => $session->id, 'role' => 'player', 'content' => "Pregunta {$i}"]);
            InterrogationMessage::create(['session_id' => $session->id, 'role' => 'suspect', 'content' => "Respuesta {$i}"]);
        }

        return $session;
    }

    // --- it is invisible ------------------------------------------------------------------

    public function test_a_game_with_envelopes_and_nothing_else_looks_the_same(): void
    {
        $this->build();

        $this->assertSnapshotsChangeNothing('envelopes only');
    }

    public function test_interrogations_look_the_same_whoever_claimed_what(): void
    {
        $this->build();
        $this->claim('ramon-alday', 'ana', 2);
        $this->claim('daniel-prieto', 'beto', 5, closed: true);

        $this->assertSnapshotsChangeNothing('two suspects claimed');
    }

    public function test_accusations_and_the_ending_look_the_same(): void
    {
        $this->build();
        $this->claim('ramon-alday', 'ana', 1);
        $this->game->timelineEvents()->where('type', 'unlock')->update(['sent_at' => now()]);

        Accusation::create([
            'game_id' => $this->game->id,
            'player_id' => $this->players['ana']->id,
            'suspect_slug' => 'ramon-alday',
            'suspect_name' => 'Ramón',
            'motive' => 'dinero',
            'weapon' => 'cuerda',
            'submitted_at' => now(),
            'epilogue_status' => 'ready',
            'epilogue_body' => 'Me descubriste.',
        ]);
        $this->game->update(['ending_type' => Game::ENDING_EPILOGUE]);

        $this->assertSnapshotsChangeNothing('accusations open, one accusation');

        $this->game->update(['ending_revealed_at' => now(), 'status' => 'finished', 'finished_at' => now()]);

        $this->assertSnapshotsChangeNothing('ending revealed');
    }

    public function test_a_paused_game_looks_the_same(): void
    {
        $this->build();
        $this->game->update(['status' => 'paused', 'paused_at' => now()]);

        $this->assertSnapshotsChangeNothing('paused');
    }

    public function test_the_notices_are_the_same(): void
    {
        $this->build();
        $this->claim('ramon-alday', 'ana', 1);
        $this->game->timelineEvents()->where('type', 'unlock')->update(['sent_at' => now()]);

        $fromDatabase = [];
        config(['immersion.snapshots.enabled' => false]);
        foreach ($this->players as $name => $player) {
            $fromDatabase[$name] = PlayerNotices::for(Player::find($player->id));
        }

        config(['immersion.snapshots.enabled' => true]);
        Cache::flush();
        foreach ($this->players as $name => $player) {
            $this->assertSame($fromDatabase[$name], PlayerNotices::for(Player::find($player->id)), $name);
        }
    }

    // --- it keeps up with what happens ------------------------------------------------------

    public function test_an_envelope_going_out_reaches_the_readers(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);

        $before = $this->props(route('immersion.player.inbox', 'tok-ana'));

        $this->game->timelineEvents()->whereNull('sent_at')->orderBy('trigger_offset_minutes')->first()
            ->update(['sent_at' => now()]);

        $this->assertNotSame($before, $this->props(route('immersion.player.inbox', 'tok-ana')));

        // And it is the same page the database would give.
        $this->assertSnapshotsChangeNothing('after an envelope went out');
    }

    public function test_a_question_reaches_the_other_players_board_and_the_transcript(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        $board = fn () => $this->props(route('immersion.player.interrogation.index', 'tok-beto'));
        $before = $board();

        $this->postJson(
            route('immersion.player.interrogation.ask', ['tok-ana', 'ramon-alday']),
            ['question' => 'Donde estaba?']
        )->assertOk();

        $this->assertNotSame($before, $board(), 'Beto sees that Ana took Ramón and asked once.');
        $this->assertStringContainsString('Donde estaba?', $this->props(route('immersion.player.interrogation.show', ['tok-ana', 'ramon-alday'])));

        $this->assertSnapshotsChangeNothing('after a question');
    }

    public function test_a_player_changing_their_name_or_role_is_seen_at_once(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);

        $this->props(route('immersion.player.inbox', 'tok-ana'));

        $this->players['ana']->update(['name' => 'Ana María']);

        $this->assertStringContainsString('Ana María', $this->props(route('immersion.player.inbox', 'tok-ana')));
    }

    public function test_a_deleted_player_is_gone_even_from_the_cache(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);

        $this->get(route('immersion.player.inbox', 'tok-ana'))->assertOk();
        $this->players['ana']->delete();

        $this->get(route('immersion.player.inbox', 'tok-ana'))->assertNotFound();
    }

    public function test_a_game_that_changed_without_anyone_saying_so_is_still_fixed_within_minutes(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);

        $this->get(route('immersion.player.inbox', 'tok-ana'))->assertOk();

        // A write that skipped every event (a raw UPDATE): no stamp moved.
        DB::table('immersion_games')->where('id', $this->game->id)->update(['name' => 'Mesa renombrada']);

        $this->assertStringNotContainsString('Mesa renombrada', $this->props(route('immersion.player.inbox', 'tok-ana')));

        Carbon::setTestNow(now()->addSeconds(GameSnapshot::TTL_SECONDS + 5));

        try {
            $this->assertStringContainsString('Mesa renombrada', $this->props(route('immersion.player.inbox', 'tok-ana')));
        } finally {
            Carbon::setTestNow();
        }
    }

    // --- it is cheap -----------------------------------------------------------------------

    /** @return array<int, string> */
    private function queriesDuring(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        return $queries;
    }

    public function test_a_page_that_is_ready_costs_no_database_queries(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);
        $this->claim('ramon-alday', 'ana', 2);
        GameRevision::bump($this->game->id);

        // The first read builds; whoever comes next pays nothing.
        foreach ($this->pages() as $url) {
            $this->get($url)->assertOk();
        }

        foreach ($this->pages() as $label => $url) {
            $queries = $this->queriesDuring(fn () => $this->get($url)->assertOk());

            $this->assertSame([], $queries, "{$label} still asked the database");
        }
    }

    public function test_without_snapshots_the_same_pages_do_ask_the_database(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => false]);

        $this->assertNotSame([], $this->queriesDuring(fn () => $this->get(route('immersion.player.inbox', 'tok-ana'))->assertOk()));
    }

    public function test_the_player_who_writes_leaves_the_readers_copy_ready(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        // Everyone has read their pages once.
        foreach ($this->pages() as $url) {
            $this->get($url)->assertOk();
        }

        // Ana asks a question: a write, on a connection it already has.
        $this->postJson(
            route('immersion.player.interrogation.ask', ['tok-ana', 'ramon-alday']),
            ['question' => 'Donde estaba?']
        )->assertOk();

        // After it, Beto's board and Ana's transcript need nothing from the database.
        foreach ([
            route('immersion.player.interrogation.index', 'tok-beto'),
            route('immersion.player.interrogation.show', ['tok-ana', 'ramon-alday']),
            route('immersion.player.interrogation.index', 'tok-cata'),
        ] as $url) {
            $this->assertSame([], $this->queriesDuring(fn () => $this->get($url)->assertOk()), $url);
        }
    }

    public function test_a_question_to_one_suspect_does_not_throw_away_the_others_transcripts(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        $this->claim('daniel-prieto', 'beto', 1);
        GameRevision::bump($this->game->id);

        $danielChat = route('immersion.player.interrogation.show', ['tok-beto', 'daniel-prieto']);
        $this->get($danielChat)->assertOk();

        $this->postJson(
            route('immersion.player.interrogation.ask', ['tok-ana', 'ramon-alday']),
            ['question' => 'Donde estaba?']
        )->assertOk();

        $this->assertSame([], $this->queriesDuring(fn () => $this->get($danielChat)->assertOk()), "Daniel's transcript did not change.");
    }

    // --- it stays out of the way --------------------------------------------------------------

    public function test_with_snapshots_off_nothing_is_kept(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => false]);

        foreach ($this->pages() as $url) {
            $this->get($url)->assertOk();
        }

        foreach (['meta', 'timeline', 'inbox', 'board', 'accusations'] as $part) {
            $this->assertFalse(Cache::has("immersion:snap:{$this->game->id}:{$part}"), $part);
        }
        $this->assertFalse(Cache::has('immersion:pb:'.sha1('tok-ana')));
    }

    public function test_a_write_is_always_made_against_the_database(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);

        // Warm everything, then change the game behind the cache's back: a write
        // must act on the truth, not on a copy.
        $this->get(route('immersion.player.inbox', 'tok-ana'))->assertOk();
        DB::table('immersion_games')->where('id', $this->game->id)->update(['interrogation_enabled' => 0]);

        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);

        $this->postJson(
            route('immersion.player.interrogation.ask', ['tok-ana', 'ramon-alday']),
            ['question' => 'Donde estaba?']
        )->assertStatus(403);
    }

    public function test_two_games_never_share_anything(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);

        $other = Game::create([
            'name' => 'Otra mesa',
            'case_slug' => self::CASE_SLUG,
            'status' => 'running',
            'interrogation_enabled' => true,
            'started_at' => now()->subMinutes(5),
            'paused_seconds_total' => 0,
        ]);
        $other->players()->create(['name' => 'Zoe', 'email' => 'zoe@example.com', 'access_token' => 'tok-zoe']);

        $mine = $this->props(route('immersion.player.inbox', 'tok-ana'));
        $hers = $this->props(route('immersion.player.inbox', 'tok-zoe'));

        $this->assertStringContainsString('Mesa', $mine);
        $this->assertStringNotContainsString('Otra mesa', $mine);
        $this->assertStringContainsString('Otra mesa', $hers);
        $this->assertStringNotContainsString('"Mesa"', $hers);
    }

    public function test_a_private_clue_never_reaches_the_wrong_player(): void
    {
        $this->build();
        config(['immersion.snapshots.enabled' => true]);

        $private = $this->game->timelineEvents()->where('delivered_to_player_id', $this->players['ana']->id)->first();

        $this->assertStringContainsString($private->title, $this->props(route('immersion.player.inbox', 'tok-ana')));
        $this->assertStringNotContainsString($private->title, $this->props(route('immersion.player.inbox', 'tok-beto')));
        $this->assertStringNotContainsString($private->title, $this->props(route('immersion.player.inbox', 'tok-cata')));
    }

    public function test_the_number_of_cached_files_does_not_grow_with_the_changes(): void
    {
        $this->build();
        $path = sys_get_temp_dir().'/snap-test-'.uniqid();

        config([
            'immersion.snapshots.enabled' => true,
            'cache.default' => 'file',
            'cache.stores.file' => ['driver' => 'file', 'path' => $path],
            'cache.ops_store' => 'file',
        ]);
        Cache::purge('file');

        try {
            foreach ($this->pages() as $url) {
                $this->get($url)->assertOk();
            }
            $filesAfterWarmUp = count(File::allFiles($path));

            foreach (range(1, 15) as $i) {
                $this->game->timelineEvents()->create([
                    'type' => 'email', 'trigger_offset_minutes' => 70 + $i, 'title' => "Extra {$i}",
                    'body_markdown' => 'x', 'delivery_mode' => 'all', 'sent_at' => now(),
                ]);
                GameRevision::bump($this->game->id);

                foreach ($this->pages() as $url) {
                    $this->get($url)->assertOk();
                }
            }

            // A part per kind, overwritten in place: more changes, same files
            // (plus what the new sessions' transcripts add, which is bounded by
            // the number of suspects).
            $this->assertLessThanOrEqual($filesAfterWarmUp + 12, count(File::allFiles($path)));
        } finally {
            Cache::purge('file');
            File::deleteDirectory($path);
        }
    }
}
