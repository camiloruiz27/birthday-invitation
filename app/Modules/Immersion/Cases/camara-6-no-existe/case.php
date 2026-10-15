<?php

/**
 * Case manifest: "Cámara 6 no existe"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "false trail" case. The table spends the first
 * half hunting for a secret sixth camera, a staged image or a hacked system.
 * None of that is true: "Cámara 6" (CAM-06) is a real, documented unit that
 * was retired from the official network in 2022 as a spare and quietly
 * reinstalled by Lucas Figueroa (anti-poaching coordinator). The image and its
 * label are both authentic. The disappearance itself is Gerardo Oyarzo's
 * (director of the reserve), who holds Diego — alive, unhurt — in a remote
 * maintenance cabin to stop him reporting the bribes he takes from Raúl Mena.
 * The wildlife trafficking is real but is only the context, not the cause.
 * Every system (database, radio, cameras) is exact: nothing fails for
 * narrative convenience.
 *
 * Reference clock (the design doc has NO hours; fixed here so all files agree,
 * do not change one without the others). Date of the disappearance: 14/05/2026.
 *  - 08:00  Patricia at the maintenance office until 12:00 (access records).
 *  - 08:20  Tomás arrives (shift starts 08:00; he claims "mi hora habitual").
 *           From then on he and Camila are together in the field lab until
 *           past noon, which covers both of them for 08:30-10:00.
 *  - 08:30  Armando sees Gerardo take the dirección's pickup up the Pista del
 *           Alto toward the high sector.
 *  - 08:35  Sofía sees Diego leave the Puesto Central on the Sendero de la
 *           Quebrada (control point 3 = Mirador del Coihue, ~10:00).
 *  - 08:45  Sofía leaves with Lucas on his round at Laguna Chica (south
 *           sector) until ~10:30: both are alibied.
 *  - 08:52  CAM-06 (second bridge of the Sendero de la Quebrada) captures
 *           Diego. Gerardo intercepts him just after, near the Cruce de la
 *           Quebrada, and takes him to the Cabaña del Alto.
 *  - 10:00 / 10:05  Armando's radio calls (A1) go unanswered; search at ~10:30.
 *  - 08:00-12:30  Raúl at a notary's office in the city.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon/method. The doc's five-component
 *    scoring (responsible, method+motive, full Cámara 6 history, trafficking
 *    separated from the disappearance, penalty for "secret camera/hack") is NOT
 *    implemented; the extra components are asked for inside the free-text motive
 *    (see the T+75 unlock text) and explained in solucion.md.
 *  - The engine has one flat question budget per suspect, so the doc's
 *    "5 questions + 2 confrontations" is 5 here (same precedent as the other
 *    cases). Confrontations are gated by evidence DELIVERED, not by time or a
 *    separate pool. Camila, Tomás and Patricia have no evidence in the design
 *    to gate on, so their md files gate on V3+V13 (delivered together at T+39,
 *    exactly when the doc unlocks confrontations) plus a concrete push.
 *  - V15 (Diego's desk) was described but never scheduled in the design.
 *    Delivered at T+13 with the official cameras, since it was photographed when
 *    his absence was noticed.
 *  - Gerardo's confrontation 2 requires V12 (Diego's personal object) and V14
 *    but the doc delivers V12 at T+65 and V14 at T+52, and unlocks that
 *    confrontation at T+52. V12 moved to T+52 (giro). V11 (cabin photo) stays
 *    at T+65. V12 is a patrol radio found ~200 m from the Cabaña del Alto on
 *    the Pista del Alto (the spare radio on V15's desk is a different unit).
 *  - V5-V7 were ONE prompt "applicable to cameras 1-5". Turned into three
 *    distinct images from three distinct official locations: V5 = CAM-02
 *    (Bebedero del Arroyo, day), V6 = CAM-04 (Cruce Los Maitenes, night/IR),
 *    V7 = CAM-05 (Laguna Chica, day). CAM-01 and CAM-03 only appear as rows in
 *    V1/V2.
 *  - V3, V9 and V10 each have a partial private version and a complete later
 *    one (separate files: v3a/v3, v9a/v9, v10a/v10). The design only
 *    scheduled V3 partial and V9 partial (T+26); the partial V10 was never
 *    scheduled and the complete V9 never delivered. Now: v3a, v9a and v10a as
 *    three random_player fragments at T+26 (no evidence_codes, so fragments
 *    never satisfy a confrontation gate); complete V3 + V9 + V13 at T+39;
 *    complete V10 at T+52.
 *  - The design says V3 shows the "ubicación real de reasignación" (N3) but
 *    also that it does not reveal where the unit is. Resolved: the sheet says
 *    "sin reasignación registrada"; the real spot (second bridge of the Sendero
 *    de la Quebrada) comes from Lucas's complete notes (V9) and can be matched
 *    against V1 (map with two bridges) and V4 (a footbridge in frame).
 *  - A1 (patrol radio, Armando) is the Gancho's audio_email. A2 (Gerardo's
 *    confession) is NOT in the timeline: it is solution.confession_script
 *    (generated per game); the confession script keeps the design's lines and
 *    adds the threat and the Sendero ambush, all of which the evidence already
 *    supports.
 *  - Where did V4 come from if Lucas hid the camera? Unit transmits by its own
 *    modem to the sensors server by ID, even though it is "repuesto" and not in
 *    the active list (sistema md; Camila's md: she saw a "CAM-06" folder and
 *    ignored it; Lucas's md and V9 explain it: he left the unit on its original
 *    ID and did not register it in the active network). Added; the design
 *    never explained it.
 *  - Circular alibis (Camila <-> Tomás) fixed by the clock above: together from
 *    08:20, covering Gerardo's window. Tomás's lateness is why he did not see
 *    Diego leave.
 *  - Armando's sighting was "that same morning" without a time; fixed at 08:30
 *    (before Diego's 08:52 image: Gerardo waited for him). Gerardo's declared
 *    alibi ("oficina central") was not in the design; it is what Armando
 *    contradicts.
 *  - V12 + V11 plausibility: the table gets photos of the cabin and of Diego's
 *    radio near it, but the cabin is padlocked and the search team may not force
 *    it until the file points at a responsible person (cierre.md). Otherwise a
 *    real search would find Diego before the accusation. V11 is a DRONE photo
 *    (the team never approaches the door, so nobody "fails to knock").
 *  - The unlock at T+75 asks for the extra components in the "motivo" field.
 *  - The UI stamps the victim as "Víctima" (InterrogationIndex). Diego is alive;
 *    the label does not reveal it, and the epilogue/confession do.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'Cámara 6 no existe',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Reserva Quebrada Honda',
    'authority' => 'Auditoría Regional de la Autoridad Nacional de Parques',

    'victim' => [
        'name' => 'Diego Valderrama',
        'label' => 'Desaparecido',
        'photo' => 'diego-valderrama.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 5 preguntas + 2 de confrontación por
        // sospechoso. The engine only supports one flat number per case, same
        // precedent as the other cases.
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
        'tagline' => 'La red oficial tiene cinco cámaras. La imagen dice que vino de la sexta.',

        'description' => <<<'TXT'
            En la Reserva Natural Quebrada Honda desaparece Diego Valderrama,
            guardaparques. La última imagen que existe de él viene de una
            cámara trampa etiquetada «Cámara 6». Pero la red oficial de la
            reserva solo tiene cinco.

            Tu equipo (4 a 7 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, dos testigos y
            a la base de datos de sensores durante 75 minutos, cruzando
            fichas de mantenimiento, registros de equipo, imágenes de cámaras
            trampa y notas de campo.

            Antes de saber qué le pasó a Diego conviene saber qué es realmente
            esa cámara. Y en la reserva hay más de una irregularidad: no todas
            tienen la misma causa.
            TXT,

        // Archivo esperado en public/immersion/camara-6-no-existe/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/camara-6-no-existe).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'La última imagen de un guardaparques desaparecido viene de una cámara que, según todos los registros, no existe.',
            'bullets' => [
                '4 a 7 investigadores cruzando fichas, cámaras trampa y notas de campo',
                'Seis sospechosos, dos testigos y la base de sensores a quienes interrogar',
                '75 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 75,
        'min_players' => 3,
        'max_players' => 7,

        'price_amount' => 64900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/camara-6-no-existe/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Sofía, Armando) and the secondary source (the sensors
    | and maintenance database) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'gerardo-oyarzo' => [
            'name' => 'Gerardo Oyarzo',
            'role' => 'sospechoso',
            'file' => 'suspects/gerardo-oyarzo.md',
            'photo' => 'gerardo-oyarzo.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V4', 'V3', 'V13'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V12', 'V14'], 'confrontation' => true],
            ]],
            'connection' => 'Director de la reserva; superior directo de Diego',
            'motive' => 'Impedir que Diego reportara a la autoridad nacional los pagos que recibía de Raúl Mena a cambio de permisividad con el tráfico de fauna',
            'alibi' => 'Declara haber estado en la oficina central; nadie lo confirma y Armando lo vio salir hacia el sector alto a las 08:30',
            'public' => [
                'age' => '55 años',
                'profile' => 'Director de la reserva y superior directo de Diego. Autoritario y sereno; conoce cada sendero y cada rutina de patrulla.',
                'alibi' => 'Dice haber pasado la mañana en la oficina central con asuntos administrativos.',
                'facts' => [
                    'Conoce las rutas habituales de patrulla de todos los guardaparques.',
                    'Es la máxima autoridad administrativa de la reserva.',
                ],
            ],
        ],
        'lucas-figueroa' => [
            'name' => 'Lucas Figueroa',
            'role' => 'sospechoso',
            'file' => 'suspects/lucas-figueroa.md',
            'photo' => 'lucas-figueroa.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V3', 'V13'], 'confrontation' => true],
            ]],
            'connection' => 'Coordinador antifurtivismo; colega cercano de Diego',
            'motive' => 'Ninguno respecto a la desaparición; instaló la Cámara 6 sin autorización formal por desconfianza hacia la gestión (acto distinto)',
            'alibi' => 'De ronda en la Laguna Chica con Sofía Ibarra de 08:45 a cerca de las 10:30',
            'public' => [
                'age' => '42 años',
                'profile' => 'Coordinador antifurtivismo: vigila el territorio contra la caza y el tráfico de fauna. Técnico, reservado y muy protector de su zona.',
                'alibi' => 'Dice haber estado de ronda en otra zona de la reserva esa mañana.',
                'facts' => [
                    'Tiene acceso técnico a los equipos y conoce a fondo el territorio.',
                    'Compartía con Diego la preocupación por el tráfico de fauna.',
                ],
            ],
        ],
        'camila-reyes' => [
            'name' => 'Camila Reyes',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-reyes.md',
            'photo' => 'camila-reyes.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V3']],
                ['questions' => 3, 'after' => ['V3', 'V13'], 'confrontation' => true],
            ]],
            'connection' => 'Bióloga de la reserva; colega de trabajo de campo',
            'motive' => 'Ninguno; ajustó ligeramente datos de un estudio de fauna para renovar una beca (sin relación con el caso)',
            'alibi' => 'En el laboratorio de campo; Tomás Bravo la confirma desde las 08:20',
            'public' => [
                'age' => '33 años',
                'profile' => 'Bióloga de la reserva: lleva los estudios de fauna y descarga los datos de las cámaras oficiales. Profesional y precisa.',
                'alibi' => 'Dice haber pasado la mañana en el laboratorio de campo.',
                'facts' => [
                    'Su beca de investigación se renueva este año.',
                    'Conoce las rutas y los datos de fauna de toda la reserva.',
                ],
            ],
        ],
        'tomas-bravo' => [
            'name' => 'Tomás Bravo',
            'role' => 'sospechoso',
            'file' => 'suspects/tomas-bravo.md',
            'photo' => 'tomas-bravo.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V10']],
                ['questions' => 3, 'after' => ['V3', 'V13'], 'confrontation' => true],
            ]],
            'connection' => 'Guardaparques joven; colega de Diego',
            'motive' => 'Ninguno; llegó tarde a su turno y no lo reportó por miedo a una sanción (sin relación con el caso)',
            'alibi' => 'En el laboratorio de campo con Camila desde las 08:20, hora real de su llegada',
            'public' => [
                'age' => '29 años',
                'profile' => 'Guardaparques joven, colega de Diego y de los últimos en hablar con él. Se pone nervioso cuando le preguntan por horarios.',
                'alibi' => 'Dice haber llegado a su turno a la hora de siempre y haber estado en el laboratorio de campo.',
                'facts' => [
                    'Habló con Diego la tarde anterior a su desaparición.',
                    'Es el más joven del equipo de guardaparques.',
                ],
            ],
        ],
        'patricia-nunez' => [
            'name' => 'Patricia Núñez',
            'role' => 'sospechosa',
            'file' => 'suspects/patricia-nunez.md',
            'photo' => 'patricia-nunez.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V3']],
                ['questions' => 3, 'after' => ['V3', 'V13'], 'confrontation' => true],
            ]],
            'connection' => 'Encargada de mantenimiento de equipos',
            'motive' => 'Ninguno; no reportó formalmente el movimiento de un equipo por lealtad a un colega (omisión)',
            'alibi' => 'En la oficina de mantenimiento de 08:00 a 12:00, según los registros de acceso',
            'public' => [
                'age' => '46 años',
                'profile' => 'Encargada de mantenimiento de equipos: gestiona la base de datos de cámaras y su historial. Meticulosa y reservada con sus colegas.',
                'alibi' => 'Dice haber estado en la oficina de mantenimiento durante la mañana.',
                'facts' => [
                    'Conoce el historial de cada cámara de la reserva.',
                    'Es muy leal a sus compañeros de trabajo.',
                ],
            ],
        ],
        'raul-mena' => [
            'name' => 'Raúl Mena',
            'role' => 'sospechoso',
            'file' => 'suspects/raul-mena.md',
            'photo' => 'raul-mena.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V14'], 'confrontation' => true],
            ]],
            'connection' => 'Empresario local; vecino de la reserva',
            'motive' => 'Se beneficia del tráfico de fauna y paga a Gerardo por permisividad; no tiene relación con la retención de Diego',
            'alibi' => 'Reunión con un notario en la ciudad de 08:00 a 12:30, acreditada por la notaría',
            'public' => [
                'age' => '50 años',
                'profile' => 'Empresario local con terrenos colindantes con la reserva. Cordial y de modales cuidados.',
                'alibi' => 'Dice haber estado fuera de la reserva esa mañana, en una reunión de negocios.',
                'facts' => [
                    'Sus terrenos limitan con el borde este de la reserva.',
                    'Tiene intereses comerciales en la zona.',
                ],
            ],
        ],
        'sofia-ibarra' => [
            'name' => 'Sofía Ibarra',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-ibarra.md',
            'photo' => 'sofia-ibarra.png',
            'connection' => 'Bióguarda voluntaria de campo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '35 años',
                'profile' => 'Bióguarda voluntaria de campo. Esa mañana estaba en el puesto central cuando salieron los guardaparques.',
                'facts' => [
                    'Vio salir a Diego hacia su ruta de patrulla.',
                    'Salió esa mañana en una ronda por el sector sur.',
                ],
            ],
            'accusable' => false,
        ],
        'armando-paillao' => [
            'name' => 'Don Armando Paillao',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/armando-paillao.md',
            'photo' => 'armando-paillao.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DA']],
            ]],
            'connection' => 'Jefe de patrullas veterano',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '60 años',
                'profile' => 'Jefe de patrullas veterano: lleva los controles de radio de los guardaparques y conoce los horarios de todas las rondas.',
                'facts' => [
                    'Fue quien notó que Diego no respondía en su punto de control.',
                    'Conoce las rutas y los horarios de todas las patrullas.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-sensores-mantenimiento' => [
            'name' => 'Base de datos de sensores y mantenimiento',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-sensores-mantenimiento.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-control-buque") — a shot
            // of the sensors management screen works as its "portrait".
            'photo' => 'sistema-sensores-mantenimiento.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V3', 'V13']],
            ]],
            'connection' => 'Identificadores, fechas y traslados de equipo de la red de cámaras',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente técnica de la reserva: archiva identificadores, fechas y traslados de las cámaras y otros sensores, sin interpretar nada.',
                'facts' => [
                    'Registra cuándo entra y sale cada equipo del almacén.',
                    'No puede decir quién instaló un equipo ni con qué intención.',
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
        'culprit_slug' => 'gerardo-oyarzo',

        'headline' => '«Cámara 6» sí existe y su imagen es auténtica: es un equipo retirado que Lucas Figueroa reinstaló por su cuenta. Lo decisivo es a quién captó: el director Gerardo Oyarzo interceptó a Diego Valderrama poco después para impedir que lo denunciara por los pagos de Raúl Mena y lo retiene, con vida, en una cabaña remota.',

        'motive' => 'Ocultar los pagos irregulares que recibía de Raúl Mena a cambio de permisividad con el tráfico de fauna, que Diego había descubierto y pensaba reportar a la Autoridad Nacional de Parques.',

        'method' => 'Interceptó a Diego en el Sendero de la Quebrada, poco después de las 08:52, lo amenazó con consecuencias laborales inventadas y con difundir información privada distorsionada, y lo condujo bajo presión a la cabaña de mantenimiento aislada del sector alto, donde lo retiene sin daño físico y sin comunicación.',

        'key_evidence' => [
            'La imagen de la Cámara 6 (08:52) fija a Diego en el Sendero de la Quebrada poco antes de desaparecer.',
            'La ficha de mantenimiento y el registro de movimiento prueban que «CAM-06» es un equipo real: retirado en 2022 como repuesto y sacado del almacén el 11/02/2026; las notas de Lucas explican quién lo reinstaló y dónde.',
            'Las notas de Diego, completas, nombran a Gerardo, y el registro de transferencias prueba los pagos mensuales de Raúl Mena.',
            'Armando vio a Gerardo tomar la Pista del Alto a las 08:30, aunque este dijo haber estado en la oficina y no haber hablado con Diego «en días».',
            'La radio de Diego apareció a unos 200 metros de la cabaña del sector alto.',
            'El tráfico de fauna es real pero no explica la retención: Raúl necesita silencio general, no la detención de nadie; Lucas, Camila, Tomás, Patricia y Raúl tienen coartadas y secretos que no los vinculan con Diego.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'lucas-figueroa' => 'Sí ocultó algo: reparó e instaló por su cuenta la Cámara 6, sin autorización formal y sin actualizar el sistema, porque desconfiaba de que una solicitud oficial se filtrara. Es una irregularidad técnica, no un delito contra Diego: perseguía el mismo objetivo que él, atrapar a los traficantes, y estaba de ronda en la Laguna Chica con Sofía Ibarra desde las 08:45 hasta cerca de las 10:30.',

            'camila-reyes' => 'Sí mintió sobre sus informes: ajustó ligeramente datos de un estudio de fauna para asegurar la renovación de su beca. Es una falta científica sin relación alguna con Diego, y estuvo en el laboratorio de campo con Tomás Bravo desde las 08:20 hasta pasado el mediodía.',

            'tomas-bravo' => 'Sí mintió sobre su hora de llegada: se quedó dormido, llegó a las 08:20 y no lo reportó por miedo a una sanción. Es un secreto menor, sin relación con Diego, y desde ese momento estuvo con Camila en el laboratorio de campo, que lo confirma.',

            'patricia-nunez' => 'Sí calló algo: notó que Lucas había sacado la Cámara 6 del almacén y no lo reportó por lealtad. Es una omisión, no una participación, y estuvo en la oficina de mantenimiento de 08:00 a 12:00, según los registros de acceso.',

            'raul-mena' => 'Sí pagaba a Gerardo para asegurar permisividad con el tráfico de fauna. Pero su interés exige silencio general, no la detención de Diego —una desaparición atrae investigación, justo lo que su negocio no quiere—, y estuvo en la ciudad con un notario toda la mañana, algo que acredita el registro de firmas de la notaría.',

            'sofia-ibarra' => 'Es una testigo, no una sospechosa: su aporte fue confirmar que vio a Diego salir hacia el Sendero de la Quebrada a las 08:35 y que acompañó a Lucas en su ronda por la Laguna Chica. No tuvo contacto con Diego después ni motivo alguno.',

            'armando-paillao' => 'Es el jefe de patrullas, no un sospechoso: su aporte fue confirmar los horarios de control por radio y que vio salir a Gerardo por la Pista del Alto a las 08:30. Fue quien dio la alarma al no recibir respuesta de Diego.',

            'sistema-sensores-mantenimiento' => 'Es una base de datos, no una persona: no puede haber intervenido. Su función fue confirmar, sin interpretar nada, que la lista activa tiene cinco cámaras, que CAM-06 es un equipo real retirado como repuesto, la fecha en que salió del almacén y la hora de la imagen de Diego. Nunca falló: la etiqueta era cierta.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Orus',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Llevaba tiempo aceptando dinero de Raúl para no complicarle las cosas con
            ciertas rutas. Nunca pensé que llegaría a esto.

            Cuando supe que Diego tenía pruebas y pensaba reportarme a la Autoridad, entré en
            pánico. Salí esa mañana hacia el sector alto, lo esperé en el sendero y lo
            intercepté. No lo pensé con calma. Lo amenacé con arruinarle la carrera, y no tuvo
            más remedio que seguirme.

            Lo llevé a la cabaña. Está bien, no le he hecho daño físico. Pero no sé cómo salir
            de esto sin que se derrumbe todo lo que he construido aquí.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué le ocurrió a Diego y cómo?',
            'hint' => 'Explica dónde y cómo lo abordaron, a dónde lo llevaron y en qué estado está.',
            'points' => 12,
            'guidance' => 'Lo esencial: lo interceptaron en el sendero tras la imagen de las 08:52 y lo retienen bajo presión, con vida, en la cabaña del sector alto. No exigir el detalle exacto de las amenazas.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hicieron desaparecer?',
            'hint' => 'Qué había descubierto Diego y qué quería evitar el responsable.',
            'points' => 13,
            'guidance' => 'Debe ligar la retención a los pagos que el responsable recibía y que Diego iba a denunciar. Pocos puntos si el motivo es solo «el tráfico de fauna» sin autoprotección.',
        ],
        'questions' => [
            [
                'key' => 'explicacion_camara_6',
                'label' => '¿Qué es realmente la «Cámara 6» y por qué no figura en la red oficial?',
                'hint' => 'Cuenta la historia del equipo: su origen, quién lo puso donde estaba y por qué el sistema no lo muestra.',
                'answer' => 'Es CAM-06, un equipo auténtico de la red oficial dañado en 2022 y retirado como repuesto sin baja definitiva. Lucas Figueroa lo sacó del almacén en febrero, lo reparó y lo reinstaló por su cuenta junto al segundo puente del Sendero de la Quebrada, donde sospechaba de traficantes, sin actualizar la lista de red activa por desconfianza a que una solicitud formal se filtrara. La imagen y la etiqueta son reales.',
                'points' => 25,
                'guidance' => 'Cero si sostiene que es una cámara secreta, una imagen trucada o un sistema hackeado. Puntos completos con: equipo real retirado, reinstalado informalmente por Lucas y no actualizado en el sistema.',
            ],
            [
                'key' => 'trafico_de_fauna',
                'label' => '¿Qué papel tiene el tráfico de fauna en la desaparición?',
                'hint' => 'Di si es real y si explica o no lo que le pasó a Diego.',
                'answer' => 'Existe una red real de tráfico de fauna vinculada a Raúl Mena: es el contexto que llevó a Diego a investigar y a Lucas a instalar la cámara, pero no es la causa directa de la desaparición. Raúl necesitaba silencio general, no retener a nadie; quien retuvo a Diego lo hizo para protegerse a sí mismo.',
                'points' => 20,
                'guidance' => 'Puntos completos solo si reconoce que el tráfico es real y lo separa de la causa de la desaparición. Pocos puntos si niega el tráfico o si culpa a la red de traficantes de la retención.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+13/T+26/T+39/T+52/T+65/T+75
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as
    | the other cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Cámara 6 no existe — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V4'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — llamada de radio a Diego',

            'audio_file' => 'a1-radio-patrulla.wav',
            'audio_scene' => 'Intercambio de radio de patrulla en una reserva natural: estática de radio y ambiente de campo (viento, aves distantes) de fondo. Entre las dos llamadas hay un silencio prolongado con solo estática; no hay respuesta. Sin música.',
            'audio_context' => 'Armando, jefe de patrullas, llama por radio a un compañero para confirmar su posición. Empieza con tono profesional y, ante la falta de respuesta, repite la llamada con seriedad creciente, sin llegar al pánico. Duración total 15-18 segundos.',
            'audio_speaker' => 'Speaker 1 - Armando Paillao',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                Diego, ¿copiado? Punto de control tres, confirma posición.

                Diego, ¿me copia? Repito, confirma posición.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 13,
            'title' => 'Expansión — cinco cámaras, una imagen',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            // V15 added here during authoring: the design never scheduled it.
            'evidence_codes' => ['V2', 'V5', 'V6', 'V7', 'V15'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — una ficha técnica',
            'source_file' => 'pista-privada-ficha.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: partial private glimpses (V3
            // fragment) must not satisfy any confrontation gate on their own.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — una nota de campo',
            'source_file' => 'pista-privada-notas-lucas.md',
            'delivery_mode' => 'random_player',
            // V9 PARTIAL (no equipment number, no author). No evidence_codes.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — una hoja de notas',
            'source_file' => 'pista-privada-notas-diego.md',
            'delivery_mode' => 'random_player',
            // V10 PARTIAL (no responsible name). The complete V10 arrives at
            // T+52. No evidence_codes.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 39,
            'title' => 'Complicación — «Cámara 6» no es una sexta cámara activa',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // V3 and V13 gate Lucas's confrontation (and, by extension, the
            // others with no evidence in the design). V9 complete was never
            // scheduled in the design: delivered with them.
            'evidence_codes' => ['V3', 'V9', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Giro — lo que Diego había descubierto',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // V12 moved here from T+65 (Gerardo's confrontation 2 needs V12 +
            // V14, and V14 only arrives at T+52).
            'evidence_codes' => ['V8', 'V10', 'V12', 'V14'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Cierre — la cabaña, el testimonio de Armando y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // DA = Armando's formal declaration (the 08:30 sighting), delivered
            // in this email: gates what he can say about it, like TP / DECL-PAOLA
            // in other cases.
            'evidence_codes' => ['V11', 'DA'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 75,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para resolver el caso: qué era realmente la «Cámara 6», quién retuvo a Diego y por qué, y cuál es el papel del tráfico de fauna. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/camara-6-no-existe/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-mapa-reserva.png', 'caption' => 'Mapa de la reserva, con las cinco cámaras oficiales'],
            ['file' => 'v4-camara-6-ultima-imagen.png', 'caption' => 'La última imagen conocida de Diego, etiquetada «CAM-06»'],
        ],
        'expansion.md' => [
            ['file' => 'v2-lista-camaras-activas.png', 'caption' => 'Lista de cámaras activas de la red oficial'],
            ['file' => 'v5-cam02-bebedero.png', 'caption' => 'CAM-02, Bebedero del Arroyo'],
            ['file' => 'v6-cam04-cruce.png', 'caption' => 'CAM-04, Cruce Los Maitenes (visión nocturna)'],
            ['file' => 'v7-cam05-laguna.png', 'caption' => 'CAM-05, Laguna Chica'],
            ['file' => 'v15-escritorio-diego.png', 'caption' => 'El escritorio de Diego en el puesto central'],
        ],
        'pista-privada-ficha.md' => [
            ['file' => 'v3a-ficha-parcial.png', 'caption' => 'Fragmento de una ficha de equipo del archivo técnico'],
        ],
        'pista-privada-notas-lucas.md' => [
            ['file' => 'v9a-notas-campo-parcial.png', 'caption' => 'Fragmento de una nota de campo'],
        ],
        'pista-privada-notas-diego.md' => [
            ['file' => 'v10a-notas-diego-parcial.png', 'caption' => 'Fragmento de unas notas sobre irregularidades'],
        ],
        'complicacion.md' => [
            ['file' => 'v3-ficha-mantenimiento.png', 'caption' => 'Ficha de mantenimiento completa del equipo «Cámara 6»'],
            ['file' => 'v13-registro-movimiento.png', 'caption' => 'Registro de movimiento de equipo'],
            ['file' => 'v9-notas-campo-lucas.png', 'caption' => 'Notas de campo completas, con su autor'],
        ],
        'giro.md' => [
            ['file' => 'v10-notas-diego.png', 'caption' => 'Notas de Diego sobre irregularidades, completas'],
            ['file' => 'v14-registro-transferencias.png', 'caption' => 'Registro de transferencias'],
            ['file' => 'v8-trampa-sendero.png', 'caption' => 'Una trampa vacía junto al sendero'],
            ['file' => 'v12-radio-diego.png', 'caption' => 'La radio de Diego, en el lugar del hallazgo'],
        ],
        'cierre.md' => [
            ['file' => 'v11-cabana-del-alto.png', 'caption' => 'La cabaña de mantenimiento del sector alto, vista desde un dron'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v3-ficha-mantenimiento.png', 'caption' => 'La historia real de la «Cámara 6»'],
            ['file' => 'v9-notas-campo-lucas.png', 'caption' => 'Quién la reinstaló y dónde'],
            ['file' => 'v10-notas-diego.png', 'caption' => 'Lo que Diego había descubierto'],
            ['file' => 'v14-registro-transferencias.png', 'caption' => 'Los pagos que Diego iba a denunciar'],
            ['file' => 'v12-radio-diego.png', 'caption' => 'Dónde apareció su radio'],
        ],
    ],
];
