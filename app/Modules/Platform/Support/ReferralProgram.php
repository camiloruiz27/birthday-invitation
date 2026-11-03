<?php

namespace App\Modules\Platform\Support;

use App\Models\User;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\ReferralCode;
use App\Modules\Platform\Models\ReferralConversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Referral rules live here rather than in checkout controllers: an approved
 * order is the only thing that may create a reward, and retries must remain
 * harmless.
 */
class ReferralProgram
{
    public const SESSION_KEY = 'pending_referral';
    public const FIRST_RECIPIENT_PERCENT = 20;
    public const FIRST_REFERRER_PERCENT = 30;
    public const RETURNING_RECIPIENT_PERCENT = 15;
    public const RETURNING_REFERRER_PERCENT = 25;
    public const REWARD_DAYS = 90;

    public function referralFor(User $user): ?ReferralCode
    {
        $code = ReferralCode::firstWhere('user_id', $user->id);

        if ($code) {
            return $code;
        }

        return $this->createFor($user);
    }

    /**
     * Keeps the paid-order hook harmless for existing checkouts. Referral
     * links are now available from registration, rather than being earned
     * after a first paid case.
     */
    public function activateFromOrder(Order $order): ?ReferralCode
    {
        if (! $this->isPaidCase($order)) {
            return null;
        }

        $code = ReferralCode::firstWhere('user_id', $order->user_id);

        return $code ?: $this->createFor($order->user);
    }

    public function remember(Request $request, string $code): void
    {
        $referral = $this->findUsable($code);

        if ($referral) {
            $request->session()->put(self::SESSION_KEY, $referral->code);

            return;
        }

        $request->session()->forget(self::SESSION_KEY);
    }

    public function pending(Request $request): ?string
    {
        $code = $this->cleanCode($request->session()->get(self::SESSION_KEY));

        return $code && $this->findUsable($code) ? $code : null;
    }

    public function consumePending(Request $request, string $code): void
    {
        if ($this->cleanCode($request->session()->get(self::SESSION_KEY)) === strtoupper(trim($code))) {
            $request->session()->forget(self::SESSION_KEY);
        }
    }

    public function hasCode(string $code): bool
    {
        return $this->cleanCode($code) !== null
            && ReferralCode::where('code', strtoupper(trim($code)))->exists();
    }

    /** Read-only checkout quote for a referral code. */
    public function preview(User $buyer, string $code, int $amount): ReferralDiscount
    {
        $referral = $this->findUsable($code);

        if (! $referral || (int) $referral->user_id === (int) $buyer->id) {
            throw new PromoCodeException(RedeemPromoCode::UNUSABLE);
        }

        return $this->discountFor($buyer, $referral, $amount);
    }

    /** Locked checkout quote. It is attached to the newly created order next. */
    public function claim(User $buyer, string $code, int $amount): ReferralDiscount
    {
        return DB::transaction(function () use ($buyer, $code, $amount) {
            $clean = $this->cleanCode($code);
            $referral = $clean
                ? ReferralCode::where('code', $clean)->lockForUpdate()->first()
                : null;

            if (! $referral || ! $referral->isUsable() || (int) $referral->user_id === (int) $buyer->id) {
                throw new PromoCodeException(RedeemPromoCode::UNUSABLE);
            }

            return $this->discountFor($buyer, $referral, $amount);
        });
    }

    public function attachToOrder(ReferralDiscount $discount, User $buyer, Order $order): ReferralConversion
    {
        $flags = [];

        if ($order->client_ip && Order::query()
            ->where('user_id', $discount->referralCode->user_id)
            ->where('type', Order::TYPE_CASE)
            ->where('status', Order::STATUS_APPROVED)
            ->where('amount', '>', 0)
            ->where('client_ip', $order->client_ip)
            ->exists()) {
            // A shared connection is not fraud by itself, so it stays an
            // admin-only signal rather than cancelling a genuine sale.
            $flags[] = 'shared_ip';
        }

        return ReferralConversion::create([
            'referral_code_id' => $discount->referralCode->id,
            'referrer_user_id' => $discount->referralCode->user_id,
            'referred_user_id' => $buyer->id,
            'order_id' => $order->id,
            'recipient_discount_percent' => $discount->recipientDiscountPercent,
            'referrer_reward_percent' => $discount->referrerRewardPercent,
            'status' => ReferralConversion::STATUS_PENDING,
            'risk_flags' => $flags ?: null,
        ]);
    }

