import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Button from '../../components/ui/Button';
import Alert from '../../components/ui/Alert';
import TextLink from '../../components/ui/TextLink';

/**
 * Where someone lands when they try to buy with an address they have not
 * confirmed yet — and, right after registering, where the link in the mail
 * eventually brings them back from.
 *
 * Written for the first of those two, because that is the one that arrives as
 * an interruption: the visitor was on their way to pay, and the page has to
 * explain why it stopped them without sounding like an error.
 */
export default function VerifyEmail({ email, sent }) {
    const { post, processing } = useForm({});

    function resend(event) {
        event.preventDefault();
        post(route('verification.send'));
    }

    return (
        <AuthLayout
            title="Confirma tu correo"
            description="Solo hace falta una vez, y desbloquea las compras de tu cuenta."
            footer={
                <TextLink href={route('dashboard')}>
                    Volver al panel
                </TextLink>
            }
        >
            <Head title="Confirma tu correo" />

            <div className="space-y-5">
                {sent && (
                    <Alert variant="status">
                        Listo, te enviamos el enlace otra vez. Puede tardar un par de
                        minutos en llegar.
                    </Alert>
                )}

                <p className="text-sm text-ink-muted">
                    Enviamos un enlace a <strong className="text-ink">{email}</strong>.
                    Ábrelo para confirmar que esa dirección es tuya.
                </p>

                <p className="text-sm text-ink-muted">
                    Te lo pedimos antes de una compra porque a ese correo llegan el
                    comprobante, los enlaces de tus jugadores y la recuperación de tu
                    contraseña. Si la dirección tiene un error, un pago se pierde en un
                    buzón que no puedes abrir.
                </p>

                <p className="text-sm text-ink-muted">
                    ¿No te llegó? Revisa la carpeta de spam, o pídelo de nuevo. Si la
                    dirección quedó mal escrita, puedes corregirla desde{' '}
                    <TextLink href={route('profile.edit')}>
                        tu perfil
                    </TextLink>
                    .
                </p>

                <form onSubmit={resend}>
                    <Button type="submit" loading={processing} fullWidth>
                        {processing ? 'Enviando…' : 'Enviar el enlace otra vez'}
                    </Button>
                </form>
            </div>
        </AuthLayout>
    );
}
