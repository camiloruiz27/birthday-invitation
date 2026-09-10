import { useId, useRef, useState } from 'react';

/**
 * Tabbed panels.
 *
 * Self-contained on purpose: the tab list, the panels and the aria wiring
 * that ties them together all live here, the same reason every input goes
 * through Field.jsx. A page that hand-rolls a row of buttons over a
 * conditional render loses `aria-controls`, the roving tab order and the
 * arrow keys, and nobody notices until someone tries to use it without a
 * mouse.
 *
 * Automatic activation (arrow key both moves and selects) rather than
 * manual: the panels here hold already-loaded content, so there is nothing
 * to pay for by selecting as you arrow through.
 *
 * `tabs` is `[{ key, label, badge?, content }]`. Controlled if you pass
 * `active` + `onChange`, uncontrolled otherwise.
 */
export default function Tabs({
    tabs,
    active,
    onChange,
    label = 'Secciones',
    // Three tabs already overflow 360px, and wrapping leaves one pill
    // stranded on a second row above the content. A strip that scrolls
    // sideways keeps the chrome one row tall on a phone.
    scrollable = false,
    className = '',
    listClassName = '',
}) {
    const base = useId().replace(/:/g, '');
    const buttonsRef = useRef([]);
    const [internal, setInternal] = useState(tabs[0]?.key);

    const current = active ?? internal;
    const currentIndex = Math.max(
        0,
        tabs.findIndex((tab) => tab.key === current)
    );

    const select = (key) => {
        if (onChange) {
            onChange(key);
        } else {
            setInternal(key);
        }
    };

    const onKeyDown = (event) => {
        const keys = { ArrowRight: 1, ArrowLeft: -1 };
        let next = null;

        if (event.key in keys) {
            next = (currentIndex + keys[event.key] + tabs.length) % tabs.length;
        } else if (event.key === 'Home') {
            next = 0;
        } else if (event.key === 'End') {
            next = tabs.length - 1;
        }

        if (next === null) {
            return;
        }

        event.preventDefault();
        select(tabs[next].key);
        buttonsRef.current[next]?.focus();
    };

    return (
        <div className={className}>
            <div
                role="tablist"
                aria-label={label}
                onKeyDown={onKeyDown}
                className={`flex gap-2 ${
                    scrollable
                        ? 'no-scrollbar -mx-4 flex-nowrap overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0'
                        : 'flex-wrap'
                } ${listClassName}`}
            >
                {tabs.map((tab, index) => {
                    const selected = tab.key === current;

                    return (
                        <button
                            key={tab.key}
                            ref={(node) => {
                                buttonsRef.current[index] = node;
                            }}
                            type="button"
                            role="tab"
                            id={`${base}-tab-${tab.key}`}
                            aria-selected={selected}
                            aria-controls={`${base}-panel-${tab.key}`}
                            // Roving tab order: one stop for the whole list,
                            // then arrows move within it.
                            tabIndex={selected ? 0 : -1}
                            onClick={() => select(tab.key)}
                            className={`inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full border px-4 py-1.5 text-sm transition-colors ${
                                selected
                                    ? 'border-accent bg-accent-dim font-semibold text-accent-strong'
                                    : 'border-line-strong text-ink-muted hover:border-accent hover:text-ink'
                            }`}
                        >
                            {tab.label}
                            {tab.badge != null && (
                                <span className="tabular text-xs text-ink-subtle">{tab.badge}</span>
                            )}
                        </button>
                    );
                })}
            </div>

            {tabs.map((tab) => (
                <div
                    key={tab.key}
                    role="tabpanel"
                    id={`${base}-panel-${tab.key}`}
                    aria-labelledby={`${base}-tab-${tab.key}`}
                    hidden={tab.key !== current}
                    className="mt-5"
                >
                    {tab.content}
                </div>
            ))}
        </div>
    );
}
