import { useState } from 'react';
import { Head } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import Alert from '../../components/ui/Alert';
import EmptyState from '../../components/ui/EmptyState';
import SuspectCard, {
    suspectState,
    SUSPECT_STATE_LABELS,
} from '../../components/player/SuspectCard';
import usePoll from '../../hooks/usePoll';

const FILTERS = ['all', 'open', 'mine', 'closed', 'taken'];

const FILTER_LABELS = {
    all: 'Todas',
    ...SUSPECT_STATE_LABELS,
};

export default function InterrogationIndex({
    player,
    game,
    suspects,
    victim,
    sessions,
    maxQuestions,
}) {
    // Suspects are claimed by whoever asks first, so a board that only
    // updates on reload actively misleads: two players walk into the same
    // interrogation because neither saw the other take it.
    usePoll(['sessions'], { interval: 12000 });

    const [filter, setFilter] = useState('all');

    const people = Object.entries(suspects).map(([slug, suspect]) => ({
        slug,
        suspect,
        session: sessions[slug],
        state: suspectState(sessions[slug], player.id),
    }));

    const counts = people.reduce(
        (totals, person) => ({ ...totals, [person.state]: (totals[person.state] ?? 0) + 1 }),
        { all: people.length }
    );

    const visible = filter === 'all' ? people : people.filter((person) => person.state === filter);

    return (
        <PlayerLayout
            player={player}
            game={game}
            section="interrogation"
            kicker="Expediente"
            title="Personas de interés"
        >
            <Head title="Interrogatorio" />

            <div className="flex items-center gap-4 rounded-card border border-line bg-surface-raised p-4">
                <img
                    src={victim.photo_url}
                    alt=""
                    className="h-20 w-20 shrink-0 rounded-control border border-line object-cover grayscale"
                />
                <div className="min-w-0">
                    <p className="case-stamp text-[10px] text-danger-strong">Víctima</p>
                    <p className="mt-1 truncate font-display text-xl font-semibold text-ink">
                        {victim.name}
                    </p>
                </div>
            </div>

            <Alert variant="info" className="mt-5">
                Tienes <strong className="text-ink">{maxQuestions} preguntas</strong> por persona.
                Cada persona habla con un solo investigador: el primero que le pregunte se queda
                con ese interrogatorio, así que repártanse y compartan lo que averigüen.
            </Alert>

            {/* Nine people is enough that "who is still free?" stops being
                answerable by looking. The strip scrolls sideways instead of
                wrapping: five pills stack into three rows at 360px, which is
                140px of filter chrome above the first suspect. */}
            <div className="no-scrollbar -mx-4 mt-2 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0">
                {FILTERS.filter((key) => key === 'all' || counts[key]).map((key) => (
                    <button
                        key={key}
                        type="button"
                        aria-pressed={filter === key}
                        onClick={() => setFilter(key)}
                        className={`inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full border px-4 text-sm transition-colors ${
                            filter === key
                                ? 'border-accent bg-accent-dim text-accent-strong'
                                : 'border-line-strong text-ink-muted hover:border-accent hover:text-ink'
                        }`}
                    >
                        {FILTER_LABELS[key]}
                        <span className="tabular text-xs text-ink-subtle">{counts[key] ?? 0}</span>
                    </button>
                ))}
            </div>

            {visible.length === 0 ? (
                <EmptyState
                    className="mt-5"
                    title="Nadie en este estado"
                    description="Cambia el filtro para ver al resto de las personas de interés."
                />
            ) : (
                <div className="mt-5 grid gap-3 sm:grid-cols-2">
                    {visible.map((person) => (
                        <SuspectCard
                            key={person.slug}
                            playerToken={player.access_token}
                            slug={person.slug}
                            suspect={person.suspect}
                            session={person.session}
                            currentPlayerId={player.id}
                        />
                    ))}
                </div>
            )}
        </PlayerLayout>
    );
}
