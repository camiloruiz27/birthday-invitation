export default function GalleryGrid({ images }) {
    if (!images.length) return null;

    return (
        <div className="grid grid-cols-2 gap-3 border-t border-dashed border-border-soft px-4 py-4 sm:grid-cols-3">
            {images.map((image) => {
                const url = `/immersion/gallery/${image.file}`;
                return (
                    <a key={image.file} href={url} target="_blank" rel="noreferrer" className="block">
                        <img
                            src={url}
                            alt={image.caption}
                            className="w-full rounded border border-border-soft object-cover"
                        />
                        <p className="mt-1 text-xs text-muted">{image.caption}</p>
                    </a>
                );
            })}
        </div>
    );
}
