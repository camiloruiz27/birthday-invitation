import { useState } from 'react';

export default function Accordion({ summary, children, defaultOpen = false }) {
    const [open, setOpen] = useState(defaultOpen);

    return (
        <div className="border-2 border-ink bg-paper-card">
            <button
                type="button"
                onClick={() => setOpen((prev) => !prev)}
                className="flex w-full items-center justify-between px-4 py-3 text-left font-display text-sm uppercase tracking-wide"
            >
                {summary}
                <span aria-hidden="true">{open ? '−' : '+'}</span>
            </button>
            {open && <div className="border-t-2 border-dashed border-border-soft px-4 py-3">{children}</div>}
        </div>
    );
}
