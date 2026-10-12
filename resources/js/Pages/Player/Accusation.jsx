import { Head, useForm } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import Alert from '../../components/ui/Alert';
import Button from '../../components/ui/Button';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import GuidedTour from '../../components/ui/GuidedTour';
import { TextField, TextArea } from '../../components/ui/Field';
import usePoll from '../../hooks/usePoll';
import { startTour } from '../../lib/tour';

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

const DEFAULT_FORM = {
    culprit_points: 40,
    max: 100,
    weapon: { label: '¿Con qué?', hint: 'El método o el arma: qué usó para hacerlo.', points: 30 },
    motive: {
        label: '¿Por qué?',
        hint: 'Tu razonamiento. Lo leerán los demás cuando se revele la solución.',
        points: 30,
    },
    questions: [],
};

/** "(15 pts)" next to a label, so it is clear where the effort pays off. */
function withPoints(label, points) {
    return points > 0 ? `${label} (${points} pts)` : label;
}

/**
 * How the accusation works, written for someone who has never done one.
 *
 * Players kept asking "what do I put in each box?" — so this says what each
 * part is for, what is optional, how it is marked and what a good answer
 * looks like. It stays on the page (a tour is shown once and forgotten) and
 * the guided tour below says the same in four short steps.
 */
function HowItWorks({ form }) {
    const extras = form.questions?.length ?? 0;

    return (
        <Card as="section" className="mb-5">
            <CardHeader
                title="Cómo funciona tu acusación"
                description="Léelo una vez: te ahorra preguntarte qué escribir en cada casilla."
                actions={
                    <Button variant="secondary" size="sm" onClick={() => startTour('accusation')}>
                        Ver el tutorial
                    </Button>
                }
            />

            <ol className="space-y-3 text-sm text-ink-muted">
                <li>
                    <strong className="text-ink">1. Elige a una persona</strong> ({form.culprit_points}{' '}
                    pts). Es la única parte que se corrige exacta: o acertaste, o no. Es lo que
                    decide quién gana.
                </li>
                <li>
                    <strong className="text-ink">2. Cuenta cómo lo hizo y por qué</strong> (
                    {form.weapon.points + form.motive.points} pts). Obligatorio. No hace falta que
                    sea largo: una o dos frases claras con lo esencial valen más que un párrafo
                    que da vueltas.
                </li>
                {extras > 0 && (
                    <li>
                        <strong className="text-ink">3. Responde las preguntas del caso</strong> (
                        {form.max - form.culprit_points - form.weapon.points - form.motive.points}{' '}
                        pts en total). Son <strong className="text-ink">opcionales</strong>: si no
                        sabes una, déjala en blanco y no pasa nada, simplemente no suma. Sirven
                        para desempatar cuando varias personas aciertan a quien culpar.
                    </li>
                )}
            </ol>

            <div className="mt-4 rounded-card border border-line bg-surface-sunken p-4 text-sm text-ink-muted">
                <p className="font-medium text-ink">Cómo se califica</p>
                <ul className="mt-2 list-disc space-y-1.5 pl-5">
                    <li>
                        Al revelarse la solución, cada respuesta se compara con la solución oficial.
                        Cuenta lo que dices, no cómo lo escribes: sinónimos y faltas de ortografía no
                        restan.
                    </li>
                    <li>
                        Puedes ganar puntos parciales si aciertas una parte de la idea y fallas otra.
                    </li>
                    <li>
                        Responde con lo que <em>crees que ocurrió</em>, sin enumerar varias opciones
                        «por si acaso»: eso no suma.
                    </li>
                    <li>
                        Puedes cambiar tu acusación cuantas veces quieras hasta que se revele la
                        solución.
                    </li>
                    <li>
                        <strong className="text-ink">No es lo mismo que interrogar.</strong>{' '}
                        Interrogar y confrontar sirven para <em>averiguar</em> (cada pregunta o
                        confrontación gasta las preguntas de esa persona). La acusación es tu
                        respuesta final: no gasta preguntas y la envía cada jugador por su cuenta.
                    </li>
                </ul>
            </div>
        </Card>
    );
}

