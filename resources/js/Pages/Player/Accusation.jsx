import { Head, Link, useForm } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import usePoll from '../../hooks/usePoll';

function CaseField({ id, label, error, children }) {
    return (
        <div>
            <label htmlFor={id} className="case-stamp block text-xs">
                {label}
            </label>
            {children}
            {error && (
                <p id={`${id}-error`} className="mt-1 text-xs font-bold text-red-800">
                    {error}
                </p>
            )}
        </div>
    );
}

const CONTROL =
    'mt-1.5 w-full border-2 bg-white px-3 py-2.5 text-base';

export default function Accusation({ player, game, unlocked, locked, suspects, pending }) {
    const { data, setData, post, processing, errors } = useForm({
        suspect_slug: player.accusation?.suspect_slug || '',
        weapon: player.accusation?.weapon || '',
        motive: player.accusation?.motive || '',
    });

    const alreadySent = Boolean(player.accusation);

    // The ending can be published by someone else finishing their accusation,
    // so the page has to notice without a reload.
    usePoll(['game'], { interval: 10000, enabled: alreadySent && !game.ending_revealed_at });

    function submit(event) {
        event.preventDefault();
        post(route('immersion.player.accusation.store', player.access_token));
    }

    function border(field) {
        return errors[field] ? 'border-red-800' : 'border-paper-ink';
    }

    return (
        <PlayerLayout
            player={player}
            game={game}
            section={game.ending_revealed_at ? 'solution' : 'accusation'}
            kicker="Expediente"
            title="Acusación final"
        >
            <Head title="Acusación final" />

            {game.ending_revealed_at ? (
                <div className="border-2 border-paper-ink bg-paper-raised px-6 py-10 text-center">
                    <p className="case-stamp text-sm">El caso está resuelto</p>
                    <p className="mx-auto mt-2 max-w-sm text-sm text-paper-muted">
                        Ya se reveló quién fue y quiénes acertaron.
                    </p>
                    <Link
                        href={route('immersion.player.solution', player.access_token)}
                        className="case-stamp mt-6 inline-block min-h-12 border-2 border-paper-ink bg-paper-ink px-6 py-3 text-sm text-paper"
                    >
                        Ver la solución
                    </Link>
                </div>
            ) : !unlocked ? (
                <div className="border-2 border-dashed border-paper-line px-6 py-12 text-center">
                    <p className="case-stamp text-sm">Todavía no disponible</p>
                    <p className="mx-auto mt-2 max-w-sm text-sm text-paper-muted">
                        El formulario se habilita cuando la investigación llega a su fase
                        final. Sigue revisando tu bandeja.
                    </p>
                </div>
            ) : locked ? (
                <div className="border-2 border-dashed border-paper-line px-6 py-12 text-center">
                    <p className="case-stamp text-sm">Las acusaciones se cerraron</p>
                    <p className="mx-auto mt-2 max-w-sm text-sm text-paper-muted">
                        El Game Master dio por terminado el caso.
                    </p>
                </div>
            ) : (
                <div className="border-2 border-paper-ink bg-paper-raised p-4 sm:p-6">
                    <h2 className="case-stamp text-sm">Tu acusación, {player.name}</h2>
                    <p className="mt-2 text-sm text-paper-muted">
                        {alreadySent
                            ? 'Ya enviaste tu acusación. Puedes cambiarla hasta que se revele la solución.'
                            : 'Puedes cambiarla hasta que se revele la solución.'}
                    </p>

                    {alreadySent && pending > 0 && (
                        <p className="mt-3 border-t border-dashed border-paper-line pt-3 text-sm">
                            Faltan <strong>{pending}</strong>{' '}
                            {pending === 1 ? 'investigador' : 'investigadores'} por acusar. La
                            solución se revela sola cuando estén todas.
                        </p>
                    )}

                    <form onSubmit={submit} className="mt-6 space-y-5">
                        <CaseField id="suspect_slug" label="¿Quién?" error={errors.suspect_slug}>
                            <select
                                id="suspect_slug"
                                name="suspect_slug"
                                required
                                value={data.suspect_slug}
                                onChange={(event) => setData('suspect_slug', event.target.value)}
                                aria-invalid={errors.suspect_slug ? 'true' : undefined}
                                className={`${CONTROL} ${border('suspect_slug')}`}
                            >
                                <option value="" disabled>
                                    Elige a una persona…
                                </option>
                                {suspects.map((suspect) => (
                                    <option key={suspect.slug} value={suspect.slug}>
                                        {suspect.name} — {suspect.role}
                                    </option>
                                ))}
                            </select>
                        </CaseField>

                        <CaseField id="weapon" label="¿Con qué?" error={errors.weapon}>
                            <input
                                id="weapon"
                                name="weapon"
                                type="text"
                                required
                                value={data.weapon}
                                onChange={(event) => setData('weapon', event.target.value)}
                                aria-invalid={errors.weapon ? 'true' : undefined}
                                className={`${CONTROL} ${border('weapon')}`}
                            />
                        </CaseField>

                        <CaseField id="motive" label="¿Por qué?" error={errors.motive}>
                            <textarea
                                id="motive"
                                name="motive"
                                rows={5}
                                required
                                value={data.motive}
                                onChange={(event) => setData('motive', event.target.value)}
                                aria-invalid={errors.motive ? 'true' : undefined}
                                className={`${CONTROL} ${border('motive')}`}
                            />
                        </CaseField>

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
