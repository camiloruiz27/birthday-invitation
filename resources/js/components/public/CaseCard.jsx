import { Link } from '@inertiajs/react';
import Badge from '../ui/Badge';
import { formatPrice, formatDuration, formatPlayerRange } from '../../lib/format';

export const DIFFICULTY_LABELS = {
    easy: 'Accesible',
    medium: 'Intermedio',
    hard: 'Exigente',
};

/** Duration · players · difficulty, the three things a buyer checks first. */
export function CaseFacts({ mysteryCase, className = '' }) {
    const facts = [
        formatDuration(mysteryCase.duration_minutes),
        formatPlayerRange(mysteryCase.min_players, mysteryCase.max_players),
        DIFFICULTY_LABELS[mysteryCase.difficulty] || mysteryCase.difficulty,
    ].filter(Boolean);

    return (
        <ul className={`flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-muted ${className}`}>
            {facts.map((fact, index) => (
                <li key={fact} className="flex items-center gap-3">
                    {index > 0 && (
                        <span aria-hidden="true" className="text-ink-subtle">
                            ·
                        </span>
                    )}
                    {fact}
                </li>
            ))}
        </ul>
    );
}

export default function CaseCard({ mysteryCase, owned = false }) {
    return (
        <Link
            href={route('cases.show', mysteryCase.slug)}
            className="group flex flex-col overflow-hidden rounded-card border border-line bg-surface-raised transition-colors hover:border-line-strong"
        >
            {mysteryCase.cover_url && (
                <div className="aspect-[3/2] overflow-hidden bg-surface-sunken">
                    <img
                        src={mysteryCase.cover_url}
                        alt=""
                        loading="lazy"
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                </div>
            )}

            <div className="flex flex-1 flex-col p-5">
                <div className="flex items-start justify-between gap-3">
                    <h3 className="font-semibold text-ink">{mysteryCase.name}</h3>
                    {owned && <Badge tone="success">En tu biblioteca</Badge>}
                </div>

                {mysteryCase.tagline && (
                    <p className="mt-2 text-sm text-ink-muted">{mysteryCase.tagline}</p>
                )}

                <CaseFacts mysteryCase={mysteryCase} className="mt-4" />

                <div className="mt-5 flex items-baseline justify-between gap-3 border-t border-line pt-4">
                    <span className="font-semibold text-ink">
                        {formatPrice(mysteryCase.price_amount, mysteryCase.currency)}
                    </span>
                    <span className="text-sm text-accent group-hover:text-accent-strong">
                        Ver el caso →
                    </span>
                </div>
            </div>
        </Link>
    );
}
