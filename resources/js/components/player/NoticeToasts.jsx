import { useEffect } from 'react';
import { Link } from '@inertiajs/react';

const AUTO_DISMISS_MS = 8000;

/** Where a notice takes the player, and what the button says. */
function destination(notice, token) {
    switch (notice.kind) {
        case 'envelope':
            return {
                href: `${route('immersion.player.inbox', token)}#sobre-${notice.event_id}`,
                label: 'Abrir sobre',
            };
        case 'interrogation':
            return { href: route('immersion.player.interrogation.index', token), label: 'Ver personas' };
        case 'accusation':
            return { href: route('immersion.player.accusation', token), label: 'Hacer mi acusación' };
        default:
            return { href: route('immersion.player.solution', token), label: 'Ver la solución' };
    }
}

function headline(notice) {
    return notice.kind === 'envelope' ? `Nuevo sobre: ${notice.title}` : notice.title;
}

function Toast({ notice, token, onDismiss }) {
    const { href, label } = destination(notice, token);

    // It disappears by itself: a notice is a nudge, and what it points at stays
    // in the inbox and the tabs whether or not it was read.
    useEffect(() => {
        const timer = window.setTimeout(() => onDismiss(notice.key), AUTO_DISMISS_MS);

        return () => window.clearTimeout(timer);
    }, [notice.key, onDismiss]);

    return (
        <li className="pointer-events-auto flex items-start gap-3 rounded-card border border-accent bg-surface-raised p-3 shadow-lg">
            <span aria-hidden="true" className="mt-1 h-2 w-2 shrink-0 rounded-full bg-accent" />

            <div className="min-w-0 flex-1">
                <p className="text-sm font-medium text-ink">{headline(notice)}</p>
                <Link
                    href={href}
                    onClick={() => onDismiss(notice.key)}
                    className="mt-1 inline-flex min-h-8 items-center text-xs font-semibold text-accent-strong underline underline-offset-2 hover:text-ink"
                >
                    {label}
                </Link>
            </div>

            <button
                type="button"
                onClick={() => onDismiss(notice.key)}
                className="-m-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-ink-muted hover:text-ink"
            >
                <span aria-hidden="true">&times;</span>
                <span className="sr-only">Cerrar aviso</span>
            </button>
        </li>
    );
}

/**
 * What just arrived, on whatever screen the player is looking at.
 *
 * Sits above the mobile bottom bar so it never covers the tabs, and announces
 * itself politely to screen readers without stealing focus.
 */
export default function NoticeToasts({ toasts, token, onDismiss }) {
    if (!toasts || toasts.length === 0) {
        return null;
    }

    return (
        <ul
            role="status"
            aria-live="polite"
            className="pointer-events-none fixed inset-x-0 bottom-24 z-30 mx-auto flex w-full max-w-md flex-col gap-2 px-4 sm:bottom-6 sm:left-auto sm:right-6 sm:mx-0 sm:px-0"
        >
            {toasts.map((notice) => (
                <Toast key={notice.key} notice={notice} token={token} onDismiss={onDismiss} />
            ))}
        </ul>
    );
}
