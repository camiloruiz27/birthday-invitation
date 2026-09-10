import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import Captcha from '../../components/ui/Captcha';
import { TextField } from '../../components/ui/Field';
import TextLink from '../../components/ui/TextLink';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        'cf-turnstile-response': '',
    });

    // Bumped after every rejected attempt so the captcha issues a fresh
    // token: the previous one was spent by the submission that failed.
    const [captchaKey, setCaptchaKey] = useState(0);

    function submit(event) {
        event.preventDefault();
        post(route('password.email'), {
            onError: () => setCaptchaKey((key) => key + 1),
        });
    }

    return (
        <AuthLayout
            title="Recuperar contraseña"
            description="Te enviamos un enlace para elegir una contraseña nueva."
            footer={
                <TextLink href={route('login')}>
                    Volver a ingresar
                </TextLink>
            }
        >
            <Head title="Recuperar contraseña" />

            <form onSubmit={submit} className="space-y-5">
                <TextField
                    id="email"
                    label="Correo"
                    type="email"
                    value={data.email}
                    onChange={(value) => setData('email', value)}
                    error={errors.email}
                    autoComplete="email"
                    required
                    autoFocus
                />

                <Captcha
                    onToken={(token) => setData('cf-turnstile-response', token)}
                    error={errors['cf-turnstile-response']}
                    resetKey={captchaKey}
                />

                <Button type="submit" loading={processing} fullWidth>
                    {processing ? 'Enviando…' : 'Enviar enlace'}
                </Button>
            </form>
        </AuthLayout>
    );
}
