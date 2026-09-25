/**
 * Native audio controls: they are keyboard accessible, they inherit the
 * platform's own playback UI, and preload="none" keeps a multi-megabyte
 * recording off a phone's data plan until the player actually presses play.
 *
 * No `type` on the source: case recordings ship as WAV or MP3 depending on
 * what was actually delivered, and the server already sends the right
 * Content-Type for whichever one this is — a hardcoded type here would just
 * be a second, potentially wrong, guess.
 */
export default function AudioPlayer({ src, label = 'Grabación adjunta', className = '' }) {
    return (
        <div className={`rounded-card border border-line bg-surface-raised p-4 ${className}`}>
            <p className="case-stamp mb-3 text-[10px] text-accent">{label}</p>
            <audio controls preload="none" className="w-full">
                <source src={src} />
                Tu navegador no puede reproducir este audio.
            </audio>
        </div>
    );
}
