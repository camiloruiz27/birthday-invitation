<?php

/**
 * Case manifest: "La Casa de Cristal"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a false "locked room" of a different flavor than
 * Habitación 314's. Here EVERY camera is genuine and unmanipulated — the
 * twist is that one room (the vestidor) was never covered by any camera, for
 * a completely mundane privacy reason, and the body was moved through a
 * corridor that WAS covered, disguised as an ordinary supportive gesture. No
 * confrontation or piece of evidence should ever suggest footage was faked,
 * looped, or desynchronized — V14 exists specifically to rule that out early.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()), same
 * as the other three cases.
 *
 * Four design-doc gaps were fixed during authoring rather than reproduced —
 * evidence described in the evidence bible (section 7 of the design doc) but
 * never actually scheduled in `timeline_de_juego`:
 *  - V4 (the full corridor CCTV frame) was only ever scheduled as a partial
 *    private glimpse ("V4_parcial") at T+26; the FULL V4 never had its own
 *    delivery, even though Raúl's confrontación 1 requires it and V5 (the
 *    comparison frame) is meaningless without it. Added to T+40 alongside
 *    V5/V9-V12.
 *  - V13 (the confrontation-segment script, Raúl's real motive) was never
 *    scheduled. Added to T+52 (Giro), where motive and method both
 *    crystallize.
 *  - V14 (camera synchronization log, the piece that rules out tampering)
 *    was never scheduled — added to T+13 (Expansión) so the "manipulated
 *    camera" red herring gets defused early, same narrative role V1 plays.
 *  - V15 (the missing garment on the rack, the weapon's origin) was never
 *    scheduled — added to T+52 (Giro) alongside V6/V8.
 * No confrontation-timing bug like Habitación 314's was found here: every
 * confrontation's `evidence_required` is already satisfied by the same
 * offset (or earlier) than its own unlock in the original design.
 */
