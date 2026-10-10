<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * An ad that promises a discount has to keep the promise on the page it lands
 * on. The code travels on the link and is remembered; the landing shows what
 * it does — but only when the checkout would honor it, because a promise the
 * checkout then refuses is worse than showing no promise at all.
 */
class AdLandingOfferTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const CASE = 'desaparecida-en-directo';

    private function discount(string $code, string $type = 'percent', int $value = 20, array $extra = []): PromoCode
    {
        return PromoCode::create($extra + [
            'code' => $code,
            'discount_type' => $type,
            'discount_value' => $value,
            'active' => true,
        ]);
    }

    private function landing(string $query = '')
    {
        $case = $this->catalogCase(self::CASE);

        return [$case, $this->get(route('ads.case', $case->slug).$query)];
    }

    public function test_a_percent_code_on_the_link_shows_the_discount_and_the_final_price(): void
    {
        $this->discount('BIENVENIDA20');

        [$case, $response] = $this->landing('?promo_code=bienvenida20&utm_source=tiktok');

        $final = (int) floor($case->price_amount * 80 / 100);

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('offer.code', 'BIENVENIDA20')
            ->where('offer.kind', 'percent')
            ->where('offer.value', 20)
            ->where('offer.final_amount', $final)
            ->where('offer.discount_amount', $case->price_amount - $final));
    }

    public function test_a_fixed_code_shows_its_amount(): void
    {
        $case = $this->catalogCase(self::CASE);
        $this->discount('MENOS5MIL', 'fixed', 5000);

        $this->get(route('ads.case', $case->slug).'?promo_code=MENOS5MIL')
            ->assertInertia(fn (Assert $page) => $page
                ->where('offer.kind', 'fixed')
                ->where('offer.final_amount', max(0, $case->price_amount - 5000)));
    }

    public function test_the_offer_is_remembered_on_the_next_visit_without_the_parameter(): void
    {
        $this->discount('BIENVENIDA20');
        $case = $this->catalogCase(self::CASE);

        $this->get(route('ads.case', $case->slug).'?promo_code=BIENVENIDA20');

        $this->get(route('ads.case', $case->slug))
            ->assertInertia(fn (Assert $page) => $page->where('offer.code', 'BIENVENIDA20'));
    }

    public function test_the_expiry_date_travels_with_the_offer(): void
    {
        $this->discount('VENCE', 'percent', 15, ['expires_at' => now()->addDays(3)]);

        [, $response] = $this->landing('?promo_code=VENCE');

        $response->assertInertia(fn (Assert $page) => $page->whereType('offer.expires_at', 'string'));
    }

    public function test_a_code_the_checkout_would_refuse_shows_nothing(): void
    {
        $this->discount('APAGADO', 'percent', 20, ['active' => false]);
        $this->discount('VENCIDO', 'percent', 20, ['expires_at' => now()->subDay()]);
        $this->discount('AGOTADO', 'percent', 20, ['max_redemptions' => 1, 'redemptions_count' => 1]);
        $this->discount('SOLOCREDITOS', 'percent', 20, ['applies_to' => PromoCode::APPLIES_CREDITS]);

        foreach (['APAGADO', 'VENCIDO', 'AGOTADO', 'SOLOCREDITOS', 'NOEXISTE'] as $code) {
            [, $response] = $this->landing("?promo_code={$code}");

            $response->assertInertia(fn (Assert $page) => $page->where('offer', null));
        }
    }

    public function test_a_code_the_visitor_already_used_up_shows_nothing(): void
    {
        $promo = $this->discount('UNAVEZ', 'percent', 20, ['max_redemptions_per_user' => 1]);
        $user = $this->userWithoutAccess();

        PromoCodeRedemption::create(['promo_code_id' => $promo->id, 'user_id' => $user->id]);

        $case = $this->catalogCase(self::CASE);

        $this->actingAs($user)
            ->get(route('ads.case', $case->slug).'?promo_code=UNAVEZ')
            ->assertInertia(fn (Assert $page) => $page->where('offer', null));
    }

    public function test_a_gift_for_this_case_shows_as_free_and_one_for_another_does_not(): void
    {
        PromoCode::create(['code' => 'REGALOESTE', 'grants_case_slug' => self::CASE, 'active' => true]);
        PromoCode::create(['code' => 'REGALOOTRO', 'grants_case_slug' => 'habitacion-314', 'active' => true]);
        PromoCode::create(['code' => 'SOLOCRED', 'grants_credits' => 50, 'active' => true]);

        [, $free] = $this->landing('?promo_code=REGALOESTE');
        $free->assertInertia(fn (Assert $page) => $page
            ->where('offer.kind', 'gift')
            ->where('offer.final_amount', 0));

        foreach (['REGALOOTRO', 'SOLOCRED'] as $code) {
            [, $response] = $this->landing("?promo_code={$code}");
            $response->assertInertia(fn (Assert $page) => $page->where('offer', null));
        }
    }

    public function test_no_code_means_no_offer(): void
    {
        $this->discount('BIENVENIDA20');

        [, $response] = $this->landing();

        $response->assertInertia(fn (Assert $page) => $page->where('offer', null));
    }

    public function test_the_platform_landing_announces_the_offer_and_prices_each_card_with_it(): void
    {
        $this->catalogCase(self::CASE);
        $this->discount('BIENVENIDA20');

        $this->get(route('ads.platform').'?promo_code=BIENVENIDA20')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('offer.code', 'BIENVENIDA20')
                ->has('featured.0', fn (Assert $card) => $card
                    ->whereType('offer_amount', 'integer')
                    ->etc()));

        // A visitor with no code in their session (the first visit above is remembered).
        $this->flushSession();

        $this->get(route('ads.platform'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('offer', null)
                ->has('featured.0', fn (Assert $card) => $card->where('offer_amount', null)->etc()));
    }
}
