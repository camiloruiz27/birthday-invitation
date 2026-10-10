import { Link, usePage } from '@inertiajs/react';
import Alert from '../components/ui/Alert';
import Brand from '../components/ui/Brand';
import InAppBrowserNotice from '../components/ui/InAppBrowserNotice';
import { openCookieSettings } from '../lib/consent';

/**
 * Shell for the pages paid ads land on.
 *
 * Deliberately less than PublicLayout: no menu, no log-in / sign-up buttons in
 * the header and no site map in the footer. Someone arriving from an ad has
 * one decision to make, and every other link is a way out of it. What stays is
 * what has to: the brand (pointing back at this same page, not the home), the
 * flash message, and the legal links and cookie preferences the law asks to be
 * reachable from every page.
 *
 * `bar` is an optional fixed strip for the bottom of a phone screen (the page
 * decides when it is visible); the main area reserves room for it so it never
 * covers the footer.
 */
export default function AdLayout({ brandHref, bar = null, children }) {
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
                <div className="mx-auto flex h-14 w-full max-w-6xl items-center px-4 sm:h-16 sm:px-6">
                    <Brand href={brandHref} />
                </div>
            </header>

            <main id="main" className={`flex-1 ${bar ? 'pb-24 sm:pb-0' : ''}`}>
                {(status || props.inAppBrowser) && (
                    <div className="mx-auto w-full max-w-6xl px-4 pt-4 sm:px-6">
                        <InAppBrowserNotice />
                        <Alert variant="status">{status}</Alert>
                    </div>
                )}

                {children}
            </main>

            <footer className="border-t border-line bg-surface-sunken px-4 py-4 sm:px-6 sm:py-8">
                {/* Each link is a 44px-tall tap target (min-h-11): these were
                    16px-tall text on a phone. */}
                <nav
                    aria-label="Legal"
                    className="mx-auto flex max-w-6xl flex-wrap justify-center gap-x-4 text-xs"
                >
                    <Link href={route('privacy')} className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink">
                        Privacidad
                    </Link>
                    <Link href={route('terms')} className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink">
                        Términos
                    </Link>
                    <Link href={route('cookies')} className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink">
                        Cookies
                    </Link>
                    <button type="button" onClick={openCookieSettings} className="inline-flex min-h-11 items-center px-1 text-ink-subtle hover:text-ink">
                        Preferencias de cookies
                    </button>
                </nav>
                <p className="mt-1 text-center text-xs text-ink-subtle">MisterioCode</p>
            </footer>

            {bar}
        </div>
    );
}
