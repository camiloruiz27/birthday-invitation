import { Head } from '@inertiajs/react';
import PublicLayout from '../../../Layouts/PublicLayout';
import LegalDocument, { Mail } from '../../../components/public/LegalDocument';
import Button from '../../../components/ui/Button';
import TextLink from '../../../components/ui/TextLink';
import { openCookieSettings } from '../../../lib/consent';

const NECESSARY = [
    {
        name: 'misteriocode_session',
        provider: 'MisterioCode',
        purpose: 'Mantiene su sesión iniciada y los avisos temporales entre páginas.',
        duration: 'Hasta 2 horas de inactividad',
    },
    {
        name: 'XSRF-TOKEN',
        provider: 'MisterioCode',
        purpose: 'Protege los formularios contra solicitudes falsificadas.',
        duration: 'Hasta 2 horas',
    },
    {
        name: 'remember_web_*',
        provider: 'MisterioCode',
        purpose: 'Solo si marca "Recordarme": lo mantiene conectado en este dispositivo.',
        duration: 'Hasta 5 años',
    },
    {
        name: 'mc_consent',
        provider: 'MisterioCode',
        purpose: 'Recuerda su elección sobre las cookies.',
        duration: '6 meses',
    },
    {
        name: 'mc_tour',
        provider: 'MisterioCode',
        purpose:
            'Recuerda que ya vio la guía de bienvenida de la plataforma o de una partida, para no mostrársela otra vez.',
        duration: '1 año',
    },
    {
        name: 'localStorage / sessionStorage',
        provider: 'MisterioCode',
        purpose: 'Recuerda en su navegador qué documentos de la partida ya abrió.',
        duration: 'Hasta que borre los datos del navegador',
    },
    {
        name: 'Cloudflare Turnstile',
        provider: 'Cloudflare',
        purpose: 'Verificación anti-bots en registro, ingreso y canje de códigos.',
        duration: 'Temporal, según Cloudflare',
    },
];

const AUDIENCE = [
    {
        name: '_ga, _ga_*',
        provider: 'Google Analytics',
        purpose:
            'Distingue visitantes y une sus visitas para medir cuánto tiempo están, cuántas páginas ven y si se van rápido.',
        duration: '2 años',
    },
];

const RECORDING = [
    {
        name: '_clck, _clsk, CLID',
        provider: 'Microsoft Clarity',
        purpose:
            'Siguen una sesión entre páginas para grabar el uso (movimiento del cursor, toques, desplazamiento) y generar mapas de calor.',
        duration: '1 día a 1 año',
    },
];

const MARKETING = [
    {
        name: '_fbp, _fbc',
        provider: 'Meta (Facebook e Instagram)',
        purpose:
            'Reconocen su navegador y, si llegó desde un anuncio de Meta, cuál fue, para medir cuántos anuncios terminan en un registro o una compra.',
        duration: 'Hasta 3 meses',
    },
    {
        name: '_ttp, ttclid',
        provider: 'TikTok',
        purpose:
            'Reconocen su navegador y, si llegó desde un anuncio de TikTok, cuál fue, para medir cuántos anuncios terminan en un registro o una compra.',
        duration: 'Hasta 13 meses',
    },
];

