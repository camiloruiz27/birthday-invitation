export default function AudioPlayer({ src }) {
    return (
        <div className="border-t border-dashed border-border-soft px-4 py-3">
            <audio controls preload="none" className="w-full">
                <source src={src} type="audio/wav" />
            </audio>
        </div>
    );
}
