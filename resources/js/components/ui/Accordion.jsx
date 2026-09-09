import { useId, useState } from 'react';

/**
 * Disclosure panel. Uses aria-expanded and aria-controls so the trigger
 * announces its state and what it toggles, rather than being a bare button.
 */
export default function Accordion({ summary, children, defaultOpen = false }) {
    const [open, setOpen] = useState(defaultOpen);
    const panelId = useId();

    return (
        <div className="overflow-hidden rounded-card border border-line bg-surface-raised">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-expanded={open}
                aria-controls={panelId}
                className="flex min-h-12 w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm text-ink hover:bg-surface-overlay"
            >
                <span className="min-w-0">{summary}</span>
                <span aria-hidden="true" className="shrink-0 text-ink-muted">
                    {open ? '−' : '+'}
                </span>
            </button>

            {open && (
                <div id={panelId} className="border-t border-line px-4 py-3">
                    {children}
                </div>
            )}
        </div>
    );
}
