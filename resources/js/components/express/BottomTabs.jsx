import { useRef } from 'react';

function Icon({ name }) {
    const paths = {
        clues: (
            <>
                <circle cx="10.5" cy="10.5" r="5.5" />
                <path strokeLinecap="round" d="M15 15l5 5" />
            </>
        ),
        ask: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M4 5h16v11H9l-5 4V5z"
            />
        ),
        accuse: (
            <>
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 3l9 4.5-9 4.5L3 7.5 12 3z" />
                <path strokeLinecap="round" strokeLinejoin="round" d="M6.5 11v4.5c0 1.2 2.5 2.5 5.5 2.5s5.5-1.3 5.5-2.5V11" />
            </>
        ),
        lock: (
            <>
                <rect x="5" y="11" width="14" height="9" rx="2" />
                <path strokeLinecap="round" d="M8 11V8a4 4 0 0 1 8 0v3" />
            </>
        ),
    };

    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.6"
            aria-hidden="true"
            className="h-6 w-6"
        >
            {paths[name]}
        </svg>
    );
}

/**
 * The game's tab bar: fixed to the bottom of the phone, where the thumb is.
 *
 * `tabs` is `[{ key, label, icon, disabled?, hint?, dot? }]`. A disabled tab
 * shows a lock and cannot be selected (accusing before the window opens).
 * `dot` is a small "something new" marker for a tab you are not looking at.
 * It follows the tab pattern (roving tabindex, arrows) and points at the
 * panels the page renders, whose ids are `express-panel-{key}`.
 */
export default function BottomTabs({ tabs, active, onChange, label = 'Secciones del juego' }) {
    const refs = useRef([]);

    function onKeyDown(event) {
        const enabled = tabs.map((tab, index) => (tab.disabled ? null : index)).filter((i) => i !== null);
        const position = enabled.indexOf(tabs.findIndex((tab) => tab.key === active));
        let next = null;

        if (event.key === 'ArrowRight') next = enabled[(position + 1) % enabled.length];
        if (event.key === 'ArrowLeft') next = enabled[(position - 1 + enabled.length) % enabled.length];

        if (next === null || next === undefined) return;

        event.preventDefault();
        onChange(tabs[next].key);
        refs.current[next]?.focus();
    }

    return (
        <nav
            aria-label={label}
            className="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-surface pb-[env(safe-area-inset-bottom)]"
        >
            <div role="tablist" aria-label={label} onKeyDown={onKeyDown} className="mx-auto flex max-w-xl">
                {tabs.map((tab, index) => {
                    const selected = tab.key === active;

                    return (
                        <button
                            key={tab.key}
                            ref={(node) => {
                                refs.current[index] = node;
                            }}
                            type="button"
                            role="tab"
                            id={`express-tab-${tab.key}`}
                            aria-selected={selected}
                            aria-controls={`express-panel-${tab.key}`}
                            tabIndex={selected ? 0 : -1}
                            disabled={tab.disabled}
                            onClick={() => onChange(tab.key)}
                            className={`relative flex min-h-16 flex-1 flex-col items-center justify-center gap-1 px-2 text-xs transition-colors disabled:cursor-not-allowed disabled:opacity-50 ${
                                selected ? 'font-semibold text-accent-strong' : 'text-ink-muted'
                            }`}
                        >
                            <span
                                aria-hidden="true"
                                className={`absolute inset-x-6 top-0 h-0.5 rounded-full ${
                                    selected ? 'bg-accent' : 'bg-transparent'
                                }`}
                            />
                            <span className="relative">
                                <Icon name={tab.disabled ? 'lock' : tab.icon} />
                                {tab.dot && !selected && (
                                    <span
                                        className="absolute -right-1 -top-0.5 h-2.5 w-2.5 rounded-full border-2 border-surface bg-accent"
                                        role="img"
                                        aria-label="Novedad"
                                    />
                                )}
                            </span>
                            <span>{tab.label}</span>
                            {tab.disabled && tab.hint && <span className="sr-only"> ({tab.hint})</span>}
                        </button>
                    );
                })}
            </div>
        </nav>
    );
}
