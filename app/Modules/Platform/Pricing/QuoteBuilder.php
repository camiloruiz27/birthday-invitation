<?php

namespace App\Modules\Platform\Pricing;

use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Support\Market;
use Illuminate\Http\Request;

/**
 * The one place a COP price becomes the price a buyer sees.
 *
 * Callers apply any promo code to the COP list price FIRST (that is what
 * RedeemPromoCode already does) and hand the result in as $finalCop. This only
 * converts, once, at the end — so a code means the same thing in every market
 * and the review screen, the ad landing and the order cannot disagree.
 */
class QuoteBuilder
{
    public function __construct(private InternationalPricer $pricer)
    {
    }

    public static function make(): self
    {
        return new self(InternationalPricer::fromConfig());
    }

    /**
     * The quote for whoever is making this request: their market is read from
     * the request the ResolveMarket middleware already tagged.
     */
    public function forRequest(int $listCop, ?int $finalCop, ?Request $request = null, ?int $percentOff = null): Quote
    {
        return $this->quote($listCop, $finalCop, Market::fromRequest($request ?? request()), $percentOff);
    }

    /**
     * Just the price a listing shows, in the shape the UI already reads
     * (`price_amount` + `currency`): the same number a purchase would charge.
     *
     * @return array{price_amount: int, currency: string}
     */
    public function display(int $listCop, ?Request $request = null): array
    {
        $quote = $this->forRequest($listCop, null, $request);

        return ['price_amount' => $quote->total, 'currency' => $quote->currency];
    }

    /**
     * The percent a code takes off, when it is a percentage discount; null for
     * a fixed amount, a gift, or no code. Only percentage codes are applied to
     * the USD price: a fixed code is a COP amount and has no USD meaning.
     */
    public static function percentOff(?PromoCode $promo): ?int
    {
        return $promo && $promo->isDiscount() && $promo->discount_type === PromoCode::DISCOUNT_PERCENT
            ? (int) $promo->discount_value
            : null;
    }

    /**
     * @param  int|null  $percentOff  The percentage of a percent code, if one is
     *                                being applied. For an international buyer it
     *                                comes off the USD price itself, so a "50% off"
     *                                is 50% of what they see. Everything else
     *                                (fixed codes, gifts, no code) goes through
     *                                $finalCop: discounted in COP, converted once.
     */
    public function quote(int $listCop, ?int $finalCop, string $market, ?int $percentOff = null): Quote
    {
        $finalCop ??= $listCop;

        if ($market !== Market::INTL) {
            return new Quote(Market::CO, 'COP', $listCop, $listCop - $finalCop, $finalCop, $listCop, $finalCop);
        }

        $list = $this->pricer->usdCentsFromCop($listCop);
        $total = $percentOff !== null && $finalCop > 0
            ? $this->pricer->applyPercentOff($list, $percentOff)
            : $this->pricer->usdCentsFromCop($finalCop);

        return new Quote(
            Market::INTL,
            'USD',
            $list,
            max(0, $list - $total),
            $total,
            $listCop,
            $finalCop,
            $this->pricer->snapshot(),
        );
    }
}
