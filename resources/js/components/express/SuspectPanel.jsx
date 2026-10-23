import { useEffect, useRef, useState } from 'react';
import Alert from '../ui/Alert';
import Button from '../ui/Button';
import Meter from '../ui/Meter';
import Modal from '../ui/Modal';
import Spinner from '../ui/Spinner';
import Avatar from './Avatar';

const MAX_QUESTION = 200;

/** What each failure code says. `unclear` is not an error: nothing was spent. */
function messageFor(result, suspect, total) {
    switch (result.code) {
        case 'unclear':
            return 'No entendí la pregunta, prueba con otras palabras. (No gastaste ninguna pregunta del equipo.)';
        case 'budget':
            return `El equipo ya usó las ${total} preguntas.`;
        case 'suspect_locked':
            return `Otro jugador ya está interrogando a ${suspect.name}.`;
        case 'closed':
            return 'El tiempo de investigación terminó.';
        default:
            return result.message || 'No se pudo enviar la pregunta. Intenta de nuevo.';
    }
}

/**
 * The questioning of ONE suspect, in a dialog.
 *
 * The team shares a small budget of questions (3, spent as a group), and each
 * suspect is claimed by whoever asks first — the others see "Interroga X" and
 * cannot ask. Chips are the questions the case wrote; free text is matched on
 * the server and may come back `unclear`, in which case nothing is spent.
 *
 * It is a conversation in the console, not a paper document (design system:
 * live chat is platform surface). The answers come back through the room
 * state, so what is shown is exactly what the whole team sees in the log.
 */
