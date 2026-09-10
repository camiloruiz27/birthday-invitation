import { Link, usePage } from '@inertiajs/react';
import Container from '../components/ui/Container';
import Alert from '../components/ui/Alert';
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

function NavLink({ item, active, className = '' }) {
    return (
        <Link
            href={route(item.route)}
            aria-current={active ? 'page' : undefined}
            // min-h-11 and shrink-0: this is the app's primary navigation and
            // it was a bare 20px-tall text link that could also be squeezed.
            className={`inline-flex min-h-11 shrink-0 items-center text-sm transition-colors ${
                active ? 'font-medium text-ink' : 'text-ink-muted hover:text-ink'
            } ${className}`}
        >
            {item.name}
        </Link>
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
    const { props } = usePage();
    const user = props.auth?.user;
    const status = props.flash?.status;
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

            <header className="sticky top-0 z-10 border-b border-line bg-surface/95 backdrop-blur">
                <Container width={width}>
                    <div className="flex flex-wrap items-center gap-x-4 py-2 sm:h-16 sm:flex-nowrap sm:gap-6 sm:py-0">
                        <Brand href={route('dashboard')} hideWordmarkOnMobile />

                        {/* One <nav> in the DOM, not one per breakpoint, so
                            there is a single "Principal" landmark. `order-last
                            w-full` drops it onto its own scrollable line under
                            the mark on a phone and returns it inline from sm
                            up: four links (five for an admin) plus the mark
                            plus the account menu measure well past 360px, and
                            they used to put every signed-in page into a
                            sideways scroll. */}
                        <nav
                            aria-label="Principal"
                            className="no-scrollbar order-last -mx-4 flex w-full items-center gap-5 overflow-x-auto px-4 sm:order-0 sm:mx-0 sm:w-auto sm:flex-1 sm:overflow-visible sm:px-0"
                        >
                            {NAV.map((item) => (
                                <NavLink
                                    key={item.route}
                                    item={item}
                                    active={current === item.route}
                                />
                            ))}

                            {user?.is_admin && (
                                <NavLink
                                    item={{ name: 'Admin', route: 'admin.dashboard' }}
                                    active={current === 'admin.dashboard'}
                                    className="text-accent hover:text-accent-strong"
                                />
                            )}
                        </nav>

                        <div className="ml-auto shrink-0 sm:ml-0">
                            {user && <UserMenu user={user} />}
                        </div>
                    </div>
                </Container>
            </header>

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
