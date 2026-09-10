<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Console\Command;

/**
 * Creates a redeemable code by hand — the only way one comes to exist.
 *
 * Console-only on purpose, not an admin screen: codes are created a handful
 * of times a year (a campaign, a launch, a partnership), and every other
 * privileged write in this app already lives here rather than behind a form
 * (see platform:grant-access, platform:make-admin). A UI would duplicate
 * validation in two places and add a write surface to an admin area that
 * today has none, for a task done rarely enough that it isn't worth it.
 *
 * A code is either a GIFT (--case and/or --credits) or a DISCOUNT
 * (--discount-percent or --discount-fixed) — never both. "Free case AND 20%
 * off" is not a real product, so this is rejected here, before anything
 * is written, rather than modeled as a database constraint.
 */
class CreatePromoCode extends Command
{
    protected $signature = 'platform:create-promo-code
                            {code : The redeemable code, e.g. LANZAMIENTO2026}
                            {--case= : Grants free access to this case slug}
                            {--credits= : Grants this many free credits (combine with --case for a gift bundle)}
                            {--discount-percent= : Percentage off a real purchase, 1-100}
                            {--discount-fixed= : Fixed amount off a real purchase}
                            {--max-redemptions= : Total uses allowed across everyone; omit for no cap}
                            {--max-per-user=1 : Uses allowed per person; 0 means no cap}
                            {--note= : A reminder of what campaign this is for}';

    protected $description = 'Create a promo/gift code';

    public function handle(): int
    {
        $code = strtoupper(trim($this->argument('code')));

        if ($code === '') {
            $this->error('El código no puede estar vacío.');

            return self::FAILURE;
        }

        if (PromoCode::where('code', $code)->exists()) {
            $this->error("Ya existe un código \"{$code}\".");

            return self::FAILURE;
        }

        $caseSlug = $this->option('case');
        $credits = $this->option('credits');
        $discountPercent = $this->option('discount-percent');
        $discountFixed = $this->option('discount-fixed');

        $isGift = $caseSlug !== null || $credits !== null;
        $isDiscount = $discountPercent !== null || $discountFixed !== null;

        if ($isGift && $isDiscount) {
            $this->error(
                'Un código es un regalo (--case/--credits) o un descuento (--discount-percent/--discount-fixed), nunca las dos cosas.'
            );

            return self::FAILURE;
        }

        if (! $isGift && ! $isDiscount) {
            $this->error('Hay que dar al menos uno: --case, --credits, --discount-percent o --discount-fixed.');

            return self::FAILURE;
        }

        if ($discountPercent !== null && $discountFixed !== null) {
            $this->error('Da --discount-percent o --discount-fixed, no los dos.');

            return self::FAILURE;
        }

        $attributes = ['code' => $code, 'note' => $this->option('note')];

        if ($caseSlug !== null) {
            if (! MysteryCase::where('slug', $caseSlug)->exists()) {
                $this->error("No existe ningun caso con el slug \"{$caseSlug}\".");
                $this->line('Disponibles: '.MysteryCase::pluck('slug')->implode(', '));

                return self::FAILURE;
            }

            $attributes['grants_case_slug'] = $caseSlug;
        }

        if ($credits !== null) {
            if ((int) $credits < 1) {
                $this->error('--credits tiene que ser un entero positivo.');

                return self::FAILURE;
            }

            $attributes['grants_credits'] = (int) $credits;
        }

        if ($discountPercent !== null) {
            $value = (int) $discountPercent;

            if ($value < 1 || $value > 100) {
                $this->error('--discount-percent tiene que estar entre 1 y 100.');

                return self::FAILURE;
            }

            $attributes['discount_type'] = PromoCode::DISCOUNT_PERCENT;
            $attributes['discount_value'] = $value;
        }

        if ($discountFixed !== null) {
            if ((int) $discountFixed < 1) {
                $this->error('--discount-fixed tiene que ser un entero positivo.');

                return self::FAILURE;
            }

            $attributes['discount_type'] = PromoCode::DISCOUNT_FIXED;
            $attributes['discount_value'] = (int) $discountFixed;
        }

        if ($this->option('max-redemptions') !== null) {
            $attributes['max_redemptions'] = (int) $this->option('max-redemptions');
        }

        // 0 is the escape hatch for "no cap per person" — the default (1)
        // covers what most codes need without a separate boolean flag.
        $maxPerUser = (int) $this->option('max-per-user');
        $attributes['max_redemptions_per_user'] = $maxPerUser === 0 ? null : $maxPerUser;

        $promo = PromoCode::create($attributes);

        $this->info("Código \"{$promo->code}\" creado.");
        $this->line('  '.$this->describe($promo));

        return self::SUCCESS;
    }

    private function describe(PromoCode $promo): string
    {
        if ($promo->isDiscount()) {
            $value = $promo->discount_type === PromoCode::DISCOUNT_PERCENT
                ? "{$promo->discount_value}%"
                : "{$promo->discount_value} (monto fijo)";

            return "Descuento: {$value}";
        }

        $parts = [];

        if ($promo->grants_case_slug) {
            $parts[] = "caso \"{$promo->grants_case_slug}\"";
        }

        if ($promo->grants_credits) {
            $parts[] = "{$promo->grants_credits} creditos";
        }

        return 'Regalo: '.implode(' + ', $parts);
    }
}
