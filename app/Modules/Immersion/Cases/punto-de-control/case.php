<?php

/**
 * Case manifest: "Punto de Control"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a disappearance case whose central trap is a false
 * equivalence. The table tends to read "dorsal 114 completed the route" as
 * "Renata completed the route". ChipTrack Sierra registers DEVICES, never
 * people, and the system never fails or is tampered with: Julieta wore Renata's
 * vest (chip included) through the canyon. Renata herself, on a route she had
 * planned beforehand, went off-route to check whether Bruno was receiving
 * illegal outside assistance; the confrontation that followed (a separate
 * event from the chip swap) left her retained, alive and with an injured ankle,
 * in a forest shelter. The AI rule that matters: never say "Renata passed the
 * control" when the source only says "dorsal 114 registered a read".
 *
 * Reference timetable (single source of truth for every file of this case; all
 * hours that appear in evidence, md files and the manifest derive from it):
 *   07:30 Salida (Plaza de la Estación).   Zona de transición (Potrero La Ceiba):
 *   114 10:12 / 115 10:13 / 207 10:05.     Control Quebrada Seca (manual, Darío):
 *   207 13:50 (then leaves the route) / 114 14:02 / 115 14:03.
 *   Camino de los Pinos (forest road, starts a few minutes' walk past Quebrada
 *   Seca, next to Fabiola's El Roble post): Bruno leaves the route there ~13:52
 *   and meets Rocío's vehicle 13:52–14:05 (changes into the dry spare shoes and
 *   takes food/water); V9 photo at 13:58; the service-zone barrier logs her out
 *   13:41 / in 14:14 (about 9 min each way). Julieta and Renata read at Quebrada
 *   Seca 14:02 / 14:03; Renata hands her vest to Julieta right after (~14:06) and
 *   leaves the route by the forest road (~14:08, Fabiola sees her). Renata reaches
 *   Bruno ~14:10, when Rocío is already gone; they argue ~12 min; the fall is
 *   ~14:22 (so Rocío's 14:14 return is before it). Bruno half-carries her to the
 *   refugio (~8 min), takes the phone and beacon, leaves them switched off on a
 *   rock and is back on the forest road ~14:34, where Fabiola sees him leave,
 *   agitated, alone. From there to the arch he needs ~34 min (the field needs ~42
 *   from Quebrada Seca, ~38 from El Roble; Bruno is rested, fed and fast) and the
 *   arch reads him at 15:08: 78 min since Quebrada Seca vs ~42 for the field.
 *   Arco de la Garganta (automatic, no staff): 114 and 115 at 14:44 (Julieta,
 *   wearing 114, with her own vest 115 in her pack: ~41 min from Quebrada Seca,
 *   in line with the field), 207 at 15:08. Manuela's metadata: near the route at
 *   13:58 (V9) and, from 14:09, shooting at Puente Viejo (reached by service
 *   road in ~11 min; it used to say 14:21, which no longer covers the
 *   encounter), i.e. far from the forest road from before Renata arrives until
 *   after Bruno leaves. Puente Viejo
 *   (control posterior): 114/115 15:36, 207 16:00. Meta: 114 17:41, 115 17:42,
 *   207 18:02.
 *   V10 (clean shoes at Puente Viejo): the day's mud is on the Transición →
 *   Quebrada Seca stretch (stream bed, wet all day) and sticks to every other
 *   runner's shoes; after Quebrada Seca the canyon is dry rock. Bruno's spare dry
 *   shoes, put on at the forest road at ~13:55 (after that mud, before the dry
 *   canyon), therefore stay clean until Puente Viejo, while the rest of the field
 *   (who kept their muddy pair) show it. The dry shoes explain V10 entirely and
 *   only because the change happened off the route, with Rocío.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 5-field form and point
 *    scoring (30/25/25/20 and the "treated the chip as proof of arrival"
 *    penalty) are NOT implemented; the extra elements are asked for inside the
 *    free-text fields (see the T+70 unlock text) and explained in solucion.md
 *    and in each exoneration.
 *  - Questions: 5 per person, plus 3 for those with confrontations (8), opened
 *    by `interrogation.stages` according to the evidence delivered (the doc's
 *    "5 + 2 confrontations" is folded into that engine rule).
 *  - V4 (timing table) is two distinct files: v4a (partial, only Salida and
 *    Transición, T+12, no evidence code) and v4 (complete, six controls, T+40,
 *    code V4). Only the complete one counts as evidence.
 *  - V3 shows the rival by FIRST NAME only ("Bruno"), never by full name, as the
 *    design asks. V13 never names him.
 *  - V13: the doc says "mañana voy a comprobar algo" but also "días antes de la
 *    carrera". The message is now sent on a Tuesday and says "este sábado" (race
 *    day), so the two statements agree.
 *  - The hook was "Ella nunca llegó a ninguno" (control). It is false: Darío
 *    scanned both Cóndor Trail runners at Quebrada Seca. Now "su chip pasó por
 *    cada control, a tiempo; ella no está en la meta".
 *  - The doc places the vest request "esa mañana" in the transition zone but has
 *    Renata registering at Quebrada Seca and being seen by Darío / Fabiola. The
 *    request is made in the transition zone; the physical swap happens right
 *    after Quebrada Seca, where both pass together each with her own vest. That
 *    keeps the 114 read there honest. Julieta carries her own vest (115) in her
 *    pack, so 115 also reads at the arch (14:44, same time as 114).
 *  - Darío gets one extra, harmless fact: he scanned both Cóndor Trail runners
 *    together at Quebrada Seca. It contradicts Julieta's lie without solving
 *    anything and only comes out with a good question.
 *  - Rocío's alibi needed a source ("horario de acceso de su vehículo a la zona
 *    de servicio" was never scheduled). Esteban can read the service-zone
 *    barrier log (out 13:41, in 14:14, before the fall); Fabiola saw the grey
 *    utility vehicle leave first. Rocío also left before Renata arrived: the
 *    doc says Renata "sorprendió" them both, which clashed with "se retiró antes
 *    de la confrontación"; now Renata finds Bruno still using the supplies after
 *    Rocío had left.
 *  - "Equipo de recambio" includes dry shoes, which explains V10 (clean shoes).
 *  - V12 (phone and beacon) was described on a rock beside a forest path while
 *    the text says Bruno retained them. Resolved: he took them off Renata and
 *    left them switched off on a rock beside the path to the refugio so as not
 *    to carry them across the finish line.
 *  - V9 reaches the table through the sports portal that published it (credit
 *    "M. Ríos"), which is also what Manuela's confrontation hinges on; the doc
 *    gave her confrontation no evidence. Darío's confrontation likewise hinges
 *    on dorsal 163 passing after the Quebrada Seca cut-off in the complete V4.
 *  - V1 (official map) is route-only; the forest road appears only in V2.
 *  - The doc's "testimonio ampliado de Fabiola" and the three fail-safes were
 *    unscheduled: the written statement is part of the T+55 giro, and the fail
 *    -safes (technical-limitation summary and crossed alibis of Esteban, Darío
 *    and Manuela) go in a new cierre.md at T+63.
 *  - Private clues (T+25): V2+V3 travel together (found among Renata's things) in
 *    one random_player event, V13 in another. Unlike the fragments of other
 *    cases they are complete pieces, so they carry evidence codes.
 *  - The refugio is not reached by the search team at T+55: the photo comes
 *    from the forest brigade's archive and the team is still on its way, so the
 *    table keeps the final accusation to find her.
 *  - Bruno's confrontation 1 needs Fabiola's testimony; it is now the code DF
 *    (her written declaration, in the T+55 giro), so Fabiola's key facts, Bruno's
 *    confrontation 1 and Rocío's confrontation 2 all wait for DF; Bruno's
 *    confrontation 2 needs V12 (T+55).
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()). There is
 * NO audio in the timeline: A1 is only Bruno's confession (confession_script),
 * generated per game. `audio` stays in mechanics like the other cases.
 */
