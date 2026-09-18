<?php

/**
 * Case manifest: "Archivo Nueve"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: three independent guilty acts on the document (Marcos'
 * manipulation, Patricia's cover-up, Rubén's data destruction), plus a fourth,
 * unrelated act (Javier's disappearance of Silvia). Same precedent as Proyecto
 * Boreal: the engine only tracks one "solution.culprit_slug" (the
 * disappearance), so Marcos', Patricia's and Rubén's own acts are resolved
 * entirely through their own confrontations, never through the accusation
 * mechanic itself, which only ever asks "who is responsible for Silvia's
 * disappearance". The whole point of the case is that identifying the three
 * document manipulators does NOT answer that question.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()), same as
 * Proyecto Boreal and Habitación 314.
 *
 * Four design-doc gaps were fixed during authoring rather than reproduced —
 * evidence described in the evidence bible (section 7 of the design doc) but
 * never actually scheduled in `timeline_de_juego`:
 *  - V2 (office/audit-zone photo) is now delivered at T+0 alongside V10/V11 —
 *    the design doc's own continuity note ties it to V11 (Silvia's desk).
 *  - V6 (Diego's legitimate v3 change, the contrast example) is now delivered
 *    at T+38 alongside V4/V5/V7 — it belongs with the other version
 *    comparisons and is explicitly meant to exonerate Diego from the document
 *    manipulation.
 *  - V12 (building card-access log showing only Silvia and Javier stayed
 *    late) is now delivered at T+52 alongside V9 — it corroborates Carmen's
 *    testimony right when the Giro starts pointing at Javier.
 *  - V13 (archive site exterior photo) is now delivered at T+68 alongside
 *    V8/V14 — it belongs with the other archive-site evidence.
 * No confrontation-timing bug like Habitación 314's was found here: every
 * confrontation's `evidence_required` is already satisfied by the same
 * offset (or earlier) than its own unlock in the original design.
 */
