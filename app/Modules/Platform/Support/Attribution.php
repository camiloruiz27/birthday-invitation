<?php

namespace App\Modules\Platform\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Where a visitor came from, kept for as long as it takes them to buy.
 *
 * An ad click lands on any page with utm_* parameters, a platform click id
 * (ttclid for TikTok, fbclid for Meta) and often a promo code. The visitor
 * then browses, registers, confirms their email — possibly on another device
 * — and finally pays, each step on a URL that no longer carries any of it.
 * This is the one place that remembers it across those steps, so the landing
 * page an ad points at never matters to measurement or to a discount.
 *
 * Two touches are kept: the FIRST that ever brought them (who gets credit for
 * the customer) and the LAST (what the ad platforms' own attribution wants
 * for the click that ended in a sale).
 *
 * Capturing is done by the CaptureAttribution middleware, which calls
 * capture(); the rest of the platform only reads.
 */
final class Attribution
{
    private const SESSION_KEY = 'attribution';

    private const PROMO_KEY = 'pending_promo';

    /**
     * The only query parameters ever stored — anything else in a URL is
     * ignored, so a crafted link cannot plant arbitrary data on an account.
     */
    public const TRACKED = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'ttclid',
        'fbclid',
    ];

    private const MAX_VALUE_LENGTH = 200;

    /**
     * Reads the tracked parameters (and a promo code) off a request into the
     * session. A request carrying none of them changes nothing.
     */
    public static function capture(Request $request): void
    {
        $touch = [];

        foreach (self::TRACKED as $key) {
            $value = $request->query($key);

            if (is_string($value) && trim($value) !== '') {
                $touch[$key] = mb_substr(trim($value), 0, self::MAX_VALUE_LENGTH);
            }
        }

        if ($touch !== []) {
            // Landed inside another app's browser (TikTok, Instagram...): worth
            // knowing per campaign, because that is where sign-up and payment
            // are most likely to break (see InAppBrowser).
            if (($inApp = InAppBrowser::detect($request->userAgent())) !== null) {
                $touch['in_app'] = $inApp;
            }

            $touch['landing_path'] = mb_substr('/'.ltrim($request->path(), '/'), 0, self::MAX_VALUE_LENGTH);
            $touch['captured_at'] = now()->toIso8601String();

            $stored = (array) $request->session()->get(self::SESSION_KEY, []);

            $request->session()->put(self::SESSION_KEY, [
                // The first touch is never overwritten.
                'first' => $stored['first'] ?? $touch,
                'last' => $touch,
            ]);
        }

        $promo = self::cleanPromo($request->query('promo_code'));

        if ($promo !== null) {
            $request->session()->put(self::PROMO_KEY, $promo);
        }
    }

    /**
     * The campaign touches for this visitor: the session's, or — for someone
     * who arrived on another device than the one that clicked the ad — what
     * was saved on their account at registration.
     *
     * @return array{first?: array<string, string>, last?: array<string, string>}
     */
    public static function touches(Request $request, ?User $user = null): array
    {
        $stored = (array) $request->session()->get(self::SESSION_KEY, []);

        if ($stored === [] && $user) {
            $saved = (array) $user->attribution;
            $stored = array_filter([
                'first' => $saved['first'] ?? null,
                'last' => $saved['last'] ?? null,
            ]);
        }

        return $stored;
    }

    /**
     * The code a visitor arrived with and has not used yet — session first,
     * then the account.
     */
    public static function pendingPromo(Request $request, ?User $user = null): ?string
    {
        $code = self::cleanPromo($request->session()->get(self::PROMO_KEY));

        if ($code === null && $user) {
            $code = self::cleanPromo(((array) $user->attribution)['promo'] ?? null);
        }

        return $code;
    }

    /**
     * The code has been used on an order: forget it, so it does not keep
     * pre-filling every later purchase.
     */
    public static function consumePromo(Request $request, ?User $user, string $usedCode): void
    {
        $used = strtoupper(trim($usedCode));

        if (self::cleanPromo($request->session()->get(self::PROMO_KEY)) === $used) {
            $request->session()->forget(self::PROMO_KEY);
        }

        if ($user) {
            $saved = (array) $user->attribution;

            if (self::cleanPromo($saved['promo'] ?? null) === $used) {
                unset($saved['promo']);
                $user->forceFill(['attribution' => $saved ?: null])->save();
            }
        }
    }

    /**
     * The visitor has done what they came for (an order exists): forget where
     * they meant to go, so confirming their email or signing in later does not
     * send them back to a checkout for something they already bought.
     */
    public static function consumeIntent(Request $request, ?User $user): void
    {
        $request->session()->forget('url.intended');

        if ($user) {
            $saved = (array) $user->attribution;

            if (isset($saved['intended'])) {
                unset($saved['intended']);
                $user->forceFill(['attribution' => $saved ?: null])->save();
            }
        }
    }

    /**
     * What gets written onto a new account: both touches, the pending code
     * and (set by the registration screen) where the visitor meant to go.
     *
     * @return array<string, mixed>|null null when there is nothing to keep
     */
    public static function forNewUser(Request $request, ?string $intendedUrl = null): ?array
    {
        $snapshot = self::touches($request);

        if (($promo = self::pendingPromo($request)) !== null) {
            $snapshot['promo'] = $promo;
        }

        if ($intendedUrl !== null) {
            $snapshot['intended'] = $intendedUrl;
        }

        return $snapshot === [] ? null : $snapshot;
    }

    /**
     * A promo code as it may be stored: letters, digits, dash and underscore
     * only, upper-cased — the same shape codes are created in.
     */
    private static function cleanPromo(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = strtoupper(trim($value));

        return $value !== '' && strlen($value) <= 60 && preg_match('/^[A-Z0-9_-]+$/', $value) === 1
            ? $value
            : null;
    }
}
