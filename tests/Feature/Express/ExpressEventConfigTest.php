<?php

namespace Tests\Feature\Express;

use App\Modules\Express\Support\ExpressEvents;
use App\Modules\Platform\Support\Attribution;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * The USB Cali fair as it is configured to ship: its name, its official promo
 * and when it closes. Nothing here overrides config/express.php.
 */
class ExpressEventConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_the_fair_is_open_on_the_day_and_closes_after_31_october_colombian_time(): void
    {
        $event = ExpressEvents::find('usb-cali-2026');

        $this->assertSame('Feria de Emprendimiento · Universidad San Buenaventura Cali', $event['name']);

        foreach (['2026-10-27 08:00', '2026-10-28 12:00', '2026-10-31 23:30'] as $moment) {
            Carbon::setTestNow(Carbon::parse($moment, 'America/Bogota'));
            $this->assertTrue(ExpressEvents::status($event)['open'], "open at {$moment}");
        }

        Carbon::setTestNow(Carbon::parse('2026-11-01 00:01', 'America/Bogota'));

        $status = ExpressEvents::status($event);
        $this->assertFalse($status['open']);
        $this->assertStringContainsString('terminó', $status['reason']);
    }

    public function test_the_official_code_travels_to_the_production_platform_and_survives_attribution(): void
    {
        $event = ExpressEvents::find('usb-cali-2026');

        $this->assertSame('FERIA-USBCALI2026', $event['promo_code']);

        $url = ExpressEvents::ctaUrl($event);

        $this->assertStringStartsWith('https://misteriocode.com/jugar?', $url);

        // What the platform does with that link: it remembers the code (and the
        // campaign) for as long as it takes the visitor to register and pay.
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $request = Request::create('/jugar', 'GET', $query);
        $request->setLaravelSession($this->app['session']->driver('array'));

        Attribution::capture($request);

        $this->assertSame('FERIA-USBCALI2026', Attribution::pendingPromo($request));
        $this->assertSame('feria_usb_20261028', Attribution::touches($request)['last']['utm_campaign']);
        $this->assertSame('express', Attribution::touches($request)['last']['utm_source']);
    }

    public function test_the_poster_qr_points_at_this_event_with_its_campaign(): void
    {
        $url = ExpressEvents::posterUrl(ExpressEvents::find('usb-cali-2026'));

        $this->assertStringContainsString('/express?', $url);
        $this->assertStringContainsString('e=usb-cali-2026', $url);
        $this->assertStringContainsString('utm_campaign=feria_usb_20261028', $url);
    }
}
