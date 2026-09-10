// The "-strong" text colours, not the base danger/success/warning: those are
// calibrated for ~7:1 on Papel (see app.css) and measure under 2:1 as small
// text on their own dim fill here — the "-strong" variants are the ones
// actually verified against a dark badge background.
const TONES = {
    neutral: 'bg-surface-sunken text-ink-muted border-line',
    accent: 'bg-accent-dim text-accent-strong border-accent-dim',
    success: 'bg-success-dim text-success-strong border-success-dim',
    danger: 'bg-danger-dim text-danger-strong border-danger-dim',
    warning: 'bg-accent-dim text-warning-strong border-accent-dim',
};

/** Maps a game's status to a tone, so the same state never reads two ways. */
export const GAME_STATUS_TONE = {
    draft: 'neutral',
    running: 'success',
    paused: 'warning',
    finished: 'accent',
};

export default function Badge({ tone = 'neutral', className = '', children }) {
    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium ${TONES[tone]} ${className}`}
        >
            {children}
        </span>
    );
}
