import { Head, Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import { TextField } from '../../components/ui/Field';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    function submit(event) {
        event.preventDefault();
        post(route('password.email'));
    }

    return (
        <AuthLayout
            title="Recuperar contraseña"
            description="Te enviamos un enlace para elegir una contraseña nueva."
            footer={
                <Link href={route('login')} className="underline hover:text-ink">
                    Volver a ingresar
                </Link>
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

                <Button type="submit" loading={processing} fullWidth>
                    {processing ? 'Enviando…' : 'Enviar enlace'}
                </Button>
            </form>
        </AuthLayout>
    );
}
