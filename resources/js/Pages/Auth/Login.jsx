import { Head, Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import { TextField, CheckboxField } from '../../components/ui/Field';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(event) {
        event.preventDefault();
        // Never keep the password in form state after an attempt.
        post(route('login'), { onFinish: () => setData('password', '') });
    }

    return (
        <AuthLayout
            title="Ingresar"
            description="Accede a tus casos y a tus partidas."
            footer={
                <>
                    ¿No tienes cuenta?{' '}
                    <Link href={route('register')} className="font-medium text-accent underline">
                        Crear una
                    </Link>
                </>
            }
        >
            <Head title="Ingresar" />

            <form onSubmit={submit} className="space-y-5">
                {/* The credentials failure arrives on `email` but is about the
                    pair, so AuthLayout shows it above the form instead of
                    blaming one field. */}
                <TextField
                    id="email"
                    label="Correo"
                    type="email"
                    value={data.email}
                    onChange={(value) => setData('email', value)}
                    autoComplete="email"
                    required
                    autoFocus
                />

                <TextField
                    id="password"
                    label="Contraseña"
                    type="password"
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    autoComplete="current-password"
                    required
                />

                <div className="flex flex-wrap items-center justify-between gap-3">
                    <CheckboxField
                        id="remember"
                        label="Recordarme"
                        checked={data.remember}
                        onChange={(value) => setData('remember', value)}
                    />

                    <Link
                        href={route('password.request')}
                        className="text-sm text-ink-muted underline hover:text-ink"
                    >
                        Olvidé mi contraseña
                    </Link>
                </div>

                <Button type="submit" loading={processing} fullWidth>
                    {processing ? 'Verificando…' : 'Ingresar'}
                </Button>
            </form>
        </AuthLayout>
    );
}
