<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Actions\CreatePromoCodes;
use App\Modules\Platform\Console\Commands\Concerns\ResolvesPromoCodeGrant;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Console\Command;

/**
 * A batch of single-use codes to hand out one per person — a giveaway, a
 * guest list, a partner's affiliate list. Every code in the batch grants the
 * exact same thing; each is only distinct in its own random suffix, and each
 * is good for exactly one redemption, ever (see CreatePromoCode for a single
 * code shared by several people instead).
 *
 * The write itself is CreatePromoCodes, shared with the admin screen.
 */
class CreatePromoCodeBatch extends Command
{
    use ResolvesPromoCodeGrant;

    protected $signature = 'platform:create-promo-code-batch
                            {prefix : Shared by every code in the batch, e.g. LANZAMIENTO}
                            {--count=10 : How many codes to generate}
                            {--case= : Grants free access to this case slug}
                            {--any-case : Lets each redeemer pick which case, instead of a fixed one (never with --case)}
                            {--credits= : Grants this many free credits (combine with --case/--any-case for a gift bundle)}
                            {--discount-percent= : Percentage off a real purchase, 1-100}
                            {--discount-fixed= : Fixed amount off a real purchase}
                            {--expires= : Last day the codes work, e.g. 2026-11-30 (through the end of that day); omit for no expiry}
                            {--applies-to= : Limit a discount to "case" purchases or "credits" top-ups; omit for both}
                            {--note= : A reminder of what campaign this batch is for}';

    protected $description = 'Create a batch of single-use promo/gift codes to hand out one per person';

    public function handle(CreatePromoCodes $creator): int
    {
        $prefix = strtoupper(trim($this->argument('prefix')));

        if ($prefix === '') {
            $this->error('El prefijo no puede estar vacío.');

            return self::FAILURE;
        }

        $count = (int) $this->option('count');

        if ($count < 1 || $count > CreatePromoCodes::MAX_BATCH) {
            $this->error('--count tiene que estar entre 1 y '.CreatePromoCodes::MAX_BATCH.'.');

            return self::FAILURE;
        }

        $grant = $this->resolveGrantAttributes();

        if ($grant === null) {
            return self::FAILURE;
        }

        $codes = $creator->batch($prefix, $count, ['note' => $this->option('note')] + $grant);

        $this->info(count($codes).' códigos creados.');
        $this->line('  '.(new PromoCode($grant))->describeGrant());
        $this->newLine();

        foreach ($codes as $code) {
            $this->line($code);
        }

        return self::SUCCESS;
    }
}
