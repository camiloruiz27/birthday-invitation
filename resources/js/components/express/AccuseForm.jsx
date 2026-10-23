import { useState } from 'react';
import Alert from '../ui/Alert';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import Skeleton from '../ui/Skeleton';
import { ConfirmModal } from '../ui/Modal';
import PlayerList from './PlayerList';
import { formatClock, secondsLeft } from '../../lib/express';

const GROUPS = [
    { key: 'who', legend: '¿Quién?', hint: 'La persona que lo hizo.' },
    { key: 'how', legend: '¿Cómo?', hint: 'De qué manera lo hizo.' },
    { key: 'why', legend: '¿Por qué?', hint: 'Su motivo.' },
];

/** One question, its options as radio cards (real radios: arrows and labels come free). */
function Choice({ group, options, value, onChange, disabled }) {
    return (
        <fieldset disabled={disabled}>
            <legend className="text-sm font-semibold text-ink">
                {group.legend}
                <span className="ml-2 text-xs font-normal text-ink-muted">{group.hint}</span>
            </legend>

            <div className="mt-2.5 space-y-2">
                {options.map((option) => {
                    const id = `accuse-${group.key}-${option.id}`;

                    return (
                        <label key={option.id} htmlFor={id} className="block cursor-pointer">
                            <input
                                id={id}
                                type="radio"
                                name={`accuse-${group.key}`}
                                value={option.id}
                                checked={value === option.id}
                                onChange={() => onChange(option.id)}
                                className="peer sr-only"
                            />
                            <span className="flex min-h-12 items-center gap-3 rounded-card border border-line bg-surface-raised px-3.5 py-3 text-sm text-ink transition-colors peer-checked:border-accent peer-checked:bg-accent-dim/40 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent hover:border-line-strong">
                                <span
                                    aria-hidden="true"
                                    className={`flex h-5 w-5 shrink-0 items-center justify-center rounded-full border ${
                                        value === option.id ? 'border-accent' : 'border-line-strong'
                                    }`}
                                >
                                    {value === option.id && <span className="h-2.5 w-2.5 rounded-full bg-accent" />}
                                </span>
                                <span className="min-w-0 [overflow-wrap:anywhere]">{option.label}</span>
                            </span>
                        </label>
                    );
                })}
            </div>
        </fieldset>
    );
}

function labelOf(options, id) {
    return (options ?? []).find((option) => option.id === id)?.label ?? null;
}

/**
 * The accusation: who, how and why, once.
 *
 * Options arrive already shuffled by the server (render them as given) and
 * correctness is never in the payload before the reveal — nothing here can
 * hint at it. Submitting goes through a confirmation, because it cannot be
 * changed; afterwards the screen only waits for the others.
 */
