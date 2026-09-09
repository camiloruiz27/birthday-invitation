<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Models\PromoCode;
use Illuminate\Console\Command;

/**
 * The read side of the console-only promo code workflow — "did that code
 * actually get created?", "how many uses are left?", "is it still active?".
 */
class ListPromoCodes extends Command
{
    protected $signature = 'platform:list-promo-codes';

    protected $description = 'List every promo/gift code and its usage';

    public function handle(): int
    {
        $codes = PromoCode::orderByDesc('id')->get();

        if ($codes->isEmpty()) {
            $this->info('No hay codigos creados todavia.');

            return self::SUCCESS;
        }

        $this->table(
            ['Codigo', 'Entrega', 'Usados', 'Activo', 'Nota'],
            $codes->map(fn (PromoCode $promo) => [
                $promo->code,
                $this->grants($promo),
                $promo->redemptions_count.'/'.($promo->max_redemptions ?? '∞'),
                $promo->active ? 'si' : 'no',
                $promo->note ?? '—',
            ])
        );

        return self::SUCCESS;
    }

    private function grants(PromoCode $promo): string
    {
        if ($promo->isDiscount()) {
            return $promo->discount_type === PromoCode::DISCOUNT_PERCENT
                ? "{$promo->discount_value}% off"
                : "{$promo->discount_value} off (fijo)";
        }

        $parts = array_filter([
            $promo->grants_case_slug,
            $promo->grants_credits ? "{$promo->grants_credits} creditos" : null,
        ]);

        return implode(' + ', $parts) ?: '—';
    }
}
