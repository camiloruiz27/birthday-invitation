import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import { TextField } from '../../components/ui/Field';

function ProfileDetails({ user }) {
    const { data, setData, patch, processing, errors } = useForm({
        name: user.name,
        email: user.email,
    });

    function submit(event) {
        event.preventDefault();
        patch(route('profile.update'), { preserveScroll: true });
    }

    return (
        <Card as="section">
            <CardHeader title="Tus datos" />

            <form onSubmit={submit} className="space-y-5">
                <TextField
                    id="name"
                    label="Nombre"
                    value={data.name}
                    onChange={(value) => setData('name', value)}
                    error={errors.name}
                    autoComplete="name"
                    required
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

                <Button type="submit" loading={processing}>
                    {processing ? 'Guardando…' : 'Guardar cambios'}
                </Button>
            </form>
        </Card>
    );
}

function PasswordSection() {
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();
        put(route('profile.password'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            // A rejected current password must not leave the old value sitting
            // in the field.
            onError: () => reset('current_password'),
        });
    }

    return (
        <Card as="section">
            <CardHeader
                title="Contraseña"
                description="Necesitas tu contraseña actual para cambiarla."
            />

            <form onSubmit={submit} className="space-y-5">
                <TextField
                    id="current_password"
                    label="Contraseña actual"
                    type="password"
                    value={data.current_password}
                    onChange={(value) => setData('current_password', value)}
                    error={errors.current_password}
                    autoComplete="current-password"
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
                />

                <TextField
                    id="password_confirmation"
                    label="Repite la contraseña nueva"
                    type="password"
                    value={data.password_confirmation}
                    onChange={(value) => setData('password_confirmation', value)}
                    error={errors.password_confirmation}
                    autoComplete="new-password"
                    required
                />

                <Button type="submit" loading={processing}>
                    {processing ? 'Guardando…' : 'Cambiar contraseña'}
                </Button>
            </form>
        </Card>
    );
}

export default function Edit() {
    const { auth } = usePage().props;

    return (
        <AppLayout kicker="Cuenta" title="Tu perfil" width="prose">
            <Head title="Tu perfil" />

            <div className="space-y-6">
                <ProfileDetails user={auth.user} />
                <PasswordSection />
            </div>
        </AppLayout>
    );
}
