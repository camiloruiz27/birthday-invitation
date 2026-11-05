import Badge from '../ui/Badge';

export const CASE_TAG_LABELS = {
    halloween: 'Halloween',
};

export default function CaseTags({ mysteryCase, className = '', limit = null }) {
    const tags = (mysteryCase.tags ?? [])
        .filter((tag) => !/^ideal\s*:/i.test(String(tag)))
        .map((tag) => CASE_TAG_LABELS[tag] ?? tag)
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
