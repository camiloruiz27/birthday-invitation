<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\PlayerNotices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * With the per-envelope email gone, the platform says what is new itself, on
 * whatever screen the player is on. The notices are derived from what the game
 * already holds — nothing is stored — and ride on every player page; these pin
 * down what they contain, who sees what, and that they stay light.
 */
class PlayerNoticesTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const CASE_SLUG = 'habitacion-314';

    /** @return array{0: Game, 1: Player, 2: Player} */
    private function table(array $gameAttributes = []): array
    {
        $game = Game::create($gameAttributes + [
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'status' => 'running',
            'interrogation_enabled' => true,
        ]);

        $ana = $game->players()->create([
            'name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-ana', 'role_slug' => 'forense',
        ]);
        $beto = $game->players()->create([
            'name' => 'Beto', 'email' => 'beto@example.com', 'access_token' => 'tok-beto', 'role_slug' => 'analista',
        ]);

        return [$game, $ana, $beto];
    }

    private function sent(Game $game, array $attributes): \App\Modules\Immersion\Models\TimelineEvent
    {
        return $game->timelineEvents()->create($attributes + [
            'type' => 'email',
            'trigger_offset_minutes' => 0,
            'title' => 'Sobre',
            'body_markdown' => 'CUERPO-SECRETO',
            'delivery_mode' => 'all',
            'sent_at' => now(),
        ]);
    }

    private function keys(array $notices): array
    {
        return array_column($notices['items'], 'key');
    }

    public function test_an_envelope_sent_to_everyone_is_a_notice_for_everyone(): void
    {
        [$game, $ana, $beto] = $this->table();
        $event = $this->sent($game, ['title' => 'Sobre 1']);

        foreach ([$ana, $beto] as $player) {
            $notices = PlayerNotices::for($player);

            $this->assertSame(["e{$event->id}"], $this->keys($notices));
            $this->assertSame('envelope', $notices['items'][0]['kind']);
            $this->assertSame($event->id, $notices['items'][0]['event_id']);
        }
    }

    public function test_a_private_clue_is_only_a_notice_for_the_player_it_was_drawn_for(): void
    {
        [$game, $ana, $beto] = $this->table();
        $this->sent($game, ['delivery_mode' => 'random_player', 'delivered_to_player_id' => $ana->id]);

        $this->assertCount(1, PlayerNotices::for($ana)['items']);
        $this->assertCount(0, PlayerNotices::for($beto)['items']);
    }

    public function test_a_role_envelope_is_only_a_notice_for_that_role(): void
    {
        [$game, $ana, $beto] = $this->table();
        $this->sent($game, ['delivery_mode' => 'role_slug', 'target_role_slug' => 'forense']);

        $this->assertCount(1, PlayerNotices::for($ana)['items']);
        $this->assertCount(0, PlayerNotices::for($beto)['items']);
    }

    public function test_an_envelope_not_yet_sent_is_not_a_notice(): void
    {
        [$game, $ana] = $this->table();
        $this->sent($game, ['sent_at' => null]);

        $this->assertSame([], PlayerNotices::for($ana)['items']);
    }

    public function test_the_accusations_envelope_is_a_notice_even_though_it_is_not_in_the_inbox(): void
    {
        [$game, $ana] = $this->table();
        $unlock = $this->sent($game, ['type' => 'unlock', 'title' => 'Fase de acusaciones habilitada']);

        $notices = PlayerNotices::for($ana);

        $this->assertSame(["a{$unlock->id}"], $this->keys($notices));
        $this->assertSame('accusation', $notices['items'][0]['kind']);
        $this->assertTrue($notices['accusations_open']);
        $this->assertCount(0, $ana->inboxEvents(), 'It never was an inbox item: that is why the notice exists.');
    }

    public function test_the_envelope_that_opens_the_interrogations_says_so_separately(): void
    {
        [$game, $ana] = $this->table();
        $cta = $this->sent($game, ['cta_interrogation' => true, 'title' => 'Las personas del caso']);

        $this->assertEqualsCanonicalizing(["e{$cta->id}", "i{$cta->id}"], $this->keys(PlayerNotices::for($ana)));
    }

    public function test_no_interrogation_notice_when_the_game_has_no_interrogations(): void
    {
        [$game, $ana] = $this->table(['interrogation_enabled' => false]);
        $cta = $this->sent($game, ['cta_interrogation' => true]);

        $this->assertSame(["e{$cta->id}"], $this->keys(PlayerNotices::for($ana)));
    }

    public function test_the_reveal_and_the_epilogue_are_notices_and_accusing_is_remembered(): void
    {
        [$game, $ana, $beto] = $this->table(['ending_type' => Game::ENDING_EPILOGUE]);
        $game->update(['ending_revealed_at' => now()]);
        Accusation::create([
            'game_id' => $game->id,
            'player_id' => $ana->id,
            'suspect_slug' => 'x',
            'suspect_name' => 'X',
            'motive' => 'm',
            'weapon' => 'w',
            'submitted_at' => now(),
            'epilogue_status' => 'ready',
        ]);

        $forAna = PlayerNotices::for($ana);
        $forBeto = PlayerNotices::for($beto);

        $this->assertEqualsCanonicalizing(['solution', 'epilogue'], $this->keys($forAna));
        $this->assertSame(['solution'], $this->keys($forBeto), 'Only their own epilogue.');
        $this->assertTrue($forAna['accused']);
        $this->assertFalse($forBeto['accused']);
        $this->assertTrue($forAna['ending_revealed']);
    }

    public function test_it_never_carries_what_an_envelope_says(): void
    {
        [$game, $ana] = $this->table();
        $this->sent($game, ['title' => 'Sobre 1']);

        $this->assertStringNotContainsString('CUERPO-SECRETO', json_encode(PlayerNotices::for($ana)));
    }

    public function test_every_player_page_carries_the_notices_and_other_pages_do_not(): void
    {
        [$game, $ana] = $this->table();
        $event = $this->sent($game, ['title' => 'Sobre 1']);

        $this->get(route('immersion.player.inbox', 'tok-ana'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notices.items', 1)
                ->where('notices.items.0.key', "e{$event->id}")
                ->where('notices.accusations_open', false));

        $this->get(route('immersion.player.accusation', 'tok-ana'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('notices.items', 1));

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('notices', null));
    }

    public function test_the_notices_cost_a_fixed_handful_of_queries_whatever_the_game_holds(): void
    {
        [$game, $ana] = $this->table();

        foreach (range(1, 12) as $i) {
            $this->sent($game, ['title' => "Sobre {$i}"]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        PlayerNotices::for($ana->fresh());
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(6, $count, 'A query per envelope would add up on every page of every player.');
    }

    // --- the way back to the console -----------------------------------------

    public function test_the_game_master_who_plays_is_marked_as_the_owner_for_the_way_back_to_the_panel(): void
    {
        $user = $this->gameMaster(self::CASE_SLUG);
        $game = Game::create([
            'user_id' => $user->id,
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'status' => 'running',
            'mode' => Game::MODE_AUTOMATIC,
        ]);
        $game->players()->create([
            'name' => 'Yo', 'email' => $user->email, 'access_token' => 'tok-owner', 'is_owner' => true,
        ]);

        $this->actingAs($user)
            ->get(route('immersion.player.inbox', 'tok-owner'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('player.is_owner', true)
                ->where('game.id', $game->id)
                ->where('auth.user.id', $user->id));

        // The destination exists and is theirs.
        $this->actingAs($user)->get(route('immersion.gm.game.show', $game))->assertOk();
    }

    public function test_an_invited_player_is_never_the_owner_and_has_no_account_to_go_back_with(): void
    {
        [$game] = $this->table();

        $this->get(route('immersion.player.inbox', 'tok-ana'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('player.is_owner', false)
                ->where('auth.user', null));
    }
}
