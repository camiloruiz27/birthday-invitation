<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Platform\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Buy first, confirm the email later — and where "later" is.
 *
 * Buying no longer needs a confirmed address (someone arriving from an ad pays
 * in the same sitting). What an unconfirmed address cannot do is make the
 * platform MAIL OTHER PEOPLE on its behalf: creating a game, sending player
 * links and starting one. Those are gated, and confirming returns the visitor
 * to what they were about to do.
 */
class GameVerificationGateTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    /** An account that owns the default case but has not confirmed its address. */
    private function unconfirmedOwner(): User
    {
        return $this->gameMaster(attributes: ['email_verified_at' => null]);
    }

    private function gameFor(User $user): Game
    {
        [$game] = $this->gameOwnedBy($user);

        return $game;
    }

    // --- The gate ----------------------------------------------------------

    public function test_an_unconfirmed_account_cannot_create_a_game(): void
    {
        $user = $this->unconfirmedOwner();

        $this->actingAs($user)
            ->get(route('immersion.gm.games.create'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('immersion.gm.games.store'), [
                'name' => 'Mesa del sábado',
                'case_slug' => 'steve-jacobs',
                'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
            ])
            ->assertRedirect(route('verification.notice'));

        $this->assertSame(0, Game::count());
    }

    public function test_an_unconfirmed_account_cannot_mail_players_their_links_or_start_a_game(): void
    {
        Mail::fake();
        $user = $this->unconfirmedOwner();
        [$game, $player] = $this->gameOwnedBy($user);

        $this->actingAs($user)
            ->post(route('immersion.gm.game.player.send-link', [$game, $player->id]))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('immersion.gm.game.send-all-links', $game))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('immersion.gm.game.start', $game))
            ->assertRedirect(route('verification.notice'));

        // Nothing went out and nothing started.
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
        $this->assertSame('draft', $game->fresh()->status);
    }

    public function test_an_unconfirmed_account_can_still_see_its_games(): void
    {
        $user = $this->unconfirmedOwner();
        $game = $this->gameFor($user);

        $this->actingAs($user)->get(route('immersion.gm.games.index'))->assertOk();

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('GameMaster/Game'));
    }

    public function test_a_confirmed_account_is_not_stopped(): void
    {
        $user = $this->gameMaster();

        $this->actingAs($user)->get(route('immersion.gm.games.create'))->assertOk();
    }

    public function test_the_gate_does_not_open_a_game_to_someone_who_does_not_own_it(): void
    {
        // The ownership check still comes first: a stranger learns nothing
        // from being asked to confirm an email.
        $owner = $this->gameMaster();
        $game = $this->gameFor($owner);
        $stranger = $this->gameMaster(attributes: ['email_verified_at' => null]);

        $this->actingAs($stranger)
            ->post(route('immersion.gm.game.send-all-links', $game))
            ->assertForbidden();
    }

    // --- Confirming picks up where the visitor was -------------------------

    public function test_confirming_returns_to_the_screen_the_gate_interrupted(): void
    {
        $user = $this->unconfirmedOwner();

        $this->actingAs($user)
            ->get(route('immersion.gm.games.create'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]))
            ->assertRedirect(route('immersion.gm.games.create'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->actingAs($user->fresh())->get(route('immersion.gm.games.create'))->assertOk();
    }

    /**
     * The link in the mail opens in the phone's own browser, not the in-app one
     * the visitor registered in, so it arrives with no session. This is the
     * chain that has to work: sign in there, come back to the very same signed
     * address, and get confirmed.
     */
    public function test_the_confirmation_link_opened_in_another_browser_survives_signing_in(): void
    {
        $user = User::factory()->unverified()->create(['password' => bcrypt('correct-horse-battery')]);
        $link = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        // A browser that has never seen the site: sent to sign in...
        $this->get($link)->assertRedirect(route('login'));
        $this->assertSame($link, session('url.intended'));

        // ...and signing in brings it back to the same signed address.
        $this->post(route('login'), ['email' => $user->email, 'password' => 'correct-horse-battery'])
            ->assertRedirect($link);

        $this->get($link)->assertRedirect();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    // --- The purchase itself needs no confirmation -------------------------

    public function test_an_unconfirmed_account_can_start_a_real_checkout_and_gets_a_pending_order(): void
    {
        config([
            'platform.payments.enabled' => true,
            'platform.payments.identity_key' => 'test-identity',
            'platform.payments.secret_key' => 'test-secret',
            'platform.simulated_checkout' => false,
        ]);
        Http::fake(['*/online/link/v1' => Http::response([
            'payload' => ['payment_link' => 'LNK_1', 'url' => 'https://checkout.bold.co/LNK_1'],
            'errors' => [],
        ], 200)]);

        $user = User::factory()->unverified()->create();
        $case = $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->get(route('cases.checkout.review', $case->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Payments/Review'));

        $this->actingAs($user)
            ->post(route('cases.acquire', $case->slug))
            ->assertRedirect('https://checkout.bold.co/LNK_1');

        $order = Order::sole();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(Order::STATUS_PENDING, $order->status);

        // What Bold is asked for never includes the account's address.
        Http::assertSent(fn ($request) => ! str_contains(json_encode($request->data()), $user->email));
    }

    public function test_buying_uses_up_the_saved_destination_so_confirming_later_goes_to_the_panel(): void
    {
        config([
            'platform.payments.enabled' => true,
            'platform.payments.identity_key' => 'test-identity',
            'platform.payments.secret_key' => 'test-secret',
            'platform.simulated_checkout' => false,
        ]);
        Http::fake(['*/online/link/v1' => Http::response([
            'payload' => ['payment_link' => 'LNK_1', 'url' => 'https://checkout.bold.co/LNK_1'],
            'errors' => [],
        ], 200)]);

        $case = $this->catalogCase('steve-jacobs');
        $user = User::factory()->unverified()->create([
            'attribution' => ['intended' => route('cases.checkout.review', $case->slug)],
        ]);

        $this->actingAs($user)->post(route('cases.acquire', $case->slug));

        $this->assertArrayNotHasKey('intended', (array) $user->fresh()->attribution);

        $this->actingAs($user->fresh())
            ->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]))
            ->assertRedirect(route('dashboard'));
    }

    // --- Coming back from the payment page ---------------------------------

    public function test_the_payment_return_page_needs_the_buyers_own_account(): void
    {
        $case = $this->catalogCase('steve-jacobs');
        $buyer = User::factory()->create();
        $order = Order::create([
            'user_id' => $buyer->id,
            'type' => Order::TYPE_CASE,
            'mystery_case_id' => $case->id,
            'amount' => 1000,
            'currency' => 'COP',
            'status' => Order::STATUS_PENDING,
            'reference' => 'REFXYZ',
            'provider' => 'bold',
        ]);

        // Back from the bank in a browser with no session: sign in first, and
        // the return address is remembered.
        $this->get(route('payments.confirm', $order))->assertRedirect(route('login'));
        $this->assertSame(route('payments.confirm', $order), session('url.intended'));

        // Someone else's account never sees another buyer's order.
        $this->actingAs(User::factory()->create())
            ->get(route('payments.confirm', $order))
            ->assertForbidden();
    }
}
