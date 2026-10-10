<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;
use Illuminate\Support\Facades\DB;

/**
 * Numbers for the admin "Códigos" screen: aggregates, every code, and a short
 * recent list. Same spirit as PlatformMetrics. The only money figures are
 * what each code has actually taken off paid orders (Order::discount_amount)
 * and what those orders brought in — the cost of a promotion, which is what
 * deciding on the next one needs.
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
        // One query for every code's totals rather than one per row. Only
        // approved orders count: a pending or rejected one never cost
        // anything.
        $money = Order::query()
            ->where('status', Order::STATUS_APPROVED)
            ->whereNotNull('promo_code_id')
            ->groupBy('promo_code_id')
            ->select(
                'promo_code_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('COALESCE(SUM(discount_amount), 0) as discounted'),
                DB::raw('COALESCE(SUM(amount), 0) as collected')
            )
            ->get()
            ->keyBy('promo_code_id');

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
                'expired' => $promo->isExpired(),
                'expires_at' => $promo->expires_at,
                'applies_to' => $promo->applies_to,
                'paid_orders' => (int) ($money[$promo->id]->orders ?? 0),
                'discounted' => (int) ($money[$promo->id]->discounted ?? 0),
                'collected' => (int) ($money[$promo->id]->collected ?? 0),
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
