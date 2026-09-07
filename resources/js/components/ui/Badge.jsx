const TONES = {
    neutral: 'bg-surface-sunken text-ink-muted border-line',
    accent: 'bg-accent-dim text-accent-strong border-accent-dim',
    success: 'bg-success-dim text-success border-success-dim',
    danger: 'bg-danger-dim text-danger border-danger-dim',
    warning: 'bg-accent-dim text-warning border-accent-dim',
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
