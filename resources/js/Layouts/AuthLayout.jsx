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
function LegalLinks() {
    return (
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
    );
}

function FormContent({ title, description, footer, children, eyebrow, errors, status }) {
    return (
        <>
            {eyebrow && <p className="text-xs font-semibold uppercase tracking-[0.24em] text-accent">{eyebrow}</p>}
            <h1 className="mt-3 font-display text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                {title}
            </h1>
            {description && <p className="mt-3 max-w-sm text-sm leading-relaxed text-ink-muted">{description}</p>}
            <div className="mt-8">
                <Alert variant="status">{status}</Alert>
                {errors.email && !errors.password && <Alert variant="error">{errors.email}</Alert>}
                {children}
            </div>
            {footer && <p className="mt-7 text-center text-sm text-ink-muted">{footer}</p>}
        </>
    );
}

function ImmersiveAuthLayout({ title, description, footer, children, eyebrow, errors, status }) {
    return (
        <div className="min-h-dvh bg-surface lg:grid lg:grid-cols-2">
            <aside className="relative hidden min-h-dvh overflow-hidden lg:flex lg:flex-col">
                <img
                    src="/brand/hero-01-1200.jpg"
                    alt=""
                    className="absolute inset-0 h-full w-full scale-105 object-cover opacity-75 transition-transform duration-1000 hover:scale-110"
                />
                <div aria-hidden="true" className="absolute inset-0 bg-linear-to-br from-surface/25 via-surface/65 to-surface" />
                <div aria-hidden="true" className="absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,transparent_0%,rgb(8_15_20_/_0.45)_55%,rgb(8_15_20_/_0.92)_100%)]" />

                <div className="relative z-10 px-10 pt-10 xl:px-16 xl:pt-14">
                    <Brand />
                </div>

                <div className="relative z-10 mt-24 max-w-xl px-10 xl:px-16">
                    <p className="text-xs font-semibold uppercase tracking-[0.25em] text-accent-strong">Archivo de casos</p>
                    <h2 className="mt-4 font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ink xl:text-5xl">
                        Cada caso deja una pista.
                    </h2>
                    <div className="mt-7 h-px w-14 bg-accent" />
                    <p className="mt-7 max-w-md text-base leading-relaxed text-ink-muted">
                        Vuelve a tus investigaciones, reúne a tu equipo y descubre lo que nadie quiso contar.
                    </p>
                </div>
            </aside>

            <main className="relative flex min-h-dvh flex-col bg-surface px-5 py-6 sm:px-10 sm:py-10 lg:px-16 xl:px-24">
                <div className="flex items-center justify-between lg:hidden">
                    <Link href={route('home')} className="inline-flex min-h-11 items-center text-xs font-semibold uppercase tracking-[0.18em] text-ink-muted transition-colors hover:text-ink">
                        ← Volver al sitio
                    </Link>
                    <Brand />
                </div>
                <Link href={route('home')} className="hidden min-h-11 items-center text-xs font-semibold uppercase tracking-[0.18em] text-ink-muted transition-colors hover:text-ink lg:inline-flex">
                    ← Volver al sitio
                </Link>

                <div className="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-12 lg:py-16">
                    <div className="animate-reveal">
                        <InAppBrowserNotice />
                        <FormContent
                            title={title}
                            description={description}
                            footer={footer}
                            eyebrow={eyebrow}
                            errors={errors}
                            status={status}
                        >
                            {children}
                        </FormContent>
                    </div>
                </div>

                <LegalLinks />
            </main>
        </div>
    );
}

export default function AuthLayout({ title, description, footer, children, immersive = false, eyebrow = null }) {
    const { props } = usePage();
    const status = props.flash?.status;
    const errors = props.errors || {};

    if (immersive) {
        return <ImmersiveAuthLayout title={title} description={description} footer={footer} eyebrow={eyebrow} errors={errors} status={status}>{children}</ImmersiveAuthLayout>;
    }

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
                        {errors.email && !errors.password && <Alert variant="error">{errors.email}</Alert>}
                        {children}
                    </div>
                </div>

                {footer && <p className="mt-6 text-center text-sm text-ink-muted">{footer}</p>}
                <LegalLinks />
            </main>
        </div>
    );
}
