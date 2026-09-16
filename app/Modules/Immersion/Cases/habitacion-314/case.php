<?php

/**
 * Case manifest: "Habitación 314"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: the mystery is a false "locked room". The card-lock log is
 * accurate from start to finish — nobody tampered with it. What is wrong is
 * the table's own assumption that the room-service order and the text message
 * prove Marina was alive at 22:40. Both were faked by Ramón, after the murder,
 * using her own tablet and phone, to push the assumed time window more than an
 * hour later than the real one. No confrontation should ever suggest the lock
 * itself was defeated or bypassed.
 *
 * This case opts into the "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()), same as
 * Proyecto Boreal: every evidence-gated confrontation in content/suspects/*.md
 * checks that the relevant code is already in the delivered-evidence list
 * before a suspect can be made to fold or admit anything.
 *
 * Two design-doc gaps were fixed during authoring rather than reproduced:
 *  - V2 (the untouched door/lock photo) is now delivered at T+0 alongside V1;
 *    the original design doc described it in the evidence bible but never
 *    actually scheduled its delivery anywhere.
 *  - V7 (the elevator log) is now delivered at T+35 alongside V6; Ramón's
 *    confrontación 1 needs both, but V7 was likewise never scheduled.
 *  - Daniel/Sofía's confrontación 1 needs V9 (the bar CCTV), which this
 *    manifest delivers at T+65 (cierre) — their confrontation gate in
 *    content/suspects/*.md checks for V9 directly, so it naturally only opens
 *    once T+65 has actually happened, instead of the original doc's T+35
 *    unlock note, which V9 could not yet satisfy.
 */
