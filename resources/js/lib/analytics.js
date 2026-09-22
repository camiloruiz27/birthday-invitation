/**
 * Thin wrapper around gtag. A no-op whenever analytics is off (no
 * PLATFORM_GA_MEASUREMENT_ID configured, so app.blade.php never loaded
 * gtag.js) — that's what keeps every call site from needing its own guard.
 *
 * No PII in `params` ever: GA4's terms forbid sending it, and Clarity has
 * nothing to wire here — it records without being told about events.
 */
export function trackEvent(name, params = {}) {
    if (typeof window === 'undefined' || typeof window.gtag !== 'function') return;

    window.gtag('event', name, params);
}
