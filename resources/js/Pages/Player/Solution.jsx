import { Head } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import Badge from '../../components/ui/Badge';
import Card from '../../components/ui/Card';
import Tabs from '../../components/ui/Tabs';
import CaseDocument from '../../components/player/CaseDocument';
import GalleryGrid from '../../components/player/GalleryGrid';
import usePoll from '../../hooks/usePoll';

function Verdict({ correct }) {
    if (correct === null) {
        return <Badge>Sin acusar</Badge>;
    }

    return (
        <Badge tone={correct ? 'success' : 'danger'}>{correct ? 'Acertó' : 'Falló'}</Badge>
    );
}

/**
 * The message from the person this player accused.
 *
 * It sits with their own verdict rather than in the reconstruction: it is
 * the personal answer to what they wrote, and it lands hardest while their
 * accusation is still on screen.
 */
function Epilogue({ epilogue }) {
    if (epilogue.status === 'pending') {
        return (
            <Card className="mt-5 border-dashed text-center">
                <p className="case-stamp text-[10px] text-accent">
                    {epilogue.suspect_name} te está escribiendo
                </p>
                <p className="mt-2 text-sm text-ink-muted">
                    Su mensaje llegará a tu correo en un momento, y también aparecerá aquí.
                </p>
            </Card>
        );
    }

    if (epilogue.status !== 'ready' || !epilogue.body) {
        return null;
    }

    return (
        <Card as="section" className="mt-5">
            <p className="case-stamp text-[10px] text-accent">
                Un mensaje de {epilogue.suspect_name}
            </p>

            <div className="mt-3 border-l-2 border-accent pl-4">
                {epilogue.body.split(/\n{2,}/).map((paragraph, index) => (
                    <p key={index} className="mt-3 leading-relaxed text-ink first:mt-0">
                        {paragraph}
                    </p>
                ))}
            </div>
        </Card>
    );
}

function Culprit({ solution }) {
    return (
        <section className="overflow-hidden rounded-hero border border-accent-dim bg-accent-dim/30">
            <div className="p-5 sm:p-8">
                <p className="case-stamp text-[10px] text-accent-strong">Culpable</p>

                <div className="mt-4 flex flex-wrap items-center gap-5">
                    {solution.culprit?.photo_url && (
                        <img
                            src={solution.culprit.photo_url}
                            alt=""
                            className="h-24 w-24 shrink-0 rounded-card border border-accent-dim object-cover sm:h-28 sm:w-28"
                        />
                    )}
                    <div className="min-w-0">
                        <h2 className="font-display text-3xl font-semibold leading-tight tracking-tight text-ink sm:text-5xl">
                            {solution.culprit?.name}
                        </h2>
                        {solution.headline && (
                            <p className="mt-2 text-base text-ink-muted">{solution.headline}</p>
                        )}
                    </div>
                </div>

                <dl className="mt-7 grid gap-5 border-t border-accent-dim pt-5 sm:grid-cols-2">
                    <div>
                        <dt className="case-stamp text-[10px] text-accent-strong">Con qué</dt>
                        <dd className="mt-1.5 text-sm text-ink">{solution.method}</dd>
                    </div>
                    <div>
                        <dt className="case-stamp text-[10px] text-accent-strong">Por qué</dt>
                        <dd className="mt-1.5 text-sm text-ink">{solution.motive}</dd>
                    </div>
                </dl>
            </div>
        </section>
    );
}

