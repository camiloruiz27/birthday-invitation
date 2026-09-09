import { useRef, useState } from 'react';

const MAX_HEIGHT_PX = 120;
const MAX_LENGTH = 600;

export default function ChatComposer({ onSend, sending, remaining }) {
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

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                send();
            }}
            className="border-t-2 border-paper-ink bg-paper-raised px-3 py-3"
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
                    className="max-h-30 min-h-11 flex-1 resize-none border-2 border-paper-ink bg-white px-3 py-2.5 text-base disabled:opacity-60"
                />

                <button
                    type="submit"
                    disabled={!canSend}
                    className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-paper-ink text-paper disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <span className="sr-only">Enviar pregunta</span>
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" className="h-5 w-5">
                        <path d="M2.5 12l19-9-6 9 6 9-19-9z" />
                    </svg>
                </button>
            </div>

            <p id="question-remaining" className="mt-1.5 text-xs text-paper-muted">
                Te quedan <span className="tabular font-bold">{remaining}</span>{' '}
                {remaining === 1 ? 'pregunta' : 'preguntas'} con esta persona.
            </p>
        </form>
    );
}
