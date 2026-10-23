import { useMemo, useState } from 'react';
import Alert from '../ui/Alert';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import EvidenceCard from './EvidenceCard';
import { secondsLeft } from '../../lib/express';

/** Newest first; within the same second the later in the list is the newer. */
function newestFirst(evidence) {
    return evidence
        .map((item, index) => ({ item, index }))
        .sort((a, b) => (b.item.unlocked_at_s ?? 0) - (a.item.unlocked_at_s ?? 0) || b.index - a.index)
        .map(({ item }) => item);
}

function IncidentCard({ caseInfo, defaultOpen }) {
    const [open, setOpen] = useState(defaultOpen);
    const { scenario, incident, hook, title } = caseInfo ?? {};

    if (!scenario && !incident && !hook) return null;

    return (
        <Card as="section" padded={false} aria-label="El caso">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-expanded={open}
                className="flex min-h-12 w-full items-center justify-between gap-3 px-4 py-3 text-left"
            >
                <span className="min-w-0">
                    <span className="case-stamp block text-[10px] text-accent">El caso</span>
                    <span className="block truncate font-semibold text-ink">{title}</span>
                </span>
                <span aria-hidden="true" className="text-ink-muted">
                    {open ? '−' : '+'}
                </span>
            </button>

            {open && (
                <div className="space-y-3 border-t border-line px-4 py-4 text-sm leading-relaxed">
                    {hook && <p className="font-medium text-ink">{hook}</p>}
                    {scenario && <p className="text-ink-muted">{scenario}</p>}
                    {incident && <p className="text-ink-muted">{incident}</p>}
                </div>
            )}
        </Card>
    );
}

export default function CluesTab({ state, elapsedS, clock, newIds, onShare, sharingId }) {
    const evidence = Array.isArray(state.evidence) ? state.evidence : [];
    const ordered = useMemo(() => newestFirst(evidence), [evidence]);
    const nextIn = secondsLeft(clock?.nextUnlockIn);
    const onlyPrivate =
        evidence.length > 0 && evidence.every((item) => item.audience !== 'global' && !item.shared);
    const nudge = onlyPrivate && elapsedS != null && elapsedS >= 100;

    return (
        <div className="space-y-4">
            <IncidentCard caseInfo={state.room?.case} defaultOpen={evidence.length <= 2} />

            {nudge && (
                <Alert variant="warning" className="mb-0!">
                    <strong className="font-semibold">Comparen lo que ven en sus pantallas.</strong> Cada
                    celular tiene pistas distintas: si no las comparten, falta información para acusar.
                </Alert>
            )}

            {state.next_unlock && nextIn != null && (
                <p
                    className="flex min-h-9 items-center gap-2 text-sm text-ink-muted"
                    aria-live="off"
                >
                    <span aria-hidden="true" className="h-2 w-2 rounded-full bg-accent" />
                    Próxima pista en <span className="tabular font-semibold text-ink">{nextIn} s</span>
                </p>
            )}

            {ordered.length === 0 ? (
                <EmptyState
                    title="Aún no ha llegado ninguna pista"
                    description="Las pistas aparecen aquí mientras corre el reloj. Mantén esta pantalla abierta."
                />
            ) : (
                <div className="space-y-4">
                    {ordered.map((item) => (
                        <EvidenceCard
                            key={item.id}
                            evidence={item}
                            meNickname={state.me?.nickname}
                            isNew={newIds.has(item.id)}
                            onShare={onShare}
                            sharing={sharingId === item.id}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
