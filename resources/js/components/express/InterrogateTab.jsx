import { useState } from 'react';
import Badge from '../ui/Badge';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import Meter from '../ui/Meter';
import Avatar from './Avatar';
import SuspectPanel from './SuspectPanel';
import { formatClock, secondsLeft } from '../../lib/express';

function LockIcon({ className = 'h-5 w-5' }) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" aria-hidden="true" className={className}>
            <rect x="5" y="11" width="14" height="9" rx="2" />
            <path strokeLinecap="round" d="M8 11V8a4 4 0 0 1 8 0v3" />
        </svg>
    );
}

function SuspectCard({ suspect, onOpen, meId }) {
    const locked = Boolean(suspect.locked_for_me);
    const claimed = suspect.claimed_by;
    const mine = claimed && claimed.id === meId;

    return (
        <li>
            <button
                type="button"
                disabled={locked}
                onClick={() => onOpen(suspect.id)}
                className="flex min-h-20 w-full items-center gap-3 rounded-card border border-line bg-surface-raised p-3 text-left transition-colors hover:border-accent disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:border-line"
            >
                <Avatar name={suspect.name} src={suspect.portrait} size="lg" />

                <span className="min-w-0 flex-1">
                    <span className="block truncate font-semibold text-ink">{suspect.name}</span>
                    <span className="block text-sm text-ink-muted">{suspect.role}</span>
                    {suspect.relation && (
                        <span className="mt-0.5 line-clamp-2 block text-xs text-ink-subtle">
                            {suspect.relation}
                        </span>
                    )}
                    {locked && claimed?.nickname && (
                        <span className="mt-1 block text-xs font-medium text-warning-strong">
                            Interroga {claimed.nickname}
                        </span>
                    )}
                </span>

                {locked ? (
                    <LockIcon className="h-5 w-5 shrink-0 text-ink-subtle" />
                ) : mine ? (
                    <Badge tone="accent">Tú</Badge>
                ) : (
                    <span aria-hidden="true" className="shrink-0 text-ink-subtle">
                        ›
                    </span>
                )}
            </button>
        </li>
    );
}

/** The whole team's questions, every suspect, for anyone who missed one. */
function TeamLog({ log, suspects }) {
    const byId = new Map(suspects.map((suspect) => [suspect.id, suspect]));

    return (
        <Card as="section" padded={false}>
            <details>
                <summary className="flex min-h-12 cursor-pointer items-center justify-between gap-3 px-4 py-3 font-semibold text-ink">
                    <span>
                        Lo que ya sabemos{' '}
                        <span className="tabular text-sm font-normal text-ink-muted">({log.length})</span>
                    </span>
                    <span aria-hidden="true" className="text-ink-muted">
                        ▾
                    </span>
                </summary>

                <div className="space-y-3 border-t border-line px-4 py-4">
                    {log.length === 0 ? (
                        <p className="text-sm text-ink-muted">
                            Todavía no han preguntado nada. Cada respuesta que reciba el equipo queda aquí.
                        </p>
                    ) : (
                        <ul className="space-y-4">
                            {log.map((entry) => (
                                <li key={entry.id} className="text-sm">
                                    <p className="text-xs font-medium text-accent-strong">
                                        {entry.nickname} le preguntó a {byId.get(entry.suspect_id)?.name ?? 'alguien'}
                                    </p>
                                    <p className="mt-1 text-ink-muted">“{entry.question}”</p>
                                    <p className="mt-1.5 border-l-2 border-line-strong pl-3 text-ink">
                                        {entry.answer}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </details>
        </Card>
    );
}

export default function InterrogateTab({ state, clock, onAsk }) {
    const [openId, setOpenId] = useState(null);

    const interrogation = state.interrogation ?? {};
    const suspects = Array.isArray(state.suspects) ? state.suspects : [];
    const log = Array.isArray(interrogation.log) ? interrogation.log : [];
    const total = interrogation.budget_total ?? 3;
    const used = interrogation.budget_used ?? 0;
    const current = suspects.find((suspect) => suspect.id === openId) ?? null;

    if (!interrogation.open) {
        const opensIn = secondsLeft(clock?.interrogationOpenIn);

        return (
            <EmptyState
                title="Los interrogatorios aún no abren"
                description={
                    opensIn != null
                        ? `Se abren en ${formatClock(opensIn)}. Mientras tanto, lean las pistas.`
                        : 'Se abren en un momento. Mientras tanto, lean las pistas.'
                }
                action={<LockIcon className="h-6 w-6 text-ink-subtle" />}
            />
        );
    }

    return (
        <div className="space-y-4">
            <div>
                <p className="flex items-center justify-between text-sm">
                    <span className="font-medium text-ink">Preguntas del equipo</span>
                    <span className="tabular font-semibold text-ink">
                        {used}/{total}
                    </span>
                </p>
                <Meter
                    value={used}
                    max={total}
                    tone={used >= total ? 'danger' : 'accent'}
                    label="Preguntas del equipo usadas"
                    size="md"
                    className="mt-2"
                />
                <p className="mt-2 text-xs text-ink-muted">
                    Son pocas y las gasta todo el grupo. Cada sospechoso lo interroga una sola persona.
                </p>
            </div>

            {suspects.length === 0 ? (
                <EmptyState title="No hay sospechosos para interrogar" description="Revisen las pistas del caso." />
            ) : (
                <ul className="space-y-3" aria-label="Sospechosos">
                    {suspects.map((suspect) => (
                        <SuspectCard
                            key={suspect.id}
                            suspect={suspect}
                            meId={state.me?.id}
                            onOpen={setOpenId}
                        />
                    ))}
                </ul>
            )}

            <TeamLog log={log} suspects={suspects} />

            <SuspectPanel
                suspect={current}
                state={state}
                open={current !== null}
                onClose={() => setOpenId(null)}
                onAsk={onAsk}
            />
        </div>
    );
}
