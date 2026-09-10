/**
 * Native audio controls: they are keyboard accessible, they inherit the
 * platform's own playback UI, and preload="none" keeps a multi-megabyte WAV
 * off a phone's data plan until the player actually presses play.
 */
export default function AudioPlayer({ src, label = 'Grabación adjunta', className = '' }) {
    return (
        <div className={`rounded-card border border-line bg-surface-raised p-4 ${className}`}>
            <p className="case-stamp mb-3 text-[10px] text-accent">{label}</p>
            <audio controls preload="none" className="w-full">
                <source src={src} type="audio/wav" />
                Tu navegador no puede reproducir este audio.
            </audio>
        </div>
    );
}
