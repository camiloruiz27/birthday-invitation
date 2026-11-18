import { Head } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import TextLink from '../../components/ui/TextLink';

/**
 * What the one-click link in a promotional mail lands on. Public and short:
 * the person has already left by the time this renders, so it only confirms,
 * says what still arrives, and how to come back.
 */
export default function Unsubscribed() {
    return (
        <AuthLayout
            title="Listo, no te enviaremos más ofertas"
            description="Dejamos de escribirte con descuentos y beneficios. Los correos de tu cuenta y de tus partidas, como la confirmación de tu correo o los enlaces de acceso, se siguen enviando."
            footer={
                <>
                    ¿Fue sin querer? Puedes volver a activarlas desde{' '}
                    <TextLink href={route('profile.edit')}>tu perfil</TextLink>.
                </>
            }
        >
            <Head title="Baja de ofertas" />

            <TextLink href={route('home')}>Volver al inicio</TextLink>
        </AuthLayout>
    );
}
