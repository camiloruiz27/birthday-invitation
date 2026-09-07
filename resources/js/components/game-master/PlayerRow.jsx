/**
 * One player line in the new-game form.
 *
 * The inputs are labelled per row rather than relying on a shared column
 * header, because on a phone the fields stack and the header is far away.
 */
export default function PlayerRow({ index, player, error, onChange, onRemove, canRemove }) {
    const nameId = `player-${index}-name`;
    const emailId = `player-${index}-email`;

    return (
        <div className="rounded-control border border-line bg-surface-sunken p-3 sm:border-0 sm:bg-transparent sm:p-0">
            <div className="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-start">
                <div>
                    <label htmlFor={nameId} className="text-xs text-ink-muted sm:sr-only">
                        Nombre del jugador {index + 1}
                    </label>
                    <input
                        id={nameId}
                        type="text"
                        placeholder="Nombre"
                        required
                        value={player.name}
                        onChange={(event) => onChange({ ...player, name: event.target.value })}
                        className="mt-1 w-full rounded-control border border-line-strong bg-surface-sunken px-3 py-2.5 text-base text-ink placeholder:text-ink-subtle sm:mt-0"
                    />
                </div>

                <div>
                    <label htmlFor={emailId} className="text-xs text-ink-muted sm:sr-only">
                        Correo del jugador {index + 1}
                    </label>
                    <input
                        id={emailId}
                        type="email"
                        placeholder="Correo"
                        required
                        value={player.email}
                        onChange={(event) => onChange({ ...player, email: event.target.value })}
                        aria-invalid={error ? 'true' : undefined}
                        className={`mt-1 w-full rounded-control border bg-surface-sunken px-3 py-2.5 text-base text-ink placeholder:text-ink-subtle sm:mt-0 ${
                            error ? 'border-danger' : 'border-line-strong'
                        }`}
                    />
                </div>

                <button
                    type="button"
                    onClick={onRemove}
                    disabled={!canRemove}
                    className="min-h-11 w-full rounded-control border border-line-strong px-3 text-sm text-ink-muted hover:text-ink disabled:cursor-not-allowed disabled:opacity-40 sm:w-11"
                >
                    <span className="sm:hidden">Quitar jugador</span>
                    <span aria-hidden="true" className="hidden sm:inline">
                        &times;
                    </span>
                    <span className="sr-only hidden sm:inline">
                        Quitar jugador {index + 1}
                    </span>
                </button>
            </div>

            {error && <p className="mt-1.5 text-xs font-medium text-danger">{error}</p>}
        </div>
    );
}
