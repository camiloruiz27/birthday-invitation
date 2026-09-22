<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Console\Commands\Concerns\ResolvesPromoCodeGrant;
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
    use ResolvesPromoCodeGrant;

    protected $signature = 'platform:create-promo-code
                            {code : The redeemable code, e.g. LANZAMIENTO2026}
                            {--case= : Grants free access to this case slug}
                            {--any-case : Lets the redeemer pick which case, instead of a fixed one (never with --case)}
                            {--credits= : Grants this many free credits (combine with --case/--any-case for a gift bundle)}
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

        $grant = $this->resolveGrantAttributes();

        if ($grant === null) {
            return self::FAILURE;
        }

        $attributes = ['code' => $code, 'note' => $this->option('note')] + $grant;

        if ($this->option('max-redemptions') !== null) {
            $attributes['max_redemptions'] = (int) $this->option('max-redemptions');
        }

        // 0 is the escape hatch for "no cap per person" — the default (1)
        // covers what most codes need without a separate boolean flag.
        $maxPerUser = (int) $this->option('max-per-user');
        $attributes['max_redemptions_per_user'] = $maxPerUser === 0 ? null : $maxPerUser;

        $promo = PromoCode::create($attributes);

        $this->info("Código \"{$promo->code}\" creado.");
        $this->line('  '.($promo->isDiscount() ? 'Descuento: ' : 'Regalo: ').$promo->describeGrant());

        return self::SUCCESS;
    }
}
