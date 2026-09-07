import { useRef, useState } from 'react';

const MAX_HEIGHT_PX = 120;

export default function ChatComposer({ onSend, sending, remaining }) {
    const [question, setQuestion] = useState('');
    const textareaRef = useRef(null);

    function resize(el) {
        el.style.height = 'auto';
        el.style.height = `${Math.min(el.scrollHeight, MAX_HEIGHT_PX)}px`;
    }

    function handleChange(e) {
        setQuestion(e.target.value);
        resize(e.target);
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

    function handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send();
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        send();
    }

    return (
        <form onSubmit={handleSubmit} className="flex items-end gap-2">
            <textarea
                ref={textareaRef}
                rows={1}
                maxLength={600}
                placeholder={sending ? 'Esperando respuesta...' : 'Escribe tu pregunta...'}
                value={question}
                onChange={handleChange}
                onKeyDown={handleKeyDown}
                disabled={sending}
                className="max-h-30 flex-1 resize-none border-2 border-ink bg-white px-3 py-2 text-sm disabled:opacity-60"
            />
            <button
                type="submit"
                disabled={sending || !question.trim()}
                aria-label={`Preguntar (${remaining} restantes)`}
                title={`Preguntar (${remaining} restantes)`}
                className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ink text-paper disabled:cursor-not-allowed disabled:opacity-40"
            >
                <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
                    <path d="M2.5 12l19-9-6 9 6 9-19-9z" />
                </svg>
            </button>
        </form>
    );
}
