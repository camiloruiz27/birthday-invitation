import { useEffect, useId, useRef } from 'react';
import Button from './Button';

/**
 * Modal dialog built on the native <dialog> element, which gives us the top
 * layer, focus trapping and Escape handling from the browser instead of
 * reimplementing them.
 *
 * Two things still need doing by hand: <dialog> fires `cancel` on Escape and
 * closes itself, so we mirror that back into React state; and a click on the
 * backdrop lands on the dialog element itself, which is how we detect it.
 */
const SIZES = {
    // Enough for a question and two buttons — the default, and what every
    // confirmation should stay at.
    sm: 'max-w-md',
    // Something you actually look at: an evidence scan, a photo.
    lg: 'max-w-3xl',
};

export default function Modal({
    open,
    onClose,
    title,
    description,
    size = 'sm',
    children,
    footer,
}) {
    const dialogRef = useRef(null);
    // Scoped ids, so two dialogs mounted at once cannot both claim to be
    // "modal-title" and point assistive tech at the wrong one.
    const base = useId();
    const titleId = `${base}-title`;
    const descriptionId = `${base}-description`;

    useEffect(() => {
        const dialog = dialogRef.current;
        if (!dialog) return;

        if (open && !dialog.open) {
            dialog.showModal();
        } else if (!open && dialog.open) {
            dialog.close();
        }
    }, [open]);

    useEffect(() => {
        const dialog = dialogRef.current;
        if (!dialog) return;

        // Escape closes the dialog natively; tell React so state agrees.
        function handleCancel(event) {
            event.preventDefault();
            onClose();
        }

        dialog.addEventListener('cancel', handleCancel);

        return () => dialog.removeEventListener('cancel', handleCancel);
    }, [onClose]);

    function handleClick(event) {
        // The backdrop is part of the dialog element, so a click whose target
        // IS the dialog (not its content) is a backdrop click.
        if (event.target === dialogRef.current) {
            onClose();
        }
    }

    return (
        <dialog
            ref={dialogRef}
            onClick={handleClick}
            aria-labelledby={titleId}
            aria-describedby={description ? descriptionId : undefined}
            className={`m-auto w-[calc(100%-2rem)] rounded-card border border-line bg-surface-raised p-0 text-ink shadow-overlay backdrop:bg-black/70 ${SIZES[size]}`}
        >
            {/* The dialog itself is capped by the UA at roughly the viewport
                height, so without a scroll container here a tall body (an
                evidence scan plus a stacked footer on a small phone) pushes
                the buttons out of reach — and Escape is not a thing on a
                phone. */}
            <div className="max-h-[85dvh] overflow-y-auto p-5 sm:p-6">
                <h2 id={titleId} className="text-base font-semibold">
                    {title}
                </h2>

                {description && (
                    <p id={descriptionId} className="mt-2 text-sm text-ink-muted">
                        {description}
                    </p>
                )}

                {children && <div className="mt-4">{children}</div>}

                <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    {footer}
                </div>
            </div>
        </dialog>
    );
}

/**
 * Confirmation for an action that cannot be undone. Destructive actions need a
 * deliberate second step, and the confirm button carries its own busy state so
 * it cannot be pressed twice.
 */
export function ConfirmModal({
    open,
    onClose,
    onConfirm,
    title,
    description,
    confirmLabel = 'Confirmar',
    cancelLabel = 'Cancelar',
    processing = false,
    children,
}) {
    return (
        <Modal
            open={open}
            onClose={onClose}
            title={title}
            description={description}
            footer={
                <>
                    <Button variant="ghost" onClick={onClose} disabled={processing}>
                        {cancelLabel}
                    </Button>
                    <Button variant="danger" onClick={onConfirm} loading={processing}>
                        {confirmLabel}
                    </Button>
                </>
            }
        >
            {children}
        </Modal>
    );
}
