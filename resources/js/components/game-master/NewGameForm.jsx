import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import Button from '../ui/Button';
import { TextField, SelectField } from '../ui/Field';
import PlayerRow from './PlayerRow';

function emptyPlayer() {
    return { name: '', email: '' };
}

export default function NewGameForm({ library }) {
    const [players, setPlayers] = useState(() => Array.from({ length: 6 }, emptyPlayer));
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        case_slug: library[0]?.slug || '',
        players,
    });

    function updatePlayers(next) {
        setPlayers(next);
        setData('players', next);
    }

    function submit(event) {
        event.preventDefault();
        post(route('immersion.gm.games.store'));
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <TextField
                id="name"
                label="Nombre de la partida"
                value={data.name}
                onChange={(value) => setData('name', value)}
                error={errors.name}
                placeholder="Ej. Mesa 1 — sábado noche"
                hint="Solo para que la reconozcas en tu panel; los jugadores no la ven."
                required
            />

            {/* Only cases in the library are offered. The server re-checks the
                entitlement: this select is convenience, not security. */}
            <SelectField
                id="case_slug"
                label="Caso"
                value={data.case_slug}
                onChange={(value) => setData('case_slug', value)}
                error={errors.case_slug}
                disabled={library.length === 1}
                options={library.map((item) => ({ value: item.slug, label: item.name }))}
            />

            <fieldset>
                <legend className="text-sm font-medium text-ink">Jugadores</legend>
                <p className="mt-1 text-xs text-ink-muted">
                    Cada jugador recibe su propio enlace de acceso. Quita filas si van a
                    ser menos de 6.
                </p>

                <div className="mt-3 space-y-3">
                    {players.map((player, index) => (
                        <PlayerRow
                            key={index}
                            index={index}
                            player={player}
                            error={errors[`players.${index}.email`] || errors[`players.${index}.name`]}
                            onChange={(value) =>
                                updatePlayers(players.map((p, i) => (i === index ? value : p)))
                            }
                            onRemove={() =>
                                updatePlayers(players.filter((_, i) => i !== index))
                            }
                            canRemove={players.length > 1}
                        />
                    ))}
                </div>

                <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => updatePlayers([...players, emptyPlayer()])}
                    className="mt-3"
                >
                    + Agregar jugador
                </Button>
            </fieldset>

            <Button type="submit" loading={processing} fullWidth>
                {processing ? 'Creando…' : 'Crear partida'}
            </Button>
        </form>
    );
}
