const TONES = {
    neutral: 'border-line text-ink-muted',
    accent: 'border-accent-dim text-accent-strong',
    success: 'border-success-dim text-success',
    warning: 'border-accent-dim text-warning',
    danger: 'border-danger-dim text-danger',
};

/**
 * A code, timestamp, or status label — the concrete piece of MisterioCode's
 * own graphic language: MC-001, ESTADO · ACTIVO, 01:17:42.
 *
 * Deliberately square, not `Badge`'s rounded pill. The distinction is the
 * point: `Badge` is digital control (a game's status, rounded, filled) —
 * this is the system showing its data, unrounded like a stamped label, never
 * filled solid. A DataTag never claims to BE a stored record; it labels one.
 */
export default function DataTag({ tone = 'neutral', className = '', children }) {
    return (
        <span
            className={`inline-flex items-center gap-1.5 border px-2 py-1 font-mono text-[11px] font-medium uppercase tracking-widest ${TONES[tone]} ${className}`}
        >
            {children}
        </span>
    );
}
