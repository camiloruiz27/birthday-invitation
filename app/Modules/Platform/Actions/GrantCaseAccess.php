<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Immersion\Support\GameCost;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Support\Carbon;

/**
 * The single door to case access.
 *
 * Purchases, manual grants, promos and any future subscription all come
 * through here. Nothing else writes an entitlement, so there is exactly one
 * place to look when asking "how could this user have got access?".
 */
class GrantCaseAccess
{
    public function __construct(private AiCredits $credits)
    {
    }

    public function grant(
        User $user,
        MysteryCase $case,
        string $source = Entitlement::SOURCE_PURCHASE,
        ?Carbon $expiresAt = null,
    ): Entitlement {
        // Idempotent: re-running a purchase webhook or a grant command must
        // not create a second right to the same case.
        $entitlement = Entitlement::updateOrCreate(
            [
                'user_id' => $user->id,
                'mystery_case_id' => $case->id,
            ],
            [
                'source' => $source,
                'granted_at' => now(),
                'expires_at' => $expiresAt,
            ]
        );

        // A case arrives playable to its limit: enough AI credits for every
        // question its roster allows plus its most expensive ending. Derived
        // from the case itself, so a bigger case comes with more — a fixed
        // figure would quietly stop covering one.
        //
        // Only on creation. Re-granting access to a case someone already owns
        // must not mint credits, or a replayed purchase webhook would be free
        // money.
        if ($entitlement->wasRecentlyCreated) {
            $this->credits->grant(
                $user,
                $this->includedCredits($case),
                CreditLedgerEntry::REASON_GRANT,
                "Creditos incluidos con \"{$case->name}\""
            );
        }

        return $entitlement;
    }

    /**
     * A catalog row can outlive its manifest — a case pulled from the install
     * still belongs to whoever bought it. Nothing to price then, and nothing
     * to grant.
     */
    private function includedCredits(MysteryCase $case): int
    {
        if (! config('immersion.credits.included_with_case', true)) {
            return 0;
        }

        $definition = app(CaseRegistry::class)->find($case->slug);

        return $definition ? app(GameCost::class)->maxForCase($definition) : 0;
    }

    public function revoke(User $user, MysteryCase $case): void
    {
        Entitlement::where('user_id', $user->id)
            ->where('mystery_case_id', $case->id)
            ->delete();
    }
}
