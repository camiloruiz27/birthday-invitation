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
