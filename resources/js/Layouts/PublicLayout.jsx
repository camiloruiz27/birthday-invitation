import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import Container from '../components/ui/Container';
import Button from '../components/ui/Button';
import Alert from '../components/ui/Alert';
import Brand from '../components/ui/Brand';

const NAV = [
    { name: 'Casos', route: 'cases.index' },
    { name: 'Mecánicas', route: 'mechanics' },
    { name: 'Inteligencia artificial', route: 'ai' },
    { name: 'Precios', route: 'pricing' },
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
    const [menuOpen, setMenuOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);

    // A navigation closes the mobile menu; otherwise it stays open over the
    // new page.
    useEffect(() => setMenuOpen(false), [url]);

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
                className={`fixed top-0 z-20 w-full transition-all duration-500 ${
                    scrolled
                        ? 'border-b border-line bg-surface/95 py-3 shadow-raised backdrop-blur-md'
                        : 'border-b border-transparent bg-transparent py-6'
                }`}
            >
                <Container width="wide">
                    <div className="flex h-10 items-center justify-between gap-6">
                        <Brand />

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
                            className="-mr-2 flex h-11 w-11 items-center justify-center rounded-control text-ink-muted hover:text-ink lg:hidden"
                        >
                            <span className="sr-only">
                                {menuOpen ? 'Cerrar menú' : 'Abrir menú'}
                            </span>
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2"
                                strokeLinecap="round"
                                aria-hidden="true"
                                className="h-5 w-5"
                            >
                                {menuOpen ? (
                                    <path d="M6 6l12 12M18 6L6 18" />
                                ) : (
                                    <path d="M4 7h16M4 12h16M4 17h16" />
                                )}
                            </svg>
                        </button>
                    </div>
                </Container>

                {menuOpen && (
                    <div id="public-menu" className="border-t border-line lg:hidden">
                        <Container width="wide">
                            <nav aria-label="Principal" className="flex flex-col py-2">
                                {NAV.map((item) => (
                                    <Link
                                        key={item.route}
                                        href={route(item.route)}
                                        aria-current={current === item.route ? 'page' : undefined}
                                        className={`flex min-h-12 items-center text-sm ${
                                            current === item.route
                                                ? 'font-medium text-ink'
                                                : 'text-ink-muted'
                                        }`}
                                    >
                                        {item.name}
                                    </Link>
                                ))}
                            </nav>

                            <div className="flex flex-col gap-2 border-t border-line py-4">
                                {user ? (
                                    <Button href={route('dashboard')} fullWidth>
                                        Mi panel
                                    </Button>
                                ) : (
                                    <>
                                        <Button href={route('register')} fullWidth>
                                            Crear cuenta
                                        </Button>
                                        <Button href={route('login')} variant="secondary" fullWidth>
                                            Ingresar
                                        </Button>
                                    </>
                                )}
                            </div>
                        </Container>
                    </div>
                )}
            </header>

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
                    </div>

                    <p className="mt-10 border-t border-line pt-6 text-xs text-ink-subtle">
                        © {new Date().getFullYear()} MisterioCode
                    </p>
                </Container>
            </footer>
        </div>
    );
}
