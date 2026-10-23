import { useState } from 'react';
import Button from '../ui/Button';
import { DIFFICULTIES, DURATIONS, SORTS } from '../../lib/caseDiscovery';
import { formatPrice } from '../../lib/format';

const CONTROL =
    'min-h-11 w-full rounded-control border border-line-strong bg-surface-sunken px-3 text-sm text-ink placeholder:text-ink-subtle';

function toggle(values, value) {
    return values.includes(value) ? values.filter((item) => item !== value) : [...values, value];
}

function CheckOption({ id, checked, onChange, children }) {
    return (
        <label htmlFor={id} className="flex min-h-10 cursor-pointer items-center gap-2 rounded-control px-2 text-sm text-ink hover:bg-surface-raised">
            <input
                id={id}
                type="checkbox"
                checked={checked}
                onChange={onChange}
                className="h-4 w-4 rounded border-line-strong bg-surface-sunken"
            />
            {children}
        </label>
    );
}

function Chip({ children, onRemove }) {
    return (
        <button
            type="button"
            onClick={onRemove}
            className="inline-flex min-h-8 items-center gap-1.5 rounded-full border border-line-strong bg-surface-raised px-3 text-xs text-ink hover:border-accent"
        >
            {children}
            <span aria-hidden="true" className="text-ink-muted">×</span>
            <span className="sr-only">Quitar filtro</span>
        </button>
    );
}

/**
 * Search, filter and ordering controls shared by the catalog and the library.
 * Filters apply as they change; nothing is submitted.
 */
