<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Creating and switching off codes from the admin screen — what a campaign
 * with frequent, varied discounts needs without a terminal.
 */
class AdminPromoCodesManageTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function admin(): User
    {
        $user = $this->gameMaster(attributes: ['email' => 'admin@example.com']);
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    private function discountForm(array $overrides = []): array
    {
        return $overrides + [
            'mode' => 'single',
            'kind' => 'discount',
            'code' => 'tiktok20',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'max_per_user' => 1,
        ];
    }

    public function test_only_an_administrator_can_create_or_switch_codes(): void
    {
        $promo = PromoCode::create(['code' => 'X', 'discount_type' => 'percent', 'discount_value' => 5]);

        // A stranger is sent to sign in, like any other signed-in-only page...
        $this->post(route('admin.codes.store'), $this->discountForm())->assertRedirect(route('login'));
        $this->patch(route('admin.codes.toggle', $promo))->assertRedirect(route('login'));

        // ...and an ordinary account learns nothing about the area at all.
        $this->actingAs($this->gameMaster())
            ->post(route('admin.codes.store'), $this->discountForm())
            ->assertNotFound();
        $this->actingAs($this->gameMaster())
            ->patch(route('admin.codes.toggle', $promo))
            ->assertNotFound();

        $this->assertSame(1, PromoCode::count());
        $this->assertTrue($promo->fresh()->active);
    }

    public function test_an_administrator_creates_a_percent_discount_for_credits_with_an_expiry(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.codes.store'), $this->discountForm([
                'applies_to' => 'credits',
                'expires_at' => now()->addDays(7)->format('Y-m-d'),
                'max_redemptions' => 100,
                'note' => 'Campaña tokens',
            ]))
            ->assertRedirect(route('admin.codes'));

        $promo = PromoCode::where('code', 'TIKTOK20')->sole();
        $this->assertSame('percent', $promo->discount_type);
        $this->assertSame(20, $promo->discount_value);
        $this->assertSame('credits', $promo->applies_to);
        $this->assertSame(now()->addDays(7)->format('Y-m-d').' 23:59:59', $promo->expires_at->format('Y-m-d H:i:s'));
        $this->assertSame(100, $promo->max_redemptions);
        $this->assertSame(1, $promo->max_redemptions_per_user);
        $this->assertSame('Campaña tokens', $promo->note);
    }

    public function test_an_administrator_creates_a_fixed_discount_for_cases(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.codes.store'), $this->discountForm([
                'code' => 'DESCUENTO5K',
                'discount_type' => 'fixed',
                'discount_value' => 5000,
                'applies_to' => 'case',
                'max_per_user' => 0,
            ]))
            ->assertRedirect(route('admin.codes'));

        $promo = PromoCode::where('code', 'DESCUENTO5K')->sole();
        $this->assertSame('fixed', $promo->discount_type);
        $this->assertSame(5000, $promo->discount_value);
        $this->assertSame('case', $promo->applies_to);
        $this->assertNull($promo->max_redemptions_per_user);
    }

    public function test_an_administrator_creates_a_gift_with_a_case_and_credits(): void
    {
        $this->catalogCase('steve-jacobs');

        $this->actingAs($this->admin())
            ->post(route('admin.codes.store'), [
                'mode' => 'single',
                'kind' => 'gift',
                'code' => 'REGALO1',
                'grants_case_slug' => 'steve-jacobs',
                'grants_credits' => 85,
                'max_per_user' => 1,
            ])
            ->assertRedirect(route('admin.codes'));

        $promo = PromoCode::where('code', 'REGALO1')->sole();
        $this->assertSame('steve-jacobs', $promo->grants_case_slug);
        $this->assertSame(85, $promo->grants_credits);
        $this->assertNull($promo->discount_type);
    }

    public function test_a_gift_may_leave_the_case_to_whoever_redeems_it(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.codes.store'), [
                'mode' => 'single',
                'kind' => 'gift',
                'code' => 'ELIGE',
                'grants_any_case' => true,
                'max_per_user' => 1,
            ])
            ->assertRedirect(route('admin.codes'));

        $this->assertTrue(PromoCode::where('code', 'ELIGE')->sole()->grants_any_case);
    }

    public function test_a_batch_creates_single_use_codes_and_shows_them_once(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.codes.store'), [
                'mode' => 'batch',
                'kind' => 'discount',
                'prefix' => 'tiktok',
                'count' => 5,
                'discount_type' => 'percent',
                'discount_value' => 15,
                // Ignored for a batch: every code is single-use.
                'max_redemptions' => 99,
            ])
            ->assertRedirect(route('admin.codes'))
            ->assertSessionHas('created_codes');

        $codes = PromoCode::where('code', 'like', 'TIKTOK-%')->get();
        $this->assertCount(5, $codes);
        $this->assertTrue($codes->every(fn (PromoCode $promo) => $promo->max_redemptions === 1
            && $promo->max_redemptions_per_user === 1));

        $this->actingAs($admin)
            ->withSession(['created_codes' => $codes->pluck('code')->all()])
            ->get(route('admin.codes'))
            ->assertInertia(fn (Assert $page) => $page->has('created', 5));
    }

    public function test_the_form_is_refused_when_it_would_make_an_impossible_code(): void
    {
        $this->catalogCase('steve-jacobs');
        $admin = $this->admin();

        // A percentage cannot exceed 100.
        $this->actingAs($admin)
            ->post(route('admin.codes.store'), $this->discountForm(['discount_value' => 150]))
            ->assertSessionHasErrors('discount_value');

        // A gift has to give something.
        $this->actingAs($admin)
            ->post(route('admin.codes.store'), ['mode' => 'single', 'kind' => 'gift', 'code' => 'VACIO'])
            ->assertSessionHasErrors('grants_credits');

        // A fixed case or the redeemer's choice, not both.
        $this->actingAs($admin)
            ->post(route('admin.codes.store'), [
                'mode' => 'single',
                'kind' => 'gift',
                'code' => 'AMBOS',
                'grants_case_slug' => 'steve-jacobs',
                'grants_any_case' => true,
            ])
            ->assertSessionHasErrors('grants_case_slug');

        // An expiry in the past is a typo, not a plan.
        $this->actingAs($admin)
            ->post(route('admin.codes.store'), $this->discountForm(['expires_at' => now()->subDays(2)->format('Y-m-d')]))
            ->assertSessionHasErrors('expires_at');

        // Only what a link can carry.
        $this->actingAs($admin)
            ->post(route('admin.codes.store'), $this->discountForm(['code' => 'CON ESPACIOS!']))
            ->assertSessionHasErrors('code');

        // A scope that does not exist.
        $this->actingAs($admin)
            ->post(route('admin.codes.store'), $this->discountForm(['applies_to' => 'todo']))
            ->assertSessionHasErrors('applies_to');

        // A case that does not exist.
        $this->actingAs($admin)
            ->post(route('admin.codes.store'), ['mode' => 'single', 'kind' => 'gift', 'code' => 'NOCASE', 'grants_case_slug' => 'nada'])
            ->assertSessionHasErrors('grants_case_slug');

        $this->assertSame(0, PromoCode::where('code', '!=', 'X')->count());
    }

    public function test_a_code_name_cannot_be_reused(): void
    {
        PromoCode::create(['code' => 'TIKTOK20', 'discount_type' => 'percent', 'discount_value' => 5]);

        $this->actingAs($this->admin())
            ->post(route('admin.codes.store'), $this->discountForm())
            ->assertSessionHasErrors('code');

        $this->assertSame(1, PromoCode::count());
    }

    public function test_a_batch_needs_a_prefix_and_a_sensible_count(): void
    {
        $admin = $this->admin();
        $base = ['mode' => 'batch', 'kind' => 'discount', 'discount_type' => 'percent', 'discount_value' => 10];

        $this->actingAs($admin)->post(route('admin.codes.store'), $base + ['count' => 5])
            ->assertSessionHasErrors('prefix');

        $this->actingAs($admin)->post(route('admin.codes.store'), $base + ['prefix' => 'X', 'count' => 501])
            ->assertSessionHasErrors('count');

        $this->assertSame(0, PromoCode::count());
    }

    public function test_a_code_can_be_switched_off_and_on_again_without_deleting_it(): void
    {
        $admin = $this->admin();
        $promo = PromoCode::create(['code' => 'APAGAR', 'discount_type' => 'percent', 'discount_value' => 5]);

        $this->actingAs($admin)->patch(route('admin.codes.toggle', $promo))->assertRedirect();
        $this->assertFalse($promo->fresh()->active);

        $this->actingAs($admin)->patch(route('admin.codes.toggle', $promo))->assertRedirect();
        $this->assertTrue($promo->fresh()->active);
    }

    public function test_the_screen_reports_expiry_scope_and_what_each_code_has_cost(): void
    {
        $user = $this->userWithoutAccess();
        $promo = PromoCode::create([
            'code' => 'CAMP',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'applies_to' => 'case',
            'expires_at' => now()->subDay(),
        ]);

        // One paid order that used it, one still pending (which cost nothing).
        Order::create([
            'user_id' => $user->id, 'type' => 'case', 'amount' => 40000, 'list_amount' => 50000,
            'discount_amount' => 10000, 'promo_code_id' => $promo->id, 'status' => Order::STATUS_APPROVED,
            'reference' => 'REF1', 'provider' => 'bold',
        ]);
        Order::create([
            'user_id' => $user->id, 'type' => 'case', 'amount' => 40000, 'list_amount' => 50000,
            'discount_amount' => 10000, 'promo_code_id' => $promo->id, 'status' => Order::STATUS_PENDING,
            'reference' => 'REF2', 'provider' => 'bold',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.codes'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('metrics.codes.0.code', 'CAMP')
                ->where('metrics.codes.0.expired', true)
                ->where('metrics.codes.0.applies_to', 'case')
                ->where('metrics.codes.0.paid_orders', 1)
                ->where('metrics.codes.0.discounted', 10000)
                ->where('metrics.codes.0.collected', 40000)
                ->has('cases')
            );
    }
}
