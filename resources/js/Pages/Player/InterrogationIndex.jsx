import { Head, Link } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import SuspectCard from '../../components/player/SuspectCard';

export default function InterrogationIndex({ player, suspects, victim, sessions, maxQuestions }) {
    return (
        <ImmersionLayout
            title="Interrogatorio"
            headerActions={
                <Link
                    href={route('immersion.player.inbox', player.access_token)}
                    className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                >
                    &larr; Bandeja
                </Link>
            }
        >
            <Head title="Interrogatorio" />

            <p className="mb-4 text-sm text-muted">Elige a quien interrogar. Tienes un máximo de {maxQuestions} preguntas por persona.</p>

            <div className="mb-6 flex items-center gap-4 border-2 border-ink bg-ink p-4 text-paper">
                <img
                    src={`/immersion/photos/${victim.photo}`}
                    alt={victim.name}
                    className="h-16 w-16 shrink-0 rounded border-2 border-paper object-cover"
                />
                <div>
                    <p className="immersion-stamp text-[10px] uppercase tracking-[0.2em] text-accent">Víctima</p>
                    <p className="font-bold">{victim.name}</p>
                </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                {Object.entries(suspects).map(([slug, suspect]) => (
                    <SuspectCard
                        key={slug}
                        playerToken={player.access_token}
                        slug={slug}
                        suspect={suspect}
                        session={sessions[slug]}
                        maxQuestions={maxQuestions}
                        currentPlayerId={player.id}
                    />
                ))}
            </div>
        </ImmersionLayout>
    );
}
