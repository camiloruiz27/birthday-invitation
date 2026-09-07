import { Head } from '@inertiajs/react';
import GameMasterLayout from '../../Layouts/GameMasterLayout';
import Accordion from '../../components/ui/Accordion';
import Badge from '../../components/ui/Badge';
import EmptyState from '../../components/ui/EmptyState';

function Transcript({ messages }) {
    return (
        <div className="space-y-3">
            {messages.map((message) => (
                <div key={message.id}>
                    <p className="text-xs font-medium uppercase tracking-wide text-ink-subtle">
                        {message.role === 'player' ? 'Jugador' : 'Sospechoso'}
                    </p>
                    <p className="mt-0.5 text-sm text-ink">{message.content}</p>
                </div>
            ))}
        </div>
    );
}

export default function Interrogations({ game, sessions }) {
    return (
        // Reaching this page means the policy already allowed spoilers.
        <GameMasterLayout
            game={game}
            tab="interrogations"
            title={game.name}
            can={{ viewSpoilers: true }}
        >
            <Head title={`Interrogatorios — ${game.name}`} />

            {sessions.length === 0 ? (
                <EmptyState
                    title="Todavía no hay ningún interrogatorio"
                    description={
                        game.interrogation_enabled
                            ? 'Aparecerán aquí en cuanto un jugador haga su primera pregunta a un sospechoso.'
                            : 'La mecánica de interrogatorio está deshabilitada para esta partida.'
                    }
                />
            ) : (
                <div className="space-y-3">
                    {sessions.map((session) => (
                        <Accordion
                            key={session.id}
                            summary={
                                <span className="flex flex-wrap items-center gap-2">
                                    <span className="font-medium">{session.suspect_slug}</span>
                                    <span className="text-ink-muted">
                                        · {session.player.name}
                                    </span>
                                    <Badge tone={session.closed_at ? 'accent' : 'success'}>
                                        {session.questions_used}/{session.max_questions}
                                        {session.closed_at ? ' · cerrado' : ''}
                                    </Badge>
                                </span>
                            }
                        >
                            <Transcript messages={session.messages} />
                        </Accordion>
                    ))}
                </div>
            )}
        </GameMasterLayout>
    );
}
