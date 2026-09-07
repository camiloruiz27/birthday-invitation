/**
 * Slots used against the game limit for ONE case.
 *
 * Shown wherever creating a game is offered, so running out is never a
 * surprise that only appears after filling in a whole form.
 */
export default function QuotaMeter({ quota, label = 'Partidas de este caso', className = '' }) {
    const pct = Math.min(100, (quota.used / quota.limit) * 100);

    return (
        <div className={className}>
            <div className="flex items-baseline justify-between gap-3">
                <span className="text-sm text-ink-muted">{label}</span>
                <span className="tabular text-sm text-ink">
                    {quota.used} / {quota.limit}
                </span>
            </div>

            <div
                className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-surface-sunken"
                role="progressbar"
                aria-valuenow={quota.used}
                aria-valuemin={0}
                aria-valuemax={quota.limit}
                aria-label={label}
            >
                <div
                    className={`h-full rounded-full transition-all ${
                        quota.full ? 'bg-danger' : 'bg-accent'
                    }`}
                    style={{ width: `${pct}%` }}
                />
            </div>

            <p className="mt-1.5 text-xs text-ink-subtle">
                {quota.full
                    ? 'Sin cupo. Borra una partida de este caso para crear otra.'
                    : `Te ${quota.remaining === 1 ? 'queda' : 'quedan'} ${quota.remaining} de ${quota.limit}.`}
            </p>
        </div>
    );
}
