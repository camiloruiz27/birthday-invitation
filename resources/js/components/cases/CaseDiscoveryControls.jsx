import { useState } from 'react';
import Button from '../ui/Button';
import { DIFFICULTIES, DURATIONS, SORTS } from '../../lib/caseDiscovery';

const CONTROL =
    'min-h-11 w-full rounded-control border border-line-strong bg-surface-sunken px-3 text-sm text-ink placeholder:text-ink-subtle';

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
    const { area, filters, update, reset, options, results, active } = discovery;
    const [open, setOpen] = useState(active);

    const difficultyNames = Object.fromEntries(DIFFICULTIES.map((difficulty) => [difficulty.value, difficulty.label]));
    const durationNames = Object.fromEntries(DURATIONS.map((duration) => [duration.value, duration.label]));
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
                <div id={`case-filters-${area}`} className="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2 lg:grid-cols-4">
                    <label className="block">
                        <span className="mb-1.5 block text-sm font-medium text-ink">Dificultad</span>
                        <select
                            value={filters.difficulty[0] ?? ''}
                            onChange={(event) => update({ difficulty: event.target.value ? [event.target.value] : [] })}
                            className={CONTROL}
                        >
                            <option value="">Cualquier dificultad</option>
                            {DIFFICULTIES.map((difficulty) => (
                                <option key={difficulty.value} value={difficulty.value}>{difficulty.label}</option>
                            ))}
                        </select>
                    </label>

                    <label className="block">
                        <span className="mb-1.5 block text-sm font-medium text-ink">Tema o ambiente</span>
                        <select
                            value={filters.tags[0] ?? ''}
                            onChange={(event) => update({ tags: event.target.value ? [event.target.value] : [] })}
                            className={CONTROL}
                        >
                            <option value="">Cualquier tema</option>
                            {options.tags.map((tag) => <option key={tag} value={tag}>{tag}</option>)}
                        </select>
                    </label>

                    <label className="block">
                        <span className="mb-1.5 block text-sm font-medium text-ink">Tamaño de tu grupo</span>
                        <select value={filters.players ?? ''} onChange={(event) => update({ players: event.target.value ? Number(event.target.value) : null })} className={CONTROL}>
                            <option value="">Cualquier grupo</option>
                            {options.players.map((players) => <option key={players} value={players}>{players} {players === 1 ? 'jugador' : 'jugadores'}</option>)}
                        </select>
                    </label>

                    <label className="block">
                        <span className="mb-1.5 block text-sm font-medium text-ink">Duración</span>
                        <select value={filters.duration ?? ''} onChange={(event) => update({ duration: event.target.value || null })} className={CONTROL}>
                            <option value="">Cualquier duración</option>
                            {DURATIONS.map((duration) => <option key={duration.value} value={duration.value}>{duration.label}</option>)}
                        </select>
                    </label>
                </div>
            )}

            <div className="mt-4 flex flex-wrap items-center gap-2 border-t border-line pt-4">
                <p className="mr-1 text-sm text-ink-muted" role="status" aria-live="polite">
                    {count === 1 ? '1 caso encontrado' : `${count} casos encontrados`}
                </p>
                {filters.q.trim() && <Chip onRemove={() => update({ q: '' })}>“{filters.q.trim()}”</Chip>}
                {filters.tags[0] && <Chip onRemove={() => update({ tags: [] })}>{filters.tags[0]}</Chip>}
                {filters.difficulty[0] && <Chip onRemove={() => update({ difficulty: [] })}>{difficultyNames[filters.difficulty[0]]}</Chip>}
                {filters.players && <Chip onRemove={() => update({ players: null })}>{filters.players} {filters.players === 1 ? 'jugador' : 'jugadores'}</Chip>}
                {filters.duration && <Chip onRemove={() => update({ duration: null })}>{durationNames[filters.duration]}</Chip>}
                {active && <button type="button" onClick={reset} className="min-h-8 px-2 text-xs font-medium text-accent underline hover:text-accent-strong">Limpiar filtros</button>}
            </div>
        </form>
    );
}
