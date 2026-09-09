import { useEffect, useRef, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import ChatMessage from '../../components/player/ChatMessage';
import ChatComposer from '../../components/player/ChatComposer';
import axios from '../../lib/axios';

// Negative ids for optimistic messages, so they cannot collide with real ones.
let tempId = -1;

export default function InterrogationChat({
    player,
    game,
    slug,
    suspect,
    session,
    originalTestimonyHtml,
    lockedBy,
}) {
    const [messages, setMessages] = useState(session.messages);
    const [closed, setClosed] = useState(session.closed_at !== null);
    const [questionsUsed, setQuestionsUsed] = useState(session.questions_used);
    const [maxQuestions, setMaxQuestions] = useState(session.max_questions);
    const [testimonyHtml, setTestimonyHtml] = useState(originalTestimonyHtml);
    const [sending, setSending] = useState(false);
    const [lastQuestion, setLastQuestion] = useState(null);
    const [raceLockedBy, setRaceLockedBy] = useState(null);
    const [outOfCredits, setOutOfCredits] = useState(false);
    const scrollRef = useRef(null);

    const effectiveLockedBy = lockedBy || raceLockedBy;
    const readOnly = closed || Boolean(effectiveLockedBy) || outOfCredits;

    useEffect(() => {
        const element = scrollRef.current;
        if (element) element.scrollTop = element.scrollHeight;
    }, [messages]);

    async function handleSend(question) {
        const playerTempId = tempId--;
        const suspectTempId = tempId--;

        setLastQuestion(question);
        setSending(true);

        // Show the question and a typing bubble immediately: the AI turn takes
        // seconds, and a silent form feels broken.
        setMessages((previous) => [
            ...previous,
            {
                id: playerTempId,
                role: 'player',
                content: question,
                created_at: new Date().toISOString(),
            },
            { id: suspectTempId, role: 'suspect', content: null, typing: true },
        ]);

        try {
            const { data } = await axios.post(
                route('immersion.player.interrogation.ask', [player.access_token, slug]),
                { question }
            );

            setMessages((previous) => [
                ...previous.filter(
                    (message) => message.id !== playerTempId && message.id !== suspectTempId
                ),
                data.player_message,
                data.suspect_message,
            ]);
            setQuestionsUsed(data.questions_used);
            setMaxQuestions(data.max_questions);
            setClosed(data.closed);
            if (data.original_testimony_html) setTestimonyHtml(data.original_testimony_html);
            setLastQuestion(null);
        } catch (error) {
            if (error.response?.data?.locked) {
                // Someone else claimed this suspect in the same instant.
                // Reload to get their transcript and the official statement.
                setRaceLockedBy(error.response.data.locked_by);
                router.reload();

                return;
            }

            if (error.response?.data?.out_of_credits) {
                // Retrying cannot help: the game has no AI capacity left. Drop
                // the optimistic bubbles rather than offering a retry that is
                // guaranteed to fail again.
                setMessages((previous) =>
                    previous.filter(
                        (message) =>
                            message.id !== playerTempId && message.id !== suspectTempId
                    )
                );
                setOutOfCredits(true);
                setLastQuestion(null);

                return;
            }

            setMessages((previous) =>
                previous.map((message) =>
                    message.id === suspectTempId
                        ? { ...message, typing: false, failed: true, content: '' }
                        : message
                )
            );
        } finally {
            setSending(false);
        }
    }

    function retry(failedId) {
        if (!lastQuestion) return;

        // Drop the failed pair (question + failed reply) before resending.
        setMessages((previous) =>
            previous.filter((message) => message.id !== failedId && message.id !== failedId + 1)
        );
        handleSend(lastQuestion);
    }

    return (
        <PlayerLayout
            player={player}
            game={game}
            focused
            kicker={suspect.role}
            title={suspect.name}
            back={{
                href: route('immersion.player.interrogation.index', player.access_token),
                label: 'Personas',
            }}
            contentClassName="px-0 py-0 sm:px-6 sm:py-6"
        >
            <Head title={`Interrogatorio — ${suspect.name}`} />

            <div className="flex min-h-0 flex-1 flex-col">
                <div className="flex items-center gap-3 border-b-2 border-paper-ink bg-paper-raised px-4 py-3 sm:border-2">
                    <img
                        src={suspect.photo_url}
                        alt={suspect.name}
                        className="h-12 w-12 shrink-0 rounded border border-paper-line object-cover"
                    />
                    <div className="min-w-0 flex-1">
                        <p className="truncate font-bold leading-tight">{suspect.name}</p>
                        {suspect.connection && (
                            <p className="truncate text-xs text-paper-muted">{suspect.connection}</p>
                        )}
                    </div>

                    {!effectiveLockedBy && (
                        <span className="tabular shrink-0 text-sm font-bold">
                            {questionsUsed}/{maxQuestions}
                        </span>
                    )}
                </div>

                {messages.length > 0 && (
                    <div
                        ref={scrollRef}
                        className="flex-1 space-y-2.5 overflow-y-auto bg-paper/40 px-3 py-4 sm:max-h-[55vh] sm:border-x-2 sm:border-paper-ink"
                    >
                        {messages.map((message) => (
                            <ChatMessage
                                key={message.id}
                                role={message.role}
                                content={message.content}
                                createdAt={message.created_at}
                                typing={message.typing}
                                failed={message.failed}
                                onRetry={() => retry(message.id)}
                            />
                        ))}
                    </div>
                )}

                {!readOnly ? (
                    <div className="sm:border-x-2 sm:border-b-2 sm:border-paper-ink">
                        <ChatComposer
                            onSend={handleSend}
                            sending={sending}
                            remaining={maxQuestions - questionsUsed}
                        />
                    </div>
                ) : outOfCredits && !closed && !effectiveLockedBy ? (
                    /* Out of AI capacity, not out of questions: the official
                       statement is not unlocked, so it must not be shown. */
                    <div className="px-4 py-4 sm:px-0">
                        <div className="border-2 border-dashed border-paper-line px-4 py-4 text-center text-sm text-paper-muted">
                            Esta partida se quedó sin créditos de inteligencia artificial, así
                            que {suspect.name} no puede seguir respondiendo. Avísale al Game
                            Master. Tus preguntas siguen intactas.
                        </div>
                    </div>
                ) : (
                    <div className="px-4 py-4 sm:px-0">
                        <div className="border-2 border-dashed border-paper-line px-4 py-4 text-center text-sm text-paper-muted">
                            {effectiveLockedBy
                                ? `${suspect.name} ya fue interrogado por ${effectiveLockedBy}. Abajo tienes lo que se preguntó y su declaración oficial.`
                                : `Usaste tus ${maxQuestions} preguntas con ${suspect.name}. Abajo tienes su declaración oficial completa.`}
                        </div>

                        <div className="mt-4 border-2 border-paper-ink bg-paper-raised p-4">
                            <p className="case-stamp mb-3 text-[10px] text-paper-muted">
                                Declaración oficial
                            </p>
                            <div
                                className="case-prose text-[15px]"
                                dangerouslySetInnerHTML={{ __html: testimonyHtml || '' }}
                            />
                        </div>
                    </div>
                )}
            </div>
        </PlayerLayout>
    );
}
