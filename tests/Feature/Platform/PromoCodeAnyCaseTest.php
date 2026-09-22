<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Console\Commands\CreatePromoCode;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * A gift code that lets the redeemer choose which case they get, instead of
 * one fixed at creation time — see RedeemPromoCode::resolveGiftCase() and
 * the redeem screen's two-step form.
 */
class PromoCodeAnyCaseTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_create_promo_code_command_rejects_combining_case_and_any_case(): void
    {
        $this->artisan(CreatePromoCode::class, [
            'code' => 'AMBOS',
            '--case' => 'steve-jacobs',
            '--any-case' => true,
        ])->assertFailed();

        $this->assertSame(0, PromoCode::count());
    }

    public function test_create_promo_code_command_creates_an_any_case_gift(): void
    {
        $this->artisan(CreatePromoCode::class, [
            'code' => 'elige2026',
            '--any-case' => true,
            '--credits' => '10',
        ])->assertSuccessful();

        $promo = PromoCode::where('code', 'ELIGE2026')->sole();

        $this->assertTrue($promo->grants_any_case);
        $this->assertNull($promo->grants_case_slug);
        $this->assertTrue($promo->isGift());
    }

    public function test_the_redeem_screen_asks_which_case_before_spending_a_use(): void
    {
        $this->catalogCase('steve-jacobs');
        $case = $this->catalogCase('el-brindis-22-14');
        $promo = PromoCode::create(['code' => 'ELIGE', 'grants_any_case' => true]);
        $user = $this->userWithoutAccess();

        $this->actingAs($user)
            ->post(route('promo.redeem.store'), ['code' => 'ELIGE'])
            ->assertSessionHasErrors('case_slug');

        // Nothing was spent by that first, incomplete attempt.
        $this->assertSame(0, $promo->fresh()->redemptions_count);
        $this->assertFalse($user->fresh()->ownsCase($case));
    }

    public function test_submitting_code_and_case_together_grants_the_chosen_case(): void
    {
        $this->catalogCase('steve-jacobs');
        $case = $this->catalogCase('el-brindis-22-14');
        PromoCode::create(['code' => 'ELIGE', 'grants_any_case' => true]);
        $user = $this->userWithoutAccess();

        $this->actingAs($user)
            ->post(route('promo.redeem.store'), ['code' => 'ELIGE', 'case_slug' => 'el-brindis-22-14'])
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->ownsCase($case));
        $this->assertSame(
            Entitlement::SOURCE_PROMO,
            Entitlement::where('user_id', $user->id)->where('mystery_case_id', $case->id)->first()->source
        );
    }

    public function test_choosing_a_case_the_user_already_owns_is_rejected_without_spending_the_code(): void
    {
        $case = $this->catalogCase('el-brindis-22-14');
        $promo = PromoCode::create(['code' => 'ELIGE', 'grants_any_case' => true]);
        $user = $this->gameMaster($case->slug);

        $this->assertTrue($user->ownsCase($case));

        $caught = null;

        try {
            app(RedeemPromoCode::class)->redeemGift($user, 'ELIGE', $case->slug);
        } catch (PromoCodeException $exception) {
            $caught = $exception;
        }

        $this->assertNotNull($caught);
        $this->assertSame(0, $promo->fresh()->redemptions_count);
    }

    public function test_an_unpublished_or_unknown_case_slug_is_rejected(): void
    {
        $promo = PromoCode::create(['code' => 'ELIGE', 'grants_any_case' => true]);
        $user = $this->userWithoutAccess();

        $this->expectException(PromoCodeException::class);

        app(RedeemPromoCode::class)->redeemGift($user, 'ELIGE', 'no-existe-este-caso');

        $this->assertSame(0, $promo->fresh()->redemptions_count);
    }

    public function test_the_redeem_page_excludes_cases_the_visitor_already_owns(): void
    {
        $owned = $this->catalogCase('steve-jacobs');
        $this->catalogCase('el-brindis-22-14');
        $user = $this->gameMaster($owned->slug);

        $this->actingAs($user)
            ->get(route('promo.redeem'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Redeem')
                ->where('cases', fn ($cases) => collect($cases)->pluck('slug')->doesntContain('steve-jacobs')
                    && collect($cases)->pluck('slug')->contains('el-brindis-22-14'))
            );
    }
}
