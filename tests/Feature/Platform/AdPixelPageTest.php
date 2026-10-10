<?php

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * What the page hands the browser for the TikTok and Meta pixels, and what
 * the content security policy lets them do. The scripts themselves are only
 * injected after the visitor accepts the marketing category (resources/js/
 * lib/consent.js) — this is the server's half: the ids, and the allow-list.
 */
class AdPixelPageTest extends TestCase
{
    use RefreshDatabase;

    private function policy(): string
    {
        $response = $this->get('/');

        return $response->headers->get('Content-Security-Policy-Report-Only')
            ?? $response->headers->get('Content-Security-Policy');
    }

    private function directive(string $name): string
    {
        foreach (explode(';', $this->policy()) as $part) {
            $part = trim($part);

            if (str_starts_with($part, "{$name} ")) {
                return $part;
            }
        }

        $this->fail("The policy has no {$name} directive.");
    }

    public function test_the_pixel_ids_reach_the_page_only_when_configured(): void
    {
        config(['platform.ads.tiktok.pixel_id' => 'TT123', 'platform.ads.meta.pixel_id' => '987654']);

        $this->get('/')
            ->assertOk()
            ->assertSee('name="mc-ads"', false)
            ->assertSee('data-tiktok="TT123"', false)
            ->assertSee('data-meta="987654"', false);
    }

    public function test_one_network_can_be_configured_without_the_other(): void
    {
        config(['platform.ads.tiktok.pixel_id' => 'TT123', 'platform.ads.meta.pixel_id' => '']);

        $this->get('/')
            ->assertSee('data-tiktok="TT123"', false)
            ->assertSee('data-meta=""', false);
    }

    public function test_with_no_pixel_ids_the_page_has_no_ad_tag_at_all(): void
    {
        config(['platform.ads.tiktok.pixel_id' => '', 'platform.ads.meta.pixel_id' => '']);

        $this->get('/')->assertOk()->assertDontSee('mc-ads', false);
    }

    public function test_the_api_tokens_never_reach_the_browser(): void
    {
        config([
            'platform.ads.tiktok.pixel_id' => 'TT123',
            'platform.ads.tiktok.access_token' => 'tiktok-secret-token',
            'platform.ads.meta.pixel_id' => '987654',
            'platform.ads.meta.access_token' => 'meta-secret-token',
        ]);

        $this->get('/')
            ->assertDontSee('tiktok-secret-token', false)
            ->assertDontSee('meta-secret-token', false);
    }

    public function test_the_policy_lets_both_pixels_load_and_report(): void
    {
        $script = $this->directive('script-src');
        $this->assertStringContainsString('https://analytics.tiktok.com', $script);
        $this->assertStringContainsString('https://connect.facebook.net', $script);

        $connect = $this->directive('connect-src');
        foreach (['https://analytics.tiktok.com', 'https://*.tiktok.com', 'https://www.facebook.com', 'https://connect.facebook.net'] as $host) {
            $this->assertStringContainsString($host, $connect);
        }

        $img = $this->directive('img-src');
        foreach (['https://analytics.tiktok.com', 'https://*.tiktok.com', 'https://www.facebook.com'] as $host) {
            $this->assertStringContainsString($host, $img);
        }
    }

    public function test_the_policy_does_not_open_the_server_side_api_hosts_to_the_browser(): void
    {
        $policy = $this->policy();

        $this->assertStringNotContainsString('business-api.tiktok.com', $policy);
        $this->assertStringNotContainsString('graph.facebook.com', $policy);
    }

    public function test_the_policy_still_has_no_inline_script_escape_hatch(): void
    {
        $this->assertStringNotContainsString("'unsafe-inline'", $this->directive('script-src'));
        $this->assertStringNotContainsString("'unsafe-eval'", $this->directive('script-src'));
    }

    public function test_the_legal_texts_are_versioned_past_the_one_that_said_no_advertising_cookies(): void
    {
        // The wording itself is rendered in the browser; what the server
        // owns is the version stamp, which is bumped whenever the text
        // changes in a way a visitor must be told about.
        $this->assertSame('1.1', config('legal.privacy_version'));
        $this->assertSame('1.1', config('legal.cookies_version'));

        $this->get(route('cookies'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Legal/Cookies')
                ->where('legal.version', '1.1'));
    }
}
