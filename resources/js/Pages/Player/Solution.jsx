import { Head } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import Badge from '../../components/ui/Badge';
import Card from '../../components/ui/Card';
import Tabs from '../../components/ui/Tabs';
import CaseDocument from '../../components/player/CaseDocument';
import GalleryGrid from '../../components/player/GalleryGrid';
import usePoll from '../../hooks/usePoll';
import useSessionState from '../../hooks/useSessionState';

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

/**
 * How this player's accusation was graded, criterion by criterion. Grading
 * runs on the queue right after the reveal, so it starts as a placeholder.
 */
function Grading({ row, maxScore }) {
    if (row.grading_status === 'pending') {
        return (
            <p className="mt-4 border-t border-line pt-3 text-sm text-ink-subtle">
                Calificando tu acusación…
            </p>
        );
    }

    if (row.score === null || row.score === undefined) {
        return null;
    }

    return (
        <div className="mt-4 border-t border-line pt-3">
            <p className="flex items-baseline justify-between gap-3">
                <span className="case-stamp text-[10px] text-ink-subtle">Tu puntaje</span>
                <span className="tabular text-lg font-semibold text-ink">
                    {row.score}
                    <span className="text-sm font-normal text-ink-subtle"> / {maxScore}</span>
                </span>
            </p>

            {row.grading?.length > 1 && (
                <ul className="mt-3 space-y-2.5">
                    {row.grading.map((item) => (
                        <li key={item.key} className="text-sm">
                            <span className="flex justify-between gap-3">
                                <span className="text-ink">{item.label}</span>
                                <span className="tabular shrink-0 text-ink-muted">
                                    {item.points} / {item.max}
                                </span>
                            </span>
                            {item.feedback && (
                                <span className="mt-0.5 block text-xs text-ink-subtle">
                                    {item.feedback}
                                </span>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            {row.grading_status === 'failed' && (
                <p className="mt-2 text-xs text-ink-subtle">
                    No se pudo calificar el resto de tus respuestas; tu puntaje cuenta solo el
                    culpable.
                </p>
            )}
        </div>
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
    maxScore,
    questions = [],
}) {
    const you = scoreboard.find((row) => row.is_you);
    const total = scoreboard.length;
    const grading = scoreboard.some((row) => row.grading_status === 'pending');

    // The grading lands on the queue a moment after the reveal.
    usePoll(['scoreboard'], { interval: 8000, enabled: grading });

    // Closing a photo remounts the page (see useSessionState); the tab the
    // reader was on has to survive that.
    const [tab, setTab] = useSessionState(`case-solution:${game.id}:tab`, 'verdict');

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

                    {questions.length > 0 && you.answers && (
                        <dl className="mt-3 space-y-2">
                            {questions.map((question) =>
                                you.answers[question.key] ? (
                                    <div key={question.key} className="text-sm">
                                        <dt className="text-ink-subtle">{question.label}</dt>
                                        <dd className="text-ink-muted">{you.answers[question.key]}</dd>
                                    </div>
                                ) : null
                            )}
                        </dl>
                    )}

                    {you.suspect_name && <Grading row={you} maxScore={maxScore} />}
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
                                {row.rank && <span className="tabular text-ink-subtle">{row.rank}º · </span>}
                                {row.player_name}
                                {row.is_you && ' (tú)'}
                            </span>
                            {row.suspect_name && (
                                <span className="block text-xs text-ink-subtle">
                                    acusó a {row.suspect_name}
                                </span>
                            )}
                        </span>
                        <span className="flex items-center gap-2.5">
                            {row.grading_status === 'pending' ? (
                                <span className="text-xs text-ink-subtle">Calificando…</span>
                            ) : (
                                row.score !== null &&
                                row.score !== undefined && (
                                    <span className="tabular text-sm text-ink-muted">
                                        {row.score}
                                        <span className="text-ink-subtle">/{maxScore}</span>
                                    </span>
                                )
                            )}
                            <Verdict correct={row.correct} />
                        </span>
                    </li>
                ))}
            </ul>

            <p className="mt-4 border-t border-line pt-3 text-xs text-ink-subtle">
                Primero quienes acertaron al culpable; entre ellos, decide el puntaje de sus
                respuestas.
            </p>

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
                active={tab}
                onChange={setTab}
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