export default function AccuseForm({ state, clock, players, onSubmit }) {
    const accusation = state.accusation ?? {};
    const options = accusation.options;
    const [picks, setPicks] = useState({ who: null, how: null, why: null });
    const [confirming, setConfirming] = useState(false);
    const [sending, setSending] = useState(false);
    const [error, setError] = useState(null);

    // The server reports `open: false` once I have accused, so "submitted"
    // has to be checked first or the waiting screen would never show.
    if (!accusation.open && !accusation.submitted) {
        const opensIn = secondsLeft(clock?.accusationsOpenIn);

        return (
            <EmptyState
                title="Las acusaciones aún están cerradas"
                description={
                    opensIn != null && opensIn > 0
                        ? `Se abren en ${formatClock(opensIn)}. Sigan investigando e interrogando.`
                        : 'Se abren en un momento. Sigan investigando e interrogando.'
                }
            />
        );
    }

    if (accusation.submitted) {
        const mine = accusation.mine ?? {};
        const waitingFor = Array.isArray(accusation.waiting_for) ? accusation.waiting_for : [];
        const left = secondsLeft(clock?.closesInLeft);

        return (
            <div className="space-y-4">
                <Card as="section">
                    <p className="case-stamp text-[10px] text-success-strong">Acusación enviada</p>
                    <h2 className="mt-1 font-display text-xl font-semibold text-ink">
                        Listo. Ya no se puede cambiar.
                    </h2>

                    {options && (
                        <dl className="mt-4 space-y-2 text-sm">
                            {GROUPS.map((group) => {
                                const text = labelOf(options[group.key], mine[group.key]);

                                return text ? (
                                    <div key={group.key}>
                                        <dt className="text-xs text-ink-muted">{group.legend}</dt>
                                        <dd className="text-ink">{text}</dd>
                                    </div>
                                ) : null;
                            })}
                        </dl>
                    )}
                </Card>

                <Card as="section" aria-live="polite">
                    <h3 className="font-semibold text-ink">
                        {waitingFor.length > 0 ? 'Esperando al resto del equipo' : 'Todos acusaron'}
                    </h3>

                    {waitingFor.length > 0 && (
                        <ul className="mt-3 flex flex-wrap gap-2" aria-label="Faltan por acusar">
                            {waitingFor.map((nickname) => (
                                <li
                                    key={nickname}
                                    className="rounded-full border border-line-strong bg-surface-sunken px-3 py-1 text-sm text-ink-muted"
                                >
                                    {nickname}
                                </li>
                            ))}
                        </ul>
                    )}

                    <p className="mt-3 text-sm text-ink-muted">
                        {waitingFor.length === 0
                            ? 'Ya viene el desenlace…'
                            : left != null
                              ? <>Se revela cuando todos acusen o en <span className="tabular font-semibold text-ink">{left} s</span>.</>
                              : 'Se revela cuando todos acusen o cuando se acabe el tiempo.'}
                    </p>

                    <PlayerList players={players} showAccused className="mt-4" />
                </Card>
            </div>
        );
    }

    if (!options) {
        return (
            <div className="space-y-3" aria-busy="true">
                <Skeleton className="h-6 w-1/3" />
                <Skeleton className="h-12 w-full" />
                <Skeleton className="h-12 w-full" />
                <Skeleton className="h-12 w-full" />
            </div>
        );
    }

    const complete = GROUPS.every((group) => picks[group.key]);

    async function confirm() {
        setSending(true);
        setError(null);

        const result = await onSubmit({ who: picks.who, how: picks.how, why: picks.why });

        setSending(false);

        if (result.ok) {
            setConfirming(false);

            return;
        }

        setConfirming(false);
        setError(
            result.code === 'already_accused'
                ? 'Tu acusación ya había sido enviada.'
                : result.code === 'closed'
                  ? 'El tiempo para acusar terminó.'
                  : result.message || 'No se pudo enviar la acusación. Intenta de nuevo.'
        );
    }

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                if (complete) setConfirming(true);
            }}
            className="space-y-6"
        >
            <div>
                <h2 className="font-display text-xl font-semibold text-ink">Hora de acusar</h2>
                <p className="mt-1 text-sm text-ink-muted">
                    Elige una opción en cada pregunta. Solo puedes acusar una vez.
                </p>
            </div>

            {GROUPS.map((group) => (
                <Choice
                    key={group.key}
                    group={group}
                    options={Array.isArray(options[group.key]) ? options[group.key] : []}
                    value={picks[group.key]}
                    onChange={(id) => setPicks((current) => ({ ...current, [group.key]: id }))}
                    disabled={sending}
                />
            ))}

            <Alert variant="error">{error}</Alert>

            <Button type="submit" size="lg" fullWidth disabled={!complete}>
                {complete ? 'Acusar' : 'Elige una opción en cada pregunta'}
            </Button>

            <ConfirmModal
                open={confirming}
                onClose={() => !sending && setConfirming(false)}
                onConfirm={confirm}
                processing={sending}
                title="¿Enviar tu acusación?"
                description="No se puede cambiar después."
                confirmLabel="Sí, acusar"
                cancelLabel="Revisar"
            >
                <dl className="space-y-2 text-sm">
                    {GROUPS.map((group) => (
                        <div key={group.key}>
                            <dt className="text-xs text-ink-muted">{group.legend}</dt>
                            <dd className="text-ink">{labelOf(options[group.key], picks[group.key]) ?? '—'}</dd>
                        </div>
                    ))}
                </dl>
            </ConfirmModal>
        </form>
    );
}
