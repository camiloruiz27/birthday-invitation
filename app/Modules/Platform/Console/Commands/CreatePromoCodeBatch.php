<?php

namespace App\Modules\Platform\Console\Commands;

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
 * Console-only, same reasoning as CreatePromoCode: a rare, privileged write.
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
                            {--note= : A reminder of what campaign this batch is for}';

    protected $description = 'Create a batch of single-use promo/gift codes to hand out one per person';

    // Excludes 0/O and 1/I: a code read off a screenshot or typed from a
    // printed flyer must not be ambiguous.
    private const SUFFIX_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const SUFFIX_LENGTH = 6;

    private const MAX_COUNT = 500;

    public function handle(): int
    {
        $prefix = strtoupper(trim($this->argument('prefix')));

        if ($prefix === '') {
            $this->error('El prefijo no puede estar vacío.');

            return self::FAILURE;
        }

        $count = (int) $this->option('count');

        if ($count < 1 || $count > self::MAX_COUNT) {
            $this->error('--count tiene que estar entre 1 y '.self::MAX_COUNT.'.');

            return self::FAILURE;
        }

        $grant = $this->resolveGrantAttributes();

        if ($grant === null) {
            return self::FAILURE;
        }

        $codes = $this->generateUniqueCodes($prefix, $count);
        $note = $this->option('note');

        foreach ($codes as $code) {
            PromoCode::create([
                'code' => $code,
                'note' => $note,
                'max_redemptions' => 1,
                'max_redemptions_per_user' => 1,
            ] + $grant);
        }

        $this->info(count($codes).' códigos creados.');
        $this->line('  '.(new PromoCode($grant))->describeGrant());
        $this->newLine();

        foreach ($codes as $code) {
            $this->line($code);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function generateUniqueCodes(string $prefix, int $count): array
    {
        // Codes already using this prefix count too, so running the same
        // campaign's batch a second time can never collide with the first.
        $seen = array_flip(
            PromoCode::where('code', 'like', "{$prefix}-%")->pluck('code')->all()
        );

        $codes = [];

        while (count($codes) < $count) {
            $code = "{$prefix}-".$this->randomSuffix();

            if (isset($seen[$code])) {
                continue;
            }

            $seen[$code] = true;
            $codes[] = $code;
        }

        return $codes;
    }

    private function randomSuffix(): string
    {
        $alphabet = self::SUFFIX_ALPHABET;
        $suffix = '';

        for ($i = 0; $i < self::SUFFIX_LENGTH; $i++) {
            $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $suffix;
    }
}