/** The same explanation as a first-visit tour, in four short steps. */
function accusationTour(form) {
    const extras = form.questions?.length ?? 0;

    return [
        {
            title: 'Tu acusación final',
            body: 'Es el último paso: dices quién fue, cómo y por qué. No es lo mismo que interrogar o confrontar (eso es para averiguar y gasta las preguntas de cada persona): esta es tu respuesta final y no gasta preguntas. Puedes cambiarla hasta que se revele la solución.',
        },
        {
            target: '[data-tour="accusation-suspect"]',
            optional: true,
            title: '1. Elige a una persona',
            body: `Es la parte más importante (${form.culprit_points} puntos) y se corrige exacta. Quien acierte a la persona queda por delante de quien no.`,
        },
        {
            target: '[data-tour="accusation-basics"]',
            optional: true,
            title: '2. Cómo y por qué',
            body: 'Obligatorias. Escribe lo esencial en una o dos frases claras: qué hizo y qué lo llevó a hacerlo.',
        },
        {
            target: '[data-tour="accusation-extras"]',
            optional: true,
            title: extras > 0 ? '3. Preguntas del caso' : 'Calificación',
            body:
                extras > 0
                    ? 'Son opcionales: si no sabes una, déjala en blanco. Cada respuesta que aciertes suma puntos y sirve de desempate entre quienes acertaron al culpable.'
                    : 'Al revelarse la solución, tu método y tu motivo se comparan con la oficial. Cuenta lo que dices, no cómo lo escribes.',
        },
    ];
}

export default function Accusation({
    player,
    game,
    unlocked,
    locked,
    suspects,
    pending,
    form = DEFAULT_FORM,
}) {
    const questions = form.questions ?? [];

    const { data, setData, post, processing, errors } = useForm({
        suspect_slug: player.accusation?.suspect_slug || '',
        weapon: player.accusation?.weapon || '',
        motive: player.accusation?.motive || '',
        answers: Object.fromEntries(
            questions.map((question) => [question.key, player.accusation?.answers?.[question.key] || ''])
        ),
    });

    function setAnswer(key, value) {
        setData('answers', { ...data.answers, [key]: value });
    }

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

                    <HowItWorks form={form} />

                    <form onSubmit={submit} className="space-y-6">
                        <div data-tour="accusation-suspect">
                            <SuspectChoice
                                suspects={suspects}
                                value={data.suspect_slug}
                                onChange={(slug) => setData('suspect_slug', slug)}
                                error={errors.suspect_slug}
                            />
                        </div>

                        <div className="space-y-6" data-tour="accusation-basics">
                            <TextField
                                id="weapon"
                                label={withPoints(form.weapon.label, form.weapon.points)}
                                value={data.weapon}
                                onChange={(value) => setData('weapon', value)}
                                error={errors.weapon}
                                hint={form.weapon.hint}
                                required
                            />

                            <TextArea
                                id="motive"
                                label={withPoints(form.motive.label, form.motive.points)}
                                rows={5}
                                value={data.motive}
                                onChange={(value) => setData('motive', value)}
                                error={errors.motive}
                                hint={form.motive.hint}
                                required
                            />
                        </div>

                        {/* The case's own questions. Optional: a blank one just
                            scores zero. Each is graded against the solution once
                            it is revealed, and the score breaks ties between
                            players who named the same culprit. */}
                        {questions.length > 0 && (
                            <div className="space-y-6" data-tour="accusation-extras">
                                <div className="border-t border-line pt-5">
                                    <p className="text-sm font-medium text-ink">
                                        Preguntas del caso{' '}
                                        <span className="font-normal text-ink-subtle">
                                            (opcionales)
                                        </span>
                                    </p>
                                    <p className="mt-1 text-xs text-ink-subtle">
                                        Si no sabes una respuesta, déjala en blanco. Suman puntos y
                                        desempatan entre quienes acierten al culpable.
                                    </p>
                                </div>

                                {questions.map((question) => (
                                    <TextArea
                                        key={question.key}
                                        id={`answers-${question.key}`}
                                        label={withPoints(question.label, question.points)}
                                        rows={3}
                                        value={data.answers[question.key] ?? ''}
                                        onChange={(value) => setAnswer(question.key, value)}
                                        error={errors[`answers.${question.key}`]}
                                        hint={question.hint ?? undefined}
                                    />
                                ))}
                            </div>
                        )}

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

            <GuidedTour
                id="accusation"
                steps={accusationTour(form)}
                enabled={unlocked && !locked && !game.ending_revealed_at}
            />
        </PlayerLayout>
    );
}
