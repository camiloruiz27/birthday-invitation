import { Link } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import Accordion from '../../components/ui/Accordion';

export default function Interrogations({ game, sessions }) {
    return (
        <ImmersionLayout
            title="Interrogatorios (Mecanica 7)"
            headerActions={
                <Link
                    href={route('immersion.gm.game.show', game.id)}
                    className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                >
                    &larr; {game.name}
                </Link>
            }
        >
            <h2 className="immersion-stamp text-sm uppercase tracking-[0.2em] text-muted">Interrogatorios (Mecanica 7)</h2>

            {sessions.length === 0 && (
                <p className="mt-3 text-sm text-muted">Todavía no hay ningún interrogatorio iniciado.</p>
            )}

            <div className="mt-4 space-y-4">
                {sessions.map((session) => (
                    <Accordion
                        key={session.id}
                        summary={
                            <>
                                {session.player.name} &rarr; {session.suspect_slug}
                                <span className="ml-2 text-xs font-normal uppercase text-muted">
                                    {session.questions_used}/5 {session.closed_at ? '— cerrado' : ''}
                                </span>
                            </>
                        }
                    >
                        <div className="space-y-2">
                            {session.messages.map((message) => (
                                <p key={message.id}>
                                    <strong>{message.role === 'player' ? 'Jugador' : 'Sospechoso'}:</strong> {message.content}
                                </p>
                            ))}
                        </div>
                    </Accordion>
                ))}
            </div>
        </ImmersionLayout>
    );
}
