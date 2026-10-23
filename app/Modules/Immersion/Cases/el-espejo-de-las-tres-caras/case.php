<?php

/**
 * Case manifest: "El Espejo de las Tres Caras"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: an aggression case (nobody dies) whose central trap is an
 * optical one. Three honest sources (Dámaris at Acceso A, Ulises at Acceso B and
 * the Control C camera) seem to describe three different people. They saw ONE
 * person, Dalila, reflected by different panels: the Corredor Central panels
 * (P1–P4) are "reflexión directa" (the image is where it seems to be); the
 * Octágono panels (P5–P8) re-reflect the image of the corridor ("reflexión
 * doble"). Acceso A looks through P2 (direct), Acceso B through P5 and Control C
 * through P7 (both double). V03 and V04 show a numbered plate on those panels so
 * the table can check them against V07 (ficha de superficies) by observation.
 * The three images must never say, before V07 and Norah allow it, that they show
 * the same person.
 *
 * Master schedule (single source of truth for every file of this case):
 *   Feria de Halloween, Laberinto de Espejos. Dalila's shift ends 21:30.
 *   22:00 cierre al público; the Corredor Central becomes personal-only.
 *   22:00 Ignacio starts his closing round (Corredor Central and Octágono).
 *   22:08 Dalila enters by the puerta de personal (Perla sees her; the paper
 *         notebook of V06 has an entry "D. O. 22:08" with no reason).
 *   22:14 Control C frame (V04, "22:14:37"): the figure, seen through P7. The
 *         figure is walking east along the Corredor Central towards the Octágono
 *         (Acceso A, through P2, sees her from behind). Ulises sees her image
 *         in the Octágono through P5.
 *   ~22:16 confrontation at the Octágono and push: Ignacio hits a panel and
 *         hurts his wrist.  ~22:18 Dalila leaves by the puerta de personal.
 *   ~22:19 Ignacio warns the cabina by radio.
 *   Window for the alibis: ~22:05–22:20. Rogelio: oficina de administración,
 *   sesión abierta y dos impresiones 21:40–22:35. Carolina: reunión de
 *   planificación (pabellón de diseño) 21:45–22:45. Esteban: taller, fuera del
 *   laberinto, hoja de herramientas 21:35 y 22:40.
 *   C1: Dalila's message was sent "esa tarde" (Hoy · 17:42, no calendar date).
 *   Plan geometry (V01, fixed grid in ASSET_PROMPTS.md): Acceso A at the west end
 *   of the Corredor Central; the corridor runs west→east into the Octágono;
 *   Acceso B on the north side of the Octágono; Control C on the south side;
 *   puerta de personal on the south wall of the corridor, a third of the way
 *   from Acceso A. Panels P1–P4 in the corridor (directos), P5–P8 in the
 *   Octágono (dobles).
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text how + free-text why. The doc's 30/20/20/30 scoring maps to
 *    culprit_points 30 + weapon ("qué ocurrió realmente") 20 + motive 20 +
 *    one extra question ("una sola persona reflejada") 30. The "penalty for
 *    assuming several people" becomes the guidance of that question: zero.
 *  - Ignacio is both the victim (label "Herido", victim.incident "la agresión
 *    a") and a witness of the roster (accusable false, same portrait file).
 *    He is interrogable only as a roster entry: the victim itself is not.
 *  - Doc inconsistency: Dalila enters "poco antes del cierre" (§3.1) vs "poco
 *    después" (cronología). Resolved: shortly AFTER the 22:00 closing, so the
 *    corridor is already personal-only and the notebook entry (22:08) matches.
 *  - Doc inconsistency: the confrontation is "en el octágono" (cronología) vs
 *    "dentro del Corredor Central" (§9). Resolved: she enters by the personal
 *    door, crosses the Corredor Central and meets Ignacio at the Octágono, where
 *    the push happens. Public texts only say "dentro del laberinto".
 *  - Ignacio cannot identify Dalila: the doc has "alguien me confrontó por el
 *    ascenso" but he would recognise a voice. Resolved: tenue lighting and
 *    voices that bounce and deform between the panels; he saw only the vest.
 *  - Dámaris and Ulises are last visitors waiting by the exits of Acceso A and
 *    Acceso B while the staff finish the closing sweep (the doc leaves open why
 *    visitors were there after the closing).
 *  - Vest rule: the blue technical vest belongs to the puesto de supervisión
 *    técnica (Dalila's role is public). That knowledge reaches the table only
 *    with Norah's expanded declaration (DN, T+45) and never as a named wearer.
 *    For fairness, Dalila's PORTRAIT does not show the vest (see ASSET_PROMPTS).
 *  - The ascenso is a process announced weeks ago and still open (Rogelio
 *    decides): the doc's "decisión de ascenso" is ambiguous.
 *  - Timeline: the doc's confrontations "T+45 intermedias" cannot exist because
 *    V06 and Perla's declaration arrive at T+55; every confrontation opens with
 *    its evidence (Dalila 2+1 at T+55; the three secondary suspects after the
 *    T+56 expedientes DR/DC/DE; Perla after V06+DP). Dalila's second
 *    confrontation cites Ignacio's testimony, which has no code (engine limit
 *    M5): it is gated on C1 only.
 *  - New text-only envelope (expedientes.md, codes DR, DC, DE): the doc says the
 *    secrets of Rogelio, Carolina and Esteban are delivered at T+55 but gives no
 *    evidence for them. Each is a neutral note of the organization (attendance
 *    figures 12.400 vs 9.150; folleto comparison; maintenance book P3/P8) that
 *    gates the corresponding confrontation. Esteban's note says explicitly that
 *    nothing touched the reflection, so the delayed maintenance is not read as
 *    tampering with the optics.
 *  - Private clue: V02 + "bandeja privada". The private event gives a CROP of
 *    V02 (figure only, no corridor, no plate) with no evidence code; the
 *    complete V02 reaches everyone at T+20 together with V03 and V07, which
 *    keeps the doc's "copia pública".
 *  - Norah is the only one who explains which panel each view crosses (the
 *    doc's "conocimiento exclusivo"); Esteban only authenticates the ficha V07.
 *  - Fail-safes: Norah confirms the rule on any direct question once V07 is
 *    delivered (T+20, before the doc's T+32); the crossed alibis go in
 *    coartadas.md (T+60).
 *  - A1 (Dalila's confession) is NOT a timeline audio: it is
 *    solution.confession_script, generated per game. The case therefore has no
 *    audio folder.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()). Codes
 * without an image: DN (Norah's declaration), DP (Perla's declaration), DR,
 * DC, DE (expedientes).
 */
