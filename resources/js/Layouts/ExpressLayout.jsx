import { Link, usePage } from '@inertiajs/react';
import Alert from '../components/ui/Alert';
import Brand from '../components/ui/Brand';
import InAppBrowserNotice from '../components/ui/InAppBrowserNotice';
import { openCookieSettings } from '../lib/consent';

/**
 * Shell for the Express fair demo (/express).
 *
 * Derived from AdLayout: someone at a fair stand has one thing to do, so there
 * is no menu and no log-in / sign-up. What stays is what has to: the brand
 * (pointing back at the Express landing), the flash message, the in-app
 * browser notice (people scan the QR from inside Instagram or WhatsApp), and
 * the legal links and cookie preferences the law asks to be reachable from
 * every page.
 *
 * Phone first: the footer links are 44px targets, and `bar` is a fixed strip
 * for the bottom of the screen (the game's tab bar) — the main area reserves
 * room for it, safe-area inset included, so it never covers the footer.
 */
export default function ExpressLayout({ brandHref, bar = null, children }) {
    const { props } = usePage();
    const status = props.flash?.status;

    return (
        <div className="flex min-h-dvh flex-col">
            <a
                href="#main"
                className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-surface-raised focus:px-4 focus:py-2 focus:text-sm focus:text-ink"
            >
                Saltar al contenido
            </a>

            <header className="border-b border-line bg-surface">
                <div className="mx-auto flex h-14 w-full max-w-xl items-center justify-between px-4 sm:px-6">
                    <Brand href={brandHref || route('express.home')} />
                    <span className="case-stamp text-[10px] text-accent">Express</span>
                </div>
            </header>

            <main
                id="main"
                className={`flex-1 ${bar ? 'pb-[calc(5.5rem+env(safe-area-inset-bottom))]' : ''}`}
            >
                {(status || props.inAppBrowser) && (
                    <div className="mx-auto w-full max-w-xl px-4 pt-4 sm:px-6">
                        <InAppBrowserNotice />
                        <Alert variant="status">{status}</Alert>
                    </div>
                )}

                {children}
            </main>

            <footer className="border-t border-line bg-surface-sunken px-4 py-3 sm:px-6">
                <nav
                    aria-label="Legal"
                    className="mx-auto flex max-w-xl flex-wrap justify-center gap-x-4 text-xs"
                >
                    <Link
                        href={route('privacy')}
                        className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink"
                    >
                        Privacidad
                    </Link>
                    <Link
                        href={route('terms')}
                        className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink"
                    >
                        Términos
                    </Link>
                    <Link
                        href={route('cookies')}
                        className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink"
                    >
                        Cookies
                    </Link>
                    <button
                        type="button"
                        onClick={openCookieSettings}
                        className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink"
                    >
                        Preferencias de cookies
                    </button>
                </nav>
            </footer>

            {bar}
        </div>
    );
}

/** The column every Express screen lives in: a phone-width strip, centred. */
export function ExpressContainer({ className = '', children }) {
    return <div className={`mx-auto w-full max-w-xl px-4 sm:px-6 ${className}`}>{children}</div>;
}
