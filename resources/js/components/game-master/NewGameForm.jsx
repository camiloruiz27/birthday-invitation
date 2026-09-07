import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import Button from '../ui/Button';
import { TextField, SelectField } from '../ui/Field';
import PlayerRow from './PlayerRow';

function emptyPlayer() {
    return { name: '', email: '' };
}

const MODES = [
    {
        value: 'gm_led',
        title: 'Yo dirijo',
        description:
            'Controlas la partida: inicias, pausas, adelantas eventos y ves todos los interrogatorios. No juegas.',
    },
    {
        value: 'automatic',
        title: 'Automática — yo también juego',
        description:
            'El sistema envía el material solo y tú investigas con el resto. Los interrogatorios y las acusaciones de los demás quedan ocultos hasta que cierres el caso.',
    },
];

function ModePicker({ value, onChange }) {
    return (
        <fieldset>
            <legend className="text-sm font-medium text-ink">¿Cómo vas a jugarla?</legend>

            <div className="mt-3 space-y-3">
                {MODES.map((mode) => (
                    <label
                        key={mode.value}
                        className={`flex cursor-pointer gap-3 rounded-card border p-4 transition-colors ${
                            value === mode.value
                                ? 'border-accent bg-accent-dim/30'
                                : 'border-line hover:border-line-strong'
                        }`}
                    >
                        <input
                            type="radio"
                            name="mode"
                            value={mode.value}
                            checked={value === mode.value}
                            onChange={(event) => onChange(event.target.value)}
                            className="mt-1 h-4 w-4 shrink-0"
                        />
                        <span className="min-w-0">
                            <span className="block text-sm font-medium text-ink">{mode.title}</span>
                            <span className="mt-1 block text-sm text-ink-muted">
                                {mode.description}
                            </span>
                        </span>
                    </label>
                ))}
            </div>
        </fieldset>
    );
}

export default function NewGameForm({ library }) {
    const [players, setPlayers] = useState(() => Array.from({ length: 6 }, emptyPlayer));
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        case_slug: library[0]?.slug || '',
        mode: 'gm_led',
        players,
    });

    const isAutomatic = data.mode === 'automatic';

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
                autoFocus
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

            <ModePicker value={data.mode} onChange={(value) => setData('mode', value)} />

            <fieldset>
                <legend className="text-sm font-medium text-ink">
                    {isAutomatic ? 'Los demás jugadores' : 'Jugadores'}
                </legend>
                <p className="mt-1 text-xs text-ink-muted">
                    Cada jugador recibe su propio enlace de acceso. Quita filas si van a
                    ser menos de 6.
                    {isAutomatic && ' Tú te agregas solo, no hace falta que te pongas aquí.'}
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
