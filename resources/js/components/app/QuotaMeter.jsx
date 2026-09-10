import Meter from '../ui/Meter';

/**
 * Slots used against the game limit for ONE case.
 *
 * Shown wherever creating a game is offered, so running out is never a
 * surprise that only appears after filling in a whole form.
 */
export default function QuotaMeter({ quota, label = 'Partidas de este caso', className = '' }) {
    return (
        <div className={className}>
            <div className="flex items-baseline justify-between gap-3">
                <span className="text-sm text-ink-muted">{label}</span>
                <span className="tabular text-sm text-ink">
                    {quota.used} / {quota.limit}
                </span>
            </div>

            {/* -strong for the full state, not the base danger hex: as a fill
                on the sunken surface the base measures too dark to read as an
                alarm at 6px tall. */}
            <Meter
                value={quota.used}
                max={quota.limit}
                tone={quota.full ? 'danger' : 'accent'}
                label={label}
                className="mt-1.5"
            />

            <p className="mt-1.5 text-sm text-ink-subtle">
                {quota.full
                    ? 'Sin cupo. Borra una partida de este caso para crear otra.'
                    : `Te ${quota.remaining === 1 ? 'queda' : 'quedan'} ${quota.remaining} de ${quota.limit}.`}
            </p>
        </div>
    );
}
