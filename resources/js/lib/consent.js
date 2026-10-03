/**
 * Cookie consent for the analytics tools (Google Analytics 4, Microsoft
 * Clarity), in three levels:
 *
 *   1. Basic, anonymous, no cookies — always on (unless PLATFORM_ANALYTICS_BASIC
 *      is switched off). Both tools load with storage DENIED, so neither
 *      writes a cookie: Google receives cookieless pings (a visit, the page and
 *      where it came from) and Clarity runs in its cookieless mode. It answers
 *      "did someone arrive, and from where". It cannot follow a visitor from
 *      one page to the next, so visit length and bounce rate are not reliable
 *      at this level.
 *   2. Audience analytics (Google Analytics cookies) — opt-in. Sessions, time
 *      on the site, bounce rate, returning visitors.
 *   3. Session recordings and heatmaps (Clarity cookies) — opt-in, separate.
 *
 * Why opt-in for 2 and 3: Ley 1581 de 2012 asks for prior, express and
 * informed authorization, and the SIC treats analytics cookies as personal-data
 * processing with no exempt category. Level 1 is disclosed in the banner and in
 * the policies instead of asked for; that is the owner's call, and
 * PLATFORM_ANALYTICS_BASIC=false turns it off, going back to "nothing loads
 * until the visitor accepts".
 *
 * The choice lives in one first-party cookie, `mc_consent`, which is strictly
 * necessary (it exists only to remember the answer). app.blade.php publishes
 * the ids in <meta name="mc-analytics"> and this module injects the scripts.
 */

import { sanitizeUrl } from './analytics';

const COOKIE_NAME = 'mc_consent';
const COOKIE_MAX_AGE = 60 * 60 * 24 * 180; // 6 months, then ask again.

// Bump when the categories change in a way that needs a fresh answer: an
// older stored choice is then treated as "not decided yet".
const CONSENT_VERSION = 2;

export const CONSENT_EVENT = 'mc:consent';
export const OPEN_SETTINGS_EVENT = 'mc:open-cookie-settings';

const GOOGLE_COOKIES = [/^_ga/, /^_gid$/, /^_gat/];
const CLARITY_COOKIES = [/^_clck$/, /^_clsk$/, /^CLID$/, /^ANONCHK$/, /^SM$/, /^MR$/, /^MUID$/];

let googleStarted = false;
let clarityStarted = false;
let lastPageView = null;

function readCookie(name) {
    if (typeof document === 'undefined') return null;

    const match = document.cookie
        .split('; ')
        .find((entry) => entry.startsWith(`${name}=`));

    return match ? decodeURIComponent(match.slice(name.length + 1)) : null;
}

function writeCookie(name, value) {
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';

    document.cookie =
        `${name}=${encodeURIComponent(value)}; Max-Age=${COOKIE_MAX_AGE}; Path=/; SameSite=Lax${secure}`;
}

/**
 * The stored choice, or null when the visitor has not decided (or decided
 * under an older version of the categories).
 *
 * @returns {{ analytics: boolean, recording: boolean, ts: number } | null}
 */
export function getConsent() {
    try {
        const raw = readCookie(COOKIE_NAME);
        if (!raw) return null;

        const parsed = JSON.parse(raw);
        if (
            parsed?.v !== CONSENT_VERSION ||
            typeof parsed.analytics !== 'boolean' ||
            typeof parsed.recording !== 'boolean'
        ) {
            return null;
        }

        return { analytics: parsed.analytics, recording: parsed.recording, ts: parsed.ts };
    } catch {
        return null;
    }
}

export function setConsent({ analytics, recording }) {
    writeCookie(
        COOKIE_NAME,
        JSON.stringify({
            v: CONSENT_VERSION,
            analytics: Boolean(analytics),
            recording: Boolean(recording),
            ts: Date.now(),
        })
    );

    applyConsent();

    window.dispatchEvent(
        new CustomEvent(CONSENT_EVENT, {
            detail: { analytics: Boolean(analytics), recording: Boolean(recording) },
        })
    );
}

export function openCookieSettings() {
    window.dispatchEvent(new CustomEvent(OPEN_SETTINGS_EVENT));
}

function analyticsConfig() {
    const meta = document.querySelector('meta[name="mc-analytics"]');

    return {
        ga: meta?.dataset.ga || '',
        clarity: meta?.dataset.clarity || '',
        basic: meta?.dataset.basic === '1',
    };
}

function injectScript(src) {
    const script = document.createElement('script');
    script.async = true;
    script.src = src;
    document.head.appendChild(script);
}

