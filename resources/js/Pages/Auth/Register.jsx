import { Head, Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import { TextField } from '../../components/ui/Field';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();
        post(route('register'), {
            onFinish: () => {
                setData('password', '');
                setData('password_confirmation', '');
            },
        });
    }

    return (
        <AuthLayout
            title="Crear cuenta"
            description="Para adquirir casos y dirigir tus propias partidas."
            footer={
                <>
                    ¿Ya tienes cuenta?{' '}
                    <Link href={route('login')} className="font-medium text-accent underline">
                        Ingresar
                    </Link>
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

                <TextField
                    id="password"
                    label="Contraseña"
                    type="password"
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    hint="Mínimo 8 caracteres."
                    autoComplete="new-password"
                    required
                />

                <TextField
                    id="password_confirmation"
                    label="Repite la contraseña"
                    type="password"
                    value={data.password_confirmation}
                    onChange={(value) => setData('password_confirmation', value)}
                    error={errors.password_confirmation}
                    autoComplete="new-password"
                    required
                />

                <Button type="submit" loading={processing} fullWidth>
                    {processing ? 'Creando…' : 'Crear cuenta'}
                </Button>

                <p className="text-xs text-ink-muted">
                    Crear una cuenta no incluye ningún caso. Los casos se adquieren por
                    separado y quedan en tu biblioteca de forma permanente.
                </p>
            </form>
        </AuthLayout>
    );
}
