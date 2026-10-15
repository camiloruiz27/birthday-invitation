<?php

/**
 * Case manifest: "La Voz de la Montaña"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: nobody forged Adrián's voice. His radio report is authentic;
 * it was emitted at 11:52 and the Collado Sur repeater held it (low-signal
 * store-and-forward) until a search team entered its coverage at 16:48. The
 * mistake is collective and human: the camp read "a transmission attributed to
 * his radio was received" as "he is alive right now". Whatever the systems
 * (Red Centinela) show is exact. No evidence or confrontation may suggest a
 * cloned voice, a replayed recording or a manipulated log.
 *
 * Concrete clock used across every file (the design doc only says "esa
 * mañana", "cerca del mediodía"...): departure 07:00, separation at the
 * Bifurcación de la Canaleta 08:40, Adrián at the intermediate refuge ~10:20,
 * Ignacio ~25 min later, report 11:52, fall shortly after, Valentina at the
 * upper refuge 14:20 (radio alert 14:33), search activated 15:00, retransmission
 * 16:48, "contacto confirmado" entry 16:53, body found at dawn.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 5-component point scoring
 *    (responsable / método / motivo / explicación de la transmisión /
 *    negligencia previa) is NOT implemented; the last two are requested inside
 *    the free-text motive (see the T+85 unlock text) and explained in
 *    solucion.md and in each exoneration. The "penalización por tratar la
 *    transmisión como prueba de vida" is likewise not scored.
 *  - Questions: 5 per person, plus 3 for those with confrontations (8), opened
 *    by `interrogation.stages` according to the evidence delivered (the doc's
 *    "5 + 2 confrontaciones" is folded into that engine rule).
 *  - Timeline minutes: the doc's T+0/15/30/50/68/85 are kept (T+0 -> 1).
 *    Added one event the doc lacks: T+75 "cierre" (cierre.md), which carries the
 *    doc's three fail-safes that were never scheduled (cross-alibis for
 *    Renato/Camila, the escuchar/recibir/hablar summary). The doc's "testimonio
 *    ampliado de Hernán" at T+68 is a paragraph of giro.md plus a gated
 *    expansion in his md (V9).
 *  - V2 arrives partial at T+15 (v2a-bitacora-parcial.png, no evidence code,
 *    only entries 15:00, 15:20 and 16:53) and complete at T+50 (v2-bitacora-
 *    completa.png, code V2). Different files because the content changes.
 *  - A2 (Ignacio's confession) is not a timeline event: it is
 *    solution.confession_script, generated per game, as in the other cases.
 *  - The design gives no discriminating evidence for Renato's and Camila's
 *    confrontations ("evidence_required" absent in the JSON). They are gated on
 *    two T+50 documents: V14 (which lists Renato's guides with credentials "por
 *    verificar") and V2-complete (which logs Camila's query about the rescue
 *    insurance). Both additions are minor red-herring lines in documents that
 *    already exist; they contradict nothing in the design.
 *  - Tomás's confrontation is gated on V2 or V7 plus the player citing Silvia's
 *    testimony (testimony is not an evidence code). Ignacio's c1 and Valentina's
 *    c2 likewise require the player to cite Hernán's testimony.
 *  - Red Centinela would answer "when was it emitted?" at T+15, before the
 *    T+50 complication. Its md gates the 11:52/16:48 hours on V7 and the
 *    store-and-forward explanation on V8; before that it says the report has not
 *    been released to the team.
 *  - The doc's 3.2 says Hernán places Valentina arriving at the upper refuge;
 *    he runs the INTERMEDIATE refuge and cannot see that. Resolved: Hernán
 *    confirms Adrián passed alone and that he never saw Valentina pass (she took
 *    the Variante del Hombro); her arrival at the upper refuge (14:20) is
 *    documented only by her own route log (V6) and the 14:35 bitácora entry.
 *  - Tomás's design lie ("hablé con él; estaba vivo", now in the past tense)
 *    collides with the global rule
 *    that nobody may confirm direct contact after the noon report. Resolved in
 *    his md: it is flagged as his exaggeration, he gives no invented details
 *    (only repeats A1's words) and no other character corroborates it.
 *  - V14 (task sheet) is described as "esa mañana" although the rescue only
 *    started at 15:00. Resolved: it is the day's assignment sheet with a morning
 *    block (no task for Ignacio) and an afternoon block (coordinator from 15:00).
 *  - V4/V5 (T+30 private clues) and V13/V15 are COMPLETE documents, not
 *    fragments, so they carry evidence codes (precedent: desaparecida-en-
 *    directo). They are delivered as two random_player events with two pieces
 *    each (1–2 pistas per player). Ignacio's c2 needs V4, V5 and V10 (T+68).
 *  - The partial/complete split of V2 is the only fragment in this case; there
 *    is no V10a-style partial.
 *  - Victim display: `victim.label` is "Desaparecido" and the shared AI prompts
 *    say "la desaparición de", so the screens do not telegraph that Adrián is
 *    dead. The case is framed retrospectively: the Comisión de Revisión de
 *    Operativos reconstructs that day, so Ignacio and Tomás speak of the camp's
 *    hope in the past tense (gancho.md / expansion.md say so) and nobody states
 *    the outcome. Marcos gives only "llegó a las 16:48, con normalidad" until V7;
 *    V1 (map) fixes the order Campamento → Bifurcación → Refugio Intermedio →
 *    Paso → Refugio Superior, with the Variante del Hombro bypassing both.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'La Voz de la Montaña',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Cerro Centinela',
    'authority' => 'Comisión de Revisión de Operativos de la Federación de Guías',

    'victim' => [
        'name' => 'Adrián Castel',
        'label' => 'Desaparecido',
        'photo' => 'adrian-castel.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 5 preguntas + 2 confrontations (1 for
        // Renato, Camila, Tomás). The engine only supports one flat number per
        // case, same precedent as the other cases.
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
        'tagline' => 'Todos escucharon su voz por radio. Lo difícil es saber qué significaba escucharla.',

        'description' => <<<'TXT'
            Cerro Centinela: una montaña de terreno técnico y cobertura de radio
            irregular. Adrián Castel, guía de montaña con años de experiencia,
            no llega al refugio superior y se activa un operativo de rescate.
            Horas después, el campamento base recibe por radio su voz: un parte
            de rutina, tranquilo. El alivio es inmediato.

            Tu equipo (4 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, dos testigos y a
            la propia red de repetidores de radio durante 85 minutos, cruzando
            rutas, horarios, bitácoras y registros técnicos.

            Cada persona del operativo domina una función distinta: la ruta, la
            radio, la logística, la escucha. Todos guardan algo, pero no todo lo
            que se calla tiene que ver con lo que le pasó a Adrián.
            TXT,

        // Archivo esperado en public/immersion/la-voz-de-la-montana/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-voz-de-la-montana).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Un guía desaparece en el Cerro Centinela. Horas después, su voz llega por radio y todo el campamento respira.',
            'bullets' => [
                '4 a 7 investigadores cruzando rutas, bitácoras y registros de radio',
                'Seis sospechosos, dos testigos y la red de repetidores a quienes interrogar',
                '85 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 85,
        'min_players' => 3,
        'max_players' => 7,

        'price_amount' => 69900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/la-voz-de-la-montana/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Silvia, Hernán) and the secondary source (Red
    | Centinela) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'ignacio-bravo' => [
            'name' => 'Ignacio Bravo',
            'role' => 'sospechoso',
            'file' => 'suspects/ignacio-bravo.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V14'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V4', 'V5', 'V10'], 'confrontation' => true],
            ]],
            'photo' => 'ignacio-bravo.png',
            'connection' => 'Coordinador de campo del operativo; antiguo mentor de Adrián',
            'motive' => 'Impedir que Adrián denunciara ante la federación de guías una negligencia antigua que él encubrió',
            'alibi' => 'Dice no haber tenido contacto con Adrián ese día; sin registro oficial de dónde estuvo esa mañana',
            'public' => [
                'age' => '52 años',
                'profile' => 'Coordinador de campo del operativo de rescate y antiguo mentor de Adrián. Seguro, paternal y muy respetado en el campamento.',
                'alibi' => 'Dice no haber tenido contacto con Adrián hasta que se activó la búsqueda.',
                'facts' => [
                    'Se ofreció como coordinador de campo cuando se activó la búsqueda oficial.',
                    'Comparte con Adrián una larga trayectoria como guía de montaña.',
                ],
            ],
        ],
        'valentina-ruiz' => [
            'name' => 'Valentina Ruiz',
            'role' => 'sospechosa',
            'file' => 'suspects/valentina-ruiz.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V6'], 'confrontation' => true],
            ]],
            'photo' => 'valentina-ruiz.png',
            'connection' => 'Compañera de cordada de Adrián esa mañana',
            'motive' => 'Ninguno respecto a la muerte; se siente culpable por haberse separado de Adrián antes de lo recomendado (error de juicio, acto distinto)',
            'alibi' => 'Siguió su propia variante de ruta, lejos del Paso del Águila, y llegó sola al refugio superior; confirmado por su registro de ruta y por Hernán',
            'public' => [
                'age' => '34 años',
                'profile' => 'Guía y compañera de cordada de Adrián esa mañana. Está afectada y repasa una y otra vez el ascenso.',
                'alibi' => 'Dice que siguieron juntos hasta el refugio intermedio.',
                'facts' => [
                    'Es la última persona que subió con Adrián esa mañana.',
                    'Fue quien avisó por radio de que Adrián no había llegado al refugio superior.',
                ],
            ],
        ],
        'marcos-elizalde' => [
            'name' => 'Marcos Elizalde',
            'role' => 'sospechoso',
            'file' => 'suspects/marcos-elizalde.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V7'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V8'], 'confrontation' => true],
            ]],
            'photo' => 'marcos-elizalde.png',
            'connection' => 'Operador de la base de comunicaciones del operativo',
            'motive' => 'Ninguno respecto a la muerte; notó una anomalía en el registro del repetidor y no la reportó por miedo a que se le atribuyera un fallo del sistema (acto distinto)',
            'alibi' => 'En la base de comunicaciones toda la tarde, confirmado por el propio registro del sistema',
            'public' => [
                'age' => '39 años',
                'profile' => 'Operador de la base de comunicaciones: recibe y registra todas las transmisiones del operativo. Técnico, meticuloso y celoso de su sistema.',
                'alibi' => 'Dice haber estado en la base de comunicaciones durante toda la tarde.',
                'facts' => [
                    'Gestiona el sistema de radio y los registros de los repetidores.',
                    'Es quien mejor conoce cómo viajan las transmisiones por la red del cerro.',
                ],
            ],
        ],
        'renato-salcedo' => [
            'name' => 'Renato Salcedo',
            'role' => 'sospechoso',
            'file' => 'suspects/renato-salcedo.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V14'], 'confrontation' => true],
            ]],
            'photo' => 'renato-salcedo.png',
            'connection' => 'Guía y director de una empresa de guías rival',
            'motive' => 'Ninguno respecto a la muerte; oculta que emplea guías sin certificación vigente (sin relación con Adrián)',
            'alibi' => 'En un sector distinto de la montaña con su grupo, confirmado por un cliente de ese grupo',
            'public' => [
                'age' => '45 años',
                'profile' => 'Guía y director de una empresa de guías que opera en la misma zona. Cordial y seguro de sí mismo, aunque se tensa al hablar de su negocio.',
                'alibi' => 'Dice haber estado todo el día con su grupo en la Cresta Este.',
                'facts' => [
                    'Compite con Adrián por clientes y contratos en la zona.',
                    'Su grupo estaba registrado en otro sector del cerro esa jornada.',
                ],
            ],
        ],
        'camila-ibarra' => [
            'name' => 'Camila Ibarra',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-ibarra.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V2'], 'confrontation' => true],
            ]],
            'photo' => 'camila-ibarra.png',
            'connection' => 'Hermana y socia de Adrián',
            'motive' => 'Ninguno respecto a la muerte; oculta las deudas de la empresa que comparten, y la muerte de Adrián la perjudica financieramente (sin relación con el caso)',
            'alibi' => 'En el campamento base todo el día, confirmada por Silvia',
            'public' => [
                'age' => '38 años',
                'profile' => 'Hermana y socia de Adrián en su empresa de guías. Contenida y muy protectora del legado de su hermano.',
                'alibi' => 'Dice haber permanecido en el campamento base todo el día.',
                'facts' => [
                    'Comparte con Adrián la propiedad y la gestión de la empresa de guías.',
                    'Estaba en el campamento base cuando se supo que Adrián no había llegado.',
                ],
            ],
        ],
        'tomas-vidal' => [
            'name' => 'Tomás Vidal',
            'role' => 'sospechoso',
            'file' => 'suspects/tomas-vidal.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V2'], 'confrontation' => true],
            ]],
            'photo' => 'tomas-vidal.png',
            'connection' => 'Rescatista; puesto de escucha de radio en el campamento base',
            'motive' => 'Ninguno respecto a la muerte; exageró un registro pasivo como si fuera contacto directo y se ausentó de su puesto sin autorización (error de entusiasmo)',
            'alibi' => 'En el campamento base toda la tarde, confirmado por Silvia, quien también confirma su breve ausencia del puesto',
            'public' => [
                'age' => '26 años',
                'profile' => 'Rescatista del operativo asignado al puesto de escucha de radio del campamento base. Joven, entusiasta y ansioso por demostrar su valía.',
                'alibi' => 'Dice haber estado en su puesto de escucha durante toda la tarde.',
                'facts' => [
                    'Fue quien anunció en el campamento que Adrián seguía con vida.',
                    'Firma como responsable la entrada de la bitácora que recoge la transmisión.',
                ],
            ],
        ],
        'silvia-paredes' => [
            'name' => 'Silvia Paredes',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/silvia-paredes.md',
            'photo' => 'silvia-paredes.png',
            'connection' => 'Jefa de logística del campamento base',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '44 años',
                'profile' => 'Jefa de logística del campamento base: coordina equipos, turnos y suministros del operativo.',
                'facts' => [
                    'Estuvo en el campamento base durante toda la tarde.',
                    'Presenció el ambiente en el campamento cuando llegó la transmisión.',
                ],
            ],
            'accusable' => false,
        ],
        'hernan-quispe' => [
            'name' => 'Hernán Quispe',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/hernan-quispe.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V9']],
            ]],
            'photo' => 'hernan-quispe.png',
            'connection' => 'Encargado del refugio intermedio',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '58 años',
                'profile' => 'Encargado del refugio intermedio, por donde pasan las cordadas rumbo al Paso del Águila.',
                'facts' => [
                    'Atendió el refugio durante toda la jornada.',
                    'Desde el refugio ve quién sube y quién baja por la ruta principal.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-red-centinela' => [
            'name' => 'Red Centinela — registro de repetidores',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-red-centinela.md',
            'interrogation' => ['stages' => [
                ['questions' => 2],
                ['questions' => 2, 'after' => ['V7']],
                ['questions' => 1, 'after' => ['V8']],
            ]],
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-control-buque") — a shot
            // of the communications console works as its "portrait".
            'photo' => 'sistema-red-centinela.png',
            'connection' => 'Centro de comunicaciones: identificadores de radio, repetidores y horas de cada transmisión',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Sistema que gestiona los repetidores de radio del cerro: registra qué radio emitió cada transmisión, qué repetidor la procesó y a qué hora.',
                'facts' => [
                    'Registra el identificador de radio, el repetidor y la hora de cada transmisión.',
                    'No puede decir quién iba al otro lado de una radio ni interpretar intenciones.',
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
        'culprit_slug' => 'ignacio-bravo',

        'headline' => 'La voz de Adrián era auténtica, pero la emitió horas antes de que el campamento la oyera: ya había muerto cuando todos creyeron que estaba vivo. Ignacio Bravo lo alcanzó en el Paso del Águila para impedir que denunciara una negligencia antigua, y en el forcejeo lo empujó.',

        'motive' => 'Impedir que Adrián denunciara ante la federación de guías la negligencia que Ignacio encubrió hace dos temporadas, cuando un cliente murió por un fallo del sistema de aseguramiento que él no revisó según el protocolo obligatorio.',

        'method' => 'Confrontación en un tramo sin cuerda fija del Paso del Águila, minutos después del parte de radio de Adrián (11:52); forcejeo que terminó en un empujón y una caída mortal, no un accidente simple. Ignacio bajó por otra ruta y se sumó a la búsqueda como coordinador.',

        'key_evidence' => [
            'Red Centinela: la transmisión de Adrián se emitió a las 11:52 y se retransmitió a las 16:48; el repetidor de Collado Sur la retuvo por baja señal.',
            'El plan de ruta y la cámara de acción de Adrián lo sitúan en el Paso del Águila a la hora del parte, con una silueta que se le acerca en el último fotograma.',
            'Hernán lo vio pasar solo y, unos veinticinco minutos después, vio pasar a Ignacio en la misma dirección; la hoja de asignación no le daba ninguna tarea en ese sector.',
            'El cuaderno de Adrián, el informe de inspección con la firma faltante y su mensaje a Ignacio muestran el motivo y el aviso previo.',
            'Su mosquetón apareció abierto y su cuerpo fuera de la ruta de caída habitual: incompatible con una caída accidental simple.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'valentina-ruiz' => 'Sí se separó de Adrián antes de lo que recomienda el protocolo y lo calló por culpa y miedo a ser señalada, pero fue una decisión de juicio, no un acto de violencia. Su registro de ruta la ubica en la Variante del Hombro, lejos del Paso del Águila durante todo el mediodía, y llegando sola al refugio superior a las 14:20; Hernán nunca la vio pasar por el refugio intermedio.',

            'marcos-elizalde' => 'Sí notó el desfase de horas en el registro del repetidor y no lo reportó, por miedo a que se le atribuyera un fallo del sistema. Es una omisión técnica propia, ajena a la muerte de Adrián. Estuvo en la base de comunicaciones toda la tarde, y el propio registro del sistema muestra su sesión abierta y continua.',

            'renato-salcedo' => 'Minimizaba su rivalidad con Adrián para no hablar de los guías sin certificación vigente que emplea en su empresa, un secreto real pero sin relación con el caso. Estuvo con su grupo en la Cresta Este, un sector distinto del cerro, y un cliente de su propio grupo lo confirma.',

            'camila-ibarra' => 'Ocultaba las deudas conjuntas de la empresa que compartía con su hermano, y temía que salieran a la luz. La muerte de Adrián la perjudicaba financieramente, no la beneficiaba. Estuvo en el campamento base todo el día, y Silvia lo confirma.',

            'tomas-vidal' => 'Exageró un registro pasivo como si fuera una conversación en vivo y se ausentó unos minutos de su puesto sin autorización. Fue un error de interpretación y entusiasmo, no de intención: nunca habló con Adrián y no tenía motivo alguno. Estuvo en el campamento base, y Silvia confirma su ubicación.',

            'silvia-paredes' => 'Es una testigo, no una sospechosa: su aporte fue confirmar el alivio general en el campamento, la breve ausencia de Tomás y la presencia de Camila en la base. Estuvo en el campamento toda la tarde y no tuvo relación alguna con Adrián ni con el Paso del Águila.',

            'hernan-quispe' => 'Es un testigo, no un sospechoso: desde el refugio intermedio vio pasar a Adrián solo y, poco después, a Ignacio, y nada más. No subió al Paso del Águila ni tenía motivo alguno.',

            'sistema-red-centinela' => 'Es un sistema automatizado de registro, no una persona: no puede haber intervenido. Retuvo la transmisión de Adrián por baja señal y la liberó cuando otro equipo entró en cobertura, tal como está documentado. Su registro de horas fue siempre exacto; el error fue de quienes interpretaron "transmisión recibida" como "contacto en vivo".',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it. Based on the design doc's A2.
        'confession_script' => <<<'TXT'
            Ese informe no contaba toda la historia. Fue un accidente, uno solo, en años de
            hacer las cosas bien. No podía dejar que Adrián tirara todo eso a la basura con
            una denuncia.

            Subí a hablar con él, nada más. Le pedí que lo pensara, que no fuera a la
            federación. Se negó, y en ese tramo, sin cuerda, forcejeamos. No planeé que
            cayera.

            Bajé por otro lado. Me uní a la búsqueda como si no supiera nada. Cuando
            escuchamos su voz por radio esa tarde, por un momento hasta yo quise creer que
            seguía vivo. Pero ya sabía que no.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 25,
        'weapon' => [
            'label' => '¿Cómo murió Adrián?',
            'hint' => 'Dónde ocurrió, qué pasó en ese momento y por qué no fue un accidente simple.',
            'points' => 20,
            'guidance' => 'Lo esencial: confrontación en un tramo sin cuerda fija del Paso del Águila, forcejeo y empujón que causa la caída. Pocos puntos si lo describe como un accidente o una caída sin intervención de nadie.',
        ],
        'motive' => [
            'label' => '¿Por qué lo mataron?',
            'hint' => 'Qué pensaba hacer Adrián y a quién perjudicaba.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'explicacion_transmision',
                'label' => '¿Cómo se explica la voz de Adrián que el campamento oyó por radio esa tarde?',
                'hint' => 'Di si la voz es auténtica, cuándo se emitió, cuándo se recibió y por qué.',
                'answer' => 'La voz es auténtica: Adrián emitió su parte de rutina con vida a las 11:52, poco antes del encuentro con Ignacio, desde una zona de señal débil del Paso del Águila. El repetidor de Collado Sur retuvo la transmisión y la liberó a las 16:48, cuando un equipo de búsqueda entró en su cobertura, y el campamento confundió esa retransmisión con un contacto en vivo.',
                'points' => 20,
                'guidance' => 'Cero si trata la transmisión como prueba de que Adrián estaba vivo a la hora en que se oyó, o si sostiene que la voz fue falsificada. Puntos completos con: voz auténtica + emitida antes + retenida y liberada después por el repetidor.',
            ],
            [
                'key' => 'negligencia_previa',
                'label' => '¿Qué hecho anterior se había ocultado y qué relación tiene con la muerte?',
                'hint' => 'Cuenta qué pasó, quién lo encubrió y cómo se conecta con este caso.',
                'answer' => 'Hace dos temporadas, Ignacio no revisó según el protocolo el sistema de aseguramiento en una expedición que dirigía y murió un cliente; el caso se archivó como «accidente inevitable» y él lo encubrió en lugar de reportarlo. Es un hecho separado del asesinato de Adrián: es el motivo, porque Adrián iba a denunciarlo ante la federación de guías.',
                'points' => 15,
                'guidance' => 'Restar si funde la negligencia y el asesinato en un solo hecho. Basta con la negligencia de Ignacio con el cliente fallecido, su encubrimiento y que es distinta del asesinato.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+15/T+30/T+50/T+68/T+85 delivery
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases), plus one extra T+75 event for the doc's fail-safes.
    |
    | `evidence_codes` opts this case into "Level 2": an interrogation can check
    | which pieces of evidence the table already holds before a confrontation
    | works. V2 (partial, T+15) carries no code on purpose.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Voz de la Montaña — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 15,
            'title' => 'Expansión — la buena noticia',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            // V2 partial: deliberately no evidence_codes. The complete V2 only
            // arrives at T+50 and is what Tomás's and Camila's confrontations use.
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 15,
            'title' => 'Audio — transmisión de radio de Adrián',

            'audio_file' => 'a1-transmision-adrian.wav',
            'audio_scene' => 'Transmisión de radio de un guía de montaña durante un ascenso: viento moderado y ligera estática de radio de fondo. Sin música ni efectos.',
            'audio_context' => 'Adrián, un hombre adulto, da su parte de posición y estado como quien cumple un trámite de rutina: tono tranquilo, voz estable, sin ninguna señal de tensión, prisa o peligro. Debe sonar exactamente como un parte cualquiera, sin dramatismo ni distorsión.',
            'audio_speaker' => 'Speaker 1 - Adrián Castel',
            'audio_voice' => 'Sadaltager',
            'audio_script' => <<<'TXT'
                Aquí Adrián. Todo bien, avanzando según lo previsto, en el Paso del Águila.
                Cambio y corto.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 30,
            'title' => 'Fragmento privado — papeles de otra temporada',
            'source_file' => 'pista-privada-pasado.md',
            'delivery_mode' => 'random_player',
            // V4 and V5 are complete documents (not fragments), so they carry
            // codes: Ignacio's second confrontation is gated on them.
            'evidence_codes' => ['V4', 'V5'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 30,
            'title' => 'Fragmento privado — la ruta y un mensaje',
            'source_file' => 'pista-privada-ruta.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V13', 'V15'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Complicación — el registro de repetidores',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V2', 'V6', 'V7', 'V8', 'V14'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 68,
            'title' => 'Giro — dos tiempos en una misma montaña',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V9', 'V10', 'V11', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 75,
            'title' => 'Cierre — coartadas cruzadas y tres formas de contacto',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // Fail-safes from the design doc (§6); no new evidence.
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 85,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para separar lo que se oyó de lo que ocurrió. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-voz-de-la-montana/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-mapa-cerro-centinela.png', 'caption' => 'Mapa general del Cerro Centinela'],
            ['file' => 'v3-campamento-base.png', 'caption' => 'El campamento base durante la búsqueda'],
        ],
        'expansion.md' => [
            ['file' => 'v2a-bitacora-parcial.png', 'caption' => 'Bitácora oficial del operativo (copia parcial)'],
        ],
        'pista-privada-pasado.md' => [
            ['file' => 'v4-cuaderno-adrian.png', 'caption' => 'Página del cuaderno personal de Adrián'],
            ['file' => 'v5-informe-inspeccion.png', 'caption' => 'Informe de inspección de equipo de una expedición anterior'],
        ],
        'pista-privada-ruta.md' => [
            ['file' => 'v13-plan-de-ruta.png', 'caption' => 'Plan de ruta de Adrián'],
            ['file' => 'v15-mensaje-a-ignacio.png', 'caption' => 'Mensaje de Adrián enviado semanas antes del ascenso'],
        ],
        'complicacion.md' => [
            ['file' => 'v7-registro-red-centinela.png', 'caption' => 'Registro de repetidores de Red Centinela'],
            ['file' => 'v8-nota-tecnica-repetidores.png', 'caption' => 'Extracto del manual técnico del sistema'],
            ['file' => 'v2-bitacora-completa.png', 'caption' => 'Bitácora oficial del operativo (versión completa)'],
            ['file' => 'v6-checkpoints-valentina.png', 'caption' => 'Registro de ruta del dispositivo de Valentina'],
            ['file' => 'v14-asignacion-tareas.png', 'caption' => 'Hoja de asignación de tareas de la jornada'],
        ],
        'giro.md' => [
            ['file' => 'v9-camara-accion.png', 'caption' => 'Fotogramas de la cámara de acción de Adrián'],
            ['file' => 'v10-aseguramiento-anomalo.png', 'caption' => 'Sistema de aseguramiento hallado junto al cuerpo'],
            ['file' => 'v11-lugar-del-hallazgo.png', 'caption' => 'El lugar del hallazgo, fuera de la ruta habitual'],
            ['file' => 'v12-parte-meteorologico.png', 'caption' => 'Parte meteorológico interno de la jornada'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v7-registro-red-centinela.png', 'caption' => 'La voz: emitida a las 11:52, retransmitida a las 16:48'],
            ['file' => 'v9-camara-accion.png', 'caption' => 'Adrián en el Paso del Águila, y alguien que se acerca'],
            ['file' => 'v14-asignacion-tareas.png', 'caption' => 'Sin ninguna tarea para Ignacio en ese sector'],
            ['file' => 'v5-informe-inspeccion.png', 'caption' => 'El motivo: la firma que faltaba'],
            ['file' => 'v10-aseguramiento-anomalo.png', 'caption' => 'No fue un accidente simple'],
        ],
    ],
];
