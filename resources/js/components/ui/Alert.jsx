const VARIANTS = {
    status: {
        classes: 'border-success-dim bg-success-dim/40 text-ink',
        // Advisory: announced when the reader is idle, so it does not cut off
        // whatever is being read.
        role: 'status',
        politeness: 'polite',
    },
    error: {
        classes: 'border-danger bg-danger-dim/40 text-ink',
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
