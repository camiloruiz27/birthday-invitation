<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class AdminPromoCodesTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function admin(): User
    {
        $user = $this->gameMaster(attributes: ['email' => 'admin@example.com']);

        // forceFill because is_admin is deliberately not mass-assignable.
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    public function test_the_codes_screen_is_invisible_to_everyone_else(): void
    {
        $this->actingAs($this->gameMaster())
            ->get(route('admin.codes'))
            ->assertNotFound();

        $this->get(route('admin.codes'))->assertNotFound();
    }

    public function test_an_administrator_reaches_the_codes_screen(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.codes'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/PromoCodes')
                ->has('metrics.summary')
                ->has('metrics.codes')
                ->has('metrics.recentRedemptions')
            );
    }

    public function test_summary_counts_gifts_discounts_and_flags_an_exhausted_code(): void
    {
        PromoCode::create(['code' => 'REGALO', 'grants_credits' => 10]);
        PromoCode::create(['code' => 'DESCUENTO', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 20]);
        PromoCode::create([
            'code' => 'AGOTADO',
            'grants_credits' => 5,
            'max_redemptions' => 1,
            'redemptions_count' => 1,
        ]);

        $redeemer = $this->userWithoutAccess();
        PromoCodeRedemption::create([
            'promo_code_id' => PromoCode::where('code', 'AGOTADO')->sole()->id,
            'user_id' => $redeemer->id,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.codes'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('metrics.summary.total', 3)
                ->where('metrics.summary.active', 3)
                ->where('metrics.summary.gifts', 2)
                ->where('metrics.summary.discounts', 1)
                ->where('metrics.summary.exhausted', 1)
                ->where('metrics.summary.total_redemptions', 1)
            );
    }

    public function test_recent_redemptions_show_who_redeemed_which_code(): void
    {
        $promo = PromoCode::create(['code' => 'QUIEN', 'grants_credits' => 10]);
        $redeemer = $this->userWithoutAccess(attributes: [
            'name' => 'Rachel Miller',
            'email' => 'rachel@example.com',
        ]);
        PromoCodeRedemption::create(['promo_code_id' => $promo->id, 'user_id' => $redeemer->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.codes'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('metrics.recentRedemptions.0.code', 'QUIEN')
                ->where('metrics.recentRedemptions.0.user_name', 'Rachel Miller')
                ->where('metrics.recentRedemptions.0.user_email', 'rachel@example.com')
                ->where('metrics.recentRedemptions.0.has_order', false)
            );
    }
}
