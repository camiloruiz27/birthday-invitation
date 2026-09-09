<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_the_landing_page_is_public(): void
    {
        $this->catalogCase('steve-jacobs');

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Landing')
                ->has('featured', 1)
                ->has('mechanics')
            );
    }

    public function test_the_marketing_pages_are_public(): void
    {
        foreach (['cases.index', 'mechanics', 'ai', 'pricing'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_the_ai_page_separates_mechanics_that_use_ai(): void
    {
        $this->get(route('ai'))
            ->assertOk()
            ->assertInertia(function (Assert $page) {
                $page->component('Public/Ai');

                // Being explicit about this split is the point of the page.
                foreach ($page->toArray()['props']['withAi'] as $mechanic) {
                    $this->assertTrue($mechanic['ai']);
                }

                foreach ($page->toArray()['props']['withoutAi'] as $mechanic) {
                    $this->assertFalse($mechanic['ai']);
                }
            });
    }

    public function test_the_catalog_lists_only_published_cases(): void
    {
        $this->catalogCase('steve-jacobs');
        MysteryCase::create(['slug' => 'draft-case', 'name' => 'Borrador', 'published_at' => null]);

        $this->get(route('cases.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Catalog')
                ->has('cases', 1)
                ->where('cases.0.slug', 'steve-jacobs')
            );
    }

    public function test_a_case_page_shows_its_mechanics_resolved(): void
    {
        $this->catalogCase('steve-jacobs');

        $this->get(route('cases.show', 'steve-jacobs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/CaseDetail')
                ->where('case.slug', 'steve-jacobs')
                ->where('case.uses_ai', true)
                ->where('owned', false)
                ->has('case.mechanics.0.name')
                ->has('case.mechanics.0.detail')
            );
    }

    public function test_an_unpublished_case_page_is_not_reachable(): void
    {
        MysteryCase::create(['slug' => 'draft-case', 'name' => 'Borrador', 'published_at' => null]);

        $this->get(route('cases.show', 'draft-case'))->assertNotFound();
        $this->get(route('cases.show', 'does-not-exist'))->assertNotFound();
    }

    public function test_a_case_page_reports_ownership_to_its_owner(): void
    {
        $user = $this->gameMaster();

        $this->actingAs($user)
            ->get(route('cases.show', 'steve-jacobs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('owned', true));
    }

    public function test_public_pages_stay_public_for_signed_in_users(): void
    {
        // Browsing the catalog while signed in must not bounce to the panel.
        $this->actingAs($this->gameMaster())
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/Landing'));
    }

    public function test_the_catalog_payload_carries_no_internal_columns(): void
    {
        $this->catalogCase('steve-jacobs');

        $response = $this->get(route('cases.index'))->assertOk();
        $case = $response->viewData('page')['props']['cases'][0];

        // The catalog is public, so what crosses is an explicit list.
        $this->assertSame(
            ['slug', 'name', 'tagline', 'cover_url', 'difficulty', 'duration_minutes',
                'min_players', 'max_players', 'price_amount', 'currency', 'mechanics'],
            array_keys($case)
        );
    }
}
