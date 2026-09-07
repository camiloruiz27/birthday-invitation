import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import Button from '../ui/Button';
import Alert from '../ui/Alert';
import { TextField, SelectField, CheckboxField } from '../ui/Field';
import QuotaMeter from '../app/QuotaMeter';
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

const ENDINGS = [
    {
        value: 'classic',
        title: 'Clásico',
        description:
            'Cuando todos acusen, el equipo ve quién fue, cómo y por qué — y quiénes acertaron.',
        available: true,
    },
    {
        value: 'epilogue',
        title: 'Epílogo personalizado',
        description:
            'Cada jugador recibe un mensaje de la persona que acusó: si acertó, confiesa; si no, se defiende.',
        available: false,
    },
    {
        value: 'confession_audio',
        title: 'Confesión en audio',
        description:
            'Recibes una grabación del culpable delatándose, para reproducirla en la mesa.',
        available: false,
    },
];

function EndingPicker({ value, onChange }) {
    return (
        <fieldset>
            <legend className="text-sm font-medium text-ink">¿Cómo termina el caso?</legend>
            <p className="mt-1 text-xs text-ink-muted">
                Se elige ahora porque los finales avanzados reservan capacidad de IA.
            </p>

            <div className="mt-3 space-y-3">
                {ENDINGS.map((ending) => (
                    <label
                        key={ending.value}
                        className={`flex gap-3 rounded-card border p-4 transition-colors ${
                            !ending.available
                                ? 'cursor-not-allowed border-line opacity-50'
                                : value === ending.value
                                  ? 'cursor-pointer border-accent bg-accent-dim/30'
                                  : 'cursor-pointer border-line hover:border-line-strong'
                        }`}
                    >
                        <input
                            type="radio"
                            name="ending_type"
                            value={ending.value}
                            checked={value === ending.value}
                            disabled={!ending.available}
                            onChange={(event) => onChange(event.target.value)}
                            className="mt-1 h-4 w-4 shrink-0"
                        />
                        <span className="min-w-0">
                            <span className="block text-sm font-medium text-ink">
                                {ending.title}
                                {!ending.available && (
                                    <span className="ml-2 text-xs font-normal text-ink-subtle">
                                        Próximamente
                                    </span>
                                )}
                            </span>
                            <span className="mt-1 block text-sm text-ink-muted">
                                {ending.description}
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

    // Start on a case that still has room, so the form does not open already
    // blocked when only one of several cases is full.
    const firstWithRoom = library.find((item) => !item.quota.full) || library[0];

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        case_slug: firstWithRoom?.slug || '',
        mode: 'gm_led',
        ending_type: 'classic',
        interrogation_enabled: true,
        players,
    });

    const isAutomatic = data.mode === 'automatic';
    const selectedCase = library.find((item) => item.slug === data.case_slug);
    const quota = selectedCase?.quota;
    const caseIsFull = Boolean(quota?.full);

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
                entitlement and the quota: this select is convenience, not
                security. */}
            <div>
                <SelectField
                    id="case_slug"
                    label="Caso"
                    value={data.case_slug}
                    onChange={(value) => setData('case_slug', value)}
                    error={errors.case_slug}
                    disabled={library.length === 1}
                    options={library.map((item) => ({
                        value: item.slug,
                        // The count is in the option itself, so a full case is
                        // obvious before it is picked.
                        label: item.quota.full
                            ? `${item.name} — sin cupo (${item.quota.used}/${item.quota.limit})`
                            : `${item.name} (${item.quota.used}/${item.quota.limit})`,
                    }))}
                />

                {quota && !caseIsFull && (
                    <QuotaMeter quota={quota} className="mt-3 max-w-xs" />
                )}
            </div>

            {caseIsFull && (
                <Alert variant="warning" title="Este caso no tiene cupo">
                    Ya tienes {quota.limit} partidas de {selectedCase.name}. Elimina una
                    partida <strong>de este mismo caso</strong> para crear otra, o elige otro
                    caso de tu biblioteca.
                </Alert>
            )}

            <ModePicker value={data.mode} onChange={(value) => setData('mode', value)} />

            <EndingPicker
                value={data.ending_type}
                onChange={(value) => setData('ending_type', value)}
            />

            <CheckboxField
                id="interrogation_enabled"
                label="Habilitar interrogatorios"
                checked={data.interrogation_enabled}
                onChange={(value) => setData('interrogation_enabled', value)}
                hint="Los jugadores podrán preguntar a los sospechosos. Se elige ahora y no se puede cambiar una vez iniciado el caso."
            />

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

            <Button type="submit" loading={processing} disabled={caseIsFull} fullWidth>
                {processing ? 'Creando…' : 'Crear partida'}
            </Button>
        </form>
    );
}
