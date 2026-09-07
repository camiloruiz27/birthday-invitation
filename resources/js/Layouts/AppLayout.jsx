import { Link, usePage } from '@inertiajs/react';
import Container from '../components/ui/Container';
import Alert from '../components/ui/Alert';
import UserMenu from '../components/ui/UserMenu';

/**
 * Shell for the signed-in platform: the investigation agency's console.
 *
 * Every authenticated page uses this, so flash messages and form errors are
 * surfaced in one place rather than being remembered per page.
 */
export default function AppLayout({ title, kicker, actions, width = 'app', children }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const status = props.flash?.status;
    const errors = props.errors || {};
    const errorList = Object.values(errors);

    return (
        <div className="flex min-h-screen flex-col">
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
                    <div className="flex h-16 items-center justify-between gap-4">
                        <Link
                            href={route('immersion.gm.dashboard')}
                            className="flex items-center gap-2.5 text-sm font-semibold text-ink"
                        >
                            <span
                                aria-hidden="true"
                                className="h-2 w-2 shrink-0 rounded-full bg-accent"
                            />
                            Central de investigación
                        </Link>

                        {user && <UserMenu user={user} />}
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
                            <h1 className="mt-1 text-xl font-semibold text-ink sm:text-2xl">
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
