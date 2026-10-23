import { formatClock } from '../../lib/express';

/**
 * A clock that says what it is counting to.
 *
 * `seconds` is already derived from the server clock by the caller; this only
 * draws it. Tabular numbers so the digits do not jiggle as they change, and
 * the last 15 seconds turn warning-coloured. `role="timer"` without a live
 * region on purpose: announcing every second would drown a screen reader.
 */
export default function CountdownChip({ label, seconds, caption, className = '' }) {
    const known = typeof seconds === 'number' && Number.isFinite(seconds);
    const urgent = known && seconds <= 15;

    return (
        <div
            role="timer"
            aria-label={known ? `${label}: ${formatClock(seconds)} ${caption ?? ''}`.trim() : label}
            className={`flex items-center gap-3 ${className}`}
        >
            <span
                className={`tabular rounded-control border px-3 py-1 font-mono text-2xl font-medium leading-none ${
                    urgent
                        ? 'border-warning bg-accent-dim text-warning-strong'
                        : 'border-line-strong bg-surface-sunken text-ink'
                }`}
            >
                {known ? formatClock(seconds) : '--:--'}
            </span>
            <span className="min-w-0 text-left text-xs leading-tight">
                <span className="block font-semibold text-ink">{label}</span>
                {caption && <span className="block text-ink-muted">{caption}</span>}
            </span>
        </div>
    );
}
