<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Jobs\SendPlayerAccessLinks;
use App\Modules\Immersion\Mail\PlayerAccessLinkMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The console's "Enviar por correo" (one player) and "Enviar a todos"
 * (the roster) buttons — a mailed alternative to copy-pasting the link.
 */
class SendPlayerAccessLinkTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->gameMaster();
    }

    public function test_sending_one_players_link_queues_only_that_player(): void
    {
        Queue::fake();

        [$game, $player] = $this->gameOwnedBy($this->owner);

        // The id, not the model: route() would otherwise resolve Player's
        // own route key (access_token — its inbox credential), while the
        // route itself is bound {player:id} for this Game-Master-only URL.
        $this->actingAs($this->owner)
            ->post(route('immersion.gm.game.player.send-link', [$game->id, $player->id]))
            ->assertRedirect();

        Queue::assertPushed(
            SendPlayerAccessLinks::class,
            fn (SendPlayerAccessLinks $job) => $job->playerIds === [$player->id]
        );
    }

    public function test_sending_to_all_queues_every_player_in_the_game(): void
    {
        Queue::fake();

        [$game, $first] = $this->gameOwnedBy($this->owner);
        $second = $game->players()->create([
            'name' => 'Player Two',
            'email' => 'player-two@example.com',
            'access_token' => 'test-token-second',
        ]);

        $this->actingAs($this->owner)
            ->post(route('immersion.gm.game.send-all-links', $game))
            ->assertRedirect();

        Queue::assertPushed(
            SendPlayerAccessLinks::class,
            fn (SendPlayerAccessLinks $job) => $job->playerIds === [$first->id, $second->id]
        );
    }

    public function test_the_job_mails_each_player_their_own_link(): void
    {
        Mail::fake();

        [, $player] = $this->gameOwnedBy($this->owner);

        (new SendPlayerAccessLinks([$player->id]))->handle();

        Mail::assertSent(
            PlayerAccessLinkMail::class,
            fn (PlayerAccessLinkMail $mail) => $mail->player->id === $player->id
                && $mail->hasTo($player->email)
        );
    }

    public function test_send_link_is_denied_to_another_game_master(): void
    {
        [$game, $player] = $this->gameOwnedBy($this->owner);
        $intruder = $this->gameMaster(attributes: ['email' => 'intruder@example.com']);

        $this->actingAs($intruder)
            ->post(route('immersion.gm.game.player.send-link', [$game->id, $player->id]))
            ->assertForbidden();
    }

    public function test_send_link_requires_authentication(): void
    {
        [$game, $player] = $this->gameOwnedBy($this->owner);

        $this->post(route('immersion.gm.game.player.send-link', [$game->id, $player->id]))
            ->assertRedirect(route('login'));
    }
}
