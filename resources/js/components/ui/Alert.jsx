const VARIANTS = {
    status: {
        classes: 'border-success-dim bg-success-dim/40 text-ink',
        // Advisory: announced when the reader is idle, so it does not cut off
        // whatever is being read.
        role: 'status',
        politeness: 'polite',
    },
    error: {
        // -strong border: the base danger hex is calibrated for ~7:1 on
        // Papel, not for sitting directly on this dark surface — see the
        // token comment in app.css. The body text stays text-ink (Papel),
        // never tinted by the tone.
        classes: 'border-danger-strong bg-danger-dim/40 text-ink',
        role: 'alert',
        politeness: 'assertive',
    },
    info: {
        classes: 'border-line-strong bg-surface-raised text-ink-muted',
        role: 'status',
        politeness: 'polite',
    },
    warning: {
        classes: 'border-accent-dim bg-accent-dim/40 text-ink',
        role: 'status',
        politeness: 'polite',
    },
};

/**
 * Inline message. Renders nothing when there is no content, so callers can
 * hand it a possibly-empty flash value without guarding.
 */
export default function Alert({ variant = 'info', title, className = '', children }) {
    if (!children && !title) {
        return null;
    }

    const { classes, role, politeness } = VARIANTS[variant];

    return (
        <div
            role={role}
            aria-live={politeness}
            className={`mb-5 rounded-card border px-4 py-3 text-sm ${classes} ${className}`}
        >
            {title && <p className="font-semibold">{title}</p>}
            {children && <div className={title ? 'mt-1' : ''}>{children}</div>}
        </div>
    );
}
