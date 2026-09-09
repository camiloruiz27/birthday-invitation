/**
 * Placeholder shape for content that is still loading.
 *
 * Marked aria-hidden with a single polite "loading" status for the whole
 * group: announcing every individual bar would be noise.
 */
export default function Skeleton({ className = '' }) {
    return <div aria-hidden="true" className={`skeleton ${className}`} />;
}

/** Stand-in for a list of cards, sized to roughly match the real rows. */
export function SkeletonList({ rows = 3, className = '' }) {
    return (
        <div role="status" aria-label="Cargando" className={`space-y-3 ${className}`}>
            {Array.from({ length: rows }, (_, index) => (
                <div
                    key={index}
                    className="rounded-card border border-line bg-surface-raised p-4"
                >
                    <Skeleton className="h-4 w-1/3" />
                    <Skeleton className="mt-3 h-3 w-1/2" />
                </div>
            ))}
        </div>
    );
}

export function SkeletonText({ lines = 3, className = '' }) {
    return (
        <div role="status" aria-label="Cargando" className={`space-y-2 ${className}`}>
            {Array.from({ length: lines }, (_, index) => (
                <Skeleton
                    key={index}
                    // Last line short, so it reads as a paragraph rather than a block.
                    className={`h-3 ${index === lines - 1 ? 'w-2/5' : 'w-full'}`}
                />
            ))}
        </div>
    );
}
