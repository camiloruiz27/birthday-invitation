import { Head } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import GalleryGrid from '../../components/player/GalleryGrid';

function Verdict({ correct }) {
    if (correct === null) {
        return (
            <span className="case-stamp border-2 border-paper-line px-2 py-0.5 text-[10px] text-paper-muted">
                Sin acusar
            </span>
        );
    }

    return (
        <span
            className={`case-stamp border-2 px-2 py-0.5 text-[10px] ${
                correct
                    ? 'border-paper-ink bg-paper-ink text-paper'
                    : 'border-red-800 text-red-800'
            }`}
        >
            {correct ? 'Acertó' : 'Falló'}
        </span>
    );
}

export default function Solution({
    player,
    game,
    solution,
    scoreboard,
    correctCount,
    revealedBy,
}) {
    const you = scoreboard.find((row) => row.is_you);
    const total = scoreboard.length;

    return (
        <PlayerLayout
            player={player}
            game={game}
            section="solution"
            kicker="Caso cerrado"
            title="La solución"
        >
            <Head title="La solución" />

            {/* The culprit, first and unmissable. */}
            <section className="border-2 border-paper-ink bg-paper-ink p-4 text-paper sm:p-6">
                <p className="case-stamp text-[10px] text-paper-accent">Culpable</p>

                <div className="mt-3 flex items-center gap-4">
                    {solution.culprit?.photo_url && (
                        <img
                            src={solution.culprit.photo_url}
                            alt={solution.culprit.name}
                            className="h-20 w-20 shrink-0 rounded border-2 border-paper object-cover"
                        />
                    )}
                    <div className="min-w-0">
                        <p className="text-xl font-bold leading-tight">{solution.culprit?.name}</p>
                        {solution.headline && (
                            <p className="mt-1.5 text-sm text-paper-accent">{solution.headline}</p>
                        )}
                    </div>
                </div>

                <dl className="mt-5 grid gap-4 border-t border-paper-line pt-4 sm:grid-cols-2">
                    <div>
                        <dt className="case-stamp text-[10px] text-paper-accent">Con qué</dt>
                        <dd className="mt-1 text-sm">{solution.method}</dd>
                    </div>
                    <div>
                        <dt className="case-stamp text-[10px] text-paper-accent">Por qué</dt>
                        <dd className="mt-1 text-sm">{solution.motive}</dd>
                    </div>
                </dl>
            </section>

            {/* How this player did. */}
            {you && (
                <section className="mt-5 border-2 border-paper-ink bg-paper-raised p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <p className="case-stamp text-xs">Tu acusación</p>
                        <Verdict correct={you.correct} />
                    </div>

                    {you.suspect_name ? (
                        <p className="mt-2 text-sm">
                            Acusaste a <strong>{you.suspect_name}</strong>
                            {you.weapon && <> con <strong>{you.weapon}</strong></>}
                            {you.motive && <>, por: {you.motive}</>}
                        </p>
                    ) : (
                        <p className="mt-2 text-sm text-paper-muted">
                            No llegaste a enviar tu acusación.
                        </p>
                    )}
                </section>
            )}

            {solution.key_evidence?.length > 0 && (
                <section className="mt-5 border-2 border-paper-ink bg-paper-raised p-4">
                    <p className="case-stamp text-xs">Lo que lo señalaba</p>
                    <ul className="mt-3 space-y-2">
                        {solution.key_evidence.map((item, index) => (
                            <li key={index} className="flex gap-2.5 text-sm">
                                <span aria-hidden="true" className="text-paper-muted">
                                    —
                                </span>
                                {item}
                            </li>
                        ))}
                    </ul>
                </section>
            )}

            {solution.body_html && (
                <section className="mt-5 border-2 border-paper-ink bg-paper-raised">
                    <div
                        className="case-prose px-4 py-4 text-[15px]"
                        dangerouslySetInnerHTML={{ __html: solution.body_html }}
                    />
                    <GalleryGrid images={solution.gallery} />
                </section>
            )}

            {/* The table's scoreboard. Names only — never anyone else's link. */}
            <section className="mt-5 border-2 border-paper-ink bg-paper-raised p-4">
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                    <p className="case-stamp text-xs">Quién acertó</p>
                    <p className="tabular text-sm font-bold">
                        {correctCount} de {total}
                    </p>
                </div>

                <ul className="mt-3 divide-y divide-dashed divide-paper-line">
                    {scoreboard.map((row, index) => (
                        <li key={index} className="flex flex-wrap items-center justify-between gap-2 py-2.5">
                            <span className="min-w-0">
                                <span className={`text-sm ${row.is_you ? 'font-bold' : ''}`}>
                                    {row.player_name}
                                    {row.is_you && ' (tú)'}
                                </span>
                                {row.suspect_name && (
                                    <span className="block text-xs text-paper-muted">
                                        acusó a {row.suspect_name}
                                    </span>
                                )}
                            </span>
                            <Verdict correct={row.correct} />
                        </li>
                    ))}
                </ul>

                {revealedBy === 'gm' && (
                    <p className="mt-4 border-t border-dashed border-paper-line pt-3 text-xs text-paper-muted">
                        El Game Master cerró el caso antes de que todos acusaran.
                    </p>
                )}
            </section>
        </PlayerLayout>
    );
}
