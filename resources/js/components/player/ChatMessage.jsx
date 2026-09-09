import { formatTime } from '../../lib/format';

function TypingDots() {
    return (
        <span role="status" aria-label="Escribiendo" className="flex items-center gap-1 py-1.5">
            {[0, 1, 2].map((index) => (
                <span
                    key={index}
                    aria-hidden="true"
                    className="h-1.5 w-1.5 animate-bounce rounded-full bg-paper-muted"
                    style={{ animationDelay: `${index * 150}ms` }}
                />
            ))}
        </span>
    );
}

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
                className={`max-w-[85%] px-3.5 py-2.5 text-sm sm:max-w-[75%] ${
                    isPlayer
                        ? 'rounded-2xl rounded-br-sm bg-paper-ink text-paper'
                        : 'rounded-2xl rounded-bl-sm border border-paper-line bg-paper-raised text-paper-ink'
                } ${failed ? 'border-2 border-red-800' : ''}`}
            >
                {typing ? (
                    <TypingDots />
                ) : (
                    <p className="whitespace-pre-wrap break-words">{content}</p>
                )}

                {failed && (
                    <div className="mt-1.5 flex flex-wrap items-center gap-2">
                        <span className="text-xs font-bold text-red-800">No se pudo enviar.</span>
                        <button
                            type="button"
                            onClick={onRetry}
                            className="min-h-8 text-xs font-bold underline underline-offset-2"
                        >
                            Reintentar
                        </button>
                    </div>
                )}

                {!typing && createdAt && (
                    <p
                        className={`mt-1 text-right text-[10px] ${
                            isPlayer ? 'text-paper-accent' : 'text-paper-muted'
                        }`}
                    >
                        {formatTime(createdAt)}
                    </p>
                )}
            </div>
        </div>
    );
}
