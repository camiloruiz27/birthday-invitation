<?php

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_three_legal_documents_are_public(): void
    {
        $documents = [
            'privacy' => 'Public/Legal/Privacy',
            'terms' => 'Public/Legal/Terms',
            'cookies' => 'Public/Legal/Cookies',
        ];

        foreach ($documents as $routeName => $component) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component($component)->has('legal'));
        }
    }

    public function test_the_pages_that_name_the_responsible_person_are_kept_out_of_search_results(): void
    {
        // A search for the owner's name must not lead to the policy or the
        // terms, where Ley 1581 and Ley 1480 require that name to appear.
        foreach (['privacy', 'terms'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, noarchive');
        }

        // The cookie policy names nobody.
        $this->get(route('cookies'))->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_the_documents_carry_the_controller_identity_from_config(): void
    {
        config([
            'legal.entity_name' => 'Ejemplo S.A.S.',
            'legal.entity_id' => '900.000.000-1',
            'legal.address' => 'Calle 1 # 2-3',
            'legal.city' => 'Bogotá',
            'legal.email' => 'datos@example.com',
            'legal.phone' => '+57 300 000 0000',
            'legal.privacy_version' => '7.0',
        ]);

        // Ley 1581 (data policy) and Ley 1480 art. 50 (online sale) both ask
        // for who is responsible; it is shipped as data, never hard-coded.
        $this->get(route('privacy'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('legal.entity_name', 'Ejemplo S.A.S.')
                ->where('legal.entity_id', '900.000.000-1')
                ->where('legal.address', 'Calle 1 # 2-3')
                ->where('legal.city', 'Bogotá')
                ->where('legal.email', 'datos@example.com')
                ->where('legal.phone', '+57 300 000 0000')
                ->where('legal.version', '7.0')
            );
    }

    public function test_each_document_reports_its_own_version(): void
    {
        config([
            'legal.privacy_version' => '1.1',
            'legal.terms_version' => '2.2',
            'legal.cookies_version' => '3.3',
        ]);

        $this->get(route('privacy'))->assertInertia(fn (Assert $page) => $page->where('legal.version', '1.1'));
        $this->get(route('terms'))->assertInertia(fn (Assert $page) => $page->where('legal.version', '2.2'));
        $this->get(route('cookies'))->assertInertia(fn (Assert $page) => $page->where('legal.version', '3.3'));
    }

    public function test_an_unset_identity_reaches_the_page_empty_so_it_shows_as_pending(): void
    {
        config(['legal.entity_name' => '', 'legal.email' => '']);

        // The page renders a visible [PENDIENTE] marker for an empty value.
        $this->get(route('terms'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('legal.entity_name', '')
                ->where('legal.email', '')
            );
    }

    public function test_the_legal_pages_stay_public_for_signed_in_users(): void
    {
        $this->actingAs(\App\Models\User::factory()->create())
            ->get(route('privacy'))
            ->assertOk();
    }

    public function test_analytics_scripts_are_not_in_the_page_before_consent(): void
    {
        config([
            'platform.analytics.ga_measurement_id' => 'G-TEST123456',
            'platform.analytics.clarity_project_id' => 'abcdefghij',
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        // Ley 1581: nothing may reach Google or Microsoft before the visitor
        // accepts. The shell only publishes the ids; consent.js injects the
        // scripts after consent.
        $this->assertStringNotContainsString('googletagmanager.com', $html);
        $this->assertStringNotContainsString('clarity.ms', $html);
        $this->assertStringContainsString('name="mc-analytics"', $html);
        $this->assertStringContainsString('data-ga="G-TEST123456"', $html);
        $this->assertStringContainsString('data-clarity="abcdefghij"', $html);
    }

    public function test_basic_cookieless_measurement_is_on_by_default_and_can_be_switched_off(): void
    {
        config([
            'platform.analytics.ga_measurement_id' => 'G-TEST123456',
            'platform.analytics.clarity_project_id' => 'abcdefghij',
        ]);

        // On by default: both tools may load with storage denied, so the
        // owner still learns that someone arrived and from where.
        $this->assertTrue(config('platform.analytics.basic_measurement'));
        $this->assertStringContainsString('data-basic="1"', $this->get(route('home'))->getContent());

        // The strictest reading of Ley 1581: nothing loads before consent.
        config(['platform.analytics.basic_measurement' => false]);
        $this->assertStringContainsString('data-basic="0"', $this->get(route('home'))->getContent());
    }

    public function test_the_terms_state_the_real_game_quota(): void
    {
        config(['immersion.games.max_per_case' => 4]);

        $this->get(route('terms'))
            ->assertInertia(fn (Assert $page) => $page->where('gamesPerCase', 4));
    }

    public function test_the_marketing_pages_state_the_real_game_quota_not_unlimited(): void
    {
        config(['immersion.games.max_per_case' => 5]);

        // "Partidas ilimitadas" used to be printed here while the quota was 6.
        $this->get(route('pricing'))
            ->assertInertia(fn (Assert $page) => $page->where('gamesPerCase', 5));
    }

    public function test_no_analytics_meta_is_emitted_when_no_ids_are_configured(): void
    {
        config([
            'platform.analytics.ga_measurement_id' => '',
            'platform.analytics.clarity_project_id' => '',
        ]);

        $this->assertStringNotContainsString(
            'mc-analytics',
            $this->get(route('home'))->assertOk()->getContent()
        );
    }
}
