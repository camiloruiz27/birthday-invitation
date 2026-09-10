import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import Captcha from '../../components/ui/Captcha';
import { TextField, PasswordField } from '../../components/ui/Field';
import TextLink from '../../components/ui/TextLink';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        'cf-turnstile-response': '',
    });

    // Bumped after every rejected attempt so the captcha issues a fresh
    // token: the previous one was spent by the submission that failed.
    const [captchaKey, setCaptchaKey] = useState(0);

    function submit(event) {
        event.preventDefault();
        post(route('register'), {
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
                    <TextLink href={route('login')}>
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

                <Button type="submit" loading={processing} fullWidth>
                    {processing ? 'Creando…' : 'Crear cuenta'}
                </Button>

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
