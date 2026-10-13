<?php

namespace Tests\Unit\Platform;

use App\Modules\Platform\Pricing\InternationalPricer;
use App\Modules\Platform\Pricing\QuoteBuilder;
use App\Modules\Platform\Support\Market;
use App\Modules\Platform\Support\Money;
use PHPUnit\Framework\TestCase;

/**
 * The international price rule: COP -> USD in one rounding, at the end.
 * usd = ceil(((cop / 3500) + 1) * 1.05)
 */
class InternationalPricerTest extends TestCase
{
    private function pricer(): InternationalPricer
    {
        return new InternationalPricer(3500, 1, 10500);
    }

    /** @dataProvider listPrices */
    public function test_converts_a_cop_price_to_whole_dollars_in_cents(int $cop, int $dollars): void
    {
        $this->assertSame($dollars * 100, $this->pricer()->usdCentsFromCop($cop));
    }

    public static function listPrices(): array
    {
        return [
            'case 59.900' => [59900, 20],
            'case 64.900' => [64900, 21],
            'case 89.000' => [89000, 28],
            'recharge 9.900' => [9900, 5],
            'recharge 22.900' => [22900, 8],
            'recharge 49.900' => [49900, 17],
        ];
    }

    public function test_zero_stays_zero_so_a_gift_never_picks_up_the_fee(): void
    {
        $this->assertSame(0, $this->pricer()->usdCentsFromCop(0));
    }

    public function test_a_price_landing_exactly_on_a_dollar_is_not_bumped_up(): void
    {
        // 70.000 -> (20 + 1) * 1.05 = 22.05 -> 23.
        $this->assertSame(2300, $this->pricer()->usdCentsFromCop(70000));
        // Without fee or markup the rule is exact: 35.000 COP is exactly USD 10,
        // and one peso more rounds up to 11.
        $exact = new InternationalPricer(3500, 0, 10000);
        $this->assertSame(1000, $exact->usdCentsFromCop(35000));
        $this->assertSame(1100, $exact->usdCentsFromCop(35001));
    }

    /** @dataProvider discountedPrices */
    public function test_discounts_apply_in_cop_first_and_convert_once(int $listCop, int $finalCop, int $dollars): void
    {
        $quote = (new QuoteBuilder($this->pricer()))->quote($listCop, $finalCop, Market::INTL);

        $this->assertSame('USD', $quote->currency);
        $this->assertSame($dollars * 100, $quote->total);
        $this->assertSame($finalCop, $quote->baseTotalCop);
    }

    public static function discountedPrices(): array
    {
        return [
            '25% off 59.900' => [59900, (int) floor(59900 * 75 / 100), 15],
            '50% off 59.900' => [59900, (int) floor(59900 * 50 / 100), 11],
            'fixed COP 10.000 off 59.900' => [59900, 49900, 17],
            '100% / gift' => [59900, 0, 0],
        ];
    }

    /** @dataProvider percentCodes */
    public function test_a_percent_code_comes_off_the_usd_price_exactly(int $listCop, int $percent, int $expectedCents): void
    {
        $finalCop = (int) floor($listCop * (100 - $percent) / 100);

        $quote = (new QuoteBuilder($this->pricer()))->quote($listCop, $finalCop, Market::INTL, $percent);

        $this->assertSame($expectedCents, $quote->total);
        $this->assertSame($finalCop, $quote->baseTotalCop);
        // list - total, so the discount shown is the one actually given.
        $this->assertSame($quote->list - $expectedCents, $quote->discount);
    }

    public static function percentCodes(): array
    {
        return [
            'USD 20, 25%' => [59900, 25, 1500],
            'USD 20, 50%' => [59900, 50, 1000],
            'USD 28, 50%' => [89000, 50, 1400],
            'USD 21, 50% (cents)' => [64900, 50, 1050],
            'USD 5, 50% (cents)' => [9900, 50, 250],
            'USD 28, 20%' => [89000, 20, 2240],
            '100% is free' => [59900, 100, 0],
        ];
    }

    public function test_a_percent_code_can_be_rounded_up_to_whole_dollars_instead(): void
    {
        $pricer = new InternationalPricer(3500, 1, 10500, InternationalPricer::ROUND_DOLLAR_UP);

        $this->assertSame(1100, $pricer->applyPercentOff(2100, 50)); // 10,50 -> 11
        $this->assertSame(1000, $pricer->applyPercentOff(2000, 50)); // already whole
    }

    public function test_a_fixed_code_is_still_discounted_in_cop_first(): void
    {
        // No percent given: 59.900 - 10.000 = 49.900 COP -> USD 17.
        $quote = (new QuoteBuilder($this->pricer()))->quote(59900, 49900, Market::INTL);

        $this->assertSame(1700, $quote->total);
    }

    public function test_colombian_market_is_untouched(): void
    {
        $quote = (new QuoteBuilder($this->pricer()))->quote(59900, 49900, Market::CO);

        $this->assertSame('COP', $quote->currency);
        $this->assertSame(59900, $quote->list);
        $this->assertSame(10000, $quote->discount);
        $this->assertSame(49900, $quote->total);
    }

    public function test_money_minor_units(): void
    {
        $this->assertSame(0, Money::exponent('COP'));
        $this->assertSame(2, Money::exponent('USD'));
        $this->assertSame(20, Money::toMajor(2000, 'USD'));
        $this->assertSame(20.5, Money::toMajor(2050, 'USD'));
        $this->assertSame(59900, Money::toMajor(59900, 'COP'));
    }
}
