<?php

/**
 * Case manifest: "Confeti plateado"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: a short, documents-first sponsorship-sabotage case with no
 * victim and no violence. The silver confetti of the year-end countdown carries
 * a gold accent with the logo of Aurea, the rival of the exclusive sponsor
 * (Nébula). It was not a printing error: Mariana Lazo (sponsorships) replaced
 * the print-order file the night before, in exchange for a freelance contract
 * promised by Carla, her contact at Aurea. The brands Nébula and Aurea (and the
 * print shop Tinta Norte) are fictional. Classic ending: NO confession_script,
 * no audio anywhere in the case.
 *
 * Reference timetable / dates (single source of truth for every file of this
 * case; evidence, md files and manifest all derive from it). Dates are dd/mm,
 * no year, and only the ones the plot needs (the log in V03, the stamp in V02):
 *   16/12 16:20  Order created in the print shop's system by m.lazo with the
 *                approved design (file v1). Same day: V02 approval stamp.
 *   (week before the party) Mariana <-> Carla message (V05), no date shown.
 *   30/12 22:47  Mariana replaces the design file from account m.lazo (file v2,
 *                the variant with the Aurea accent). Reception of files closes
 *                at 23:00; the order goes to production at 23:05.
 *   31/12 08:30  Printed boxes, sealed with the print shop's seal, delivered to
 *                Tomás Ferreira (logistics), who signs the delivery note.
 *   31/12 night  Countdown: the confetti falls on the stage. The next morning
 *                the Nébula representative sees the accent in the photos and the
 *                confetti is collected from the stage (V01).
 *   The player-facing envelopes never state a date outside V02 / V03.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit and its accusation form is suspect +
 *    free-text motive + free-text method. The doc's scoring (35/25/20/20) is
 *    translated into culprit_points 35 + weapon 25 + motive 20 + one extra
 *    question ("quién se benefició", 20); the "treat it as a printing error"
 *    penalty lives in the weapon guidance. The extra question is announced in
 *    the unlock event.
 *  - NO VICTIM: the UI always shows a victim card, so the victim is the
 *    affected party, the sponsor brand "Nébula" (label "Afectada", portrait =
 *    its fictional logo card, no face). `victim.incident` overrides the default
 *    ("el sabotaje del patrocinio de Nébula") so the AI prompts never talk
 *    about a death or a disappearance. Joaquín (Nébula's account manager) is a
 *    suspect, not the "affected" person.
 *  - Questions: the doc gives 2 per person; the engine needs 3-5 normal ones
 *    per person. Mariana, Tomás and Joaquín: 3 normal (one stage). Delia: 3 +
 *    2 after V03 (the file printed and the account/time of the change depend on
 *    the order history). Mariana additionally has 3 confrontation questions
 *    after V03 + V04 + V05 (the doc's "second question as automatic
 *    confrontation" becomes the engine's confrontation block). Her
 *    confrontation 1 (edit admitted) needs V03+V04, confrontation 2 (Aurea
 *    deal) also needs V05, so V05 is what unlocks the motive, as in node N4.
 *  - Delia is a witness, not a source of the doc's "testimonio de Delia" in an
 *    envelope: it only exists as her interrogation, gated by V03 (T+12). Before
 *    V03 she answers vaguely and truthfully ("tendría que revisar el
 *    historial").
 *  - V03 (order history, private clue): the doc asks for private + public
 *    access. A partial private fragment "v03a" (status + the two movements with
 *    date and time but the ACCOUNT column restricted) goes to one random player
 *    at minute 6, WITHOUT evidence code (precedent: camara-6-no-existe); the
 *    complete V03 (with the account) is public at T+12 with the code V03. Nothing
 *    critical depends on the private fragment.
 *  - The doc's T+25 fail-safe (direct comparison V01 vs V02 before the form) is
 *    covered by cierre.md at minute 19 (text-only side-by-side description of
 *    what both documents already show, plus crossed alibis of Tomás and Joaquín)
 *    and by the T+12 envelope, which tells the table to start with that
 *    comparison. cierre.md does not name Mariana.
 *  - Timeline: T+0 -> minute 1 (gancho + V01 + interrogation opens, as the doc
 *    asks), T+12 -> minute 12 (V02..V05), minute 6 private fragment, minute 19
 *    cierre, T+25 -> minute 25 unlock.
 *  - V02 date: the doc says both "semanas antes" and "días antes"; it is fixed
 *    at 16/12, two weeks before the party.
 *  - V05 never names a brand: Carla is identified as Aurea's contact by the
 *    envelope text (giro.md), not by the image. The message is sent the week
 *    BEFORE the edit, so Mariana says she will do it "la víspera"; it never
 *    says "ya está hecho".
 *  - The clause in V04 says "incluido el confeti" explicitly (the doc only says
 *    "decoración y materiales impresos") so the table needs no inference.
 *  - Delia's md carries the exact V03 values (dates, hours, account, delivery
 *    time) so her answers cannot drift from the image.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()). There is
 * NO audio in the timeline and NO confession (`audio` stays in mechanics like
 * every other case).
 */
