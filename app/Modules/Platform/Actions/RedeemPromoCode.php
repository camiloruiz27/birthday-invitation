<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;
use Illuminate\Support\Facades\DB;

/**
 * The single door a code is ever redeemed through — same principle as
 * GrantCaseAccess and AiCredits::grant: one writer, so there is one place to
 * look when asking "how could this user have a promo entitlement?".
 *
 * Reuses GrantCaseAccess and AiCredits::grant exactly as they already exist;
 * neither needed a change. This is what finally puts Entitlement::SOURCE_PROMO
 * to use — it existed, unused, before this class did.
 */
class RedeemPromoCode
{
    /**
     * One message for every reason a code cannot be used that would otherwise
     * reveal whether it EXISTS: unknown, switched off, or all used up.
     *
     * Distinct messages there turn any code field into an enumeration oracle —
     * "no existe" versus "ya alcanzó su límite" tells an attacker they guessed
     * a real code, which is exactly the signal a brute-force run needs. The
     * throttle on these routes is the other half of the same fix; this is what
     * makes each attempt worthless even when one gets through.
     *
     * The two rejections that survive with their own wording are the ones that
     * leak nothing: "ya usaste este código" can only be reached by someone who
     * already redeemed it, and the gift/discount mix-up is only reachable by
     * someone holding a real code who typed it on the wrong screen.
     */
    public const UNUSABLE = 'Ese código no es válido o ya no se puede usar.';

    public function __construct(private GrantCaseAccess $access, private AiCredits $credits)
    {
    }

    /**
     * A pure gift (a case, credits, or both — the bundle is just both fields
     * set on one code). No external call is involved, so the whole thing —
     * claim, grant — runs in one transaction with nothing to roll back by
     * hand if something fails partway.
     */
    public function redeemGift(User $user, string $code): PromoCode
    {
        return DB::transaction(function () use ($user, $code) {
            $promo = $this->lockAndValidate($code, $user);

            if (! $promo->isGift()) {
                throw new PromoCodeException(
                    'Este código es un descuento: se aplica al comprar un caso o al recargar créditos, no aquí.'
                );
            }

            $this->recordRedemption($promo, $user);

            if ($promo->grants_case_slug) {
                $case = MysteryCase::where('slug', $promo->grants_case_slug)->first();

                if (! $case) {
                    throw new PromoCodeException(
                        'Este código ya no se puede usar: el caso al que apunta no está disponible.'
                    );
                }

                $this->access->grant($user, $case, Entitlement::SOURCE_PROMO);
            }

            if ($promo->grants_credits) {
                $this->credits->grant(
                    $user,
                    $promo->grants_credits,
                    CreditLedgerEntry::REASON_PROMO,
                    "Codigo: {$promo->code}"
                );
            }

            return $promo->fresh();
        });
    }

    /**
     * A discount, computed against $amount BEFORE the caller creates its
     * Order — StartCheckout needs the already-discounted amount to ask Bold
     * for a link. The redemption's order_id is null until the caller sets it
     * once the Order actually exists (see StartCheckout::create()).
     *
     * If the checkout that was supposed to use this never completes (Bold
     * unreachable, etc.), the caller must call release() with the returned
     * redemption — otherwise a code with a hard cap loses a use to a sale
     * that never happened.
     */
    public function applyDiscount(User $user, string $code, int $amount): PromoDiscount
    {
        return DB::transaction(function () use ($user, $code, $amount) {
            $promo = $this->lockAndValidate($code, $user);

            if (! $promo->isDiscount()) {
                throw new PromoCodeException(
                    'Este código es un regalo: canjéalo desde "Canjear código", no aquí.'
                );
            }

            $redemption = $this->recordRedemption($promo, $user);

            return new PromoDiscount($promo->discountedAmount($amount), $redemption);
        });
    }

    /**
     * Gives back a claimed-but-unused discount. Deletes the redemption row
     * outright rather than marking it voided: an order that never completed
     * never really consumed the code, so there is nothing worth keeping a
     * record of.
     */
    public function release(PromoCodeRedemption $redemption): void
    {
        DB::transaction(function () use ($redemption) {
            PromoCode::whereKey($redemption->promo_code_id)->decrement('redemptions_count');
            $redemption->delete();
        });
    }

    /**
     * A read-only look at what a discount code would do to $amount, for the
     * order review screen — no lock, nothing claimed, nothing incremented.
     * The real applyDiscount() at actual checkout time re-validates
     * everything under a lock regardless, so a code that becomes invalid
     * between the preview and the real purchase is still caught correctly;
     * this only has to be honest, not airtight.
     */
    public function preview(User $user, string $code, int $amount): PromoPreview
    {
        $promo = PromoCode::where('code', strtoupper(trim($code)))->first();

        if (! $promo) {
            throw new PromoCodeException(self::UNUSABLE);
        }

        $this->validateLimits($promo, $user);

        if (! $promo->isDiscount()) {
            throw new PromoCodeException(
                'Este código es un regalo: canjéalo desde "Canjear código", no aquí.'
            );
        }

        return new PromoPreview($promo->discountedAmount($amount), $promo);
    }

    /**
     * Locks the code's row for the rest of the transaction, then applies
     * every limit check that does not depend on which kind of code this
     * turns out to be. Does NOT increment anything, so a caller can still
     * reject a code for being the wrong kind (gift entered as a discount, or
     * vice versa) without spending a use.
     */
    private function lockAndValidate(string $code, User $user): PromoCode
    {
        $promo = PromoCode::where('code', strtoupper(trim($code)))->lockForUpdate()->first();

        if (! $promo) {
            throw new PromoCodeException(self::UNUSABLE);
        }

        $this->validateLimits($promo, $user);

        return $promo;
    }

    /**
     * Existence aside (callers look the row up their own way, locked or
     * not), everything else a code can be rejected for regardless of kind:
     * turned off, exhausted, or already used up by this person.
     *
     * The first two answer with the same words as "does not exist" on
     * purpose — see the UNUSABLE constant.
     */
    private function validateLimits(PromoCode $promo, User $user): void
    {
        if (! $promo->active) {
            throw new PromoCodeException(self::UNUSABLE);
        }

        if ($promo->isExhausted()) {
            throw new PromoCodeException(self::UNUSABLE);
        }

        if ($promo->max_redemptions_per_user !== null) {
            $usedByThisUser = PromoCodeRedemption::where('promo_code_id', $promo->id)
                ->where('user_id', $user->id)
                ->count();

            if ($usedByThisUser >= $promo->max_redemptions_per_user) {
                throw new PromoCodeException('Ya usaste este código.');
            }
        }
    }

    private function recordRedemption(PromoCode $promo, User $user): PromoCodeRedemption
    {
        $promo->increment('redemptions_count');

        return PromoCodeRedemption::create([
            'promo_code_id' => $promo->id,
            'user_id' => $user->id,
        ]);
    }
}
