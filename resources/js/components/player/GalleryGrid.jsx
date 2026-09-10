import { useCallback, useEffect, useState } from 'react';
import Button from '../ui/Button';
import Modal from '../ui/Modal';

/**
 * Evidence scans.
 *
 * These used to be plain links to the image file, which on a phone means
 * leaving the game to look at a photo and finding your way back. They open
 * in a dialog now: a player can hold a scan up against what they were
 * reading without losing the page, and Escape closes it.
 *
 * loading="lazy" matters — sobre-1 carries eleven scans.
 */
export default function GalleryGrid({ images, className = '' }) {
    const [openIndex, setOpenIndex] = useState(null);

    /**
     * The open scan gets a history entry, so Android's back gesture — which
     * is how everyone closes a full-screen image — closes the photo instead
     * of navigating out of the game. The inbox already does this for the open
     * envelope; a lightbox that ignored it was the one place the back button
     * lost your place.
     *
     * Inertia keeps the page's props in history.state, so the existing state
     * is spread through rather than replaced: dropping it would leave Inertia
     * unable to restore this page on a forward navigation.
     */
    function open(index) {
        setOpenIndex(index);
        window.history.pushState({ ...window.history.state, galleryOpen: true }, '');
    }

    const close = useCallback(() => {
        if (window.history.state?.galleryOpen) {
            // Let popstate do the closing, so the entry is consumed instead
            // of piling up every time a photo is opened and shut.
            window.history.back();

            return;
        }

        setOpenIndex(null);
    }, []);

    useEffect(() => {
        if (openIndex === null) {
            return undefined;
        }

        const handlePop = () => setOpenIndex(null);

        window.addEventListener('popstate', handlePop);

        return () => window.removeEventListener('popstate', handlePop);
    }, [openIndex]);

    if (!images.length) {
        return null;
    }

    const current = openIndex === null ? null : images[openIndex];

    return (
        <div className={className}>
            <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                {images.map((image, index) => (
                    <li key={image.url}>
                        <button
                            type="button"
                            onClick={() => open(index)}
                            className="group block w-full text-left"
                        >
                            {/* A fixed ratio, so eleven lazy-loading scans do
                               not reflow the page under the player's thumb as
                               each one decodes. It is also what gives
                               object-cover a height to work against. */}
                            <span className="block aspect-4/3 overflow-hidden rounded-card border border-line bg-surface-sunken">
                                <img
                                    src={image.url}
                                    alt={image.caption}
                                    loading="lazy"
                                    className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105 group-active:scale-105"
                                />
                            </span>
                            <span className="mt-2 block text-sm leading-snug text-ink-muted group-hover:text-ink">
                                {image.caption}
                            </span>
                        </button>
                    </li>
                ))}
            </ul>

            <Modal
                open={current !== null}
                onClose={close}
                size="lg"
                title={current?.caption || 'Prueba'}
                footer={
                    <>
                        <Button
                            variant="ghost"
                            href={current?.url}
                            external
                            target="_blank"
                            rel="noreferrer"
                        >
                            Abrir a tamaño completo
                        </Button>
                        <Button variant="secondary" onClick={close}>
                            Cerrar
                        </Button>
                    </>
                }
            >
                {current && (
                    <img
                        src={current.url}
                        alt={current.caption}
                        className="max-h-[65vh] w-full rounded-control bg-surface-sunken object-contain"
                    />
                )}
            </Modal>
        </div>
    );
}
