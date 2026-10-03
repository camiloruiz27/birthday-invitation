import { Head } from '@inertiajs/react';
import PublicLayout from '../../../Layouts/PublicLayout';
import LegalDocument, { ControllerCard, Mail, Val } from '../../../components/public/LegalDocument';
import TextLink from '../../../components/ui/TextLink';

/**
 * Política de Tratamiento de Datos Personales.
 *
 * Contenido mínimo exigido por el Decreto 1074 de 2015 (art. 2.2.2.25.3.1,
 * antes art. 13 del Decreto 1377 de 2013): identificación y contacto del
 * responsable, tratamiento y finalidad, y derechos del titular — más lo que
 * la Ley 1581 de 2012 pide informar (arts. 12, 14, 15 y 26).
 */
export default function Privacy({ legal }) {
    const sections = [
        {
            id: 'responsable',
            title: 'Responsable del tratamiento',
            body: (
                <>
                    <p>
                        El responsable del tratamiento de sus datos personales es quien opera la
                        plataforma MisterioCode:
                    </p>
                    <ControllerCard legal={legal} />
                    <p>
                        Para cualquier consulta, reclamo o ejercicio de sus derechos sobre sus
                        datos puede escribirnos a <Mail value={legal.email} />.
                    </p>
                </>
            ),
        },
        {
            id: 'marco',
            title: 'Marco legal y alcance',
            body: (
                <>
                    <p>
                        Esta política se expide en cumplimiento del artículo 15 de la Constitución
                        Política de Colombia, la Ley Estatutaria 1581 de 2012, el Decreto Único
                        Reglamentario 1074 de 2015 (que compila el Decreto 1377 de 2013) y las
                        instrucciones de la Superintendencia de Industria y Comercio (SIC),
                        incluida su Circular Única, Título V.
                    </p>
                    <p>
                        Se aplica a los datos personales de quienes visitan el sitio, crean una
                        cuenta, compran casos o créditos, dirigen una partida como Game Master o
                        participan en una partida como jugador.
                    </p>
                </>
            ),
        },
        {
            id: 'datos',
            title: 'Qué datos recolectamos',
            body: (
                <>
                    <ul>
                        <li>
                            <strong>Cuenta:</strong> nombre, correo electrónico y contraseña (se
                            guarda cifrada con un algoritmo de hash; no podemos leerla), y la fecha
                            y versión de los textos legales que usted aceptó.
                        </li>
                        <li>
                            <strong>Compras:</strong> el pedido (caso o paquete de créditos),
                            valor, estado y los identificadores del pago. Los datos de su tarjeta
                            los captura directamente nuestro proveedor de pagos, Bold; no pasan por
                            nuestros servidores ni los almacenamos. Guardamos la notificación de
                            pago que Bold nos envía para confirmar la transacción.
                        </li>
                        <li>
                            <strong>Partidas (Game Master):</strong> el nombre de la partida, su
                            configuración y el nombre y correo electrónico de cada jugador que usted
                            inscribe.
                        </li>
                        <li>
                            <strong>Participación (jugadores):</strong> lo que el jugador escribe
                            durante la partida — preguntas a los sospechosos, la acusación (a quién,
                            con qué arma y por qué motivo) — y su avance y puntaje.
                        </li>
                        <li>
                            <strong>Créditos de IA:</strong> el saldo y los movimientos de su
                            billetera de créditos.
                        </li>
                        <li>
                            <strong>Datos técnicos:</strong> dirección IP, tipo de navegador y
                            registros de funcionamiento y seguridad de los servidores; una medición
                            básica y anónima de las visitas, sin cookies; y, solo si usted lo
                            autoriza, datos de uso medidos con cookies de analítica y grabaciones de
                            sesión (ver la{' '}
                            <TextLink href={route('cookies')}>Política de Cookies</TextLink>).
                        </li>
                        <li>
                            <strong>Comunicaciones:</strong> los correos de servicio que le
                            enviamos (verificación de cuenta, recuperación de contraseña, enlaces
                            de acceso a partidas, eventos del caso y epílogos) y los mensajes que
                            usted nos escriba.
                        </li>
                    </ul>
                    <p>
                        <strong>Datos sensibles.</strong> No solicitamos datos sensibles (los que
                        afectan la intimidad o cuyo uso indebido puede generar discriminación: salud,
                        origen racial o étnico, orientación política, religiosa o sexual, datos
                        biométricos, entre otros). Le pedimos no escribirlos en los campos de texto
                        libre, como las preguntas o la acusación. La entrega de datos sensibles es
                        siempre facultativa.
                    </p>
                    <p>
                        <strong>Menores de edad.</strong> La plataforma está dirigida a mayores de
                        18 años y no recolectamos de manera consciente datos de niños, niñas o
                        adolescentes. Si nos enteramos de que se registraron, los eliminaremos.
                    </p>
                </>
            ),
        },
        {
            id: 'finalidades',
            title: 'Para qué usamos sus datos (finalidades)',
            body: (
                <>
                    <ul>
                        <li>Crear y administrar su cuenta, autenticarlo y mantener su sesión.</li>
                        <li>
                            Entregarle los casos adquiridos, crear y operar las partidas, enviar a
                            cada jugador su enlace de acceso y los correos de la partida.
                        </li>
                        <li>
                            Procesar pagos, administrar créditos de IA, aplicar códigos
                            promocionales y emitir comprobantes.
                        </li>
                        <li>
                            Generar con inteligencia artificial las respuestas de los sospechosos, los
                            epílogos y los audios de confesión de la partida.
                        </li>
                        <li>
                            Garantizar la seguridad: prevenir fraude, abuso y accesos no
                            autorizados (verificación anti-bots, límites de intentos).
                        </li>
                        <li>Atender consultas, reclamos, solicitudes de retracto y soporte.</li>
                        <li>
                            Cumplir obligaciones legales, contables y tributarias, y atender
                            requerimientos de autoridades competentes.
                        </li>
                        <li>
                            Medir el uso del sitio y mejorarlo: de forma básica y anónima, sin
                            cookies, para saber cuántas personas llegan y desde dónde; y con mayor
                            detalle (tiempo de visita, abandono, grabaciones de uso) únicamente si
                            usted lo autoriza.
                        </li>
                    </ul>
                    <p>
                        No vendemos sus datos ni los usamos para publicidad de terceros. No le
                        enviaremos comunicaciones comerciales o promocionales sin su autorización
                        previa y expresa.
                    </p>
                </>
            ),
        },
        {
            id: 'autorizacion',
            title: 'Autorización y jugadores invitados',
            body: (
                <>
                    <p>
                        Tratamos sus datos con su autorización previa, expresa e informada. Al crear
                        una cuenta usted debe marcar una casilla, que parte desmarcada, aceptando
                        los{' '}
                        <TextLink href={route('terms')}>Términos y Condiciones</TextLink> y esta
                        política. Guardamos la fecha y la versión aceptadas como prueba de la
                        autorización. Para las cookies analíticas, la autorización se pide por
                        separado en el aviso de cookies.
                    </p>
                    <p>
                        <strong>Jugadores invitados.</strong> Un jugador no necesita cuenta: el Game
                        Master lo inscribe con su nombre y correo y recibe un enlace personal. El
                        Game Master declara contar con la autorización de cada persona que inscribe.
                        Además, la primera vez que el jugador abre su enlace se le muestra un aviso
                        de privacidad y no puede continuar hasta aceptarlo; guardamos la fecha y la
                        versión aceptadas. Usamos esos datos únicamente para entregarle su enlace y
                        operar la partida. Si usted es un jugador invitado y no desea que tratemos
                        sus datos, escríbanos a <Mail value={legal.email} /> y los suprimiremos.
                    </p>
                    <p>
                        Puede revocar su autorización en cualquier momento, salvo cuando exista un
                        deber legal o contractual de conservar los datos. Si tiene cuenta, puede
                        eliminarla usted mismo desde su perfil; también puede pedirlo por correo.
                    </p>
                </>
            ),
        },
        {
            id: 'terceros',
            title: 'Con quién compartimos sus datos',
            body: (
                <>
                    <p>
                        Para prestar el servicio nos apoyamos en proveedores que actúan como
                        encargados del tratamiento o proveedores de infraestructura:
                    </p>
                    <ul>
                        <li>
                            <strong>Bold</strong> (Colombia): procesa los pagos con tarjeta.
                        </li>
                        <li>
                            <strong>Proveedor de alojamiento y de correo electrónico:</strong>{' '}
                            almacena la plataforma y entrega nuestros correos.
                        </li>
                        <li>
                            <strong>Google</strong>: modelos de inteligencia artificial (Gemini) que
                            generan las respuestas, epílogos y audios; Google Analytics (medición
                            básica anónima sin cookies y, con su autorización, analítica de
                            audiencia) y Google Fonts (tipografías del sitio).
                        </li>
                        <li>
                            <strong>Microsoft Clarity</strong>: medición básica anónima sin cookies
                            y, solo con su autorización, mapas de calor y grabaciones de uso.
                        </li>
                        <li>
                            <strong>Cloudflare Turnstile</strong>: verificación anti-bots en los
                            formularios.
                        </li>
                    </ul>
                    <p>
                        <strong>Inteligencia artificial.</strong> Cuando un jugador pregunta a un
                        sospechoso, o cuando se genera un epílogo o una confesión, enviamos al
                        servicio de IA solo el texto necesario: la pregunta y el historial de la
                        conversación, el testimonio del sospechoso y, para el epílogo, el nombre del
                        jugador y la acusación (arma y motivo). No enviamos correos electrónicos.
                        Nuestro servicio de IA no guarda ni registra esos textos: solo anota datos
                        técnicos de funcionamiento, como la duración de la consulta. No usamos lo que
                        escriben los jugadores para entrenar modelos.
                    </p>
                    <p>
                        Fuera de lo anterior, solo entregaremos datos a autoridades cuando una
                        norma o una orden de autoridad competente nos obligue.
                    </p>
                </>
            ),
        },
        {
            id: 'transferencias',
            title: 'Transmisiones y transferencias internacionales',
            body: (
                <>
                    <p>
                        Algunos de los proveedores anteriores (Google, Microsoft, Cloudflare)
                        procesan datos en servidores ubicados fuera de Colombia, principalmente en
                        Estados Unidos. Estas transmisiones a encargados se rigen por los términos
                        de tratamiento de datos de cada proveedor y, en la medida en que constituyan
                        una transferencia internacional, se apoyan en su autorización expresa e
                        inequívoca (artículo 26, literal a, de la Ley 1581 de 2012), que usted
                        otorga al aceptar esta política.
                    </p>
                </>
            ),
        },
        {
            id: 'derechos',
            title: 'Sus derechos como titular',
            body: (
                <>
                    <p>De acuerdo con el artículo 8 de la Ley 1581 de 2012, usted tiene derecho a:</p>
                    <ul>
                        <li>Conocer, actualizar y rectificar sus datos personales.</li>
                        <li>Solicitar prueba de la autorización que nos otorgó.</li>
                        <li>Ser informado, previa solicitud, del uso que hemos dado a sus datos.</li>
                        <li>
                            Presentar quejas ante la Superintendencia de Industria y Comercio por
                            infracciones a la ley.
                        </li>
                        <li>
                            Revocar la autorización y solicitar la supresión de sus datos cuando no
                            se respeten los principios, derechos y garantías legales, o cuando no
                            exista un deber legal o contractual de conservarlos.
                        </li>
                        <li>Acceder de forma gratuita a sus datos personales.</li>
                    </ul>
                </>
            ),
        },
        {
            id: 'ejercicio',
            title: 'Cómo ejercer sus derechos',
            body: (
                <>
                    <p>
                        Escríbanos a <Mail value={legal.email} /> indicando su nombre, un medio de
                        contacto, el correo con el que se registró y qué solicita. Podemos pedirle
                        información para verificar su identidad. Si actúa por otra persona, debe
                        acreditar su representación.
                    </p>
                    <ul>
                        <li>
                            <strong>Consultas:</strong> las respondemos en máximo diez (10) días
                            hábiles. Si no es posible, le informaremos el motivo y la nueva fecha, que
                            no pasará de cinco (5) días hábiles adicionales.
                        </li>
                        <li>
                            <strong>Reclamos</strong> (corrección, actualización, supresión o
                            presunto incumplimiento): los resolvemos en máximo quince (15) días
                            hábiles. Si no es posible, le informaremos el motivo y la nueva fecha, que
                            no pasará de ocho (8) días hábiles adicionales. Si el reclamo está
                            incompleto, le pediremos subsanarlo dentro de los cinco (5) días
                            siguientes; si pasan dos (2) meses sin respuesta suya, entenderemos que
                            desistió.
                        </li>
                    </ul>
                    <p>
                        Solo puede acudir a la Superintendencia de Industria y Comercio (
                        <a href="https://www.sic.gov.co" target="_blank" rel="noopener noreferrer">
                            www.sic.gov.co
                        </a>
                        ) después de haber agotado este trámite ante nosotros.
                    </p>
                </>
            ),
        },
        {
            id: 'conservacion',
            title: 'Cuánto tiempo conservamos sus datos',
            body: (
                <>
                    <ul>
                        <li>
                            <strong>Cuenta y biblioteca:</strong> mientras la cuenta esté activa.
                        </li>
                        <li>
                            <strong>Partidas y datos de jugadores:</strong> hasta que el Game Master
                            elimine la partida o elimine su cuenta, o hasta que el jugador pida su
                            supresión.
                        </li>
                        <li>
                            <strong>Soportes de compras:</strong> el tiempo que exijan las normas
                            contables y tributarias (hasta diez años, Ley 962 de 2005, art. 28). Al
                            eliminar su cuenta, estos registros se conservan desvinculados de ella.
                        </li>
                        <li>
                            <strong>Registros técnicos y de seguridad:</strong> el tiempo
                            estrictamente necesario para operar y proteger el servicio.
                        </li>
                    </ul>
                    <p>
                        Cuando termine la finalidad y no exista un deber legal de conservarlos,
                        eliminaremos o anonimizaremos los datos.
                    </p>
                </>
            ),
        },
        {
            id: 'seguridad',
            title: 'Seguridad',
            body: (
                <p>
                    Aplicamos medidas técnicas, humanas y administrativas razonables para proteger
                    sus datos contra acceso no autorizado, pérdida o uso indebido: contraseñas
                    cifradas, conexión cifrada (HTTPS), enlaces de jugador personales e
                    intransferibles, verificación anti-bots y límites de intentos. Ningún sistema es
                    completamente invulnerable; cuide su contraseña y no comparta su enlace de
                    jugador.
                </p>
            ),
        },
        {
            id: 'cookies',
            title: 'Cookies y analítica',
            body: (
                <p>
                    Usamos cookies necesarias para que el sitio funcione, una medición básica y
                    anónima de las visitas que no usa cookies y, solo con su autorización, cookies
                    de analítica de audiencia (Google Analytics) y de grabaciones de uso (Microsoft
                    Clarity), que usted acepta por separado. El detalle y cómo cambiar su elección
                    están en la{' '}
                    <TextLink href={route('cookies')}>Política de Cookies</TextLink>.
                </p>
            ),
        },
        {
            id: 'cambios',
            title: 'Cambios y vigencia',
            body: (
                <>
                    <p>
                        Podemos actualizar esta política. Publicaremos la nueva versión en esta
                        página con su fecha de vigencia y, si el cambio altera de fondo las
                        finalidades o sus derechos, se lo informaremos por correo o dentro de la
                        plataforma para que usted pueda aceptarlo o no.
                    </p>
                    <p>
                        Esta política rige desde la fecha indicada al inicio y mientras
                        conservemos datos personales para las finalidades descritas.
                    </p>
                    <p>
                        Responsable: <Val value={legal.entity_name} label="nombre o razón social" />.
                    </p>
                </>
            ),
        },
    ];

    return (
        <PublicLayout>
            <Head title="Política de Privacidad" />

            <LegalDocument
                kicker="Legal"
                title="Política de Privacidad y Tratamiento de Datos Personales"
                legal={legal}
                intro={
                    <p>
                        Aquí le explicamos qué datos suyos tratamos en MisterioCode, para qué, con
                        quién los compartimos y cómo puede ejercer sus derechos conforme a la
                        legislación colombiana de protección de datos personales.
                    </p>
                }
                sections={sections}
            />
        </PublicLayout>
    );
}
