import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import Alert from './Alert';
import Button from './Button';

const APP_NAMES = {
    tiktok: 'TikTok',
    instagram: 'Instagram',
    facebook: 'Facebook',
    other: 'esta app',
};

/**
 * Which app's embedded browser this visit is inside, or null for an ordinary
 * browser. Decided by the server from the User-Agent (see InAppBrowser.php) and
 * shared on every page, so it is known on the very first render.
 */
export function useInAppBrowser() {
    return usePage().props.inAppBrowser ?? null;
}

/**
 * Copies text to the clipboard. The async API is missing or refused in some
 * embedded browsers (it needs a secure context and a user gesture), so there
 * is a textarea + execCommand fallback; it still can say "no" and the caller
 * shows the link to copy by hand in that case.
 */
export async function copyText(text) {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);

            return true;
        }
    } catch {
        // Fall through to the textarea route.
    }

    try {
        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        const ok = document.execCommand('copy');
        document.body.removeChild(area);

        return ok;
    } catch {
        return false;
    }
}

/**
 * "Open this in your browser."
 *
 * Tapping an ad in TikTok or Instagram opens that app's own browser, not the
 * phone's. In there the anti-bot check and the payment page are the things
 * that most often misbehave, the session does not follow the visitor to the
 * browser their mail app opens, and there is no address bar to fix any of it.
 * There is no reliable way to push someone out of it (iOS has no API for it),
 * so the honest option is to say so and make leaving a single tap: copy the
 * current address — query string included, so the case and the promo code
 * come along — and say where the menu is.
 *
 * Renders nothing in an ordinary browser. `children` is an optional extra line
 * for the page (the payment screen adds what to do when coming back).
 */
export default function InAppBrowserNotice({ className = '', children }) {
    const app = useInAppBrowser();
    const [hidden, setHidden] = useState(false);
    const [copied, setCopied] = useState(null);

    if (!app || hidden) {
        return null;
    }

    async function copy() {
        const ok = await copyText(window.location.href);
        setCopied(ok);
        if (ok) setTimeout(() => setCopied(null), 3000);
    }

    return (
        <Alert variant="warning" className={className}>
            {/* One short paragraph: on a phone this sits above the page's own
                button, and the longer title-plus-body version pushed it under
                the cookie banner. */}
            <p>
                <span className="font-medium">
                    {app === 'other' ? 'Estás en el navegador de otra app.' : `Estás en ${APP_NAMES[app]}.`}
                </span>{' '}
                Para registrarte y pagar sin problemas, ábrelo en tu navegador: toca{' '}
                <span aria-label="los tres puntos">⋯</span> y elige «Abrir en el navegador».
            </p>
            {children && <p className="mt-1 text-ink-muted">{children}</p>}

            {/* Medium buttons: 44px tall, a comfortable thumb target. */}
            <div className="mt-2 flex flex-wrap items-center gap-2">
                <Button variant="secondary" onClick={copy}>
                    {copied ? 'Enlace copiado ✓' : 'Copiar enlace'}
                </Button>
                <Button variant="ghost" onClick={() => setHidden(true)}>
                    Seguir aquí
                </Button>
            </div>

            {copied === false && (
                <p className="mt-2 break-all text-xs text-ink-muted">
                    No pudimos copiarlo solo. Mantén pulsado y copia: {window.location.href}
                </p>
            )}
        </Alert>
    );
}
