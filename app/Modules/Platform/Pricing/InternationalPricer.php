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
    /** A percent code takes exactly its percent off, down to the cent. */
    public const ROUND_CENTS = 'cents';

    /** A percent code's result is rounded UP to the next whole dollar. */
    public const ROUND_DOLLAR_UP = 'dollar_up';

    public function __construct(
        private int $fxRate,
        private int $feeUsd,
        private int $markupBps,
        private string $percentRounding = self::ROUND_CENTS,
    ) {
        if ($fxRate < 1 || $feeUsd < 0 || $markupBps < 1) {
            throw new InvalidArgumentException('Invalid international pricing parameters.');
        }

        if (! in_array($percentRounding, [self::ROUND_CENTS, self::ROUND_DOLLAR_UP], true)) {
            throw new InvalidArgumentException("Unknown percent rounding [{$percentRounding}].");
        }
    }

    public static function fromConfig(): self
    {
        return new self(
            (int) config('platform.market.fx_rate'),
            (int) config('platform.market.fee_usd'),
            (int) config('platform.market.markup_bps'),
            (string) config('platform.market.percent_rounding', self::ROUND_CENTS),
        );
    }

    /**
     * A percentage code applied to a USD price (cents), so "50% off" is 50%
     * off what the buyer sees: USD 28 -> 14, USD 21 -> 10.50.
     *
     * The list price is always whole dollars, so for an integer percent the
     * result is already a whole number of cents and nothing is rounded. Only
     * the dollar_up mode rounds (up), for when the payment provider cannot
     * take cents.
     */
    public function applyPercentOff(int $usdCents, int $percent): int
    {
        $percent = max(0, min(100, $percent));
        $cents = intdiv($usdCents * (100 - $percent), 100);

        return $this->percentRounding === self::ROUND_DOLLAR_UP
            ? intdiv($cents + 99, 100) * 100
            : $cents;
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
            'percent_rounding' => $this->percentRounding,
        ];
    }
}
