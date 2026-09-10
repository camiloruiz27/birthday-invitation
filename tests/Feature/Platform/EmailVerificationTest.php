<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Confirming the address on an account.
 *
 * The line is drawn at spending money, not at signing in: an unconfirmed
 * account can browse, sign in and read its own panel, and is only stopped
 * where a purchase would otherwise send the receipt, the player links and
 * the password recovery to an address nobody has proved they can read.
 */
class EmailVerificationTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function unverified(): User
    {
        return User::factory()->unverified()->create();
    }

    public function test_registering_sends_the_confirmation_mail(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'Isabella Figueroa',
            'email' => 'nueva@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ]);

        Notification::assertSentTo(User::firstWhere('email', 'nueva@example.com'), VerifyEmail::class);
    }

    /**
     * @dataProvider buyingRoutes
     */
    public function test_an_unconfirmed_account_cannot_reach_a_route_that_spends_money(string $method, string $route, array $parameters): void
    {
        config(['platform.payments.enabled' => true, 'platform.simulated_checkout' => true]);
        $this->catalogCase('steve-jacobs');

        $this->actingAs($this->unverified())
            ->call($method, route($route, $parameters))
            ->assertRedirect(route('verification.notice'));
    }

    public static function buyingRoutes(): array
    {
        return [
            'case review' => ['GET', 'cases.checkout.review', ['slug' => 'steve-jacobs']],
            'case purchase' => ['POST', 'cases.acquire', ['slug' => 'steve-jacobs']],
            'credits review' => ['GET', 'credits.checkout.review', []],
            'credits purchase' => ['POST', 'credits.purchase', []],
            'redeem a code' => ['POST', 'promo.redeem.store', []],
        ];
    }

    /**
     * The other half of the same decision: verification must not turn into a
     * wall in front of the whole product. Someone waiting on an email that is
     * slow to arrive can still use everything they already own.
     */
    public function test_an_unconfirmed_account_can_still_use_the_rest_of_the_site(): void
    {
        $user = $this->unverified();

        foreach (['dashboard', 'library', 'credits', 'promo.redeem', 'profile.edit'] as $name) {
            $this->actingAs($user)->get(route($name))->assertOk();
        }
    }

    public function test_following_the_link_in_the_mail_confirms_the_address(): void
    {
        $user = $this->unverified();

        $this->actingAs($user)
            ->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]))
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    /**
     * The link is signed, so it cannot be typed by hand or edited. Without
     * this, "verified" would mean nothing more than knowing your own user id.
     */
    public function test_an_unsigned_verification_link_confirms_nothing(): void
    {
        $user = $this->unverified();

        $this->actingAs($user)
            ->get(route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))
            ->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    /**
     * A verified account that changes its address has proved nothing about
     * the NEW one. Without this, confirming once and then editing the email
     * would be a way to end up verified on an address nobody owns.
     */
    public function test_changing_the_email_removes_the_confirmation_and_blocks_buying_again(): void
    {
        Notification::fake();
        config(['platform.simulated_checkout' => true]);
        $this->catalogCase('steve-jacobs');

        $user = $this->userWithoutAccess();
        $this->assertTrue($user->hasVerifiedEmail());

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'otra-direccion@example.com',
        ])->assertRedirect();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($user->fresh(), VerifyEmail::class);

        $this->actingAs($user->fresh())
            ->post(route('cases.acquire', 'steve-jacobs'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_the_notice_screen_sends_the_link_again(): void
    {
        Notification::fake();

        $user = $this->unverified();

        $this->actingAs($user)->post(route('verification.send'))->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_an_already_verified_account_is_sent_on_rather_than_shown_the_notice(): void
    {
        $this->actingAs($this->userWithoutAccess())
            ->get(route('verification.notice'))
            ->assertRedirect(route('dashboard'));
    }
}
