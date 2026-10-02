import { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import TextLink from '../../components/ui/TextLink';
import { TextField, PasswordField } from '../../components/ui/Field';
import { openCookieSettings } from '../../lib/consent';

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
                <PasswordField
                    id="current_password"
                    label="Contraseña actual"
                    value={data.current_password}
                    onChange={(value) => setData('current_password', value)}
                    error={errors.current_password}
                    autoComplete="current-password"
                    required
                />

                <PasswordField
                    id="password"
                    label="Contraseña nueva"
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    hint="Mínimo 8 caracteres."
                    autoComplete="new-password"
                    required
                />

                <PasswordField
                    id="password_confirmation"
                    label="Repite la contraseña nueva"
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

/**
 * Where a signed-in user finds the legal texts and can change the cookie
 * choice they made on the banner — withdrawing consent has to be as easy as
 * giving it.
 */
function PrivacySection() {
    return (
        <Card as="section">
            <CardHeader
                title="Privacidad y cookies"
                description="Cómo tratamos tus datos y cómo puedes ejercer tus derechos."
            />

            <ul className="space-y-2 text-sm">
                <li>
                    <TextLink href={route('privacy')}>Política de Privacidad</TextLink>
                </li>
                <li>
                    <TextLink href={route('terms')}>Términos y Condiciones</TextLink>
                </li>
                <li>
                    <TextLink href={route('cookies')}>Política de Cookies</TextLink>
                </li>
            </ul>

            <Button variant="secondary" onClick={openCookieSettings} className="mt-5">
                Preferencias de cookies
            </Button>
        </Card>
    );
}

/**
 * The right to have your data erased (Ley 1581 de 2012, art. 8), as a button
 * rather than an email to the owner. Asks for the password, and says up front
 * what is lost and what the law makes us keep.
 */
function DeleteAccountSection() {
    const [confirming, setConfirming] = useState(false);
    const { data, setData, delete: destroy, processing, errors, reset } = useForm({ password: '' });

    function submit(event) {
        event.preventDefault();
        destroy(route('profile.destroy'), {
            onError: () => setData('password', ''),
            onFinish: () => reset('password'),
        });
    }

    return (
        <Card as="section">
            <CardHeader
                title="Eliminar cuenta"
                description="Borra tus datos personales de la plataforma."
            />

            <p className="text-sm text-ink-muted">
                Se eliminan tu cuenta, tu biblioteca de casos, tus créditos, tus partidas y los
                datos de los jugadores que inscribiste. No se puede deshacer y no hay reembolso
                de lo ya comprado, salvo el derecho de retracto. Conservamos únicamente los soportes
                de tus pagos, desvinculados de tu cuenta, porque la ley contable nos obliga.
            </p>

            {confirming ? (
                <form onSubmit={submit} className="mt-5 space-y-4">
                    <PasswordField
                        id="delete_password"
                        label="Confirma con tu contraseña"
                        value={data.password}
                        onChange={(value) => setData('password', value)}
                        error={errors.password}
                        autoComplete="current-password"
                        required
                    />

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" variant="danger" loading={processing}>
                            {processing ? 'Eliminando…' : 'Eliminar mi cuenta definitivamente'}
                        </Button>
                        <Button variant="ghost" onClick={() => setConfirming(false)}>
                            Cancelar
                        </Button>
                    </div>
                </form>
            ) : (
                <Button variant="danger" onClick={() => setConfirming(true)} className="mt-5">
                    Eliminar mi cuenta
                </Button>
            )}
        </Card>
    );
}

export default function Edit() {
    const { auth } = usePage().props;

    return (
        <AppLayout kicker="Cuenta" title="Tu perfil" width="prose" current="profile">
            <Head title="Tu perfil" />

            <div className="space-y-6">
                <ProfileDetails user={auth.user} />
                <PasswordSection />
                <PrivacySection />
                <DeleteAccountSection />
            </div>
        </AppLayout>
    );
}
