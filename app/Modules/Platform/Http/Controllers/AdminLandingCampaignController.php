<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Platform\Models\LandingCampaign;
use App\Modules\Platform\Models\LandingCampaignCase;
use App\Modules\Platform\Models\LandingCampaignEvent;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminLandingCampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = LandingCampaign::with(['promoCode', 'selections.mysteryCase'])->latest()->get();
        $eventCounts = LandingCampaignEvent::query()
            ->select('landing_campaign_id', 'event', DB::raw('COUNT(*) as total'))
            ->groupBy('landing_campaign_id', 'event')->get()->groupBy('landing_campaign_id');
        $freeClaims = Order::query()->where('provider', 'free')->whereNotNull('landing_campaign_id')
            ->select('landing_campaign_id', DB::raw('COUNT(*) as total'))->groupBy('landing_campaign_id')->pluck('total', 'landing_campaign_id');
        $sales = Order::query()->where('status', Order::STATUS_APPROVED)->where('amount', '>', 0)->whereNotNull('promo_code_id')
            ->select('promo_code_id', DB::raw('COUNT(*) as total'))->groupBy('promo_code_id')->pluck('total', 'promo_code_id');

        return Inertia::render('Admin/LandingCampaigns', [
            'campaigns' => $campaigns->map(fn (LandingCampaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'active' => $campaign->active,
                'promo_code_id' => $campaign->promo_code_id,
                'promo_code' => $campaign->promoCode?->code,
                'eyebrow' => $campaign->eyebrow,
                'headline' => $campaign->headline,
                'description' => $campaign->description,
                'paid_case_ids' => $campaign->selections->where('placement', LandingCampaignCase::PAID)->pluck('mystery_case_id')->values(),
                'free_case_ids' => $campaign->selections->where('placement', LandingCampaignCase::FREE)->pluck('mystery_case_id')->values(),
                'recommended_case_id' => $campaign->selections->firstWhere('recommended', true)?->mystery_case_id,
                'metrics' => $this->metrics($campaign, $eventCounts[$campaign->id] ?? collect(), (int) ($freeClaims[$campaign->id] ?? 0), (int) ($sales[$campaign->promo_code_id] ?? 0)),
            ])->values(),
            'promoCodes' => PromoCode::query()->whereNotNull('discount_type')->where(fn ($query) => $query->whereNull('applies_to')->orWhere('applies_to', PromoCode::APPLIES_CASE))
                ->whereNull('assigned_user_id')->orderBy('code')->get(['id', 'code', 'active', 'expires_at', 'max_redemptions', 'redemptions_count', 'discount_type', 'discount_value']),
            'cases' => MysteryCase::published()->ordered()->get(['id', 'slug', 'name', 'price_amount', 'currency', 'duration_minutes', 'min_players', 'max_players']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $campaign = LandingCampaign::create($this->attributes($data));
        $this->sync($campaign, $data);

        return back()->with('status', 'Campaña de landing creada.');
    }

    public function update(Request $request, LandingCampaign $landingCampaign): RedirectResponse
    {
        $data = $this->validated($request, $landingCampaign);
        $landingCampaign->update($this->attributes($data));
        $this->sync($landingCampaign, $data);

        return back()->with('status', 'Campaña de landing actualizada.');
    }

    public function toggle(LandingCampaign $landingCampaign): RedirectResponse
    {
        $landingCampaign->update(['active' => ! $landingCampaign->active]);
        return back()->with('status', $landingCampaign->active ? 'Campaña activada.' : 'Campaña pausada.');
    }

    public function destroy(LandingCampaign $landingCampaign): RedirectResponse
    {
        if ($landingCampaign->active) {
            return back()->withErrors(['campaign' => 'Pausa la campaña antes de eliminarla.']);
        }

        $name = $landingCampaign->name;
        $landingCampaign->delete();

        return back()->with('status', "Campaña {$name} eliminada.");
    }

    private function validated(Request $request, ?LandingCampaign $editing = null): array
    {
        $data = $request->validate([
            'promo_code_id' => ['required', 'integer', Rule::exists('promo_codes', 'id'), Rule::unique('landing_campaigns', 'promo_code_id')->ignore($editing?->id)],
            'name' => ['required', 'string', 'max:120'],
            'active' => ['boolean'],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'paid_case_ids' => ['required', 'array', 'size:3'],
            'paid_case_ids.*' => ['integer', 'distinct', Rule::exists('mystery_cases', 'id')],
            'recommended_case_id' => ['required', 'integer', Rule::in($request->input('paid_case_ids', []))],
            'free_case_ids' => ['nullable', 'array', 'max:2'],
            'free_case_ids.*' => ['integer', 'distinct', Rule::exists('mystery_cases', 'id')],
        ]);

        $promo = PromoCode::findOrFail($data['promo_code_id']);
        if (! $promo->active || $promo->isExpired() || $promo->isExhausted() || ! $promo->isDiscount() || ! $promo->appliesTo(PromoCode::APPLIES_CASE) || $promo->assigned_user_id) {
            throw ValidationException::withMessages(['promo_code_id' => 'Elige un código público, activo, vigente y válido para casos.']);
        }

        $paid = MysteryCase::published()->whereIn('id', $data['paid_case_ids'])->get();
        if ($paid->count() !== 3 || $paid->contains(fn (MysteryCase $case) => $case->isFree())) {
            throw ValidationException::withMessages(['paid_case_ids' => 'Elige exactamente tres casos pagos publicados.']);
        }

        $freeIds = $data['free_case_ids'] ?? [];
        if (array_intersect($data['paid_case_ids'], $freeIds) !== []) {
            throw ValidationException::withMessages(['free_case_ids' => 'Un caso no puede estar en ambas secciones.']);
        }
        $free = MysteryCase::published()->whereIn('id', $freeIds)->get();
        if ($free->count() !== count($freeIds) || $free->contains(fn (MysteryCase $case) => ! $case->isFree())) {
            throw ValidationException::withMessages(['free_case_ids' => 'Los casos de prueba deben ser gratuitos y publicados.']);
        }

        return $data;
    }

    private function attributes(array $data): array
    {
        return collect($data)->only(['promo_code_id', 'name', 'active', 'eyebrow', 'headline', 'description'])->all();
    }

    private function sync(LandingCampaign $campaign, array $data): void
    {
        $campaign->selections()->delete();
        foreach ($data['paid_case_ids'] as $index => $caseId) {
            $campaign->selections()->create(['mystery_case_id' => $caseId, 'placement' => LandingCampaignCase::PAID, 'sort_order' => $index, 'recommended' => (int) $caseId === (int) $data['recommended_case_id']]);
        }
        foreach ($data['free_case_ids'] ?? [] as $index => $caseId) {
            $campaign->selections()->create(['mystery_case_id' => $caseId, 'placement' => LandingCampaignCase::FREE, 'sort_order' => $index]);
        }
    }

    /** @return array<string, int> */
    private function metrics(LandingCampaign $campaign, $events, int $freeClaims, int $sales): array
    {
        $freeOrders = Order::query()->where('landing_campaign_id', $campaign->id)->where('provider', 'free')->get(['user_id', 'mystery_case_id', 'paid_at']);
        $ownerIds = $freeOrders->pluck('user_id')->unique();
        $caseIds = $freeOrders->pluck('mystery_case_id')->unique();
        $caseSlugs = MysteryCase::whereIn('id', $caseIds)->pluck('slug');
        $games = $ownerIds->isEmpty() ? collect() : Game::query()->whereIn('user_id', $ownerIds)->whereIn('case_slug', $caseSlugs)->get(['user_id', 'case_slug', 'started_at', 'finished_at']);
        $conversions = $freeOrders->filter(fn (Order $free) => Order::query()
            ->where('user_id', $free->user_id)->where('type', Order::TYPE_CASE)->where('amount', '>', 0)
            ->where('status', Order::STATUS_APPROVED)->whereBetween('paid_at', [$free->paid_at, $free->paid_at?->copy()->addDays(30)])
            ->exists())->count();

        return [
            'views' => (int) ($events->firstWhere('event', LandingCampaignEvent::VIEW)?->total ?? 0),
            'paid_clicks' => (int) ($events->firstWhere('event', LandingCampaignEvent::PAID_CLICK)?->total ?? 0),
            'post_free_clicks' => (int) ($events->firstWhere('event', LandingCampaignEvent::POST_FREE_CLICK)?->total ?? 0),
            'registrations' => User::query()->where('attribution->promo', $campaign->promoCode->code)->count(),
            'checkout_starts' => Order::query()->where('promo_code_id', $campaign->promo_code_id)->where('type', Order::TYPE_CASE)->count(),
            'free_claims' => $freeClaims,
            'free_started' => $games->whereNotNull('started_at')->count(),
            'free_finished' => $games->whereNotNull('finished_at')->count(),
            'sales' => $sales,
            'free_to_paid' => $conversions,
        ];
    }
}
