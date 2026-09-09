<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The review screen every real purchase lands on before Bold — what's being
 * bought, any discount code applied, the actual total. It is the fix for a
 * real gap: the original implementation had no place for a buyer to see a
 * discount's effect before being redirected away to pay.
 */
class CheckoutReviewTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.payments.enabled' => true,
            'platform.simulated_checkout' => false,
        ]);
    }

    public function test_the_case_review_screen_shows_the_original_price_without_a_code(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->get(route('cases.checkout.review', 'steve-jacobs'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Payments/Review')
                ->where('type', 'case')
                ->where('original_amount', $case->price_amount)
                ->where('final_amount', $case->price_amount)
                ->where('discount_amount', null)
                ->where('promo_code', null)
            );
    }

    public function test_the_case_review_screen_applies_a_valid_discount_code(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'REVISION20', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 20]);

        $this->actingAs($user)
            ->get(route('cases.checkout.review', 'steve-jacobs').'?promo_code=revision20')
            ->assertInertia(fn (Assert $page) => $page
                ->where('promo_code', 'REVISION20')
                ->where('final_amount', (int) floor($case->price_amount * 0.8))
                ->where('discount_amount', (int) ceil($case->price_amount * 0.2))
            );

        // Previewing must never claim anything — only the real purchase does.
        $this->assertSame(0, PromoCode::sole()->redemptions_count);
        $this->assertSame(0, PromoCodeRedemption::count());
    }

    public function test_the_case_review_screen_shows_an_error_for_an_invalid_code_without_crashing(): void
    {
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->get(route('cases.checkout.review', 'steve-jacobs').'?promo_code=NOEXISTE')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('promo_code', null)
                ->where('promo_error', 'Ese código no existe.')
            );
    }

    public function test_the_case_review_screen_flags_an_already_owned_case_instead_of_pricing_it(): void
    {
        $user = $this->gameMaster('steve-jacobs');

        $this->actingAs($user)
            ->get(route('cases.checkout.review', 'steve-jacobs'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Payments/Review')
                ->where('alreadyOwned', true)
            );
    }

    public function test_the_case_review_screen_is_unreachable_when_payments_are_disabled(): void
    {
        config(['platform.payments.enabled' => false]);
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->get(route('cases.checkout.review', 'steve-jacobs'))
            ->assertNotFound();
    }

    public function test_the_credits_review_screen_shows_the_package_and_applies_a_discount(): void
    {
        $user = $this->gameMaster();
        $package = collect((array) config('platform.credit_packages'))->first();
        PromoCode::create(['code' => 'CREDDESC', 'discount_type' => PromoCode::DISCOUNT_FIXED, 'discount_value' => 5000]);

        $this->actingAs($user)
            ->get(route('credits.checkout.review', ['package' => $package['id'], 'promo_code' => 'creddesc']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Payments/Review')
                ->where('type', 'credit_package')
                ->where('package_id', $package['id'])
                ->where('promo_code', 'CREDDESC')
                ->where('final_amount', (int) $package['price_amount'] - 5000)
            );
    }

    public function test_the_credits_review_screen_404s_for_an_unknown_package(): void
    {
        $user = $this->gameMaster();

        $this->actingAs($user)
            ->get(route('credits.checkout.review', ['package' => 'no-existe']))
            ->assertNotFound();
    }
}