return [

    'name' => 'Punto de Control',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Raid Sierra Alta',
    'authority' => 'Comité de Investigación del Raid Sierra Alta',

    'victim' => [
        'name' => 'Renata Campos',
        'label' => 'Desaparecida',
        'photo' => 'renata-campos.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 5 preguntas + 2 de confrontación por
        // sospechoso (1 para Esteban, Darío y Manuela). The engine only supports
        // one flat number per case, same precedent as the other cases.
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
        'tagline' => 'Su chip pasó por cada control, a tiempo. Ella no está en la meta.',

        'description' => <<<'TXT'
            Raid Sierra Alta: una carrera de aventura por equipos en terreno de
            montaña, clasificatoria para la selección nacional. Cuando termina,
            una corredora no aparece. Su teléfono está apagado y nadie la vio
            llegar, pero el sistema de cronometraje dice que su chip pasó por
            cada control, hasta la meta.

            Tu equipo (4 a 7 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, dos testigos y
            al propio sistema de cronometraje, durante 70 minutos, cruzando
            tablas de tiempos, fotografías de control y testimonios.

            No todo lo que se oculta en una carrera tiene que ver con la
            desaparición. Separar los secretos de cada uno es lo que lleva
            hasta ella.
            TXT,

        // Archivo esperado en public/immersion/punto-de-control/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/punto-de-control).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Su chip pasó por cada control, a tiempo. Ella no está en la meta.',
            'bullets' => [
                '4 a 7 investigadores cruzando tablas de tiempos, fotos de control y testimonios',
                'Seis sospechosos, dos testigos y el sistema de cronometraje a quienes interrogar',
                '70 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 70,
        'min_players' => 4,
        'max_players' => 7,

        'price_amount' => 59900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/punto-de-control/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 19,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Nico, Fabiola) and the secondary source (ChipTrack
    | Sierra) are modeled exactly like a suspect entry. Renata, the missing
    | runner, is the "victim" and is not interrogable.
    |
    */

    'suspects' => [
        'bruno-salcedo' => [
            'name' => 'Bruno Salcedo',
            'role' => 'sospechoso',
            'file' => 'suspects/bruno-salcedo.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['DF', 'V12'], 'confrontation' => true],
            ]],
            'photo' => 'bruno-salcedo.png',
            'connection' => 'Atleta de Team Altair Endurance; rival directo de Renata por el último cupo',
            'motive' => 'Evitar ser descalificado y perder el cupo a la selección nacional al ser descubierto recibiendo asistencia externa ilegal',
            'alibi' => 'Dice haber corrido el tramo del cañón a su ritmo; sin coartada sólida para el camino forestal',
            'public' => [
                'age' => '31 años',
                'profile' => 'Atleta de Team Altair Endurance (dorsal 207). Seguro de sí mismo en público, muy competitivo.',
                'alibi' => 'Dice haber hecho el tramo del cañón a su ritmo y haber cruzado los controles con normalidad.',
                'facts' => [
                    'Es el rival directo de Renata: ambos se disputan el último cupo a la selección nacional.',
                    'Su entrenadora, Rocío Salgado, lo acompaña en las zonas de servicio de cada carrera.',
                ],
            ],
        ],
        'julieta-ferreira' => [
            'name' => 'Julieta Ferreira',
            'role' => 'sospechosa',
            'file' => 'suspects/julieta-ferreira.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V6'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V6', 'V13'], 'confrontation' => true],
            ]],
            'photo' => 'julieta-ferreira.png',
            'connection' => 'Compañera de equipo y amiga de Renata en Team Cóndor Trail',
            'motive' => 'Ninguno respecto a la desaparición; llevó el chaleco y el chip de Renata en el cañón y lo ocultó por miedo a una descalificación',
            'alibi' => 'Cruzó el cañón y los controles posteriores con normalidad, llevando el chaleco de Renata',
            'public' => [
                'age' => '26 años',
                'profile' => 'Compañera de equipo y amiga cercana de Renata en Team Cóndor Trail (dorsal 115). Afectuosa y visiblemente preocupada.',
                'alibi' => 'Dice no haber vuelto a ver a Renata después de la zona de transición.',
                'facts' => [
                    'Corrió con Renata durante buena parte de la carrera.',
                    'Pudo ser una de las últimas personas en tener contacto con ella.',
                ],
            ],
        ],
        'esteban-nava' => [
            'name' => 'Esteban Nava',
            'role' => 'sospechoso',
            'file' => 'suspects/esteban-nava.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V7']],
                ['questions' => 1, 'after' => ['V9', 'DF']],
                ['questions' => 3, 'after' => ['V7'], 'confrontation' => true],
            ]],
            'photo' => 'esteban-nava.png',
            'connection' => 'Director de la organización del Raid Sierra Alta',
            'motive' => 'Ninguno respecto a la desaparición; ya sospechaba de los tiempos de Bruno y calló por miedo a un escándalo con los patrocinadores',
            'alibi' => 'En la carpa de cronometraje toda la tarde, con dos técnicos y con validaciones hechas desde la consola',
            'public' => [
                'age' => '48 años',
                'profile' => 'Director de la organización del Raid Sierra Alta. Institucional y calmado, muy atento a la reputación del evento.',
                'alibi' => 'Dice haber permanecido en la carpa de cronometraje durante la tarde.',
                'facts' => [
                    'Supervisa el sistema de cronometraje y tiene acceso a todos sus datos.',
                    'Los patrocinadores del evento dependen de que la carrera salga bien.',
                ],
            ],
        ],
        'rocio-salgado' => [
            'name' => 'Rocío Salgado',
            'role' => 'sospechosa',
            'file' => 'suspects/rocio-salgado.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V8', 'V9', 'DF'], 'confrontation' => true],
            ]],
            'photo' => 'rocio-salgado.png',
            'connection' => 'Entrenadora y mánager de Bruno',
            'motive' => 'Ninguno respecto a la desaparición; facilitó asistencia externa ilegal a Bruno',
            'alibi' => 'Se marchó del camino forestal en su vehículo antes de que Renata llegara; volvió a la zona de servicio',
            'public' => [
                'age' => '44 años',
                'profile' => 'Entrenadora y mánager de Bruno. Profesional y protectora de su atleta; reservada sobre sus movimientos.',
                'alibi' => 'Dice no haber estado cerca del camino forestal esa tarde.',
                'facts' => [
                    'Si Bruno clasifica a la selección, su trabajo gana prestigio y contratos.',
                    'Dispone de un vehículo de apoyo acreditado para las zonas de servicio.',
                ],
            ],
        ],
        'dario-pinto' => [
            'name' => 'Darío Pinto',
            'role' => 'sospechoso',
            'file' => 'suspects/dario-pinto.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V4'], 'confrontation' => true],
            ]],
            'photo' => 'dario-pinto.png',
            'connection' => 'Voluntario del control manual de Quebrada Seca, previo al cañón',
            'motive' => 'Ninguno respecto a la desaparición; aceptó dinero para no reportar el retraso de otro corredor',
            'alibi' => 'En su puesto toda la tarde; su lector portátil registró lecturas sin interrupción',
            'public' => [
                'age' => '29 años',
                'profile' => 'Voluntario del control manual de Quebrada Seca, el último punto con personal antes del cañón. Técnico y algo seco al hablar del sistema.',
                'alibi' => 'Dice haber estado en su puesto durante toda la tarde.',
                'facts' => [
                    'Registra a los corredores con un lector portátil de chips.',
                    'Su puesto queda justo antes del tramo del cañón.',
                ],
            ],
        ],
        'manuela-rios' => [
            'name' => 'Manuela Ríos',
            'role' => 'sospechosa',
            'file' => 'suspects/manuela-rios.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V9'], 'confrontation' => true],
            ]],
            'photo' => 'manuela-rios.png',
            'connection' => 'Fotógrafa oficial de la carrera',
            'motive' => 'Ninguno respecto a la desaparición; vendió fotos a un portal deportivo sin autorización de la organización',
            'alibi' => 'En movimiento entre puntos fotográficos, confirmada por los metadatos de su cámara',
            'public' => [
                'age' => '37 años',
                'profile' => 'Fotógrafa oficial de la carrera. Profesional, algo nerviosa al hablar de sus archivos.',
                'alibi' => 'Dice haberse movido entre puntos fotográficos durante toda la tarde.',
                'facts' => [
                    'Tiene acceso a zonas restringidas de la ruta para hacer su trabajo.',
                    'Su cámara guarda la hora de cada fotografía.',
                ],
            ],
        ],
        'nico-fuentes' => [
            'name' => 'Nico Fuentes',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/nico-fuentes.md',
            'photo' => 'nico-fuentes.png',
            'connection' => 'Compañero de club de Renata (otro equipo dentro de Cóndor Trail)',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '25 años',
                'profile' => 'Compañero de club de Renata: corre en otro equipo dentro de Cóndor Trail. Joven y directo.',
                'facts' => [
                    'Conoce de cerca los entrenamientos y las preocupaciones de Renata.',
                    'Habló con ella en los días previos a la carrera.',
                ],
            ],
            'accusable' => false,
        ],
        'fabiola-reyes' => [
            'name' => 'Fabiola Reyes',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/fabiola-reyes.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DF']],
            ]],
            'photo' => 'fabiola-reyes.png',
            'connection' => 'Encargada del puesto de apoyo El Roble, cerca de un camino forestal',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '52 años',
                'profile' => 'Encargada del puesto de apoyo El Roble, cerca del arranque de un camino forestal secundario. Cálida y observadora.',
                'facts' => [
                    'Desde su puesto ve el sendero marcado y el comienzo del camino forestal.',
                    'Atendió a corredores durante toda la tarde.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-chiptrack-sierra' => [
            'name' => 'Sistema de cronometraje ChipTrack Sierra',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-chiptrack-sierra.md',
            'interrogation' => ['stages' => [
                ['questions' => 2],
                ['questions' => 2, 'after' => ['V4']],
                ['questions' => 1, 'after' => ['V6']],
            ]],
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as the other systems) — a shot of
            // the timing console works as its "portrait".
            'photo' => 'sistema-chiptrack-sierra.png',
            'connection' => 'Lecturas de chips y dorsales en los controles de la carrera',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente técnica de la carrera: registra la hora y el número de dorsal de cada lectura en los controles.',
                'facts' => [
                    'Anota la hora exacta de cada lectura en cada control.',
                    'Es una fuente técnica: responde con datos, no opina ni interpreta.',
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
        'culprit_slug' => 'bruno-salcedo',

        'headline' => 'El chip de Renata llegó a la meta, pero Renata no: Julieta lo llevó en el cañón mientras Renata se desviaba para comprobar un fraude, y Bruno, al ser descubierto, la retuvo en un refugio forestal.',

        'motive' => 'Evitar ser descalificado y perder el último cupo a la selección nacional al ser descubierto recibiendo asistencia externa ilegal de su entrenadora; el pánico se agravó cuando Renata se lesionó el tobillo en el forcejeo.',

        'method' => 'Renata lo sorprendió en el Camino de los Pinos usando el equipo de recambio que le había llevado Rocío. En el forcejeo, Renata cayó sobre las rocas y se lesionó el tobillo; Bruno la llevó a un refugio forestal, le quitó el teléfono y la baliza de emergencia y siguió corriendo. El chip de Renata completó la ruta porque Julieta llevaba su chaleco en el cañón, a pedido de la propia Renata, que se había desviado a propósito, con un plan anterior a la carrera.',

        'key_evidence' => [
            'La foto del Arco de la Garganta: dorsal 114, pero el rostro es el de Julieta; ChipTrack registra dispositivos, no personas.',
            'La declaración de Fabiola: vio a Renata salirse de la ruta sola y, más tarde, a Bruno salir agitado del camino forestal sin ella.',
            'El hueco de 78 minutos del dorsal 207 entre Quebrada Seca y el arco, frente a unos 42 del resto del campo.',
            'La foto de Manuela: un utilitario gris junto al camino forestal, donde no podía haber ninguno; y el calzado limpio de Bruno en Puente Viejo.',
            'El mapa marcado, la bitácora y el mensaje a Nico: el desvío de Renata fue planeado antes de la carrera.',
            'El teléfono y la baliza de Renata, apagados sobre una roca: no pudo pedir ayuda.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'julieta-ferreira' => 'Sí llevó el chaleco y el chip de Renata en el cañón y lo ocultó por miedo a que ambas fueran descalificadas, pero lo hizo a pedido de Renata, sin conocer el motivo real, y su acto es anterior e independiente de la confrontación. Su preocupación al saberse la desaparición es genuina y su versión coincide con la de Nico.',

            'esteban-nava' => 'Sí sospechaba de los tiempos de Bruno desde la edición anterior y calló por miedo a un escándalo con los patrocinadores, una falta real pero ajena a la desaparición. Estuvo en la carpa de cronometraje toda la tarde, con dos técnicos y con validaciones hechas desde la consola.',

            'rocio-salgado' => 'Sí entregó asistencia ilegal a Bruno (agua, comida y equipo de recambio, incluidas unas zapatillas secas), pero se marchó del camino forestal antes de que Renata llegara: Fabiola vio salir el utilitario antes de ver a Renata, y la barrera de la zona de servicio de Quebrada Seca registra su vuelta a las 14:14, antes de la caída. Su motivo se satisface sin que Renata desaparezca.',

            'dario-pinto' => 'Sí aceptó dinero para no reportar el retraso de otro corredor, el dorsal 163, que no tiene relación con Bruno ni con Renata. Estuvo en su puesto de Quebrada Seca toda la tarde, antes de la zona del desvío, y su lector registró lecturas sin interrupción.',

            'manuela-rios' => 'Sí vendió fotos a un portal deportivo sin autorización, y una de ellas captó sin saberlo el utilitario de Rocío; esa foto ayudó a resolver el caso. Sus metadatos la sitúan junto a la ruta cerca de Quebrada Seca a las 13:58 y, desde las 14:09, lejos del camino forestal, fotografiando en Puente Viejo.',

            'nico-fuentes' => 'Es un testigo, no un sospechoso: solo confirmó que Renata sospechaba de los tiempos de Bruno y que le avisó de que iba a comprobar algo en el cañón. No estuvo en la zona, no sabía del chaleco ni tenía motivo alguno.',

            'fabiola-reyes' => 'Es una testigo, no una sospechosa: desde su puesto vio a Renata salirse de la ruta, a Bruno salir agitado del camino forestal sin ella y, antes, un utilitario gris alejándose. No sabe qué pasó entre esos momentos ni tenía motivo alguno.',

            'sistema-chiptrack-sierra' => 'Es un sistema automatizado de cronometraje, no una persona: no puede haber intervenido. Registró con exactitud el paso de dispositivos por cada control y nunca falló ni fue manipulado; su límite es que registra chips, no a quien los lleva.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Fenrir',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Rocío me traía agua y comida en ese tramo desde hace dos carreras. Era la
            única forma de aguantar el ritmo que necesitaba para el cupo. Cuando Renata
            apareció ahí, supe que se había terminado todo.

            Traté de que no se fuera así, sin hablarlo. Ella se resbaló, se torció el
            tobillo, y yo no supe qué hacer. Pensé que si la dejaba en el refugio y
            terminaba la carrera, podía volver después y arreglarlo de alguna forma.

            Está bien. Está en el refugio, cerca del camino forestal. No la lastimé a
            propósito. Pero no supe cómo parar esto sin perderlo todo.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué le ocurrió a Renata y cómo?',
            'hint' => 'Cuenta qué pasó cuando se encontraron, dónde está ahora y por qué no pudo pedir ayuda.',
            'points' => 12,
            'guidance' => 'Lo esencial: lo sorprendió recibiendo asistencia ilegal, en el forcejeo ella se lesionó el tobillo y él la dejó en un refugio forestal sin teléfono ni baliza. No exigir aquí lo del chip: se pregunta aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué la retuvieron?',
            'hint' => 'Qué había visto Renata y qué perdía el responsable si ella lo contaba.',
            'points' => 13,
        ],
        'questions' => [
            [
                'key' => 'chip_ruta_completa',
                'label' => '¿Por qué el chip de Renata registró la ruta completa?',
                'hint' => 'Explica quién o qué cruzó realmente cada control y qué registra el sistema.',
                'answer' => 'Porque Julieta Ferreira, su compañera de equipo, llevaba el chaleco y el chip del dorsal 114 durante el tramo del cañón, a pedido de la propia Renata. ChipTrack registra dispositivos, no personas: el chip llegó a la meta, Renata no.',
                'points' => 25,
                'guidance' => 'Cero si trata el registro completo del chip como prueba de que Renata siguió en carrera o llegó a la meta. Puntos completos con: Julieta llevaba el chaleco/chip a pedido de Renata.',
            ],
            [
                'key' => 'desvio_de_renata',
                'label' => '¿Qué desvío había planeado Renata y para qué?',
                'hint' => 'Qué quería comprobar, dónde y desde cuándo lo sospechaba.',
                'answer' => 'Quería confirmar, en el camino forestal junto al cañón (Camino de los Pinos), que Bruno recibía asistencia externa ilegal de su entrenadora Rocío Salgado, tras notar en una carrera anterior tiempos de recuperación imposibles. Era un plan anterior a la carrera: lo dejó marcado en su mapa y en su bitácora y se lo avisó a Nico.',
                'points' => 20,
                'guidance' => 'Basta con la sospecha de asistencia ilegal a Bruno y que el desvío fue planeado; no exigir el nombre exacto del camino.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+25/T+40/T+55/T+70 delivery
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases). The extra T+63 cierre carries the doc's three fail-safes.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Punto de Control — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V5'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — sospechosos, testigos y la tabla parcial',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            // Deliberately no evidence_codes: v4a is the PARTIAL table (only the
            // first two reads). The complete V4 arrives at T+40.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 25,
            'title' => 'Pista privada — el mapa y la bitácora de Renata',
            'source_file' => 'pista-privada-mapa-bitacora.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V2', 'V3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 25,
            'title' => 'Pista privada — un mensaje de Renata',
            'source_file' => 'pista-privada-mensaje.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 40,
            'title' => 'Complicación — lo que el chip sí registra, y lo que no',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4', 'V6', 'V7'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Giro — lo que pasó fuera de la ruta',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DF = Fabiola's written (expanded) declaration, the body of giro.md.
            // It has no image, so it gets its own code (same device as TP and
            // DECL-PAOLA in other cases).
            'evidence_codes' => ['DF', 'V8', 'V9', 'V10', 'V11', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 63,
            'title' => 'Cierre — coartadas cruzadas y resumen técnico',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 70,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/punto-de-control/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-mapa-oficial-carrera.png', 'caption' => 'Mapa oficial del Raid Sierra Alta, con sus seis controles'],
            ['file' => 'v5-salida-ambiente.png', 'caption' => 'La salida, esa mañana'],
        ],
        'expansion.md' => [
            ['file' => 'v4a-cronometraje-parcial.png', 'caption' => 'Tabla de cronometraje (parcial): Salida y Zona de transición'],
        ],
        'pista-privada-mapa-bitacora.md' => [
            ['file' => 'v2-mapa-marcado-renata.png', 'caption' => 'El mapa personal de Renata, con anotaciones a mano'],
            ['file' => 'v3-bitacora-renata.png', 'caption' => 'Una página de la bitácora de entrenamiento de Renata'],
        ],
        'pista-privada-mensaje.md' => [
            ['file' => 'v13-mensaje-renata-nico.png', 'caption' => 'Captura de la conversación entre Renata y Nico'],
        ],
        'complicacion.md' => [
            ['file' => 'v7-informe-chiptrack.png', 'caption' => 'Informe de ChipTrack Sierra, con su nota técnica'],
            ['file' => 'v6-arco-garganta.png', 'caption' => 'Cámara fija del Arco de la Garganta'],
            ['file' => 'v4-cronometraje-completo.png', 'caption' => 'Tabla de cronometraje completa'],
        ],
        'giro.md' => [
            ['file' => 'v8-detalle-cronometraje.png', 'caption' => 'Detalle de cronometraje del dorsal 207'],
            ['file' => 'v9-foto-vehiculo-camino.png', 'caption' => 'Un tramo del camino forestal, fotografía publicada por un portal deportivo'],
            ['file' => 'v10-calzado-control-posterior.png', 'caption' => 'Calzado de dos corredores en el control posterior al cañón'],
            ['file' => 'v11-refugio-forestal.png', 'caption' => 'Un refugio forestal cercano, del archivo de la brigada forestal'],
            ['file' => 'v12-telefono-baliza.png', 'caption' => 'Teléfono y baliza de emergencia hallados junto al sendero'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v6-arco-garganta.png', 'caption' => 'El dorsal es el de Renata; el rostro es el de Julieta'],
            ['file' => 'v2-mapa-marcado-renata.png', 'caption' => 'El desvío de Renata, planeado antes de la carrera'],
            ['file' => 'v8-detalle-cronometraje.png', 'caption' => 'El hueco de Bruno entre Quebrada Seca y el arco'],
            ['file' => 'v12-telefono-baliza.png', 'caption' => 'Renata no pudo pedir ayuda'],
        ],
    ],
];
