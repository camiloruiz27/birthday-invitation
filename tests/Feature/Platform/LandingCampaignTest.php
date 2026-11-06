<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\LandingCampaign;
use App\Modules\Platform\Models\LandingCampaignCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class LandingCampaignTest extends TestCase
{
    use CreatesGameMasters;
    use RefreshDatabase;

    private function campaign(): array
    {
        $paid = collect(['el-brindis-22-14', 'proyecto-boreal', 'desaparecida-en-directo'])
            ->map(fn (string $slug) => $this->catalogCase($slug));
        $free = $this->catalogCase('la-caja-de-donaciones');
        $promo = PromoCode::create(['code' => 'ANUNCIO50', 'discount_type' => 'percent', 'discount_value' => 50, 'active' => true, 'applies_to' => PromoCode::APPLIES_CASE]);
        $campaign = LandingCampaign::create(['promo_code_id' => $promo->id, 'name' => 'Anuncio noviembre', 'headline' => 'Resuelvan algo distinto esta noche.']);

        foreach ($paid as $index => $case) {
            $campaign->selections()->create(['mystery_case_id' => $case->id, 'placement' => LandingCampaignCase::PAID, 'sort_order' => $index, 'recommended' => $index === 0]);
        }
        $campaign->selections()->create(['mystery_case_id' => $free->id, 'placement' => LandingCampaignCase::FREE, 'sort_order' => 0]);

        return [$campaign, $promo, $free, $paid];
    }

    public function test_a_valid_campaign_code_renders_its_three_paid_cases_and_secondary_free_case(): void
    {
        [$campaign] = $this->campaign();

        $this->get(route('ads.platform').'?promo_code=ANUNCIO50')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/AdLanding')
                ->where('campaign.id', $campaign->id)
                ->has('campaign.paid', 3)
                ->has('campaign.free', 1)
                ->where('campaign.recommended.recommended', true)
                ->where('campaign.recommended.offer_amount', 24950));
    }

    public function test_claiming_a_free_case_from_a_campaign_records_the_campaign_without_redeeming_its_code(): void
    {
        [$campaign, $promo, $free] = $this->campaign();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('ads.platform').'?promo_code=ANUNCIO50')->assertOk();
        $this->actingAs($user)->post(route('cases.acquire', $free->slug))->assertRedirect(route('library'));

        $order = Order::where('user_id', $user->id)->sole();
        $this->assertSame($campaign->id, $order->landing_campaign_id);
        $this->assertNull($order->promo_code_id);
        $this->assertSame(0, $order->amount);
        $this->assertSame($promo->id, $campaign->promo_code_id);
    }

    public function test_a_landing_campaign_must_be_paused_before_it_can_be_deleted(): void
    {
        [$campaign] = $this->campaign();
        $admin = $this->gameMaster(attributes: ['email' => 'campaign-admin@example.com']);
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->delete(route('admin.landing-campaigns.destroy', $campaign))
            ->assertSessionHasErrors('campaign');
        $this->assertDatabaseHas('landing_campaigns', ['id' => $campaign->id]);

        $this->actingAs($admin)->patch(route('admin.landing-campaigns.toggle', $campaign));
        $this->actingAs($admin)->delete(route('admin.landing-campaigns.destroy', $campaign));

        $this->assertDatabaseMissing('landing_campaigns', ['id' => $campaign->id]);
    }
}
