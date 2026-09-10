/**
 * One sheet of paper lying on the dark desk.
 *
 * `ui/Card` is the platform's panel — round, dark, soft. A case document is
 * none of those, so it cannot reuse it: this is Card's counterpart on the
 * fiction surface. Square corners, paper fill, a hard ink border, and a
 * shadow whose only job is to make the sheet read as lying ON the desk
 * rather than as a hole cut into it.
 *
 * Applying `.case-surface` here (rather than on the layout, which is where
 * it used to live) is what carries Courier Prime and the paper focus ring
 * into the document and nowhere else — the whole point of the dark-desk
 * redesign is that paper marks a document, not a screen.
 *
 * `padded` defaults to false: a sheet usually holds its own header/body rows
 * that pad themselves.
 */
export default function Sheet({
    as: Tag = 'div',
    padded = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Tag
            className={`case-surface border-2 border-paper-ink shadow-sheet ${
                padded ? 'px-4 py-4 sm:px-6' : ''
            } ${className}`}
            {...props}
        >
            {children}
        </Tag>
    );
}

/**
 * The sheet's masthead: an inked strip with a stamp line over a title — the
 * shape every case document in this app already uses.
 */
export function SheetHeader({ kicker, title, actions, className = '' }) {
    return (
        <div
            className={`flex flex-wrap items-start justify-between gap-3 border-b-2 border-paper-ink bg-paper-ink px-4 py-3 text-paper sm:px-6 ${className}`}
        >
            <div className="min-w-0">
                {kicker && <p className="case-stamp text-[10px] text-paper-accent">{kicker}</p>}
                <h2 className="mt-0.5 font-bold">{title}</h2>
            </div>

            {actions && <div className="flex shrink-0 flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
