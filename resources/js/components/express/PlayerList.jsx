import Badge from '../ui/Badge';

/**
 * Who is in the room, as chips. `showAccused` adds a check next to whoever has
 * already accused (during the accusation window). Safe with no players.
 */
export default function PlayerList({ players, showAccused = false, className = '' }) {
    const list = Array.isArray(players) ? players : [];

    if (list.length === 0) {
        return <p className={`text-sm text-ink-muted ${className}`}>Todavía no hay jugadores.</p>;
    }

    return (
        <ul className={`flex flex-wrap gap-2 ${className}`} aria-label="Jugadores en la sala">
            {list.map((player) => (
                <li
                    key={player.id}
                    className={`inline-flex min-h-9 max-w-full items-center gap-2 rounded-full border px-3.5 py-1 text-sm ${
                        player.is_me
                            ? 'border-accent bg-accent-dim/50 text-ink'
                            : 'border-line-strong bg-surface-raised text-ink'
                    }`}
                >
                    <span className="truncate">{player.nickname}</span>
                    {player.is_me && <span className="text-xs text-ink-muted">(tú)</span>}
                    {player.is_gm && <Badge tone="accent">Anfitrión</Badge>}
                    {showAccused && player.accused && (
                        <span className="text-success-strong" role="img" aria-label="Ya acusó">
                            ✓
                        </span>
                    )}
                </li>
            ))}
        </ul>
    );
}