return [

    'name' => 'El Espejo de las Tres Caras',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Feria de Halloween, Laberinto de Espejos',
    'authority' => 'Comité de Seguridad de la Feria',

    'victim' => [
        'name' => 'Ignacio Bermúdez',
        'label' => 'Herido',
        'incident' => 'la agresión a',
        'photo' => 'ignacio-bermudez.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        'interrogation_questions' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Catalog
    |--------------------------------------------------------------------------
    */

    'catalog' => [
        'tagline' => 'Tres testigos sinceros describen a tres personas distintas. Ninguno miente.',

        'description' => <<<'TXT'
            En el Laberinto de Espejos de una feria de Halloween, ya cerrado al
            público, alguien confronta a un operador y lo empuja contra un panel.
            Sale herido, no grave, pero no vio quién fue. Tres puntos de
            observación registraron a alguien moviéndose por el laberinto: dos
            testigos y una cámara de vigilancia. Sus descripciones no coinciden.

            Tu equipo (3 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco sospechosos, tres testigos y a
            la vigilante técnica de la cabina de control, durante 65 minutos,
            comparando un plano, tres vistas y una ficha técnica de los paneles.

            No hay óptica avanzada ni modelos 3D: basta observar con cuidado qué
            panel muestra cada imagen. Y separar lo que cada persona oculta de lo
            que de verdad ocurrió esa noche.
            TXT,

        // Archivo esperado en public/immersion/el-espejo-de-las-tres-caras/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-espejo-de-las-tres-caras).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Tres testigos sinceros describen a tres personas distintas. Ninguno miente.',
            'bullets' => [
                '3 a 7 investigadores comparando un plano, tres vistas y una ficha de paneles',
                'Cinco sospechosos, tres testigos y la vigilante de la cabina a interrogar',
                '65 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 65,
        'min_players' => 3,
        'max_players' => 7,

        'price_amount' => 54900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/el-espejo-de-las-tres-caras/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | The full testimony lives only in content/suspects/*.md. Ignacio (the
    | victim) is also a roster witness, with the same portrait as victim.photo.
    |
    */

    'suspects' => [
        'dalila-orozco' => [
            'name' => 'Dalila Orozco',
            'role' => 'sospechosa',
            'file' => 'suspects/dalila-orozco.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V06', 'DP'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V06', 'DP', 'C1'], 'confrontation' => true],
            ]],
            'photo' => 'dalila-orozco.png',
            'connection' => 'Supervisora técnica de atracciones; aspirante al ascenso a Encargado Principal de Atracciones',
            'motive' => 'La rivalidad laboral directa con Ignacio por el ascenso a Encargado Principal de Atracciones',
            'alibi' => 'Dice haberse ido directo a casa al terminar su turno, sin entrar al laberinto',
            'public' => [
                'age' => '37 años',
                'profile' => 'Supervisora técnica de atracciones de la feria. Profesional y segura, muy exigente con los equipos a su cargo.',
                'alibi' => 'Dice haberse ido directo a casa después de su turno.',
                'facts' => [
                    'Es una de las tres personas que aspiran al ascenso a Encargado Principal de Atracciones.',
                    'Conoce a fondo los equipos y las atracciones técnicas de la feria.',
                ],
            ],
        ],
        'rogelio-fuenmayor' => [
            'name' => 'Rogelio Fuenmayor',
            'role' => 'sospechoso',
            'file' => 'suspects/rogelio-fuenmayor.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['DR'], 'confrontation' => true],
            ]],
            'photo' => 'rogelio-fuenmayor.png',
            'connection' => 'Encargado General de Atracciones de la feria; decide el ascenso en disputa',
            'motive' => 'Ninguno respecto al incidente; infla las cifras de asistencia del laberinto para justificar presupuesto adicional',
            'alibi' => 'En la oficina de administración de la feria durante toda la ventana del incidente',
            'public' => [
                'age' => '50 años',
                'profile' => 'Encargado General de Atracciones de la feria. Ejecutivo y calmado, muy pendiente de los reportes y del presupuesto.',
                'alibi' => 'Dice haber estado en la oficina de administración de la feria.',
                'facts' => [
                    'Es quien decide el ascenso a Encargado Principal de Atracciones.',
                    'Reporta a los organizadores de la feria las cifras de asistencia de cada atracción.',
                ],
            ],
        ],
        'carolina-sotillo' => [
            'name' => 'Carolina Sotillo',
            'role' => 'sospechosa',
            'file' => 'suspects/carolina-sotillo.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['DC'], 'confrontation' => true],
            ]],
            'photo' => 'carolina-sotillo.png',
            'connection' => 'Supervisora de área; aspirante al mismo ascenso que Dalila e Ignacio',
            'motive' => 'Ninguno respecto al incidente; copió el diseño de un recorrido de una feria competidora',
            'alibi' => 'En una reunión de planificación en el pabellón de diseño durante toda la ventana del incidente',
            'public' => [
                'age' => '39 años',
                'profile' => 'Supervisora de área de la feria. Profesional y ambiciosa, orgullosa de su trabajo de diseño.',
                'alibi' => 'Dice haber estado en una reunión de planificación en otra área de la feria.',
                'facts' => [
                    'Es una de las tres personas que aspiran al ascenso a Encargado Principal de Atracciones.',
                    'Participa en el diseño de los recorridos de la feria.',
                ],
            ],
        ],
        'esteban-vargas' => [
            'name' => 'Esteban Vargas',
            'role' => 'sospechoso',
            'file' => 'suspects/esteban-vargas.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V07']],
                ['questions' => 3, 'after' => ['DE'], 'confrontation' => true],
            ]],
            'photo' => 'esteban-vargas.png',
            'connection' => 'Técnico de mantenimiento del laberinto; responsable técnico de sus paneles',
            'motive' => 'Ninguno respecto al incidente; pospuso el mantenimiento de algunos paneles',
            'alibi' => 'En el taller de mantenimiento, fuera del laberinto, durante toda la ventana del incidente',
            'public' => [
                'age' => '44 años',
                'profile' => 'Técnico de mantenimiento del laberinto. Práctico y de pocas palabras, muy ligado a los paneles que cuida.',
                'alibi' => 'Dice haber estado en el taller de mantenimiento, fuera del laberinto.',
                'facts' => [
                    'Es responsable técnico de los paneles de espejo del laberinto.',
                    'Trabaja con overol de mantenimiento.',
                ],
            ],
        ],
        'perla-issasi' => [
            'name' => 'Perla Issasi',
            'role' => 'sospechosa',
            'file' => 'suspects/perla-issasi.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DP']],
                ['questions' => 3, 'after' => ['V06', 'DP'], 'confrontation' => true],
            ]],
            'photo' => 'perla-issasi.png',
            'connection' => 'Guía del laberinto; esa noche hacía el relevo en la puerta de personal',
            'motive' => 'Ninguno respecto al incidente; ha dejado entrar a conocidos por la puerta de personal sin pagar',
            'alibi' => 'En la puerta de personal, sin entrar al laberinto',
            'public' => [
                'age' => '28 años',
                'profile' => 'Guía del laberinto, con el vestuario de temporada. Amigable y conversadora, algo nerviosa al hablar de su puesto.',
                'alibi' => 'Dice haber estado en la puerta de personal durante el relevo de cierre.',
                'facts' => [
                    'Esa noche trabajaba cerca de la entrada de personal.',
                    'Conoce a casi todo el equipo de la feria.',
                ],
            ],
        ],
        'ignacio-bermudez' => [
            'name' => 'Ignacio Bermúdez',
            'role' => 'testigo, persona agredida (no es sospechoso)',
            'file' => 'suspects/ignacio-bermudez.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
            ]],
            'photo' => 'ignacio-bermudez.png',
            'connection' => 'Operador del laberinto; hacía su recorrido de cierre cuando lo empujaron',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '34 años',
                'profile' => 'Operador del laberinto, con camisa roja y gorra. Sereno, con la muñeca vendada, quiere que todo se aclare.',
                'facts' => [
                    'Es la persona agredida: sufrió una lesión de muñeca, real pero no grave.',
                    'Es una de las tres personas que aspiran al ascenso a Encargado Principal de Atracciones.',
                ],
            ],
            'accusable' => false,
        ],
        'damaris-lobo' => [
            'name' => 'Dámaris Lobo',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/damaris-lobo.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'damaris-lobo.png',
            'connection' => 'Visitante que esperaba junto a la salida del Acceso A',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '30 años',
                'profile' => 'Visitante de la feria, con ropa casual. Cordial y observadora, algo impresionada por lo ocurrido.',
                'facts' => [
                    'Esa noche esperaba junto a la salida del Acceso A.',
                    'Desde allí tuvo vista hacia el interior del laberinto.',
                ],
            ],
            'accusable' => false,
        ],
        'ulises-franco' => [
            'name' => 'Ulises Franco',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/ulises-franco.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'ulises-franco.png',
            'connection' => 'Visitante que esperaba junto a la salida del Acceso B',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '42 años',
                'profile' => 'Visitante de la feria, con ropa casual. Tranquilo y seguro de lo que vio.',
                'facts' => [
                    'Esa noche esperaba junto a la salida del Acceso B.',
                    'Desde allí tuvo vista hacia el interior del laberinto.',
                ],
            ],
            'accusable' => false,
        ],
        'norah-esquivel' => [
            'name' => 'Norah Esquivel',
            'role' => 'fuente técnica (vigilante de la cabina de control)',
            'file' => 'suspects/norah-esquivel.md',
            'interrogation' => ['stages' => [
                ['questions' => 2],
                ['questions' => 1, 'after' => ['V07']],
                ['questions' => 1, 'after' => ['V04']],
                ['questions' => 1, 'after' => ['DN']],
            ]],
            'photo' => 'norah-esquivel.png',
            'connection' => 'Vigilante técnica de la cabina de control; opera las cámaras fijas',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '45 años',
                'profile' => 'Vigilante técnica de la cabina de control, con uniforme y credencial. Metódica y precisa, habla de lo que registran sus cámaras.',
                'facts' => [
                    'Opera las cámaras fijas del laberinto desde la cabina de Control C.',
                    'Conoce cómo está montado el laberinto desde la cabina.',
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
        'culprit_slug' => 'dalila-orozco',

        'headline' => 'No hubo tres personas en el laberinto, sino una: Dalila Orozco, vista tres veces por paneles distintos, que confrontó a Ignacio por el ascenso y lo empujó.',

        'motive' => 'La rivalidad laboral directa con Ignacio por el ascenso a Encargado Principal de Atracciones.',

        'method' => 'Dalila entró por la puerta de personal poco después del cierre, cruzó el Corredor Central hasta el Octágono, confrontó a Ignacio por el ascenso y lo empujó durante la discusión: Ignacio golpeó un panel y se lesionó la muñeca, una lesión real pero no grave. Los tres testigos vieron a la misma persona: la vista desde el Acceso A (panel P2, reflexión directa) es la posición real, y las vistas desde el Acceso B (P5) y Control C (P7), paneles de reflexión doble, muestran la misma figura duplicada.',

        'key_evidence' => [
            'La fotografía desde el Acceso A (V02): vista directa, con el chaleco técnico azul y la dirección real del movimiento.',
            'Las vistas del Acceso B y de Control C (V03, V04) frente a la ficha de superficies (V07): los paneles P5 y P7 son de reflexión doble.',
            'El cartel del corredor y la declaración de Norah (V05, DN): el corredor era solo para personal y el chaleco técnico azul es el de la supervisión técnica.',
            'El cuaderno de la puerta de personal y la declaración de Perla (V06, DP): Dalila entró poco después del cierre.',
            'El testimonio de Ignacio y el mensaje de esa tarde (C1): Dalila pensaba hablar con él esa noche.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including the witnesses and the technical source.
         */
        'exonerations' => [
            'rogelio-fuenmayor' => 'Sí inflaba las cifras de asistencia del laberinto para justificar presupuesto adicional, una falta administrativa real pero ajena a la agresión. Estuvo toda la ventana en la oficina de administración, con su sesión abierta en el equipo y dos documentos impresos entre las 21:40 y las 22:35.',

            'carolina-sotillo' => 'Sí copió el diseño de un recorrido de una feria competidora, una falta real pero ajena a la agresión. Estuvo en una reunión de planificación en el pabellón de diseño, con dos coordinadores y hoja de asistencia de 21:45 a 22:45, y su chaleco de supervisión de área es naranja, no el azul de la figura.',

            'esteban-vargas' => 'Sí pospuso el mantenimiento de algunos paneles, una falta técnica real pero ajena a la agresión (solo limpieza y fijación del marco, nunca la reflexión). Estuvo en el taller, fuera del laberinto, con la hoja de herramientas firmada a las 21:35 y a las 22:40, y su overol no es el chaleco técnico azul.',

            'perla-issasi' => 'Sí dejaba entrar a conocidos suyos por la puerta de personal sin pagar, un abuso de confianza menor y distinto. Estuvo en la puerta toda la noche, sin entrar al laberinto, y su declaración ayudó a ubicar a Dalila.',

            'ignacio-bermudez' => 'Es la persona agredida y un testigo, no un sospechoso: solo vio un chaleco técnico azul antes de que lo empujaran y no pudo reconocer a nadie. Hacía su recorrido de cierre y no tenía motivo ni secreto relevante.',

            'damaris-lobo' => 'Es una testigo, no una sospechosa: desde el Acceso A vio con claridad, a través de un panel de reflexión directa, una figura con chaleco azul caminando hacia el Octágono. No tenía relación con nadie del personal ni motivo alguno.',

            'ulises-franco' => 'Es un testigo, no un sospechoso: desde el Acceso B vio la misma figura a través de un panel de reflexión doble, en otro lugar y otra dirección aparente. Describió con honestidad lo que vio; no tenía relación con nadie del personal ni motivo alguno.',

            'norah-esquivel' => 'Es una fuente técnica, no una sospechosa: opera las cámaras de la cabina y solo confirmó el fotograma, el montaje de los paneles y la ropa de trabajo del personal. No vio la agresión ni tenía motivo alguno.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Kore',

        // A1 of the design. Written to be played as-is: the model only reads
        // it aloud, it never composes it.
        'confession_script' => <<<'TXT'
            Fui a hablar con él, nada más. Pero la discusión se salió de control, y
            lo empujé sin pensarlo. No quise lastimarlo, pero fue mi mano la que lo
            empujó, y no la de nadie más.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió realmente?',
            'hint' => 'Cuenta cómo llegó la persona hasta Ignacio, qué pasó entre ellos y cómo terminó.',
            'points' => 20,
            'guidance' => 'Lo esencial: entró por la puerta de personal, confrontó a Ignacio por el ascenso dentro del laberinto y lo empujó durante la discusión, causándole una lesión no grave. No exigir aquí lo de los reflejos: se pregunta aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué disputaba con Ignacio la persona responsable.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'una_persona_reflejada',
                'label' => '¿Cómo se explica que los tres testigos vieran a la misma persona?',
                'hint' => 'Explica qué vista es directa y confiable, cuáles son reflejos duplicados y qué pruebas lo demuestran.',
                'answer' => 'La vista desde el Acceso A pasa por un panel de reflexión directa (P2) y muestra la posición real de la persona. Las vistas desde el Acceso B y Control C pasan por paneles de reflexión doble (P5 y P7), identificados en la ficha de superficies, que duplican la imagen del corredor: no son tres personas distintas, sino una sola persona real vista tres veces.',
                'points' => 30,
                'guidance' => 'Cero si afirma o da a entender que hubo varias personas distintas en el laberinto, o si no usa la regla de reflejos/ficha de superficies. Puntos completos con: una sola persona; la vista del Acceso A es directa; las vistas del Acceso B y de Control C son reflejos dobles según la ficha. Parcial si acierta que es la misma persona pero no explica por qué vista.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | T+N of the design -> trigger_offset_minutes (T+0 -> 1). The extra T+56
    | expedientes and T+60 coartadas carry the secrets' evidence and the doc's
    | crossed-alibi fail-safe.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El Espejo de las Tres Caras — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 10,
            'title' => 'Expansión — las personas del expediente',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 10,
            'title' => 'Pista privada — un recorte de la vista desde el Acceso A',
            'source_file' => 'pista-privada-vista-acceso-a.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: this is a crop of V02. The
            // complete V02 (code V02) reaches the whole table at T+20.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 20,
            'title' => 'Desarrollo — tres puntos de observación',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V02', 'V03', 'V07'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 32,
            'title' => 'Complicación — el fotograma de la cabina',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V04'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 45,
            'title' => 'Giro — el cartel y la vigilante',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DN = Norah's written (expanded) declaration, the body of giro.md.
            'evidence_codes' => ['V05', 'DN'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Cierre — la puerta de personal, un mensaje y una declaración',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // DP = Perla's written (expanded) declaration, in cierre.md.
            'evidence_codes' => ['V06', 'DP', 'C1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 56,
            'title' => 'Expedientes — tres cotejos pendientes',
            'source_file' => 'expedientes.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['DR', 'DC', 'DE'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 60,
            'title' => 'Cierre final — coartadas cruzadas',
            'source_file' => 'coartadas.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 65,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario pregunta cómo se explica lo que vieron los tres testigos.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-espejo-de-las-tres-caras/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-planta-laberinto.png', 'caption' => 'Plano del Laberinto de Espejos, con sus accesos, su cabina y la numeración de sus paneles'],
        ],
        'pista-privada-vista-acceso-a.md' => [
            ['file' => 'v02-recorte-vista-acceso-a.png', 'caption' => 'Recorte de una fotografía tomada desde el Acceso A'],
        ],
        'desarrollo.md' => [
            ['file' => 'v02-vista-acceso-a.png', 'caption' => 'Vista desde el Acceso A, hacia el Corredor Central'],
            ['file' => 'v03-vista-acceso-b.png', 'caption' => 'Vista desde el Acceso B'],
            ['file' => 'v07-guia-superficies.png', 'caption' => 'Ficha de superficies del taller de mantenimiento'],
        ],
        'complicacion.md' => [
            ['file' => 'v04-fotograma-control-c.png', 'caption' => 'Fotograma de la cámara fija de Control C'],
        ],
        'giro.md' => [
            ['file' => 'v05-senaletica-corredor.png', 'caption' => 'Cartel en la entrada del Corredor Central'],
        ],
        'cierre.md' => [
            ['file' => 'v06-entrada-personal.png', 'caption' => 'La puerta de personal y su cuaderno de registro'],
            ['file' => 'c1-captura-mensaje.png', 'caption' => 'Captura de un mensaje enviado esa tarde'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v02-vista-acceso-a.png', 'caption' => 'Vista directa: la figura está donde parece estar'],
            ['file' => 'v03-vista-acceso-b.png', 'caption' => 'Panel de reflexión doble: la misma figura, duplicada'],
            ['file' => 'v04-fotograma-control-c.png', 'caption' => 'Otro panel de reflexión doble, a las 22:14'],
            ['file' => 'v07-guia-superficies.png', 'caption' => 'La ficha que distingue paneles directos y dobles'],
            ['file' => 'v06-entrada-personal.png', 'caption' => 'La entrada de personal, poco después del cierre'],
        ],
    ],
];
