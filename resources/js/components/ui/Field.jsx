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
    return `${CONTROL_BASE} ${error ? 'border-danger' : 'border-line-strong'} ${className}`;
}

function Label({ htmlFor, children, required }) {
    return (
        <label htmlFor={htmlFor} className="block text-sm font-medium text-ink">
            {children}
            {required && (
                <span className="ml-1 text-danger" aria-hidden="true">
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
                <p id={`${id}-error`} className="mt-1.5 text-xs font-medium text-danger">
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
