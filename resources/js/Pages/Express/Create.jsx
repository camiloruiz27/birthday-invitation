import { Head, useForm } from '@inertiajs/react';
import ExpressLayout, { ExpressContainer } from '../../Layouts/ExpressLayout';
import Alert from '../../components/ui/Alert';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import EmptyState from '../../components/ui/EmptyState';
import PlayerFields, { PLAYER_FIELD_DEFAULTS, useRefusalNotice } from '../../components/express/PlayerFields';

/** The cover when the case has one; a quiet gradient (with the genre) when not. */
function Cover({ hero, genre, title }) {
    return (
        <span className="relative block aspect-4/5 w-20 shrink-0 overflow-hidden rounded-control border border-line-strong bg-linear-to-br from-accent-dim via-surface-raised to-surface-sunken">
            {hero ? (
                <img src={hero} alt="" loading="lazy" className="h-full w-full object-cover" />
            ) : (
                <span
                    aria-hidden="true"
                    className="absolute inset-0 flex items-end p-2 font-mono text-[10px] uppercase leading-tight tracking-wider text-accent-strong"
                >
                    {genre || title}
                </span>
            )}
        </span>
    );
}

function CaseChoice({ cases, value, onChange, suggested, error }) {
    return (
        <fieldset>
            <legend className="text-sm font-medium text-ink">Elige el caso</legend>

            <div className="mt-2.5 space-y-3">
                {cases.map((item) => {
                    const id = `case-${item.slug}`;
                    const selected = value === item.slug;

                    return (
                        <label key={item.slug} htmlFor={id} className="block cursor-pointer">
                            <input
                                id={id}
                                type="radio"
                                name="case_slug"
                                value={item.slug}
                                checked={selected}
                                onChange={() => onChange(item.slug)}
                                className="peer sr-only"
                            />
                            <span
                                className={`flex gap-3 rounded-card border bg-surface-raised p-3 transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent ${
                                    selected ? 'border-accent bg-accent-dim/30' : 'border-line hover:border-line-strong'
                                }`}
                            >
                                <Cover hero={item.hero} genre={item.genre} title={item.title} />

                                <span className="min-w-0 flex-1">
                                    <span className="flex flex-wrap items-center gap-1.5">
                                        {item.slug === suggested && <Badge tone="accent">Sugerido</Badge>}
                                        {item.genre && (
                                            <span className="case-stamp text-[10px] text-ink-subtle">{item.genre}</span>
                                        )}
                                    </span>
                                    <span className="mt-1 block font-semibold leading-snug text-ink">{item.title}</span>
                                    {item.tagline && (
                                        <span className="mt-0.5 block text-sm text-ink-muted">{item.tagline}</span>
                                    )}
                                    {item.hook && (
                                        <span className="mt-1.5 line-clamp-3 block text-xs leading-relaxed text-ink-subtle">
                                            {item.hook}
                                        </span>
                                    )}
                                </span>

                                <span
                                    aria-hidden="true"
                                    className={`mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border ${
                                        selected ? 'border-accent' : 'border-line-strong'
                                    }`}
                                >
                                    {selected && <span className="h-2.5 w-2.5 rounded-full bg-accent" />}
                                </span>
                            </span>
                        </label>
                    );
                })}
            </div>

            {error && <p className="mt-1.5 text-xs font-medium text-danger-strong">{error}</p>}
        </fieldset>
    );
}

export default function Create({ event, cases = [], suggested = null, consent = null, errors: serverErrors = {} }) {
    const suggestedSlug = typeof suggested === 'string' ? suggested : (suggested?.slug ?? null);
    const initialCase = cases.some((item) => item.slug === suggestedSlug)
        ? suggestedSlug
        : (cases[0]?.slug ?? '');

    const { data, setData, post, processing, errors, transform } = useForm({
        ...PLAYER_FIELD_DEFAULTS,
        // A form post carries no query string: the event travels in the body.
        event: event?.slug ?? '',
        case_slug: initialCase,
    });
    const [refusal, clearRefusal] = useRefusalNotice();

    // The form's own errors win; whatever the server sent as a prop fills in.
    const allErrors = { ...serverErrors, ...errors };

    transform((payload) => ({
        ...payload,
        contact: payload.contact_type === 'none' ? '' : payload.contact.trim(),
        nickname: payload.nickname.trim(),
    }));

    function submit(event) {
        event.preventDefault();
        clearRefusal();
        post(route('express.rooms.store'));
    }

    return (
        <ExpressLayout brandHref={route('express.home')}>
            <Head title="Crear una sala" />

            <ExpressContainer className="py-8">
                {event?.name && <p className="case-stamp text-[10px] text-accent">{event.name}</p>}
                <h1 className="mt-1 font-display text-3xl font-semibold tracking-tight text-ink">Crear una sala</h1>
                <p className="mt-2 text-sm text-ink-muted">
                    Tú serás el anfitrión y también juegas; invita de 1 a 4 amigos.
                </p>

                {cases.length === 0 ? (
                    <EmptyState
                        className="mt-8"
                        title="No hay casos disponibles ahora"
                        description="Pásate de nuevo por el stand en unos minutos."
                    />
                ) : (
                    <form onSubmit={submit} className="relative mt-6 space-y-6">
                        <Alert variant="error">{allErrors.website || refusal}</Alert>

                        <CaseChoice
                            cases={cases}
                            value={data.case_slug}
                            onChange={(slug) => setData('case_slug', slug)}
                            suggested={suggestedSlug}
                            error={allErrors.case_slug}
                        />

                        <PlayerFields data={data} setData={setData} errors={allErrors} consent={consent} />

                        <Button type="submit" size="lg" fullWidth loading={processing} disabled={!data.case_slug}>
                            Crear sala
                        </Button>
                    </form>
                )}
            </ExpressContainer>
        </ExpressLayout>
    );
}
