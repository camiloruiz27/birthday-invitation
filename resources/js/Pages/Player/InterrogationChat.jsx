import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import ChatMessage from '../../components/player/ChatMessage';
import ChatComposer from '../../components/player/ChatComposer';
import axios from '../../lib/axios';

let tempId = -1;

export default function InterrogationChat({ player, slug, suspect, session, originalTestimonyHtml, lockedBy }) {
    const [messages, setMessages] = useState(session.messages);
    const [closed, setClosed] = useState(session.closed_at !== null);
    const [questionsUsed, setQuestionsUsed] = useState(session.questions_used);
    const [testimonyHtml, setTestimonyHtml] = useState(originalTestimonyHtml);
    const [sending, setSending] = useState(false);
    const [lastQuestion, setLastQuestion] = useState(null);
    const [raceLockedBy, setRaceLockedBy] = useState(null);
    const scrollRef = useRef(null);

    const effectiveLockedBy = lockedBy || raceLockedBy;

    useEffect(() => {
        const el = scrollRef.current;
        if (el) el.scrollTop = el.scrollHeight;
    }, [messages]);

    async function handleSend(question) {
        const playerTempId = tempId--;
        const suspectTempId = tempId--;
        const now = new Date().toISOString();

        setLastQuestion(question);
        setSending(true);
        setMessages((prev) => [
            ...prev,
            { id: playerTempId, role: 'player', content: question, created_at: now },
            { id: suspectTempId, role: 'suspect', content: null, typing: true },
        ]);

        try {
            const { data } = await axios.post(
                route('immersion.player.interrogation.ask', [player.access_token, slug]),
                { question }
            );

            setMessages((prev) => [
                ...prev.filter((m) => m.id !== playerTempId && m.id !== suspectTempId),
                data.player_message,
                data.suspect_message,
            ]);
            setQuestionsUsed(data.questions_used);
            setClosed(data.closed);
            if (data.original_testimony_html) setTestimonyHtml(data.original_testimony_html);
            setLastQuestion(null);
        } catch (err) {
            if (err.response?.data?.locked) {
                // Alguien mas gano la carrera por interrogar a este sospechoso
                // justo ahora: recargamos la pagina para traer el chat real
                // (el de la otra persona) y la declaracion oficial ya reveladas.
                router.reload();
                return;
            }

            setMessages((prev) =>
                prev.map((m) =>
                    m.id === suspectTempId ? { ...m, typing: false, failed: true, content: '' } : m
                )
            );
        } finally {
            setSending(false);
        }
    }

    function retry(failedSuspectId) {
        if (!lastQuestion) return;
        setMessages((prev) => prev.filter((m) => m.id !== failedSuspectId && m.id !== failedSuspectId + 1));
        handleSend(lastQuestion);
    }

    return (
        <ImmersionLayout
            title={`Interrogatorio — ${suspect.name}`}
            headerActions={
                <Link
                    href={route('immersion.player.interrogation.index', player.access_token)}
                    className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                >
                    &larr; Personas
                </Link>
            }
        >
            <Head title={`Interrogatorio — ${suspect.name}`} />

            <div className="mb-4 flex flex-wrap items-center justify-between gap-3 border-2 border-ink bg-paper-card px-4 py-3">
                <div className="flex min-w-0 items-center gap-3">
                    <img
                        src={`/immersion/photos/${suspect.photo}`}
                        alt={suspect.name}
                        className="h-14 w-14 shrink-0 rounded border border-border-soft object-cover"
                    />
                    <div className="min-w-0">
                        <p className="immersion-stamp text-xs uppercase tracking-[0.2em] text-muted">{suspect.role}</p>
                        <h2 className="text-lg font-bold">{suspect.name}</h2>
                    </div>
                </div>
                {!effectiveLockedBy && <span className="shrink-0 text-sm font-bold">Preguntas: {questionsUsed}/5</span>}
            </div>

            {messages.length > 0 && (
                <div
                    ref={scrollRef}
                    className="mb-4 max-h-[60vh] space-y-2 overflow-y-auto border border-border-soft bg-paper/40 p-3"
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

            {!closed && !effectiveLockedBy ? (
                <ChatComposer onSend={handleSend} sending={sending} remaining={5 - questionsUsed} />
            ) : (
                <>
                    <div className="border-2 border-dashed border-border-soft p-4 text-center text-sm text-muted">
                        {effectiveLockedBy
                            ? `Ya fue interrogado por ${effectiveLockedBy}. Aquí tienes lo que se preguntó y su declaración oficial.`
                            : `Ya usaste tus 5 preguntas con ${suspect.name}. Abajo tienes su declaración oficial completa.`}
                    </div>

                    <div className="mt-4 border-2 border-ink bg-paper-card p-4">
                        <p className="immersion-stamp mb-2 text-xs uppercase tracking-[0.2em] text-muted">Declaración oficial</p>
                        <div
                            className="prose prose-sm max-w-none"
                            dangerouslySetInnerHTML={{ __html: testimonyHtml || '' }}
                        />
                    </div>
                </>
            )}
        </ImmersionLayout>
    );
}
