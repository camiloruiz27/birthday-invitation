/**
 * Raised panel on the platform surface. `as` lets a card be a semantic
 * <section> or <article> instead of a bare div when it is real page structure.
 */
export default function Card({
    as: Tag = 'div',
    padded = true,
    className = '',
    children,
    ...props
}) {
    return (
        <Tag
            className={`rounded-card border border-line bg-surface-raised shadow-raised ${
                padded ? 'p-5 sm:p-6' : ''
            } ${className}`}
            {...props}
        >
            {children}
        </Tag>
    );
}

/**
 * Card heading with an optional action on the right. Keeps the title/action
 * pair from being rebuilt slightly differently on every page.
 */
export function CardHeader({ title, description, actions, className = '' }) {
    return (
        <div className={`mb-4 flex flex-wrap items-start justify-between gap-3 ${className}`}>
            <div className="min-w-0">
                <h2 className="text-base font-semibold text-ink">{title}</h2>
                {description && <p className="mt-1 text-sm text-ink-muted">{description}</p>}
            </div>
            {actions && <div className="flex shrink-0 flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
