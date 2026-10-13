<?php

namespace App\Modules\Platform\Support;

/**
 * How many decimal places a currency's stored integer carries.
 *
 * Orders store the smallest unit of their own currency as an integer: whole
 * pesos for COP (no practical cents), cents for USD. The browser applies the
 * same rule in resources/js/lib/format.js — keep the two lists identical, or a
 * price shown and a price charged will disagree by a factor of 100.
 */
class Money
{
    /** Currencies with no minor unit: the stored integer IS the whole amount. */
    private const ZERO_DECIMAL = ['COP', 'CLP', 'JPY', 'KRW'];

    public static function exponent(string $currency): int
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    /**
     * The stored integer as the amount people read ("value" in an ad
     * conversion event, for instance): 2000 USD-cents -> 20, 2050 -> 20.5,
     * 59900 COP -> 59900. A whole amount stays an int so existing COP
     * payloads are byte-for-byte what they were.
     */
    public static function toMajor(int $amount, string $currency): int|float
    {
        $factor = 10 ** self::exponent($currency);

        return $amount % $factor === 0 ? intdiv($amount, $factor) : $amount / $factor;
    }
}
