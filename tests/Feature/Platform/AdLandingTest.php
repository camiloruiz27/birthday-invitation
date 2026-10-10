<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The page paid ads land on: /jugar/{slug} for one case, /jugar for the
 * platform. Beyond rendering, what matters here is that it is safe and honest
 * to point money at — unpublished cases 404, a case with no ad copy still gets
 * a complete page, link previews are in the server's HTML (crawlers never run
 * the app), and a campaign link is remembered like on any other page.
 */
class AdLandingTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    // A case with written ad copy and a cover (so it has share images).
    private const WITH_AD = 'desaparecida-en-directo';

    // A case with NO cover art, which is what the fallbacks are exercised on.
    // Every installed manifest carries ad copy now, so a test that needs a
    // case without it clears it first.
    private const WITHOUT_AD = 'steve-jacobs';

    // --- Rendering ---------------------------------------------------------

    public function test_a_case_landing_renders_with_the_cases_data(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $this->get(route('ads.case', $case->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/AdLanding')
                ->where('mode', 'case')
                ->where('case.slug', $case->slug)
                ->where('case.name', $case->name)
                ->where('case.price_amount', $case->price_amount)
                ->where('owned', false)
                ->has('gamesPerCase')
                ->has('canPurchase')
                ->has('canSimulatePurchase')
            );
    }

    public function test_the_platform_landing_offers_three_published_cases_that_lead_to_their_own_page(): void
    {
        $this->catalogCase(self::WITH_AD);
        MysteryCase::where('slug', '!=', self::WITH_AD)->update(['published_at' => now()]);

        $this->get(route('ads.platform'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/AdLanding')
                ->where('mode', 'platform')
                ->where('case', null)
                ->has('featured', 3)
                ->has('featured.0', fn (Assert $card) => $card
                    ->has('slug')
                    ->has('name')
                    ->has('price_amount')
                    // The web-sized cover, not the 2 MB original.
                    ->has('thumb_url')
                    ->etc())
            );
    }

    public function test_the_platform_landing_never_lists_an_unpublished_case(): void
    {
        $this->catalogCase('steve-jacobs');
        MysteryCase::where('slug', '!=', 'steve-jacobs')->update(['published_at' => null]);

        $this->get(route('ads.platform'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('featured', 1));

        MysteryCase::where('slug', 'steve-jacobs')->update(['published_at' => null]);

        $this->get(route('ads.platform'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('featured', 0));
    }

    public function test_an_unknown_or_unpublished_case_is_a_404_so_an_ad_never_leads_to_nothing(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $this->get(route('ads.case', 'no-existe'))->assertNotFound();

        $case->update(['published_at' => null]);
        $this->get(route('ads.case', $case->slug))->assertNotFound();

        $case->update(['published_at' => now()->addDay()]);
        $this->get(route('ads.case', $case->slug))->assertNotFound();
    }

    public function test_the_landing_is_the_same_for_a_visitor_and_an_owner_except_the_button(): void
    {
        $case = $this->catalogCase(self::WITH_AD);
        $owner = $this->gameMaster($case->slug);

        $this->actingAs($owner)
            ->get(route('ads.case', $case->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('owned', true));
    }

    // --- Ad copy -----------------------------------------------------------

    public function test_written_ad_copy_is_used(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $this->get(route('ads.case', $case->slug))
            ->assertInertia(fn (Assert $page) => $page
                ->where('case.ad.hook', fn ($hook) => str_contains($hook, 'Todas las pruebas dicen'))
                ->has('case.ad.bullets', 3)
                ->where('case.ad.cta', 'Quiero este caso')
            );
    }

    public function test_a_case_with_no_ad_copy_still_gets_a_complete_page_from_its_own_facts(): void
    {
        $case = $this->catalogCase(self::WITHOUT_AD);
        $case->update(['ad' => null]);

        $this->get(route('ads.case', $case->slug))
            ->assertInertia(fn (Assert $page) => $page
                // The tagline stands in for the hook...
                ->where('case.ad.hook', $case->tagline ?? $case->name)
                // ...three points are built from real data, not invented...
                ->has('case.ad.bullets', 3)
                ->where('case.ad.bullets.0', "De {$case->min_players} a {$case->max_players} jugadores, juntos o a distancia")
                ->where('case.ad.bullets.1', "{$case->duration_minutes} minutos con el expediente llegando en tiempo real")
                // ...and the button has words.
                ->where('case.ad.cta', 'Quiero este caso')
            );
    }

    public function test_partial_ad_copy_falls_back_field_by_field(): void
    {
        $case = $this->catalogCase(self::WITHOUT_AD);
        $case->update(['ad' => ['hook' => 'Solo escribí el gancho', 'bullets' => [], 'cta' => null]]);

        $this->get(route('ads.case', $case->slug))
            ->assertInertia(fn (Assert $page) => $page
                ->where('case.ad.hook', 'Solo escribí el gancho')
                ->has('case.ad.bullets', 3)
                ->where('case.ad.cta', 'Quiero este caso')
            );
    }

    public function test_more_than_three_written_points_are_cut_to_three(): void
    {
        $case = $this->catalogCase(self::WITHOUT_AD);
        $case->update(['ad' => ['bullets' => ['a', 'b', 'c', 'd', 'e']]]);

        $this->get(route('ads.case', $case->slug))
            ->assertInertia(fn (Assert $page) => $page->has('case.ad.bullets', 3));
    }

    public function test_the_catalog_summary_shape_is_untouched_by_ad_copy(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $this->get(route('cases.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('cases.0', fn (Assert $card) => $card->missing('ad')->missing('landing_cover_url')->etc())
            );

        $this->get(route('cases.show', $case->slug))
            ->assertInertia(fn (Assert $page) => $page->missing('case.ad'));
    }

    // --- Syncing the copy --------------------------------------------------

    public function test_sync_stores_the_manifests_ad_block(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        $with = MysteryCase::firstWhere('slug', self::WITH_AD);
        $this->assertSame('Quiero este caso', $with->ad['cta']);
        $this->assertCount(3, $with->ad['bullets']);
    }

    /**
     * A guard on the copy itself: every case that ships has a hook and three
     * short points, so no paid landing quietly falls back to generic text.
     */
    public function test_every_installed_case_ships_with_complete_ad_copy(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        foreach (MysteryCase::all() as $case) {
            $this->assertNotEmpty($case->ad['hook'] ?? null, "{$case->slug} has no ad hook");
            $this->assertCount(3, $case->ad['bullets'] ?? [], "{$case->slug} needs exactly 3 ad points");
            $this->assertNotEmpty($case->ad['cta'] ?? null, "{$case->slug} has no ad button text");
            $this->assertLessThanOrEqual(200, mb_strlen($case->ad['hook']), "{$case->slug} hook is too long for a share preview");

            foreach ($case->ad['bullets'] as $bullet) {
                $this->assertLessThanOrEqual(80, mb_strlen($bullet), "{$case->slug} has a point too long for a phone card: {$bullet}");
            }
        }
    }

    public function test_sync_refreshes_ad_copy_the_way_it_does_the_tagline(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        MysteryCase::where('slug', self::WITH_AD)->update(['ad' => ['hook' => 'viejo']]);

        $this->artisan('platform:sync-cases')->assertSuccessful();

        $this->assertNotSame('viejo', MysteryCase::firstWhere('slug', self::WITH_AD)->ad['hook']);
    }

    public function test_the_manifest_ad_block_is_cleaned_before_it_reaches_the_catalog(): void
    {
        $definition = fn (array $ad) => new CaseDefinition('x', sys_get_temp_dir(), ['catalog' => ['ad' => $ad]]);

        // Blanks and padding never become empty bullets on a paid page.
        $this->assertSame(
            ['hook' => 'Gancho', 'bullets' => ['uno', 'dos'], 'cta' => null],
            $definition(['hook' => '  Gancho ', 'bullets' => ['uno', '', '   ', ' dos '], 'cta' => ''])->catalog()['ad']
        );

        // At most three points.
        $this->assertCount(3, $definition(['bullets' => ['a', 'b', 'c', 'd']])->catalog()['ad']['bullets']);

        // Nothing written at all is null, not a block of empties.
        $this->assertNull($definition([])->catalog()['ad']);
        $this->assertNull($definition(['hook' => ' ', 'bullets' => [''], 'cta' => null])->catalog()['ad']);
    }

    // --- Link previews: what a crawler that never runs the app sees --------

    public function test_a_case_landing_carries_its_own_link_preview_in_the_raw_html(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $html = $this->get(route('ads.case', $case->slug))->getContent();

        $this->assertStringContainsString('<meta property="og:title" content="'.e("{$case->name} — MisterioCode").'">', $html);
        $this->assertStringContainsString('Todas las pruebas dicen que se fue por su cuenta', $html);
        $this->assertStringContainsString(
            '<meta property="og:image" content="'.url("/immersion/{$case->slug}/cover/og.jpg").'">',
            $html
        );
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        $this->assertStringContainsString('<meta name="twitter:image" content="'.url("/immersion/{$case->slug}/cover/og.jpg").'">', $html);
        // Not the site-wide card.
        $this->assertStringNotContainsString('social-network', $html);
    }

    public function test_a_case_without_a_share_image_uses_the_site_default_not_a_broken_link(): void
    {
        $case = $this->catalogCase(self::WITHOUT_AD);

        $html = $this->get(route('ads.case', $case->slug))->getContent();

        $this->assertStringContainsString('content="'.url('/brand/social-network.jpg').'"', $html);
    }

    public function test_ad_pages_are_never_indexed_even_when_the_site_is(): void
    {
        config(['platform.indexable' => true]);
        $case = $this->catalogCase(self::WITH_AD);

        foreach ([route('ads.case', $case->slug), route('ads.platform')] as $url) {
            $html = $this->get($url)->getContent();
            $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
        }

        // The rest of the site keeps following the switch.
        $this->assertStringContainsString(
            '<meta name="robots" content="index, follow">',
            $this->get(route('cases.show', $case->slug))->getContent()
        );
    }

    public function test_ad_pages_point_search_engines_at_the_page_that_owns_the_content(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $this->assertStringContainsString(
            '<link rel="canonical" href="'.route('cases.show', $case->slug).'">',
            $this->get(route('ads.case', $case->slug))->getContent()
        );

        $this->assertStringContainsString(
            '<link rel="canonical" href="'.route('home').'">',
            $this->get(route('ads.platform'))->getContent()
        );
    }

    public function test_the_first_image_is_preloaded_before_the_app_runs(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $this->assertStringContainsString(
            '<link rel="preload" as="image" href="/immersion/'.$case->slug.'/cover/landing.jpg" fetchpriority="high">',
            $this->get(route('ads.case', $case->slug))->getContent()
        );

        $this->assertStringContainsString(
            '<link rel="preload" as="image" href="/brand/hero-01-1200.jpg" fetchpriority="high">',
            $this->get(route('ads.platform'))->getContent()
        );

        // Ordinary pages preload nothing.
        $this->assertStringNotContainsString('rel="preload" as="image"', $this->get(route('cases.index'))->getContent());
    }

    public function test_ad_pages_skip_the_font_only_the_case_documents_use(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $ad = $this->get(route('ads.case', $case->slug))->getContent();
        $this->assertStringContainsString('family=Inter', $ad);
        $this->assertStringNotContainsString('Courier+Prime', $ad);

        $this->assertStringContainsString('Courier+Prime', $this->get(route('home'))->getContent());
    }

    public function test_every_other_page_keeps_the_default_link_preview(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString('<meta property="og:title" content="MisterioCode — Casos de misterio para jugar en equipo">', $html);
        $this->assertStringContainsString('content="'.url('/brand/social-network.jpg').'"', $html);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $html);
    }

    // --- Campaign links ----------------------------------------------------

    public function test_a_campaign_link_to_the_landing_is_remembered_with_its_path_and_code(): void
    {
        $case = $this->catalogCase(self::WITH_AD);

        $this->get(route('ads.case', $case->slug).'?utm_source=tiktok&utm_campaign=lanzamiento&ttclid=ABC&promo_code=tiktok20')
            ->assertOk();

        $first = session('attribution')['first'];
        $this->assertSame('tiktok', $first['utm_source']);
        $this->assertSame('ABC', $first['ttclid']);
        $this->assertSame("/jugar/{$case->slug}", $first['landing_path']);
        $this->assertSame('TIKTOK20', session('pending_promo'));
    }

    public function test_the_platform_landing_remembers_a_campaign_link_too(): void
    {
        $this->get(route('ads.platform').'?utm_source=meta&fbclid=ZZZ')->assertOk();

        $this->assertSame('/jugar', session('attribution')['first']['landing_path']);
        $this->assertSame('ZZZ', session('attribution')['last']['fbclid']);
    }

    // --- The small images --------------------------------------------------

    public function test_the_case_and_brand_images_are_small_enough_for_a_phone_on_a_slow_connection(): void
    {
        $this->artisan('platform:sync-cases')->assertSuccessful();

        foreach (MysteryCase::whereNotNull('cover_path')->get() as $case) {
            $landing = public_path(dirname($case->cover_path).'/landing.jpg');
            $og = public_path(dirname($case->cover_path).'/og.jpg');

            $this->assertFileExists($landing, "{$case->slug} has no landing.jpg: run platform:build-case-images");
            $this->assertFileExists($og, "{$case->slug} has no og.jpg: run platform:build-case-images");

            [$width, $height] = getimagesize($og);
            $this->assertSame([1200, 630], [$width, $height], "{$case->slug} og.jpg is not 1200x630");
            $this->assertLessThan(300 * 1024, filesize($og), "{$case->slug} og.jpg is heavy");

            [$lw, $lh] = getimagesize($landing);
            $this->assertLessThanOrEqual(1100, max($lw, $lh), "{$case->slug} landing.jpg is oversize");
            $this->assertLessThan(300 * 1024, filesize($landing), "{$case->slug} landing.jpg is heavy");
        }

        $this->assertLessThan(60 * 1024, filesize(public_path('brand/isotipo-96.png')));
        $this->assertLessThan(120 * 1024, filesize(public_path('brand/isotipo-192.png')));
        $this->assertLessThan(150 * 1024, filesize(public_path('brand/hero-01-1200.jpg')));
        $this->assertLessThan(200 * 1024, filesize(public_path('brand/social-network.jpg')));
        $this->assertSame([1200, 630], array_slice(getimagesize(public_path('brand/social-network.jpg')), 0, 2));
    }

    public function test_building_the_images_is_idempotent(): void
    {
        $this->artisan('platform:build-case-images', ['--brand' => true])
            ->expectsOutputToContain('0 imagen(es) generada(s).')
            ->assertSuccessful();
    }

    public function test_share_and_landing_urls_exist_only_when_the_derived_file_does(): void
    {
        $withCover = $this->catalogCase(self::WITH_AD);
        $this->assertSame("/immersion/{$withCover->slug}/cover/og.jpg", $withCover->ogImageUrl());
        $this->assertSame("/immersion/{$withCover->slug}/cover/landing.jpg", $withCover->landingCoverUrl());

        // No cover art: no share image, and the landing falls back to
        // whatever the case page itself would show.
        $noCover = $this->catalogCase(self::WITHOUT_AD);
        $this->assertNull($noCover->ogImageUrl());
        $this->assertSame($noCover->coverUrl(), $noCover->landingCoverUrl());

        // A cover whose derivatives have not been built falls back too.
        $withCover->update(['cover_path' => '/immersion/nada/cover/portada.png']);
        $this->assertNull($withCover->fresh()->ogImageUrl());
        $this->assertSame('/immersion/nada/cover/portada.png', $withCover->fresh()->landingCoverUrl());
    }
}
