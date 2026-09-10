import { useEffect, useRef, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import Alert from '../../components/ui/Alert';
import Badge from '../../components/ui/Badge';
import CaseDocument from '../../components/player/CaseDocument';
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
        >
            <Head title={`Interrogatorio — ${suspect.name}`} />

            {/* Not flex-1: the layout root is min-h-dvh, a MINIMUM, so nothing
                up the chain ever hands this a bounded height. Growing to fit
                the transcript pushed the composer below the fold and — worse —
                made the auto-scroll below a silent no-op, so after sending a
                question the player saw nothing happen at all. The message list
                caps itself instead. */}
            <div className="flex flex-col overflow-hidden rounded-card border border-line bg-surface">
                <div className="flex items-center gap-3 border-b border-line bg-surface-raised px-4 py-3">
                    <img
                        src={suspect.photo_url}
                        alt=""
                        className="h-12 w-12 shrink-0 rounded-control border border-line object-cover"
                    />
                    <div className="min-w-0 flex-1">
                        <p className="truncate font-semibold leading-tight text-ink">
                            {suspect.name}
                        </p>
                        {suspect.connection && (
                            <p className="truncate text-xs text-ink-muted">{suspect.connection}</p>
                        )}
                    </div>

                    {effectiveLockedBy ? (
                        // shrink-0 + truncate: this branch fires exactly when
                        // two players race for the same suspect, and a full
                        // name with no cap wraps to three lines inside a pill
                        // and squeezes the suspect's own name out.
                        <Badge className="max-w-32 shrink-0">
                            <span className="min-w-0 truncate">{effectiveLockedBy}</span>
                        </Badge>
                    ) : (
                        <Badge tone={closed ? 'success' : 'accent'}>
                            <span className="tabular">
                                {questionsUsed}/{maxQuestions}
                            </span>
                        </Badge>
                    )}
                </div>

                {messages.length > 0 && (
                    <div
                        ref={scrollRef}
                        className="max-h-[45dvh] space-y-3 overflow-y-auto bg-surface-sunken/40 px-3 py-4 sm:max-h-[55vh]"
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

                {!readOnly && (
                    <ChatComposer
                        onSend={handleSend}
                        sending={sending}
                        remaining={maxQuestions - questionsUsed}
                        total={maxQuestions}
                    />
                )}
            </div>

            {readOnly &&
                (outOfCredits && !closed && !effectiveLockedBy ? (
                    /* Out of AI capacity, not out of questions: the official
                       statement is not unlocked, so it must not be shown. */
                    <Alert variant="warning" className="mt-5 mb-0">
                        Esta partida se quedó sin créditos de inteligencia artificial, así que{' '}
                        {suspect.name} no puede seguir respondiendo. Avísale al Game Master. Tus
                        preguntas siguen intactas.
                    </Alert>
                ) : (
                    <div className="mt-5">
                        <Alert variant="info">
                            {effectiveLockedBy
                                ? `${suspect.name} ya fue interrogado por ${effectiveLockedBy}. Arriba tienes lo que se preguntó, y aquí abajo su declaración oficial.`
                                : `Usaste tus ${maxQuestions} preguntas con ${suspect.name}. Aquí abajo tienes su declaración oficial completa.`}
                        </Alert>

                        <p className="case-stamp mb-3 text-[10px] text-accent">
                            Declaración oficial
                        </p>

                        <CaseDocument
                            html={testimonyHtml || ''}
                            id={`declaracion-${slug}`}
                            headingLevel={3}
                        />
                    </div>
                ))}
        </PlayerLayout>
    );
}
