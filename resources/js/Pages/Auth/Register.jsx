import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import Captcha from '../../components/ui/Captcha';
import { CheckboxField, TextField, PasswordField } from '../../components/ui/Field';
import TextLink from '../../components/ui/TextLink';
import { trackAd, trackEvent } from '../../lib/analytics';

export default function Register({ case: caseSlug = null }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        accept_terms: false,
        'cf-turnstile-response': '',
    });

    // Bumped after every rejected attempt so the captcha issues a fresh
    // token: the previous one was spent by the submission that failed.
    const [captchaKey, setCaptchaKey] = useState(0);

    function submit(event) {
        event.preventDefault();
        post(route('register'), {
            onSuccess: (page) => {
                trackEvent('sign_up');

                // Same id the server uses for its own CompleteRegistration
                // (Ads/AdEvents.php), so each ad network counts it once.
                const userId = page?.props?.auth?.user?.id;
                if (userId) trackAd('CompleteRegistration', {}, `reg-${userId}`);
            },
            onFinish: () => {
                setData('password', '');
                setData('password_confirmation', '');
            },
            onError: () => setCaptchaKey((key) => key + 1),
        });
    }

    return (
        <AuthLayout
            title="Crear cuenta"
            description="Para adquirir casos y dirigir tus propias partidas."
            footer={
                <>
                    ¿Ya tienes cuenta?{' '}
                    <TextLink href={route('login', caseSlug ? { case: caseSlug } : {})}>
                        Ingresar
                    </TextLink>
                </>
            }
        >
            <Head title="Crear cuenta" />

            <form onSubmit={submit} className="space-y-5">
                <TextField
                    id="name"
                    label="Nombre"
                    value={data.name}
                    onChange={(value) => setData('name', value)}
                    error={errors.name}
                    autoComplete="name"
                    required
                    autoFocus
                />

                <TextField
                    id="email"
                    label="Correo"
                    type="email"
                    value={data.email}
                    onChange={(value) => setData('email', value)}
                    error={errors.email}
                    autoComplete="email"
                    required
                />

                <PasswordField
                    id="password"
                    label="Contraseña"
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    hint="Mínimo 8 caracteres."
                    autoComplete="new-password"
                    required
                />

                <PasswordField
                    id="password_confirmation"
                    label="Repite la contraseña"
                    value={data.password_confirmation}
                    onChange={(value) => setData('password_confirmation', value)}
                    error={errors.password_confirmation}
                    autoComplete="new-password"
                    required
                />

                <Captcha
                    onToken={(token) => setData('cf-turnstile-response', token)}
                    error={errors['cf-turnstile-response']}
                    resetKey={captchaKey}
                />

                {/* Express authorization (Ley 1581 de 2012): starts unticked
                    and the server refuses the registration without it. The
                    links open in a new tab so the half-filled form survives. */}
                <CheckboxField
                    id="accept_terms"
                    label={
                        <>
                            Acepto los{' '}
                            <TextLink href={route('terms')} external target="_blank" rel="noopener noreferrer">
                                Términos y Condiciones
                            </TextLink>{' '}
                            y autorizo el tratamiento de mis datos personales según la{' '}
                            <TextLink href={route('privacy')} external target="_blank" rel="noopener noreferrer">
                                Política de Privacidad
                            </TextLink>
                            .
                        </>
                    }
                    checked={data.accept_terms}
                    onChange={(value) => setData('accept_terms', value)}
                    error={errors.accept_terms}
                    required
                />

                <Button type="submit" loading={processing} fullWidth>
                    {processing ? 'Creando…' : 'Crear cuenta'}
                </Button>

                <p className="text-xs text-ink-muted">
                    <strong className="font-medium text-ink">Aviso de privacidad.</strong> Usaremos tu
                    nombre y correo para crear y administrar tu cuenta, enviarte los correos del
                    servicio y atender tus compras. Tienes derecho a conocer, actualizar, rectificar y
                    suprimir tus datos y a revocar esta autorización escribiendo al contacto indicado
                    en la Política de Privacidad. No es obligatorio dar datos sensibles.
                </p>

                <p className="text-sm text-ink-muted">
                    Crear una cuenta no incluye ningún caso. Los casos se adquieren por
                    separado y quedan en tu biblioteca de forma permanente. Te enviaremos
                    un correo para confirmar tu dirección: sin confirmarla no podrás
                    comprar ni canjear códigos.
                </p>
            </form>
        </AuthLayout>
    );
}