    /** Safe to call after every approved webhook or reconciliation attempt. */
    public function approveFor(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $conversion = ReferralConversion::where('order_id', $order->id)->lockForUpdate()->first();

            if (! $conversion || in_array($conversion->status, [ReferralConversion::STATUS_CANCELLED, ReferralConversion::STATUS_VOIDED], true)) {
                return;
            }

            if (! $conversion->reward_promo_code_id) {
                $reward = PromoCode::create([
                    'code' => $this->newRewardCode(),
                    'discount_type' => PromoCode::DISCOUNT_PERCENT,
                    'discount_value' => $conversion->referrer_reward_percent,
                    'max_redemptions' => 1,
                    'max_redemptions_per_user' => 1,
                    'assigned_user_id' => $conversion->referrer_user_id,
                    'active' => true,
                    'expires_at' => now()->addDays(self::REWARD_DAYS),
                    'applies_to' => PromoCode::APPLIES_CASE,
                    'note' => "Recompensa de referido #{$conversion->id}",
                ]);

                $conversion->reward_promo_code_id = $reward->id;
                $conversion->rewarded_at = now();
            }

            $conversion->status = ReferralConversion::STATUS_APPROVED;
            $conversion->save();
        });
    }

    public function cancelFor(Order $order): void
    {
        ReferralConversion::where('order_id', $order->id)
            ->where('status', ReferralConversion::STATUS_PENDING)
            ->update([
                'status' => ReferralConversion::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
    }

    public function voidFor(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $conversion = ReferralConversion::where('order_id', $order->id)->lockForUpdate()->first();

            if (! $conversion || $conversion->status === ReferralConversion::STATUS_VOIDED) {
                return;
            }

            $flags = array_values(array_unique([...(array) $conversion->risk_flags, 'voided_after_approval']));
            $conversion->update([
                'status' => ReferralConversion::STATUS_VOIDED,
                'risk_flags' => $flags,
                'cancelled_at' => now(),
            ]);

            if ($conversion->rewardPromoCode && ! $conversion->rewardPromoCode->redemptions()->exists()) {
                $conversion->rewardPromoCode->update(['active' => false]);
            }
        });
    }

    private function discountFor(User $buyer, ReferralCode $referral, int $amount): ReferralDiscount
    {
        $first = ! $this->hasPaidCase($buyer->id);
        $recipient = $first ? self::FIRST_RECIPIENT_PERCENT : self::RETURNING_RECIPIENT_PERCENT;
        $reward = $first ? self::FIRST_REFERRER_PERCENT : self::RETURNING_REFERRER_PERCENT;

        return new ReferralDiscount(
            $referral,
            (int) floor($amount * (100 - $recipient) / 100),
            $recipient,
            $reward,
        );
    }

    private function hasPaidCase(int $userId): bool
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('type', Order::TYPE_CASE)
            ->where('status', Order::STATUS_APPROVED)
            ->where('amount', '>', 0)
            ->exists();
    }

    private function isPaidCase(Order $order): bool
    {
        return $order->type === Order::TYPE_CASE
            && $order->status === Order::STATUS_APPROVED
            && $order->amount > 0;
    }

    private function findUsable(string $code): ?ReferralCode
    {
        $clean = $this->cleanCode($code);
        $referral = $clean ? ReferralCode::where('code', $clean)->first() : null;

        return $referral && $referral->isUsable() ? $referral : null;
    }

    private function createFor(User $user): ReferralCode
    {
        return ReferralCode::firstOrCreate(
            ['user_id' => $user->id],
            ['code' => $this->newReferralCode(), 'eligible_at' => now(), 'active' => true]
        );
    }

    private function newReferralCode(): string
    {
        do {
            $code = 'REF-'.strtoupper(bin2hex(random_bytes(5)));
        } while (ReferralCode::where('code', $code)->exists() || PromoCode::where('code', $code)->exists());

        return $code;
    }

    private function newRewardCode(): string
    {
        do {
            $code = 'PREMIO-'.strtoupper(bin2hex(random_bytes(5)));
        } while (PromoCode::where('code', $code)->exists() || ReferralCode::where('code', $code)->exists());

        return $code;
    }

    private function cleanCode(mixed $code): ?string
    {
        if (! is_string($code)) {
            return null;
        }

        $code = strtoupper(trim($code));

        return $code !== '' && strlen($code) <= 60 && preg_match('/^[A-Z0-9_-]+$/', $code) === 1 ? $code : null;
    }
}
