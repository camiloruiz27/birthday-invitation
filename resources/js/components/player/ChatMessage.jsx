import { formatTime } from '../../lib/format';

function TypingDots() {
    return (
        <span className="flex items-center gap-1 py-1">
            {[0, 1, 2].map((i) => (
                <span
                    key={i}
                    className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted"
                    style={{ animationDelay: `${i * 150}ms` }}
                />
            ))}
        </span>
    );
}

export default function ChatMessage({ content, role, createdAt, typing = false, failed = false, onRetry }) {
    const isPlayer = role === 'player';

    return (
        <div className={`flex ${isPlayer ? 'justify-end' : 'justify-start'}`}>
            <div
                className={`max-w-[75%] px-3 py-2 text-sm shadow-sm ${
                    isPlayer
                        ? 'rounded-2xl rounded-br-sm bg-ink text-paper'
                        : 'rounded-2xl rounded-bl-sm border border-border-soft bg-paper-card text-ink'
                } ${failed ? 'border border-red-800/60' : ''}`}
            >
                {typing ? (
                    <TypingDots />
                ) : (
                    <p className="whitespace-pre-wrap break-words">{content}</p>
                )}

                {failed && (
                    <div className="mt-1 flex items-center gap-2">
                        <span className="text-xs text-red-700">No se pudo enviar.</span>
                        <button
                            type="button"
                            onClick={onRetry}
                            className="text-xs font-bold underline underline-offset-2"
                        >
                            Reintentar
                        </button>
                    </div>
                )}

                {!typing && createdAt && (
                    <p className={`mt-0.5 text-right text-[10px] opacity-70 ${isPlayer ? 'text-paper' : 'text-muted'}`}>
                        {formatTime(createdAt)}
                    </p>
                )}
            </div>
        </div>
    );
}
