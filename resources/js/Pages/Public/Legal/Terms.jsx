import { Head } from '@inertiajs/react';
import PublicLayout from '../../../Layouts/PublicLayout';
import LegalDocument, { ControllerIdentity, Mail } from '../../../components/public/LegalDocument';
import TextLink from '../../../components/ui/TextLink';

/**
 * Términos y Condiciones de uso y venta.
 *
 * Cubre lo que el Estatuto del Consumidor (Ley 1480 de 2011) exige informar
 * en el comercio electrónico (art. 50: identidad del proveedor, medios de pago,
 * entrega, precio con impuestos y derecho de retracto con su procedimiento)
 * y las reglas propias de la plataforma.
 */
export default function Terms({ legal, gamesPerCase }) {
    const sections = [
        {
            id: 'proveedor',
            title: 'Quién ofrece el servicio y aceptación',
            body: (
                <>
                    <p>
                        MisterioCode es una plataforma de casos de misterio interactivos que se
                        juegan en equipo desde el navegador. Es ofrecida por{' '}
                        <ControllerIdentity legal={legal} />.
                    </p>
                    <p>
                        Al crear una cuenta, comprar o usar la plataforma usted declara haber leído
                        y aceptado estos Términos y Condiciones y la{' '}
                        <TextLink href={route('privacy')}>Política de Privacidad</TextLink>. Si no
                        está de acuerdo, no la use.
                    </p>
                </>
            ),
        },
        {
            id: 'definiciones',
            title: 'Definiciones',
            body: (
                <ul>
                    <li><strong>Caso:</strong> un misterio interactivo disponible en el catálogo.</li>
                    <li><strong>Partida:</strong> una sesión de juego de un Caso, creada por un Game Master.</li>
                    <li>
                        <strong>Game Master:</strong> el usuario con cuenta que adquiere Casos, crea
                        Partidas, invita jugadores y las dirige.
                    </li>
                    <li>
                        <strong>Jugador:</strong> la persona invitada a una Partida mediante un
                        enlace personal. No necesita cuenta ni pagar.
                    </li>
                    <li>
                        <strong>Créditos de IA:</strong> unidad prepagada que se consume cuando una
                        Partida usa funciones de inteligencia artificial.
                    </li>
                </ul>
            ),
        },
        {
            id: 'cuenta',
            title: 'Cuenta y capacidad',
            body: (
                <>
                    <p>
                        Para crear una cuenta debe ser mayor de 18 años y tener capacidad legal para
                        contratar. Debe dar datos veraces y mantenerlos actualizados. Debe confirmar
                        su correo electrónico antes de comprar o canjear códigos.
                    </p>
                    <p>
                        La cuenta es personal. Usted es responsable de custodiar su contraseña y de
                        lo que ocurra desde su cuenta; avísenos de inmediato si sospecha un uso no
                        autorizado.
                    </p>
                </>
            ),
        },
        {
            id: 'servicio',
            title: 'El servicio y las partidas',
            body: (
                <>
                    <p>
                        Al adquirir un Caso, este queda en su biblioteca. El acceso no vence por el
                        paso del tiempo: se mantiene mientras su cuenta esté activa y la plataforma
                        esté en operación. Si decidimos cerrar la plataforma o retirar un Caso, se lo
                        informaremos con antelación razonable.
                    </p>
                    <p>
                        Cada Game Master puede crear hasta <strong>{gamesPerCase} Partidas por Caso</strong>.
                        Las Partidas finalizadas también cuentan; solo eliminar una Partida libera
                        un cupo. Cada Jugador accede con un enlace personal e intransferible: quien
                        lo tenga actúa como ese Jugador.
                    </p>
                    <p>
                        Los Casos, personajes y hechos son ficción. Cualquier parecido con personas
                        reales es casual.
                    </p>
                </>
            ),
        },
        {
            id: 'creditos',
            title: 'Créditos de IA',
            body: (
                <>
                    <p>
                        Algunas funciones (por ejemplo, interrogar a un sospechoso, el epílogo
                        personalizado o el audio de confesión) consumen Créditos de IA. El costo de
                        cada función y los paquetes disponibles se muestran en la plataforma antes de
                        usarlos o comprarlos; consulte{' '}
                        <TextLink href={route('pricing')}>Precios</TextLink>.
                    </p>
                    <p>
                        Al iniciar una Partida se reserva el máximo de Créditos que podría consumir;
                        al cerrarla, el saldo no consumido vuelve a su billetera. Solo se descuenta lo
                        que efectivamente se consume.
                    </p>
                    <p>
                        Los Créditos no son dinero, no generan intereses ni se pueden transferir o
                        canjear por efectivo. Salvo que se indique otra cosa al momento de la compra,
                        no tienen fecha de vencimiento.
                    </p>
                </>
            ),
        },
        {
            id: 'precios',
            title: 'Precios y pago',
            body: (
                <>
                    <p>
                        Los precios están en pesos colombianos (COP) e incluyen los impuestos que
                        correspondan. Antes de pagar siempre verá un resumen con lo que compra, el
                        descuento aplicado y el total. El precio vigente es el que se muestra al
                        confirmar la compra.
                    </p>
                    <p>
                        El pago se hace con tarjeta a través de Bold, en su propia página de pago.
                        Nosotros no recibimos ni almacenamos los datos de su tarjeta. Confirmado el
                        pago, el Caso o los Créditos se acreditan de inmediato en su cuenta, por ser
                        contenido digital. Los comprobantes se envían a su correo.
                    </p>
                </>
            ),
        },
        {
            id: 'codigos',
            title: 'Códigos promocionales',
            body: (
                <p>
                    Los códigos de descuento o de regalo tienen las condiciones, el cupo y la
                    vigencia con que se emitan. No son acumulables, no se pueden vender ni canjear
                    por dinero, y podemos anular los obtenidos o usados de forma fraudulenta o
                    automatizada.
                </p>
            ),
        },
        {
            id: 'retracto',
            title: 'Derecho de retracto, reversión del pago y garantía',
            body: (
                <>
                    <p>
                        <strong>Retracto (Ley 1480 de 2011, art. 47).</strong> Por ser una venta a
                        distancia, usted puede retractarse de la compra dentro de los{' '}
                        <strong>cinco (5) días hábiles</strong> siguientes a la celebración del
                        contrato, siempre que el contenido <strong>no haya empezado a
                        ejecutarse</strong>. En esta plataforma eso significa:
                    </p>
                    <ul>
                        <li>
                            <strong>Casos:</strong> no se ha creado ni iniciado ninguna Partida con
                            ese Caso.
                        </li>
                        <li>
                            <strong>Créditos de IA:</strong> aplica a los Créditos que no se han
                            consumido. Lo ya consumido no se reembolsa.
                        </li>
                    </ul>
                    <p>
                        Para ejercerlo escriba a <Mail value={legal.email} /> con su nombre, el correo
                        de su cuenta y la referencia del pedido. Si procede, le devolvemos el dinero
                        por el mismo medio de pago, a más tardar dentro de los treinta (30) días
                        calendario siguientes, y retiramos el acceso al Caso o los Créditos devueltos.
                    </p>
                    <p>
                        <strong>Reversión del pago (art. 51 de la Ley 1480).</strong> Si pagó con
                        tarjeta y hubo fraude, la operación no fue solicitada, el servicio no se
                        prestó o no corresponde a lo ofrecido, puede pedir la reversión ante su
                        banco emisor dentro de los cinco (5) días hábiles siguientes a que se
                        enteró de la operación. Avísenos también para ayudarle.
                    </p>
                    <p>
                        <strong>Garantía.</strong> Respondemos por la calidad e idoneidad del
                        servicio según la Ley 1480. Si algo no funciona como se ofreció, repórtelo y
                        lo corregiremos o, si no es posible, procederemos según la ley.
                    </p>
                </>
            ),
        },
        {
            id: 'game-master',
            title: 'Reglas para el Game Master y datos de los jugadores',
            body: (
                <>
                    <p>
                        Al inscribir jugadores usted nos entrega su nombre y correo. Declara que
                        esas personas lo saben y están de acuerdo, y que solo se usarán para la
                        Partida. Usted es responsable de ello frente a ellas.
                    </p>
                    <p>
                        No inscriba a menores de edad con sus datos personales. Si un menor participa
                        bajo su cuidado como representante legal, hágalo desde su propio
                        dispositivo, con un seudónimo y bajo su supervisión.
                    </p>
                    <p>
                        Cómo tratamos los datos de los jugadores está en la{' '}
                        <TextLink href={route('privacy')}>Política de Privacidad</TextLink>.
                    </p>
                </>
            ),
        },
        {
            id: 'ia',
            title: 'Contenido generado por inteligencia artificial',
            body: (
                <>
                    <p>
                        Los sospechosos, epílogos y audios que genera la IA son ficción para
                        entretenimiento. Pueden ser inexactos, inventar detalles o contradecirse, y no
                        constituyen hechos, consejo profesional ni opinión de nadie real. La historia,
                        las pistas y la solución de cada Caso las escriben personas.
                    </p>
                    <p>
                        No escriba en la plataforma datos personales reales de terceros, datos
                        sensibles ni información confidencial. Lo que escribe en las preguntas y en la
                        acusación se envía a un servicio de IA para generar la respuesta.
                    </p>
                </>
            ),
        },
        {
            id: 'uso',
            title: 'Uso aceptable',
            body: (
                <>
                    <p>Está prohibido:</p>
                    <ul>
                        <li>Compartir, revender o prestar su cuenta o los enlaces de acceso para lucrarse.</li>
                        <li>Eludir los límites, la verificación anti-bots o las medidas de seguridad.</li>
                        <li>Extraer contenido de forma automatizada, o intentar acceder a sistemas o datos ajenos.</li>
                        <li>
                            Escribir contenido ilícito, amenazante, discriminatorio o que vulnere
                            derechos de terceros, o intentar manipular la IA para obtenerlo.
                        </li>
                    </ul>
                    <p>
                        Podemos suspender o cerrar las cuentas que incumplan estas reglas.
                    </p>
                </>
            ),
        },
        {
            id: 'propiedad',
            title: 'Propiedad intelectual',
            body: (
                <>
                    <p>
                        Los Casos, textos, imágenes, audios, el software y la marca MisterioCode están
                        protegidos por las normas de derechos de autor y propiedad industrial (Ley 23
                        de 1982, Decisión Andina 351 de 1993 y Decisión Andina 486 de 2000). La compra
                        de un Caso le da una licencia limitada, personal, no exclusiva e intransferible
                        para jugarlo con fines de entretenimiento no comercial.
                    </p>
                    <p>
                        No puede copiar, distribuir, publicar ni explotar comercialmente el material
                        fuera de la Partida sin autorización escrita. Sobre lo que usted escribe en la
                        plataforma, nos autoriza solo a procesarlo para operar la Partida.
                    </p>
                </>
            ),
        },
        {
            id: 'disponibilidad',
            title: 'Disponibilidad del servicio',
            body: (
                <p>
                    Trabajamos para que la plataforma esté disponible, pero puede haber
                    interrupciones por mantenimiento, fallas de proveedores (pagos, correo, IA) o
                    fuerza mayor. Un Caso sigue siendo jugable aunque una función de IA no esté
                    disponible.
                </p>
            ),
        },
        {
            id: 'terminacion',
            title: 'Suspensión y terminación',
            body: (
                <p>
                    Usted puede dejar de usar la plataforma y eliminar su cuenta en cualquier
                    momento desde su perfil, o pidiéndolo a <Mail value={legal.email} />; perderá el
                    acceso a sus Casos, Partidas y Créditos, sin reembolso salvo lo previsto en la
                    sección de retracto.
                    Podemos suspender o terminar su cuenta por incumplimiento de estos Términos,
                    informándole el motivo.
                </p>
            ),
        },
        {
            id: 'responsabilidad',
            title: 'Responsabilidad',
            body: (
                <p>
                    Nada de estos Términos excluye ni limita los derechos que la ley le reconoce como
                    consumidor ni nuestra responsabilidad por dolo o culpa grave (Ley 1480 de 2011,
                    arts. 4, 42 y 43). Dentro de lo que la ley permite, no respondemos por daños
                    indirectos, por el contenido que usted o otros jugadores escriban, ni por fallas
                    ajenas a nuestro control.
                </p>
            ),
        },
        {
            id: 'datos',
            title: 'Datos personales y cookies',
            body: (
                <p>
                    El tratamiento de sus datos se rige por la{' '}
                    <TextLink href={route('privacy')}>Política de Privacidad</TextLink> y el uso de
                    cookies por la <TextLink href={route('cookies')}>Política de Cookies</TextLink>.
                </p>
            ),
        },
        {
            id: 'pqrs',
            title: 'Peticiones, quejas y reclamos',
            body: (
                <>
                    <p>
                        Escríbanos a <Mail value={legal.email} />. Responderemos dentro de los quince
                        (15) días hábiles siguientes.
                    </p>
                    <p>
                        Como consumidor, también puede acudir a la Superintendencia de Industria y
                        Comercio (
                        <a href="https://www.sic.gov.co" target="_blank" rel="noopener noreferrer">
                            www.sic.gov.co
                        </a>
                        ), autoridad de protección al consumidor.
                    </p>
                </>
            ),
        },
        {
            id: 'comunicaciones',
            title: 'Comunicaciones electrónicas',
            body: (
                <p>
                    Aceptamos que las comunicaciones, avisos y comprobantes se hagan por medios
                    electrónicos, al correo de su cuenta o dentro de la plataforma, con la validez
                    que la Ley 527 de 1999 reconoce a los mensajes de datos.
                </p>
            ),
        },
        {
            id: 'cambios',
            title: 'Cambios, ley aplicable y jurisdicción',
            body: (
                <>
                    <p>
                        Podemos modificar estos Términos. Los cambios importantes se publican aquí y
                        se informan por correo o dentro de la plataforma con antelación razonable;
                        si no está de acuerdo, puede dejar de usar el servicio. Las compras ya hechas
                        se rigen por los Términos vigentes al comprar.
                    </p>
                    <p>
                        Estos Términos se rigen por las leyes de la República de Colombia. Cualquier
                        controversia se someterá a las autoridades y jueces colombianos competentes,
                        sin perjuicio de sus derechos como consumidor. Si una cláusula resulta
                        inválida, las demás siguen vigentes.
                    </p>
                </>
            ),
        },
    ];

    return (
        <PublicLayout>
            <Head title="Términos y Condiciones" />

            <LegalDocument
                kicker="Legal"
                title="Términos y Condiciones"
                legal={legal}
                intro={
                    <p>
                        Estas son las reglas de uso y de compra de MisterioCode: qué ofrecemos, qué
                        puede esperar de nosotros y qué esperamos de usted.
                    </p>
                }
                sections={sections}
            />
        </PublicLayout>
    );
}
