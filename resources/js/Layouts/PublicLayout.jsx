import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import Container from '../components/ui/Container';
import Button from '../components/ui/Button';
import Alert from '../components/ui/Alert';
import Brand from '../components/ui/Brand';
import MarketSelector from '../components/public/MarketSelector';
import { openCookieSettings } from '../lib/consent';

const NAV = [
    { name: 'Casos', route: 'cases.index' },
    { name: 'Mecánicas', route: 'mechanics' },
    { name: 'Inteligencia artificial', route: 'ai' },
    { name: 'Precios', route: 'pricing' },
];

const LEGAL = [
    { name: 'Política de Privacidad', route: 'privacy' },
    { name: 'Términos y Condiciones', route: 'terms' },
    { name: 'Política de Cookies', route: 'cookies' },
];

/**
 * Marketing shell.
 *
 * Open to everyone: a signed-in user browsing the catalog stays on the public
 * pages instead of being bounced to their dashboard, so the header swaps its
 * calls to action rather than the whole layout.
 */
export default function PublicLayout({ current, bleed = false, children }) {
    const { props, url } = usePage();
    const user = props.auth?.user;
    const status = props.flash?.status;
    const contactEmail = props.contactEmail;
    const [menuOpen, setMenuOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);

    // A navigation closes the mobile menu; otherwise it stays open over the
    // new page.
    useEffect(() => setMenuOpen(false), [url]);

    // The mobile navigation is a full-screen layer, not a dropdown below the
    // header. Keep the document still while it is open, just like a native
    // menu, so the hero never scrolls underneath it.
    useEffect(() => {
        if (!menuOpen) return undefined;

        const previousOverflow = document.body.style.overflow;
        const closeOnEscape = (event) => {
            if (event.key === 'Escape') setMenuOpen(false);
        };

        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', closeOnEscape);

        return () => {
            document.body.style.overflow = previousOverflow;
            document.removeEventListener('keydown', closeOnEscape);
        };
    }, [menuOpen]);

    // Transparent and tall at the top of the page, solid and compact once
    // scrolled — the header stops competing with a hero for attention, then
    // steps in as an anchored bar once there is real content behind it.
    useEffect(() => {
        const handleScroll = () => setScrolled(window.scrollY > 24);
        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <div className="flex min-h-dvh flex-col">
            <a
                href="#main"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-ink-inverse"
            >
                Saltar al contenido
            </a>

            <header
                className={`fixed top-0 z-[70] w-full transition-all duration-500 ${
                    menuOpen || scrolled
                        ? 'border-b border-line bg-surface/95 py-3 shadow-raised backdrop-blur-md'
                        : 'border-b border-transparent bg-transparent py-6'
                }`}
            >
                <Container width="wide">
                    <div className="flex h-10 items-center justify-between gap-6">
                        <Brand onClick={() => setMenuOpen(false)} />

                        <nav aria-label="Principal" className="hidden items-center gap-8 lg:flex">
                            {NAV.map((item) => (
                                <Link
                                    key={item.route}
                                    href={route(item.route)}
                                    aria-current={current === item.route ? 'page' : undefined}
                                    className={`group relative overflow-hidden py-1 text-sm transition-colors ${
                                        current === item.route
                                            ? 'font-medium text-ink'
                                            : 'text-ink-muted hover:text-ink'
                                    }`}
                                >
                                    {item.name}
                                    <span
                                        aria-hidden="true"
                                        className="absolute bottom-0 left-0 h-px w-full -translate-x-full bg-accent transition-transform duration-300 group-hover:translate-x-0"
                                    />
                                </Link>
                            ))}
                        </nav>

                        <div className="hidden items-center gap-3 lg:flex">
                            {user ? (
                                <Button href={route('dashboard')} size="sm">
                                    Mi panel
                                </Button>
                            ) : (
                                <>
                                    <Button href={route('login')} variant="ghost" size="sm">
                                        Ingresar
                                    </Button>
                                    <Button href={route('register')} size="sm">
                                        Crear cuenta
                                    </Button>
                                </>
                            )}
                        </div>

                        <button
                            type="button"
                            onClick={() => setMenuOpen((value) => !value)}
                            aria-expanded={menuOpen}
                            aria-controls="public-menu"
                            className="-mr-2 flex h-11 w-11 flex-col items-center justify-center gap-1.5 rounded-control text-ink transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent lg:hidden"
                        >
                            <span className="sr-only">
                                {menuOpen ? 'Cerrar menú' : 'Abrir menú'}
                            </span>
                            <span
                                aria-hidden="true"
                                className={`block h-px w-6 bg-current transition-transform duration-300 ${
                                    menuOpen ? 'translate-y-[7px] rotate-45' : ''
                                }`}
                            />
                            <span
                                aria-hidden="true"
                                className={`block h-px w-6 bg-current transition-opacity duration-300 ${
                                    menuOpen ? 'opacity-0' : ''
                                }`}
                            />
                            <span
                                aria-hidden="true"
                                className={`block h-px w-6 bg-current transition-transform duration-300 ${
                                    menuOpen ? '-translate-y-[7px] -rotate-45' : ''
                                }`}
                            />
                        </button>
                    </div>
                </Container>
            </header>

            {/*
                Mobile menu patterned after Island Residential: a separate,
                full-viewport surface below the always-visible brand and close
                control. It deliberately sits above the hero and cookie banner
                instead of pushing a short dropdown over the page content.
            */}
            <div
                id="public-menu"
                role="dialog"
                aria-modal="true"
                aria-label="Menú principal"
                aria-hidden={!menuOpen}
                className={`fixed inset-0 z-[60] flex items-center justify-center bg-surface/95 px-6 pt-20 backdrop-blur-xl transition-all duration-500 lg:hidden ${
                    menuOpen ? 'visible opacity-100' : 'invisible pointer-events-none opacity-0'
                }`}
            >
                <nav aria-label="Principal" className="flex w-full max-w-xs flex-col items-center gap-8 text-center">
                    {NAV.map((item) => (
                        <Link
                            key={item.route}
                            href={route(item.route)}
                            aria-current={current === item.route ? 'page' : undefined}
                            onClick={() => setMenuOpen(false)}
                            className={`text-base font-semibold uppercase tracking-[0.18em] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                                current === item.route ? 'text-accent-strong' : 'text-ink hover:text-accent-strong'
                            }`}
                        >
                            {item.name}
                        </Link>
                    ))}

                    <span aria-hidden="true" className="h-px w-12 bg-line" />

                    {user ? (
                        <Link
                            href={route('dashboard')}
                            onClick={() => setMenuOpen(false)}
                            className="text-base font-semibold uppercase tracking-[0.18em] text-ink transition-colors hover:text-accent-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                        >
                            Mi panel
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={route('register')}
                                onClick={() => setMenuOpen(false)}
                                className="text-base font-semibold uppercase tracking-[0.18em] text-ink transition-colors hover:text-accent-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                            >
                                Crear cuenta
                            </Link>
                            <Link
                                href={route('login')}
                                onClick={() => setMenuOpen(false)}
                                className="text-base font-semibold uppercase tracking-[0.18em] text-ink transition-colors hover:text-accent-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                            >
                                Ingresar
                            </Link>
                        </>
                    )}
                </nav>
            </div>

            {/*
                The header is `fixed`, so it never reserves space in normal
                flow — necessary for it to float transparently over a hero
                photo. Every OTHER page needs that space back, or its first
                heading renders straight underneath the (transparent, but
                real) header. `bleed` is Landing's opt-out: its hero already
                clears the header height with its own generous padding.
            */}
            <main id="main" className={`flex-1 ${bleed ? '' : 'pt-24 sm:pt-28'}`}>
                {status && (
                    <Container width="wide" className="pt-6">
                        <Alert variant="status">{status}</Alert>
                    </Container>
                )}

                {children}
            </main>

            <footer className="mt-20 border-t border-line py-10">
                <Container width="wide">
                    <div className="flex flex-col gap-8 sm:flex-row sm:justify-between">
                        <div className="max-w-xs">
                            <Brand />
                            <p className="mt-3 text-sm text-ink-muted">
                                Misterios interactivos para jugar en equipo. Sin imprimir nada.
                            </p>
                        </div>

                        <nav aria-label="Pie de página" className="flex flex-col gap-2.5">
                            {NAV.map((item) => (
                                <Link
                                    key={item.route}
                                    href={route(item.route)}
                                    className="text-sm text-ink-muted hover:text-ink"
                                >
                                    {item.name}
                                </Link>
                            ))}
                        </nav>

                        {contactEmail && (
                            <div className="max-w-xs">
                                <h2 className="text-sm font-medium text-ink">Contacto</h2>
                                <p className="mt-3 text-sm text-ink-muted">
                                    ¿Dudas, consultas sobre tus datos o los términos y condiciones?
                                    Escríbenos.
                                </p>
                                <a
                                    href={`mailto:${contactEmail}`}
                                    className="mt-2 inline-block text-sm font-medium text-accent underline underline-offset-2 hover:text-accent-strong"
                                >
                                    {contactEmail}
                                </a>
                            </div>
                        )}
                    </div>

                    <div className="mt-10 flex flex-col gap-4 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-xs text-ink-subtle">
                            © {new Date().getFullYear()} MisterioCode
                        </p>

                        <nav aria-label="Legal" className="flex flex-wrap gap-x-5 gap-y-2">
                            {LEGAL.map((item) => (
                                <Link
                                    key={item.route}
                                    href={route(item.route)}
                                    className="text-xs text-ink-muted hover:text-ink"
                                >
                                    {item.name}
                                </Link>
                            ))}
                            <button
                                type="button"
                                onClick={openCookieSettings}
                                className="text-xs text-ink-muted hover:text-ink"
                            >
                                Preferencias de cookies
                            </button>
                            <MarketSelector />
                        </nav>
                    </div>
                </Container>
            </footer>
        </div>
    );
}
