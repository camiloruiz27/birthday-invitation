/**
 * The ficha of a person of interest, shown once their interrogation is over.
 *
 * It replaces the "declaración oficial", which was the suspect's whole source
 * file — the same text the AI plays the character from, motive and lies
 * included. The ficha is an allowlist the case author writes for the
 * investigators (`public` in the case manifest): who the person is, what they
 * claim, and a few things already known. Nothing here says who did it.
 */
export default function SuspectFicha({ suspect, ficha }) {
    if (!ficha) {
        return null;
    }

    const hasFacts = ficha.facts?.length > 0;

    return (
        <section
            aria-label={`Ficha de ${suspect.name}`}
            className="rounded-card border border-line bg-surface-raised p-5"
        >
            <div className="flex items-center gap-4">
                {suspect.photo_url && (
                    <img
                        src={suspect.photo_url}
                        alt=""
                        className="h-16 w-16 shrink-0 rounded-control border border-line object-cover"
                    />
                )}
                <div className="min-w-0">
                    <p className="case-stamp text-[10px] text-accent">Ficha</p>
                    <h3 className="truncate font-display text-lg font-semibold text-ink">
                        {suspect.name}
                    </h3>
                    <p className="truncate text-xs text-ink-muted">
                        {[suspect.connection, ficha.age].filter(Boolean).join(' · ')}
                    </p>
                </div>
            </div>

            {ficha.profile && <p className="mt-4 text-sm leading-relaxed text-ink">{ficha.profile}</p>}

            {ficha.alibi && (
                <div className="mt-4">
                    <p className="case-stamp text-[10px] text-ink-subtle">Dónde dice haber estado</p>
                    <p className="mt-1 text-sm text-ink-muted">{ficha.alibi}</p>
                </div>
            )}

            {hasFacts && (
                <div className="mt-4">
                    <p className="case-stamp text-[10px] text-ink-subtle">Lo que ya se sabe</p>
                    <ul className="mt-1 list-disc space-y-1 pl-5 text-sm text-ink-muted">
                        {ficha.facts.map((fact) => (
                            <li key={fact}>{fact}</li>
                        ))}
                    </ul>
                </div>
            )}
        </section>
    );
}
