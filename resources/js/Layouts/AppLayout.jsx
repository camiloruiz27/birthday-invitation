import { useEffect, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import Container from '../components/ui/Container';
import Alert from '../components/ui/Alert';
import Button from '../components/ui/Button';
import UserMenu from '../components/ui/UserMenu';
import Brand from '../components/ui/Brand';

/**
 * Shell for the signed-in platform: the investigation agency's console.
 *
 * Every authenticated page uses this, so flash messages and form errors are
 * surfaced in one place rather than being remembered per page.
 */
const NAV = [
    { name: 'Panel', route: 'dashboard' },
    { name: 'Biblioteca', route: 'library' },
    { name: 'Partidas', route: 'immersion.gm.games.index' },
    { name: 'Créditos', route: 'credits' },
];

function NavLink({ item, active, className = '', onClick }) {
    return (
        <Link
            href={route(item.route)}
            aria-current={active ? 'page' : undefined}
            onClick={onClick}
            className={`relative inline-flex h-16 shrink-0 items-center px-1 text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent ${
                active ? 'font-medium text-ink' : 'text-ink-muted hover:text-ink'
            } ${className}`}
        >
            {item.name}
            {active && <span aria-hidden="true" className="absolute inset-x-1 bottom-0 h-0.5 bg-accent" />}
        </Link>
    );
}

/**
 * Buying does not need a confirmed address, but creating a game and mailing
 * its players does (see the `verified` routes). So an account that has not
 * confirmed yet is told, on every signed-in page, what is still missing and
 * how to fix it — instead of discovering it at the moment it tries to start
 * a game.
 */
function VerifyEmailNotice({ email }) {
    function resend() {
        router.post(route('verification.send'), {}, { preserveScroll: true });
    }

    return (
        <Alert variant="warning">
            <p>
                <span className="font-medium">Falta confirmar tu correo</span> ({email}). Lo necesitas para
                crear partidas y enviar los enlaces a tus jugadores.
            </p>
            <div className="mt-3 flex flex-wrap items-center gap-2">
                <Button variant="secondary" size="sm" onClick={resend}>
                    Reenviar el correo
                </Button>
                <Link href={route('profile.edit')} className="text-sm text-accent underline">
                    ¿Correo mal escrito? Corrígelo
                </Link>
            </div>
        </Alert>
    );
}

export default function AppLayout({
    title,
    kicker,
    actions,
    current,
    width = 'app',
    children,
}) {
    const { props, url } = usePage();
    const user = props.auth?.user;
    const [mobileNavOpen, setMobileNavOpen] = useState(false);

    // Inertia keeps layouts mounted between visits. Close the drawer after a
    // navigation so the next screen never arrives hidden behind an old menu.
    useEffect(() => {
        setMobileNavOpen(false);
    }, [url]);

    useEffect(() => {
        if (!mobileNavOpen) return undefined;

        const previousOverflow = document.body.style.overflow;
        function closeOnEscape(event) {
            if (event.key === 'Escape') setMobileNavOpen(false);
        }

        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', closeOnEscape);

        return () => {
            document.body.style.overflow = previousOverflow;
            document.removeEventListener('keydown', closeOnEscape);
        };
    }, [mobileNavOpen]);
    // `verification-link-sent` is a key the resend action flashes for the
    // confirmation page to word itself; here it is turned into a sentence
    // instead of being printed raw.
    const rawStatus = props.flash?.status;
    const status =
        rawStatus === 'verification-link-sent'
            ? 'Listo, te enviamos el correo otra vez. Puede tardar un par de minutos.'
            : rawStatus;
    const unverified = Boolean(user) && user.email_verified === false;
    const errors = props.errors || {};
    const errorList = Object.values(errors);

    return (
        <div className="flex min-h-dvh flex-col">
            {/* Lets a keyboard user jump the navigation. Visually hidden until
                focused, which is when it matters. */}
            <a
                href="#main"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-ink-inverse"
            >
                Saltar al contenido
            </a>

            <header className="sticky top-0 z-[70] border-b border-line bg-surface/95 shadow-sm backdrop-blur">
                {/* The header always uses the application width. Tying it to a
                    narrow page (for example Profile's prose width) squeezed
                    the navigation even on a large screen. */}
                <Container width="app">
                    <div className="flex h-16 items-center gap-6">
                        <Brand href={route('dashboard')} hideWordmarkOnMobile onClick={() => setMobileNavOpen(false)} />

                        {/* There can be six links for an administrator. Keep the
                            full row for genuinely wide screens and use the
                            reliable drawer pattern below everywhere else. */}
                        <nav
                            aria-label="Principal"
                            className="hidden min-w-0 flex-1 items-center gap-6 lg:flex"
                        >
                            {NAV.map((item) => (
                                <NavLink
                                    key={item.route}
                                    item={item}
                                    active={current === item.route}
                                />
                            ))}

                            {user?.is_admin && (
                                <>
                                    <NavLink
                                        item={{ name: 'Admin', route: 'admin.dashboard' }}
                                        active={current === 'admin.dashboard'}
                                    />
                                    <NavLink
                                        item={{ name: 'Casos', route: 'admin.cases' }}
                                        active={current === 'admin.cases'}
                                    />
                                    <NavLink
                                        item={{ name: 'Códigos', route: 'admin.codes' }}
                                        active={current === 'admin.codes'}
                                    />
                                    <NavLink
                                        item={{ name: 'Email marketing', route: 'admin.email-marketing' }}
                                        active={current === 'admin.email-marketing'}
                                    />
                                </>
                            )}
                        </nav>

                        <div className="ml-auto hidden shrink-0 lg:block">
                            {user && <UserMenu user={user} />}
                        </div>

                        <button
                            type="button"
                            onClick={() => setMobileNavOpen((open) => !open)}
                            aria-expanded={mobileNavOpen}
                            aria-controls="app-mobile-menu"
                            aria-label={mobileNavOpen ? 'Cerrar menú' : 'Abrir menú'}
                            className="ml-auto inline-flex h-11 w-11 flex-col items-center justify-center gap-1.5 rounded-control text-ink transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent lg:hidden"
                        >
                            <span
                                aria-hidden="true"
                                className={`block h-px w-6 bg-current transition-transform duration-300 ${
                                    mobileNavOpen ? 'translate-y-[7px] rotate-45' : ''
                                }`}
                            />
                            <span
                                aria-hidden="true"
                                className={`block h-px w-6 bg-current transition-opacity duration-300 ${
                                    mobileNavOpen ? 'opacity-0' : ''
                                }`}
                            />
                            <span
                                aria-hidden="true"
                                className={`block h-px w-6 bg-current transition-transform duration-300 ${
                                    mobileNavOpen ? '-translate-y-[7px] -rotate-45' : ''
                                }`}
                            />
                        </button>
                    </div>
                </Container>
            </header>

            {/* The signed-in navigation follows the same full-screen mobile
                pattern as the public site: a quiet, dedicated surface rather
                than a short panel competing with the current page. */}
            <div
                id="app-mobile-menu"
                role="dialog"
                aria-modal="true"
                aria-label="Menú principal"
                aria-hidden={!mobileNavOpen}
                className={`fixed inset-0 z-[60] flex items-center justify-center bg-surface/95 px-6 pt-20 backdrop-blur-xl transition-all duration-500 lg:hidden ${
                    mobileNavOpen ? 'visible opacity-100' : 'invisible pointer-events-none opacity-0'
                }`}
            >
                <nav aria-label="Principal" className="flex w-full max-w-xs flex-col items-center gap-7 text-center">
                    {NAV.map((item) => (
                        <Link
                            key={item.route}
                            href={route(item.route)}
                            aria-current={current === item.route ? 'page' : undefined}
                            onClick={() => setMobileNavOpen(false)}
                            className={`text-base font-semibold uppercase tracking-[0.18em] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                                current === item.route ? 'text-accent-strong' : 'text-ink hover:text-accent-strong'
                            }`}
                        >
                            {item.name}
                        </Link>
                    ))}

                    {user?.is_admin && (
                        <>
                            <Link
                                href={route('admin.dashboard')}
                                aria-current={current === 'admin.dashboard' ? 'page' : undefined}
                                onClick={() => setMobileNavOpen(false)}
                                className={`text-base font-semibold uppercase tracking-[0.18em] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                                    current === 'admin.dashboard' ? 'text-accent-strong' : 'text-ink hover:text-accent-strong'
                                }`}
                            >
                                Admin
                            </Link>
                            <Link
                                href={route('admin.cases')}
                                aria-current={current === 'admin.cases' ? 'page' : undefined}
                                onClick={() => setMobileNavOpen(false)}
                                className={`text-base font-semibold uppercase tracking-[0.18em] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                                    current === 'admin.cases' ? 'text-accent-strong' : 'text-ink hover:text-accent-strong'
                                }`}
                            >
                                Casos
                            </Link>
                            <Link
                                href={route('admin.codes')}
                                aria-current={current === 'admin.codes' ? 'page' : undefined}
                                onClick={() => setMobileNavOpen(false)}
                                className={`text-base font-semibold uppercase tracking-[0.18em] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                                    current === 'admin.codes' ? 'text-accent-strong' : 'text-ink hover:text-accent-strong'
                                }`}
                            >
                                Códigos
                            </Link>
                            <Link
                                href={route('admin.email-marketing')}
                                aria-current={current === 'admin.email-marketing' ? 'page' : undefined}
                                onClick={() => setMobileNavOpen(false)}
                                className={`text-base font-semibold uppercase tracking-[0.18em] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                                    current === 'admin.email-marketing' ? 'text-accent-strong' : 'text-ink hover:text-accent-strong'
                                }`}
                            >
                                Email marketing
                            </Link>
                        </>
                    )}

                    <span aria-hidden="true" className="h-px w-12 bg-line" />

                    <Link
                        href={route('profile.edit')}
                        onClick={() => setMobileNavOpen(false)}
                        className={`text-base font-semibold uppercase tracking-[0.18em] transition-colors hover:text-accent-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                            current === 'profile' ? 'text-accent-strong' : 'text-ink'
                        }`}
                    >
                        Tu perfil
                    </Link>
                    <button
                        type="button"
                        onClick={() => router.post(route('logout'))}
                        className="text-base font-semibold uppercase tracking-[0.18em] text-ink transition-colors hover:text-accent-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    >
                        Salir
                    </button>
                </nav>
            </div>

            <main id="main" className="flex-1 py-8">
                <Container width={width}>
                    <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                        <div className="min-w-0">
                            {kicker && (
                                <p className="text-xs font-medium uppercase tracking-widest text-accent">
                                    {kicker}
                                </p>
                            )}
                            {/* font-display and a real size: the h1 used to be
                                20px Inter, which left the biggest text on
                                Panel, Créditos and Admin being a number in a
                                tile rather than the name of the page. */}
                            <h1 className="mt-1 font-display text-2xl font-semibold tracking-tight text-ink sm:text-3xl">
                                {title}
                            </h1>
                        </div>

                        {actions && (
                            <div className="flex flex-wrap items-center gap-2">{actions}</div>
                        )}
                    </div>

                    <Alert variant="status">{status}</Alert>

                    {unverified && <VerifyEmailNotice email={user.email} />}

                    {errorList.length > 0 && (
                        <Alert variant="error" title="Revisa lo siguiente">
                            <ul className={errorList.length > 1 ? 'list-inside list-disc' : ''}>
                                {errorList.map((message, index) => (
                                    <li key={index}>{message}</li>
                                ))}
                            </ul>
                        </Alert>
                    )}

                    {children}
                </Container>
            </main>
        </div>
    );
}
