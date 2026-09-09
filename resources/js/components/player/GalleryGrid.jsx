/**
 * Evidence images beside an envelope's text.
 *
 * Two columns even on the narrowest phone, because these are scans meant to be
 * scanned visually and then opened full size — the link target is the image
 * itself, which is how a player zooms into a document on a phone.
 *
 * loading="lazy" matters: sobre-1 carries eleven scans.
 */
export default function GalleryGrid({ images }) {
    if (!images.length) return null;

    return (
        <div className="grid grid-cols-2 gap-3 border-t border-dashed border-paper-line px-4 py-4 sm:grid-cols-3">
            {images.map((image) => (
                <a
                    key={image.url}
                    href={image.url}
                    target="_blank"
                    rel="noreferrer"
                    className="block"
                >
                    <img
                        src={image.url}
                        alt={image.caption}
                        loading="lazy"
                        className="w-full rounded border border-paper-line bg-paper object-cover"
                    />
                    <p className="mt-1.5 text-xs leading-snug text-paper-muted">{image.caption}</p>
                </a>
            ))}
        </div>
    );
}
