import { Head, useForm } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import Alert from '../../components/ui/Alert';
import Button from '../../components/ui/Button';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import { TextField, TextArea } from '../../components/ui/Field';
import usePoll from '../../hooks/usePoll';

/**
 * Picking the culprit as a board of people rather than a dropdown.
 *
 * A <select> holding nine names is the wrong control for the most dramatic
 * decision in the game: it hides eight of the options and reads like a
 * shipping form. These are radios — real ones, so the arrow keys and the
 * label association come from the browser — dressed as cards.
 */
function SuspectChoice({ suspects, value, onChange, error }) {
    return (
        <fieldset>
            <legend className="text-sm font-medium text-ink">
                ¿Quién?
                <span className="ml-1 text-danger-strong" aria-hidden="true">
                    *
                </span>
            </legend>

            <div
                className="mt-2.5 grid gap-2.5 sm:grid-cols-2"
                aria-describedby={error ? 'suspect_slug-error' : undefined}
            >
                {suspects.map((suspect) => {
                    const selected = value === suspect.slug;

                    return (
                        <label
                            key={suspect.slug}
                            className={`flex cursor-pointer items-start gap-3 rounded-card border p-3.5 transition-colors ${
                                selected
                                    ? 'border-accent bg-accent-dim/40'
                                    : 'border-line bg-surface-raised hover:border-line-strong'
                            }`}
                        >
                            <input
                                type="radio"
                                name="suspect_slug"
                                value={suspect.slug}
                                checked={selected}
                                onChange={() => onChange(suspect.slug)}
                                required
                                className="mt-1 h-4 w-4 shrink-0 border-line-strong bg-surface-sunken"
                            />
                            <span className="min-w-0">
                                <span className="block font-semibold text-ink">{suspect.name}</span>
                                {/* Plain, not a 10px tracked stamp: the role
                                    is what tells two suspects apart, on the
                                    most consequential tap of the game. */}
                                <span className="mt-0.5 block text-sm text-ink-muted">
                                    {suspect.role}
                                </span>
                            </span>
                        </label>
                    );
                })}
            </div>

            {error && (
                <p id="suspect_slug-error" className="mt-1.5 text-xs font-medium text-danger-strong">
                    {error}
                </p>
            )}
        </fieldset>
    );
}

export default function Accusation({ player, game, unlocked, locked, suspects, pending }) {
    const { data, setData, post, processing, errors } = useForm({
        suspect_slug: player.accusation?.suspect_slug || '',
        weapon: player.accusation?.weapon || '',
        motive: player.accusation?.motive || '',
    });

    const alreadySent = Boolean(player.accusation);
    const chosen = suspects.find((suspect) => suspect.slug === data.suspect_slug);

    // The ending can be published by someone else finishing their accusation,
    // so the page has to notice without a reload.
    usePoll(['game'], { interval: 10000, enabled: alreadySent && !game.ending_revealed_at });

    function submit(event) {
        event.preventDefault();
        post(route('immersion.player.accusation.store', player.access_token));
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
                <EmptyState
                    title="El caso está resuelto"
                    description="Ya se reveló quién fue y quiénes acertaron."
                    action={
                        <Button href={route('immersion.player.solution', player.access_token)}>
                            Ver la solución
                        </Button>
                    }
                />
            ) : !unlocked ? (
                <EmptyState
                    title="Todavía no disponible"
                    description="El formulario se habilita cuando la investigación llega a su fase final. Sigue revisando tu bandeja."
                    action={
                        <Button
                            variant="secondary"
                            href={route('immersion.player.inbox', player.access_token)}
                        >
                            Volver a la bandeja
                        </Button>
                    }
                />
            ) : locked ? (
                <EmptyState
                    title="Las acusaciones se cerraron"
                    description="El Game Master dio por terminado el caso."
                />
            ) : (
                <Card as="section">
                    <CardHeader
                        title={`Tu acusación, ${player.name}`}
                        description={
                            alreadySent
                                ? 'Ya la enviaste. Puedes cambiarla hasta que se revele la solución.'
                                : 'Puedes cambiarla hasta que se revele la solución.'
                        }
                    />

                    {alreadySent && pending > 0 && (
                        <Alert variant="info">
                            Faltan <strong className="text-ink">{pending}</strong>{' '}
                            {pending === 1 ? 'investigador' : 'investigadores'} por acusar. La
                            solución se revela sola cuando estén todas.
                        </Alert>
                    )}

                    <form onSubmit={submit} className="space-y-6">
                        <SuspectChoice
                            suspects={suspects}
                            value={data.suspect_slug}
                            onChange={(slug) => setData('suspect_slug', slug)}
                            error={errors.suspect_slug}
                        />

                        <TextField
                            id="weapon"
                            label="¿Con qué?"
                            value={data.weapon}
                            onChange={(value) => setData('weapon', value)}
                            error={errors.weapon}
                            hint="El método o el arma: qué usó para hacerlo."
                            required
                        />

                        <TextArea
                            id="motive"
                            label="¿Por qué?"
                            rows={5}
                            value={data.motive}
                            onChange={(value) => setData('motive', value)}
                            error={errors.motive}
                            hint="Tu razonamiento. Lo leerán los demás cuando se revele la solución."
                            required
                        />

                        {/* Reading the accusation back as one sentence is the
                            last chance to notice it does not hold together. */}
                        {chosen && data.weapon && (
                            <p className="rounded-card border border-line bg-surface-sunken p-4 text-sm text-ink-muted">
                                Vas a acusar a{' '}
                                <strong className="text-ink">{chosen.name}</strong>, con{' '}
                                <strong className="text-ink">{data.weapon}</strong>.
                            </p>
                        )}

                        <Button type="submit" loading={processing} fullWidth size="lg">
                            {alreadySent ? 'Actualizar acusación' : 'Enviar acusación'}
                        </Button>
                    </form>
                </Card>
            )}
        </PlayerLayout>
    );
}
