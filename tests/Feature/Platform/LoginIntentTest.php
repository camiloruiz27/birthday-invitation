<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * A customer who comes back from an ad or a case page and signs in (instead of
 * registering) lands on that case's checkout, not on the panel — the same
 * `?case=` the register screen understands, through the same PurchaseIntent.
 */
class LoginIntentTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['password' => bcrypt('correct-horse-battery')]);
    }

    private function signIn(User $user)
    {
        return $this->post(route('login'), ['email' => $user->email, 'password' => 'correct-horse-battery']);
    }

    public function test_signing_in_from_a_case_link_lands_on_that_cases_checkout(): void
    {
        config(['platform.payments.enabled' => true]);
        $case = $this->catalogCase('steve-jacobs');
        $user = $this->user();

        $this->get(route('login', ['case' => $case->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->where('case', $case->slug));

        $this->assertSame(route('cases.checkout.review', $case->slug), session('url.intended'));

        $this->signIn($user)->assertRedirect(route('cases.checkout.review', $case->slug));
    }

    public function test_with_payments_off_it_leads_to_the_case_page(): void
    {
        config(['platform.payments.enabled' => false]);
        $case = $this->catalogCase('steve-jacobs');

        $this->get(route('login', ['case' => $case->slug]));

        $this->assertSame(route('cases.show', $case->slug), session('url.intended'));
    }

    public function test_a_plain_login_still_goes_to_the_panel(): void
    {
        $this->get(route('login'))
            ->assertInertia(fn (Assert $page) => $page->where('case', null));

        $this->signIn($this->user())->assertRedirect(route('dashboard'));
    }

    public function test_an_unknown_unpublished_or_foreign_destination_is_ignored(): void
    {
        $case = $this->catalogCase('steve-jacobs');

        foreach (['no-existe', 'https://evil.example/', '//evil.example', '../admin', ''] as $value) {
            $this->get(route('login', ['case' => $value]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->where('case', null));

            $this->assertNull(session('url.intended'), "case={$value} must not set a destination");
        }

        $case->update(['published_at' => null]);

        $this->get(route('login', ['case' => $case->slug]))
            ->assertInertia(fn (Assert $page) => $page->where('case', null));
        $this->assertNull(session('url.intended'));
    }

    public function test_the_register_screen_hands_the_same_slug_to_its_page(): void
    {
        $case = $this->catalogCase('steve-jacobs');

        $this->get(route('register', ['case' => $case->slug]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Register')
                ->where('case', $case->slug));

        $this->get(route('register'))
            ->assertInertia(fn (Assert $page) => $page->where('case', null));
    }

    public function test_a_login_link_keeps_a_promo_code_like_any_other_page(): void
    {
        $this->get(route('login').'?promo_code=tiktok20&utm_source=tiktok')->assertOk();

        $this->assertSame('TIKTOK20', session('pending_promo'));
        $this->assertSame('tiktok', session('attribution')['first']['utm_source']);
    }
}