function expireCookie(name) {
    const host = window.location.hostname;
    const parts = host.split('.');
    const domains = [undefined, host];

    // The tools may have set the cookie on a parent domain.
    for (let i = 1; i < parts.length - 1; i += 1) {
        domains.push(`.${parts.slice(i).join('.')}`);
    }

    domains.forEach((domain) => {
        const scope = domain ? `; Domain=${domain}` : '';
        document.cookie = `${name}=; Max-Age=0; Path=/${scope}`;
    });
}

function purgeCookies(patterns) {
    document.cookie
        .split('; ')
        .map((entry) => entry.split('=')[0])
        .filter((name) => patterns.some((pattern) => pattern.test(name)))
        .forEach(expireCookie);
}

/**
 * Sends one page_view with the token-free URL. Inertia navigations never
 * reload the page, so GA cannot see them by itself, and its own automatic
 * history tracking would send the raw URL — which is why the automatic
 * page_view is switched off and this one is explicit. With storage denied it
 * goes out as a cookieless ping.
 */
export function trackPageView() {
    if (typeof window.gtag !== 'function') return;

    const location = sanitizeUrl(window.location.href);
    const path = sanitizeUrl(window.location.pathname);

    // Inertia fires `navigate` for the initial load too, and a partial reload
    // (the payment page polling itself) lands on the same address: neither
    // is a new page view.
    if (location === lastPageView) return;
    lastPageView = location;

    window.gtag('event', 'page_view', {
        page_location: location,
        page_path: path,
        page_title: document.title,
    });
}

/**
 * Google Analytics. Storage starts at what the visitor allowed; a later
 * change is a consent update, never a reload.
 */
function applyGoogle(id, granted) {
    if (!googleStarted) {
        googleStarted = true;

        window.dataLayer = window.dataLayer || [];
        window.gtag = function gtag() {
            window.dataLayer.push(arguments);
        };

        // The default has to come before `config`. Advertising signals are
        // never granted: this is measurement only.
        window.gtag('consent', 'default', {
            analytics_storage: granted ? 'granted' : 'denied',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
        });
        window.gtag('js', new Date());
        window.gtag('config', id, {
            send_page_view: false,
            page_location: sanitizeUrl(window.location.href),
            page_path: sanitizeUrl(window.location.pathname),
            allow_google_signals: false,
            allow_ad_personalization_signals: false,
        });

        injectScript(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`);
        trackPageView();

        return;
    }

    window.gtag('consent', 'update', { analytics_storage: granted ? 'granted' : 'denied' });

    if (!granted) purgeCookies(GOOGLE_COOKIES);
}

/**
 * Microsoft Clarity. Loaded whatever the answer (Microsoft's "advanced
 * mode"): without analytics storage it runs cookieless, with no recordings
 * that follow a visitor across pages.
 */
function applyClarity(id, granted) {
    window.clarity =
        window.clarity ||
        function clarityQueue() {
            (window.clarity.q = window.clarity.q || []).push(arguments);
        };

    // Calls made before the tag has loaded are queued and replayed by it.
    window.clarity('consentv2', {
        ad_Storage: 'denied',
        analytics_Storage: granted ? 'granted' : 'denied',
    });

    if (!clarityStarted) {
        clarityStarted = true;
        injectScript(`https://www.clarity.ms/tag/${encodeURIComponent(id)}`);

        return;
    }

    if (!granted) purgeCookies(CLARITY_COOKIES);
}

/**
 * Brings both tools in line with what the visitor allowed. Safe to call at
 * startup and after every change.
 */
export function applyConsent() {
    if (typeof document === 'undefined') return;

    const { ga, clarity, basic } = analyticsConfig();
    const consent = getConsent();

    if (ga) {
        if (basic || consent?.analytics) {
            window[`ga-disable-${ga}`] = false;
            applyGoogle(ga, Boolean(consent?.analytics));
        } else if (googleStarted) {
            // Basic measurement is off and the visitor withdrew: not even a
            // cookieless ping may go out any more.
            window[`ga-disable-${ga}`] = true;
            applyGoogle(ga, false);
        }
    }

    if (clarity) {
        if (basic || consent?.recording) {
            applyClarity(clarity, Boolean(consent?.recording));
        } else if (clarityStarted) {
            applyClarity(clarity, false);

            try {
                window.clarity('stop');
            } catch {
                // Clarity's own shutdown failing must not break the page.
            }
        }
    }
}

/** Called once at startup. */
export function initConsent() {
    applyConsent();
}
