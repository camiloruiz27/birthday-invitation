import { useEffect, useRef } from 'react';
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
export default function Modal({ open, onClose, title, description, children, footer }) {
    const dialogRef = useRef(null);

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
            aria-labelledby="modal-title"
            aria-describedby={description ? 'modal-description' : undefined}
            className="m-auto w-[calc(100%-2rem)] max-w-md rounded-card border border-line bg-surface-raised p-0 text-ink shadow-overlay backdrop:bg-black/70"
        >
            <div className="p-5 sm:p-6">
                <h2 id="modal-title" className="text-base font-semibold">
                    {title}
                </h2>

                {description && (
                    <p id="modal-description" className="mt-2 text-sm text-ink-muted">
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
