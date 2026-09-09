import { Link } from '@inertiajs/react';

/**
 * One person on the suspect board.
 *
 * A suspect belongs to the first player who actually questions them, so the
 * card has three distinct states — untouched, yours, and taken by someone else
 * — and it must be obvious which without reading closely.
 */
export default function SuspectCard({ playerToken, slug, suspect, session, currentPlayerId }) {
    const takenByOther = session && session.player_id !== currentPlayerId;
    const closed = session?.closed_at;

    return (
        <Link
            href={route('immersion.player.interrogation.show', [playerToken, slug])}
            className={`flex items-start gap-3 border-2 border-paper-ink p-3 transition-colors ${
                takenByOther ? 'bg-paper-sunken' : 'bg-paper-raised hover:bg-[#efe6ce]'
            }`}
        >
            <img
                src={suspect.photo_url}
                alt={suspect.name}
                loading="lazy"
                className={`h-16 w-16 shrink-0 rounded border border-paper-line object-cover ${
                    takenByOther ? 'opacity-60 grayscale' : ''
                }`}
            />

            <div className="min-w-0">
                <p className="font-bold leading-tight">{suspect.name}</p>
                <p className="case-stamp mt-0.5 text-[10px] text-paper-muted">{suspect.role}</p>

                {suspect.connection && (
                    <p className="mt-1 text-xs leading-snug">{suspect.connection}</p>
                )}

                <p className="mt-1.5 text-xs font-bold">
                    {!session && 'Sin interrogar'}
                    {takenByOther && `Interrogado por ${session.player.name}`}
                    {session && !takenByOther && (
                        <>
                            <span className="tabular">
                                {session.questions_used}/{session.max_questions}
                            </span>
                            {closed ? ' preguntas — cerrado' : ' preguntas'}
                        </>
                    )}
                </p>
            </div>
        </Link>
    );
}
