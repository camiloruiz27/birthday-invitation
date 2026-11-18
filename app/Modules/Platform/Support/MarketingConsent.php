<?php

namespace App\Modules\Platform\Support;

use App\Models\User;
use Illuminate\Support\Facades\URL;

/**
 * The one place that changes whether someone receives promotional email.
 *
 * Authorisation (`marketing_consent_at`) and withdrawal
 * (`marketing_opted_out_at`) are two columns on purpose: the withdrawal wins
 * wherever either is read (User::acceptsMarketing), and it survives a later
 * re-authorisation being clicked by accident in a stale tab only if the user
 * means it — granting clears it, which is the user's explicit act.
 *
 * Promotional email only. Account mail and the mail of a game (verification,
 * recovery, access links) is not promotional and never asks this.
 */
class MarketingConsent
{
    public const SOURCE_REGISTER = 'register';

    public const SOURCE_PROFILE = 'profile';

    /** Authorises, and clears an earlier withdrawal: the user chose to come back. */
    public static function grant(User $user, string $source): void
    {
        $user->forceFill([
            'marketing_consent_at' => now(),
            'marketing_consent_source' => $source,
            'marketing_opted_out_at' => null,
        ])->save();
    }

    /** Leaves. Idempotent: the first withdrawal date is the one kept. */
    public static function revoke(User $user): void
    {
        if ($user->marketing_opted_out_at !== null) {
            return;
        }

        $user->forceFill(['marketing_opted_out_at' => now()])->save();
    }

    /**
     * The link in the footer of every promotional mail. Signed, not expiring:
     * the mail may be opened months later on another device, where there is no
     * session, and a person leaving must never be turned away.
     */
    public static function unsubscribeUrl(User $user): string
    {
        return URL::signedRoute('marketing.unsubscribe', ['user' => $user->id]);
    }
}
