/**
 * Thin wrapper around gtag. A no-op whenever analytics is not running — no
 * PLATFORM_GA_MEASUREMENT_ID configured, or the visitor has not accepted
 * analytics cookies (see lib/consent.js, which is what loads gtag.js now).
 * That is what keeps every call site from needing its own guard.
 *
 * No PII in `params` ever: GA4's terms forbid sending it, and Clarity has
 * nothing to wire here — it records without being told about events.
 */
export function trackEvent(name, params = {}) {
    if (typeof window === 'undefined' || typeof window.gtag !== 'function') return;

    window.gtag('event', name, params);
}

/**
 * TikTok's name for each standard event where it differs from the neutral
 * one used across the app (which follows Meta's).
 */
const TIKTOK_EVENT_NAMES = { Purchase: 'CompletePayment' };

/**
 * Reports a conversion to the TikTok and Meta pixels. A no-op unless the
 * visitor accepted the marketing category (lib/consent.js sets
 * window.mcMarketingConsent, and only then loads either pixel), so call sites
 * never need their own guard — same contract as trackEvent above.
 *
 * `eventId` is what lets each network match this browser event to the one the
 * server sends for the same sale (Ads/AdEvents.php) and count it once. For a
 * purchase it is the order id, for a registration 'reg-<user id>'.
 *
 * @param {'ViewContent'|'CompleteRegistration'|'InitiateCheckout'|'Purchase'} event
 * @param {{ value?: number, currency?: string, contentId?: string, contentName?: string }} [data]
 * @param {string} [eventId]
 */
export function trackAd(event, data = {}, eventId) {
    if (typeof window === 'undefined' || window.mcMarketingConsent !== true) return;

    const { value, currency, contentId, contentName } = data;
    const hasValue = typeof value === 'number';

    if (typeof window.ttq?.track === 'function') {
        window.ttq.track(
            TIKTOK_EVENT_NAMES[event] ?? event,
            {
                ...(contentId && {
                    contents: [{ content_id: contentId, content_type: 'product', content_name: contentName }],
                }),
                ...(hasValue && { value, currency: currency || 'COP' }),
            },
            eventId ? { event_id: eventId } : undefined
        );
    }

    if (typeof window.fbq === 'function') {
        window.fbq(
            'track',
            event,
            {
                ...(contentId && { content_ids: [contentId], content_type: 'product', content_name: contentName }),
                ...(hasValue && { value, currency: currency || 'COP' }),
            },
            eventId ? { eventID: eventId } : undefined
        );
    }
}

/**
 * What analytics is allowed to know about a URL: the page, never the secret.
 *
 * Three kinds of address here carry a credential in the path, and the reset
 * and verification links also carry an email or a signature in the query:
 *
 *   /jugador/{token}/...             whoever holds it IS that player
 *   /restablecer-clave/{token}?email  a password reset
 *   /verificar-correo/{id}/{hash}?... a signed verification link
 *
 * So those segments become placeholders and the query string and fragment
 * are dropped everywhere — a tracking tool needs the route, not what was
 * typed into it. Accepts a full URL or a bare path and returns the same shape.
 */
export function sanitizeUrl(url) {
    return String(url)
        .replace(/[?#].*$/, '')
        .replace(/(\/jugador\/)[^/]+/, '$1:token')
        .replace(/(\/restablecer-clave\/)[^/]+/, '$1:token')
        .replace(/(\/verificar-correo\/)[^/]+\/[^/]+/, '$1:id/:hash');
}
