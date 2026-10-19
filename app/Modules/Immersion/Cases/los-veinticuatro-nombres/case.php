<?php

/**
 * Case manifest: "Los Veinticuatro Nombres"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: a death case with TWO responsible people for two different
 * acts. Hugo Benavídez (with Ramiro Casals, hidden owner of the supplier)
 * inflated the hours and roles certified for several of the 24 names of the
 * "Calendario de los 24" to divert part of a municipal volunteering grant.
 * Marisol Ferreira holds the biggest falsification, was used as a front
 * without knowing it, and is nevertheless the one who killed the internal
 * auditor, Rosario Beltrán: in a panic, believing she alone would be named
 * before the board that night, she struggled with Rosario, who fell and hit
 * her head on a shelf. The engine scores the culprit (Marisol); the fraud's
 * author and method are asked as extra accusation questions.
 *
 * Reference timetable (single source of truth for every file of this case; the
 * design doc has almost no clock times, so these were fixed here). No calendar
 * dates anywhere: it is "el día de la gala".
 *   Gala: 21:00, Salón de Eventos. Pre-gala board meeting (junta): 19:30 in the
 *   sede. Rosario's card, planta de administración (P2, entries only; exit is
 *   by push button and is not logged): 08:52, 14:10, 16:34. Dora's card (ronda
 *   de cierre): 18:38. Contabilidad door (C1, entries AND exits, it guards the
 *   cash box): Patricio 08:30 in / 13:10 out / 14:02 in / 18:47 out.
 *   16:20 Rosario goes down to the reception; ~16:30 Marisol arrives; 16:34
 *   Rosario's card opens P2 for both (Marisol's volunteer credential is an ID
 *   badge, not an access card, so only Rosario shows in the log); the struggle
 *   and the fall happen shortly after; ~16:54 Marisol comes down alone ("unos
 *   veinte minutos después" in Dora's statement); 18:38–18:40 Dora finds the
 *   body ("a las siete menos veinte"). Rosario never leaves the office.
 *   Almacén (registro de entregas, signed by Hugo AND Ramiro): 15:45, 16:40,
 *   17:25. Almacén ↔ sede: 15 min by car. Salón ↔ sede: 10 min on foot. R1 ↔
 *   sede 20 min, R2 ↔ sede 25 min, almacén ↔ salón 20 min (V08).
 *   Esteban at the salón 15:30–17:40 (until "casi las seis menos veinte"):
 *   Fernanda with the donors in the adjoining room, Valentina in the main
 *   room. Noelia + Camilo in Camilo's despacho (sede, planta baja) 16:00–17:30.
 *   Campaign: 8 weeks. Inflated hours are dated in weeks 3 and 5; payments to
 *   Materiales Hogar Cálido double in those same weeks. V03 = R1, week 3;
 *   V04 = R2, week 5.
 *   The 24 names, with declared and verified hours, are in ASSET_PROMPTS.md
 *   and in content/suspects/registro-horas-obra.md (declared 954 h, verified
 *   876 h, difference 78 h; Marisol 86/41, Antonia 60/47, Rubén 48/36,
 *   Camilo 52/44).
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - Two culprits. `culprit_slug` is Marisol (the one who caused the physical
 *    harm). The fraud's author (Hugo, with Ramiro) and the method are the two
 *    extra `accusation.questions`; the design's 30/20/25/25 scoring becomes
 *    culprit 30 + "qué ocurrió" 10 + motive 10 + responsable_fraude 25 +
 *    metodo_fraude 25. The design's "same author for both without evidence →
 *    revisión obligatoria" penalty is folded into the `guidance` of those two
 *    questions (zero if the answer merges both acts). The T+100 unlock event
 *    says that the form asks these extra questions.
 *  - Questions: 5 per person at most, plus 3 for the seven suspects with
 *    confrontations (the design's "2 / 1 confrontations" are folded into the
 *    engine's 3). Gated by `interrogation.stages` on the evidence codes below.
 *  - The junta. The design says both "esa misma noche" and "la noche siguiente
 *    a la gala". It is the same evening, before the gala (19:30), because the
 *    victim's own voice note and the design's confession both say "esta noche".
 *    The body is found 18:40, "horas antes de la gala" (21:00).
 *  - The access log. The design says "nadie más entró o salió" of the planta.
 *    A push-button exit cannot be logged, so V11 proves that NOBODY ELSE
 *    ENTERED; that Marisol left alone and alone is Dora's testimony (DD).
 *    Marisol's credential is a volunteer ID badge; she went up behind
 *    Rosario's card, which is why only Rosario appears at 16:34.
 *  - The private matrix ("Tabla de los 24"). Modelled as six `random_player`
 *    events at T+12, one fragment each (codes F1–F6), four names per fragment
 *    exactly as the design's json (F1 Marisol, F2 Antonia, F3 Rubén, F4 Camilo,
 *    F5 and F6 only control names, so the four discrepant names are always in
 *    distinct fragments). With 4 players some get two, with 8 players two
 *    get none: that is why the public copy exists. The design's "fragment size
 *    depends on the number of players" cannot be done by the engine, so the
 *    split is fixed at six. The public copy (V02, T+25, `all`) lists the same
 *    24 rows with names shortened to initial + surname, declared role and
 *    declared hours, WITHOUT the approval column or notes ("anonimizada"). It
 *    is deliberately complete: no critical datum depends on a single private
 *    fragment.
 *  - T+55 fail-safe: the "resumen agregado anónimo" is its own `all` event
 *    (resumen-agregado.md, code RA), delivered whether or not the table was
 *    reconstructed. The other fail-safes (Dora making Marisol explicit, the
 *    crossed alibis) are Dora's fail-safe section, giro.md and cierre.md.
 *  - Source of declarations without an image, same device as DF/DA elsewhere:
 *    DD = Dora's written statement (giro.md, T+70); PP = full supplier payment
 *    register by week (complicacion.md, T+55); RE = warehouse delivery register
 *    (cierre.md, T+85); RA = aggregate summary (T+55). The engine's markdown
 *    has NO tables extension (Str::markdown), so every table of those texts is
 *    a bullet list.
 *  - A1 (Rosario's posthumous voice note) is a timeline `audio_email`; A2 (the
 *    confession) is `solution.confession_script`, generated per game.
 *  - V01 numbers the 24 boxes in alphabetical order of first name, so that
 *    the box numbers do not reveal which names are discrepant.
 *  - The design's "evidence ≠ roster": the 24 names are table data. Only
 *    Marisol and Camilo (already suspects) are people; Antonia and Rubén exist
 *    only in the tables, with no roster entry.
 *  - The registro de horas does not give the per-person hours until V02 (the
 *    shared reference) and does not give the certification trace (who loaded
 *    and approved each entry) until V05; Noelia does not give hours until
 *    V03+V04; Dora does not say who she saw until DD; Hugo and Ramiro do not
 *    mention signing the delivery register until RE.
 *  - Money amounts (V09 and PP) are invented for this port (the design asks
 *    only for "tarifa por hora" and "total aproximado"): US$ 10 per certified
 *    hour, +25 % for coordination roles, ~US$ 13,000 granted, and the
 *    weekly payments to Materiales Hogar Cálido listed in complicacion.md.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'Los Veinticuatro Nombres',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Fundación Calles Cálidas',
    'authority' => 'Comisión de Revisión de la Fundación Calles Cálidas',

    'victim' => [
        'name' => 'Rosario Beltrán',
        'label' => 'Víctima',
        'photo' => 'rosario-beltran.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design asks for 4 questions per suspect + 2 / 1 confrontations,
        // 3 per witness. The engine rule is up to 5 + 3 (see `stages`).
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
        'tagline' => 'Los veinticuatro nombres son reales. Lo que hicieron, no todos.',

        'description' => <<<'TXT'
            Horas antes de la gala de la Fundación Calles Cálidas, en la que se
            iba a revelar el Calendario de los 24 (las veinticuatro personas
            voluntarias que la fundación reconoce cada año), la auditora interna
            aparece sin vida en la oficina de administración. Los veinticuatro
            nombres del calendario son reales. Las horas y los roles que la
            fundación certificó para la subvención municipal, no todos.

            Tu equipo (4 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a siete sospechosos, tres testigos y
            dos fuentes de registro durante 100 minutos. Cada investigador
            puede recibir un extracto privado de la hoja de horas: nadie tiene
            la tabla completa, y solo existe si la mesa la reconstruye en voz alta
            y la contrasta con las obras, los pagos y los accesos.

            No todo lo que se oculta en una fundación tiene que ver con la
            muerte de Rosario. Separar los secretos de cada uno es lo que lleva
            hasta ella.
            TXT,

        // Archivo esperado en public/immersion/los-veinticuatro-nombres/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/los-veinticuatro-nombres).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Los veinticuatro nombres son reales. Lo que hicieron, no todos.',
            'bullets' => [
                '4 a 8 investigadores y una hoja de horas repartida en extractos privados: la tabla completa solo existe si la reconstruyen entre todos',
                'Siete sospechosos, tres testigos y dos fuentes de registro a quienes interrogar',
                '100 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 100,
        'min_players' => 4,
        'max_players' => 8,

        'price_amount' => 74900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/los-veinticuatro-nombres/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 28,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The three witnesses and the two record sources are modeled exactly like a
    | suspect entry. Rosario, the dead auditor, is the "victim" and is not
    | interrogable. The other 22 names of the table are data, not people.
    |
    */

    'suspects' => [
        'hugo-benavidez' => [
            'name' => 'Hugo Benavídez',
            'role' => 'sospechoso',
            'file' => 'suspects/hugo-benavidez.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V02', 'V05', 'PP'], 'confrontation' => true],
            ]],
            'photo' => 'hugo-benavidez.png',
            'connection' => 'Coordinador de obras de la fundación',
            'motive' => 'Ninguno respecto a la muerte; necesitaba a Rosario viva y callada. Organizó el fraude de las horas y las facturas',
            'alibi' => 'En el almacén de materiales con Ramiro Casals toda la tarde; ambos firmaron las tres recepciones de material',
            'public' => [
                'age' => '45 años',
                'profile' => 'Coordinador de obras de la fundación. Seguro y técnico; habla con orgullo de sus cuadrillas.',
                'alibi' => 'Dice haber estado en el almacén de materiales con el proveedor toda la tarde.',
                'facts' => [
                    'Organiza las cuadrillas de las dos viviendas en renovación y la logística del almacén.',
                    'Es quien más trato diario tiene con los registros de obra de la campaña.',
                ],
            ],
        ],
        'marisol-ferreira' => [
            'name' => 'Marisol Ferreira',
            'role' => 'sospechosa',
            'file' => 'suspects/marisol-ferreira.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V02']],
                ['questions' => 2, 'after' => ['DD'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['DD', 'V10', 'V11'], 'confrontation' => true],
            ]],
            'photo' => 'marisol-ferreira.png',
            'connection' => 'Voluntaria histórica de la fundación; una de las veinticuatro personas reconocidas en el calendario',
            'motive' => 'Pánico: creyó que sería la única señalada ante la junta esa noche y temió perder su empleo y su estabilidad',
            'alibi' => 'Dice que salió temprano de su trabajo y se fue a casa, y que no habló con Rosario esa tarde; sin testigos',
            'public' => [
                'age' => '34 años',
                'profile' => 'Voluntaria histórica de la fundación, una de las veinticuatro reconocidas este año. Cálida y trabajadora; trabaja como administrativa en un centro educativo.',
                'alibi' => 'Dice haberse ido temprano a casa esa tarde.',
                'facts' => [
                    'Lleva años colaborando en las obras de la fundación.',
                    'Es una de las personas más queridas del equipo de voluntarios.',
                ],
            ],
        ],
        'fernanda-ocampo' => [
            'name' => 'Fernanda Ocampo',
            'role' => 'sospechosa',
            'file' => 'suspects/fernanda-ocampo.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V05'], 'confrontation' => true],
            ]],
            'photo' => 'fernanda-ocampo.png',
            'connection' => 'Presidenta y directora de la fundación; superiora directa de Rosario',
            'motive' => 'Ninguno respecto a la muerte; intuía que algo no cuadraba y prefirió no indagar antes de la gala',
            'alibi' => 'En una reunión con donantes en el salón de la gala toda la tarde, con Esteban Vidal',
            'public' => [
                'age' => '56 años',
                'profile' => 'Presidenta y directora de la fundación. Institucional y cálida; muy atenta a que la gala salga bien.',
                'alibi' => 'Dice haber estado con los donantes toda la tarde.',
                'facts' => [
                    'Es la máxima responsable institucional de la campaña.',
                    'La subvención municipal depende de que la campaña cierre bien.',
                ],
            ],
        ],
        'valentina-rios' => [
            'name' => 'Valentina Ríos',
            'role' => 'sospechosa',
            'file' => 'suspects/valentina-rios.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V02', 'V07', 'V05'], 'confrontation' => true],
            ]],
            'photo' => 'valentina-rios.png',
            'connection' => 'Responsable de comunicación y del diseño del Calendario de los 24',
            'motive' => 'Ninguno respecto a la muerte; oculta que fue ella quien alertó a Rosario sobre el rol de Marisol',
            'alibi' => 'En el salón de la gala preparando las tarjetas y la mesa de entrada, con Esteban Vidal pasando por allí',
            'public' => [
                'age' => '27 años',
                'profile' => 'Responsable de comunicación y del diseño del Calendario de los 24. Entusiasta con la campaña.',
                'alibi' => 'Dice haber estado en el salón de la gala preparándolo todo.',
                'facts' => [
                    'Diseña cada año las tarjetas y el tablero del calendario.',
                    'Controla la imagen pública de la campaña.',
                ],
            ],
        ],
        'patricio-nunez' => [
            'name' => 'Patricio Núñez',
            'role' => 'sospechoso',
            'file' => 'suspects/patricio-nunez.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['PP'], 'confrontation' => true],
            ]],
            'photo' => 'patricio-nunez.png',
            'connection' => 'Tesorero y contable de la fundación',
            'motive' => 'Ninguno respecto a la muerte; aceptó una comisión de Ramiro por no profundizar en facturas altas',
            'alibi' => 'En contabilidad, planta baja de la sede, toda la tarde; el registro de acceso de esa puerta lo confirma',
            'public' => [
                'age' => '50 años',
                'profile' => 'Tesorero y contable de la fundación. Meticuloso y orgulloso de sus libros.',
                'alibi' => 'Dice haber estado en su oficina revisando la documentación de la subvención.',
                'facts' => [
                    'Ejecuta los pagos a los proveedores de la fundación.',
                    'Colabora con Rosario en los documentos de la subvención.',
                ],
            ],
        ],
        'ramiro-casals' => [
            'name' => 'Ramiro Casals',
            'role' => 'sospechoso',
            'file' => 'suspects/ramiro-casals.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['PP'], 'confrontation' => true],
            ]],
            'photo' => 'ramiro-casals.png',
            'connection' => 'Proveedor de materiales de la fundación (Materiales Hogar Cálido)',
            'motive' => 'Ninguno respecto a la muerte; su interés está en que el esquema siga en silencio',
            'alibi' => 'En el almacén de materiales con Hugo Benavídez toda la tarde; ambos firmaron las tres recepciones de material',
            'public' => [
                'age' => '48 años',
                'profile' => 'Proveedor de materiales de la fundación. Comercial y afable.',
                'alibi' => 'Dice haber estado en el almacén recibiendo entregas.',
                'facts' => [
                    'La empresa que representa, Materiales Hogar Cálido, es el mayor proveedor de la campaña.',
                    'Visita a menudo el almacén de materiales.',
                ],
            ],
        ],
        'camilo-serrano' => [
            'name' => 'Camilo Serrano',
            'role' => 'sospechoso',
            'file' => 'suspects/camilo-serrano.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V02', 'V05'], 'confrontation' => true],
            ]],
            'photo' => 'camilo-serrano.png',
            'connection' => 'Trabajador social; encargado de seleccionar los veinticuatro nombres del calendario',
            'motive' => 'Ninguno respecto a la muerte ni al esquema de las horas; un fraude personal menor, de vergüenza',
            'alibi' => 'En su despacho de la planta baja de la sede con Noelia Camacho, de las cuatro a las cinco y media',
            'public' => [
                'age' => '38 años',
                'profile' => 'Trabajador social de la fundación y una de las veinticuatro personas del calendario. Cercano y amable.',
                'alibi' => 'Dice haber estado en su despacho toda la tarde.',
                'facts' => [
                    'Elige cada año los veinticuatro nombres del calendario.',
                    'Coordinaba con Rosario el cierre administrativo de la lista.',
                ],
            ],
        ],
        'dora-quintero' => [
            'name' => 'Dora Quintero',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/dora-quintero.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DD']],
            ]],
            'photo' => 'dora-quintero.png',
            'connection' => 'Conserje y portera de la sede',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '62 años',
                'profile' => 'Conserje y portera de la sede. Precisa y serena; conoce a todo el mundo por el nombre.',
                'facts' => [
                    'Está en la recepción durante el día y hace la ronda de cierre.',
                    'Encontró a Rosario al hacer su ronda de cierre.',
                ],
            ],
            'accusable' => false,
        ],
        'esteban-vidal' => [
            'name' => 'Esteban Vidal',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/esteban-vidal.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
            ]],
            'photo' => 'esteban-vidal.png',
            'connection' => 'Encargado de relaciones con donantes',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '41 años',
                'profile' => 'Encargado de relaciones con donantes. Sociable y algo agobiado por la presión de la gala.',
                'facts' => [
                    'Pasó la tarde en el salón de la gala.',
                    'Conoce el ambiente de presión por cerrar la subvención.',
                ],
            ],
            'accusable' => false,
        ],
        'noelia-camacho' => [
            'name' => 'Noelia Camacho',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/noelia-camacho.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V03', 'V04']],
            ]],
            'photo' => 'noelia-camacho.png',
            'connection' => 'Voluntaria y coordinadora de turnos en obra',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '29 años',
                'profile' => 'Voluntaria que lleva el control de turnos en las dos obras. Práctica y ordenada.',
                'facts' => [
                    'Apunta en una tablilla quién llega y quién se va de cada jornada de obra.',
                    'Conoce de cerca a casi todos los voluntarios.',
                ],
            ],
            'accusable' => false,
        ],
        'registro-horas-obra' => [
            'name' => 'Registro de horas y control de obra',
            'role' => 'fuente secundaria (registro de campo)',
            'file' => 'suspects/registro-horas-obra.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V02']],
                ['questions' => 1, 'after' => ['V05']],
            ]],
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as the other systems) — a shot of
            // the time-sheet screen works as its "portrait".
            'photo' => 'registro-horas-obra.png',
            'connection' => 'Firmas de entrada y salida, función y horas por jornada en las dos obras',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente de registro: guarda quién firmó entrada y salida en cada obra y en qué función.',
                'facts' => [
                    'Responde con fechas, nombres y horas firmadas.',
                    'No interpreta intenciones: solo contrasta la realidad de campo.',
                ],
            ],
            'accusable' => false,
        ],
        'contabilidad-pagos' => [
            'name' => 'Contabilidad / registro de pagos a proveedores',
            'role' => 'fuente secundaria (registro contable)',
            'file' => 'suspects/contabilidad-pagos.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['PP']],
                ['questions' => 1, 'after' => ['V09']],
            ]],
            'photo' => 'contabilidad-pagos.png',
            'connection' => 'Fechas, montos y proveedor de cada pago realizado por la fundación',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente contable: registra la fecha, el monto y el proveedor de cada pago de la fundación.',
                'facts' => [
                    'Responde con fechas y montos.',
                    'No interpreta intenciones: solo registra los pagos.',
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
        'culprit_slug' => 'marisol-ferreira',

        'headline' => 'Marisol, la voluntaria cuyo nombre concentra la mayor falsificación, no organizó el fraude, pero sí causó la muerte de Rosario en un forcejeo, por pánico. El fraude fue de Hugo, con Ramiro.',

        'motive' => 'Pánico, no avaricia: Marisol creyó, equivocadamente, que Rosario la señalaría a ella sola esa misma noche ante la junta como responsable de un fraude, y temió perder su empleo y la estabilidad de su hijo.',

        'method' => 'Rosario la citó a solas en la oficina de administración para preguntarle qué sabía de las diferencias en las horas. Al oír que lo reportaría esa noche ante toda la junta, Marisol entró en pánico; hubo un forcejeo breve y no premeditado, y Rosario cayó y se golpeó la cabeza contra el borde de una estantería. Marisol salió sin pedir ayuda y dejó caer su credencial junto al escritorio.',

        'key_evidence' => [
            'La declaración de Dora: vio subir juntas a Rosario y a Marisol, vio bajar a Marisol sola y alterada, y nunca vio salir a Rosario.',
            'La credencial de voluntaria de Marisol, caída junto al escritorio de la oficina de administración, frente a su versión de que no habló con Rosario esa tarde.',
            'El registro de control de acceso: nadie más entró en la planta de administración entre las 16:34 y las 18:38.',
            'El acta y la nota de voz de Rosario: la revisión era sobre un patrón de varios nombres y su intención con Marisol era preguntar, no denunciarla.',
            'Los extractos, la tabla y el registro de obra, el módulo de certificación y los pagos por semana: Hugo cargó y aprobó cada entrada, y los pagos a la empresa de Ramiro se duplican en las semanas de las horas infladas.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including the three witnesses and the two record sources.
         */
        'exonerations' => [
            'hugo-benavidez' => 'Sí organizó el fraude de las horas, con Ramiro, pero no pudo causar la muerte de Rosario: esa tarde estaba en el almacén, a 15 minutos de la sede, y firmó con Ramiro las recepciones de material de las 15:45, las 16:40 y las 17:25. Además necesitaba a Rosario viva y callada; su muerte provocó justo la auditoría a fondo que quería evitar.',

            'fernanda-ocampo' => 'Sí intuía desde hacía meses que algo no cuadraba y prefirió no indagar antes de la gala, una negligencia real pero ajena a la muerte. Estuvo con los donantes en el salón de la gala toda la tarde, y Esteban Vidal estuvo con ella.',

            'valentina-rios' => 'Sí ocultó que había alertado a Rosario sobre el rol de Marisol, por miedo a que la culparan de arruinar la gala, pero eso no es un crimen. Estuvo montando el salón de la gala toda la tarde, y Esteban Vidal la veía allí cada vez que pasaba.',

            'patricio-nunez' => 'Sí aceptó una comisión de Ramiro por no profundizar en facturas altas, una complicidad pasiva ajena a la muerte. El registro de acceso de contabilidad lo muestra dentro de su oficina, sin salir, de las 14:02 a las 18:47, y no subió a la planta de administración.',

            'ramiro-casals' => 'Sí es el dueño real de la empresa proveedora y socio oculto de Hugo, y se repartían el desvío, pero su interés era que Rosario no hablara, no que muriera. Estuvo en el almacén con Hugo toda la tarde y firmó las mismas tres recepciones.',

            'camilo-serrano' => 'Sí figura con 8 horas que no hizo, por unos turnos que le cubrió un compañero, un fraude personal de vergüenza sin relación con el esquema de Hugo ni con la muerte. Estuvo en su despacho de la planta baja con Noelia de las cuatro a las cinco y media.',

            'dora-quintero' => 'Es una testigo, no una sospechosa: contó lo que vio desde la recepción (que Marisol subió con Rosario y bajó sola) y encontró a Rosario en su ronda de cierre. No sabía qué pasó dentro de la oficina ni tenía motivo alguno.',

            'esteban-vidal' => 'Es un testigo, no un sospechoso: confirmó dónde estaban Fernanda y Valentina esa tarde y contó el ambiente de presión por cerrar la subvención. Estuvo en el salón de la gala, lejos de la sede, y no tenía motivo alguno.',

            'noelia-camacho' => 'Es una testigo, no una sospechosa: solo contó quién trabajó realmente en obra y en qué función, y confirmó la coartada de Camilo. Estuvo con él en su despacho de la planta baja y no tenía motivo alguno.',

            'registro-horas-obra' => 'Es un registro de campo, no una persona: no puede haber intervenido. Guardó con exactitud quién firmó y cuántas horas, y mostró qué usuario cargó y aprobó cada entrada certificada; no interpreta intenciones.',

            'contabilidad-pagos' => 'Es un registro contable, no una persona: no puede haber intervenido. Guardó con exactitud fechas, montos y proveedor de cada pago, incluidos los picos de las semanas 3 y 5; no interpreta intenciones.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Leda',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            No fui a esa oficina para hacerle daño. Me dijo que lo iba a reportar
            esa misma noche, delante de toda la junta, y pensé... pensé que hablaba
            de mí, que me iban a señalar a mí sola. No pensé con claridad.

            Intenté explicarle, ella intentó calmarme, hubo un forcejeo, muy breve,
            y se cayó. No supe qué hacer. Salí de ahí y no le dije a nadie. No fue lo
            que planeé, pero fue lo que hice, y tengo que responder por eso.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió en la oficina de administración?',
            'hint' => 'Cuenta qué pasó entre Rosario y la responsable de su muerte esa tarde, y cómo murió.',
            'points' => 10,
            'guidance' => 'Lo esencial: un forcejeo breve y no premeditado durante una conversación sobre las horas, tras el cual Rosario cayó y se golpeó la cabeza contra una estantería; la responsable salió sin pedir ayuda. Cero si lo presenta como un asesinato planeado.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué creía la responsable que iba a pasar esa noche y qué temía perder.',
            'points' => 10,
            'guidance' => 'Lo esencial: pánico y miedo a ser señalada personalmente como responsable del fraude, con riesgo para su empleo y su estabilidad. Cero si dice que lo hizo para encubrir un fraude que ella organizó o por dinero.',
        ],
        'questions' => [
            [
                'key' => 'responsable_fraude',
                'label' => '¿Quién organizó la falsificación de las horas y quién lo ayudó?',
                'hint' => 'Nombra al responsable del esquema de las horas y a su cómplice, si lo hubo. No tiene por qué ser la misma persona que causó la muerte.',
                'answer' => 'Hugo Benavídez, coordinador de obras, en connivencia con Ramiro Casals, dueño encubierto de Materiales Hogar Cálido, el proveedor de materiales de la fundación.',
                'points' => 25,
                'guidance' => 'Cero si atribuye la organización del fraude a Marisol o si presenta el fraude y la muerte como obra de la misma persona sin distinguirlos. Más de la mitad si nombra a Hugo como organizador aunque omita a Ramiro; puntos completos con Hugo y Ramiro.',
            ],
            [
                'key' => 'metodo_fraude',
                'label' => '¿Cómo se hizo el fraude y qué se ganaba con él?',
                'hint' => 'Qué se alteró, en qué nombres y cómo se convertía en dinero.',
                'answer' => 'Inflar las horas y, en algunos casos, el rol certificado de varios de los veinticuatro nombres del calendario (sobre todo el de Marisol, una voluntaria usada sin saberlo) para cobrar más subvención y justificar compras de materiales infladas a la empresa de Ramiro; la diferencia entre el coste real y lo facturado se repartía en efectivo.',
                'points' => 25,
                'guidance' => 'Lo esencial: horas o roles certificados de más en varios nombres, ligados a pagos o facturas de materiales infladas con el proveedor. Mitad de puntos si solo explica la inflación de horas sin conectarla con los pagos al proveedor. No exigir las cifras exactas.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+25/T+40/T+55/T+70/T+85/T+100
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Los Veinticuatro Nombres — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01', 'V10'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Apertura — las personas, las fuentes y las hojas de horas',
            'source_file' => 'apertura.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V07'],
        ],
        // The private matrix: one complete fragment per event, handed to the
        // player with the fewest private clues. The public copy (V02, T+25)
        // repeats all 24 rows, so no critical datum depends on one fragment.
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — extracto 1 de la hoja de certificación',
            'source_file' => 'pista-privada-fragmento-1.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['F1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — extracto 2 de la hoja de certificación',
            'source_file' => 'pista-privada-fragmento-2.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['F2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — extracto 3 de la hoja de certificación',
            'source_file' => 'pista-privada-fragmento-3.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['F3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — extracto 4 de la hoja de certificación',
            'source_file' => 'pista-privada-fragmento-4.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['F4'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — extracto 5 de la hoja de certificación',
            'source_file' => 'pista-privada-fragmento-5.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['F5'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — extracto 6 de la hoja de certificación',
            'source_file' => 'pista-privada-fragmento-6.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['F6'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 25,
            'title' => 'Expansión — la tabla de jornadas y el mapa de las intervenciones',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V02', 'V08'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 40,
            'title' => 'Desarrollo — lo que se ve en obra',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V03', 'V04'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Complicación — el acta de Rosario y los pagos completos',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // PP = the full week-by-week payment register, the body of
            // complicacion.md. It has no image, so it gets its own code (same
            // device as DF in other cases).
            'evidence_codes' => ['V05', 'PP'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 55,
            'title' => 'Audio — nota de voz de Rosario, hallada después',

            'audio_file' => 'a1-nota-voz-rosario.mp3',
            'audio_scene' => 'Nota de voz grabada en una oficina silenciosa, sin música; al final se oye, muy leve, ruido de papeles.',
            'audio_context' => 'Rosario, auditora interna de 58 años, graba una nota para sí misma o para un colega de confianza: voz tranquila, profesional, con un ligero nerviosismo contenido y una duda genuina al final. Debe sonar como alguien a punto de hacer lo correcto, no como alguien que ya tiene todas las respuestas. Duración: 20-25 segundos.',
            'audio_speaker' => 'Speaker 1 - Rosario Beltrán',
            'audio_voice' => 'Gacrux',
            'audio_script' => <<<'TXT'
                Terminé de cruzar los números. No es solo un nombre, son varios. El de
                Marisol es el que más destaca, con diferencia, pero no creo que ella
                supiera nada de esto. Voy a hablar con ella esta tarde, con calma, antes
                de llevarlo a la junta esta noche. Si hay algo que no cuadra del todo con
                Hugo, prefiero confirmarlo antes de decir nada en público.

                Espero estar equivocada. Pero no creo que lo esté.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Resumen agregado de la hoja de horas',
            'source_file' => 'resumen-agregado.md',
            'delivery_mode' => 'all',
            // RA = the design's T+55 fail-safe: an anonymous aggregate summary
            // with no causes or culprits, delivered to every table.
            'evidence_codes' => ['RA'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 70,
            'title' => 'Giro — la tarde en la sede',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DD = Dora's written (expanded) declaration, the body of giro.md.
            'evidence_codes' => ['DD', 'V06', 'V11'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 85,
            'title' => 'Cierre — la planilla de fondos y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // RE = the warehouse delivery register (three signed receptions),
            // text inside cierre.md.
            'evidence_codes' => ['V09', 'RE'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 100,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién causó la muerte de Rosario, cómo y por qué, el formulario hace dos preguntas más sobre el caso, que no tienen por qué tener la misma respuesta: quién organizó la falsificación de las horas y cómo se hizo.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/los-veinticuatro-nombres/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-calendario-de-los-24.png', 'caption' => 'El Calendario de los 24, tablero expuesto en la sede'],
            ['file' => 'v10-oficina-administracion.png', 'caption' => 'La oficina de administración, cuando Rosario fue hallada'],
        ],
        'apertura.md' => [
            ['file' => 'v07-tarjetas-reconocimiento.png', 'caption' => 'Tarjetas de reconocimiento preparadas para la gala (un grupo de ellas)'],
        ],
        'pista-privada-fragmento-1.md' => [
            ['file' => 'f1-extracto-hoja-horas.png', 'caption' => 'Extracto 1 de 6 de la hoja de certificación de horas'],
        ],
        'pista-privada-fragmento-2.md' => [
            ['file' => 'f2-extracto-hoja-horas.png', 'caption' => 'Extracto 2 de 6 de la hoja de certificación de horas'],
        ],
        'pista-privada-fragmento-3.md' => [
            ['file' => 'f3-extracto-hoja-horas.png', 'caption' => 'Extracto 3 de 6 de la hoja de certificación de horas'],
        ],
        'pista-privada-fragmento-4.md' => [
            ['file' => 'f4-extracto-hoja-horas.png', 'caption' => 'Extracto 4 de 6 de la hoja de certificación de horas'],
        ],
        'pista-privada-fragmento-5.md' => [
            ['file' => 'f5-extracto-hoja-horas.png', 'caption' => 'Extracto 5 de 6 de la hoja de certificación de horas'],
        ],
        'pista-privada-fragmento-6.md' => [
            ['file' => 'f6-extracto-hoja-horas.png', 'caption' => 'Extracto 6 de 6 de la hoja de certificación de horas'],
        ],
        'expansion.md' => [
            ['file' => 'v02-tabla-jornadas.png', 'caption' => 'Copia de consulta de la tabla de jornadas: los veinticuatro nombres, con rol y horas declaradas'],
            ['file' => 'v08-mapa-intervenciones.png', 'caption' => 'Mapa de las intervenciones de la campaña'],
        ],
        'desarrollo.md' => [
            ['file' => 'v03-obra-r1-correa.png', 'caption' => 'Vivienda R1 (familia Correa): jornada de la semana 3'],
            ['file' => 'v04-obra-r2-giraldo.png', 'caption' => 'Vivienda R2 (familia Giraldo): jornada de la semana 5'],
        ],
        'complicacion.md' => [
            ['file' => 'v05-acta-revision-interna.png', 'caption' => 'Acta de revisión interna de Rosario Beltrán'],
        ],
        'giro.md' => [
            ['file' => 'v06-registro-herramientas.png', 'caption' => 'Registro de herramientas de las obras (extracto)'],
            ['file' => 'v11-registro-control-acceso.png', 'caption' => 'Registro de control de acceso de la sede'],
        ],
        'cierre.md' => [
            ['file' => 'v09-planilla-fondos.png', 'caption' => 'Planilla de fondos de la campaña'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v10-oficina-administracion.png', 'caption' => 'La credencial de Marisol, caída junto al escritorio'],
            ['file' => 'v11-registro-control-acceso.png', 'caption' => 'Nadie más entró en la planta de administración'],
            ['file' => 'v05-acta-revision-interna.png', 'caption' => 'El acta de Rosario: un patrón de varios nombres, no una persona'],
            ['file' => 'v09-planilla-fondos.png', 'caption' => 'El incentivo del fraude: cada hora y cada rol certificados de más'],
        ],
    ],
];
