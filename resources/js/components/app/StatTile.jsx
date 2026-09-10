/**
 * One figure that matters, in a panel.
 *
 * Built four times before this existed — twice byte-identical, twice with a
 * different label treatment — across the dashboard, the admin dashboard, the
 * credits screen and the payment confirmation. The label styling is the part
 * that had drifted, so it lives here now.
 *
 * `tabular` on the value is not cosmetic: several of these poll and change
 * in place, and proportional digits make the layout twitch.
 */
const TONES = {
    default: 'text-ink',
    accent: 'text-accent-strong',
    alert: 'text-danger-strong',
};

export default function StatTile({
    label,
    value,
    unit,
    hint,
    tone = 'default',
    size = 'md',
    className = '',
    children,
}) {
    return (
        <div className={`rounded-card border border-line bg-surface-raised p-4 ${className}`}>
            <p className="text-sm text-ink-muted">{label}</p>

            <p
                className={`tabular mt-1 font-semibold ${TONES[tone]} ${
                    size === 'lg' ? 'text-3xl' : 'text-2xl'
                }`}
            >
                {value}
                {unit && (
                    <span className="ml-1.5 text-sm font-normal text-ink-muted">{unit}</span>
                )}
            </p>

            {hint && <p className="mt-1 text-sm text-ink-subtle">{hint}</p>}
            {children && <div className="mt-3">{children}</div>}
        </div>
    );
}
