<?php

/**
 * Case manifest: "Entre Sueño y Vigilia"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is an "epistemic" case. Nobody who testifies for the
 * table lies except the culprit:
 *  - Bruno heard a REAL discussion through the wall during a real two-minute
 *    micro-awakening (01:52) and mixed it into a dream. He is sincere.
 *  - Camila really saw Julián in the corridor, but dated it "at two" by
 *    inference (Julián had told them his round was around two). She woke at
 *    about 03:00. She is sincere; her error is a date, not an invention.
 *  - Julián lies on purpose and bet that no participant memory would be
 *    taken seriously. He never tampered with any record.
 * The monitoring system is exact: sleep/wake states with time and card
 * readings, never the content of a dream or perception. Every roster md has a
 * "Regla de conocimiento" section that makes the AI separate recuerdo /
 * sensación / inferencia / hecho observado and keep equivalent questions
 * ("¿lo viste?", "¿cómo sabes que era él?", "¿estabas despierto?") coherent.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 5-field form and point
 *    scoring (incl. "Bruno's testimony is sincere" / "Camila's error is
 *    honest") are NOT implemented; they are asked for inside the free-text
 *    motive (see the T+65 unlock text) and explained in solucion.md and in
 *    each exoneration.
 *  - Each person has 5 base questions, plus 3 more in `confrontation` stages
 *    for those with confrontations (8 in all; the doc's "4 + 1/2" becomes
 *    5 + 3). The stages in `interrogation.stages` wait on the evidence each
 *    md names, not on a separate pool.
 *  - V9 (Bruno's room) was never scheduled in the design timeline. Delivered
 *    at T+13 with the roster, as context for the room next to the monitoring
 *    room.
 *  - V12 (Héctor's file) was never scheduled although his confrontation
 *    requires it at T+39. Delivered at T+39 with the complication.
 *  - V3 and V4 are delivered twice with DISTINCT files: private partial
 *    fragments at T+26 (v3a / v4a: marks without hour; no evidence_codes, so
 *    they satisfy no confrontation) and the complete records afterwards. The
 *    complete V3 arrives at T+39 because Bruno's confrontation (A2 + V3) is
 *    unlocked then; the complete V4 arrives at T+52, when Camila's
 *    confrontation unlocks.
 *  - V5 (raw vs reported data) is ONE version at T+39 whose responsible
 *    column only shows the user code INV-02. V6 (Renata's notes) arrives at
 *    T+52 and is the piece that names "J. Meza (INV-02)". Julián's second
 *    confrontation requires both V5 and V6.
 *  - Julián's first confrontation was "unlocked at T+39" but needs V7, which
 *    the design delivers at T+52. The md gates it on the evidence itself.
 *  - CONTRADICTION in the design: Camila sees Julián "leaving the area" at
 *    about 03:00 and N4 says the access log "coincides with the real
 *    sighting", but the log only showed him leaving at 02:03. V7 now lists a
 *    second visit (02:52–03:01), so the log literally supports N4 and
 *    "entré un momento y salí enseguida" is false twice over. The first visit
 *    (01:44–02:03) and the 01:52 / 01:53 facts are unchanged. The second
 *    visit has a motive: he came back to finish recomposing the scene and
 *    cleaning the cart (solucion.md, Julián's md and the confession agree).
 *  - CONTRADICTION in the design: Daniel's break (01:40–02:05) leaves nobody
 *    to confirm Laura's alibi, and he would have found Renata at 02:05 if his
 *    post were the monitoring room. Resolved: Daniel's post is a technical
 *    desk at the end of the corridor, facing the live sleep panel and the
 *    nursing station, with no view of the monitoring room door (behind a
 *    bend). He saw Laura at 01:38 and 02:06; the card log (nobody but Renata
 *    and Julián entered between 23:22 and 06:29) closes the rest. The panel
 *    does not flag micro-awakenings as alerts, which is why nobody noticed
 *    Bruno's.
 *  - V1 layout fixed for every md and for V10: corridor with rooms 1 (Bruno,
 *    shared wall with the monitoring room), 2 (Camila), 3 (Héctor); the
 *    participants' bathroom beside the monitoring room door; nursing station
 *    and technical desk at the far end. V10 lists Julián's "monitor review" at
 *    02:00 (about 5 min), which supports Camila's inference.
 *  - A2 in the design had the female voice say Julián's name, which would
 *    identify the speakers at T+39 against the design's own "ambiguity must
 *    come from the sound". The audio has a single voice (his line only); her
 *    reply is an unintelligible murmur mixed under it, so no script line.
 *  - Laura's confrontation had no evidence in the design: it is gated on A2
 *    (the unlock marker) plus the player citing the reprimand, and Patricia
 *    (who knows of it) is the table's source. Patricia also gains the minimal
 *    fact that Renata asked for a meeting at 08:00 (supports N1).
 *  - The design's fail-safe "coartadas de Héctor y Laura se entregan
 *    igualmente de forma global" is the T+58 delivery (cierre.md), which also
 *    highlights, neutrally, Camila's 03:00 awakening and the 03:01 exit.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'Entre Sueño y Vigilia',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Clínica del Sueño Aurora',
    'authority' => 'Dirección Clínica de la Clínica del Sueño Aurora',

    'victim' => [
        'name' => 'Dra. Renata Solís',
        'label' => 'Víctima',
        'photo' => 'renata-solis.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // Base of 5 preguntas per person; the confrontation extras (3) are
        // declared per person in `interrogation.stages` (see the roster).
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
        'tagline' => 'Todos estaban dormidos, según el estudio. Alguien recuerda algo de todas formas.',

        'description' => <<<'TXT'
            En la Clínica del Sueño Aurora, durante un estudio nocturno con
            participantes conectados a sensores, la directora del estudio
            aparece muerta en la sala de monitoreo. Varios de los presentes
            recuerdan haber oído o visto algo esa noche, pero sus propios
            registros de sueño dicen que deberían haber estado dormidos.

            Tu equipo (4 a 6 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco sospechosos, dos testigos y
            al sistema de monitorización durante 65 minutos, cruzando registros
            de sueño, accesos por tarjeta y un audio ambiental.

            Aquí importa distinguir qué es un recuerdo, qué es una sensación,
            qué es una inferencia y qué es un hecho observado. No todo el que
            se equivoca miente, y no todo lo que parece un sueño lo es.
            TXT,

        // Archivo esperado en public/immersion/entre-sueno-y-vigilia/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/entre-sueno-y-vigilia).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Una clínica del sueño. Una directora muerta. Todos los testigos estaban dormidos.',
            'bullets' => [
                '4 a 6 investigadores cruzando registros de sueño, accesos y un audio ambiental',
                'Cinco sospechosos, dos testigos y el sistema de monitoreo a quienes interrogar',
                '65 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 65,
        'min_players' => 3,
        'max_players' => 6,

        'price_amount' => 64900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/entre-sueno-y-vigilia/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 16,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Daniel, Patricia) and the secondary source (the sleep
    | monitoring system) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'julian-meza' => [
            'name' => 'Julián Meza',
            'role' => 'sospechoso',
            'file' => 'suspects/julian-meza.md',
            'photo' => 'julian-meza.png',
            'connection' => 'Co-investigador del estudio; segundo responsable bajo la dirección de Renata',
            'motive' => 'Impedir que Renata reportara ante el comité de ética su falsificación de datos del estudio',
            'alibi' => 'Dice haber pasado un momento por la sala de monitoreo y haber salido enseguida; la tarjeta registra más tiempo y un segundo acceso',
            'public' => [
                'age' => '44 años',
                'profile' => 'Co-investigador del estudio y segundo responsable bajo la dirección de la Dra. Solís. Investigador del sueño de formación, seguro de su criterio profesional.',
                'alibi' => 'Dice haber entrado un momento a revisar los monitores y haber salido enseguida.',
                'facts' => [
                    'Tiene tarjeta de acceso a la sala de monitoreo.',
                    'Mantenía con Renata la tensión profesional habitual entre colegas de investigación.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V7'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V5', 'V6'], 'confrontation' => true],
            ]],
        ],
        'bruno-castro' => [
            'name' => 'Bruno Castro',
            'role' => 'sospechoso',
            'file' => 'suspects/bruno-castro.md',
            'photo' => 'bruno-castro.png',
            'connection' => 'Participante voluntario del estudio, habitación 1',
            'motive' => 'Ninguno; su relato parece el menos fiable de todos, pero es sincero',
            'alibi' => 'Dormido en su habitación toda la noche, con un único microdespertar breve registrado por el sistema',
            'public' => [
                'age' => '36 años',
                'profile' => 'Participante voluntario del estudio. Duerme en la habitación 1, pegada a la sala de monitoreo. Confundido y algo avergonzado de lo que recuerda de esa noche.',
                'alibi' => 'Dice haber dormido en su habitación, conectado a los sensores.',
                'facts' => [
                    'Estuvo conectado a sensores desde las 22:00.',
                    'Cuenta haber tenido un sueño muy intenso esa noche.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['A2', 'V3'], 'confrontation' => true],
            ]],
        ],
        'camila-vega' => [
            'name' => 'Camila Vega',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-vega.md',
            'photo' => 'camila-vega.png',
            'connection' => 'Participante voluntaria del estudio, habitación 2',
            'motive' => 'Ninguno; su seguridad viene de una inferencia razonable pero mal fechada',
            'alibi' => 'Dormida casi toda la noche; despertar real y breve hacia las 03:00 confirmado por el sistema',
            'public' => [
                'age' => '29 años',
                'profile' => 'Participante voluntaria del estudio. Habla con seguridad y colabora sin reservas.',
                'alibi' => 'Dice haber dormido casi toda la noche y haberse levantado una vez.',
                'facts' => [
                    'Dice haber visto a Julián Meza en el pasillo esa noche.',
                    'Estuvo conectada a sensores desde las 22:00.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V4'], 'confrontation' => true],
            ]],
        ],
        'hector-paz' => [
            'name' => 'Héctor Paz',
            'role' => 'sospechoso',
            'file' => 'suspects/hector-paz.md',
            'photo' => 'hector-paz.png',
            'connection' => 'Participante del estudio, habitación 3',
            'motive' => 'Ninguno respecto a la muerte; exagera síntomas para respaldar una reclamación de incapacidad (secreto ajeno al caso)',
            'alibi' => 'Dormido en su habitación; registro de sueño sin eventos relevantes',
            'public' => [
                'age' => '50 años',
                'profile' => 'Participante del estudio por problemas de sueño. Complexión robusta. Evasivo y nervioso cuando se le pregunta por su salud.',
                'alibi' => 'Dice haber dormido en su habitación, al fondo del pasillo.',
                'facts' => [
                    'Es paciente del estudio, sin relación personal con la directora ni con los investigadores.',
                    'Su habitación es la más alejada de la sala de monitoreo.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V12'], 'confrontation' => true],
            ]],
        ],
        'laura-andrade' => [
            'name' => 'Laura Andrade',
            'role' => 'sospechosa',
            'file' => 'suspects/laura-andrade.md',
            'photo' => 'laura-andrade.png',
            'connection' => 'Enfermera y técnica de turno nocturno; colega clínica de Renata',
            'motive' => 'Ninguno respecto a la muerte; una amonestación reciente por una falta menor de protocolo, casi resuelta',
            'alibi' => 'En la estación de enfermería, vista por el técnico de guardia; sin lectura de tarjeta en la sala de monitoreo en la ventana crítica',
            'public' => [
                'age' => '38 años',
                'profile' => 'Enfermera y técnica de turno nocturno. Profesional y algo orgullosa de su trabajo.',
                'alibi' => 'Dice haber pasado la noche en la estación de enfermería, salvo una ronda por las habitaciones.',
                'facts' => [
                    'Colega clínica de la directora, con cierta fricción profesional reciente.',
                    'Tiene tarjeta de acceso a la sala de monitoreo.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['A2'], 'confrontation' => true],
            ]],
        ],
        'daniel-rojas' => [
            'name' => 'Daniel Rojas',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/daniel-rojas.md',
            'photo' => 'daniel-rojas.png',
            'connection' => 'Técnico de guardia',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '30 años',
                'profile' => 'Técnico de guardia. Vigilaba en vivo el panel de fases de sueño desde su puesto técnico, al fondo del pasillo.',
                'facts' => [
                    'Tomó un descanso breve esa noche; el estudio lo tenía previsto.',
                    'Abrió la sala de monitoreo con la Dra. Luna a las 06:30.',
                ],
            ],
            'accusable' => false,
        ],
        'patricia-luna' => [
            'name' => 'Dra. Patricia Luna',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/patricia-luna.md',
            'photo' => 'patricia-luna.png',
            'connection' => 'Directora clínica y supervisora de la clínica',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '55 años',
                'profile' => 'Directora clínica y supervisora. Conoce el contexto institucional del estudio y su relación con el patrocinador.',
                'facts' => [
                    'Llegó a la clínica a las 06:15, ya por la mañana.',
                    'Renata le había comentado, días antes, que iba a revisar unos datos del estudio.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-monitorizacion' => [
            'name' => 'Sistema de monitorización del sueño',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-monitorizacion.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-control-buque") — a shot
            // of the monitoring panel works as its "portrait".
            'photo' => 'sistema-monitorizacion.png',
            'connection' => 'Registros de fases de sueño, microdespertares y accesos por tarjeta a la sala de monitoreo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente técnica de la clínica: registra estados de sueño y vigilia de cada participante con su hora y los accesos por tarjeta, sin interpretar nada.',
                'facts' => [
                    'Registra con hora y duración exactas los microdespertares, que son interrupciones breves y normales del sueño.',
                    'No puede decir qué soñó ni qué percibió nadie.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['A2', 'V3']],
                ['questions' => 1, 'after' => ['V4', 'V7']],
            ]],
            'accusable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The ending
    |--------------------------------------------------------------------------
    */

    'solution' => [
        'culprit_slug' => 'julian-meza',

        'headline' => 'Julián Meza mató a la Dra. Renata Solís con un carrito de equipo para impedir que denunciara su falsificación de datos. Calculó que nadie recordaría nada fiable de esa hora de la noche, pero un microdespertar real de Bruno incorporó sonidos auténticos de la discusión a un sueño, y el error de fecha —no de invención— de Camila, una vez corregido, confirma su presencia.',

        'motive' => 'Renata había descubierto, revisando los datos crudos del estudio, que Julián los ajustaba para favorecer los resultados que esperaba el patrocinador farmacéutico, y pensaba reportarlo al comité de ética a la mañana siguiente.',

        'method' => 'Golpe con un carrito de equipo en la sala de monitoreo, hacia las 01:53, durante una confrontación que escaló. Julián había entrado a las 01:44, mientras el técnico de guardia estaba en su descanso; después recompuso mínimamente la escena y salió a las 02:03. Volvió a entrar a las 02:52 para terminar de recomponerla y limpiar el carrito, y salió a las 03:01.',

        'key_evidence' => [
            'El registro de sueño de Bruno muestra un microdespertar real de unos dos minutos a las 01:52, y el audio ambiental con esa misma marca de hora recoge una discusión y un golpe sordo filtrados por la pared.',
            'El registro de acceso por tarjeta sitúa a Julián en la sala de monitoreo de 01:44 a 02:03 y de 02:52 a 03:01, muy lejos del "entré un momento y salí enseguida" que declara.',
            'La comparación de datos crudos y procesados y las notas de Renata, con el nombre del responsable, prueban el motivo.',
            'El carrito de equipo aparece fuera de su posición habitual y limpiado de forma inusual.',
            'Camila vio a Julián en el pasillo y lo describió con precisión; corregida su hora (03:00, no las dos), coincide con su salida a las 03:01.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'bruno-castro' => 'No mintió ni inventó: su relato era sincero. A las 01:52 tuvo un microdespertar fisiológico normal de unos dos minutos, registrado por el sistema, y los sonidos reales de la discusión se incorporaron a un sueño sobre su hermano. Estuvo dormido en su habitación toda la noche, y sin tarjeta no podía abrir la sala de monitoreo. Su testimonio era la pista más valiosa del caso, no una fabulación.',

            'camila-vega' => 'No mintió: vio de verdad a Julián en el pasillo y lo describió con precisión, pero fechó el avistamiento a las dos de la madrugada por inferencia (Julián les había dicho que su ronda era hacia las dos), no por haber mirado la hora. Su registro de sueño sitúa su despertar real hacia las 03:00. Su error era de fecha, no de invención, y no tenía motivo ni acceso a la sala.',

            'hector-paz' => 'Sí ocultaba algo: exageraba sus síntomas de sueño para respaldar una reclamación de incapacidad, y por eso se mostraba evasivo. Pero ese secreto no tiene relación con Renata. Su registro muestra sueño continuo toda la noche, sin eventos, y no tiene tarjeta para abrir la sala de monitoreo.',

            'laura-andrade' => 'Sí minimizó su relación con Renata: había sido amonestada hacía poco por una falta menor de protocolo, casi resuelta, y le incomodaba mencionarlo por orgullo profesional. No es un motivo para agredir a nadie. El técnico de guardia la vio en la estación de enfermería a las 01:38 y a las 02:06, y su tarjeta no figura en la sala de monitoreo entre las 23:22 y las 06:30.',

            'daniel-rojas' => 'Es un testigo, no un sospechoso: su descanso programado de 01:40 a 02:05 dejó esa franja sin vigilancia directa, pero eso no lo vincula con nada. Su tarjeta no figura en la sala de monitoreo entre las 22:10 y las 06:30, no tenía motivo alguno y fue quien, con la Dra. Luna, abrió la sala a las 06:30.',

            'patricia-luna' => 'Es una testigo, no una sospechosa: solo conocía la intención de Renata de revisar unos datos y la reunión que le pidió para las 8:00. Llegó a la clínica a las 06:15 y su primera lectura de tarjeta en la sala es la de las 06:29. No tenía motivo alguno.',

            'sistema-monitorizacion' => 'Es un sistema automatizado de registro, no una persona: no puede haber intervenido. Su función fue confirmar, con exactitud, los estados de sueño y vigilia con su hora y los accesos por tarjeta, sin interpretar nunca el contenido de un sueño ni una intención. Nunca falló: lo que mostró era cierto.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Orus',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Llevaba meses ajustando los números para que el patrocinador viera lo que
            quería ver. Pensé que tendría tiempo de corregirlo antes de que alguien lo
            notara.

            Cuando Renata me dijo que ya lo sabía todo, y que lo reportaría por la mañana,
            no lo pensé con calma. Discutimos, y el carrito de equipo estaba al alcance de
            mi mano. No fue planeado: fue un segundo, y ya no hubo vuelta atrás. Arreglé la sala
            como pude y volví más tarde a terminar de recomponerla y a limpiar el carrito.

            Sabía que a esa hora todos deberían estar dormidos, que nadie recordaría nada
            con claridad. Ese era mi cálculo.

            No conté con que alguien pudiera recordar algo real, aunque fuera a medias
            entre el sueño y la vigilia. Y no debí confiar en eso para sentirme a salvo.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Con qué y cómo la mataron?',
            'hint' => 'El objeto usado, el lugar y, si puedes, la franja horaria.',
            'points' => 12,
            'guidance' => 'Lo esencial: golpe con el carrito de equipo en la sala de monitoreo, hacia las 01:53. No exigir el horario exacto de entradas y salidas.',
        ],
        'motive' => [
            'label' => '¿Por qué la mataron?',
            'hint' => 'Qué había descubierto la Dra. Solís y qué pensaba hacer al día siguiente.',
            'points' => 13,
        ],
        'questions' => [
            [
                'key' => 'testimonio_bruno',
                'label' => '¿Qué valor tiene el recuerdo de Bruno Castro de esa noche?',
                'hint' => 'Di si era sueño, invento o algo real, y en qué registros te apoyas.',
                'answer' => 'Es un recuerdo real, parcial y sincero: a las 01:52 Bruno tuvo un microdespertar fisiológico normal de unos dos minutos, registrado por el sistema, y los sonidos reales de la discusión y del golpe, filtrados por la pared, se incorporaron a un sueño sobre su hermano. El audio ambiental con la misma marca de hora lo confirma.',
                'points' => 20,
                'guidance' => 'Cero si descarta el testimonio de Bruno como sueño o mentira sin cruzarlo con su registro de sueño y el audio. Puntos completos con: microdespertar real + sonidos auténticos mezclados con un sueño + sinceridad.',
            ],
            [
                'key' => 'error_de_camila',
                'label' => '¿Qué hay de cierto y qué hay de erróneo en lo que Camila Vega dice haber visto?',
                'hint' => 'Separa lo que vio de lo que dedujo, y explica de dónde sale el error.',
                'answer' => 'Camila vio de verdad a Julián en el pasillo y lo describió con precisión, pero fechó el avistamiento a las dos de la madrugada por inferencia (Julián había dicho que su ronda era hacia las dos), no por mirar la hora. Su registro de sueño sitúa su despertar hacia las 03:00, que coincide con la salida de Julián de la sala a las 03:01. Es un error honesto de fecha, no una mentira.',
                'points' => 15,
                'guidance' => 'Pocos puntos si la acusa de mentir. Puntos completos si distingue el avistamiento real del error de hora por inferencia.',
            ],
            [
                'key' => 'como_fueron_utilizados',
                'label' => '¿Con qué contaba el culpable para que nadie lo descubriera?',
                'hint' => 'Explica en qué se basaba su coartada y qué no previó.',
                'answer' => 'Julián no manipuló ningún registro: confió en su conocimiento como investigador del sueño de que cualquier recuerdo de esa hora sonaría a sueño o confusión y sería fácil de descartar. No previó el microdespertar real de Bruno ni la descripción física precisa de Camila.',
                'points' => 10,
                'guidance' => 'Restar si afirma que el culpable alteró registros o indujo los recuerdos. Basta con la apuesta a que nadie creería un recuerdo nocturno y lo que no previó.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+13/T+26/T+39/T+52/T+65 delivery
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases). T+58 is an addition: the design's "coartadas cruzadas" fail-safe.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Entre Sueño y Vigilia — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V2'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — la clínica durante la noche',

            'audio_file' => 'a1-ambiente-clinica-noche.wav',
            'audio_scene' => 'Ambiente sonoro de una clínica durante la noche: silencio interior con un leve zumbido de equipos médicos y pasos distantes y suaves. Sin música.',
            'audio_context' => 'Daniel, técnico de guardia, comenta en voz baja y con tono profesional y rutinario que todo está tranquilo durante las rondas. Una noche de estudio sin incidentes aparentes. Duración aproximada: 10-12 segundos.',
            'audio_speaker' => 'Speaker 1 - Daniel Rojas (voz distante)',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                Todo tranquilo por aquí, sigo las rondas normales.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 13,
            'title' => 'Expansión — quién estaba en la clínica',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            // V9 added here during authoring: never scheduled in the design.
            'evidence_codes' => ['V9', 'V10', 'V11'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — un registro de sueño (B. C.)',
            'source_file' => 'pista-privada-registro-bruno.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V3 PARTIAL (no hour, no
            // duration). The complete V3 arrives at T+39.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — otro registro de sueño (C. V.)',
            'source_file' => 'pista-privada-registro-camila.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V4 PARTIAL (no hour). The
            // complete V4 arrives at T+52 and is what Camila's confrontation
            // requires.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 39,
            'title' => 'Complicación — un sonido que sí existió',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // V12 added here during authoring: never scheduled in the design,
            // and Héctor's confrontation requires it at T+39. V5 has no
            // responsible name (only the code INV-02); V6 names it at T+52.
            'evidence_codes' => ['V3', 'V5', 'V12'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 39,
            'title' => 'Audio — micrófono ambiental de la habitación 1',

            'audio_file' => 'a2-audio-ambiental-habitacion.wav',
            'audio_scene' => 'Grabación ambiental de un micrófono de monitorización clínica en una habitación de estudio del sueño, muy tenue y filtrada a través de una pared: se oye apenas una voz masculina tensa, con palabras parcialmente ininteligibles, y de fondo un murmullo femenino bajo y firme, sin palabras identificables; termina en un sonido sordo y breve seguido de silencio. Sin música ni contenido gráfico. Duración aproximada: 15-18 segundos.',
            'audio_context' => 'Dos adultos discuten en la sala contigua: él suplica con tono tenso y se le entiende a medias; ella solo se percibe como un murmullo firme y contenido, sin palabras. La ambigüedad debe venir del sonido real, de la pared y de la distancia, no de ocultar información: debe oírse a duras penas, sin nombres claros.',
            'audio_speaker' => 'Speaker 1 - Julián Meza (voz masculina tensa, discusión filtrada por una pared, parcialmente audible)',
            'audio_voice' => 'Orus',
            'audio_script' => <<<'TXT'
                No puedes hacer esto, no así... dame tiempo.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Giro — lo que cada recuerdo valía',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4', 'V6', 'V7', 'V8'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 58,
            'title' => 'Cierre — coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 65,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para distinguir qué recuerdos eran reales, cuáles eran inferencias presentadas como recuerdos y quién, de verdad, mintió. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/entre-sueno-y-vigilia/gallery. Partial
    | (private) and complete versions of V3/V4 are DIFFERENT files.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-clinica.png', 'caption' => 'Plano funcional de la Clínica del Sueño Aurora'],
            ['file' => 'v2-sala-monitoreo-escena.png', 'caption' => 'La sala de monitoreo al hacer el hallazgo'],
        ],
        'expansion.md' => [
            ['file' => 'v9-habitacion-bruno.png', 'caption' => 'La habitación 1, la de Bruno Castro'],
            ['file' => 'v10-horario-estudio.png', 'caption' => 'Agenda del estudio nocturno: rondas y responsables'],
            ['file' => 'v11-escritorio-renata.png', 'caption' => 'El escritorio de la directora'],
        ],
        'pista-privada-registro-bruno.md' => [
            ['file' => 'v3a-registro-bruno-parcial.png', 'caption' => 'Fragmento de un registro de sueño (B. C.)'],
        ],
        'pista-privada-registro-camila.md' => [
            ['file' => 'v4a-registro-camila-parcial.png', 'caption' => 'Fragmento de un registro de sueño (C. V.)'],
        ],
        'complicacion.md' => [
            ['file' => 'v3-registro-bruno.png', 'caption' => 'Registro de sueño completo de Bruno Castro'],
            ['file' => 'v5-datos-crudos-vs-procesados.png', 'caption' => 'Comparación entre datos crudos y datos reportados'],
            ['file' => 'v12-expediente-hector.png', 'caption' => 'Expediente clínico de Héctor Paz'],
        ],
        'giro.md' => [
            ['file' => 'v7-registro-acceso-sala.png', 'caption' => 'Registro de acceso por tarjeta a la sala de monitoreo'],
            ['file' => 'v6-notas-renata.png', 'caption' => 'Notas de trabajo de la directora'],
            ['file' => 'v8-carrito-equipo.png', 'caption' => 'El carrito de equipo de la sala de monitoreo'],
            ['file' => 'v4-registro-camila.png', 'caption' => 'Registro de sueño completo de Camila Vega'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v3-registro-bruno.png', 'caption' => 'El microdespertar real de Bruno'],
            ['file' => 'v4-registro-camila.png', 'caption' => 'La hora real del despertar de Camila'],
            ['file' => 'v7-registro-acceso-sala.png', 'caption' => 'Quién estuvo dentro de la sala, y cuánto tiempo'],
            ['file' => 'v6-notas-renata.png', 'caption' => 'El motivo que Renata había descubierto'],
            ['file' => 'v8-carrito-equipo.png', 'caption' => 'El carrito, limpiado de forma inusual'],
        ],
    ],
];