export default function Solution({
    player,
    game,
    solution,
    scoreboard,
    correctCount,
    revealedBy,
    epilogue,
}) {
    const you = scoreboard.find((row) => row.is_you);
    const total = scoreboard.length;

    // The epilogue is written on the queue after the reveal, so a player who
    // opens this page immediately would otherwise have to refresh by hand.
    usePoll(['epilogue'], {
        interval: 8000,
        enabled: epilogue?.status === 'pending',
    });

    const verdict = (
        <>
            <Culprit solution={solution} />

            {you && (
                <Card as="section" className="mt-5">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <p className="case-stamp text-[10px] text-ink-subtle">Tu acusación</p>
                        <Verdict correct={you.correct} />
                    </div>

                    {you.suspect_name ? (
                        <p className="mt-3 text-sm text-ink-muted">
                            Acusaste a <strong className="text-ink">{you.suspect_name}</strong>
                            {you.weapon && (
                                <>
                                    {' '}
                                    con <strong className="text-ink">{you.weapon}</strong>
                                </>
                            )}
                            {you.motive && <>, por: {you.motive}</>}
                        </p>
                    ) : (
                        <p className="mt-3 text-sm text-ink-muted">
                            No llegaste a enviar tu acusación.
                        </p>
                    )}
                </Card>
            )}

            {epilogue && <Epilogue epilogue={epilogue} />}

            {solution.key_evidence?.length > 0 && (
                <Card as="section" className="mt-5">
                    <p className="case-stamp text-[10px] text-ink-subtle">Lo que lo señalaba</p>
                    <ul className="mt-3 space-y-2.5">
                        {solution.key_evidence.map((item, index) => (
                            <li key={index} className="flex gap-2.5 text-sm text-ink-muted">
                                <span aria-hidden="true" className="text-accent">
                                    —
                                </span>
                                {item}
                            </li>
                        ))}
                    </ul>
                </Card>
            )}
        </>
    );

    const reconstruction = (
        <>
            {/* initial="all", against the automatic policy: this is the
                ending, written to be read straight through and out loud at
                the table. Collapsing the climax would be the one place the
                progressive-disclosure rule makes things worse. */}
            <CaseDocument html={solution.body_html} id="solucion" initial="all" toc />
            <GalleryGrid images={solution.gallery} className="mt-6" />
        </>
    );

    /* Names only — never anyone else's access link. */
    const board = (
        <Card as="section">
            <div className="flex flex-wrap items-baseline justify-between gap-2">
                <p className="case-stamp text-[10px] text-ink-subtle">Quién acertó</p>
                <p className="tabular text-sm font-semibold text-ink">
                    {correctCount} de {total}
                </p>
            </div>

            <ul className="mt-4 divide-y divide-line">
                {scoreboard.map((row, index) => (
                    <li
                        key={index}
                        className="flex flex-wrap items-center justify-between gap-2 py-3"
                    >
                        <span className="min-w-0">
                            <span
                                className={`text-sm ${
                                    row.is_you ? 'font-semibold text-ink' : 'text-ink-muted'
                                }`}
                            >
                                {row.player_name}
                                {row.is_you && ' (tú)'}
                            </span>
                            {row.suspect_name && (
                                <span className="block text-xs text-ink-subtle">
                                    acusó a {row.suspect_name}
                                </span>
                            )}
                        </span>
                        <Verdict correct={row.correct} />
                    </li>
                ))}
            </ul>

            {revealedBy === 'gm' && (
                <p className="mt-4 border-t border-line pt-3 text-xs text-ink-subtle">
                    El Game Master cerró el caso antes de que todos acusaran.
                </p>
            )}
        </Card>
    );

    return (
        <PlayerLayout
            player={player}
            game={game}
            section="solution"
            kicker="Caso cerrado"
            title="La solución"
        >
            <Head title="La solución" />

            {/* Three acts instead of six stacked sections — and the 2,000-word
                reconstruction stops being the thing you scroll through to
                reach the scoreboard. */}
            <Tabs
                label="La solución"
                scrollable
                tabs={[
                    { key: 'verdict', label: 'Veredicto', content: verdict },
                    ...(solution.body_html
                        ? [
                              {
                                  key: 'reconstruction',
                                  label: 'Reconstrucción',
                                  content: reconstruction,
                              },
                          ]
                        : []),
                    { key: 'board', label: 'Marcador', badge: `${correctCount}/${total}`, content: board },
                ]}
            />
        </PlayerLayout>
    );
}
