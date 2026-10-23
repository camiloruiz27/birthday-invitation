<?php

/**
 * Case manifest: "El reparto invisible"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: a death and a fraud that share one discovery but not one
 * author. Walter Benítez (driver) caused Celina Rengifo's death in a brief,
 * unplanned scuffle on the loading dock; Rodrigo Palau (operations director),
 * with Débora Salinas as nominal director of Centro Ribera, designed the
 * diversion of donated goods to an outside buyer (Distribuidora Hinares). The
 * central trap is a false equivalence of three facts that are never the same:
 *   (1) what LEFT the warehouse (V02, the load order),
 *   (2) what the PAPER says arrived (V03, Ribera's signed delivery note),
 *   (3) what REALLY arrived (V04 photo, V09 inventory, V07 a genuine centre).
 * A second false equivalence: no dispatch record proves who was driving. Only
 * Osvaldo's testimony (DO) closes that. The AI rule that matters: never say
 * "Walter drove" from a dispatch record, never say "Rodrigo killed" because he
 * designed the fraud.
 *
 * Reference timetable (single source of truth for every file of this case; all
 * hours that appear in evidence, md files and the manifest derive from it).
 * The night of the death is a Friday (la víspera); La Noche de Reparto, the
 * all-routes operation, leaves at 05:00 on Saturday. The base is ~40 minutes
 * across town from the sede central. No calendar dates are used anywhere.
 *   Chofer shift: 12:00–20:00. Mon–Thu routes leave the base at 14:00, bodega
 *   releases at 12:30. Friday plan: bodega releases Sat 03:30, load at 04:00,
 *   routes leave Sat 05:00.
 *   Fri 11:52–12:03  choferes badge in at the base (V11).
 *   Fri 18:52        Osvaldo starts his night shift (V11, DO).
 *   Fri 19:00–23:30  Evento previo en la sede central (Carolina; V06 footer).
 *                    Rodrigo and Herminia there all night; the brindis of
 *                    Rodrigo falls in the final part. Agustina in the oficina de
 *                    comunicación with Carolina. Débora: oficina sede central,
 *                    building access 17:40 in / 00:15 out. Felisa: oficina sede
 *                    central, access 08:05 in / 23:40 out. Benjamín: bodega,
 *                    console validations 21:30 / 23:10 / 00:20 (V02 footer).
 *   Fri 19:58, 20:03, 20:07  Iván, Teodoro, Mauricio badge out (V11). Walter
 *                    does NOT (his lie: "me fui directo a casa a las ocho").
 *   Fri 21:30        Bodega releases C-3 (route R4, Ribera): 9 pallets (V02).
 *                    The plan (V06) says 6 pallets, load Sat 04:00, leave 05:00.
 *   Fri 21:34        Celina records her voice note (A1) and heads to the base.
 *   Fri 22:00        Load time written on the night manifest; Walter loads.
 *                    Departure written on the manifest: 23:30 (never happened).
 *   Fri 22:05        Celina badges in at the base main gate (V11) and asks
 *                    Osvaldo whether Walter is still at the muelle (DO).
 *   Fri ~22:20–22:35 Osvaldo sees them talking, arms raised, by the truck; the
 *                    fall happens behind the truck, out of his sight.
 *   Fri 22:47        Walter badges out by the muelle's pedestrian gate (V11).
 *                    The vehicle gate is never opened: the C-3 stays.
 *   Sat 04:10        Osvaldo finds Celina at the foot of the platform (gancho).
 *                    Security photo of the muelle shortly after (V08).
 * Earlier facts: Rodrigo approved Centro Ribera's alta a year ago (IA); the
 * Hinares payments are 14 over the last 7 months (US$ 2,450–3,800 each, total
 * US$ 42,100) to an account ending 4471 held by R. Palau, each within 48 h after an
 * R4 departure (IA). Celina visited Ribera unannounced weeks before (V04) and
 * asked Hernán twice that week for the R4 manifests.
 * Numbers of the campaign (6 weeks): Norte received 300 canastas for 298 family
 * visits (V07, V09); Sur 280 / 276; Ribera 520 for 142 reported visits (V09).
 * V09 also has a small gap between books and physical count in bodega (Benjamín).
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 5-field form (30/20/25/25)
 *    is translated: culprit_slug = walter (30), "weapon" = what happened on the
 *    dock (10), "motive" = why (10), and two extra questions carry the diversion
 *    (who designed it, with whom: 25; its method: 25). The "same author without
 *    evidence" penalty is the guidance of the two diversion questions.
 *  - The map has EXACTLY five nodes and four routes, and does not show the
 *    Hinares leg (it would be a sixth node and would give the twist away). The
 *    doc's JSON lists bodega→Norte / Sur / Ribera plus Ribera→Hinares; here the
 *    four routes are R1 Bodega Central→Base de Transportistas, R2 Base→Centro
 *    Norte, R3 Base→Centro Sur, R4 Base→Centro Ribera, because the doc itself
 *    loads the truck at the base's muelle. Hinares is never a node.
 *  - Private clues (T+15), all random_player and each a complete piece (PT1–PT4
 *    with their own code, V02a without one): the plan de turnos in four day-fragments (PT1 Mon–Tue, PT2 Wed,
 *    PT3 Thu, PT4 Fri) and a partial load order (V02a, Mon–Thu, no Friday row; it has NO evidence code, see its event).
 *    Walter's Ribera shifts sit in PT1, PT2 and PT4 and the only Ribera shift of
 *    another driver (Iván) in PT3, so the pattern only shows by comparing. The
 *    complete public copies are V06 (T+30, the whole plan) and V02 (T+45, the
 *    whole load order). Nothing critical depends on a single private fragment.
 *    The doc's fail-safe summary of the plan (without naming the driver) is in
 *    complicacion.md at T+60.
 *  - V02: the doc leaves unspecified which document shows "the manifiesto de esa
 *    noche". It is the Friday row of V02 (9 pallets, 21:30, load 22:00, departure
 *    23:30), contrasted with the plan row of V06 (6 pallets, Sat 04:00/05:00).
 *  - Written statements with their own codes (no image, like DF in
 *    punto-de-control): DR = Rogelio's declaration (T+45), IA = auditora's
 *    report (T+75, text of giro.md), DO = Osvaldo's declaration (T+75). The doc's
 *    "testimonio ampliado de Osvaldo" is DO; before it Osvaldo only says he will
 *    consult his libro de novedades, so a Walter confrontation cannot be won at
 *    minute 16.
 *  - The doc's N6 says Osvaldo "la vio confrontar a Walter" while 3.8 says only
 *    that he saw Walter loading and leaving. Resolved in between: he saw them
 *    talking heatedly by the truck from his caseta, never the fall (the platform
 *    is hidden behind the truck). He assumed Celina left on her own because the
 *    vehicle gate was never opened.
 *  - Confrontations: the doc unlocks them all at T+60 but several need evidence
 *    that arrives later. Gates by code instead: Walter c1 after DO, c2 after V11
 *    and V02 (both T+75); Rodrigo c1 after V04+DR (T+60), c2 after IA+V05 (T+75);
 *    Herminia and Felisa after IA (T+75); Débora and Benjamín after V09 (T+90);
 *    Agustina after V06+V08 (V08 only marks the T+60 opening). The doc's "1
 *    confrontation" per person is the engine's 3 questions (Walter 1+2, Rodrigo
 *    1+2).
 *  - Fuentes delimitadas (Hernán, Marcela) are roster entries with
 *    'accusable' => false; Hernán's portrait is his dispatch console (an
 *    object), Marcela's a face. Neither opines about intent.
 *  - The doc's V11 note "nadie más coincide con Walter" is honoured: the list has
 *    only Celina (in, never out) and Walter (out 22:47) between 22:05 and 22:47.
 *    The other drivers' exits and the other staff are in other systems or places.
 *  - Walter does not name the mastermind: he got instructions and pay through
 *    intermediaries. Rodrigo's c2 and Débora's c1 are what close N3/N4.
 *  - The epilogues of the doc live in solucion.md (the engine's personalised
 *    epilogue works from `solution` + `exonerations`). A2 (Walter's confession)
 *    is `confession_script`, generated per game, never in the timeline; A1
 *    (Celina's voice note, found afterwards) is a timeline audio event.
 *  - The doc marks La Noche de Reparto as the operation "a la mañana siguiente";
 *    it leaves at 05:00 so the title still reads. No calendar dates anywhere.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'El reparto invisible',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Cadena de Invierno, víspera de La Noche de Reparto',
    'authority' => 'Comité de Revisión de Cadena de Invierno',

    'victim' => [
        'name' => 'Celina Rengifo',
        'label' => 'Víctima',
        'photo' => 'celina-rengifo.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The doc asks for 4 preguntas por sospechoso, 3 por testigo and 1-2
        // confrontaciones. The engine folds that into 3–5 base questions plus 3
        // for those with confrontations, opened by `interrogation.stages`.
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
        'tagline' => 'Cinco nodos, cuatro rutas. Uno de los destinos nunca estuvo realmente ahí.',

        'description' => <<<'TXT'
            Cadena de Invierno es una red solidaria que reparte donaciones
            antes de Nochebuena. La víspera de su gran operativo, la
            responsable de verificar que las entregas llegan de verdad
            aparece muerta en el muelle de carga de la base de transportistas.
            La red tiene cinco nodos y cuatro rutas; uno de los centros recibe
            camiones llenos cada semana, sobre el papel.

            Tu equipo (4 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real, se reparte piezas privadas que solo cobran sentido si
            se comparan, y podrá interrogar a siete sospechosos, tres testigos
            y dos fuentes de alcance limitado, durante 110 minutos, cruzando
            órdenes de carga, actas, registros de acceso y testimonios.

            Lo que salió de bodega, lo que dice el papel y lo que llegó de
            verdad no son lo mismo. Ningún registro basta por sí solo: hay
            que cruzarlos todos.
            TXT,

        // Archivo esperado en public/immersion/el-reparto-invisible/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-reparto-invisible).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Cinco nodos, cuatro rutas. Uno de los destinos nunca estuvo realmente ahí.',
            'bullets' => [
                '4 a 8 investigadores con piezas privadas que solo encajan si las comparan',
                'Siete sospechosos, tres testigos y dos fuentes limitadas a quienes interrogar',
                '110 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 110,
        'min_players' => 4,
        'max_players' => 8,

        'price_amount' => 79900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/el-reparto-invisible/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 29,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The three witnesses and the two limited sources are modeled exactly like a
    | suspect entry. Celina, the dead woman, is the "victim" and is not
    | interrogable.
    |
    */

    'suspects' => [
        'walter-benitez' => [
            'name' => 'Walter Benítez',
            'role' => 'sospechoso',
            'file' => 'suspects/walter-benitez.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['DO'], 'confrontation' => true],
                ['questions' => 2, 'after' => ['V11', 'V02'], 'confrontation' => true],
            ]],
            'photo' => 'walter-benitez.png',
            'connection' => 'Chofer de la red; colega operativo de Celina',
            'motive' => 'Miedo a ser la única persona con evidencia física en su contra si Celina lo reportaba esa misma noche',
            'alibi' => 'Dice que se fue directo a casa al terminar su turno',
            'public' => [
                'age' => '39 años',
                'profile' => 'Chofer y transportista de Cadena de Invierno. Cercano y bromista con el equipo.',
                'alibi' => 'Dice que se fue directo a casa al terminar su turno.',
                'facts' => [
                    'Conduce varias de las rutas de reparto de la red.',
                    'Celina dependía de los manifiestos que él transportaba para verificar las rutas.',
                ],
            ],
        ],
        'rodrigo-palau' => [
            'name' => 'Rodrigo Palau',
            'role' => 'sospechoso',
            'file' => 'suspects/rodrigo-palau.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['V04', 'DR'], 'confrontation' => true],
                ['questions' => 2, 'after' => ['IA', 'V05'], 'confrontation' => true],
            ]],
            'photo' => 'rodrigo-palau.png',
            'connection' => 'Director de operaciones; Celina le reportaba sus hallazgos',
            'motive' => 'Ninguno respecto a la muerte; su interés requería que Celina siguiera viva y callada',
            'alibi' => 'En el acto previo a La Noche de Reparto, en la sede central, confirmado por Carolina',
            'public' => [
                'age' => '47 años',
                'profile' => 'Director de operaciones de Cadena de Invierno. Seguro y persuasivo; aspecto ejecutivo informal.',
                'alibi' => 'Dice haber estado en el acto previo a La Noche de Reparto, en la sede central.',
                'facts' => [
                    'Celina le reportaba sus hallazgos antes de llevarlos a la junta.',
                    'Supervisa la incorporación de centros y las rutas de la red.',
                ],
            ],
        ],
        'debora-salinas' => [
            'name' => 'Débora Salinas',
            'role' => 'sospechosa',
            'file' => 'suspects/debora-salinas.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V03', 'V09'], 'confrontation' => true],
            ]],
            'photo' => 'debora-salinas.png',
            'connection' => 'Coordinadora de alianzas comunitarias; dirige Centro Ribera',
            'motive' => 'Ninguno respecto a la muerte; su interés era sostener la fachada de Ribera',
            'alibi' => 'En su oficina de la sede central; el registro de acceso del edificio lo confirma',
            'public' => [
                'age' => '44 años',
                'profile' => 'Coordinadora de alianzas comunitarias y directora de Centro Ribera. Cálida en público.',
                'alibi' => 'Dice haber estado en su oficina preparando los reportes de fin de año.',
                'facts' => [
                    'Celina la visitó en Centro Ribera, sin avisar, semanas antes.',
                    'Firma las actas de entrega de su centro.',
                ],
            ],
        ],
        'herminia-castro' => [
            'name' => 'Herminia Castro',
            'role' => 'sospechosa',
            'file' => 'suspects/herminia-castro.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['IA'], 'confrontation' => true],
            ]],
            'photo' => 'herminia-castro.png',
            'connection' => 'Presidenta de Cadena de Invierno; superiora institucional de Celina',
            'motive' => 'Ninguno respecto a la muerte; calló una duda sobre el crecimiento de Ribera',
            'alibi' => 'En el acto previo a La Noche de Reparto, confirmada por Carolina',
            'public' => [
                'age' => '58 años',
                'profile' => 'Presidenta de Cadena de Invierno. Institucional y elegante; defiende a su equipo.',
                'alibi' => 'Dice haber estado preparando el acto previo y luego en él, en la sede central.',
                'facts' => [
                    'Es la máxima responsable institucional de la red.',
                    'Celina llevaba sus informes a la junta que ella preside.',
                ],
            ],
        ],
        'agustina-prado' => [
            'name' => 'Agustina Prado',
            'role' => 'sospechosa',
            'file' => 'suspects/agustina-prado.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V06', 'V08'], 'confrontation' => true],
            ]],
            'photo' => 'agustina-prado.png',
            'connection' => 'Colega de Celina en comunicación; pareja de Walter Benítez',
            'motive' => 'Ninguno; sospechaba vagamente de Walter y calló por lealtad',
            'alibi' => 'En la oficina de comunicación de la sede central, confirmada por Carolina',
            'public' => [
                'age' => '30 años',
                'profile' => 'Responsable de comunicación y voluntariado. Entusiasta con la campaña.',
                'alibi' => 'Dice haber estado en la oficina de comunicación preparando materiales para el acto.',
                'facts' => [
                    'Coordinaba con Celina el contenido público de la campaña.',
                    'Su pareja es Walter Benítez, chofer de la red.',
                ],
            ],
        ],
        'benjamin-ortiz' => [
            'name' => 'Benjamín Ortiz',
            'role' => 'sospechoso',
            'file' => 'suspects/benjamin-ortiz.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V09'], 'confrontation' => true],
            ]],
            'photo' => 'benjamin-ortiz.png',
            'connection' => 'Encargado de la bodega central; Celina verificaba con él los volúmenes',
            'motive' => 'Ninguno respecto a la muerte; se lleva pequeñas cantidades de mercancía para su familia',
            'alibi' => 'En la bodega central toda la noche; el registro de despacho lo confirma',
            'public' => [
                'age' => '42 años',
                'profile' => 'Encargado de la bodega central. Práctico, algo hosco.',
                'alibi' => 'Dice haber estado en la bodega central toda la noche.',
                'facts' => [
                    'Controla lo que sale de bodega hacia cada ruta.',
                    'Celina contrastaba con él los volúmenes despachados.',
                ],
            ],
        ],
        'felisa-rondon' => [
            'name' => 'Felisa Rondón',
            'role' => 'sospechosa',
            'file' => 'suspects/felisa-rondon.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['IA'], 'confrontation' => true],
            ]],
            'photo' => 'felisa-rondon.png',
            'connection' => 'Contable de la red; cruzaba cifras con los hallazgos de Celina',
            'motive' => 'Ninguno respecto a la muerte; calló discrepancias por miedo a perder su empleo',
            'alibi' => 'En su oficina de la sede central; el registro de acceso del edificio lo confirma',
            'public' => [
                'age' => '53 años',
                'profile' => 'Contable de la red. Meticulosa y orgullosa de sus libros.',
                'alibi' => 'Dice haber estado en su oficina cerrando los libros de fin de año.',
                'facts' => [
                    'Maneja los números financieros de todos los centros.',
                    'Su jefe directo es Rodrigo Palau.',
                ],
            ],
        ],
        'osvaldo-mejia' => [
            'name' => 'Osvaldo Mejía',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/osvaldo-mejia.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DO']],
            ]],
            'photo' => 'osvaldo-mejia.png',
            'connection' => 'Vigilante nocturno de la base de transportistas; encontró el cuerpo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '60 años',
                'profile' => 'Vigilante nocturno de la base de transportistas. Sereno y formal.',
                'facts' => [
                    'Desde su caseta se ve el muelle de carga.',
                    'Encontró a Celina en su ronda de madrugada.',
                ],
            ],
            'accusable' => false,
        ],
        'carolina-ibanez' => [
            'name' => 'Carolina Ibáñez',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/carolina-ibanez.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'carolina-ibanez.png',
            'connection' => 'Encargada de eventos de La Noche de Reparto; coordinó el acto previo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '35 años',
                'profile' => 'Encargada de eventos de La Noche de Reparto. Ágil y algo agobiada por la presión del operativo.',
                'facts' => [
                    'Coordinó el acto previo en la sede central.',
                    'Lleva el cronograma del evento en una tablet.',
                ],
            ],
            'accusable' => false,
        ],
        'rogelio-fuentes' => [
            'name' => 'Rogelio Fuentes',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/rogelio-fuentes.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V07']],
            ]],
            'photo' => 'rogelio-fuentes.png',
            'connection' => 'Coordinador del Centro Comunitario Norte',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '34 años',
                'profile' => 'Coordinador del Centro Comunitario Norte. Cercano y práctico.',
                'facts' => [
                    'Su centro recibe mercancía de la red y reparte a familias.',
                    'Lleva el registro de recepción de su centro.',
                ],
            ],
            'accusable' => false,
        ],
        'hernan-casares' => [
            'name' => 'Hernán Casares',
            'role' => 'fuente de alcance limitado (operador de despacho)',
            'file' => 'suspects/hernan-casares.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V02', 'V06']],
            ]],
            // Not a face: the dispatch console is his "portrait" (CaseAssetsTest
            // expects one image per roster entry).
            'photo' => 'hernan-casares.png',
            'connection' => 'Gestiona el sistema de asignación de rutas y manifiestos de carga',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '50 años',
                'profile' => 'Operador de despacho. Fuente técnica: responde con datos de su registro, no opina ni interpreta.',
                'facts' => [
                    'Registra qué camión sale de bodega, con qué mercancía, hacia qué ruta y a qué hora.',
                    'No puede confirmar por sí solo quién conducía cada camión.',
                ],
            ],
            'accusable' => false,
        ],
        'marcela-hinojosa' => [
            'name' => 'Marcela Hinojosa',
            'role' => 'fuente de alcance limitado (auditora externa)',
            'file' => 'suspects/marcela-hinojosa.md',
            'interrogation' => ['stages' => [
                ['questions' => 1],
                ['questions' => 3, 'after' => ['IA']],
            ]],
            'photo' => 'marcela-hinojosa.png',
            'connection' => 'Auditora externa contratada por la junta',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '45 años',
                'profile' => 'Auditora externa. Escueta y exacta: confirma montos, fechas y cuentas, no intenciones.',
                'facts' => [
                    'Contratada por la junta tras detectarse irregularidades preliminares en las cifras de un centro.',
                    'Su alcance se limita a confirmar cifras.',
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
        'culprit_slug' => 'walter-benitez',

        'headline' => 'Walter Benítez causó la muerte de Celina en un forcejeo no premeditado; Rodrigo Palau, con Débora Salinas, diseñó el desvío de donaciones que ella había ido a comprobar.',

        'motive' => 'Miedo a ser la única persona con evidencia física e innegable en su contra —él, al volante, con la mercancía desviada— cuando Celina le dijo que lo reportaría esa misma noche; no fue avaricia ni diseñó el esquema, a diferencia de los administradores, que podían alegar que no sabían a dónde iba esa carga.',

        'method' => 'Celina lo encontró cargando el camión C-3 de la ruta de Ribera, fuera de hora y con más pallets de los previstos, y lo confrontó. En un forcejeo breve junto a la plataforma del muelle, ella cayó y se golpeó la cabeza contra el borde de una tarima. Walter no pidió ayuda: dejó el camión sin cerrar y salió por la puerta peatonal.',

        'key_evidence' => [
            'El registro de acceso de la base (V11): la tarjeta de Celina entra a las 22:05 y no sale; la de Walter sale a las 22:47, no a las 20:00 como él dice.',
            'La declaración de Osvaldo: Walter cargando el C-3 fuera de hora, la discusión junto al camión, su salida apresurada y el portón de vehículos que no se abrió.',
            'La orden de carga (V02) frente al plan de entregas (V06): nueve pallets a las 21:30, no seis con salida a las 05:00; ningún registro de despacho dice quién conducía.',
            'La nota de voz de Celina (A1): iba esa noche a la base a hablar con quien condujera la ruta de Ribera.',
            'El informe de la auditoría y el contrato (V05): el alta de Ribera sin verificación y catorce pagos de Distribuidora Hinares a una cuenta de Rodrigo Palau.',
            'El acta firmada por Débora (V03) frente a la oficina casi vacía (V04) y el inventario (V09).',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including the witnesses and the limited sources.
         */
        'exonerations' => [
            'rodrigo-palau' => 'Sí diseñó el desvío de donaciones hacia Distribuidora Hinares y se quedó con una parte, pero no tuvo nada que ver con la muerte de Celina: estuvo toda la noche en el acto previo en la sede central, que Carolina confirma, y su interés exigía que Celina siguiera viva y callada, no una muerte que trajera una auditoría externa.',

            'debora-salinas' => 'Sí sostuvo la fachada de Centro Ribera firmando actas de entrega sin verificar la mercancía y recibió parte de la diferencia, pero su papel es administrativo, no logístico, y estuvo esa noche en su oficina de la sede central: el registro de acceso marca su entrada a las 17:40 y su salida a las 00:15.',

            'herminia-castro' => 'Sí tuvo dudas desde hacía meses sobre el crecimiento de Centro Ribera y prefirió no cuestionarlo en público antes de la temporada alta, un silencio real pero ajeno a la muerte. Estuvo en el acto previo en la sede central, que Carolina confirma.',

            'agustina-prado' => 'Sí notaba tenso y evasivo a Walter y calló por lealtad, pero era una sospecha vaga, sin ningún conocimiento directo del crimen ni del desvío. Estuvo en la oficina de comunicación de la sede central, con Carolina.',

            'benjamin-ortiz' => 'Sí se llevaba pequeñas cantidades de mercancía donada para su familia, un hurto de otra escala y otra motivación que el esquema de Rodrigo. Estuvo toda la noche en la bodega central: el registro de despacho muestra validaciones suyas a las 21:30, a las 23:10 y a las 00:20.',

            'felisa-rondon' => 'Sí notó hace meses que las cifras de Centro Ribera no cuadraban y calló por miedo a perder su empleo, un silencio y no una acción. Estuvo en su oficina de la sede central cerrando los libros: el registro de acceso marca su entrada a las 08:05 y su salida a las 23:40.',

            'osvaldo-mejia' => 'Es un testigo, no un sospechoso: vio a Walter cargar el camión, discutir con Celina y salir deprisa, y encontró el cuerpo en su ronda. No vio la caída ni tenía motivo alguno.',

            'carolina-ibanez' => 'Es una testigo, no una sospechosa: coordinaba el acto previo en la sede central, desde donde confirma a Rodrigo, Herminia y Agustina. No estuvo en la base ni tenía motivo alguno.',

            'rogelio-fuentes' => 'Es un testigo, no un sospechoso: solo describió cómo funciona un centro que sí opera y lo que vio en Ribera las dos veces que fue. No estuvo en la base ni tenía motivo alguno.',

            'hernan-casares' => 'Es una fuente técnica de alcance limitado, no un sospechoso: su sistema registra qué camión salió de bodega y a qué hora, nunca quién conducía, y no opina sobre intenciones. Trabaja desde la oficina de despacho de la sede central.',

            'marcela-hinojosa' => 'Es una auditora externa, no una sospechosa: confirma montos, fechas y cuentas, no intenciones, y no estuvo en la base ni tenía motivo alguno.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it. (This is the doc's audio A2.)
        'confession_script' => <<<'TXT'
            No fui a buscarla para hacerle daño. Me dijo que iba a reportarlo
            esa misma noche, y pensé en lo que significaba para mí: yo era el
            que estaba ahí, con el camión, con la carga. Los demás podían decir
            que no sabían nada. Yo no.

            Intenté explicarle, ella no se movió, hubo un forcejeo, muy breve,
            y cayó de la plataforma. No supe qué hacer. Me fui y dejé el
            camión así. No fue lo que planeé, pero fue lo que hice, y tengo que
            responder por eso.
            TXT,
    ],

    /*
     * Accusation form. The doc scores: murder responsible 30; what happened and
     * motive 20; responsible for the diversion 25; method of the diversion 25.
     * Translated: culprit 30, weapon (what happened) 10, motive 10, and two extra
     * questions for the diversion (25 + 25) = 100.
     */
    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió en el muelle de carga?',
            'hint' => 'Cuenta cómo se encontraron Celina y el responsable, qué pasó entre ellos y cómo murió ella.',
            'points' => 10,
            'guidance' => 'Lo esencial: Celina lo confrontó por la ruta que conducía y dijo que lo reportaría esa noche; hubo un forcejeo breve y no premeditado y ella cayó desde la plataforma y se golpeó la cabeza. No exigir aquí el desvío: se pregunta aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué perdía el responsable si Celina hablaba esa noche, y por qué eso no tiene que ver con quien diseñó el desvío.',
            'points' => 10,
            'guidance' => 'Lo esencial: miedo a ser el único con evidencia física en su contra (él, al volante, con la carga), no avaricia ni diseño del esquema. Cero si lo presenta como cómplice que mató para proteger el esquema de Rodrigo.',
        ],
        'questions' => [
            [
                'key' => 'responsable_desvio',
                'label' => '¿Quién diseñó el desvío de mercancía y con quién?',
                'hint' => 'Nombra al responsable del desvío y a quien lo ayudó a sostenerlo por escrito.',
                'answer' => 'Rodrigo Palau, director de operaciones, que incorporó a Centro Ribera sin verificación y recibe los pagos de Distribuidora Hinares; con la complicidad administrativa de Débora Salinas, directora nominal de Ribera, que firmaba las actas de entrega.',
                'points' => 25,
                'guidance' => 'Cero si atribuye el diseño del desvío a Walter, o si trata el desvío y la muerte como obra de la misma persona sin evidencia que lo sostenga. Puntos completos con Rodrigo como responsable y Débora como cómplice administrativa; la mitad si solo nombra a Rodrigo.',
            ],
            [
                'key' => 'metodo_desvio',
                'label' => '¿Cómo funcionaba el desvío?',
                'hint' => 'Qué papel cumplía Centro Ribera, qué decían los documentos y a dónde iba realmente la mercancía.',
                'answer' => 'Centro Ribera se incorporó como socio beneficiario sin la verificación habitual, y se sostuvo con actas de entrega firmadas «en su totalidad» aunque el centro apenas operaba; la mercancía declarada entregada seguía hacia un comprador externo, Distribuidora Hinares, que pagaba a una cuenta de Rodrigo, y la diferencia se repartía.',
                'points' => 25,
                'guidance' => 'Basta con: centro administrativo usado como destino en el papel, actas firmadas, mercancía desviada hacia un comprador externo. No exigir el nombre de Hinares ni las cifras. Cero si afirma que un registro de despacho prueba quién conducía.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/15/30/45/60/75/90/110 delivery plan
    | into trigger_offset_minutes (T+0 -> 1, same convention as the other cases).
    | Evidence codes: V01-V11 and A1 as in the doc; PT1-PT4 are the plan de
    | turnos fragments; the partial load order (V02a) has no code on purpose, it
    | would be confused with V02; DR, IA and DO are written statements without
    | an image.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El reparto invisible — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 15,
            'title' => 'Apertura — las personas de la red y el reparto de turnos',
            'source_file' => 'apertura.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 15,
            'title' => 'Pista privada — plan de turnos, lunes y martes',
            'source_file' => 'pista-privada-turnos-1.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['PT1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 15,
            'title' => 'Pista privada — plan de turnos, miércoles',
            'source_file' => 'pista-privada-turnos-2.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['PT2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 15,
            'title' => 'Pista privada — plan de turnos, jueves',
            'source_file' => 'pista-privada-turnos-3.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['PT3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 15,
            'title' => 'Pista privada — plan de turnos, viernes',
            'source_file' => 'pista-privada-turnos-4.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['PT4'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 15,
            'title' => 'Pista privada — orden de carga de bodega (vista parcial)',
            'source_file' => 'pista-privada-orden-carga.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: "V02a" would sit in the delivered list
            // from T+15 and the AI could read it as V02 (the complete order, T+45)
            // and give the Friday-night data of Hernán/Benjamín too early. V02a is
            // only the label of the image file; no stage or persona needs it.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 30,
            'title' => 'Expansión — el plan de entregas y el tablero de la oficina',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V06', 'V10'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 45,
            'title' => 'Desarrollo — lo que salió de bodega y lo que dice el papel',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            // DR = Rogelio's written declaration (body of desarrollo.md).
            'evidence_codes' => ['V02', 'V03', 'DR'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 60,
            'title' => 'Complicación — dos lugares, dos momentos',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V04', 'V08'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 75,
            'title' => 'Giro — el contrato, el registro de acceso y lo que el vigilante vio',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // IA = informe de la auditora (extract in giro.md); DO = Osvaldo's
            // written declaration (also in giro.md). Neither has an image.
            'evidence_codes' => ['V05', 'V11', 'IA', 'DO'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 75,
            'title' => 'Audio — nota de voz de Celina, recuperada de su teléfono',

            'audio_file' => 'a1-nota-voz-celina.mp3',
            'audio_scene' => 'Nota de voz de móvil grabada dentro de un vehículo parado: ruido de motor y de patio de carga de fondo, sin música. Una pausa breve a mitad de la nota, con el ruido del vehículo.',
            'audio_context' => 'Celina, mujer de 50 años, profesional y contenida, graba un mensaje para una colega de confianza poco antes de ir a la base de transportistas. Un leve nerviosismo al final. No acusa a nadie en concreto. Duración: 25–30 segundos.',
            'audio_speaker' => 'Speaker 1 - Celina Rengifo',
            'audio_voice' => 'Gacrux',
            'audio_script' => <<<'TXT'
                Ya tengo claro el patrón. Ribera recibe camiones completos cada semana, en el papel, pero esa oficina apenas tiene mercancía. Voy a ir esta noche a la base de transportistas, a ver quién hace esa ruta, antes de que arranque todo mañana. Si hay alguien ahí que sepa algo, prefiero hablarlo esta noche, en privado, antes de llevarlo a la junta.

                Espero que sea solo un malentendido administrativo. Pero no lo creo.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 90,
            'title' => 'Cierre — lo declarado y lo real, y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V07', 'V09'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 110,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, qué ocurrió y por qué, el formulario hace dos preguntas sobre el desvío de mercancía: quién lo diseñó y con quién, y cómo funcionaba. No son la misma pregunta.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-reparto-invisible/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-mapa-red.png', 'caption' => 'Mapa esquemático de la red: cinco nodos, cuatro rutas'],
        ],
        'pista-privada-turnos-1.md' => [
            ['file' => 'pt1-turnos-lunes-martes.png', 'caption' => 'Plan de turnos de transporte: lunes y martes'],
        ],
        'pista-privada-turnos-2.md' => [
            ['file' => 'pt2-turnos-miercoles.png', 'caption' => 'Plan de turnos de transporte: miércoles'],
        ],
        'pista-privada-turnos-3.md' => [
            ['file' => 'pt3-turnos-jueves.png', 'caption' => 'Plan de turnos de transporte: jueves'],
        ],
        'pista-privada-turnos-4.md' => [
            ['file' => 'pt4-turnos-viernes.png', 'caption' => 'Plan de turnos de transporte: viernes, turnos del operativo'],
        ],
        'pista-privada-orden-carga.md' => [
            ['file' => 'v02a-orden-carga-parcial.png', 'caption' => 'Orden de carga de bodega, vista parcial: lunes a jueves'],
        ],
        'expansion.md' => [
            ['file' => 'v06-plan-entregas.png', 'caption' => 'Plan de entregas de la semana previa a La Noche de Reparto'],
            ['file' => 'v10-tablero-voluntarios.png', 'caption' => 'Tablero de voluntarios de la oficina central'],
        ],
        'desarrollo.md' => [
            ['file' => 'v02-orden-carga.png', 'caption' => 'Orden de carga de bodega, completa'],
            ['file' => 'v03-acta-entrega-ribera.png', 'caption' => 'Acta de entrega de Centro Ribera'],
        ],
        'complicacion.md' => [
            ['file' => 'v04-oficina-visita.png', 'caption' => 'Centro Ribera, en la visita de verificación de Celina'],
            ['file' => 'v08-muelle-carga-noche.png', 'caption' => 'La zona de carga de la base de transportistas, esa madrugada'],
        ],
        'giro.md' => [
            ['file' => 'v05-contrato-distribucion.png', 'caption' => 'Contrato de distribución'],
            ['file' => 'v11-registro-acceso-base.png', 'caption' => 'Registro de control de acceso de la base de transportistas'],
        ],
        'cierre.md' => [
            ['file' => 'v07-registro-recepcion-norte.png', 'caption' => 'Registro de recepción del Centro Comunitario Norte'],
            ['file' => 'v09-inventario-existencias.png', 'caption' => 'Inventario de existencias al cierre de la campaña'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v11-registro-acceso-base.png', 'caption' => 'Celina entra a las 22:05 y no sale; Walter sale a las 22:47'],
            ['file' => 'v02-orden-carga.png', 'caption' => 'Lo que salió de bodega: nueve pallets el viernes por la noche'],
            ['file' => 'v03-acta-entrega-ribera.png', 'caption' => 'Lo que dice el papel que llegó a Ribera'],
            ['file' => 'v09-inventario-existencias.png', 'caption' => 'Lo que de verdad llegó: lo asignado a Ribera frente a lo asignado a Norte'],
        ],
    ],
];