function CookieTable({ rows }) {
    return (
        <div className="mt-3 overflow-x-auto rounded-card border border-line">
            <table className="w-full min-w-[34rem] text-left text-sm">
                <thead className="bg-surface-sunken text-xs uppercase tracking-wider text-ink-subtle">
                    <tr>
                        <th scope="col" className="px-4 py-2.5 font-medium">Nombre</th>
                        <th scope="col" className="px-4 py-2.5 font-medium">Proveedor</th>
                        <th scope="col" className="px-4 py-2.5 font-medium">Finalidad</th>
                        <th scope="col" className="px-4 py-2.5 font-medium">Duración</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-line">
                    {rows.map((row) => (
                        <tr key={row.name}>
                            <td className="px-4 py-3 align-top font-mono text-xs text-ink">{row.name}</td>
                            <td className="px-4 py-3 align-top">{row.provider}</td>
                            <td className="px-4 py-3 align-top">{row.purpose}</td>
                            <td className="px-4 py-3 align-top">{row.duration}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

/**
 * Política de Cookies. The banner (components/public/CookieConsent.jsx) links
 * here, and the button below reopens its settings panel.
 */
export default function Cookies({ legal }) {
    const sections = [
        {
            id: 'que-son',
            title: 'Qué son las cookies',
            body: (
                <p>
                    Son pequeños archivos o datos que el sitio guarda en su navegador. Sirven para
                    recordar su sesión, proteger los formularios o, si usted lo permite, medir cómo se
                    usa el sitio. Algunas tecnologías equivalentes, como el almacenamiento local del
                    navegador, se tratan aquí igual.
                </p>
            ),
        },
        {
            id: 'consentimiento',
            title: 'Los cuatro niveles y su autorización',
            body: (
                <>
                    <p>
                        La Ley 1581 de 2012 exige su autorización previa, expresa e informada para
                        tratar datos personales, y la Superintendencia de Industria y Comercio
                        considera que las cookies que permiten identificar o seguir a un usuario
                        quedan cubiertas. Por eso medimos el uso del sitio y de nuestra publicidad en
                        cuatro niveles:
                    </p>
                    <ul>
                        <li>
                            <strong>Necesarias:</strong> siempre activas, porque sin ellas el sitio no
                            funciona. No requieren autorización.
                        </li>
                        <li>
                            <strong>Medición básica anónima, sin cookies:</strong> activa para todos y
                            la informamos aquí y en el aviso de cookies. No usa cookies ni
                            identificadores, no lo sigue de una página a otra y solo nos dice que
                            hubo una visita, a qué página y desde qué sitio llegó.
                        </li>
                        <li>
                            <strong>Analítica de audiencia y grabaciones:</strong> apagadas por
                            defecto; solo se activan si usted las acepta, cada una por separado.
                        </li>
                        <li>
                            <strong>Medición publicitaria:</strong> apagada por defecto; solo si
                            usted la acepta cargamos el píxel de TikTok y el de Meta. No hay una
                            versión anónima de este nivel: sin su autorización no se carga nada de
                            esas empresas.
                        </li>
                    </ul>
                </>
            ),
        },
        {
            id: 'necesarias',
            title: 'Cookies necesarias',
            body: <CookieTable rows={NECESSARY} />,
        },
        {
            id: 'basica',
            title: 'Medición básica anónima (sin cookies)',
            body: (
                <>
                    <p>
                        Google Analytics y Microsoft Clarity se cargan con el almacenamiento
                        desactivado, por lo que <strong>no escriben ninguna cookie</strong> en su
                        navegador. Reciben datos técnicos propios de toda visita web (dirección IP,
                        navegador, pantalla, la página y el sitio desde el que llegó), sin
                        identificador de usuario ni de sesión. Con eso podemos saber que alguien
                        llegó y por dónde, pero no seguir su visita completa: el tiempo de permanencia
                        y la tasa de abandono solo se miden con la analítica de audiencia, que usted
                        decide si activa.
                    </p>
                    <p>
                        Si no está de acuerdo con esta medición básica, escríbanos a{' '}
                        <Mail value={legal.email} /> o use las opciones de bloqueo de su navegador.
                    </p>
                </>
            ),
        },
        {
            id: 'audiencia',
            title: 'Analítica de audiencia (solo con su autorización)',
            body: (
                <>
                    <CookieTable rows={AUDIENCE} />
                    <p>
                        Configuramos Google Analytics sin señales de Google ni personalización
                        publicitaria, y enviamos las direcciones de las páginas sin credenciales ni
                        parámetros (por ejemplo, el enlace personal de un jugador se reporta sin su
                        código). Estas cookies de analítica no se usan para publicidad.
                    </p>
                </>
            ),
        },
        {
            id: 'publicidad',
            title: 'Medición publicitaria (solo con su autorización)',
            body: (
                <>
                    <CookieTable rows={MARKETING} />
                    <p>
                        Anunciamos MisterioCode en TikTok e Instagram/Facebook. Con su autorización,
                        el píxel de cada red nos dice si un anuncio trajo a alguien hasta aquí y si
                        terminó registrándose o comprando, y nos permite mostrar el anuncio a
                        personas parecidas. Además, cuando usted se registra o compra, nuestros
                        servidores informan ese hecho a TikTok y a Meta con su correo convertido en
                        una huella cifrada irreversible, su dirección IP y el tipo de navegador.
                        Estas empresas pueden procesar esos datos fuera de Colombia; ver la{' '}
                        <TextLink href={route('privacy')}>Política de Privacidad</TextLink>.
                    </p>
                    <p>
                        Si no acepta este nivel, no se carga ningún píxel y no se envía ningún dato
                        suyo a esas redes.
                    </p>
                </>
            ),
        },
        {
            id: 'grabaciones',
            title: 'Grabaciones y mapas de calor (solo con su autorización)',
            body: (
                <>
                    <CookieTable rows={RECORDING} />
                    <p>
                        Microsoft Clarity graba cómo se usa el sitio para que podamos detectar partes
                        confusas. Enmascara por defecto lo que se escribe en los campos de
                        formularios, y en las pantallas del jugador ocultamos además todo el texto.
                        Los proveedores de analítica pueden procesar datos fuera de Colombia,
                        principalmente en Estados Unidos; ver la{' '}
                        <TextLink href={route('privacy')}>Política de Privacidad</TextLink>.
                    </p>
                </>
            ),
        },
        {
            id: 'otros',
            title: 'Otros recursos de terceros',
            body: (
                <p>
                    Las tipografías del sitio se cargan desde Google Fonts. Al hacerlo, Google
                    recibe su dirección IP, aunque no guardamos cookies por ello. Ese recurso es
                    necesario para mostrar el sitio con su diseño.
                </p>
            ),
        },
        {
            id: 'gestionar',
            title: 'Cómo cambiar o retirar su elección',
            body: (
                <>
                    <p>
                        Puede cambiar su decisión cuando quiera. Al retirar una autorización dejamos de
                        usar esas cookies y borramos las que ya estén en su navegador.
                    </p>
                    <div className="mt-4">
                        <Button variant="secondary" onClick={openCookieSettings}>
                            Abrir preferencias de cookies
                        </Button>
                    </div>
                    <p>
                        También puede borrar o bloquear cookies desde la configuración de su navegador;
                        tenga en cuenta que bloquear las necesarias puede impedir iniciar sesión o
                        enviar formularios. Para Google Analytics existe además el{' '}
                        <a
                            href="https://tools.google.com/dlpage/gaoptout"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            complemento de inhabilitación de Google
                        </a>
                        .
                    </p>
                </>
            ),
        },
        {
            id: 'contacto',
            title: 'Contacto y cambios',
            body: (
                <p>
                    Para dudas sobre cookies escríbanos a <Mail value={legal.email} />. Si cambiamos
                    las cookies que usamos, actualizaremos esta página y le volveremos a pedir su
                    elección cuando el cambio lo requiera.
                </p>
            ),
        },
    ];

    return (
        <PublicLayout>
            <Head title="Política de Cookies" />

            <LegalDocument
                kicker="Legal"
                title="Política de Cookies"
                legal={legal}
                intro={
                    <p>
                        Qué cookies y mediciones usa MisterioCode, para qué, cuánto duran y cómo puede
                        aceptarlas, rechazarlas o cambiar su elección.
                    </p>
                }
                sections={sections}
            />
        </PublicLayout>
    );
}
