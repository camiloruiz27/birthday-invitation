import { useState } from 'react';
import Alert from '../ui/Alert';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card, { CardHeader } from '../ui/Card';
import Sheet, { SheetHeader } from '../player/paper/Sheet';
import { copyText } from '../ui/InAppBrowserNotice';
import { trackEvent } from '../../lib/analytics';

const PARTS = [
    { key: 'who', label: '¿Quién?' },
    { key: 'how', label: '¿Cómo?' },
    { key: 'why', label: '¿Por qué?' },
];

function ScoreRow({ label, part }) {
    // The server sends every part, with a null label when nobody answered it.
    if (!part || part.label == null) {
        return (
            <li className="flex items-start gap-3 py-3">
                <span className="w-16 shrink-0 text-xs text-ink-muted">{label}</span>
                <span className="min-w-0 flex-1 text-sm text-ink-muted">Sin respuesta</span>
                <span className="tabular w-12 shrink-0 text-right font-mono text-sm text-ink-subtle">0</span>
            </li>
        );
    }

    const correct = Boolean(part.correct);

    return (
        <li className="flex items-start gap-3 py-3">
            <span className="w-16 shrink-0 text-xs text-ink-muted">{label}</span>
            <span className="min-w-0 flex-1 text-sm text-ink [overflow-wrap:anywhere]">{part.label}</span>
            <span
                className={`flex shrink-0 items-center gap-1.5 font-mono text-sm ${
                    correct ? 'text-success-strong' : 'text-danger-strong'
                }`}
            >
                <span role="img" aria-label={correct ? 'Correcto' : 'Incorrecto'}>
                    {correct ? '✓' : '✗'}
                </span>
                <span className="tabular w-8 text-right">
                    {correct ? `+${part.points ?? 0}` : '0'}
                </span>
                {typeof part.out_of === 'number' && (
                    <span className="tabular text-xs font-normal text-ink-subtle">/{part.out_of}</span>
                )}
            </span>
        </li>
    );
}

function ScoreCard({ my }) {
    // `my` always exists, but without an accusation every label is null.
    const answered = my && PARTS.some((part) => my[part.key]?.label != null);

    if (!answered) {
        return (
            <Card as="section">
                <CardHeader title="Tu resultado" />
                <p className="text-sm text-ink-muted">No alcanzaste a acusar a tiempo. Mira la solución abajo.</p>
            </Card>
        );
    }

    return (
        <Card as="section">
            <p className="case-stamp text-[10px] text-accent">Tu resultado</p>
            <p className="mt-1 flex items-baseline gap-2">
                <span className="tabular font-display text-5xl font-semibold text-ink">{my.total ?? 0}</span>
                <span className="text-sm text-ink-muted">puntos</span>
            </p>

            <ul className="mt-3 divide-y divide-line border-t border-line">
                {PARTS.map((part) => (
                    <ScoreRow key={part.key} label={part.label} part={my[part.key]} />
                ))}
            </ul>
        </Card>
    );
}

function Ranking({ ranking }) {
    const list = Array.isArray(ranking) ? ranking : [];

    if (list.length === 0) return null;

    return (
        <Card as="section">
            <CardHeader title="Ranking de la sala" />
            <ol className="space-y-2">
                {list.map((entry, index) => (
                    <li
                        key={`${entry.nickname}-${index}`}
                        className={`flex items-center gap-3 rounded-control border px-3 py-2.5 ${
                            entry.is_me ? 'border-accent bg-accent-dim/40' : 'border-line bg-surface-sunken'
                        }`}
                    >
                        <span className="tabular w-6 shrink-0 text-center font-mono text-sm text-ink-muted">
                            {index + 1}
                        </span>
                        <span className="min-w-0 flex-1 truncate text-sm font-medium text-ink">
                            {entry.nickname}
                            {entry.is_me && <span className="ml-1.5 text-xs font-normal text-ink-muted">(tú)</span>}
                        </span>
                        {index === 0 && <Badge tone="accent">1.º</Badge>}
                        <span className="tabular shrink-0 font-mono text-sm text-ink">{entry.total ?? 0}</span>
                    </li>
                ))}
            </ol>
        </Card>
    );
}

