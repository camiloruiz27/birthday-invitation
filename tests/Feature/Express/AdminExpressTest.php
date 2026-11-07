<?php

namespace Tests\Feature\Express;

use App\Models\User;
use App\Modules\Express\Models\EventSetting;
use App\Modules\Express\Models\CtaClick;
use App\Modules\Express\Models\Player;
use App\Modules\Express\Models\Room;
use App\Modules\Express\Support\ExpressEvents;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class AdminExpressTest extends TestCase
{
    use CreatesGameMasters;
    use RefreshDatabase;

    private function admin(): User
    {
        $user = $this->gameMaster(attributes: ['email' => 'express-admin@example.com']);
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    protected function tearDown(): void
    {
        EventSetting::forgetCached('usb-cali-2026');

        parent::tearDown();
    }

    public function test_only_administrators_can_manage_the_express_promotion(): void
    {
        $this->actingAs($this->gameMaster())->get(route('admin.express'))->assertNotFound();
        $this->get(route('admin.express'))->assertNotFound();
    }

    public function test_an_administrator_can_choose_a_public_case_promotion_for_an_express_event(): void
    {
        PromoCode::create([
            'code' => 'FERIA-NUEVA',
            'discount_type' => PromoCode::DISCOUNT_PERCENT,
            'discount_value' => 20,
            'applies_to' => PromoCode::APPLIES_CASE,
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.express'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Express')
                ->where('events.0.slug', 'usb-cali-2026')
                ->where('codes.0.code', 'FERIA-NUEVA')
            );

        $this->actingAs($admin)
            ->patch(route('admin.express.update', 'usb-cali-2026'), ['promo_code' => 'feria-nueva'])
            ->assertRedirect(route('admin.express'));

        $this->assertDatabaseHas('express_event_settings', [
            'event_slug' => 'usb-cali-2026',
            'promo_code' => 'FERIA-NUEVA',
            'updated_by' => $admin->id,
        ]);
        $this->assertSame('FERIA-NUEVA', ExpressEvents::find('usb-cali-2026')['promo_code']);
    }

    public function test_an_invalid_or_private_promotion_cannot_be_shown_to_fair_players(): void
    {
        $private = PromoCode::create([
            'code' => 'SOLO-REFERIDO',
            'discount_type' => PromoCode::DISCOUNT_PERCENT,
            'discount_value' => 25,
            'assigned_user_id' => $this->gameMaster()->id,
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.express.update', 'usb-cali-2026'), ['promo_code' => $private->code])
            ->assertSessionHasErrors('promo_code');

        $this->assertDatabaseMissing('express_event_settings', ['event_slug' => 'usb-cali-2026']);
    }

    public function test_an_administrator_can_personalize_and_reopen_the_public_express_landing(): void
    {
        $this->actingAs($this->admin())
            ->patch(route('admin.express.update', 'usb-cali-2026'), [
                'display_name' => 'Noche de misterios',
                'landing_title' => 'Encuentren la verdad.',
                'landing_emphasis' => 'Antes de que termine el reloj.',
                'tagline' => 'Una experiencia breve para compartir en equipo.',
                'closed_title' => 'Volvemos pronto',
                'closed_reason' => 'Estamos preparando la siguiente ronda.',
                'active' => true,
                'ends_at_enabled' => false,
                'utm_medium' => '',
                'utm_campaign' => 'feria-pruebas-2026',
            ])
            ->assertRedirect(route('admin.express'));

        $event = ExpressEvents::find('usb-cali-2026');

        $this->assertSame('Noche de misterios', $event['name']);
        $this->assertSame('Encuentren la verdad.', $event['landing_title']);
        $this->assertSame('Antes de que termine el reloj.', $event['landing_emphasis']);
        $this->assertNull($event['ends_at']);
        $this->assertTrue(ExpressEvents::status($event)['open']);

        $query = [];
        parse_str((string) parse_url(ExpressEvents::ctaUrl($event), PHP_URL_QUERY), $query);
        $this->assertSame('express', $query['utm_source']);
        $this->assertArrayNotHasKey('utm_medium', $query);
        $this->assertSame('feria-pruebas-2026', $query['utm_campaign']);

        $this->get('/express?e=usb-cali-2026')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('event.name', 'Noche de misterios')
                ->where('event.landing_title', 'Encuentren la verdad.')
                ->where('event.closed_title', 'Volvemos pronto')
                ->where('enabled', true)
            );
    }

    public function test_statistics_are_filterable_by_event_and_frozen_promotion(): void
    {
        $room = Room::query()->create([
            'code' => 'ST4T5',
            'event_slug' => 'usb-cali-2026',
            'case_slug' => 'la-ultima-funcion',
            'status' => Room::FINISHED,
            'started_at_ms' => 1,
            'revealed_at' => now(),
            'promo_code' => 'FERIA-A',
            'utm_campaign' => 'campana-a',
            'campaign_snapshotted' => true,
        ]);
        $player = Player::query()->create([
            'room_id' => $room->id,
            'token' => '11111111-1111-4111-8111-111111111111',
            'nickname' => 'Ana',
            'audience' => 'A',
            'is_gm' => true,
            'joined_order' => 1,
        ]);
        CtaClick::query()->create(['room_id' => $room->id, 'player_id' => $player->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.express', ['event' => 'usb-cali-2026', 'promo_code' => 'FERIA-A']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('statistics.summary.rooms', 1)
                ->where('statistics.summary.finished', 1)
                ->where('statistics.summary.players', 1)
                ->where('statistics.summary.cta_clicks', 1)
                ->where('statistics.campaigns.0.promo_code', 'FERIA-A')
                ->where('statistics.cases.0.slug', 'la-ultima-funcion')
            );
    }

    public function test_the_final_cta_click_is_recorded_only_once_per_anonymous_player(): void
    {
        $room = Room::query()->create([
            'code' => 'CL1CK',
            'event_slug' => 'usb-cali-2026',
            'case_slug' => 'la-ultima-funcion',
            'status' => Room::FINISHED,
        ]);
        $player = Player::query()->create([
            'room_id' => $room->id,
            'token' => '22222222-2222-4222-8222-222222222222',
            'nickname' => 'Leo',
            'audience' => 'A',
            'is_gm' => true,
            'joined_order' => 1,
        ]);

        $this->post(route('express.cta.track', $player->token))->assertNoContent();
        $this->post(route('express.cta.track', $player->token))->assertNoContent();

        $this->assertDatabaseCount('express_cta_clicks', 1);
    }
}
