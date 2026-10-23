<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\PublishedCases;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The catalog and the library filter in the browser (lib/caseDiscovery.js), so
 * the server's whole job is to hand over the full list with what a search and
 * an ordering need, and to keep a filter from costing database connections.
 */
class CaseDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalog_ships_every_published_case_with_what_search_and_ordering_need(): void
    {
        $this->case('archivo-aureo', [
            'name' => 'Archivo Áureo',
            'tagline' => 'Un legado',
            'description' => 'Un expediente perdido en la montaña.',
        ]);
        $this->case('not-published', ['published_at' => null]);

        // The query string is the browser's business: the server must answer
        // the same whatever filters the address carries.
        foreach ([[], ['q' => 'nada', 'difficulty' => 'hard', 'sort' => 'price_asc']] as $query) {
            $this->get(route('cases.index', $query))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Public/Catalog')
                    ->has('cases', 1)
                    ->where('cases.0.slug', 'archivo-aureo')
                    ->where('cases.0.search', 'Un legado Un expediente perdido en la montaña.')
                    ->has('cases.0.published_at')
                    ->has('cases.0.mechanics.0.slug')
                    ->has('cases.0.price_amount')
                );
        }
    }

    public function test_browsing_and_filtering_the_catalog_opens_no_database_connection(): void
    {
        $this->case('warm-up');
        PublishedCases::forget();
        $this->get(route('cases.index'))->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('cases.index', ['q' => 'warm', 'sort' => 'name_desc']))->assertOk();

        $this->assertSame([], DB::getQueryLog());
    }

    public function test_the_catalog_prices_are_in_the_visitors_market(): void
    {
        config([
            'platform.market.international_enabled' => true,
            'platform.market.force' => 'INTL',
        ]);

        $this->case('usd-case', ['price_amount' => 49900]);

        $this->get(route('cases.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('cases.0.currency', 'USD'));
    }

    public function test_the_library_only_ships_owned_cases_with_their_acquisition_date_and_current_price(): void
    {
        $user = User::factory()->create();
        $older = $this->case('older-owned', ['price_amount' => 49900]);
        $newer = $this->case('newer-owned', ['price_amount' => 69900]);
        $this->case('not-owned', ['price_amount' => 100]);

        $this->grant($user, $older, now()->subWeek());
        $this->grant($user, $newer, now());

        $this->actingAs($user)
            ->get(route('library', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Library')
                ->has('cases', 2)
                ->where('cases.0.slug', 'newer-owned')
                ->where('cases.0.price_amount', 69900)
                ->where('cases.0.currency', 'COP')
                ->where('cases.1.slug', 'older-owned')
                ->where('cases.1.acquired_at', $older->entitlements()->first()->granted_at->toIso8601String())
            );
    }

    public function test_an_expired_entitlement_is_not_in_the_library(): void
    {
        $user = User::factory()->create();

        $this->grant($user, $this->case('current'), now()->subDays(10));
        $this->grant($user, $this->case('expired'), now()->subMonth(), now()->subDay());

        $this->actingAs($user)
            ->get(route('library'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('cases', 1)
                ->where('cases.0.slug', 'current')
            );
    }

    public function test_the_library_reads_the_owned_cases_in_a_single_query(): void
    {
        $user = User::factory()->create();
        $this->grant($user, $this->case('one'), now());
        $this->grant($user, $this->case('two'), now());

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->assertCount(2, $user->library());
        $this->assertCount(1, DB::getQueryLog());
    }

    private function grant(User $user, MysteryCase $case, $grantedAt, $expiresAt = null): void
    {
        Entitlement::create([
            'user_id' => $user->id,
            'mystery_case_id' => $case->id,
            'source' => Entitlement::SOURCE_GRANT,
            'granted_at' => $grantedAt,
            'expires_at' => $expiresAt,
        ]);
    }

    private function case(string $slug, array $attributes = []): MysteryCase
    {
        return MysteryCase::create(array_merge([
            'slug' => $slug,
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'difficulty' => 'medium',
            'duration_minutes' => 65,
            'min_players' => 3,
            'max_players' => 6,
            'mechanics' => ['inbox'],
            'price_amount' => 59900,
            'currency' => 'COP',
            'published_at' => now(),
        ], $attributes));
    }
}
