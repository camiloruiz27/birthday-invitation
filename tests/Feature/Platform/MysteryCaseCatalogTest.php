<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\Game;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MysteryCaseCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_publishes_the_installed_manifests_into_the_catalog(): void
    {
        $this->assertDatabaseCount('mystery_cases', 0);

        $this->artisan('platform:sync-cases')->assertSuccessful();

        $case = MysteryCase::firstWhere('slug', 'steve-jacobs');

        $this->assertNotNull($case);
        $this->assertSame('¿Qué le sucedió a Steve Jacobs?', $case->name);
        $this->assertSame('1.0', $case->content_version);
        $this->assertSame(90, $case->duration_minutes);
        $this->assertSame('medium', $case->difficulty);
        $this->assertSame(89000, $case->price_amount);
        $this->assertSame('COP', $case->currency);
        $this->assertContains('interrogation', $case->mechanics);
        $this->assertTrue($case->isPublished());
    }

    public function test_sync_is_idempotent(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();
        $this->artisan('platform:sync-cases')->assertSuccessful();

        $this->assertDatabaseCount('mystery_cases', 1);
    }

    public function test_sync_refreshes_content_fields_but_preserves_pricing(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        // Stand in for an admin editing the catalog: a new price, taken off
        // sale, and a stale name.
        MysteryCase::firstWhere('slug', 'steve-jacobs')->update([
            'name' => 'Nombre viejo',
            'price_amount' => 120000,
            'published_at' => null,
        ]);

        $this->artisan('platform:sync-cases')->assertSuccessful();

        $case = MysteryCase::firstWhere('slug', 'steve-jacobs');

        // Content comes back from the manifest...
        $this->assertSame('¿Qué le sucedió a Steve Jacobs?', $case->name);
        // ...but the commercial decisions made in the database stand.
        $this->assertSame(120000, $case->price_amount);
        $this->assertNull($case->published_at);
    }

    public function test_prices_flag_deliberately_reapplies_the_manifest(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        MysteryCase::firstWhere('slug', 'steve-jacobs')->update([
            'price_amount' => 120000,
            'published_at' => null,
        ]);

        $this->artisan('platform:sync-cases --prices')->assertSuccessful();

        $case = MysteryCase::firstWhere('slug', 'steve-jacobs');

        $this->assertSame(89000, $case->price_amount);
        $this->assertTrue($case->isPublished());
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->artisan('platform:sync-cases --dry-run')->assertSuccessful();

        $this->assertDatabaseCount('mystery_cases', 0);
    }

    public function test_published_scope_hides_unpublished_and_future_cases(): void
    {
        MysteryCase::create(['slug' => 'live', 'name' => 'Live', 'published_at' => now()->subDay()]);
        MysteryCase::create(['slug' => 'draft', 'name' => 'Draft', 'published_at' => null]);
        MysteryCase::create(['slug' => 'scheduled', 'name' => 'Scheduled', 'published_at' => now()->addDay()]);

        $slugs = MysteryCase::published()->pluck('slug')->all();

        $this->assertSame(['live'], $slugs);
    }

    public function test_ordered_scope_uses_sort_order_then_name(): void
    {
        MysteryCase::create(['slug' => 'c', 'name' => 'Carlos', 'sort_order' => 1]);
        MysteryCase::create(['slug' => 'a', 'name' => 'Ana', 'sort_order' => 1]);
        MysteryCase::create(['slug' => 'z', 'name' => 'Zoe', 'sort_order' => 0]);

        $this->assertSame(['z', 'a', 'c'], MysteryCase::ordered()->pluck('slug')->all());
    }

    public function test_it_reaches_the_playable_definition_behind_the_product(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        $case = MysteryCase::firstWhere('slug', 'steve-jacobs');

        $this->assertTrue($case->isPlayable());
        $this->assertSame('SF 554301', $case->definition()->code());
        $this->assertCount(9, $case->definition()->suspects());
    }

    public function test_a_catalog_row_without_its_manifest_is_flagged_unplayable(): void
    {
        $orphan = MysteryCase::create(['slug' => 'removed-case', 'name' => 'Removed']);

        $this->assertFalse($orphan->isPlayable());
        $this->assertNull($orphan->coverUrl());
    }

    public function test_cover_falls_back_to_the_victim_portrait(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        $case = MysteryCase::firstWhere('slug', 'steve-jacobs');

        $this->assertSame('/immersion/steve-jacobs/photos/steve-jacobs.jpg', $case->coverUrl());
    }

    public function test_games_are_joined_to_their_case_by_slug(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        $game = Game::create(['name' => 'Partida', 'status' => 'draft']);

        $case = MysteryCase::firstWhere('slug', 'steve-jacobs');

        $this->assertTrue($case->games->contains($game));
    }
}
