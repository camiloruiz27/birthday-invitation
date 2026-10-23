import { useState } from 'react';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Modal from '../ui/Modal';
import AudioPlayer from '../player/AudioPlayer';
import Sheet from '../player/paper/Sheet';
import { cleanLines, formatClock, parseLine } from '../../lib/express';

const TYPE_LABEL = {
    audio: 'Audio',
    document: 'Documento',
    screenshot: 'Captura de pantalla',
    photo: 'Foto',
    cctv: 'Cámara de seguridad',
};

/**
 * One clue, drawn the way the thing it is would look:
 *
 *   document    paper (the fiction surface — Sheet is the only place paper lives)
 *   screenshot  a dark monospace phone screen (platform surface)
 *   photo/cctv  the image, or a neutral frame when the file is not there yet
 *   audio       the player plus the transcript
 *
 * The frame around it (title, who can see it, share button) is platform
 * chrome. Every optional field (image, audio, lines, shared_by) may be
 * missing and the card still renders.
 */

function audienceBadge(evidence, meNickname) {
    if (evidence.audience === 'global') {
        return { tone: 'success', text: 'Grupo' };
    }

    if (evidence.shared) {
        const by = evidence.shared_by;
        const text = !by ? 'Compartida' : by === meNickname ? 'Compartida por ti' : `Compartida por ${by}`;

        return { tone: 'accent', text };
    }

    return { tone: 'neutral', text: 'Solo tú' };
}

function LineLabel({ children, className = '' }) {
    return <span className={`shrink-0 font-mono text-[11px] uppercase tracking-wider ${className}`}>{children}</span>;
}

function DocumentBody({ lines }) {
    return (
        <Sheet padded className="text-sm leading-relaxed">
            {lines.length === 0 ? (
                <p className="italic text-paper-muted">Documento sin texto legible.</p>
            ) : (
                <ul className="space-y-2">
                    {lines.map((line, index) => {
                        const { label, value } = parseLine(line);

                        return (
                            <li key={index} className="flex gap-3">
                                {label && (
                                    <LineLabel className="min-w-[3.25rem] pt-0.5 text-paper-muted">
                                        {label}
                                    </LineLabel>
                                )}
                                <span className="min-w-0 [overflow-wrap:anywhere]">{value}</span>
                            </li>
                        );
                    })}
                </ul>
            )}
        </Sheet>
    );
}

function ScreenshotBody({ title, lines }) {
    return (
        <div className="overflow-hidden rounded-card border border-line-strong bg-surface-sunken font-mono text-sm text-ink">
            <div className="flex items-center justify-between border-b border-line px-3 py-1.5 text-[11px] text-ink-subtle">
                <span className="truncate">{title}</span>
                <span aria-hidden="true">● ● ●</span>
            </div>
            {lines.length === 0 ? (
                <p className="p-3 text-ink-muted">Pantalla en blanco.</p>
            ) : (
                <ul className="space-y-2 p-3">
                    {lines.map((line, index) => {
                        const { label, value } = parseLine(line);

                        return (
                            <li key={index} className="flex gap-3">
                                {label && (
                                    <LineLabel className="min-w-[3.25rem] pt-0.5 text-accent-strong">
                                        {label}
                                    </LineLabel>
                                )}
                                <span className="min-w-0 [overflow-wrap:anywhere]">{value}</span>
                            </li>
                        );
                    })}
                </ul>
            )}
        </div>
    );
}

function ImagePlaceholder({ cctv }) {
    return (
        <div className="flex h-full w-full flex-col items-center justify-center gap-2 text-ink-subtle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true" className="h-8 w-8">
                <path strokeLinecap="round" strokeLinejoin="round" d="M3 8a2 2 0 0 1 2-2h2l1.2-1.6h7.6L17 6h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8z" />
                <circle cx="12" cy="12.5" r="3.5" />
            </svg>
            <span className="text-xs">{cctv ? 'Imagen de cámara no disponible' : 'Imagen no disponible aún'}</span>
        </div>
    );
}

