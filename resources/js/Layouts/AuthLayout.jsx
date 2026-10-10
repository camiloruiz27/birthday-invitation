import { Link, usePage } from '@inertiajs/react';
import Alert from '../components/ui/Alert';
import Brand from '../components/ui/Brand';
import InAppBrowserNotice from '../components/ui/InAppBrowserNotice';
import { openCookieSettings } from '../lib/consent';

/**
 * Centred single-column shell for the account screens.
 *
 * Quieter than both the console and the case surface: signing in is admin, not
 * fiction, and it should feel unremarkable.
 */
export default function AuthLayout({ title, description, footer, children }) {
    const { props } = usePage();
    const status = props.flash?.status;
    const errors = props.errors || {};

    return (
        <div className="flex min-h-dvh flex-col items-center justify-center px-4 py-12">
            <main className="w-full max-w-md">
                <Brand className="mb-8 justify-center" />

                {/* Inside TikTok's or Instagram's browser the anti-bot check can
                    fail and the confirmation mail opens somewhere else: say so
                    before the visitor types anything. */}
                <InAppBrowserNotice />

                <div className="rounded-card border border-line bg-surface-raised p-6 shadow-raised sm:p-8">
                    {/* Same face and weight as AppLayout's h1, one step down
                        for the narrower column. It used to be 18px Inter —
                        smaller than the title of the page you land on right
                        after signing in, which read as a step backwards. */}
                    <h1 className="font-display text-xl font-semibold tracking-tight text-ink">
                        {title}
                    </h1>
                    {description && <p className="mt-1.5 text-sm text-ink-muted">{description}</p>}

                    <div className="mt-6">
                        <Alert variant="status">{status}</Alert>

                        {/* A credentials failure is reported on `email` but is
                            not about the email field, so it is surfaced here
                            rather than under the input. */}
                        {errors.email && !errors.password && (
                            <Alert variant="error">{errors.email}</Alert>
                        )}

                        {children}
                    </div>
                </div>

                {footer && <p className="mt-6 text-center text-sm text-ink-muted">{footer}</p>}

                {/* Each link is a 44px-tall tap target (min-h-11). */}
                <nav aria-label="Legal" className="mt-3 flex flex-wrap justify-center gap-x-4 text-xs">
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
            </main>
        </div>
    );
}
