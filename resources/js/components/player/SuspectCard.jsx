import { Link } from '@inertiajs/react';
import Badge from '../ui/Badge';

/**
 * One person on the suspect board.
 *
 * A suspect belongs to the first player who actually questions them, so the
 * card has three distinct states — untouched, yours, and taken by someone
 * else — and it must be obvious which without reading closely. The state
 * used to be a line of bold text; it is a Badge now, so the board can be
 * read at a glance across nine people.
 */

/** The one place that decides what state a suspect is in, so the card, the
 *  filter row and the counters can never disagree about it. */
export function suspectState(session, currentPlayerId) {
    if (!session) {
        return 'open';
    }

    if (session.player_id !== currentPlayerId) {
        return 'taken';
    }

    return session.closed_at ? 'closed' : 'mine';
}

export const SUSPECT_STATE_LABELS = {
    open: 'Sin interrogar',
    mine: 'En curso',
    closed: 'Cerrado',
    taken: 'De otro',
};

const STATE_TONES = {
    open: 'neutral',
    mine: 'accent',
    closed: 'success',
    taken: 'neutral',
};

export default function SuspectCard({ playerToken, slug, suspect, session, currentPlayerId }) {
    const state = suspectState(session, currentPlayerId);
    const takenByOther = state === 'taken';

    return (
        <Link
            href={route('immersion.player.interrogation.show', [playerToken, slug])}
            className={`group flex items-start gap-3.5 rounded-card border p-3.5 transition-colors ${
                takenByOther
                    ? 'border-line bg-surface-sunken'
                    : 'border-line bg-surface-raised hover:border-accent'
            }`}
        >
            <img
                src={suspect.photo_url}
                alt=""
                loading="lazy"
                className={`h-16 w-16 shrink-0 rounded-control border border-line object-cover ${
                    takenByOther ? 'opacity-50 grayscale' : ''
                }`}
            />

            <div className="min-w-0 flex-1">
                <p
                    className={`font-semibold leading-tight ${
                        takenByOther ? 'text-ink-muted' : 'text-ink'
                    }`}
                >
                    {suspect.name}
                </p>
                <p className="case-stamp mt-1 text-[11px] text-ink-subtle">{suspect.role}</p>

                {suspect.connection && (
                    /* "Why this person matters" is the line a player actually
                       reads to decide who to interrogate. */
                    <p className="mt-1.5 text-sm leading-snug text-ink-muted">
                        {suspect.connection}
                    </p>
                )}

                <div className="mt-2.5 flex flex-wrap items-center gap-2">
                    <Badge tone={STATE_TONES[state]} className="max-w-full">
                        <span className="min-w-0 truncate">
                            {takenByOther
                                ? `${SUSPECT_STATE_LABELS.taken} · ${session.player.name}`
                                : SUSPECT_STATE_LABELS[state]}
                        </span>
                    </Badge>

                    {session && !takenByOther && (
                        <span className="text-xs text-ink-subtle">
                            <span className="tabular">
                                {session.questions_used}/{session.max_questions}
                            </span>{' '}
                            preguntas
                        </span>
                    )}
                </div>
            </div>
        </Link>
    );
}
