import { useEffect, useRef, useState } from 'react';
import { Link, router } from '@inertiajs/react';

/**
 * Account dropdown. Closes on outside click and on Escape, and reports its
 * state through aria-expanded so it is usable without sight or a mouse.
 */
export default function UserMenu({ user }) {
    const [open, setOpen] = useState(false);
    const containerRef = useRef(null);

    useEffect(() => {
        if (!open) return;

        function handlePointerDown(event) {
            if (!containerRef.current?.contains(event.target)) {
                setOpen(false);
            }
        }

        function handleKeyDown(event) {
            if (event.key === 'Escape') setOpen(false);
        }

        document.addEventListener('mousedown', handlePointerDown);
        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('mousedown', handlePointerDown);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [open]);

    const initials = user.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return (
        <div ref={containerRef} className="relative">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-expanded={open}
                aria-haspopup="menu"
                className="flex min-h-11 items-center gap-2 rounded-control px-2 py-1.5 text-sm text-ink-muted hover:bg-surface-raised hover:text-ink"
            >
                <span
                    aria-hidden="true"
                    className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-dim text-xs font-semibold text-accent-strong"
                >
                    {initials}
                </span>
                <span className="hidden max-w-32 truncate sm:block">{user.name}</span>
            </button>

            {open && (
                <div
                    role="menu"
                    className="absolute right-0 z-20 mt-1 w-52 overflow-hidden rounded-card border border-line bg-surface-raised shadow-overlay"
                >
                    <div className="border-b border-line px-4 py-3">
                        <p className="truncate text-sm font-medium text-ink">{user.name}</p>
                        <p className="truncate text-xs text-ink-muted">{user.email}</p>
                    </div>

                    <Link
                        href={route('profile.edit')}
                        role="menuitem"
                        onClick={() => setOpen(false)}
                        className="block px-4 py-2.5 text-sm text-ink-muted hover:bg-surface-sunken hover:text-ink"
                    >
                        Tu perfil
                    </Link>

                    <button
                        type="button"
                        role="menuitem"
                        onClick={() => router.post(route('logout'))}
                        className="block w-full px-4 py-2.5 text-left text-sm text-ink-muted hover:bg-surface-sunken hover:text-ink"
                    >
                        Salir
                    </button>
                </div>
            )}
        </div>
    );
}
