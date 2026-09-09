/**
 * What a list shows when it has nothing in it.
 *
 * An empty list should say why it is empty and what to do about it — "no games
 * yet" plus the action that creates one — never just render blank space.
 */
export default function EmptyState({ title, description, action, className = '' }) {
    return (
        <div
            className={`rounded-card border border-dashed border-line-strong px-6 py-10 text-center ${className}`}
        >
            <p className="text-sm font-semibold text-ink">{title}</p>
            {description && (
                <p className="mx-auto mt-2 max-w-sm text-sm text-ink-muted">{description}</p>
            )}
            {action && <div className="mt-5 flex justify-center">{action}</div>}
        </div>
    );
}
