import Container from '../ui/Container';

/**
 * Reading layout shared by the three legal documents (privacy, terms,
 * cookies): title, version and effective date, an anchored index, then the
 * numbered sections.
 *
 * Prose styling is applied here with child selectors so each document is just
 * <p>, <ul> and <strong> — the long texts stay readable as source and a new
 * paragraph never needs its own classes.
 */

function formatDate(value) {
    if (!value) return '';

    const date = new Date(`${value}T00:00:00`);
    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric' });
}

/**
 * A value that comes from config/legal.php. An empty one is shown as a
 * visible marker rather than silently dropped, so a page can never go live
 * with the controller's identity missing and nobody noticing.
 */
export function Val({ value, label }) {
    if (value) return <>{value}</>;

    return (
        <mark className="rounded-sm border border-warning-strong bg-surface-sunken px-1 font-medium text-warning-strong">
            [PENDIENTE: {label}]
        </mark>
    );
}

export function Mail({ value }) {
    if (!value) return <Val value="" label="correo de contacto" />;

    return (
        <a href={`mailto:${value}`} className="font-medium text-accent underline underline-offset-2 hover:text-accent-strong">
            {value}
        </a>
    );
}

/**
 * Who is responsible, as a phrase to drop INSIDE a paragraph.
 *
 * Ley 1581 (data policy) and Ley 1480 art. 50 (online sale) require this
 * identification, so it stays on the page — but it is deliberately plain
 * running text: same size, weight and colour as the sentence around it, no
 * card, no list, no bold, and the email is not a styled link. Nothing about it
 * is set apart for a visitor, or a scraper, to find at a glance.
 *
 * The address is omitted when LEGAL_ADDRESS is empty rather than shown as a
 * [PENDIENTE] marker; it is the one value the owner may legitimately choose to
 * keep out of the text (Ley 1480 art. 50 asks for a judicial-notification
 * address, which is the owner's call to supply).
 */
export function ControllerIdentity({ legal }) {
    return (
        <>
            <Val value={legal.entity_name} label="nombre o razón social" />, identificado con{' '}
            <Val value={legal.entity_id} label="NIT o cédula" />
            {legal.address && (
                <>
                    , con domicilio en {legal.address}
                    {legal.city ? `, ${legal.city}` : ''}, Colombia
                </>
            )}
            , correo electrónico{' '}
            {legal.email ? legal.email : <Val value="" label="correo de contacto" />} y teléfono{' '}
            <Val value={legal.phone} label="teléfono" />
        </>
    );
}

export default function LegalDocument({ kicker, title, legal, intro, sections }) {
    return (
        <article className="py-12 sm:py-16">
            <Container width="prose">
                <p className="text-xs font-medium uppercase tracking-widest text-accent">{kicker}</p>
                <h1 className="mt-3 font-display text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                    {title}
                </h1>
                <p className="mt-3 text-sm text-ink-subtle">
                    Versión {legal.version} · Vigente desde el {formatDate(legal.updated_at)}
                </p>

                {intro && (
                    <div className="mt-6 text-base leading-relaxed text-ink-muted [&_p+p]:mt-3">
                        {intro}
                    </div>
                )}

                <nav aria-label="Contenido" className="mt-8 rounded-card border border-line bg-surface-raised p-5">
                    <p className="text-sm font-medium text-ink">Contenido</p>
                    <ol className="mt-3 grid gap-x-6 gap-y-1.5 text-sm sm:grid-cols-2">
                        {sections.map((section, index) => (
                            <li key={section.id} className="flex gap-2">
                                <span className="tabular w-5 shrink-0 text-ink-subtle">{index + 1}.</span>
                                <a href={`#${section.id}`} className="text-ink-muted hover:text-ink">
                                    {section.title}
                                </a>
                            </li>
                        ))}
                    </ol>
                </nav>

                <div className="mt-10 space-y-10">
                    {sections.map((section, index) => (
                        <section key={section.id} id={section.id} className="scroll-mt-28">
                            <h2 className="font-display text-xl font-semibold text-ink">
                                {index + 1}. {section.title}
                            </h2>
                            <div
                                className={[
                                    'mt-3 text-[0.95rem] leading-relaxed text-ink-muted',
                                    '[&_p+p]:mt-3 [&_p+ul]:mt-3 [&_ul+p]:mt-3',
                                    '[&_ul]:list-disc [&_ul]:space-y-1.5 [&_ul]:pl-5',
                                    '[&_strong]:font-semibold [&_strong]:text-ink',
                                    '[&_a]:font-medium [&_a]:text-accent [&_a]:underline [&_a]:underline-offset-2 hover:[&_a]:text-accent-strong',
                                ].join(' ')}
                            >
                                {section.body}
                            </div>
                        </section>
                    ))}
                </div>

                <p className="mt-12 border-t border-line pt-6 text-xs text-ink-subtle">
                    Versión {legal.version} · Actualizado el {formatDate(legal.updated_at)}.
                </p>
            </Container>
        </article>
    );
}
