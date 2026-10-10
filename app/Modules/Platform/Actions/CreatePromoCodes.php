<?php

namespace App\Modules\Platform\Actions;

use App\Modules\Platform\Models\PromoCode;

/**
 * The single writer of promo codes — the console commands and the admin
 * screen both create codes through here, so the rules for what a code looks
 * like (shape, uniqueness, one-use batches) live in one place.
 *
 * What a code GRANTS is validated by whoever calls this (the console trait,
 * or the admin controller's validator); this only writes what it is given.
 */
class CreatePromoCodes
{
    public const MAX_BATCH = 500;

    // Excludes 0/O and 1/I: a code read off a screenshot or typed from a
    // printed flyer must not be ambiguous.
    private const SUFFIX_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const SUFFIX_LENGTH = 6;

    /**
     * One code, shared by however many people the caps allow.
     *
     * @param  array<string, mixed>  $attributes  everything but `code`
     */
    public function single(string $code, array $attributes): PromoCode
    {
        return PromoCode::create(['code' => strtoupper(trim($code))] + $attributes);
    }

    /**
     * N single-use codes, one per person, all granting the same thing.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<int, string> the codes created
     */
    public function batch(string $prefix, int $count, array $attributes): array
    {
        $codes = $this->uniqueCodes(strtoupper(trim($prefix)), $count);

        foreach ($codes as $code) {
            PromoCode::create([
                'code' => $code,
                'max_redemptions' => 1,
                'max_redemptions_per_user' => 1,
            ] + $attributes);
        }

        return $codes;
    }

    /**
     * @return array<int, string>
     */
    public function uniqueCodes(string $prefix, int $count): array
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