export default function SuspectPanel({ suspect, state, open, onClose, onAsk }) {
    const [text, setText] = useState('');
    const [pending, setPending] = useState(false);
    const [notice, setNotice] = useState(null);
    const endRef = useRef(null);

    const interrogation = state.interrogation ?? {};
    const total = interrogation.budget_total ?? 3;
    const used = interrogation.budget_used ?? 0;
    const left = interrogation.budget_left ?? Math.max(0, total - used);
    const log = (Array.isArray(interrogation.log) ? interrogation.log : []).filter(
        (entry) => suspect && entry.suspect_id === suspect.id
    );
    const answered = Array.isArray(suspect?.answered_topics) ? suspect.answered_topics : [];
    const chips = Array.isArray(suspect?.chips) ? suspect.chips : [];

    const locked = Boolean(suspect?.locked_for_me);
    const blocked = locked || left <= 0 || !interrogation.open;

    // A fresh suspect, a fresh conversation.
    useEffect(() => {
        setText('');
        setNotice(null);
    }, [suspect?.id]);

    useEffect(() => {
        if (open) endRef.current?.scrollIntoView({ block: 'nearest' });
    }, [log.length, pending, open]);

    if (!suspect) return null;

    async function send(payload, clearText) {
        if (pending || blocked) return;

        setPending(true);
        setNotice(null);

        const result = await onAsk({ suspect_id: suspect.id, ...payload });

        setPending(false);

        if (result.ok) {
            if (clearText) setText('');

            // Asking again about something the team already has is free.
            if (result.data?.result?.status === 'repeat') {
                setNotice({
                    code: 'repeat',
                    text: 'Eso ya lo habían preguntado: la respuesta está arriba y no gastaste ninguna pregunta.',
                });
            }

            return;
        }

        setNotice({ code: result.code, text: messageFor(result, suspect, total) });
    }

    function submit(event) {
        event.preventDefault();

        const question = text.trim();
        if (!question) return;

        send({ question }, true);
    }

    return (
        <Modal
            open={open}
            onClose={onClose}
            title={suspect.name}
            footer={
                <Button variant="secondary" onClick={onClose}>
                    Cerrar
                </Button>
            }
        >
            <div className="space-y-4">
                <div className="flex items-center gap-3">
                    <Avatar name={suspect.name} src={suspect.portrait} size="lg" />
                    <div className="min-w-0 text-sm">
                        <p className="font-medium text-ink">{suspect.role}</p>
                        {suspect.relation && <p className="text-ink-muted">{suspect.relation}</p>}
                    </div>
                </div>

                <div>
                    <p className="flex items-center justify-between text-xs text-ink-muted">
                        <span>Preguntas del equipo</span>
                        <span className="tabular font-semibold text-ink">
                            {used}/{total}
                        </span>
                    </p>
                    <Meter
                        value={used}
                        max={total}
                        tone={left <= 0 ? 'danger' : 'accent'}
                        label="Preguntas del equipo usadas"
                        className="mt-1.5"
                    />
                </div>

                {locked && (
                    <Alert variant="info" className="mb-0!">
                        {suspect.claimed_by?.nickname
                            ? `${suspect.claimed_by.nickname} ya está interrogando a ${suspect.name}.`
                            : `Otro jugador ya está interrogando a ${suspect.name}.`}
                    </Alert>
                )}

                {!locked && left <= 0 && (
                    <Alert variant="warning" className="mb-0!">
                        El equipo ya usó todas sus preguntas. Revisen "Lo que ya sabemos" y acusen.
                    </Alert>
                )}

                {/* The conversation so far with THIS suspect. */}
                <div
                    role="log"
                    aria-label={`Conversación con ${suspect.name}`}
                    aria-live="polite"
                    className="max-h-64 space-y-3 overflow-y-auto rounded-card border border-line bg-surface-sunken p-3"
                >
                    {log.length === 0 && !pending && (
                        <p className="text-sm text-ink-muted">
                            Aún nadie le ha preguntado nada a {suspect.name}.
                        </p>
                    )}

                    {log.map((entry) => (
                        <div key={entry.id} className="space-y-2 text-sm">
                            <p className="ml-auto max-w-[85%] rounded-card rounded-br-sm bg-accent-dim px-3 py-2 text-ink">
                                <span className="mb-0.5 block text-[11px] text-accent-strong">
                                    {entry.nickname}
                                </span>
                                {entry.question}
                            </p>
                            <p className="max-w-[85%] rounded-card rounded-bl-sm border border-line bg-surface-raised px-3 py-2 text-ink">
                                <span className="mb-0.5 block text-[11px] text-ink-muted">{suspect.name}</span>
                                {entry.answer}
                            </p>
                        </div>
                    ))}

                    {pending && (
                        <p className="flex items-center gap-2 text-sm text-ink-muted">
                            <Spinner size="sm" label={null} /> {suspect.name} está respondiendo…
                        </p>
                    )}

                    <div ref={endRef} />
                </div>

                {chips.length > 0 && (
                    <div>
                        <p className="mb-2 text-xs font-medium text-ink-muted">Preguntas sugeridas</p>
                        <ul className="flex flex-col gap-2">
                            {chips.map((chip) => {
                                const done = answered.includes(chip.topic_id);

                                return (
                                    <li key={chip.topic_id}>
                                        <button
                                            type="button"
                                            disabled={blocked || pending || done}
                                            onClick={() =>
                                                send({ question: chip.text, topic_id: chip.topic_id }, false)
                                            }
                                            className="flex min-h-11 w-full items-center justify-between gap-3 rounded-control border border-line-strong bg-surface-raised px-3 py-2 text-left text-sm text-ink hover:border-accent disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <span>{chip.text}</span>
                                            {done && (
                                                <span className="shrink-0 text-xs text-success-strong">
                                                    <span aria-hidden="true">✓ </span>Ya preguntado
                                                </span>
                                            )}
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                )}

                <form onSubmit={submit}>
                    <label htmlFor={`ask-${suspect.id}`} className="mb-1.5 block text-xs font-medium text-ink-muted">
                        O escribe tu propia pregunta
                    </label>
                    <div className="flex items-start gap-2">
                        <input
                            id={`ask-${suspect.id}`}
                            type="text"
                            value={text}
                            maxLength={MAX_QUESTION}
                            enterKeyHint="send"
                            autoComplete="off"
                            disabled={blocked || pending}
                            onChange={(event) => setText(event.target.value)}
                            placeholder="Escribe tu pregunta…"
                            className="min-h-11 min-w-0 flex-1 rounded-control border border-line-strong bg-surface-sunken px-3 py-2.5 text-base text-ink placeholder:text-ink-subtle disabled:opacity-60"
                        />
                        <Button
                            type="submit"
                            disabled={blocked || !text.trim()}
                            loading={pending}
                            aria-label="Enviar pregunta"
                        >
                            Enviar
                        </Button>
                    </div>
                    <p className="tabular mt-1 text-right text-[11px] text-ink-subtle">
                        {text.length}/{MAX_QUESTION}
                    </p>
                </form>

                {pending && (
                    <p role="status" className="mb-2 text-sm text-ink-muted">
                        {suspect.name} está pensando su respuesta…
                    </p>
                )}

                {notice && (
                    <Alert
                        variant={['unclear', 'repeat', 'ai_unavailable'].includes(notice.code) ? 'info' : 'error'}
                        className="mb-0!"
                    >
                        {notice.text}
                    </Alert>
                )}
            </div>
        </Modal>
    );
}
