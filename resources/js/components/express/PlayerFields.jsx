import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import { TextField, CheckboxField } from '../ui/Field';

const CONTACT_TYPES = [
    { value: 'none', label: 'Sin contacto' },
    { value: 'email', label: 'Correo' },
    { value: 'whatsapp', label: 'WhatsApp' },
];

/**
 * The fields Create and Join share: a nickname, an optional contact and, only
 * when a contact is given, the two checkboxes the law asks for (authorization
 * to be contacted, and being of age). Plus a honeypot: a field no person sees
 * and every form-filling bot fills, which the server rejects.
 *
 * `data`/`setData`/`errors` come straight from Inertia's `useForm`.
 */
export default function PlayerFields({ data, setData, errors, consent }) {
    const hasContact = data.contact_type !== 'none';

    function changeType(value) {
        setData((current) => ({
            ...current,
            contact_type: value,
            contact: '',
            consent: value === 'none' ? false : current.consent,
            adult: value === 'none' ? false : current.adult,
        }));
    }

    return (
        <>
            <TextField
                id="nickname"
                label="Tu apodo"
                value={data.nickname}
                onChange={(value) => setData('nickname', value)}
                error={errors.nickname}
                hint="Así te verán los demás jugadores."
                maxLength={24}
                autoComplete="nickname"
                autoCapitalize="words"
                enterKeyHint="next"
                required
            />

            <fieldset>
                <legend className="text-sm font-medium text-ink">
                    Contacto <span className="font-normal text-ink-muted">(opcional)</span>
                </legend>
                <p className="mt-1 text-xs text-ink-muted">
                    Déjalo si quieres que te escribamos sobre los casos completos. Puedes jugar sin dejarlo.
                </p>

                <div className="mt-2.5 grid grid-cols-3 gap-1.5 rounded-full border border-line-strong bg-surface-sunken p-1">
                    {CONTACT_TYPES.map((type) => {
                        const selected = data.contact_type === type.value;

                        return (
                            <label key={type.value} className="block cursor-pointer">
                                <input
                                    type="radio"
                                    name="contact_type"
                                    value={type.value}
                                    checked={selected}
                                    onChange={() => changeType(type.value)}
                                    className="peer sr-only"
                                />
                                <span
                                    className={`flex min-h-10 items-center justify-center rounded-full px-2 text-center text-sm transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent ${
                                        selected
                                            ? 'bg-accent font-semibold text-ink-inverse'
                                            : 'text-ink-muted hover:text-ink'
                                    }`}
                                >
                                    {type.label}
                                </span>
                            </label>
                        );
                    })}
                </div>
                {errors.contact_type && (
                    <p className="mt-1.5 text-xs font-medium text-danger-strong">{errors.contact_type}</p>
                )}
            </fieldset>

            {hasContact && (
                <>
                    <TextField
                        id="contact"
                        label={data.contact_type === 'email' ? 'Tu correo' : 'Tu WhatsApp'}
                        type={data.contact_type === 'email' ? 'email' : 'tel'}
                        inputMode={data.contact_type === 'email' ? 'email' : 'tel'}
                        autoComplete={data.contact_type === 'email' ? 'email' : 'tel'}
                        placeholder={data.contact_type === 'email' ? 'tu@correo.com' : '300 123 4567'}
                        value={data.contact}
                        onChange={(value) => setData('contact', value)}
                        error={errors.contact}
                        required
                    />

                    <div>
                        <CheckboxField
                            id="consent"
                            checked={data.consent}
                            onChange={(value) => setData('consent', value)}
                            error={errors.consent}
                            required
                            label={
                                <>
                                    {consent?.text ?? 'Autorizo el tratamiento de mis datos para que me contacten.'}{' '}
                                    <a
                                        href={route('privacy')}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="font-medium text-accent underline underline-offset-2 hover:text-accent-strong"
                                    >
                                        Política de privacidad
                                    </a>
                                </>
                            }
                        />
                        <CheckboxField
                            id="adult"
                            checked={data.adult}
                            onChange={(value) => setData('adult', value)}
                            error={errors.adult}
                            required
                            label="Soy mayor de 18 años."
                        />
                    </div>
                </>
            )}

            {/* Honeypot: off-screen, out of the tab order, hidden from assistive
                tech. A person never sees it; a bot fills it and is turned away. */}
            <div aria-hidden="true" className="absolute -left-[9999px] h-0 w-0 overflow-hidden">
                <label>
                    No llenes este campo
                    <input
                        type="text"
                        name="website"
                        tabIndex={-1}
                        autoComplete="off"
                        value={data.website}
                        onChange={(event) => setData('website', event.target.value)}
                    />
                </label>
            </div>
        </>
    );
}

/** Starting values for the shared fields. */
export const PLAYER_FIELD_DEFAULTS = {
    nickname: '',
    contact_type: 'none',
    contact: '',
    consent: false,
    adult: false,
    website: '',
};

/**
 * What to say when the server refuses the whole POST rather than a field:
 * too many requests from a shared fair Wi-Fi (429) or a stale session (419).
 * Inertia would otherwise open the raw error page in a modal. Returns the
 * message to show (or null) and clears it on the next attempt.
 */
export function useRefusalNotice() {
    const [message, setMessage] = useState(null);

    useEffect(
        () =>
            router.on('invalid', (event) => {
                const status = event.detail?.response?.status;

                if (status === 429) {
                    event.preventDefault();
                    setMessage('Hay mucha gente jugando ahora mismo. Espera unos segundos e inténtalo de nuevo.');
                } else if (status === 419) {
                    event.preventDefault();
                    setMessage('La página caducó. Recárgala e inténtalo de nuevo.');
                }
            }),
        []
    );

    return [message, () => setMessage(null)];
}
