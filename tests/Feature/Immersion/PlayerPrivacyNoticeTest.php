<?php

namespace Tests\Feature\Immersion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Players have no account, so the privacy notice on their first visit is where
 * their authorization (Ley 1581 de 2012) is captured.
 */
class PlayerPrivacyNoticeTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_a_new_player_has_not_accepted_the_notice(): void
    {
        [, $player] = $this->gameOwnedBy($this->gameMaster());

        // The page is handed `privacy_accepted_at: null`, which is what makes
        // PlayerLayout show the notice.
        $this->get(route('immersion.player.inbox', $player->access_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('player.privacy_accepted_at', null)
            );
    }

    public function test_accepting_records_when_and_which_version(): void
    {
        config(['legal.privacy_version' => '4.2']);
        [, $player] = $this->gameOwnedBy($this->gameMaster());

        $this->post(route('immersion.player.privacy.accept', $player->access_token))
            ->assertRedirect();

        $player = $player->fresh();

        $this->assertNotNull($player->privacy_accepted_at);
        $this->assertSame('4.2', $player->privacy_version);

        $this->get(route('immersion.player.inbox', $player->access_token))
            ->assertInertia(fn (Assert $page) => $page
                ->where('player.privacy_accepted_at', fn ($value) => $value !== null)
            );
    }

    public function test_accepting_twice_keeps_the_original_evidence(): void
    {
        [, $player] = $this->gameOwnedBy($this->gameMaster());

        $this->post(route('immersion.player.privacy.accept', $player->access_token));
        $first = $player->fresh()->privacy_accepted_at;

        $this->travel(2)->days();
        config(['legal.privacy_version' => '9.9']);

        $this->post(route('immersion.player.privacy.accept', $player->access_token));

        $player = $player->fresh();

        $this->assertTrue($first->equalTo($player->privacy_accepted_at));
        $this->assertNotSame('9.9', $player->privacy_version);
    }

    public function test_an_unknown_token_cannot_accept_for_anyone(): void
    {
        $this->post(route('immersion.player.privacy.accept', 'no-such-token'))->assertNotFound();
    }

    public function test_one_players_acceptance_does_not_cover_another(): void
    {
        $user = $this->gameMaster();
        [$game, $first] = $this->gameOwnedBy($user);
        $second = $game->players()->create([
            'name' => 'Player Two',
            'email' => 'two@example.com',
            'access_token' => 'second-token',
        ]);

        $this->post(route('immersion.player.privacy.accept', $first->access_token));

        $this->assertNotNull($first->fresh()->privacy_accepted_at);
        $this->assertNull($second->fresh()->privacy_accepted_at);
    }
}
