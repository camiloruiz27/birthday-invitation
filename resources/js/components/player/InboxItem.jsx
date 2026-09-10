import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Tabs from '../ui/Tabs';
import AudioPlayer from './AudioPlayer';
import CaseDocument from './CaseDocument';
import GalleryGrid from './GalleryGrid';
import { formatDateTime } from '../../lib/format';

/**
 * An envelope, in its two states: a row in the list and the thing you read.
 *
 * They live in one file because they are one object — change what an
 * envelope carries and both have to learn about it on the same day.
 */

const ENVELOPE_ICON =
    'M3 8l9 6 9-6M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z';

const AUDIO_ICON =
    'M12 1.5a3 3 0 00-3 3v7a3 3 0 006 0v-7a3 3 0 00-3-3zM19 10.5v1.5a7 7 0 01-14 0v-1.5M12 19v3.5M8.5 22.5h7';

function EnvelopeIcon({ audio, className = 'h-5 w-5' }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.5"
            aria-hidden="true"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d={audio ? AUDIO_ICON : ENVELOPE_ICON}
            />
        </svg>
    );
}

/**
 * One line in the inbox list.
 *
 * A real <a href="#sobre-N">, not a button: that is what makes the phone's
 * back gesture close the envelope instead of leaving the game, and what lets
 * a player send a teammate a link straight to a piece of evidence.
 */
export function EnvelopeRow({ item, number, active, unread }) {
    const { event, gallery } = item;
    const isAudio = event.type === 'audio_email';

    return (
        <li>
            <a
                href={`#sobre-${event.id}`}
                aria-current={active ? 'true' : undefined}
                className={`flex w-full items-start gap-3 rounded-card border p-3.5 text-left transition-colors ${
                    active
                        ? 'border-accent bg-accent-dim/40'
                        : 'border-line bg-surface-raised hover:border-line-strong'
                }`}
            >
                <span
                    className={`mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-control ${
                        unread ? 'bg-accent text-ink-inverse' : 'bg-surface-sunken text-ink-muted'
                    }`}
                >
                    <EnvelopeIcon audio={isAudio} />
                </span>

                <span className="min-w-0 flex-1">
                    <span className="flex items-baseline gap-2">
                        <span className="tabular shrink-0 font-mono text-[11px] text-ink-subtle">
                            {String(number).padStart(2, '0')}
                        </span>
                        <span
                            className={`min-w-0 flex-1 truncate text-sm ${
                                unread ? 'font-semibold text-ink' : 'text-ink-muted'
                            }`}
                        >
                            {event.title}
                        </span>
                    </span>

                    <span className="mt-1 block font-mono text-[11px] text-ink-subtle">
                        {formatDateTime(event.sent_at)}
                    </span>

                    {(unread || isAudio || gallery.length > 0 || event.cta_interrogation) && (
                        <span className="mt-2 flex flex-wrap items-center gap-1.5">
                            {unread && <Badge tone="accent">Nuevo</Badge>}
                            {isAudio && <Badge>Audio</Badge>}
                            {gallery.length > 0 && (
                                <Badge>
                                    <span className="tabular">{gallery.length}</span> fotos
                                </Badge>
                            )}
                            {event.cta_interrogation && <Badge tone="success">Interrogatorios</Badge>}
                        </span>
                    )}
                </span>
            </a>
        </li>
    );
}

/**
 * The envelope open on the desk.
 *
 * The body used to be a single dangerouslySetInnerHTML div holding an entire
 * markdown file — up to nine interrogation transcripts in one scroll.
 * CaseDocument is what breaks that up; everything else here is the envelope
 * around it.
 */
export default function InboxItem({ item, playerToken, game }) {
    const { event, body_html: bodyHtml, gallery } = item;
    const isAudio = event.type === 'audio_email';

    const galleryFallback =
        gallery.length > 0 ? (
            <p className="text-sm text-ink-muted">
                Todo el contenido de este sobre está en las imágenes.
            </p>
        ) : null;

    const document = (
        <CaseDocument
            html={bodyHtml}
            id={`sobre-${event.id}`}
            headingLevel={3}
            emptyFallback={galleryFallback}
        />
    );

    return (
        <article id={`sobre-${event.id}`}>
            <header className="border-b border-line pb-4">
                <p className="font-mono text-xs text-ink-subtle">{formatDateTime(event.sent_at)}</p>
                <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight text-ink sm:text-3xl">
                    {event.title}
                </h2>
            </header>

            {isAudio && (
                <AudioPlayer
                    src={route('immersion.player.audio', [playerToken, event.id])}
                    className="mt-5"
                />
            )}

            <div className="mt-6">
                {gallery.length > 0 ? (
                    <Tabs
                        label="Contenido del sobre"
                        tabs={[
                            { key: 'document', label: 'Documento', content: document },
                            {
                                key: 'gallery',
                                label: 'Fotos',
                                badge: gallery.length,
                                content: <GalleryGrid images={gallery} />,
                            },
                        ]}
                    />
                ) : (
                    document
                )}
            </div>

            {/* The one event flagged cta_interrogation is by definition the
                moment the suspects open up. The payload has carried this flag
                since the beginning and only the email ever used it. */}
            {event.cta_interrogation && game?.interrogation_enabled && (
                <div className="mt-8 rounded-card border border-line bg-surface-raised p-5">
                    <p className="text-sm text-ink">
                        Este sobre abre los interrogatorios. Ya puedes sentarte con las personas
                        de interés.
                    </p>
                    <Button
                        href={route('immersion.player.interrogation.index', playerToken)}
                        className="mt-4"
                    >
                        Ir a interrogar
                    </Button>
                </div>
            )}
        </article>
    );
}
