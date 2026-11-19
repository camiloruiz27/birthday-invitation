<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Support\MarketingConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * How a person gives and withdraws the authorisation for promotional email:
 * the unticked box at registration, the link in the mail, the switch in the
 * profile.
 */
class MarketingPreferenceTest extends TestCase
{
    use RefreshDatabase;

    private function register(array $extra = []): User
    {
        $this->post(route('register'), array_merge([
            'name' => 'Isabella',
            'email' => 'isabella@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'accept_terms' => true,
        ], $extra))->assertRedirect();

        return User::firstWhere('email', 'isabella@example.com');
    }

    public function test_registering_without_the_box_does_not_authorise_offers(): void
    {
        $user = $this->register();

        $this->assertNull($user->marketing_consent_at);
        $this->assertFalse($user->acceptsMarketing());
    }

    public function test_registering_with_the_box_records_the_authorisation_and_where_it_came_from(): void
    {
        $user = $this->register(['accept_marketing' => true]);

        $this->assertNotNull($user->marketing_consent_at);
        $this->assertSame('register', $user->marketing_consent_source);
        $this->assertTrue($user->acceptsMarketing());
    }

    public function test_the_marketing_box_is_not_a_condition_of_the_account(): void
    {
        $this->register(['accept_marketing' => false]);

        $this->assertAuthenticated();
    }

    public function test_the_signed_link_unsubscribes_without_signing_in(): void
    {
        $user = User::factory()->marketingConsent()->create();

        $this->get(MarketingConsent::unsubscribeUrl($user))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Marketing/Unsubscribed'));

        $this->assertNotNull($user->fresh()->marketing_opted_out_at);
        $this->assertFalse($user->fresh()->acceptsMarketing());
    }

    public function test_the_link_is_idempotent_and_keeps_the_first_withdrawal_date(): void
    {
        $user = User::factory()->marketingConsent()->create();
        $url = MarketingConsent::unsubscribeUrl($user);

        $this->get($url)->assertOk();
        $first = $user->fresh()->marketing_opted_out_at;

        $this->travel(2)->days();
        $this->get($url)->assertOk();

        $this->assertEquals($first, $user->fresh()->marketing_opted_out_at);
    }

    public function test_an_unsigned_or_tampered_link_does_nothing(): void
    {
        $user = User::factory()->marketingConsent()->create();
        $other = User::factory()->marketingConsent()->create();

        $this->get(route('marketing.unsubscribe', $user))->assertForbidden();

        // A valid signature for one account cannot be reused for another.
        $signed = URL::signedRoute('marketing.unsubscribe', ['user' => $user->id]);
        $this->get(str_replace('/'.$user->id.'?', '/'.$other->id.'?', $signed))->assertForbidden();

        $this->assertNull($user->fresh()->marketing_opted_out_at);
        $this->assertNull($other->fresh()->marketing_opted_out_at);
    }

    public function test_the_profile_shows_the_current_choice(): void
    {
        $subscribed = User::factory()->marketingConsent()->create();
        $never = User::factory()->create();

        $this->actingAs($subscribed)->get(route('profile.edit'))
            ->assertInertia(fn (Assert $page) => $page->where('marketing', true));

        $this->actingAs($never)->get(route('profile.edit'))
            ->assertInertia(fn (Assert $page) => $page->where('marketing', false));
    }

    public function test_the_profile_switch_turns_offers_on_and_off(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.marketing'), ['subscribed' => true])
            ->assertSessionHas('status');
        $this->assertTrue($user->fresh()->acceptsMarketing());
        $this->assertSame('profile', $user->fresh()->marketing_consent_source);

        $this->actingAs($user)->patch(route('profile.marketing'), ['subscribed' => false])
            ->assertSessionHas('status');
        $this->assertFalse($user->fresh()->acceptsMarketing());
    }

    public function test_coming_back_from_the_profile_clears_an_earlier_unsubscribe(): void
    {
        $user = User::factory()->marketingConsent()->create();
        MarketingConsent::revoke($user);

        $this->actingAs($user)->patch(route('profile.marketing'), ['subscribed' => true]);

        $this->assertTrue($user->fresh()->acceptsMarketing());
        $this->assertNull($user->fresh()->marketing_opted_out_at);
    }

    public function test_the_profile_switch_needs_a_signed_in_user_and_a_value(): void
    {
        $this->patch(route('profile.marketing'), ['subscribed' => true])->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->patch(route('profile.marketing'), [])
            ->assertSessionHasErrors('subscribed');
    }
}
