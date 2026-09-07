import { Head, useForm } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';

function CaseField({ id, label, value, onChange, error, type = 'text', rows }) {
    const Control = rows ? 'textarea' : 'input';

    return (
        <div>
            <label htmlFor={id} className="case-stamp block text-xs">
                {label}
            </label>
            <Control
                id={id}
                name={id}
                type={rows ? undefined : type}
                rows={rows}
                required
                value={value}
                onChange={(event) => onChange(event.target.value)}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={error ? `${id}-error` : undefined}
                className={`mt-1.5 w-full border-2 bg-white px-3 py-2.5 text-base ${
                    error ? 'border-red-800' : 'border-paper-ink'
                }`}
            />
            {error && (
                <p id={`${id}-error`} className="mt-1 text-xs font-bold text-red-800">
                    {error}
                </p>
            )}
        </div>
    );
}

export default function Accusation({ player, game, unlocked }) {
    const { data, setData, post, processing, errors } = useForm({
        suspect_name: player.accusation?.suspect_name || '',
        weapon: player.accusation?.weapon || '',
        motive: player.accusation?.motive || '',
    });

    const alreadySent = Boolean(player.accusation);

    function submit(event) {
        event.preventDefault();
        post(route('immersion.player.accusation.store', player.access_token));
    }

    return (
        <PlayerLayout
            player={player}
            game={game}
            section="accusation"
            kicker="Expediente"
            title="Acusación final"
        >
            <Head title="Acusación final" />

            {!unlocked ? (
                <div className="border-2 border-dashed border-paper-line px-6 py-12 text-center">
                    <p className="case-stamp text-sm">Todavía no disponible</p>
                    <p className="mx-auto mt-2 max-w-sm text-sm text-paper-muted">
                        El formulario se habilita cuando la investigación llega a su fase
                        final. Sigue revisando tu bandeja.
                    </p>
                </div>
            ) : (
                <div className="border-2 border-paper-ink bg-paper-raised p-4 sm:p-6">
                    <h2 className="case-stamp text-sm">Tu acusación, {player.name}</h2>
                    <p className="mt-2 text-sm text-paper-muted">
                        {alreadySent
                            ? 'Ya enviaste una acusación. Puedes cambiarla mientras el Game Master no revele la solución.'
                            : 'Puedes enviarla y cambiarla mientras el Game Master no revele la solución.'}
                    </p>

                    <form onSubmit={submit} className="mt-6 space-y-5">
                        <CaseField
                            id="suspect_name"
                            label="¿Quién?"
                            value={data.suspect_name}
                            onChange={(value) => setData('suspect_name', value)}
                            error={errors.suspect_name}
                        />

                        <CaseField
                            id="weapon"
                            label="¿Con qué?"
                            value={data.weapon}
                            onChange={(value) => setData('weapon', value)}
                            error={errors.weapon}
                        />

                        <CaseField
                            id="motive"
                            label="¿Por qué?"
                            rows={5}
                            value={data.motive}
                            onChange={(value) => setData('motive', value)}
                            error={errors.motive}
                        />

                        <button
                            type="submit"
                            disabled={processing}
                            className="case-stamp min-h-12 w-full border-2 border-paper-ink bg-paper-ink px-4 py-3 text-sm text-paper disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {processing
                                ? 'Enviando…'
                                : alreadySent
                                  ? 'Actualizar acusación'
                                  : 'Enviar acusación'}
                        </button>
                    </form>
                </div>
            )}
        </PlayerLayout>
    );
}
