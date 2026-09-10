import { useState } from 'react';

/**
 * Label + control + hint + error, wired for assistive tech.
 *
 * The pieces are always associated: the label points at the control, and the
 * hint and error reach it through aria-describedby rather than only sitting
 * next to it visually. Every input in the platform goes through here so that
 * wiring cannot be forgotten one field at a time.
 */

const CONTROL_BASE =
    'mt-1.5 w-full rounded-control border bg-surface-sunken px-3 py-2.5 text-base text-ink ' +
    'placeholder:text-ink-subtle disabled:cursor-not-allowed disabled:opacity-60';

function controlClasses(error, className) {
    // -strong: the base danger hex is calibrated for ~7:1 on Papel, and
    // measures under 3:1 as a border directly on this dark control — see
    // the token comment in app.css.
    return `${CONTROL_BASE} ${error ? 'border-danger-strong' : 'border-line-strong'} ${className}`;
}

function Label({ htmlFor, children, required }) {
    return (
        <label htmlFor={htmlFor} className="block text-sm font-medium text-ink">
            {children}
            {required && (
                <span className="ml-1 text-danger-strong" aria-hidden="true">
                    *
                </span>
            )}
        </label>
    );
}

function Messages({ id, hint, error }) {
    return (
        <>
            {hint && !error && (
                <p id={`${id}-hint`} className="mt-1.5 text-xs text-ink-muted">
                    {hint}
                </p>
            )}
            {error && (
                <p id={`${id}-error`} className="mt-1.5 text-xs font-medium text-danger-strong">
                    {error}
                </p>
            )}
        </>
    );
}

function describedBy(id, hint, error) {
    if (error) return `${id}-error`;
    if (hint) return `${id}-hint`;

    return undefined;
}

export function TextField({
    id,
    label,
    type = 'text',
    value,
    onChange,
    error,
    hint,
    required = false,
    className = '',
    ...props
}) {
    return (
        <div>
            <Label htmlFor={id} required={required}>
                {label}
            </Label>
            <input
                id={id}
                name={id}
                type={type}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                required={required}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy(id, hint, error)}
                className={controlClasses(error, className)}
                {...props}
            />
            <Messages id={id} hint={hint} error={error} />
        </div>
    );
}

/**
 * A password box with a reveal toggle.
 *
 * Typing a password blind on a phone keyboard is where most failed logins
 * come from, so the eye is not a nicety. Three things it has to get right:
 *
 * - The toggle is a real <button> inside the field, not an icon with an
 *   onClick, so it is reachable by keyboard and announces its state.
 * - It is `tabIndex={-1}`: tabbing out of a password box should land on the
 *   next field or the submit button, not on a decoration in between.
 * - Switching `type` keeps the same input element, so the value, the caret
 *   and the password manager's binding all survive the toggle.
 *
 * The field never remembers being revealed: every mount starts hidden.
 */
export function PasswordField({
    id,
    label,
    value,
    onChange,
    error,
    hint,
    required = false,
    className = '',
    ...props
}) {
    const [visible, setVisible] = useState(false);

    return (
        <div>
            <Label htmlFor={id} required={required}>
                {label}
            </Label>

            <div className="relative">
                <input
                    id={id}
                    name={id}
                    type={visible ? 'text' : 'password'}
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    required={required}
                    aria-invalid={error ? 'true' : undefined}
                    aria-describedby={describedBy(id, hint, error)}
                    // pr-12 keeps the text from running under the button.
                    className={`${controlClasses(error, className)} pr-12`}
                    {...props}
                />

                <button
                    type="button"
                    onClick={() => setVisible((shown) => !shown)}
                    aria-pressed={visible}
                    aria-controls={id}
                    tabIndex={-1}
                    className="absolute inset-y-0 right-0 top-1.5 flex w-12 items-center justify-center rounded-r-control text-ink-muted hover:text-ink"
                >
                    <span className="sr-only">
                        {visible ? 'Ocultar la contraseña' : 'Mostrar la contraseña'}
                    </span>
                    <EyeIcon crossed={visible} />
                </button>
            </div>

            <Messages id={id} hint={hint} error={error} />
        </div>
    );
}

function EyeIcon({ crossed }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.5"
            aria-hidden="true"
            className="h-5 w-5"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"
            />
            <circle cx="12" cy="12" r="3" />
            {crossed && <path strokeLinecap="round" d="M4 20L20 4" />}
        </svg>
    );
}

export function TextArea({
    id,
    label,
    value,
    onChange,
    error,
    hint,
    rows = 4,
    required = false,
    className = '',
    ...props
}) {
    return (
        <div>
            <Label htmlFor={id} required={required}>
                {label}
            </Label>
            <textarea
                id={id}
                name={id}
                rows={rows}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                required={required}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy(id, hint, error)}
                className={controlClasses(error, className)}
                {...props}
            />
            <Messages id={id} hint={hint} error={error} />
        </div>
    );
}

export function SelectField({
    id,
    label,
    value,
    onChange,
    options,
    error,
    hint,
    required = false,
    className = '',
    ...props
}) {
    return (
        <div>
            <Label htmlFor={id} required={required}>
                {label}
            </Label>
            <select
                id={id}
                name={id}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                required={required}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy(id, hint, error)}
                className={controlClasses(error, className)}
                {...props}
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            <Messages id={id} hint={hint} error={error} />
        </div>
    );
}

export function CheckboxField({ id, label, checked, onChange, hint }) {
    return (
        <div>
            <label htmlFor={id} className="flex items-start gap-2.5 text-sm text-ink">
                <input
                    id={id}
                    name={id}
                    type="checkbox"
                    checked={checked}
                    onChange={(event) => onChange(event.target.checked)}
                    aria-describedby={hint ? `${id}-hint` : undefined}
                    className="mt-0.5 h-4 w-4 shrink-0 rounded border-line-strong bg-surface-sunken"
                />
                <span>{label}</span>
            </label>
            {hint && (
                <p id={`${id}-hint`} className="mt-1 pl-6.5 text-xs text-ink-muted">
                    {hint}
                </p>
            )}
        </div>
    );
}
