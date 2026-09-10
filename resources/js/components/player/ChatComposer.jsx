import { useRef, useState } from 'react';

const MAX_HEIGHT_PX = 120;
const MAX_LENGTH = 600;

export default function ChatComposer({ onSend, sending, remaining, total }) {
    const [question, setQuestion] = useState('');
    const textareaRef = useRef(null);

    function resize(element) {
        element.style.height = 'auto';
        element.style.height = `${Math.min(element.scrollHeight, MAX_HEIGHT_PX)}px`;
    }

    function send() {
        const trimmed = question.trim();
        if (!trimmed || sending) return;

        onSend(trimmed);
        setQuestion('');

        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
        }
    }

    function handleKeyDown(event) {
        // Enter sends, Shift+Enter makes a new line — the convention people
        // already expect from a chat.
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            send();
        }
    }

    const canSend = question.trim() !== '' && !sending;
    const used = total ? total - remaining : 0;

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                send();
            }}
            className="border-t border-line bg-surface-raised p-3"
        >
            <div className="flex items-end gap-2">
                <label htmlFor="question" className="sr-only">
                    Tu pregunta
                </label>

                <textarea
                    ref={textareaRef}
                    id="question"
                    name="question"
                    rows={1}
                    maxLength={MAX_LENGTH}
                    placeholder={sending ? 'Esperando respuesta…' : 'Escribe tu pregunta…'}
                    value={question}
                    onChange={(event) => {
                        setQuestion(event.target.value);
                        resize(event.target);
                    }}
                    onKeyDown={handleKeyDown}
                    disabled={sending}
                    aria-describedby="question-remaining"
                    className="max-h-30 min-h-11 flex-1 resize-none rounded-control border border-line-strong bg-surface-sunken px-3 py-2.5 text-base text-ink placeholder:text-ink-subtle disabled:opacity-60"
                />

                <button
                    type="submit"
                    disabled={!canSend}
                    className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accent text-ink-inverse transition-opacity disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <span className="sr-only">Enviar pregunta</span>
                    <svg
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        aria-hidden="true"
                        className="h-5 w-5"
                    >
                        <path d="M2.5 12l19-9-6 9 6 9-19-9z" />
                    </svg>
                </button>
            </div>

            {/* A bar as well as a number: "2 left" is a fact, a bar that is
                two-thirds gone is a feeling, and this is a resource the
                player is meant to spend carefully. */}
            {total > 0 && (
                <div
                    aria-hidden="true"
                    className="mt-3 flex gap-1"
                >
                    {Array.from({ length: total }, (unused, index) => (
                        <span
                            key={index}
                            className={`h-1 flex-1 rounded-full ${
                                index < used ? 'bg-line-strong' : 'bg-accent'
                            }`}
                        />
                    ))}
                </div>
            )}

            <p id="question-remaining" className="mt-2 text-xs text-ink-muted">
                Te quedan <span className="tabular font-semibold text-ink">{remaining}</span>{' '}
                {remaining === 1 ? 'pregunta' : 'preguntas'} con esta persona.
            </p>
        </form>
    );
}
