/**
 * Native audio controls: they are keyboard accessible, they inherit the
 * platform's own playback UI, and preload="none" keeps a multi-megabyte WAV
 * off a phone's data plan until the player actually presses play.
 */
export default function AudioPlayer({ src, label = 'Grabación adjunta' }) {
    return (
        <div className="border-t border-dashed border-paper-line px-4 py-3.5">
            <p className="case-stamp mb-2 text-[10px] text-paper-muted">{label}</p>
            <audio controls preload="none" className="w-full">
                <source src={src} type="audio/wav" />
                Tu navegador no puede reproducir este audio.
            </audio>
        </div>
    );
}