/** The solution as a case document: paper on the dark desk. Paper tokens only. */
function Solution({ solution }) {
    if (!solution) return null;

    const correct = solution.correct ?? {};
    const nodes = Array.isArray(solution.nodes) ? solution.nodes : [];

    return (
        <Sheet as="section" aria-label="La solución del caso">
            <SheetHeader kicker="Caso cerrado" title={solution.headline || 'La solución'} />

            <div className="space-y-5 px-4 py-5 text-sm leading-relaxed sm:px-6">
                {solution.truth && <p className="font-bold">{solution.truth}</p>}

                {solution.reconstruction && (
                    <div>
                        <p className="case-stamp text-[10px] text-paper-muted">Cómo ocurrió</p>
                        <p className="mt-1.5 whitespace-pre-line [overflow-wrap:anywhere]">
                            {solution.reconstruction}
                        </p>
                    </div>
                )}

                <dl className="space-y-2 border-t border-dashed border-paper-line pt-4">
                    {PARTS.map((part) =>
                        correct[part.key] ? (
                            <div key={part.key}>
                                <dt className="case-stamp text-[10px] text-paper-muted">{part.label}</dt>
                                <dd className="font-bold">{correct[part.key]}</dd>
                            </div>
                        ) : null
                    )}
                </dl>

                {nodes.length > 0 && (
                    <div className="border-t border-dashed border-paper-line pt-4">
                        <p className="case-stamp text-[10px] text-paper-muted">Cómo se deduce</p>
                        <ul className="mt-2 space-y-1.5">
                            {nodes.map((node, index) => {
                                const text = String(node ?? '');
                                const split = text.match(/^([A-Za-z]\d+):\s*(.*)$/s);

                                return (
                                    <li key={index} className="flex gap-3">
                                        {split && (
                                            <span className="case-stamp shrink-0 pt-0.5 text-[11px] text-paper-muted">
                                                {split[1]}
                                            </span>
                                        )}
                                        <span className="min-w-0 [overflow-wrap:anywhere]">{split ? split[2] : text}</span>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                )}
            </div>
        </Sheet>
    );
}

function NextRound({ state, act }) {
    const nextRound = state.reveal?.next_round ?? {};
    const caseSlug = state.room?.case?.slug;
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState(null);

    async function go(routeName, body) {
        setBusy(true);
        setError(null);

        const result = await act(routeName, body);

        if (!result.ok) {
            setBusy(false);
            setError(result.message || 'No se pudo abrir la siguiente ronda.');

            return;
        }

        let redirect = result.data?.redirect;

        // Creating the room does not move me into it: ask to be re-joined.
        if (!redirect && routeName === 'express.next.create') {
            const joined = await act('express.next.join', {});

            if (!joined.ok) {
                setBusy(false);
                setError(joined.message || 'La sala se creó, pero no pudimos entrar. Intenta de nuevo.');

                return;
            }

            redirect = joined.data?.redirect;
        }

        if (redirect) {
            window.location.href = redirect;

            return;
        }

        setBusy(false);
    }

    return (
        <Card as="section">
            <CardHeader
                title="¿Otra ronda?"
                description={
                    nextRound.code
                        ? 'Ya hay una sala nueva con el mismo grupo.'
                        : 'Jueguen otra vez con el mismo caso y las mismas personas.'
                }
            />

            <Alert variant="error">{error}</Alert>

            {nextRound.code ? (
                <Button fullWidth size="lg" loading={busy} onClick={() => go('express.next.join', {})}>
                    Unirme a la siguiente ronda
                </Button>
            ) : (
                <Button
                    fullWidth
                    size="lg"
                    variant="secondary"
                    loading={busy}
                    onClick={() => go('express.next.create', { case_slug: caseSlug })}
                >
                    Crear otra ronda
                </Button>
            )}
        </Card>
    );
}

function CtaCard({ cta, state }) {
    const [copied, setCopied] = useState(false);

    if (!cta?.url) return null;

    async function copyCode() {
        if (await copyText(cta.promo_code)) {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    }

    function track() {
        trackEvent('express_cta_clicked', {
            case_slug: state.room?.case?.slug ?? '',
            event_slug: state.room?.event?.slug ?? '',
        });
    }

    return (
        <Card as="section" className="border-accent">
            <p className="case-stamp text-[10px] text-accent">Si te quedaste con ganas</p>
            <h2 className="mt-1 font-display text-xl font-semibold text-ink">
                Esto fue un caso corto. Los completos duran una noche entera.
            </h2>

            {cta.promo_code && (
                <div className="mt-4 rounded-card border border-dashed border-accent bg-accent-dim/30 p-3">
                    <p className="text-xs text-ink-muted">Tu código</p>
                    <div className="mt-1 flex items-center justify-between gap-3">
                        <span className="font-mono text-lg font-medium tracking-wider text-ink [overflow-wrap:anywhere]">
                            {cta.promo_code}
                        </span>
                        <Button variant="secondary" size="sm" onClick={copyCode} aria-live="polite">
                            {copied ? 'Copiado' : 'Copiar'}
                        </Button>
                    </div>
                    {cta.promo_note && <p className="mt-2 text-xs text-ink-muted">{cta.promo_note}</p>}
                </div>
            )}

            <Button
                href={cta.url}
                external
                fullWidth
                size="lg"
                className="mt-4"
                onClick={track}
            >
                {cta.label || 'Ver los casos completos'}
            </Button>
        </Card>
    );
}

export default function RevealPanel({ state, act }) {
    const reveal = state.reveal;

    if (!reveal) return null;

    return (
        <div className="space-y-5">
            <div>
                <p className="case-stamp text-[10px] text-accent">{state.room?.case?.title}</p>
                <h1 className="mt-1 font-display text-3xl font-semibold tracking-tight text-ink">
                    Caso resuelto
                </h1>
            </div>

            <ScoreCard my={reveal.my} />
            <Ranking ranking={reveal.ranking} />
            <Solution solution={reveal.solution} />
            <NextRound state={state} act={act} />
            <CtaCard cta={reveal.cta} state={state} />
        </div>
    );
}
