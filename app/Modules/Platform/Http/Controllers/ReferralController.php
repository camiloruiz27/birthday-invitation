<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\ReferralConversion;
use App\Modules\Platform\Support\ReferralProgram;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __invoke(Request $request, ReferralProgram $referrals): Response
    {
        $user = $request->user();
        $referral = $referrals->referralFor($user);
        $conversions = ReferralConversion::query()
            ->where('referrer_user_id', $user->id)
            ->with(['order.mysteryCase:id,slug,name', 'rewardPromoCode:id,code,active,expires_at,redemptions_count'])
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Referrals', [
            'referral' => $referral ? [
                'code' => $referral->code,
                'active' => $referral->isUsable(),
                'share_url' => route('referrals.visit', $referral->code),
            ] : null,
            'summary' => [
                'approved' => $conversions->where('status', ReferralConversion::STATUS_APPROVED)->count(),
                'pending' => $conversions->where('status', ReferralConversion::STATUS_PENDING)->count(),
                'available_rewards' => $conversions->filter(fn (ReferralConversion $conversion) => $this->rewardAvailable($conversion))->count(),
            ],
            'conversions' => $conversions->map(fn (ReferralConversion $conversion) => [
                'id' => $conversion->id,
                'status' => $conversion->status,
                'created_at' => $conversion->created_at?->toIso8601String(),
                'case_name' => $conversion->order?->mysteryCase?->name,
                'recipient_discount_percent' => $conversion->recipient_discount_percent,
                'referrer_reward_percent' => $conversion->referrer_reward_percent,
                'reward' => $conversion->rewardPromoCode ? [
                    'code' => $conversion->rewardPromoCode->code,
                    'active' => $this->rewardAvailable($conversion),
                    'used' => $conversion->rewardPromoCode->redemptions_count > 0,
                    'expires_at' => $conversion->rewardPromoCode->expires_at?->toIso8601String(),
                ] : null,
            ])->values(),
        ]);
    }

    private function rewardAvailable(ReferralConversion $conversion): bool
    {
        $reward = $conversion->rewardPromoCode;

        return $reward
            && $reward->active
            && $reward->redemptions_count === 0
            && (! $reward->expires_at || ! $reward->expires_at->isPast());
    }
}
