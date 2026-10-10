<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Support\InAppBrowser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Tapping an ad in TikTok or Instagram opens that app's own browser. Knowing
 * so lets the pages say what to do when the anti-bot check or the payment page
 * misbehaves in there, and lets us count how much of a campaign lands there.
 */
class InAppBrowserTest extends TestCase
{
    use RefreshDatabase;

    private const TIKTOK_IOS = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 musical_ly_30.8.0 JsSdk/2.0 NetType/WIFI Channel/App Store ByteLocale/es Region/CO';

    private const TIKTOK_ANDROID = 'Mozilla/5.0 (Linux; Android 13; SM-A546E Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/120.0.0.0 Mobile Safari/537.36 trill_2023008030 JsSdk/1.0 NetType/WIFI Channel/googleplay AppName/musical_ly app_version/30.8.3 ByteLocale/es-CO BytedanceWebview/d8a21c6';

    private const INSTAGRAM_IOS = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Instagram 300.0.0.29.110 (iPhone14,5; iOS 17_0; es_CO; es-CO; scale=3.00; 1170x2532; 463736449)';

    private const INSTAGRAM_ANDROID = 'Mozilla/5.0 (Linux; Android 13; SM-A546E Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/120.0.0.0 Mobile Safari/537.36 Instagram 300.0.0.29.110 Android (33/13; 480dpi; 1080x2340; samsung; SM-A546E; a54x; exynos1380; es_CO; 463736449)';

    private const FACEBOOK_IOS = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/430.0.0.40.119;FBBV/543210;FBDV/iPhone14,5;FBMD/iPhone;FBSN/iOS;FBSV/17.0;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5]';

    private const CHROME_ANDROID = 'Mozilla/5.0 (Linux; Android 13; SM-A546E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

    private const SAFARI_IOS = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    private const DESKTOP = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0';

    /**
     * @dataProvider userAgents
     */
    public function test_it_recognises_the_in_app_browsers_and_leaves_ordinary_ones_alone(?string $userAgent, ?string $expected): void
    {
        $this->assertSame($expected, InAppBrowser::detect($userAgent));
    }

    public static function userAgents(): array
    {
        return [
            'tiktok on ios' => [self::TIKTOK_IOS, 'tiktok'],
            // Its Android webview also carries the generic "; wv)" marker:
            // TikTok, the more specific, wins.
            'tiktok on android' => [self::TIKTOK_ANDROID, 'tiktok'],
            'instagram on ios' => [self::INSTAGRAM_IOS, 'instagram'],
            'instagram on android' => [self::INSTAGRAM_ANDROID, 'instagram'],
            'facebook on ios' => [self::FACEBOOK_IOS, 'facebook'],
            'a generic android webview' => ['Mozilla/5.0 (Linux; Android 12; Pixel 6 Build/SD1A; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/110.0.0.0 Mobile Safari/537.36', 'other'],
            'snapchat' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile/15E148 Snapchat/12.50.0.37 (like Safari/8618.2.12)', 'other'],
            'chrome on android' => [self::CHROME_ANDROID, null],
            'safari on ios' => [self::SAFARI_IOS, null],
            'desktop edge' => [self::DESKTOP, null],
            'no user agent' => [null, null],
            'empty user agent' => ['', null],
        ];
    }

    public function test_every_page_is_told_which_browser_it_is_in(): void
    {
        $this->get('/', ['User-Agent' => self::INSTAGRAM_IOS])
            ->assertInertia(fn (Assert $page) => $page->where('inAppBrowser', 'instagram'));

        $this->get('/jugar', ['User-Agent' => self::TIKTOK_ANDROID])
            ->assertInertia(fn (Assert $page) => $page->where('inAppBrowser', 'tiktok'));

        $this->get(route('register'), ['User-Agent' => self::FACEBOOK_IOS])
            ->assertInertia(fn (Assert $page) => $page->where('inAppBrowser', 'facebook'));

        $this->get('/', ['User-Agent' => self::CHROME_ANDROID])
            ->assertInertia(fn (Assert $page) => $page->where('inAppBrowser', null));
    }

    public function test_a_campaign_landing_inside_an_in_app_browser_is_marked_on_the_touch(): void
    {
        $this->get('/jugar?utm_source=tiktok&utm_campaign=lanzamiento', ['User-Agent' => self::TIKTOK_IOS])->assertOk();

        $this->assertSame('tiktok', session('attribution')['first']['in_app']);
        $this->assertSame('tiktok', session('attribution')['last']['in_app']);
    }

    public function test_an_ordinary_browser_leaves_no_marker(): void
    {
        $this->get('/jugar?utm_source=tiktok', ['User-Agent' => self::CHROME_ANDROID])->assertOk();

        $this->assertArrayNotHasKey('in_app', session('attribution')['first']);
    }

    public function test_a_visit_with_no_campaign_stores_nothing_even_inside_an_in_app_browser(): void
    {
        $this->get('/jugar', ['User-Agent' => self::INSTAGRAM_IOS])->assertOk();

        $this->assertNull(session('attribution'));
    }

    public function test_the_marker_reaches_the_account_at_registration(): void
    {
        $this->get('/jugar?utm_source=instagram', ['User-Agent' => self::INSTAGRAM_IOS]);

        $this->withHeader('User-Agent', self::INSTAGRAM_IOS)->post(route('register'), [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'accept_terms' => true,
        ]);

        $user = \App\Models\User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertSame('instagram', $user->attribution['first']['in_app']);
    }

    public function test_a_missing_anti_bot_token_is_reported_in_words_a_person_can_read(): void
    {
        config(['platform.captcha.site_key' => 'site', 'platform.captcha.secret_key' => 'secret']);

        $response = $this->post(route('login'), ['email' => 'a@example.com', 'password' => 'x']);

        $response->assertSessionHasErrors('cf-turnstile-response');

        $message = session('errors')->first('cf-turnstile-response');
        $this->assertStringContainsString('verificación anti-robots', $message);
        $this->assertStringNotContainsString('cf-turnstile-response', $message);
    }
}
