<?php

namespace App\Modules\Platform\Console\Commands\Concerns;

use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\PromoCode;

/**
 * What a code hands over: a GIFT (--case and/or --credits) or a DISCOUNT
 * (--discount-percent or --discount-fixed), never both. Shared by
 * CreatePromoCode and CreatePromoCodeBatch so this one rule — and its exact
 * wording — never drifts between "one code" and "a batch of codes".
 */
trait ResolvesPromoCodeGrant
{
    /**
     * @return array<string, mixed>|null null means an error was already
     *         printed and the caller should return self::FAILURE.
     */
    private function resolveGrantAttributes(): ?array
    {
        $caseSlug = $this->option('case');
        $anyCase = (bool) $this->option('any-case');
        $credits = $this->option('credits');
        $discountPercent = $this->option('discount-percent');
        $discountFixed = $this->option('discount-fixed');

        if ($caseSlug !== null && $anyCase) {
            $this->error('Da --case (un caso fijo) o --any-case (a elección de quien lo canjea), no los dos.');

            return null;
        }

        $isGift = $caseSlug !== null || $anyCase || $credits !== null;
        $isDiscount = $discountPercent !== null || $discountFixed !== null;

        if ($isGift && $isDiscount) {
            $this->error(
                'Un código es un regalo (--case/--any-case/--credits) o un descuento (--discount-percent/--discount-fixed), nunca las dos cosas.'
            );

            return null;
        }

        if (! $isGift && ! $isDiscount) {
            $this->error('Hay que dar al menos uno: --case, --any-case, --credits, --discount-percent o --discount-fixed.');

            return null;
        }

        if ($discountPercent !== null && $discountFixed !== null) {
            $this->error('Da --discount-percent o --discount-fixed, no los dos.');

            return null;
        }

        $attributes = [];

        if ($anyCase) {
            $attributes['grants_any_case'] = true;
        }

        if ($caseSlug !== null) {
            if (! MysteryCase::where('slug', $caseSlug)->exists()) {
                $this->error("No existe ningun caso con el slug \"{$caseSlug}\".");
                $this->line('Disponibles: '.MysteryCase::pluck('slug')->implode(', '));

                return null;
            }

            $attributes['grants_case_slug'] = $caseSlug;
        }

        if ($credits !== null) {
            if ((int) $credits < 1) {
                $this->error('--credits tiene que ser un entero positivo.');

                return null;
            }

            $attributes['grants_credits'] = (int) $credits;
        }

        if ($discountPercent !== null) {
            $value = (int) $discountPercent;

            if ($value < 1 || $value > 100) {
                $this->error('--discount-percent tiene que estar entre 1 y 100.');

                return null;
            }

            $attributes['discount_type'] = PromoCode::DISCOUNT_PERCENT;
            $attributes['discount_value'] = $value;
        }

        if ($discountFixed !== null) {
            if ((int) $discountFixed < 1) {
                $this->error('--discount-fixed tiene que ser un entero positivo.');

                return null;
            }

            $attributes['discount_type'] = PromoCode::DISCOUNT_FIXED;
            $attributes['discount_value'] = (int) $discountFixed;
        }

        return $attributes;
    }
}
