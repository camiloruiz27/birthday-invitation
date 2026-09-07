import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import { TextField } from '../../components/ui/Field';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email: email || '',
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();
        post(route('password.store'), {
            onFinish: () => {
                setData('password', '');
                setData('password_confirmation', '');
            },
        });
    }

    return (
        <AuthLayout title="Nueva contraseña">
            <Head title="Nueva contraseña" />

            <form onSubmit={submit} className="space-y-5">
                <TextField
                    id="email"
                    label="Correo"
                    type="email"
                    value={data.email}
                    onChange={(value) => setData('email', value)}
                    autoComplete="email"
                    required
                />

                <TextField
                    id="password"
                    label="Contraseña nueva"
                    type="password"
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    hint="Mínimo 8 caracteres."
                    autoComplete="new-password"
                    required
                    autoFocus
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
                    {processing ? 'Guardando…' : 'Guardar contraseña'}
                </Button>
            </form>
        </AuthLayout>
    );
}
