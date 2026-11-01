import Badge from '../ui/Badge';

export const CASE_TAG_LABELS = {
    halloween: 'Halloween',
};

export default function CaseTags({ mysteryCase, className = '' }) {
    const tags = (mysteryCase.tags ?? [])
        .map((tag) => CASE_TAG_LABELS[tag] ?? tag)
        .filter(Boolean);

    if (tags.length === 0) return null;

    return (
        <div className={`flex flex-wrap gap-2 ${className}`} aria-label="Etiquetas del caso">
            {tags.map((tag) => (
                <Badge key={tag} tone="warning">{tag}</Badge>
            ))}
        </div>
    );
}