return [

    'name' => 'La Casa de Cristal',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Casa de Grabación',
    'authority' => 'Equipo de Seguridad y Producción',

    'victim' => [
        'name' => 'Claudia Mercader',
        'photo' => 'claudia-mercader.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 4-5 preguntas iniciales + hasta 2 de
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
        'tagline' => 'Todas las cámaras dicen la verdad. Lo que está mal es la pregunta que le estamos haciendo a las cámaras.',

        'description' => <<<'TXT'
            Claudia Mercader, empresaria en plena entrevista exclusiva, aparece muerta
            en la sala de lectura de la casa donde se graba, durante una pausa. La casa
            está llena de cámaras de producción y seguridad. Ninguna grabación muestra
            a nadie entrando en esa sala.

            Tu equipo (4 a 7 investigadores, ideal 5) recibe el expediente en tiempo
            real y podrá interrogar a cinco sospechosos y a dos testigos, uno por uno,
            durante 65 minutos, revisando fotograma por fotograma lo que las cámaras
            realmente muestran.

            El giro central: ninguna cámara miente ni está manipulada. El vestidor,
            la única zona sin cobertura, no la tiene por una razón perfectamente
            normal — privacidad durante los cambios de vestuario. El error no está en
            las grabaciones: está en asumir que Claudia murió donde apareció.
            TXT,

        // Archivo esperado en public/immersion/la-casa-de-cristal/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'hard',
        'duration_minutes' => 65,
        'min_players' => 4,
        'max_players' => 7,

        'price_amount' => 59900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Sofía, Óscar) and the secondary source (the
    | production/recording system) are modeled exactly like a suspect entry —
    | same precedent as the other three cases.
    |
    */

    'suspects' => [
        'raul-septien' => [
            'name' => 'Raúl Septién',
            'role' => 'sospechoso',
            'file' => 'suspects/raul-septien.md',
            'photo' => 'raul-septien.png',
            'connection' => 'Exsocio de negocios de Claudia',
            'motive' => 'Evitar un segmento de confrontación en cámara sobre irregularidades financieras',
            'alibi' => 'Su presencia esa tarde era esperada y legítima (invitado para su propio segmento)',
        ],
        'ines-bravo' => [
            'name' => 'Inés Bravo',
            'role' => 'sospechosa',
            'file' => 'suspects/ines-bravo.md',
            'photo' => 'ines-bravo.png',
            'connection' => 'Estilista/responsable de vestuario',
            'motive' => 'Ninguno; vende fotos del detrás de cámaras a una revista (sin relación con la muerte)',
            'alibi' => 'Ausente del vestidor durante la ventana crítica, confirmada por Sofía',
        ],
        'hector-duran' => [
            'name' => 'Héctor Durán',
            'role' => 'sospechoso',
            'file' => 'suspects/hector-duran.md',
            'photo' => 'hector-duran.png',
            'connection' => 'Periodista/entrevistador',
            'motive' => 'Ninguno; negociaba en secreto filtrar la entrevista a un medio rival (sin relación con la muerte)',
            'alibi' => 'Afuera de la casa haciendo esa llamada, visto por Óscar, en horario fuera de la ventana crítica',
        ],
        'camila-andrade' => [
            'name' => 'Camila Andrade',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-andrade.md',
            'photo' => 'camila-andrade.png',
            'connection' => 'Asistente personal de Claudia',
            'motive' => 'Ninguno; mantiene una relación con Tomás, el marido de Claudia (sin relación con la muerte)',
            'alibi' => 'En la zona de producción durante la ventana crítica, confirmada por Sofía',
        ],
        'tomas-vega' => [
            'name' => 'Tomás Vega',
            'role' => 'sospechoso',
            'file' => 'suspects/tomas-vega.md',
            'photo' => 'tomas-vega.png',
            'connection' => 'Marido de Claudia',
            'motive' => 'Ninguno; sabía que Claudia sospechaba de su infidelidad (sin relación con la muerte)',
            'alibi' => 'Un fotograma lo sitúa alejándose de la zona del vestidor antes de la ventana crítica',
        ],
        'sofia-ledesma' => [
            'name' => 'Sofía Ledesma',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-ledesma.md',
            'photo' => 'sofia-ledesma.png',
            'connection' => 'Script/continuidad de producción',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'oscar-prieto' => [
            'name' => 'Óscar Prieto',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/oscar-prieto.md',
            'photo' => 'oscar-prieto.png',
            'connection' => 'Seguridad de la casa',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'sistema-produccion' => [
            'name' => 'Técnico de grabación / sistema de producción',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-produccion.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-hotel" /
            // "sistema-auditoria") — a shot of the production control panel
            // works as its "portrait".
            'photo' => 'sistema-produccion.png',
            'connection' => 'Mapa de cámaras, sincronización horaria y cadena de custodia del metraje',
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
        'culprit_slug' => 'raul-septien',

        'headline' => 'Claudia no murió en la sala de lectura. Murió en el vestidor —la única zona sin cámara, por privacidad legítima— estrangulada por Raúl Septién con una prenda del perchero, quien luego trasladó el cuerpo por un pasillo sí cubierto por cámara, disfrazando el traslado de un gesto cotidiano.',

        'motive' => 'Evitar un segmento de confrontación en cámara, programado para las 17:00, en el que Claudia iba a presentarlo con pruebas de irregularidades financieras durante la separación de la empresa que fundaron juntos.',

        'method' => 'Estrangulamiento con una prenda del perchero de vestuario, en el vestidor, hacia las 16:28. El cuerpo fue trasladado por el pasillo de servicio hasta la sala de lectura, sosteniéndolo por los hombros en una postura que a simple vista parecía ayudar a alguien mareado, aprovechando el hábito conocido de Claudia de descansar allí entre segmentos.',

        'key_evidence' => [
            'Claudia nunca terminó de cambiarse de vestuario: aparece con la ropa del segmento anterior, no la prevista para el siguiente bloque.',
            'Su forma de caminar por el pasillo, sostenida por los hombros, es visiblemente distinta de su forma habitual de caminar.',
            'Sofía vio a Raúl dirigirse hacia el vestidor antes de su hora programada.',
            'El registro de acceso confirma que Raúl llegó a las 16:10, antes de su segmento de las 17:00.',
            'El vestidor nunca tuvo cámara por un acuerdo estándar y legítimo de privacidad de producción — nunca fue una cámara manipulada o desincronizada.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'ines-bravo' => 'Sofía confirma su ausencia del vestidor durante la ventana crítica exacta: había salido a buscar un accesorio. Su único secreto es la venta de fotos del detrás de cámaras a una revista, sin ninguna relación con la muerte de Claudia.',

            'hector-duran' => 'Óscar la vio afuera de la casa haciendo una llamada tensa, en un horario que no coincide con la ventana crítica. Su motivo real —negociar en secreto la filtración de la entrevista— es exactamente lo opuesto de querer que Claudia muriera: su muerte le costó la exclusiva completa.',

            'camila-andrade' => 'Sofía confirma que estuvo en la zona de producción durante toda la ventana crítica. Su único secreto es una relación con Tomás, el marido de Claudia, sin ninguna relación con la muerte.',

            'tomas-vega' => 'El fotograma que en un primer vistazo parece incriminarlo —cerca del pasillo del vestidor— en realidad, con el timestamp correcto, lo muestra alejándose de esa zona antes de la ventana crítica. Se acercó con intención de hablar con Claudia sobre su propia infidelidad, se arrepintió, y se fue sin llegar a verla.',

            'sofia-ledesma' => 'Es la script de continuidad, no una sospechosa: su único aporte es confirmar el hábito de Claudia, los movimientos de Raúl, Inés y Camila esa tarde. No tuvo acceso al vestidor durante la ventana crítica ni motivo alguno.',

            'oscar-prieto' => 'Es el encargado de seguridad, no un sospechoso: confirma la hora de llegada de Raúl y la llamada tensa de Héctor desde su propio puesto. No tiene relación alguna con lo ocurrido en el vestidor.',

            'sistema-produccion' => 'Es un sistema automatizado de registro, no una persona: no tiene forma física de haber intervenido. Su función fue confirmar que todas las cámaras estaban sincronizadas y sin manipular, y registrar con exactitud qué cámara grabó cada secuencia — precisamente el dato que descarta la hipótesis de un fallo técnico y obliga a reinterpretar lo que las cámaras sí muestran.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Le pedí que quitara el segmento. Le dije que podíamos hablarlo en privado,
            que no hacía falta ponerlo en cámara delante de todo el mundo.

            Me dijo que ya estaba decidido, que las pruebas ya estaban con su equipo
            legal de todas formas. Que lo sacara en cámara o no, iba a salir igual.

            No lo pensé. Fue un segundo, y ya no había vuelta atrás. La llevé a la sala
            de lectura porque... porque sabía que ahí la buscarían de todas formas,
            tarde o temprano, y no supe qué más hacer.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+13/T+26/T+40/T+52/T+65 delivery
    | plan into trigger_offset_minutes, same convention as the other three
    | cases.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Casa de Cristal — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 13,
            'title' => 'Expansión — sospechosos y testigos',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            // V14 added here during authoring: rules out camera tampering
            // early, the same narrative role V1 plays, and was never
            // scheduled in the original design doc.
            'evidence_codes' => ['V3', 'V7', 'V14'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — un vistazo al pasillo',
            'source_file' => 'pista-privada-pasillo.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: this is a PARTIAL glimpse
            // ("V4_parcial" in the design doc), not the full V4 frame — it
            // must not satisfy Raúl's confrontación 1 gate on its own.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 40,
            'title' => 'Complicación — segunda tanda de CCTV',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // V4 (full) added here during authoring: only a partial glimpse
            // was ever scheduled, even though V5's comparison and Raúl's
            // confrontación 1 both need the full frame.
            'evidence_codes' => ['V4', 'V5', 'V9', 'V10', 'V11', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Giro — el vestidor',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // V13 and V15 added here during authoring: the motive document
            // and the weapon's origin, both described in the evidence bible
            // but never scheduled in the original design doc.
            'evidence_codes' => ['V6', 'V8', 'V13', 'V15'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 65,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para entender que ninguna cámara mintió: el error estaba en asumir que Claudia murió donde apareció. Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-casa-de-cristal/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-mapa-camaras.png', 'caption' => 'Mapa de cámaras y estancias de la casa'],
            ['file' => 'v2-escena-sala-lectura.png', 'caption' => 'La escena en la sala de lectura'],
        ],
        'expansion.md' => [
            ['file' => 'v3-vestidor.png', 'caption' => 'El vestidor/camerino, sin cámara'],
            ['file' => 'v7-agenda-vestuario.png', 'caption' => 'Agenda de vestuario de producción'],
            ['file' => 'v14-sincronizacion-camaras.png', 'caption' => 'Registro de sincronización horaria del sistema'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-cctv-pasillo-traslado.png', 'caption' => 'CCTV del pasillo de servicio — el traslado'],
            ['file' => 'v5-cctv-caminar-normal.png', 'caption' => 'CCTV de Claudia caminando con normalidad (comparación)'],
            ['file' => 'v9-cctv-ines-foto.png', 'caption' => 'CCTV de Inés fotografiando el set'],
            ['file' => 'v10-cctv-hector-llamada.png', 'caption' => 'CCTV de Héctor haciendo una llamada tensa'],
            ['file' => 'v11-cctv-camila-zona-tomas.png', 'caption' => 'CCTV de Camila entrando en la zona de Tomás'],
            ['file' => 'v12-cctv-tomas-alejandose.png', 'caption' => 'CCTV de Tomás alejándose del vestidor'],
        ],
        'giro.md' => [
            ['file' => 'v6-vestuario-segmento-anterior.png', 'caption' => 'Claudia con la ropa del segmento anterior'],
            ['file' => 'v8-registro-acceso-raul.png', 'caption' => 'Registro de acceso — hora de llegada de Raúl'],
            ['file' => 'v13-guion-segmento-confrontacion.png', 'caption' => 'Borrador del segmento de confrontación'],
            ['file' => 'v15-perchero-hueco.png', 'caption' => 'El perchero de vestuario, con un hueco entre las prendas'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v4-cctv-pasillo-traslado.png', 'caption' => 'El traslado, disfrazado de un gesto cotidiano'],
            ['file' => 'v7-agenda-vestuario.png', 'caption' => 'El vestuario que nunca llegó a cambiarse'],
            ['file' => 'v13-guion-segmento-confrontacion.png', 'caption' => 'El motivo que Raúl no podía permitir que saliera al aire'],
        ],
    ],
];
