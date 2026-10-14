<?php

namespace App\Modules\Platform\Pricing;

use InvalidArgumentException;

/**
 * COP -> USD, by one rule, in integers.
 *
 *   usd = ceil( ((cop / fx_rate) + fee_usd) * markup_bps / 10000 )
 *
 * Rearranged so nothing is ever divided until the very end:
 *
 *   usd = ceil( (cop + fee_usd * fx_rate) * markup_bps / (fx_rate * 10000) )
 *
 * A float version would turn a price that lands exactly on a whole dollar into
 * 19.000000000000004 and round it up a dollar. The result is whole dollars, but
 * returned as cents because that is how every non-COP amount is stored.
 *
 * Zero stays zero: a gift or a 100% code must never pick up the +1 USD fee.
 */
class InternationalPricer
{
    public function __construct(
        private int $fxRate,
        private int $feeUsd,
        private int $markupBps,
    ) {
        if ($fxRate < 1 || $feeUsd < 0 || $markupBps < 1) {
            throw new InvalidArgumentException('Invalid international pricing parameters.');
        }
    }

    public static function fromConfig(): self
    {
        return new self(
            (int) config('platform.market.fx_rate'),
            (int) config('platform.market.fee_usd'),
            (int) config('platform.market.markup_bps'),
        );
    }

    /**
     * A percentage code applied to the USD price the buyer sees, kept in whole
     * dollars and rounded DOWN: the discount is never smaller than promised,
     * and no cent ever reaches the payment provider.
     *
     *   50% of USD 28 -> 14     50% of USD 21 -> 10     50% of USD 5 -> 2
     *
     * A code under 100% never makes a purchase free: it stops at USD 1, because
     * a price of 0 is what marks an order as a gift (no payment, delivered
     * directly) and 95% off a USD 5 recharge rounds to exactly that.
     */
    public function applyPercentOff(int $usdCents, int $percent): int
    {
        $percent = max(0, min(100, $percent));

        if ($percent === 100 || $usdCents <= 0) {
            return 0;
        }

        // usdCents is always whole dollars, so this division is exact; the
        // second one is the floor to a whole dollar.
        $wholeDollars = intdiv(intdiv($usdCents * (100 - $percent), 100), 100);

        return max(1, $wholeDollars) * 100;
    }

    /** @return int USD in cents (always a multiple of 100). */
    public function usdCentsFromCop(int $cop): int
    {
        if ($cop <= 0) {
            return 0;
        }

        $numerator = ($cop + $this->feeUsd * $this->fxRate) * $this->markupBps;
        $denominator = $this->fxRate * 10000;

        $dollars = intdiv($numerator + $denominator - 1, $denominator);

        return $dollars * 100;
    }

    /** The parameters as applied, kept on the order so it can be audited later. */
    public function snapshot(): array
    {
        return [
            'fx_rate' => $this->fxRate,
            'fee_usd' => $this->feeUsd,
            'markup_bps' => $this->markupBps,
            'percent_rounding' => 'whole_dollar_down',
        ];
    }
}
