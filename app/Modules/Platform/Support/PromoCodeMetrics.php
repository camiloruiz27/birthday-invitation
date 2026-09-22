<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;

/**
 * Numbers for the admin "Códigos" screen. Codes are still created only from
 * the console (see CreatePromoCode) — this is read-only, same spirit as
 * PlatformMetrics: aggregates and a short recent list, no money figures
 * (checkout has no revenue reporting elsewhere either).
 */
class PromoCodeMetrics
{
    private const RECENT_REDEMPTIONS = 10;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'summary' => $this->summary(),
            'codes' => $this->codes(),
            'recentRedemptions' => $this->recentRedemptions(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        return [
            'total' => PromoCode::count(),
            'active' => PromoCode::where('active', true)->count(),
            'gifts' => PromoCode::where(fn ($query) => $query
                ->whereNotNull('grants_case_slug')
                ->orWhere('grants_any_case', true)
                ->orWhereNotNull('grants_credits'))->count(),
            'discounts' => PromoCode::whereNotNull('discount_type')->count(),
            'exhausted' => PromoCode::whereNotNull('max_redemptions')
                ->whereColumn('redemptions_count', '>=', 'max_redemptions')
                ->count(),
            'total_redemptions' => PromoCodeRedemption::count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function codes(): array
    {
        return PromoCode::query()
            ->orderByDesc('id')
            ->get()
            ->map(fn (PromoCode $promo) => [
                'id' => $promo->id,
                'code' => $promo->code,
                'type' => $promo->isDiscount() ? 'discount' : 'gift',
                'grant' => $promo->describeGrant(),
                'redemptions_count' => $promo->redemptions_count,
                'max_redemptions' => $promo->max_redemptions,
                'active' => $promo->active,
                'exhausted' => $promo->isExhausted(),
                'note' => $promo->note,
                'created_at' => $promo->created_at,
            ])
            ->all();
    }

    /**
     * Who claimed what, most recent first — the audit trail the console
     * listing cannot show.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentRedemptions(): array
    {
        return PromoCodeRedemption::query()
            ->with(['promoCode:id,code', 'user:id,name,email'])
            ->latest()
            ->limit(self::RECENT_REDEMPTIONS)
            ->get()
            ->map(fn (PromoCodeRedemption $redemption) => [
                'id' => $redemption->id,
                'code' => $redemption->promoCode->code,
                'user_name' => $redemption->user->name,
                'user_email' => $redemption->user->email,
                'has_order' => $redemption->order_id !== null,
                'created_at' => $redemption->created_at,
            ])
            ->all();
    }
}
