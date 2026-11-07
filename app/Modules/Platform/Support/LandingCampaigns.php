<?php

namespace App\Modules\Platform\Support;

use App\Models\User;
use App\Modules\Immersion\Models\CaseRating;
use App\Modules\Immersion\Models\Player;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Http\Resources\CaseCardData;
use App\Modules\Platform\Models\LandingCampaign;
use App\Modules\Platform\Models\LandingCampaignCase;
use App\Modules\Platform\Models\LandingCampaignEvent;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Pricing\QuoteBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Campaign presentation and attribution stay in Platform, never in the game engine. */
class LandingCampaigns
{
    public function forOffer(?array $offer): ?LandingCampaign
    {
        if (! $offer || ($offer['kind'] ?? null) === 'gift') {
            return null;
        }

        return LandingCampaign::query()
            ->where('active', true)
            ->whereHas('promoCode', fn ($query) => $query->where('code', $offer['code']))
            ->with(['promoCode', 'selections.mysteryCase'])
            ->first();
    }

    /** @return array<string, mixed>|null */
    public function publicPayload(LandingCampaign $campaign, array $offer, Request $request, ?User $user = null): ?array
    {
        $paid = $campaign->selections
            ->where('placement', LandingCampaignCase::PAID)
            ->filter(fn (LandingCampaignCase $selection) => $selection->mysteryCase?->isPublished() && ! $selection->mysteryCase->isFree())
            ->values();

        // A partially configured campaign must never become a broken paid-ad page.
        if ($paid->count() !== 3 || $paid->where('recommended', true)->count() !== 1) {
            return null;
        }

        $free = $campaign->selections
            ->where('placement', LandingCampaignCase::FREE)
            ->filter(fn (LandingCampaignCase $selection) => $selection->mysteryCase?->isPublished() && $selection->mysteryCase->isFree())
            ->take(2)
            ->values();

        $ratingByCase = CaseRating::query()
            ->whereIn('case_slug', $campaign->selections->pluck('mysteryCase.slug')->filter())
            ->selectRaw('case_slug, AVG(rating_half_steps) / 2 as average, COUNT(*) as count')
            ->groupBy('case_slug')->get()->keyBy('case_slug');
        $rating = fn (MysteryCase $case) => ($row = $ratingByCase->get($case->slug)) && $row->count >= 5
            ? ['average' => round((float) $row->average, 1), 'count' => (int) $row->count]
            : null;
        $card = fn (LandingCampaignCase $selection) => $this->card($selection->mysteryCase, $offer, $request, $selection->recommended, $rating($selection->mysteryCase));

        return [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'eyebrow' => $campaign->eyebrow,
            'headline' => $campaign->headline,
            'description' => $campaign->description,
            'recommended' => $card($paid->firstWhere('recommended', true)),
            'paid' => $paid->map($card)->values()->all(),
            'free' => $free->map(fn (LandingCampaignCase $selection) => $this->card($selection->mysteryCase, null, $request, false))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function card(MysteryCase $case, ?array $offer, Request $request, bool $recommended, ?array $rating = null): array
    {
        $card = CaseCardData::summary($case, $rating);
        $card['landing_cover_url'] = $case->landingCoverUrl();
        $card['recommended'] = $recommended;
        $card['offer_amount'] = null;
        $card['price_per_person'] = null;

        if ($offer && ! $case->isFree() && ($promo = $this->promoFor($offer))) {
            $quote = QuoteBuilder::make()->forRequest(
                $case->price_amount,
                $promo->discountedAmount($case->price_amount),
                $request,
                QuoteBuilder::percentOff($promo),
            );
            $card['offer_amount'] = $quote->total;
            $card['currency'] = $quote->currency;
            $card['price_amount'] = $quote->list;
            $card['price_per_person'] = $case->max_players > 1 ? (int) ceil($quote->total / $case->max_players) : null;
        }

        return $card;
    }

    private function promoFor(array $offer): ?\App\Modules\Platform\Models\PromoCode
    {
        return \App\Modules\Platform\Models\PromoCode::where('code', $offer['code'])->first();
    }

    public function campaignForFreeClaim(Request $request, ?User $user = null): ?LandingCampaign
    {
        $code = Attribution::pendingPromo($request, $user);
        if (! $code) return null;

        return LandingCampaign::query()
            ->where('active', true)
            ->whereHas('promoCode', fn ($query) => $query->where('code', $code))
            ->first();
    }

    /** @return array<string, mixed>|null */
    public function finalOfferFor(Player $player, RedeemPromoCode $promos): ?array
    {
        $case = MysteryCase::where('slug', $player->game->case_slug)->first();
        if (! $case?->isFree()) return null;

        $campaignId = Order::query()
            ->where('user_id', $player->game->user_id)
            ->where('mystery_case_id', $case->id)
            ->where('provider', 'free')
            ->whereNotNull('landing_campaign_id')
            ->latest('id')
            ->value('landing_campaign_id');

        $generic = MysteryCase::published()->where('price_amount', '>', 0)->ordered()->first();
        if (! $campaignId) return $generic ? ['kind' => 'generic', 'case' => CaseCardData::summary($generic)] : null;

        $campaign = LandingCampaign::with(['promoCode', 'selections.mysteryCase'])->find($campaignId);
        $recommended = $campaign?->selections->first(fn (LandingCampaignCase $item) => $item->placement === LandingCampaignCase::PAID && $item->recommended);
        if (! $campaign || ! $campaign->active || ! $recommended?->mysteryCase?->isPublished()) {
            return $generic ? ['kind' => 'generic', 'case' => CaseCardData::summary($generic)] : null;
        }

        $offer = $promos->landingOffer(null, $campaign->promoCode->code, $recommended->mysteryCase);
        if (! $offer) return $generic ? ['kind' => 'generic', 'case' => CaseCardData::summary($generic)] : null;

        $this->track(request(), $campaign, LandingCampaignEvent::POST_FREE_VIEW, $recommended->mysteryCase, $player);

        return [
            'kind' => 'campaign',
            'campaign_id' => $campaign->id,
            'code' => $offer['code'],
            'expires_at' => $offer['expires_at'],
            'value' => $offer['value'],
            'offer_kind' => $offer['kind'],
            'case' => $this->card($recommended->mysteryCase, $offer, request(), true),
        ];
    }

    public function track(Request $request, LandingCampaign $campaign, string $event, ?MysteryCase $case = null, ?Player $player = null): void
    {
        $allowed = [LandingCampaignEvent::VIEW, LandingCampaignEvent::PAID_CLICK, LandingCampaignEvent::FREE_CLICK, LandingCampaignEvent::POST_FREE_VIEW, LandingCampaignEvent::POST_FREE_CLICK];
        if (! in_array($event, $allowed, true)) return;

        $key = $request->session()->get('landing_campaign_visitor');
        if (! is_string($key)) {
            $key = Str::random(40);
            $request->session()->put('landing_campaign_visitor', $key);
        }

        $dedupe = hash('sha256', implode('|', [
            $campaign->id, $event, $case?->id, $player?->game_id, $player?->id, $key,
            $event === LandingCampaignEvent::VIEW ? now()->toDateString() : '',
        ]));

        LandingCampaignEvent::firstOrCreate(['dedupe_key' => $dedupe], [
            'landing_campaign_id' => $campaign->id,
            'mystery_case_id' => $case?->id,
            'user_id' => $request->user()?->id,
            'game_id' => $player?->game_id,
            'player_id' => $player?->id,
            'event' => $event,
            'occurred_at' => now(),
        ]);
    }
}
