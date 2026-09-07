import { Head } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import SuspectCard from '../../components/player/SuspectCard';

export default function InterrogationIndex({ player, game, suspects, victim, sessions, maxQuestions }) {
    return (
        <PlayerLayout
            player={player}
            game={game}
            section="interrogation"
            kicker="Expediente"
            title="Personas de interés"
        >
            <Head title="Interrogatorio" />

            <div className="mb-5 flex items-center gap-4 border-2 border-paper-ink bg-paper-ink p-4 text-paper">
                <img
                    src={victim.photo_url}
                    alt={victim.name}
                    className="h-16 w-16 shrink-0 rounded border-2 border-paper object-cover"
                />
                <div className="min-w-0">
                    <p className="case-stamp text-[10px] text-paper-accent">Víctima</p>
                    <p className="truncate font-bold">{victim.name}</p>
                </div>
            </div>

            <p className="mb-4 text-sm text-paper-muted">
                Tienes <strong className="text-paper-ink">{maxQuestions} preguntas</strong> por
                persona. Cada persona habla con un solo investigador: el primero que le
                pregunte se queda con ese interrogatorio, así que repártanse y compartan lo
                que averigüen.
            </p>

            <div className="grid gap-3 sm:grid-cols-2">
                {Object.entries(suspects).map(([slug, suspect]) => (
                    <SuspectCard
                        key={slug}
                        playerToken={player.access_token}
                        slug={slug}
                        suspect={suspect}
                        session={sessions[slug]}
                        currentPlayerId={player.id}
                    />
                ))}
            </div>
        </PlayerLayout>
    );
}
