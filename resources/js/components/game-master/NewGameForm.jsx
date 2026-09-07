import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import Button from '../ui/Button';
import PlayerRow from './PlayerRow';

function emptyPlayer() {
    return { name: '', email: '' };
}

export default function NewGameForm() {
    const [players, setPlayers] = useState(() => Array.from({ length: 6 }, emptyPlayer));
    const { data, setData, post, processing } = useForm({ name: '', players });

    function updatePlayer(index, value) {
        const next = players.map((p, i) => (i === index ? value : p));
        setPlayers(next);
        setData('players', next);
    }

    function addPlayer() {
        const next = [...players, emptyPlayer()];
        setPlayers(next);
        setData('players', next);
    }

    function removePlayer(index) {
        if (players.length <= 1) return;
        const next = players.filter((_, i) => i !== index);
        setPlayers(next);
        setData('players', next);
    }

    function submit(e) {
        e.preventDefault();
        post(route('immersion.gm.games.store'));
    }

    return (
        <form onSubmit={submit} className="mt-4 space-y-6">
            <div>
                <label htmlFor="name" className="block text-sm font-bold">Nombre de la partida</label>
                <input
                    type="text"
                    id="name"
                    required
                    placeholder="Ej. Mesa 1 - sabado noche"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className="mt-1 w-full border-2 border-ink bg-white px-3 py-2 text-sm"
                />
            </div>

            <div>
                <p className="text-sm font-bold">Jugadores</p>
                <p className="text-xs text-muted">Quita filas si vas a probar con menos de 6 personas.</p>
                <div className="mt-2 space-y-2">
                    {players.map((player, index) => (
                        <PlayerRow
                            key={index}
                            player={player}
                            onChange={(value) => updatePlayer(index, value)}
                            onRemove={() => removePlayer(index)}
                            canRemove={players.length > 1}
                        />
                    ))}
                </div>
                <button
                    type="button"
                    onClick={addPlayer}
                    className="mt-3 text-xs font-bold uppercase tracking-wide underline"
                >
                    + Agregar jugador
                </button>
            </div>

            <Button type="submit" disabled={processing} className="w-full">
                Crear partida
            </Button>
        </form>
    );
}
