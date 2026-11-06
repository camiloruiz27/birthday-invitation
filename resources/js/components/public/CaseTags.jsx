import Badge from '../ui/Badge';

export const CASE_TAG_LABELS = {
    halloween: 'Halloween',
};

// Discovery tags remain available to search and filters, but cards only show
// time-sensitive campaign labels. This keeps the catalog visually quiet while
// still letting seasonal cases stand out when their moment arrives.
const VISIBLE_CASE_TAGS = new Set(['halloween']);

export default function CaseTags({ mysteryCase, className = '', limit = null }) {
    const tags = (mysteryCase.tags ?? [])
        .filter((tag) => !/^ideal\s*:/i.test(String(tag)))
        .map((tag) => String(tag).trim())
        .filter((tag) => VISIBLE_CASE_TAGS.has(tag.toLowerCase()))
        .map((tag) => CASE_TAG_LABELS[tag.toLowerCase()] ?? tag)
        .filter(Boolean)
        .slice(0, limit ?? undefined);

    if (tags.length === 0) return null;

    return (
        <div className={`flex flex-wrap gap-2 ${className}`} aria-label="Etiquetas del caso">
            {tags.map((tag) => (
                <Badge key={tag} tone="warning">{tag}</Badge>
            ))}
        </div>
    );
}