return [

    'name' => 'Archivo Nueve',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Archivo Nueve',
    'authority' => 'Auditoría Externa Independiente, Laboratorios Kestrel',

    'victim' => [
        'name' => 'Silvia Rangel',
        'photo' => 'silvia-rangel.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 5 preguntas iniciales + hasta 2 de
        // confrontación por sospechoso. The engine only supports one flat
        // number per case, same precedent as the other three cases.
        'interrogation_questions' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Catalog
    |--------------------------------------------------------------------------
    |
    | Shopper-facing presentation, synced into the mystery_cases table by
    | `php artisan platform:sync-cases`.
    |
    | The content fields below are re-synced on every run. The commercial ones
    | (price_amount, currency, published_at) are seeded only the first time,
    | so changing a price in the database is not undone by the next deploy.
    |
    */

    'catalog' => [
        'tagline' => 'Todos parecen tener algo que ocultar sobre ese documento. El problema es que ninguno de ellos parece saber dónde está ella.',

        'description' => <<<'TXT'
            Silvia Rangel, auditora interna de Laboratorios Kestrel, desaparece pocas
            horas después de anunciar por correo que encontró "alteraciones en Archivo
            Nueve" — el expediente digital de seguridad de un ensayo clínico — y que
            presentará un informe completo en dos días.

            Tu equipo (5 a 8 investigadores, ideal 6) recibe el expediente en tiempo
            real y podrá interrogar a seis sospechosos y a dos testigos, uno por uno,
            durante 90 minutos, mientras reconstruye qué pasó realmente esa noche.

            El giro central: al menos tres personas distintas manipularon Archivo
            Nueve, en momentos distintos, por razones distintas — y ninguna de ellas
            tuvo nada que ver con la desaparición de Silvia. Confirmar quién falsificó
            el documento no resuelve quién la hizo desaparecer a ella. Esa respuesta
            está en un segundo hallazgo, casi incidental, que Silvia hizo esa misma
            semana.
            TXT,

        // Archivo esperado en public/immersion/archivo-nueve/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'hard',
        'duration_minutes' => 90,
        'min_players' => 5,
        'max_players' => 8,

        'price_amount' => 68900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Carmen, Andrés) and the secondary source (the external
    | system/document audit) are modeled exactly like a suspect entry — same
    | precedent as Proyecto Boreal and Habitación 314.
    |
    */

    'suspects' => [
        'marcos-iturbe' => [
            'name' => 'Marcos Iturbe',
            'role' => 'sospechoso',
            'file' => 'suspects/marcos-iturbe.md',
            'photo' => 'marcos-iturbe.png',
            'connection' => 'Director científico del ensayo del compuesto KX-9',
            'motive' => 'Manipuló la versión 2 del expediente por presión regulatoria (sin relación con la desaparición)',
            'alibi' => 'En una cena con inversores externos esa noche, confirmable por múltiples testigos ajenos a la empresa',
        ],
        'patricia-soler' => [
            'name' => 'Patricia Soler',
            'role' => 'sospechosa',
            'file' => 'suspects/patricia-soler.md',
            'photo' => 'patricia-soler.png',
            'connection' => 'Directora de compliance/legal',
            'motive' => 'Encubrió la alteración de Marcos por miedo al impacto legal y financiero (sin relación con la desaparición)',
            'alibi' => 'En una llamada de trabajo documentada con el despacho legal externo durante toda la ventana crítica',
        ],
        'ruben-ospina' => [
            'name' => 'Rubén Ospina',
            'role' => 'sospechoso',
            'file' => 'suspects/ruben-ospina.md',
            'photo' => 'ruben-ospina.png',
            'connection' => 'Responsable de sistemas',
            'motive' => 'Destruyó una versión/backup por pánico ante su propia negligencia (sin relación con la desaparición)',
            'alibi' => 'Trabajando en remoto esa noche, confirmado por su propio historial de conexión',
        ],
        'javier-montes' => [
            'name' => 'Javier Montes',
            'role' => 'sospechoso',
            'file' => 'suspects/javier-montes.md',
            'photo' => 'javier-montes.png',
            'connection' => 'Colega de Silvia en el equipo de auditoría de cumplimiento',
            'motive' => 'Filtración de información interna a un fondo de inversión externo a cambio de dinero',
            'alibi' => 'Se quedó ayudándola hasta tarde esa noche (aparente colaborador de confianza)',
        ],
        'elena-vargas' => [
            'name' => 'Elena Vargas',
            'role' => 'sospechosa',
            'file' => 'suspects/elena-vargas.md',
            'photo' => 'elena-vargas.png',
            'connection' => 'Directora general de Laboratorios Kestrel',
            'motive' => 'Preocupación por la reputación y una ronda de financiación (aparente, sin relación con la desaparición)',
            'alibi' => 'En una cena institucional esa noche, con decenas de testigos externos',
        ],
        'diego-sanz' => [
            'name' => 'Diego Sanz',
            'role' => 'sospechoso',
            'file' => 'suspects/diego-sanz.md',
            'photo' => 'diego-sanz.png',
            'connection' => 'Analista junior de cumplimiento, asistente de Silvia',
            'motive' => 'Ninguno; busca empleo en secreto en una empresa competidora',
            'alibi' => 'En su casa esa noche, sin coartada externa fuerte pero sin ningún indicio que lo vincule al caso',
        ],
        'carmen-ruiz' => [
            'name' => 'Carmen Ruiz',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/carmen-ruiz.md',
            'photo' => 'carmen-ruiz.png',
            'connection' => 'Recepcionista',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'andres-pelaez' => [
            'name' => 'Andrés Peláez',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/andres-pelaez.md',
            'photo' => 'andres-pelaez.png',
            'connection' => 'Técnico de laboratorio',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'sistema-auditoria' => [
            'name' => 'Auditoría externa del sistema documental',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-auditoria.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-estacion" y
            // "sistema-hotel") — a shot of the audit/server terminal works as
            // its "portrait".
            'photo' => 'sistema-auditoria.png',
            'connection' => 'Registros objetivos del sistema documental y de transferencias de datos',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The ending
    |--------------------------------------------------------------------------
    */

    'solution' => [
        'culprit_slug' => 'javier-montes',

        'headline' => 'Javier Montes condujo a Silvia Rangel al sitio de archivo externo con el pretexto de asegurar una copia de pruebas, la confrontó sobre las transferencias externas de datos que ella había detectado, y la retiene allí, viva e ilesa, para impedir que lo expusiera.',

        'motive' => 'Meses filtrando información interna a un fondo de inversión externo a cambio de dinero. Silvia notó, casi de forma incidental, transferencias inusuales bajo su cuenta mientras auditaba otra cosa por completo.',

        'method' => 'Con el pretexto de ayudarla a "asegurar una copia de las pruebas", la condujo esa misma noche al sitio externo de archivo, al que tiene acceso legítimo por su rol. La confrontó allí sobre las transferencias, y ante su negativa a callar, la retuvo contra su voluntad.',

        'key_evidence' => [
            'Carmen confirma que solo Silvia y Javier se quedaron hasta muy tarde esa noche.',
            'Andrés los vio salir juntos por la salida de servicio.',
            'El registro de acceso por tarjeta al sitio de archivo es independiente y exacto: sitúa a Javier allí mucho más tarde de lo que declaró.',
            'Silvia dejó notas personales mencionando "revisar transferencias externas de J.M." antes de desaparecer.',
            'Su coartada ("la ayudé a guardar unas copias y se fue temprano, no sé más") no explica por qué el registro lo sitúa en el archivo mucho más tarde.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * responsible for Silvia's disappearance. One entry per suspect that
         * is not the culprit, including both witnesses and the secondary
         * source. For Marcos, Patricia and Rubén this is specifically about
         * the disappearance — their own guilt (document manipulation,
         * cover-up, data destruction) is a separate matter, already resolved
         * through their own confrontation, never through this exoneration
         * text.
         */
        'exonerations' => [
            'marcos-iturbe' => 'Estuvo en una cena con inversores externos toda la noche de la desaparición, confirmable por múltiples testigos ajenos a la empresa. Su motivo real (que nadie descubriera que rebajó la clasificación de eventos adversos en la versión 2) se satisface con el silencio del documento, no con el silencio de Silvia — al contrario, su desaparición atrajo mucha más atención sobre Archivo Nueve.',

            'patricia-soler' => 'Estuvo en una llamada de trabajo documentada con el despacho legal externo durante toda la ventana crítica. Su motivo real (encubrir la alteración de Marcos en la versión 4) tampoco requería la desaparición de Silvia, solo su silencio sobre el documento.',

            'ruben-ospina' => 'Su propio historial de conexión remota lo ubica trabajando desde su casa esa noche, no en ningún lugar cerca de Silvia. Borró una versión/backup por pánico ante su propia negligencia de control de versiones, un acto separado, sin relación con la desaparición.',

            'elena-vargas' => 'Tiene una coartada pública sólida: una cena institucional esa noche, con decenas de testigos externos. Su motivo real —una reacción financiera egoísta ante el anuncio de Silvia, de la que se avergüenza— es exactamente lo opuesto de querer que desapareciera: la desaparición perjudica gravemente la ronda de financiación que le preocupaba.',

            'diego-sanz' => 'Ningún registro lo sitúa cerca de Silvia esa noche ni cerca del sitio de archivo, ni tiene relación alguna con las transferencias externas. Su único secreto es que busca empleo en secreto en una empresa competidora, sin ninguna relación con el caso.',

            'carmen-ruiz' => 'Es la recepcionista, no una sospechosa: su único aporte es confirmar quién se quedó hasta tarde esa noche y a qué hora salió cada uno. No tuvo acceso al sitio de archivo ni motivo alguno.',

            'andres-pelaez' => 'Es un técnico de laboratorio que los vio salir juntos por la puerta de servicio, nada más. No tiene relación con las transferencias externas ni con el sitio de archivo esa noche.',

            'sistema-auditoria' => 'Es un sistema automatizado de registro, no una persona: no tiene forma de haber intervenido en la desaparición ni motivo alguno. Su función fue registrar con exactitud el historial de versiones, la eliminación de Rubén, las transferencias de Javier y el acceso al sitio de archivo — precisamente los datos que, cruzados entre sí, resuelven el caso.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Fenrir',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Llevaba meses pasando información antes de los anuncios importantes. No
            fue una decisión, fue... una cosa llevó a la otra, y ya no supe cómo
            parar.

            Esa noche ella me preguntó, sin más, si yo tenía acceso a esos registros
            de transferencias. No sospechaba nada todavía, solo preguntaba. Pero supe
            que en dos días lo iba a ver todo.

            Le dije que la ayudaba a guardar una copia en el archivo externo. La
            llevé, y cuando se lo pregunté directamente, no quiso prometerme nada. No
            la he lastimado. Está bien, está ahí. Pero no supe cómo parar esto sin que
            se derrumbara todo.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+24/T+38/T+52/T+68/T+80/T+90
    | delivery plan into trigger_offset_minutes, same convention as the other
    | three cases.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Archivo Nueve — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            // V2 added here during authoring: the design doc's own continuity
            // note ties it to V11 (Silvia's desk), but it was never scheduled.
            'evidence_codes' => ['V10', 'V11', 'V2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — sospechosos y testigos',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V1', 'V3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — un vistazo a la versión 2',
            'source_file' => 'pista-privada-comparacion.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: this is a PARTIAL glimpse
            // ("V4_parcial" in the design doc), not the full V4 comparison —
            // it must not satisfy Marcos' confrontación 1 gate on its own.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 38,
            'title' => 'Complicación — tres manos distintas en Archivo Nueve',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // V6 added here during authoring: Diego's legitimate contrast
            // change belongs with the other version comparisons, and was
            // never scheduled in the original design doc.
            'evidence_codes' => ['V4', 'V5', 'V6', 'V7'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Giro — una segunda línea de investigación',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // V12 added here during authoring: corroborates Carmen's
            // testimony right when suspicion turns to Javier, and was never
            // scheduled in the original design doc.
            'evidence_codes' => ['V9', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 68,
            'title' => 'Desarrollo final — el sitio de archivo',
            'source_file' => 'desarrollo-final.md',
            'delivery_mode' => 'all',
            // V13 added here during authoring: belongs with the other
            // archive-site evidence, and was never scheduled in the original
            // design doc.
            'evidence_codes' => ['V8', 'V13', 'V14'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 80,
            'title' => 'Cierre — la reconstrucción final',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V15', 'V16'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 90,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para distinguir las cuatro líneas de esta investigación: el fraude de Marcos, el encubrimiento de Patricia, la destrucción de datos de Rubén, y la desaparición de Silvia a manos de Javier. Confirmar quién manipuló el documento no responde quién la hizo desaparecer a ella. Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/archivo-nueve/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v10-anuncio-silvia.png', 'caption' => 'El correo de Silvia anunciando hallazgos en Archivo Nueve'],
            ['file' => 'v11-escritorio-silvia.png', 'caption' => 'El escritorio de Silvia, tal como quedó'],
            ['file' => 'v2-oficina-auditoria.png', 'caption' => 'La zona de auditoría interna'],
        ],
        'expansion.md' => [
            ['file' => 'v1-organigrama.png', 'caption' => 'Organigrama de Laboratorios Kestrel'],
            ['file' => 'v3-historial-versiones.png', 'caption' => 'Historial general de versiones de Archivo Nueve'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-comparacion-v1-v2.png', 'caption' => 'Comparación versión 1 vs. versión 2 — el cambio de Marcos'],
            ['file' => 'v5-comparacion-v2-v4.png', 'caption' => 'Comparación versión 2 vs. versión 4 — el encubrimiento de Patricia'],
            ['file' => 'v6-cambio-v3-legitimo.png', 'caption' => 'Registro del cambio de la versión 3 — legítimo, de Diego'],
            ['file' => 'v7-registro-eliminacion.png', 'caption' => 'Registro de eliminación de una versión/backup intermedia'],
        ],
        'giro.md' => [
            ['file' => 'v9-notas-silvia.png', 'caption' => 'Notas personales de Silvia — "revisar transferencias externas de J.M."'],
            ['file' => 'v12-registro-acceso-edificio.png', 'caption' => 'Registro de acceso al edificio — solo Silvia y Javier hasta tarde'],
        ],
        'desarrollo-final.md' => [
            ['file' => 'v8-registro-transferencias.png', 'caption' => 'Registro de transferencias externas de datos'],
            ['file' => 'v13-sitio-archivo-exterior.png', 'caption' => 'El sitio externo de archivo, fachada'],
            ['file' => 'v14-registro-acceso-archivo.png', 'caption' => 'Registro de acceso por tarjeta al sitio de archivo'],
        ],
        'cierre.md' => [
            ['file' => 'v15-objeto-personal-silvia.png', 'caption' => 'Un objeto personal de Silvia, hallado en el sitio de archivo'],
            ['file' => 'v16-agenda-reunion.png', 'caption' => 'Agenda de Silvia — la reunión pendiente'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v9-notas-silvia.png', 'caption' => 'Las notas que ya apuntaban a "J.M."'],
            ['file' => 'v14-registro-acceso-archivo.png', 'caption' => 'La hora que Javier no pudo explicar'],
            ['file' => 'v8-registro-transferencias.png', 'caption' => 'El hallazgo que nadie del documento sabía que ella había hecho'],
        ],
    ],
];
