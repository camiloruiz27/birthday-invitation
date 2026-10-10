<?php

namespace App\Modules\Platform\Support;

/**
 * Is this visit coming from inside another app's browser?
 *
 * Tapping an ad in TikTok or Instagram does not open the phone's browser: it
 * opens that app's own embedded one. It is a different place to be a customer:
 * it has its own cookie jar, it can block the anti-bot widget and the payment
 * page, and the link in the confirmation email opens somewhere else entirely.
 * Knowing that lets the page say so, and lets us count how much of a campaign's
 * traffic lands there.
 *
 * Decided on the server from the User-Agent, not in the browser, so the very
 * first response already knows (no flash of the wrong page) and there is one
 * list of signatures to keep up to date. User-Agent strings are not a contract:
 * a miss only means no notice is shown, never that something breaks.
 */
final class InAppBrowser
{
    public const TIKTOK = 'tiktok';

    public const INSTAGRAM = 'instagram';

    public const FACEBOOK = 'facebook';

    public const OTHER = 'other';

    /**
     * Which in-app browser the User-Agent is, or null for an ordinary browser.
     */
    public static function detect(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        // Order matters: Instagram's and TikTok's own strings are the more
        // specific, and an Instagram webview also carries Facebook markers.
        if (preg_match('/musical_ly|bytedance|tiktok|trill/i', $userAgent) === 1) {
            return self::TIKTOK;
        }

        if (stripos($userAgent, 'Instagram') !== false) {
            return self::INSTAGRAM;
        }

        if (preg_match('/FBAN|FBAV|FB_IAB|FBIOS/', $userAgent) === 1) {
            return self::FACEBOOK;
        }

        // Other apps that open links in their own embedded browser, and the
        // generic Android WebView marker ("; wv)").
        if (preg_match('/Snapchat|\bLine\/|; wv\)/i', $userAgent) === 1) {
            return self::OTHER;
        }

        return null;
    }
}
