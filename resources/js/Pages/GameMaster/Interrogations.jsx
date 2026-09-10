import { Head } from '@inertiajs/react';
import GameMasterLayout from '../../Layouts/GameMasterLayout';
import Accordion from '../../components/ui/Accordion';
import Badge from '../../components/ui/Badge';
import EmptyState from '../../components/ui/EmptyState';
import usePoll from '../../hooks/usePoll';

/**
 * The same conversation the player had, read back by the Game Master.
 *
 * Not ChatMessage: the GM is reviewing a record, not taking part, so this
 * reads as a transcript rather than a chat. What it does borrow is the two
 * things the flat version dropped — who is speaking, told by more than a
 * 12px label, and whitespace-pre-wrap, without which every multi-paragraph
 * answer collapsed into one block.
 */
function Transcript({ messages }) {
    return (
        <div className="space-y-3">
            {messages.map((message) => {
                const isPlayer = message.role === 'player';

                return (
                    <div
                        key={message.id}
                        className={`border-l-2 pl-3 ${
                            isPlayer ? 'border-accent' : 'border-line-strong'
                        }`}
                    >
                        <p className="case-stamp text-[10px] text-ink-subtle">
                            {isPlayer ? 'Jugador' : 'Sospechoso'}
                        </p>
                        <p
                            className={`mt-1 whitespace-pre-wrap wrap-break-word text-sm ${
                                isPlayer ? 'text-ink-muted' : 'text-ink'
                            }`}
                        >
                            {message.content}
                        </p>
                    </div>
                );
            })}
        </div>
    );
}

export default function Interrogations({ game, sessions }) {
    // Questions are being asked right now; a static page shows a GM the
    // state of the table when they opened it, not the state of the table.
    usePoll(['sessions'], { interval: 12000, enabled: game.status === 'running' });

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
                                <span className="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span className="min-w-0 truncate font-medium">
                                        {session.suspect_name || session.suspect_slug}
                                    </span>
                                    <span className="min-w-0 truncate text-ink-muted">
                                        · {session.player.name}
                                    </span>
                                    <Badge
                                        tone={session.closed_at ? 'accent' : 'success'}
                                        className="shrink-0"
                                    >
                                        <span className="tabular">
                                            {session.questions_used}/{session.max_questions}
                                        </span>
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
