<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Platform\Ads\AdEvents;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Support\LandingCampaigns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Gives a published zero-price case to an account without creating a payment
 * link. The user's row is locked so two quick clicks cannot mint two orders
 * or included-credit grants.
 */
class ClaimFreeCase
{
    public function __construct(
        private GrantCaseAccess $access,
        private Request $request,
        private LandingCampaigns $campaigns,
    ) {
    }

    public function claim(User $user, MysteryCase $case): ?Order
    {
        if (! $case->isPublished() || ! $case->isFree()) {
            throw new LogicException('Only published zero-price cases can be claimed for free.');
        }

        return DB::transaction(function () use ($user, $case) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($user->ownsCase($case)) {
                return null;
            }

            $campaign = $this->campaigns->campaignForFreeClaim($this->request, $user);

            $order = Order::create([
                'user_id' => $user->id,
                'type' => Order::TYPE_CASE,
                'mystery_case_id' => $case->id,
                'amount' => 0,
                'list_amount' => 0,
                'discount_amount' => 0,
                'currency' => $case->currency,
                'status' => Order::STATUS_APPROVED,
                'provider' => 'free',
                'landing_campaign_id' => $campaign?->id,
                'reference' => strtoupper(bin2hex(random_bytes(12))),
                'paid_at' => now(),
            ] + AdEvents::orderContext($this->request, $user));

            // GrantCaseAccess is itself idempotent and grants exactly the AI
            // credits included with this case on this first entitlement.
            $this->access->grant($user, $case, Entitlement::SOURCE_FREE);

            return $order;
        });
    }
}
