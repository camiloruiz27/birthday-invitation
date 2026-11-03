<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\ReferralCode;
use App\Modules\Platform\Models\ReferralConversion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminReferralsController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                ReferralConversion::STATUS_PENDING,
                ReferralConversion::STATUS_APPROVED,
                ReferralConversion::STATUS_CANCELLED,
                ReferralConversion::STATUS_VOIDED,
            ])],
            'code' => ['nullable', 'string', 'max:60'],
            'search' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = ReferralConversion::query()
            ->with([
                'referralCode:id,code,active',
                'referrer:id,name,email',
                'referred:id,name,email',
                'order:id,mystery_case_id,amount,currency,status,paid_at',
                'order.mysteryCase:id,name',
                'rewardPromoCode:id,active,expires_at,redemptions_count',
            ])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['code'] ?? null, fn ($query, $code) => $query->whereHas('referralCode', fn ($codes) => $codes->where('code', 'like', '%'.strtoupper($code).'%')))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->whereHas('referrer', fn ($users) => $users->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('referred', fn ($users) => $users->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to));

        $conversions = (clone $query)->latest()->limit(100)->get();
        $approved = $conversions->where('status', ReferralConversion::STATUS_APPROVED);
        $sales = $approved->groupBy(fn (ReferralConversion $conversion) => $conversion->order?->currency ?? 'COP')
            ->map(fn ($items) => $items->sum(fn (ReferralConversion $conversion) => (int) ($conversion->order?->amount ?? 0)))
            ->map(fn ($amount, $currency) => ['currency' => $currency, 'amount' => $amount])
            ->values();
        $codes = ReferralCode::query()
            ->with('user:id,name,email')
            ->withCount('conversions')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (ReferralCode $code) => [
                'id' => $code->id,
                'code' => $code->code,
                'active' => $code->active,
                'eligible_at' => $code->eligible_at?->toIso8601String(),
                'user_name' => $code->user?->name,
                'conversions_count' => $code->conversions_count,
            ])->values();

        return Inertia::render('Admin/Referrals', [
            'filters' => [
                'status' => $filters['status'] ?? '',
                'code' => $filters['code'] ?? '',
                'search' => $filters['search'] ?? '',
                'from' => $filters['from'] ?? '',
                'to' => $filters['to'] ?? '',
            ],
            'summary' => [
                'total' => $conversions->count(),
                'approved' => $approved->count(),
                'pending' => $conversions->where('status', ReferralConversion::STATUS_PENDING)->count(),
                'alerts' => $conversions->filter(fn (ReferralConversion $conversion) => ! empty($conversion->risk_flags))->count(),
                'rewards_issued' => $approved->whereNotNull('reward_promo_code_id')->count(),
                'sales' => $sales,
            ],
            'codes' => $codes,
            'conversions' => $conversions->map(fn (ReferralConversion $conversion) => [
                'id' => $conversion->id,
                'status' => $conversion->status,
                'created_at' => $conversion->created_at?->toIso8601String(),
                'code' => $conversion->referralCode?->code,
                'code_id' => $conversion->referralCode?->id,
                'code_active' => $conversion->referralCode?->active,
                'referrer' => $conversion->referrer ? $conversion->referrer->only(['name', 'email']) : null,
                'referred' => $conversion->referred ? $conversion->referred->only(['name', 'email']) : null,
                'case_name' => $conversion->order?->mysteryCase?->name,
                'amount' => $conversion->order?->amount,
                'currency' => $conversion->order?->currency,
                'recipient_discount_percent' => $conversion->recipient_discount_percent,
                'referrer_reward_percent' => $conversion->referrer_reward_percent,
                'reward' => $conversion->rewardPromoCode ? [
                    'active' => $conversion->rewardPromoCode->active,
                    'used' => $conversion->rewardPromoCode->redemptions_count > 0,
                    'expires_at' => $conversion->rewardPromoCode->expires_at?->toIso8601String(),
                ] : null,
                'risk_flags' => $conversion->risk_flags ?? [],
            ])->values(),
        ]);
    }

    public function toggle(ReferralCode $referralCode): RedirectResponse
    {
        $referralCode->update(['active' => ! $referralCode->active]);

        return back()->with('status', $referralCode->active ? 'Código de referido activado.' : 'Código de referido desactivado.');
    }
}
