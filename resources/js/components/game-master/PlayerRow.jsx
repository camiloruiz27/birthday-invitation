export default function PlayerRow({ player, onChange, onRemove, canRemove }) {
    return (
        <div className="grid grid-cols-1 items-center gap-2 sm:grid-cols-[1fr_1fr_auto]">
            <input
                type="text"
                placeholder="Nombre"
                required
                value={player.name}
                onChange={(e) => onChange({ ...player, name: e.target.value })}
                className="border-2 border-ink bg-white px-3 py-2 text-sm"
            />
            <input
                type="email"
                placeholder="Correo"
                required
                value={player.email}
                onChange={(e) => onChange({ ...player, email: e.target.value })}
                className="border-2 border-ink bg-white px-3 py-2 text-sm"
            />
            <button
                type="button"
                onClick={onRemove}
                disabled={!canRemove}
                title="Quitar"
                className="w-full border-2 border-ink px-2 py-2 text-xs font-bold disabled:cursor-not-allowed disabled:opacity-40 sm:w-auto"
            >
                <span className="sm:hidden">Quitar jugador</span>
                <span className="hidden sm:inline">&times;</span>
            </button>
        </div>
    );
}
