/**
 * A heading above a block that is not inside a Card.
 *
 * `CardHeader` covers headings inside a panel; there was nothing for a
 * heading above a grid or a list, so seven places invented one and settled
 * on three different spellings of it. `components/public/Section.jsx` is the
 * marketing surface's equivalent — this is the console's.
 *
 * `as` exists because heading level is a document-structure decision the
 * page owns, not something a shared component should pick: most uses are h2
 * under the layout's h1, but a nested block may need h3.
 */
export default function SectionHeader({
    as: Tag = 'h2',
    title,
    description,
    actions,
    className = '',
}) {
    return (
        <div className={`mb-4 flex flex-wrap items-end justify-between gap-3 ${className}`}>
            <div className="min-w-0">
                <Tag className="text-base font-semibold text-ink">{title}</Tag>
                {description && <p className="mt-1 text-sm text-ink-muted">{description}</p>}
            </div>

            {actions && <div className="flex shrink-0 flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
