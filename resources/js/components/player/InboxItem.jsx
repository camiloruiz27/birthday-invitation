import AudioPlayer from './AudioPlayer';
import GalleryGrid from './GalleryGrid';
import { formatDateTime } from '../../lib/format';

function stripTags(html) {
    return html.replace(/<[^>]*>/g, '').trim();
}

export default function InboxItem({ item, playerToken }) {
    const { event, body_html: bodyHtml, gallery } = item;
    const hasBody = stripTags(bodyHtml || '') !== '';

    return (
        <article className="border-2 border-ink bg-paper-card">
            <header className="border-b-2 border-ink bg-ink px-4 py-2 text-paper">
                <p className="immersion-stamp text-[10px] uppercase tracking-[0.2em] text-accent">
                    {formatDateTime(event.sent_at)}
                </p>
                <h2 className="font-bold">{event.title}</h2>
            </header>

            {hasBody ? (
                <div className="prose prose-sm max-w-none px-4 py-4" dangerouslySetInnerHTML={{ __html: bodyHtml }} />
            ) : gallery.length > 0 ? (
                <p className="px-4 py-4 text-sm italic text-muted">Todo el contenido de este sobre está en las imágenes de abajo.</p>
            ) : null}

            <GalleryGrid images={gallery} />

            {event.type === 'audio_email' && (
                <AudioPlayer src={route('immersion.player.audio', [playerToken, event.id])} />
            )}
        </article>
    );
}
