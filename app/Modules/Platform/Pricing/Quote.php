<?php

namespace App\Modules\Platform\Pricing;

/**
 * What a buyer sees and is charged for one purchase, in their market's
 * currency. `list`, `discount` and `total` are in that currency's stored unit
 * (COP pesos, or USD cents); `baseListCop` and `baseTotalCop` are the same two
 * prices in COP, the base every discount was applied to.
 */
class Quote
{
    public function __construct(
        public readonly string $market,
        public readonly string $currency,
        public readonly int $list,
        public readonly int $discount,
        public readonly int $total,
        public readonly int $baseListCop,
        public readonly int $baseTotalCop,
        public readonly ?array $snapshot = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'market' => $this->market,
            'currency' => $this->currency,
            'list' => $this->list,
            'discount' => $this->discount,
            'total' => $this->total,
        ];
    }
}
