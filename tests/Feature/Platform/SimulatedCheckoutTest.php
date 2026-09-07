<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The stand-in for checkout while there is no payment provider. It must be
 * impossible to reach when disabled, and its grants must stay distinguishable
 * from real purchases in the data.
 */
class SimulatedCheckoutTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['platform.simulated_checkout' => true]);
    }

    public function test_a_signed_in_user_can_acquire_a_published_case(): void
    {
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'))
            ->assertRedirect(route('immersion.gm.dashboard'));

        $this->assertTrue($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_a_simulated_acquisition_is_marked_as_a_grant_not_a_purchase(): void
    {
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));

        // Nothing was paid, so the data must never claim it was.
        $this->assertSame(Entitlement::SOURCE_GRANT, Entitlement::first()->source);
    }

    public function test_acquiring_twice_does_not_duplicate_access(): void
    {
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'))
            ->assertRedirect(route('cases.show', 'steve-jacobs'));

        $this->assertSame(1, Entitlement::count());
    }

    public function test_it_is_unreachable_when_the_simulation_is_off(): void
    {
        config(['platform.simulated_checkout' => false]);

        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'))
            ->assertNotFound();

        $this->assertFalse($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_it_requires_signing_in(): void
    {
        $this->catalogCase('steve-jacobs');

        $this->post(route('cases.acquire', 'steve-jacobs'))
            ->assertRedirect(route('login'));

        $this->assertSame(0, Entitlement::count());
    }

    public function test_an_unpublished_case_cannot_be_acquired(): void
    {
        $user = $this->userWithoutAccess();
        MysteryCase::create(['slug' => 'draft-case', 'name' => 'Borrador', 'published_at' => null]);

        $this->actingAs($user)
            ->post(route('cases.acquire', 'draft-case'))
            ->assertNotFound();

        $this->assertSame(0, Entitlement::count());
    }

    public function test_the_case_page_does_not_offer_acquisition_when_disabled(): void
    {
        config(['platform.simulated_checkout' => false]);
        $this->catalogCase('steve-jacobs');

        $this->actingAs($this->userWithoutAccess())
            ->get(route('cases.show', 'steve-jacobs'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('canSimulatePurchase', false));
    }
}
