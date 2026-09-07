import { Link } from '@inertiajs/react';

export default function SuspectCard({ playerToken, slug, suspect, session, maxQuestions, currentPlayerId }) {
    const lockedByOther = session && session.player_id !== currentPlayerId;

    return (
        <Link
            href={route('immersion.player.interrogation.show', [playerToken, slug])}
            className="flex items-start gap-3 border-2 border-ink bg-paper-card p-3 hover:bg-[#efe6ce]"
        >
            <img
                src={`/immersion/photos/${suspect.photo}`}
                alt={suspect.name}
                className="h-16 w-16 shrink-0 rounded border border-border-soft object-cover"
            />
            <div className="min-w-0">
                <span className="font-bold">{suspect.name}</span>
                <div className="text-xs uppercase tracking-wide text-muted">{suspect.role}</div>
                {suspect.connection && <p className="mt-1 text-xs">{suspect.connection}</p>}
                <p className="mt-1 text-xs font-bold">
                    {!session && 'Sin iniciar'}
                    {session && lockedByOther && `Interrogado por ${session.player.name}`}
                    {session && !lockedByOther && (
                        <>
                            Preguntas: {session.questions_used}/{maxQuestions}
                            {session.closed_at && ' — cerrado'}
                        </>
                    )}
                </p>
            </div>
        </Link>
    );
}