return [

    'name' => 'Confeti plateado',
    'version' => '1.0',
    'code' => 'Expediente interno — Fiesta de fin de año de Consultora Vértice',
    'authority' => 'Dirección de Consultora Vértice',

    'victim' => [
        'name' => 'Nébula',
        'label' => 'Afectada',
        'photo' => 'nebula-patrocinador.png',
        'incident' => 'el sabotaje del patrocinio de {name}',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 2 questions per person; the engine needs
        // 3-5 normal ones per person (see "Adaptations" above).
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
    | (price_amount, currency, published_at, sort_order) are seeded only the
    | first time, so changing a price in the database is not undone by the next
    | deploy. `platform:sync-cases --prices` forces them back to this manifest.
    |
    */

    'catalog' => [
        'tagline' => 'El confeti era plateado. La marca equivocada, también.',

        'description' => <<<'TXT'
            En la fiesta de fin de año de Consultora Vértice, el confeti plateado
            de la cuenta regresiva no era el diseño aprobado: una parte de las
            piezas lleva el logotipo de la marca rival del patrocinador
            exclusivo. El patrocinador amenaza con cancelar el contrato.

            Tu equipo (2 a 5 investigadores, ideal 3) recibe el expediente en
            tiempo real y podrá interrogar a tres sospechosos y a un testigo
            durante 25 minutos, comparando documentos: el confeti recuperado, la
            muestra aprobada, un pedido de imprenta y una cláusula de contrato.

            No hay víctimas ni violencia: solo un detalle pequeño que alguien
            pidió a propósito.
            TXT,

        // Archivo esperado en public/immersion/confeti-plateado/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/confeti-plateado).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'El confeti era plateado. La marca equivocada, también.',
            'bullets' => [
                '2 a 5 investigadores comparando documentos: confeti, muestra aprobada, pedido y contrato',
                'Tres sospechosos y un testigo a quienes interrogar',
                '25 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 25,
        'min_players' => 2,
        'max_players' => 5,

        'price_amount' => 29900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/confeti-plateado/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | Delia (the print shop operator) is a witness: modeled exactly like a
    | suspect entry but not accusable. The affected party (Nébula) is the
    | "victim" and is not interrogable.
    |
    */

    'suspects' => [
        'mariana-lazo' => [
            'name' => 'Mariana Lazo',
            'role' => 'sospechosa',
            'file' => 'suspects/mariana-lazo.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 3, 'after' => ['V03', 'V04', 'V05'], 'confrontation' => true],
            ]],
            'photo' => 'mariana-lazo.png',
            'connection' => 'Encargada de patrocinios de Consultora Vértice; gestiona la relación con Nébula y los materiales del evento',
            'motive' => 'Un pequeño contrato freelance que Aurea le ofreció a cambio de conseguirles visibilidad en el evento',
            'alibi' => 'Ninguna que la separe del archivo de diseño; dice que el pedido final fue el mismo que se aprobó',
            'public' => [
                'age' => '34 años',
                'profile' => 'Encargada de patrocinios de Consultora Vértice. Profesional y simpática; gestiona la relación con Nébula.',
                'alibi' => 'Dice que gestionó el pedido del confeti con normalidad.',
                'facts' => [
                    'Coordina con Nébula lo que va en la decoración y con la imprenta los archivos de los materiales del evento.',
                    'Gestiona el patrocinio que ahora está en riesgo.',
                ],
            ],
        ],
        'tomas-ferreira' => [
            'name' => 'Tomás Ferreira',
            'role' => 'sospechoso',
            'file' => 'suspects/tomas-ferreira.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'tomas-ferreira.png',
            'connection' => 'Coordinador de logística del evento; recibió y almacenó las cajas de confeti ya impresas',
            'motive' => 'Ninguno respecto al sabotaje; su implicación es puramente logística',
            'alibi' => 'Firmó el albarán de entrega de cajas ya impresas y precintadas por la imprenta; nunca tuvo acceso al archivo de diseño',
            'public' => [
                'age' => '41 años',
                'profile' => 'Coordinador de logística del evento. Relajado, algo disperso y encantado de hablar de su trabajo.',
                'alibi' => 'Dice que las cajas llegaron de la imprenta ya selladas.',
                'facts' => [
                    'Tuvo acceso físico a las cajas de confeti antes de la fiesta.',
                    'Se ocupó del almacén de decoración y del escenario.',
                ],
            ],
        ],
        'joaquin-beltran' => [
            'name' => 'Joaquín Beltrán',
            'role' => 'sospechoso',
            'file' => 'suspects/joaquin-beltran.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'joaquin-beltran.png',
            'connection' => 'Gerente de la cuenta de Nébula; su cuenta es la perjudicada por el incumplimiento',
            'motive' => 'Ninguno respecto al sabotaje; su nerviosismo tiene otro origen',
            'alibi' => 'No tiene usuario en el sistema de pedidos de la imprenta; gestiona la relación comercial, no la producción del confeti',
            'public' => [
                'age' => '38 años',
                'profile' => 'Gerente de la cuenta de Nébula. Profesional, visiblemente incómodo y a la defensiva; siempre con el móvil en la mano.',
                'alibi' => 'Dice que él no toca la producción del confeti.',
                'facts' => [
                    'Su reputación depende de que la cláusula de exclusividad se respete.',
                    'Esta semana se ha mostrado muy nervioso.',
                ],
            ],
        ],
        'delia-suarez' => [
            'name' => 'Delia Suárez',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/delia-suarez.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V03']],
            ]],
            'photo' => 'delia-suarez.png',
            'connection' => 'Operaria de la imprenta externa que produjo el confeti',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '29 años',
                'profile' => 'Operaria de la imprenta externa (Tinta Norte). Práctica y directa.',
                'facts' => [
                    'Conoce el sistema de pedidos de la imprenta.',
                    'Imprime lo que haya cargado en cada pedido.',
                ],
            ],
            'accusable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The ending
    |--------------------------------------------------------------------------
    */

    'solution' => [
        'culprit_slug' => 'mariana-lazo',

        'headline' => 'No fue un error de imprenta: Mariana editó el pedido la víspera para colar en el confeti el acento de Aurea, a cambio de un contrato freelance.',

        'motive' => 'Carla, su contacto en Aurea, le ofreció un pequeño contrato freelance a cambio de conseguirles cualquier visibilidad en el evento; Mariana necesitaba el ingreso extra y creyó que, tratándose de confeti, nadie lo notaría.',

        'method' => 'La noche anterior a la fiesta (30/12, 22:47), desde su cuenta m.lazo, reemplazó el archivo del pedido final de impresión por una variante casi idéntica con un pequeño acento dorado con el logotipo de Aurea, minutos antes del cierre de recepción de archivos. La imprenta imprimió ese archivo y entregó las cajas selladas a Tomás.',

        'key_evidence' => [
            'El confeti recuperado frente a la muestra aprobada: mezcla de piezas con y sin el acento dorado de Aurea.',
            'El pedido final: segundo movimiento del historial, el 30/12 a las 22:47, desde la cuenta m.lazo.',
            'El testimonio de Delia: se imprimió el archivo editado, no el aprobado.',
            'El mensaje con Carla: «la variante plateada con nuestro toque» y «lo tuyo, como quedamos».',
            'La cláusula de exclusividad: explica las consecuencias económicas del cambio.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including the witness.
         */
        'exonerations' => [
            'tomas-ferreira' => 'Sí tuvo acceso físico a las cajas, pero llegaron ya impresas y precintadas por la imprenta, y firmó un albarán que lo prueba; nunca tocó el archivo de diseño. El cambio ocurrió en el pedido de impresión antes de imprimir, no en las cajas que él recibió. Su único descuido, olvidar la lista de invitados VIP, no tiene relación con el confeti.',

            'joaquin-beltran' => 'Sí le interesaba que la cláusula se respetara y estaba nervioso toda la semana, pero no tiene usuario en el sistema de pedidos de la imprenta y no gestiona la producción del confeti. Su nerviosismo se debía a una oferta de trabajo en otra consultora, sin relación con el caso.',

            'delia-suarez' => 'Es una testigo, no una sospechosa: solo contó lo que registra el sistema de pedidos de la imprenta, que imprimió el archivo que estaba cargado, y no tenía motivo ni relación alguna con Aurea.',
        ],
    ],

    'accusation' => [
        'culprit_points' => 35,
        'weapon' => [
            'label' => '¿Qué hizo y cuándo?',
            'hint' => 'Qué cambió, en qué documento y en qué momento respecto a la fiesta.',
            'points' => 25,
            'guidance' => 'Lo esencial: editó el archivo del pedido final de impresión la noche anterior a la fiesta, sustituyendo el diseño aprobado por una variante con un acento de la marca rival Aurea. Cero si lo trata como un simple error de imprenta o de la imprenta sin intención.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué ganaba y de quién venía la propuesta.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'beneficiarios',
                'label' => '¿Quién se benefició del incumplimiento?',
                'hint' => 'Nombra a quienes salieron ganando con que esa variante llegara al evento.',
                'answer' => 'Aurea, que obtuvo exposición de marca gratuita en un evento de alto perfil, y Mariana, con el ingreso del contrato freelance.',
                'points' => 20,
                'guidance' => 'Puntos completos si nombra a Aurea y a Mariana; la mitad si solo a uno de los dos. Cero si dice que nadie se benefició o que fue un error.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+25 delivery plan into
    | trigger_offset_minutes (T+0 -> 1, same convention as the other cases),
    | plus the private fragment of V03 at minute 6 and the fail-safe cierre at
    | minute 19.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Confeti plateado — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 6,
            'title' => 'Pista privada — el historial del pedido (vista parcial)',
            'source_file' => 'pista-privada-historial.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: v03a is the PARTIAL view (account
            // column restricted). The complete V03 is public at minute 12.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Giro — la muestra, el pedido, la cláusula y un mensaje',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V02', 'V03', 'V04', 'V05'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 19,
            'title' => 'Cierre — la comparación directa y los cruces pendientes',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 25,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario pregunta quién se benefició del incumplimiento.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/confeti-plateado/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-confeti-recuperado.png', 'caption' => 'Confeti recogido del escenario la mañana siguiente'],
        ],
        'pista-privada-historial.md' => [
            ['file' => 'v03a-historial-pedido-parcial.png', 'caption' => 'Pedido del confeti en el sistema de la imprenta (vista parcial)'],
        ],
        'giro.md' => [
            ['file' => 'v02-muestra-aprobada.png', 'caption' => 'Ficha de aprobación de la muestra del confeti'],
            ['file' => 'v03-historial-pedido.png', 'caption' => 'Pedido final de impresión, con su historial completo'],
            ['file' => 'v04-clausula-exclusividad.png', 'caption' => 'Extracto del contrato de patrocinio de Nébula'],
            ['file' => 'v05-mensaje-carla.png', 'caption' => 'Intercambio de mensajes entre Mariana y Carla'],
        ],
        'cierre.md' => [
            ['file' => 'v02-muestra-aprobada.png', 'caption' => 'La muestra aprobada'],
            ['file' => 'v01-confeti-recuperado.png', 'caption' => 'El confeti recuperado del escenario'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v01-confeti-recuperado.png', 'caption' => 'El confeti que cayó: algunas piezas llevan el acento de Aurea'],
            ['file' => 'v03-historial-pedido.png', 'caption' => 'La edición del pedido: la víspera, desde la cuenta de Mariana'],
            ['file' => 'v05-mensaje-carla.png', 'caption' => 'El acuerdo con Carla'],
            ['file' => 'v04-clausula-exclusividad.png', 'caption' => 'Por qué importaba: la cláusula de exclusividad'],
        ],
    ],
];
