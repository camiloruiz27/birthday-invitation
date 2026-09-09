import { Link, usePage } from '@inertiajs/react';
import Alert from '../components/ui/Alert';

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
        <div className="flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <main className="w-full max-w-md">
                <Link
                    href={route('home')}
                    className="mb-8 flex items-center justify-center gap-2.5 text-sm font-semibold text-ink"
                >
                    <span aria-hidden="true" className="h-2 w-2 rounded-full bg-accent" />
                    Central de investigación
                </Link>

                <div className="rounded-card border border-line bg-surface-raised p-6 shadow-raised sm:p-8">
                    <h1 className="text-lg font-semibold text-ink">{title}</h1>
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
            </main>
        </div>
    );
}