export default function CaseDiscoveryControls({ discovery }) {
    const { area, filters, update, reset, options, results, active, priceError } = discovery;
    const [open, setOpen] = useState(active);

    const mechanicNames = Object.fromEntries(options.mechanics.map((mechanic) => [mechanic.slug, mechanic.name]));
    const difficultyNames = Object.fromEntries(DIFFICULTIES.map((difficulty) => [difficulty.value, difficulty.label]));
    const durationNames = Object.fromEntries(DURATIONS.map((duration) => [duration.value, duration.label]));
    const priceStep = options.currency === 'USD' ? '0.01' : '1';
    const count = results.length;

    return (
        <form onSubmit={(event) => event.preventDefault()} role="search" className="mb-8 rounded-card border border-line bg-surface-raised p-4 sm:p-5">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-end">
                <label className="block min-w-0 flex-1">
                    <span className="mb-1.5 block text-sm font-medium text-ink">Buscar casos</span>
                    <input
                        type="search"
                        value={filters.q}
                        maxLength={120}
                        onChange={(event) => update({ q: event.target.value })}
                        placeholder="Nombre, tema o descripción"
                        className={CONTROL}
                    />
                </label>

                <label className="block lg:w-64">
                    <span className="mb-1.5 block text-sm font-medium text-ink">Ordenar por</span>
                    <select value={filters.sort} onChange={(event) => update({ sort: event.target.value })} className={CONTROL}>
                        {SORTS[area].map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                    </select>
                </label>

                <Button type="button" variant="secondary" onClick={() => setOpen((value) => !value)} aria-expanded={open} aria-controls={`case-filters-${area}`}>
                    Filtros{active ? ' activos' : ''}
                </Button>
            </div>

            {open && (
                <div id={`case-filters-${area}`} className="mt-5 grid gap-5 border-t border-line pt-5 sm:grid-cols-2 lg:grid-cols-4">
                    <fieldset>
                        <legend className="text-sm font-medium text-ink">Dificultad</legend>
                        <div className="mt-2 space-y-1">
                            {DIFFICULTIES.map((difficulty) => (
                                <CheckOption key={difficulty.value} id={`${area}-difficulty-${difficulty.value}`} checked={filters.difficulty.includes(difficulty.value)} onChange={() => update({ difficulty: toggle(filters.difficulty, difficulty.value) })}>
                                    {difficulty.label}
                                </CheckOption>
                            ))}
                        </div>
                    </fieldset>

                    <div className="space-y-4">
                        {options.players.length > 0 && (
                            <label className="block">
                                <span className="mb-1.5 block text-sm font-medium text-ink">Tamaño de tu grupo</span>
                                <select value={filters.players ?? ''} onChange={(event) => update({ players: event.target.value ? Number(event.target.value) : null })} className={CONTROL}>
                                    <option value="">Cualquier grupo</option>
                                    {options.players.map((players) => <option key={players} value={players}>{players} {players === 1 ? 'jugador' : 'jugadores'}</option>)}
                                </select>
                            </label>
                        )}
                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-ink">Duración</span>
                            <select value={filters.duration ?? ''} onChange={(event) => update({ duration: event.target.value || null })} className={CONTROL}>
                                <option value="">Cualquier duración</option>
                                {DURATIONS.map((duration) => <option key={duration.value} value={duration.value}>{duration.label}</option>)}
                            </select>
                        </label>
                    </div>

                    <fieldset>
                        <legend className="text-sm font-medium text-ink">Mecánicas</legend>
                        <div className="mt-2 space-y-1">
                            {options.mechanics.map((mechanic) => (
                                <CheckOption key={mechanic.slug} id={`${area}-mechanic-${mechanic.slug}`} checked={filters.mechanics.includes(mechanic.slug)} onChange={() => update({ mechanics: toggle(filters.mechanics, mechanic.slug) })}>
                                    {mechanic.name}
                                </CheckOption>
                            ))}
                            <CheckOption id={`${area}-ai`} checked={filters.ai} onChange={() => update({ ai: !filters.ai })}>
                                Incluye IA
                            </CheckOption>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend className="text-sm font-medium text-ink">Precio{area === 'library' ? ' actual' : ''} ({options.currency})</legend>
                        <div className="mt-2 grid grid-cols-2 gap-2">
                            <label>
                                <span className="sr-only">Precio mínimo</span>
                                <input type="number" inputMode="decimal" min="0" step={priceStep} value={filters.priceMin} onChange={(event) => update({ priceMin: event.target.value })} placeholder="Mínimo" className={CONTROL} />
                            </label>
                            <label>
                                <span className="sr-only">Precio máximo</span>
                                <input type="number" inputMode="decimal" min="0" step={priceStep} value={filters.priceMax} onChange={(event) => update({ priceMax: event.target.value })} placeholder="Máximo" className={CONTROL} />
                            </label>
                        </div>
                        {priceError && <p role="alert" className="mt-2 text-xs font-medium text-danger-strong">{priceError}</p>}
                    </fieldset>
                </div>
            )}

            <div className="mt-4 flex flex-wrap items-center gap-2 border-t border-line pt-4">
                <p className="mr-1 text-sm text-ink-muted" role="status" aria-live="polite">
                    {count === 1 ? '1 caso encontrado' : `${count} casos encontrados`}
                </p>
                {filters.q.trim() && <Chip onRemove={() => update({ q: '' })}>“{filters.q.trim()}”</Chip>}
                {filters.difficulty.map((value) => <Chip key={value} onRemove={() => update({ difficulty: filters.difficulty.filter((item) => item !== value) })}>{difficultyNames[value]}</Chip>)}
                {filters.players && <Chip onRemove={() => update({ players: null })}>{filters.players} {filters.players === 1 ? 'jugador' : 'jugadores'}</Chip>}
                {filters.duration && <Chip onRemove={() => update({ duration: null })}>{durationNames[filters.duration]}</Chip>}
                {filters.mechanics.map((value) => <Chip key={value} onRemove={() => update({ mechanics: filters.mechanics.filter((item) => item !== value) })}>{mechanicNames[value] ?? value}</Chip>)}
                {filters.ai && <Chip onRemove={() => update({ ai: false })}>Incluye IA</Chip>}
                {filters.priceMin !== '' && <Chip onRemove={() => update({ priceMin: '' })}>Desde {formatPrice(Number(filters.priceMin) * (options.currency === 'USD' ? 100 : 1), options.currency)}</Chip>}
                {filters.priceMax !== '' && <Chip onRemove={() => update({ priceMax: '' })}>Hasta {formatPrice(Number(filters.priceMax) * (options.currency === 'USD' ? 100 : 1), options.currency)}</Chip>}
                {active && <button type="button" onClick={reset} className="min-h-8 px-2 text-xs font-medium text-accent underline hover:text-accent-strong">Limpiar filtros</button>}
            </div>
        </form>
    );
}
