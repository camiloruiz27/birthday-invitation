import { useForm } from '@inertiajs/react';
import ReactStarsRating from 'react-awesome-stars-rating';
import Card from '../ui/Card';
import Button from '../ui/Button';

function labelFor(halfSteps) {
    const score = halfSteps / 2;

    return `${String(score).replace('.', ',')} ${score === 1 ? 'estrella' : 'estrellas'}`;
}

function StarPicker({ halfSteps, disabled, onChange }) {
    const value = halfSteps ? halfSteps / 2 : 0;

    return (
        <div className="rounded-control border border-line bg-surface-raised px-4 py-5 sm:px-6">
            <div className="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                <div>
                    <p className="text-sm font-medium text-ink">Elige de 1 a 5 estrellas</p>
                    <p className="mt-1 text-xs leading-relaxed text-ink-subtle">
                        Toca la mitad izquierda para sumar medio punto o la derecha para completar la estrella.
                    </p>
                </div>
                <p
                    aria-live="polite"
                    className={`rounded-full border px-3 py-1.5 text-sm font-semibold tabular-nums ${
                        value
                            ? 'border-accent bg-accent-dim text-ink'
                            : 'border-line text-ink-subtle'
                    }`}
                >
                    {value ? `${String(value).replace('.', ',')} de 5` : 'Sin seleccionar'}
                </p>
            </div>

            <div className="mt-5 flex justify-center sm:justify-start">
                <ReactStarsRating
                    value={value}
                    onChange={(nextValue) => onChange(Math.round(nextValue * 2))}
                    count={5}
                    isHalf
                    size={46}
                    starGap={8}
                    disabled={disabled}
                    primaryColor="#a5af5d"
                    secondaryColor="#455567"
                    hoverColor="#c9d577"
                    ariaLabel="Tu puntuación del caso"
                    getValueText={(nextValue) => `${String(nextValue).replace('.', ',')} de 5 estrellas`}
                    style={{ '--stars-rating-focus-color': '#c9d577' }}
                />
            </div>
            <div aria-hidden="true" className="mt-2 flex justify-between px-1 text-xs text-ink-subtle sm:max-w-[262px]">
                <span>1</span>
                <span>2</span>
                <span>3</span>
                <span>4</span>
                <span>5</span>
            </div>
        </div>
    );
}

/** A private, one-time rating card tied to this player's own access token. */
export default function CaseRating({ playerToken, rating }) {
    const form = useForm({
        rating_half_steps: rating?.rating_half_steps ?? null,
        tags: rating?.tags ?? [],
    });

    if (!rating) return null;

    const submitted = rating.submitted;
    const selectedTags = form.data.tags;

    function selectScore(value) {
        if (!submitted) {
            form.setData('rating_half_steps', value);
            form.clearErrors('rating_half_steps');
        }
    }

    function toggleTag(tag) {
        if (submitted) return;

        if (selectedTags.includes(tag)) {
            form.setData('tags', selectedTags.filter((item) => item !== tag));
            return;
        }

        if (selectedTags.length < 3) {
            form.setData('tags', [...selectedTags, tag]);
        }
    }

    function submit(event) {
        event.preventDefault();
        form.post(route('immersion.player.rating.store', playerToken), { preserveScroll: true });
    }

    return (
        <Card as="section" className="mt-5 border-accent-dim bg-accent-dim/20">
            <p className="case-stamp text-[10px] text-accent-strong">Valoración del caso</p>
            <h2 className="mt-2 font-display text-2xl font-semibold tracking-tight text-ink">
                {submitted ? 'Gracias por tu valoración' : '¿Cómo te pareció este caso?'}
            </h2>
            <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                {submitted
                    ? `Registraste ${labelFor(rating.rating_half_steps)}.`
                    : 'Tu opinión ayuda a mejorar los próximos misterios. Solo se registra una vez por participante.'}
            </p>

            <form onSubmit={submit} className="mt-5 space-y-5">
                <fieldset disabled={submitted || form.processing}>
                    <legend className="mb-3 text-sm font-medium text-ink">Tu puntuación</legend>
                    <StarPicker
                        halfSteps={form.data.rating_half_steps}
                        disabled={submitted || form.processing}
                        onChange={selectScore}
                    />
                    {form.errors.rating_half_steps && <p className="mt-2 text-xs font-medium text-danger-strong">{form.errors.rating_half_steps}</p>}
                </fieldset>

                <fieldset disabled={submitted || form.processing}>
                    <legend className="text-sm font-medium text-ink">
                        ¿Qué destacarías? <span className="font-normal text-ink-subtle">Opcional · máximo 3.</span>
                    </legend>
                    <div className="mt-3 flex flex-wrap gap-2">
                        {Object.entries(rating.tag_options).map(([key, label]) => {
                            const active = selectedTags.includes(key);
                            const unavailable = !active && selectedTags.length >= 3;

                            return (
                                <button
                                    key={key}
                                    type="button"
                                    aria-pressed={active}
                                    disabled={unavailable}
                                    onClick={() => toggleTag(key)}
                                    className={`min-h-10 rounded-full border px-3 text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent ${
                                        active ? 'border-accent bg-accent-dim text-ink' : 'border-line text-ink-muted hover:border-line-strong hover:text-ink'
                                    }`}
                                >
                                    {active && <span aria-hidden="true" className="mr-1">✓</span>}
                                    {label}
                                </button>
                            );
                        })}
                    </div>
                    {form.errors.tags && <p className="mt-2 text-xs font-medium text-danger-strong">{form.errors.tags}</p>}
                </fieldset>

                {!submitted && (
                    <Button type="submit" variant="secondary" loading={form.processing} disabled={!form.data.rating_half_steps}>
                        {form.processing ? 'Guardando…' : 'Enviar valoración'}
                    </Button>
                )}
            </form>
        </Card>
    );
}
