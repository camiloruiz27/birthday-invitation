import AudioPlayer from './AudioPlayer';
import GalleryGrid from './GalleryGrid';
import { formatDateTime } from '../../lib/format';

function hasContent(html) {
    return html.replace(/<[^>]*>/g, '').trim() !== '';
}

export default function InboxItem({ item, playerToken }) {
    const { event, body_html: bodyHtml, gallery } = item;
    const bodyIsEmpty = !hasContent(bodyHtml || '');

    return (
        <article className="border-2 border-paper-ink bg-paper-raised">
            <header className="border-b-2 border-paper-ink bg-paper-ink px-4 py-3 text-paper">
                <p className="case-stamp text-[10px] text-paper-accent">
                    {formatDateTime(event.sent_at)}
                </p>
                <h2 className="mt-0.5 font-bold">{event.title}</h2>
            </header>

            {!bodyIsEmpty ? (
                <div className="case-prose px-4 py-4 text-[15px]" dangerouslySetInnerHTML={{ __html: bodyHtml }} />
            ) : (
                gallery.length > 0 && (
                    <p className="px-4 py-4 text-sm italic text-paper-muted">
                        Todo el contenido de este sobre está en las imágenes de abajo.
                    </p>
                )
            )}

            <GalleryGrid images={gallery} />

            {event.type === 'audio_email' && (
                <AudioPlayer src={route('immersion.player.audio', [playerToken, event.id])} />
            )}
        </article>
    );
}