function PhotoBody({ evidence, lines, cctv }) {
    const [broken, setBroken] = useState(false);
    const [zoomed, setZoomed] = useState(false);
    const hasImage = Boolean(evidence.image) && !broken;

    const frame = (
        <div className="relative aspect-4/3 overflow-hidden rounded-card border border-line-strong bg-surface-sunken">
            {hasImage ? (
                <img
                    src={evidence.image}
                    alt={evidence.title}
                    loading="lazy"
                    onError={() => setBroken(true)}
                    className="h-full w-full object-cover"
                />
            ) : (
                <ImagePlaceholder cctv={cctv} />
            )}
            {cctv && (
                <span
                    aria-hidden="true"
                    className="absolute left-2 top-2 rounded bg-black/70 px-2 py-0.5 font-mono text-[11px] text-danger-strong"
                >
                    ● REC
                </span>
            )}
        </div>
    );

    return (
        <figure>
            {hasImage ? (
                <button
                    type="button"
                    onClick={() => setZoomed(true)}
                    className="block w-full text-left"
                    aria-label={`Ampliar imagen: ${evidence.title}`}
                >
                    {frame}
                </button>
            ) : (
                frame
            )}

            {lines.length > 0 && (
                <figcaption>
                    <ul className="mt-2 flex flex-wrap gap-2">
                        {lines.map((line, index) => {
                            const { label, value } = parseLine(line);

                            return (
                                <li
                                    key={index}
                                    className="max-w-full rounded-control border border-line bg-surface-sunken px-2.5 py-1.5 text-xs text-ink-muted"
                                >
                                    {label && (
                                        <span className="mr-1.5 font-mono text-accent-strong">{label}</span>
                                    )}
                                    <span className="[overflow-wrap:anywhere]">{value}</span>
                                </li>
                            );
                        })}
                    </ul>
                </figcaption>
            )}

            {hasImage && (
                <Modal
                    open={zoomed}
                    onClose={() => setZoomed(false)}
                    size="lg"
                    title={evidence.title}
                    footer={
                        <Button variant="secondary" onClick={() => setZoomed(false)}>
                            Cerrar
                        </Button>
                    }
                >
                    <img
                        src={evidence.image}
                        alt={evidence.title}
                        className="max-h-[65vh] w-full rounded-control bg-surface-sunken object-contain"
                    />
                </Modal>
            )}
        </figure>
    );
}

function AudioBody({ evidence, lines }) {
    return (
        <div className="space-y-3">
            {evidence.audio ? (
                <AudioPlayer src={evidence.audio} label={evidence.title} />
            ) : (
                <div className="rounded-card border border-dashed border-line-strong bg-surface-sunken p-4 text-sm text-ink-muted">
                    Audio no disponible aún.
                </div>
            )}

            {lines.length > 0 && (
                <div className="rounded-card border border-line bg-surface-sunken p-3">
                    <p className="case-stamp text-[10px] text-ink-subtle">Transcripción</p>
                    <ul className="mt-2 space-y-1.5 text-sm text-ink-muted">
                        {lines.map((line, index) => {
                            const { label, value } = parseLine(line);

                            return (
                                <li key={index} className="flex gap-3">
                                    {label && <LineLabel className="min-w-[3.25rem] pt-0.5 text-accent-strong">{label}</LineLabel>}
                                    <span className="min-w-0 [overflow-wrap:anywhere]">{value}</span>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            )}
        </div>
    );
}

export default function EvidenceCard({
    evidence,
    meNickname = null,
    isNew = false,
    onShare = null,
    sharing = false,
}) {
    const lines = cleanLines(evidence.lines);
    const audience = audienceBadge(evidence, meNickname);
    const type = evidence.type;

    let body;
    if (type === 'screenshot') {
        body = <ScreenshotBody title={evidence.title} lines={lines} />;
    } else if (type === 'photo' || type === 'cctv') {
        body = <PhotoBody evidence={evidence} lines={lines} cctv={type === 'cctv'} />;
    } else if (type === 'audio') {
        body = <AudioBody evidence={evidence} lines={lines} />;
    } else {
        body = <DocumentBody lines={lines} />;
    }

    return (
        <article
            aria-label={evidence.title}
            className={`rounded-card border bg-surface-raised p-3.5 shadow-raised sm:p-4 ${
                isNew ? 'border-accent' : 'border-line'
            }`}
        >
            <div className="mb-3 flex flex-wrap items-center gap-2">
                <Badge tone={audience.tone}>{audience.text}</Badge>
                <span className="case-stamp text-[10px] text-ink-subtle">
                    {TYPE_LABEL[type] ?? 'Pista'}
                </span>
                {isNew && <Badge tone="accent">Nueva</Badge>}
                {typeof evidence.unlocked_at_s === 'number' && (
                    <span className="tabular ml-auto font-mono text-[11px] text-ink-subtle">
                        {formatClock(evidence.unlocked_at_s)}
                    </span>
                )}
            </div>

            <h3 className="mb-3 text-base font-semibold text-ink">{evidence.title}</h3>

            {body}

            {onShare && evidence.can_share && (
                <Button
                    variant="secondary"
                    fullWidth
                    className="mt-3"
                    loading={sharing}
                    onClick={() => onShare(evidence)}
                >
                    Compartir con el grupo
                </Button>
            )}
        </article>
    );
}
