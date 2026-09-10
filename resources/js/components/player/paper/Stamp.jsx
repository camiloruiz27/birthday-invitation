/*
 * The BASE danger/success hexes here, not the "-strong" ones.
 *
 * app.css measures the base pair at ~7:1 against Papel, which is exactly the
 * background a stamp sits on; "-strong" exists for text on Carbón and looks
 * washed out on paper. This is the one place in the app where reaching for
 * the base colour is the correct call rather than the documented mistake.
 */
const TONES = {
    ink: 'border-paper-ink text-paper-ink',
    muted: 'border-paper-line text-paper-muted',
    danger: 'border-danger text-danger',
    success: 'border-success text-success',
};

/**
 * `ui/Badge`'s counterpart on paper.
 *
 * Badge is a soft dark pill; a mark on a document is a rubber stamp, so this
 * is square, mono and letterspaced — via the existing `.case-stamp` class
 * rather than reinventing those three rules.
 */
export default function Stamp({ tone = 'ink', className = '', children }) {
    return (
        <span
            className={`case-stamp inline-flex items-center gap-1.5 border px-2 py-0.5 text-[10px] ${TONES[tone]} ${className}`}
        >
            {children}
        </span>
    );
}
