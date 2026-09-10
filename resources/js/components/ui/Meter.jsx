/**
 * A proportion, as a bar.
 *
 * Built by hand three times before this existed — and one of the three
 * forgot `role="progressbar"`, so the same idea was announced to a screen
 * reader on two screens and silent on the third. The aria wiring is the
 * reason this is a component and not a div with a background.
 *
 * `tone` is about meaning, not decoration: `accent` is a normal reading,
 * `danger` is "you are out". Pass `label` whenever the bar is not already
 * described by adjacent text.
 */
const TONES = {
    accent: 'bg-accent',
    danger: 'bg-danger-strong',
    muted: 'bg-line-strong',
};

export default function Meter({
    value,
    max,
    tone = 'accent',
    label,
    size = 'sm',
    className = '',
}) {
    const safeMax = max > 0 ? max : 1;
    const percent = Math.min(100, Math.max(0, (value / safeMax) * 100));

    return (
        <div
            role="progressbar"
            aria-valuenow={value}
            aria-valuemin={0}
            aria-valuemax={max}
            aria-label={label}
            className={`overflow-hidden rounded-full bg-surface-sunken ${
                size === 'md' ? 'h-2' : 'h-1.5'
            } ${className}`}
        >
            <div className={`h-full rounded-full ${TONES[tone]}`} style={{ width: `${percent}%` }} />
        </div>
    );
}
