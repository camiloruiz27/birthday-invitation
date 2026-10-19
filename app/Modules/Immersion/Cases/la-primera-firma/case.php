<?php

/**
 * Case manifest: "La primera firma"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: a double incident with THREE distinct responsibles. (1) Ariana
 * Folgado, president-elect, signed the sale of the Bodega Sur at 00:05; her
 * signature is AUTHENTIC, the problem is authority: the presidency only passes
 * with the Acta de Posesión (10:00), so Horacio Belmonte was still the one who
 * had to sign. (2) Gonzalo Prada, an undeclared consultant of the buyer (Grupo
 * Insignia), pushed for the early signature. (3) Patricia Lemus, who has nothing
 * to do with the sale, attacked Leonor Campuzano in the archive out of fear that
 * Leonor's general review of stamps would expose an old document she had
 * altered. The engine scores only the culprit, so `culprit_slug` is Patricia
 * (the physical harm) and the other two threads are asked for with
 * `accusation.questions`. Leonor survives (label "Herida"). NO piece of evidence
 * may invite a handwriting analysis: the table must never be tempted to treat
 * the signature as forged.
 *
 * Master schedule (single source of truth for every file of this case; all
 * hours in evidence, md files and the manifest derive from it):
 *   Night before: 21:47 Gonzalo's email to Ariana (V06). 23:00 Fabiola in the
 *   presidency office. 23:30 last toast of the outgoing board. 23:59 countdown.
 *   00:00 new year. 00:05 Ariana signs the Bodega Sur order; Fabiola stamps the
 *   radicación (V03). 00:40 presidency office closed. 01:30 celebration ends,
 *   Rubén closes the building and locks the archive.
 *   Morning: 07:45 Rubén opens the building and the archive. 08:00 Leonor enters
 *   the archive to prepare the Acta folder. 08:30 breakfast of both boards in
 *   the salón. 08:50–09:05 Marisela talks with Leonor in the secretaría (Rubén
 *   sees Leonor leave at 08:50 and return at 09:05). ~09:10 Leonor, from inside
 *   the archive, phones Patricia (internal line; Patricia is in her office) and
 *   tells her she will review "todos los sellos recientes, no solo el de hoy"
 *   (by phone, NOT in person: Leonor never leaves the archive between 09:05 and
 *   09:40, or Rubén would have seen her use the door). 09:00–09:40 Esteban at the welcome table by
 *   the salón door. 09:15 Horacio's farewell speech (salón). 09:22 Patricia
 *   enters the archive (Rubén, with her coffee thermos); 09:31 she leaves in a
 *   hurry. 09:40 an auxiliary of the celebration, sent for the Acta folder,
 *   finds Leonor. 09:45 symbolic key handover. 10:00 Acta de Posesión
 *   (programmed). Camera 1 covers the corridor and the archive door (the only
 *   access), never the interior. Between 09:05 and 09:40 only Leonor (already
 *   inside), Patricia and the auxiliary used that door.
 *   Alibis (V08 + Yesenia): Horacio gives the speech 09:15; Ariana and Gonzalo
 *   in the salón; Esteban at the welcome table 09:00–09:40; Marisela in the
 *   salón from 09:05.
 *   Old document (V07): "Inventario de equipos — Gerencia de Operaciones",
 *   radicación 14/03 (altered by Patricia months before; the old date is the
 *   only calendar date of the case besides 01/01 and is part of the plot).
 *
 * Adaptations made while porting the design doc to the engine (kept here so the
 * next author does not "fix" them back):
 *  - culprit_slug is `patricia-lemus` (slugs here are full names; the design
 *    says "patricia"). The 5-component scoring of the doc (30/20/25/25 plus the
 *    "treated the signature as a forgery" penalty) becomes culprit_points 30 +
 *    weapon ("¿Qué le ocurrió a Leonor?", 10) + motive (10) + two questions
 *    (who signed without authority, 25; who benefited, 25) = 100. The penalty
 *    is expressed in each question's `guidance` (zero if the answer treats the
 *    signature as forged or the vote as rigged).
 *  - Leonor is NOT interrogable. The doc makes her "interrogable with 1
 *    question from T+64", but the engine demands at least 3 normal questions.
 *    Her partial testimony (A1: smell of coffee + sound of a folder) is a
 *    timeline `audio_email` at T+64 instead. `victim.label` is "Herida" and
 *    `victim.incident` avoids "la muerte de".
 *  - Interrogations open with the first envelope (the doc opens them at T+0),
 *    so `cta_interrogation` is on gancho.md, which also introduces the people;
 *    the T+12 envelope (expansion.md) introduces the private fragments.
 *  - Private information ("Cronología de la madrugada"): four random_player
 *    fragments of three hourly entries each (T+12) — 00:05 lives only in
 *    fragment 2 and 10:00 only in fragment 4 — plus one random_player fragment
 *    of the acta de relevo (v02a, cargos only, WITHOUT the hour line). The
 *    complete public copy of the acta (V02, hour 10:00) arrives at T+24 with V03
 *    and V05 (the doc put V02 at T+12 in both forms; the public copy needs to
 *    be there for the V02/V03 comparison). The aggregate summary of the doc's
 *    fail-safe (T+36) is unconditional here: the "Cronología consolidada" table
 *    inside complicacion.md, which points at the discrepancy of hours without
 *    naming anyone (code CR). The fragments carry NO evidence code: they are
 *    text pieces and no critical fact depends on a single one (everything is
 *    repeated in V03, V02, V04 and CR).
 *  - Written declarations carry their own codes, like DF in punto-de-control:
 *    DFA = Fabiola's expanded declaration (body of giro.md, T+50), DRU =
 *    Rubén's (body of cierre.md, T+64). Fabiola's, Rubén's and Yesenia's key
 *    facts are gated on them (DFA, DRU, V08) in their .md files and in their
 *    stages. Rubén's doc fail-safe ("si no han conectado a Patricia, Rubén lo
 *    hace explícito al ser preguntado de nuevo") is the stage opened by DRU.
 *  - Confrontations are gated on the evidence that anchors them: Patricia 1 =
 *    DRU, Patricia 2 = V07 + A1 (+ DRU), all delivered at T+64 (the three are
 *    one stage); Ariana = V01 + V04; Horacio = V04 (T+36); Gonzalo 1 and 2 = V06
 *    (T+50; the doc's json says T+50, its md T+36); Marisela = V08 (the doc
 *    cites Yesenia's testimony, which the table can only anchor with her list).
 *    Esteban has NO confrontation stage: his admission needs no evidence in the
 *    doc ("cualquier pregunta directa de seguimiento"), so it is part of his
 *    base questions in his .md (4 questions).
 *  - Fabiola 3+2, Rubén 3+2, Yesenia 3+2, Mauricio 3+1 (after V02, for the hour
 *    of the Acta): witnesses get 3–5 questions according to the facts of their
 *    .md. Mauricio only explains the three rules of V01 (verbatim from the doc).
 *  - Extra envelope cierre-coartadas.md at T+72: the doc's third fail-safe
 *    (crossed alibis of Horacio, Marisela and Esteban before the accusation),
 *    extended to the five board members. The unlock is at T+80.
 *  - The doc says Leonor was found "durante la celebración"; here she is found
 *    at 09:40 by an unnamed auxiliary, before the Acta (10:00). Nothing is said
 *    about whether the ceremony took place.
 *  - V05 (plano) draws only the zones needed: archivo, único acceso, pasillo,
 *    puesto de seguridad, secretaría general, oficina de presidencia, oficinas
 *    administrativas and salón. The old document (V07) is an "inventario de
 *    equipos" of the Gerencia de Operaciones: it ties to Patricia's area without
 *    naming her.
 *  - V06 never names the buyer ("el comprador"); the buyer's name, Grupo
 *    Insignia, appears in V03.
 *  - The coffee: Patricia's public ficha and Rubén's declaration (DRU) establish
 *    the thermos before A1 ("el olor del café que siempre lleva encima").
 *  - The design's epilogues are produced by the engine from `exonerations`;
 *    A2 (Patricia's confession) is `solution.confession_script`, generated per
 *    game. A1 is the only recording of the timeline.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'La primera firma',
    'version' => '1.0',
    'code' => 'Expediente reservado — Cooperativa Horizonte',
    'authority' => 'Comité de Vigilancia de la Cooperativa Horizonte',

    'victim' => [
        'name' => 'Leonor Campuzano',
        'label' => 'Herida',
        'incident' => 'la agresión contra {name}',
        'photo' => 'leonor-campuzano.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The doc asks for 3–4 questions per person plus confrontations; the
        // engine uses 3–5 normal questions per person (by stages) and 3 more
        // for those with a confrontation.
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
    */

    'catalog' => [
        'tagline' => 'La firma es de verdad. El cargo, todavía no.',

        'description' => <<<'TXT'
            Cooperativa Horizonte vive su relevo anual de junta directiva. Minutos
            después de la medianoche se firma la venta de un inmueble de la
            cooperativa; por la mañana, justo antes de la ceremonia de posesión, la
            archivista principal aparece herida en el archivo. La firma es auténtica.
            Lo que está en duda es quién podía firmar y a qué hora.

            Tu equipo (4 a 8 investigadores, ideal 6) recibe el expediente en tiempo
            real y podrá interrogar a seis sospechosos, tres testigos y a un
            secretario jurídico, durante 80 minutos. La cronología de la madrugada
            llega repartida en fragmentos privados: nadie la tiene entera, y solo al
            compararlos en la mesa aparece la discrepancia.

            Una agresión y una operación irregular comparten el mismo contexto, pero
            no a los mismos responsables. Separar quién hizo qué, y por qué, es lo
            que lleva hasta la verdad.
            TXT,

        // Archivo esperado en public/immersion/la-primera-firma/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-primera-firma).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'La firma es de verdad. El cargo, todavía no.',
            'bullets' => [
                '4 a 8 investigadores reconstruyendo una cronología repartida en fragmentos privados',
                'Seis sospechosos, tres testigos y un secretario jurídico a quienes interrogar',
                '80 minutos con el expediente llegando en tiempo real: una venta radicada de madrugada y una agresión en el archivo',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 80,
        'min_players' => 4,
        'max_players' => 8,

        'price_amount' => 64900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/la-primera-firma/ (ver ASSET_PROMPTS.md).
        'published' => false,
        'sort_order' => 31,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | The full testimony lives only in content/suspects/*.md. Fabiola, Rubén and
    | Yesenia are witnesses and Mauricio a neutral source (accusable => false).
    | Leonor, the victim, is not interrogable: her recollection is the A1 audio.
    |
    */

    'suspects' => [
        'patricia-lemus' => [
            'name' => 'Patricia Lemus',
            'role' => 'sospechosa',
            'file' => 'suspects/patricia-lemus.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['DRU', 'V07', 'A1'], 'confrontation' => true],
            ]],
            'photo' => 'patricia-lemus.png',
            'connection' => 'Gerente de operaciones y archivista adjunta; trabaja codo a codo con Leonor en el archivo',
            'motive' => 'Miedo a que la revisión general de sellos de Leonor expusiera un documento antiguo que ella alteró meses atrás, sin relación con la venta',
            'alibi' => 'Dice que estaba en su oficina; nadie lo confirma',
            'public' => [
                'age' => '41 años',
                'profile' => 'Gerente de operaciones y archivista adjunta. Eficiente y cordial, siempre con una carpeta de archivo a mano y un termo de café.',
                'alibi' => 'Dice haber pasado la mañana en su oficina.',
                'facts' => [
                    'Trabaja codo a codo con Leonor en el archivo.',
                    'Sus compañeros bromean con que nunca suelta su termo de café.',
                ],
            ],
        ],
        'ariana-folgado' => [
            'name' => 'Ariana Folgado',
            'role' => 'sospechosa',
            'file' => 'suspects/ariana-folgado.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V01', 'V04'], 'confrontation' => true],
            ]],
            'photo' => 'ariana-folgado.png',
            'connection' => 'Presidenta electa; firmó la venta de la Bodega Sur',
            'motive' => 'Quería ejecutar su programa de modernización cuanto antes',
            'alibi' => 'En el salón de celebración con la junta toda la mañana',
            'public' => [
                'age' => '45 años',
                'profile' => 'Presidenta electa de la cooperativa. Segura y entusiasta, con aspecto ejecutivo moderno y el logo de la cooperativa en la solapa.',
                'alibi' => 'Dice haber estado en la celebración con la junta durante la mañana.',
                'facts' => [
                    'Ganó la elección con un programa de modernización.',
                    'Su firma figura en la orden de venta de la Bodega Sur.',
                ],
            ],
        ],
        'horacio-belmonte' => [
            'name' => 'Horacio Belmonte',
            'role' => 'sospechoso',
            'file' => 'suspects/horacio-belmonte.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V04'], 'confrontation' => true],
            ]],
            'photo' => 'horacio-belmonte.png',
            'connection' => 'Presidente saliente; terminaba su gestión esa mañana',
            'motive' => 'Orgullo herido y un interés claro en que se respete el protocolo que él representa',
            'alibi' => 'Dando su discurso de despedida en el salón de celebración',
            'public' => [
                'age' => '63 años',
                'profile' => 'Presidente saliente. Digno, de traje formal clásico, algo dolido por la forma en que cierra su gestión.',
                'alibi' => 'Dice haber dado su discurso de despedida en el salón.',
                'facts' => [
                    'Presidió la cooperativa hasta el relevo.',
                    'Le preocupa que se respete el protocolo del relevo.',
                ],
            ],
        ],
        'gonzalo-prada' => [
            'name' => 'Gonzalo Prada',
            'role' => 'sospechoso',
            'file' => 'suspects/gonzalo-prada.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V06'], 'confrontation' => true],
            ]],
            'photo' => 'gonzalo-prada.png',
            'connection' => 'Vocal de la junta y aliado cercano de Ariana',
            'motive' => 'Consultor no declarado de Grupo Insignia, comprador de la Bodega Sur; se beneficia de que la venta se cierre cuanto antes',
            'alibi' => 'En el salón de celebración con la junta toda la mañana',
            'public' => [
                'age' => '52 años',
                'profile' => 'Vocal de la junta y aliado cercano de Ariana. Persuasivo y cuidado en el trato, con el móvil siempre cerca.',
                'alibi' => 'Dice haber estado en el salón con la junta durante la mañana.',
                'facts' => [
                    'Respalda el programa de modernización de Ariana.',
                    'Defiende la venta de la Bodega Sur como un buen trato.',
                ],
            ],
        ],
        'marisela-duarte' => [
            'name' => 'Marisela Duarte',
            'role' => 'sospechosa',
            'file' => 'suspects/marisela-duarte.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V08'], 'confrontation' => true],
            ]],
            'photo' => 'marisela-duarte.png',
            'connection' => 'Vocal de la junta; discutió con Leonor sobre la venta esa mañana',
            'motive' => 'Oposición pública y vehemente a la venta de la Bodega Sur',
            'alibi' => 'En el salón de celebración desde las 09:05',
            'public' => [
                'age' => '48 años',
                'profile' => 'Vocal de la junta. Directa y apasionada, sin adornos en la forma de hablar ni de vestir.',
                'alibi' => 'Dice haber estado en el salón después de hablar con Leonor.',
                'facts' => [
                    'Se opone abiertamente a la venta de la Bodega Sur.',
                    'Habló con Leonor esa mañana sobre la operación.',
                ],
            ],
        ],
        'esteban-rueda' => [
            'name' => 'Esteban Rueda',
            'role' => 'sospechoso',
            'file' => 'suspects/esteban-rueda.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
            ]],
            'photo' => 'esteban-rueda.png',
            'connection' => 'Vocal de la junta con acceso a la información financiera de la cooperativa',
            'motive' => 'Ninguno respecto al caso; tiene un préstamo personal con la cooperativa en mora que no ha declarado',
            'alibi' => 'En la mesa de bienvenida, junto a la puerta del salón, toda la mañana',
            'public' => [
                'age' => '54 años',
                'profile' => 'Vocal de la junta. Correcto, aunque algo tenso cuando la conversación toca temas de dinero.',
                'alibi' => 'Dice haber atendido la mesa de bienvenida de los invitados.',
                'facts' => [
                    'Tiene acceso a la información financiera de la cooperativa.',
                    'Es evasivo cuando se habla de sus finanzas personales.',
                ],
            ],
        ],
        'fabiola-santos' => [
            'name' => 'Fabiola Santos',
            'role' => 'testigo (no es sospechosa)',
            'file' => 'suspects/fabiola-santos.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DFA']],
            ]],
            'photo' => 'fabiola-santos.png',
            'connection' => 'Asistente de la presidencia; estuvo presente cuando se firmó la venta',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '33 años',
                'profile' => 'Asistente de la presidencia. Precisa y ordenada, con la tablet siempre en la mano.',
                'facts' => [
                    'Estuvo en la oficina de presidencia durante la noche del relevo.',
                    'Lleva la agenda y los papeles de la oficina de presidencia.',
                ],
            ],
            'accusable' => false,
        ],
        'ruben-castano' => [
            'name' => 'Rubén Castaño',
            'role' => 'testigo (no es sospechoso)',
            'file' => 'suspects/ruben-castano.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DRU']],
            ]],
            'photo' => 'ruben-castano.png',
            'connection' => 'Guardia de seguridad; su puesto está en el pasillo de acceso al archivo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '50 años',
                'profile' => 'Guardia de seguridad de la sede, de uniforme y con radio al cinto. Sereno y metódico.',
                'facts' => [
                    'Su mesa está en el pasillo, frente a la puerta del archivo.',
                    'Ve desde su puesto el monitor de la cámara del pasillo.',
                ],
            ],
            'accusable' => false,
        ],
        'yesenia-roldan' => [
            'name' => 'Yesenia Roldán',
            'role' => 'testigo (no es sospechosa)',
            'file' => 'suspects/yesenia-roldan.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V08']],
            ]],
            'photo' => 'yesenia-roldan.png',
            'connection' => 'Coordinadora de la celebración del relevo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '37 años',
                'profile' => 'Coordinadora de la celebración. Organizada y rápida, con la tablilla del cronograma siempre a mano.',
                'facts' => [
                    'Llevó el cronograma y la lista de asistentes de la celebración.',
                    'Coordinó el desayuno y el discurso de despedida.',
                ],
            ],
            'accusable' => false,
        ],
        'mauricio-ibarra' => [
            'name' => 'Mauricio Ibarra',
            'role' => 'fuente neutral (secretario jurídico)',
            'file' => 'suspects/mauricio-ibarra.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V02']],
            ]],
            'photo' => 'mauricio-ibarra.png',
            'connection' => 'Secretario jurídico; explica el reglamento de la cooperativa',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '58 años',
                'profile' => 'Secretario jurídico de la cooperativa. Formal y exacto, siempre con el reglamento bajo el brazo.',
                'facts' => [
                    'Explica el contenido literal del reglamento de la cooperativa.',
                    'No opina sobre hechos ni sobre personas.',
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
        'culprit_slug' => 'patricia-lemus',

        'headline' => 'La firma de Ariana es auténtica pero sin facultad, Gonzalo se benefició de la venta, y fue Patricia, por un motivo aparte, quien agredió a Leonor en el archivo.',

        'motive' => 'Miedo a que un error personal y antiguo, sin relación con la venta de la Bodega Sur, quedara expuesto cuando Leonor anunció que revisaría todos los sellos de radicación recientes: meses atrás Patricia había cambiado la fecha de radicación de un documento para ocultar un error de agenda.',

        'method' => 'Patricia entró al archivo a recuperar la carpeta de ese documento antiguo antes de que Leonor lo encontrara. Hubo un forcejeo breve y sin premeditación por la carpeta; Leonor cayó y se golpeó la cabeza contra un estante. Patricia salió sin pedir ayuda. No hubo arma ni violencia explícita.',

        'key_evidence' => [
            'La declaración de Rubén: vio a Patricia entrar al archivo a las 09:22 y salir de prisa a las 09:31; nadie más pasó por esa puerta, y la cámara cubre el acceso pero no el interior.',
            'El plano de la sede: el archivo tiene un único acceso y su interior queda fuera de la cámara.',
            'La fotografía del archivo: una carpeta fuera de lugar, el inventario de equipos de la Gerencia de Operaciones.',
            'El recuerdo de Leonor: el olor a café que esa persona lleva siempre encima y el sonido de una carpeta cayendo contra el estante.',
            'La orden de venta (sello 00:05), el acta de relevo y la agenda (Acta de Posesión a las 10:00) y la tabla de poderes: Ariana firmó sin facultad, aunque su firma es auténtica.',
            'El correo de Gonzalo y la declaración de Fabiola: la presión para firmar cuanto antes, sin esperar la ceremonia.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including the three witnesses and the neutral source.
         */
        'exonerations' => [
            'ariana-folgado' => 'Sí firmó la venta de la Bodega Sur a las 00:05, sin la facultad que solo le daba el Acta de Posesión de las 10:00, aunque la firma es auténtica y la puso por su voluntad. Fue un error de buena fe bajo la presión de Gonzalo. Estuvo en el salón con la junta toda la mañana, y su error es institucional, no un intento de ocultar nada con violencia.',

            'horacio-belmonte' => 'Era quien técnicamente debía firmar la venta, y su malestar es real, pero su respuesta es institucional: pide corregir el procedimiento. Dio su discurso de despedida en el salón, ante las dos juntas, mientras Leonor caía.',

            'gonzalo-prada' => 'Sí presionó para que la venta se firmara cuanto antes y es consultor no declarado de Grupo Insignia, el comprador, una falta real contra el reglamento. Pero protege su interés con presión discreta, no con violencia, y estuvo en el salón con la junta toda la mañana.',

            'marisela-duarte' => 'Sí discutió con Leonor sobre la operación, pero era sobre cómo documentar su objeción formal, en la secretaría y antes del discurso. Desde las 09:05 estuvo en el salón con los invitados, lejos del archivo.',

            'esteban-rueda' => 'Sí guarda un secreto real, un préstamo personal en mora con la cooperativa, y fue evasivo al hablar de ello, pero no tiene relación con la venta ni con Leonor. Atendió la mesa de bienvenida junto a la puerta del salón toda la mañana.',

            'fabiola-santos' => 'Es una testigo, no una sospechosa: estuvo en la oficina de presidencia cuando se firmó la venta y contó lo que vio y oyó, incluida la insistencia de Gonzalo. No tenía ninguna relación con el archivo esa mañana ni motivo alguno.',

            'ruben-castano' => 'Es un testigo, no un sospechoso: desde su mesa del pasillo vio entrar y salir a quien pasó por la puerta del archivo, y lo contó con precisión. No entró al archivo ni tenía motivo alguno.',

            'yesenia-roldan' => 'Es una testigo, no una sospechosa: coordinó la celebración y su lista permite confirmar dónde estaban cinco de los miembros de la junta. No tenía relación con el archivo ni motivo alguno.',

            'mauricio-ibarra' => 'Es una fuente neutral, no un sospechoso: solo explicó el contenido literal de las tres reglas de vigencia de la cooperativa, sin opinar sobre nadie. No tenía relación con la venta ni con el archivo.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Kore',

        // Audio A2 of the design. Written to be played as-is: the model only
        // reads it aloud and lightly weaves in 1-2 real questions from the
        // table, it never composes it.
        'confession_script' => <<<'TXT'
            No fui a buscarla para hacerle daño. Cuando dijo que iba a revisar todos los
            sellos, no los de hoy, pensé en un documento mío, de hace meses, que nada tiene
            que ver con la Bodega Sur. Entré a sacarlo antes de que ella lo encontrara.

            Forcejeamos un momento por la carpeta, ella cayó y se golpeó. Me asusté y salí.
            No fue lo que planeé, pero fue lo que hice, y tengo que responder por eso.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué le ocurrió a Leonor y cómo?',
            'hint' => 'Cuenta qué pasó en el archivo entre la responsable y Leonor.',
            'points' => 10,
            'guidance' => 'Lo esencial: un forcejeo breve y no premeditado por una carpeta, en el que Leonor cayó y se golpeó la cabeza contra un estante; sin arma. No exigir aquí el motivo ni lo de la venta: se preguntan aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué ocurrió?',
            'hint' => 'Qué temía la responsable que Leonor descubriera, y si tiene o no relación con la venta de la Bodega Sur.',
            'points' => 10,
            'guidance' => 'Lo esencial: miedo a que un error personal y antiguo (un documento distinto, alterado meses atrás) quedara expuesto en la revisión de sellos de Leonor. Cero si lo vincula con la venta de la Bodega Sur, con Gonzalo o con Grupo Insignia.',
        ],
        'questions' => [
            [
                'key' => 'firma_sin_facultad',
                'label' => '¿Quién firmó la venta de la Bodega Sur sin tener todavía la facultad, y por qué no la tenía?',
                'hint' => 'Nombra a la persona, la hora de la firma y la regla que explica el problema.',
                'answer' => 'Ariana Folgado, presidenta electa: firmó la venta a las 00:05, pero el cargo de presidente solo pasa a la junta entrante con la firma del Acta de Posesión, programada a las 10:00; hasta entonces seguía siéndolo Horacio Belmonte. La firma es auténtica; lo que faltaba era la facultad.',
                'points' => 25,
                'guidance' => 'Puntos completos con Ariana y la razón (Acta de Posesión aún sin firmar / el cargo no había pasado). Parcial si solo nombra a Ariana sin la regla. Cero si trata la firma como falsificada o dice que hubo una votación amañada o un cambio de control sucesorio.',
            ],
            [
                'key' => 'beneficiado_venta',
                'label' => '¿Quién se benefició de la venta y qué hizo para que se firmara tan pronto?',
                'hint' => 'Nombra a la persona, su vínculo con el comprador y lo que hizo.',
                'answer' => 'Gonzalo Prada, vocal de la junta y consultor no declarado de Grupo Insignia, el comprador de la Bodega Sur: presionó a Ariana para que firmara cuanto antes, sin esperar la ceremonia, y nunca declaró su interés a la junta.',
                'points' => 25,
                'guidance' => 'Puntos completos con Gonzalo, su vínculo no declarado con el comprador y la presión para firmar pronto. Parcial si solo nombra a Gonzalo. Cero si lo acusa de haber agredido a Leonor o si responde con otra persona.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+24/T+36/T+50/T+64/T+80 plan
    | into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases). Extra T+72 envelope carries the crossed-alibis fail-safe.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La primera firma — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Apertura — la cronología de la madrugada, en pedazos',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Fragmento privado — cronología de la madrugada (1)',
            'source_file' => 'pista-privada-cronologia-1.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Fragmento privado — cronología de la madrugada (2)',
            'source_file' => 'pista-privada-cronologia-2.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Fragmento privado — cronología de la madrugada (3)',
            'source_file' => 'pista-privada-cronologia-3.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Fragmento privado — cronología de la madrugada (4)',
            'source_file' => 'pista-privada-cronologia-4.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Fragmento privado — un trozo del acta de relevo',
            'source_file' => 'pista-privada-acta-fragmento.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: v02a is a PARTIAL piece (cargos
            // only, no hour line). The complete V02 arrives at T+24.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Desarrollo — el acta, la orden y el plano',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V02', 'V03', 'V05'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Complicación — la agenda del día y la cronología consolidada',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // CR = the consolidated chronology table in the body (the doc's
            // aggregate fail-safe), V04 = the full agenda.
            'evidence_codes' => ['CR', 'V04'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Giro — un correo de la víspera y la declaración de Fabiola',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DFA = Fabiola's written (expanded) declaration, the body of giro.md.
            'evidence_codes' => ['V06', 'DFA'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 64,
            'title' => 'Cierre — el archivo, la lista de la mañana y la declaración de Rubén',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // DRU = Rubén's written (expanded) declaration, the body of cierre.md.
            'evidence_codes' => ['V07', 'V08', 'DRU'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 64,
            'title' => 'Audio — lo que Leonor recuerda',

            'audio_file' => 'a1-testimonio-leonor.mp3',
            'audio_scene' => 'Habitación de descanso de la sede, ambiente silencioso, apenas un reloj de pared lejano; sin música.',
            'audio_context' => 'Leonor, ya recuperada de la conmoción, cuenta con voz débil pero serena lo poco que recuerda, despacio y con pausas. Debe sonar como un recuerdo genuinamente parcial, no como una pista oculta a propósito. No identifica a nadie.',
            'audio_speaker' => 'Speaker 1 - Leonor Campuzano',
            'audio_voice' => 'Vindemiatrix',
            'audio_script' => <<<'TXT'
                No recuerdo bien su cara... todo pasó muy rápido. Pero recuerdo el olor del
                café que siempre lleva encima. Y un sonido, como de una carpeta cayendo contra
                el estante. Nada más. Lo siento, me gustaría recordar más.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 72,
            'title' => 'Cruces pendientes — las coartadas de la junta',
            'source_file' => 'cierre-coartadas.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 80,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién agredió a Leonor, cómo y por qué, el formulario pregunta quién firmó la venta de la Bodega Sur sin tener todavía la facultad para hacerlo y quién se benefició de la operación. Cada pregunta se responde por separado.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-primera-firma/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-tabla-poderes.png', 'caption' => 'Tabla de poderes y matriz de autorizaciones del reglamento interno'],
        ],
        'pista-privada-acta-fragmento.md' => [
            ['file' => 'v02a-acta-relevo-fragmento.png', 'caption' => 'Fragmento del acta de relevo: cargos salientes y entrantes'],
        ],
        'desarrollo.md' => [
            ['file' => 'v02-acta-relevo.png', 'caption' => 'Acta de relevo de la junta directiva (copia completa)'],
            ['file' => 'v03-orden-patrimonial.png', 'caption' => 'Orden de operación patrimonial, con su sello de radicación'],
            ['file' => 'v05-plano-sede.png', 'caption' => 'Plano de la sede, con la cobertura de la cámara de seguridad'],
        ],
        'complicacion.md' => [
            ['file' => 'v04-agenda-transicion.png', 'caption' => 'Agenda oficial del día de la celebración'],
        ],
        'giro.md' => [
            ['file' => 'v06-correo-autorizacion.png', 'caption' => 'Correo recibido por la presidenta electa la noche anterior'],
        ],
        'cierre.md' => [
            ['file' => 'v07-foto-archivo.png', 'caption' => 'Fotografía del archivo, tomada tras el hallazgo'],
            ['file' => 'v08-lista-asistentes.png', 'caption' => 'Lista de asistentes y horarios de la celebración'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v03-orden-patrimonial.png', 'caption' => 'La venta se radicó a las 00:05'],
            ['file' => 'v04-agenda-transicion.png', 'caption' => 'El Acta de Posesión estaba programada para las 10:00'],
            ['file' => 'v06-correo-autorizacion.png', 'caption' => 'La insistencia en firmar sin esperar la ceremonia'],
            ['file' => 'v07-foto-archivo.png', 'caption' => 'La carpeta fuera de lugar en el archivo'],
        ],
    ],
];
