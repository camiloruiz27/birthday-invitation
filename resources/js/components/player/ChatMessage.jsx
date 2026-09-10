import { formatTime } from '../../lib/format';

function TypingDots() {
    return (
        <span role="status" aria-label="Escribiendo" className="flex items-center gap-1 py-1.5">
            {[0, 1, 2].map((index) => (
                <span
                    key={index}
                    aria-hidden="true"
                    className="h-1.5 w-1.5 animate-bounce rounded-full bg-ink-subtle motion-reduce:hidden"
                    style={{ animationDelay: `${index * 150}ms` }}
                />
            ))}

            {/* app.css forces every animation to ~0s under reduced motion, so
                the dots would freeze mid-bounce and the only sign that the
                suspect is thinking would be a static blob. */}
            <span aria-hidden="true" className="hidden text-xs italic text-ink-muted motion-reduce:inline">
                Escribiendo…
            </span>
        </span>
    );
}

/**
 * One turn of the interrogation.
 *
 * A live conversation is not a document, so this sits on the console rather
 * than on paper — the suspect's written statement is the thing that gets a
 * paper sheet, once it unlocks.
 */
export default function ChatMessage({
    content,
    role,
    createdAt,
    typing = false,
    failed = false,
    onRetry,
}) {
    const isPlayer = role === 'player';

    return (
        <div className={`flex ${isPlayer ? 'justify-end' : 'justify-start'}`}>
            <div
                className={`max-w-[85%] px-4 py-2.5 text-sm sm:max-w-[75%] ${
                    isPlayer
                        ? 'rounded-2xl rounded-br-sm bg-accent text-ink-inverse'
                        : 'rounded-2xl rounded-bl-sm border border-line bg-surface-raised text-ink'
                } ${failed ? 'border border-danger-strong' : ''}`}
            >
                {typing ? (
                    <TypingDots />
                ) : (
                    <p className="whitespace-pre-wrap wrap-break-word">{content}</p>
                )}

                {failed && (
                    <div className="mt-1.5 flex flex-wrap items-center gap-2">
                        <span className="text-xs font-semibold text-danger-strong">
                            No se pudo enviar.
                        </span>
                        <button
                            type="button"
                            onClick={onRetry}
                            /* min-h-11: the recovery tap after a failed AI
                               call, on venue wifi. It has to be easy. */
                            className="inline-flex min-h-11 items-center text-sm font-semibold text-ink underline underline-offset-2"
                        >
                            Reintentar
                        </button>
                    </div>
                )}

                {!typing && createdAt && (
                    <p
                        className={`mt-1 text-right font-mono text-[10px] ${
                            isPlayer ? 'text-ink-inverse/70' : 'text-ink-subtle'
                        }`}
                    >
                        {formatTime(createdAt)}
                    </p>
                )}
            </div>
        </div>
    );
}
