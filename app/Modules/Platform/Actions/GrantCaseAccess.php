<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
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
    public function grant(
        User $user,
        MysteryCase $case,
        string $source = Entitlement::SOURCE_PURCHASE,
        ?Carbon $expiresAt = null,
    ): Entitlement {
        // Idempotent: re-running a purchase webhook or a grant command must
        // not create a second right to the same case.
        return Entitlement::updateOrCreate(
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
    }

    public function revoke(User $user, MysteryCase $case): void
    {
        Entitlement::where('user_id', $user->id)
            ->where('mystery_case_id', $case->id)
            ->delete();
    }
}