return [

    'name' => 'Habitación 314',
    'version' => '1.0',
    'code' => 'Expediente interno — Hotel Alcázar, Habitación 314',
    'authority' => 'Dirección de Seguridad, Hotel Alcázar',

    'victim' => [
        'name' => 'Marina Costas',
        'photo' => 'marina-costas.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 5 preguntas iniciales + hasta 2 de
        // confrontación por sospechoso. The engine only supports one flat
        // number per case (CaseDefinition::interrogationQuestions()), same
        // precedent as the other three cases.
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
        'tagline' => 'El registro dice que nadie entró después de las 22:40. Pero ella llevaba horas muerta cuando alguien pidió esa cena en su nombre.',

        'description' => <<<'TXT'
            Marina Costas, directora regional de una consultora, aparece muerta en su
            habitación del Hotel Alcázar durante la convención anual de la empresa. El
            registro electrónico de la cerradura parece la coartada perfecta para
            cualquiera: nadie entró después de que, supuestamente, ella seguía con
            vida — pidió servicio de habitación esa misma noche.

            Tu equipo (4 a 7 investigadores, ideal 5) recibe el expediente en tiempo
            real y podrá interrogar a cinco sospechosos y a dos testigos, uno por
            uno, mientras el reloj corre hacia la acusación final.

            El giro central: el registro de la cerradura nunca falló ni fue
            manipulado. Lo que estaba mal era la suposición de a qué hora probaba que
            Marina seguía viva. La mesa pasará la primera mitad de la partida
            preguntándose cómo entró el asesino sin dejar rastro, y la segunda mitad
            entendiendo que esa nunca fue la pregunta correcta.
            TXT,

        // Archivo esperado en public/immersion/habitacion-314/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'hard',
        'duration_minutes' => 75,
        'min_players' => 4,
        'max_players' => 7,

        'price_amount' => 64900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Carlos, Paola) and the secondary source (the hotel's
    | lock/reception system) are modeled exactly like a suspect entry — the
    | engine has no separate code path for "witness" vs "suspect", only
    | free-text in `role`, same precedent as Proyecto Boreal.
    |
    */

    'suspects' => [
        'ramon-alday' => [
            'name' => 'Ramón Alday',
            'role' => 'sospechoso',
            'file' => 'suspects/ramon-alday.md',
            'photo' => 'ramon-alday.png',
            'connection' => 'Socio senior, antiguo mentor profesional de Marina',
            'motive' => 'Evitar ser expuesto al día siguiente por un fraude de comisiones encubiertas',
            'alibi' => 'Reapareció tranquilo en la recepción de la convención hacia las 21:50',
        ],
        'daniel-prieto' => [
            'name' => 'Daniel Prieto',
            'role' => 'sospechoso',
            'file' => 'suspects/daniel-prieto.md',
            'photo' => 'daniel-prieto.png',
            'connection' => 'Codirector, rival profesional de Marina',
            'motive' => 'Tensión por la reestructuración y una discusión pública esa tarde (aparente)',
            'alibi' => 'En el bar del hotel junto a Sofía Reguera durante la ventana crítica, confirmado por CCTV',
        ],
        'sofia-reguera' => [
            'name' => 'Sofía Reguera',
            'role' => 'sospechosa',
            'file' => 'suspects/sofia-reguera.md',
            'photo' => 'sofia-reguera.png',
            'connection' => 'Directora de otra región, competidora por una nueva posición global',
            'motive' => 'Eliminar competencia por un ascenso (aparente)',
            'alibi' => 'En el bar del hotel junto a Daniel Prieto durante la ventana crítica, mismo CCTV',
        ],
        'lucia-ferrer' => [
            'name' => 'Lucía Ferrer',
            'role' => 'sospechosa',
            'file' => 'suspects/lucia-ferrer.md',
            'photo' => 'lucia-ferrer.png',
            'connection' => 'Asistente y protegida de Marina desde hace tres años',
            'motive' => 'Ninguno homicida; irregularidades menores en sus propios reportes de gastos',
            'alibi' => 'En su propia habitación, confirmado por el registro de actividad de su portátil',
        ],
        'hugo-valle' => [
            'name' => 'Hugo Valle',
            'role' => 'sospechoso',
            'file' => 'suspects/hugo-valle.md',
            'photo' => 'hugo-valle.png',
            'connection' => 'Exmarido de Marina, representante de un proveedor asistente a la convención',
            'motive' => 'Historial de divorcio conflictivo (aparente)',
            'alibi' => 'En videollamada con su hija durante la ventana crítica, confirmado por registro de red',
        ],
        'carlos-mena' => [
            'name' => 'Carlos Mena',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/carlos-mena.md',
            'photo' => 'carlos-mena.png',
            'connection' => 'Conserje de turno nocturno del Hotel Alcázar',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'paola-diaz' => [
            'name' => 'Paola Díaz',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/paola-diaz.md',
            'photo' => 'paola-diaz.png',
            'connection' => 'Camarera de servicio a la habitación',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'sistema-hotel' => [
            'name' => 'Sistema de cerraduras y recepción del hotel',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-hotel.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as Proyecto Boreal's
            // "sistema-estacion") — a shot of the keycard terminal/reception
            // panel works as its "portrait".
            'photo' => 'sistema-hotel.png',
            'connection' => 'Registros objetivos del hotel (cerradura, ascensores, pedidos)',
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
        'culprit_slug' => 'ramon-alday',

        'headline' => 'Ramón Alday estranguló a Marina Costas con el cinturón del propio albornoz de la habitación, y luego, usando su tablet y su teléfono, fabricó una prueba de vida que nunca existió.',

        'motive' => 'Marina iba a exponer al día siguiente un fraude de comisiones encubiertas de Ramón, ya remitido a control interno. Él fue a pedirle que lo dejara renunciar en silencio y, ante su negativa, la discusión escaló hasta un arrebato de pánico, no de cálculo.',

        'method' => 'Estrangulamiento con el cinturón del albornoz de cortesía de la habitación, entre las 21:10 y las 21:25 — un arma de oportunidad, no traída por él.',

        'key_evidence' => [
            'El registro de la cerradura fue exacto todo el tiempo: la única entrada real a la 314 fue la tarjeta de Ramón, a las 21:10.',
            'La hora en la que se registró el pedido de servicio (21:32) está mucho más cerca de la discusión que de la hora de entrega (22:40) — el sistema del hotel guarda ambos campos por separado.',
            'El pedido incluía un plato con marisco, al que Marina era alérgica.',
            'El mensaje de texto de las 21:34 se despide con "buenas noches", un cierre que Marina no usaba — ella siempre escribía "nos vemos mañana".',
            'Ningún registro de ascensor ni de tarjeta muestra a nadie más subiendo al piso 3 en la ventana relevante.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not have
         * killed Marina. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'daniel-prieto' => 'El CCTV del bar del hotel lo ubica junto a Sofía Reguera durante toda la ventana crítica (21:00–21:45), no en la 314. Su discusión pública con Marina esa tarde fue real, pero por otro motivo: ella había notado favoritismo presupuestario y sospechaba, con razón, la relación personal no declarada entre ambos — un conflicto de interés, no un móvil de asesinato.',

            'sofia-reguera' => 'El mismo CCTV que exonera a Daniel la ubica junto a él en el bar durante toda la ventana crítica. Su motivo real no era eliminar competencia por el ascenso: era ocultar, junto con Daniel, una relación personal que Marina ya había detectado y sobre la que los había advertido.',

            'lucia-ferrer' => 'El registro de actividad de su propio portátil la ubica trabajando en su habitación durante toda la ventana crítica, no en la 314. Su único secreto son irregularidades menores en sus propios reportes de gastos de viaje, sin relación alguna con el fraude mayor de Ramón ni con la muerte de Marina; de hecho, fue su conocimiento del estilo de escritura de Marina lo que ayudó a desenmascarar el mensaje falso.',

            'hugo-valle' => 'Se vio con Marina esa noche, pero antes de la ventana crítica: el CCTV del lobby confirma un encuentro cordial entre las 20:30 y las 20:50, sobre la boda de la hija de ambos. Durante la ventana crítica misma, el registro de red del hotel lo ubica en videollamada con su hija. Su única mentira ocultaba vergüenza por haber vuelto a hablarse bien con Marina tras un divorcio difícil, nunca un crimen.',

            'carlos-mena' => 'Es el conserje de turno, no un sospechoso: su único aporte es lo que vio desde su puesto (a Ramón subiendo nervioso, la entrega sin contacto, a Daniel y Sofía en el bar). No tuvo acceso a la 314 esa noche ni motivo alguno contra Marina.',

            'paola-diaz' => 'Entregó la bandeja del pedido a las 22:40 y no vio a nadie: dejó el pedido en la puerta siguiendo la instrucción de "no molestar" que Ramón había fabricado. Para cuando ella llegó, Marina llevaba más de una hora muerta y Paola no tenía forma de saberlo.',

            'sistema-hotel' => 'Es un sistema automatizado de registro, no una persona: no tiene forma de haber entrado a la 314 ni motivo alguno. Su función esa noche fue registrar con exactitud la entrada de Ramón a las 21:10 y las horas de solicitud y entrega del pedido por separado — precisamente el dato que, bien leído, desarma la coartada temporal fabricada.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Fui a pedirle que me dejara renunciar en silencio. Solo eso. Le dije que
            asumiría las consecuencias a mi manera, sin que mi nombre apareciera
            mañana delante de toda la empresa.

            Me dijo que ya no dependía de ella. Que cumplimiento ya lo sabía. Que no
            había nada que hablar.

            No lo pensé. No fue un plan. Fue un segundo, y después ya no había vuelta
            atrás. Usé su tablet, su teléfono... pensé que si parecía que seguía viva
            un rato más, nadie miraría hacia mí primero.

            Ni siquiera sabía que era alérgica al marisco. Después de todos estos
            años, ni siquiera sabía eso.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+10/T+20/T+35/T+50/T+65/T+75
    | delivery plan into trigger_offset_minutes (T+0 becomes minute 1, same
    | convention as the other three cases).
    |
    | `evidence_codes` opts this case into "Level 2": an interrogation can
    | check a player's claim about a piece of evidence against what this game
    | has actually delivered so far (see Game::deliveredEvidenceCodes()). This
    | works the same whether the event was sent to everyone or to a single
    | random player: once ANY event at that offset is sent, its codes count as
    | delivered for the whole table.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Habitación 314 — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            // V2 added here during authoring: described in the evidence bible
            // but never scheduled in the original design doc.
            'evidence_codes' => ['V1', 'V2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 10,
            'title' => 'Expansión — sospechosos y testigos',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V3', 'V4'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 20,
            'title' => 'Fragmento privado — borrador de la reestructuración',
            'source_file' => 'pista-privada-agenda.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V11'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 20,
            'title' => 'Fragmento privado — CCTV del lobby',
            'source_file' => 'pista-privada-lobby.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V8'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 35,
            'title' => 'Complicación — el registro de la cerradura',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // V7 added here during authoring: required by Ramón's
            // confrontación 1 but never scheduled in the original design doc.
            'evidence_codes' => ['V5', 'V6', 'V7'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Giro — el mensaje de las 21:34',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V10'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Cierre — coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V9', 'V12', 'V13'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 75,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para reconstruir lo que pasó en la 314 — y para entender por qué la pregunta correcta nunca fue "cómo entró", sino "por qué dimos por hecho que seguía viva a las 22:40". Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/habitacion-314/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-habitacion-314.png', 'caption' => 'La habitación 314, la mañana del hallazgo'],
            ['file' => 'v2-puerta-cerradura.png', 'caption' => 'La puerta y la cerradura, sin señales de forzado'],
        ],
        'expansion.md' => [
            ['file' => 'v3-plano-piso-3.png', 'caption' => 'Plano funcional del piso 3'],
            ['file' => 'v4-bandeja-puerta.png', 'caption' => 'La bandeja de servicio dejada en la puerta'],
        ],
        'pista-privada-agenda.md' => [
            ['file' => 'v11-borrador-reestructuracion.png', 'caption' => 'Fragmento del borrador de la reestructuración'],
        ],
        'pista-privada-lobby.md' => [
            ['file' => 'v8-cctv-lobby.png', 'caption' => 'CCTV del lobby — Marina y Hugo, 20:30'],
        ],
        'complicacion.md' => [
            ['file' => 'v5-ticket-servicio.png', 'caption' => 'Ticket de servicio de habitación — hora de pedido vs. hora de entrega'],
            ['file' => 'v6-registro-cerradura.png', 'caption' => 'Registro electrónico de la cerradura de la 314'],
            ['file' => 'v7-registro-ascensores.png', 'caption' => 'Registro de movimientos de ascensor'],
        ],
        'giro.md' => [
            ['file' => 'v10-mensaje-texto.png', 'caption' => 'Captura del mensaje de texto de las 21:34'],
        ],
        'cierre.md' => [
            ['file' => 'v9-cctv-bar.png', 'caption' => 'CCTV del bar — Daniel y Sofía, 21:00'],
            ['file' => 'v12-registro-portatil.png', 'caption' => 'Registro de actividad del portátil de Lucía'],
            ['file' => 'v13-registro-red.png', 'caption' => 'Registro de actividad de red — videollamada de Hugo'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v6-registro-cerradura.png', 'caption' => 'La única entrada real — Ramón, 21:10'],
            ['file' => 'v5-ticket-servicio.png', 'caption' => 'La prueba de vida fabricada — hora de pedido vs. entrega'],
            ['file' => 'v10-mensaje-texto.png', 'caption' => 'El mensaje que Marina nunca escribió'],
        ],
    ],
];
