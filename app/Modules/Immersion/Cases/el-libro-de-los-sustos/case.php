<?php

/**
 * Case manifest: "El Libro de los Sustos"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it: identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: a short (35 min) literary mystery with a classic ending and
 * NO audio. During the annual anonymous reading of the Halloween story club,
 * the last paragraph of Nicolás Abarca's story ("La puerta del fondo") turns out
 * to be a veiled warning aimed at the club president, Georgina Valls. The table
 * proves the intervention by comparing two perfectly legible pages (V02 approved
 * version vs V03 reading copy: identical up to "la única que siempre permanecía
 * cerrada", then a completely different final paragraph), crossing it with the
 * print order (V04: Rosaura Mindel closed the print file) and Georgina's
 * testimony. No cipher, no wordplay, no single-typo proof.
 *
 * Reference timetable (single source of truth for every file of this case;
 * documents are dated by relative day, never by calendar date):
 *   Weeks before the reading: Nicolás submits "La puerta del fondo" anonymously;
 *     the editorial review approves it "sin cambios", signed by Rosaura as
 *     coordinadora editorial (F1, V02).
 *   Same period: the club announces that the presidency election will be held
 *     shortly after the reading.
 *   Days before: Rosaura receives the final file of all the stories.
 *   VÍSPERA de la lectura (the day before): Rosaura replaces the last paragraph
 *     of Nicolás's page; closes the print file at 17:42 and sends it to the
 *     printer at 17:55 (12 numbered copies); copies delivered at 19:20 (V04).
 *   DÍA DE LA LECTURA: 18:47 Rosaura's message (C1); the reading starts at
 *     20:00 in the library; story no. 3 ("La puerta del fondo") is read from
 *     copy no. 7, the "ejemplar de lectura" (V03, F2, V05). After the reading,
 *     the same night, Georgina asks to compare with the approved original.
 *   Copies: 12 numbered booklets; every copy comes from the same closed file.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 4-field form and its point
 *    scoring (30/20/20/30, with the "errata aislada" penalty) are translated to
 *    culprit_points 30 + weapon 20 (what really happened) + motive 20 + one
 *    extra question worth 30 (how the intervention is proven), whose guidance
 *    gives zero if the answer rests on an isolated typo or a single word.
 *  - Georgina Valls is both the `victim` (label "Amenazada", with `incident`
 *    "la amenaza dirigida a {name}") AND a roster witness with
 *    `accusable => false`, using the same portrait file. The victim entry is
 *    not interrogable by itself; the roster entry is how the table talks to her.
 *  - Nicolás has NO secret and NO lie (the design is explicit). His .md says so
 *    and gives the exact original ending, which is also V02.
 *  - The design says Rosaura "cerró el archivo pocas horas antes de la lectura"
 *    (culprit_errors) but also "la víspera" / "la tarde antes" (timeline, V04).
 *    The timeline wins: file closed at 17:42 of the VÍSPERA; the reading is the
 *    next evening (20:00). "Pocas horas" is not used anywhere.
 *  - The doc places V05 (table photo) at T+28 but Rosaura's confrontation 1
 *    lists "orden de tirada + fotografía de mesa" at T+21. Confrontation 1 now
 *    waits only for V04 (the print order), which is what makes her admit
 *    closing the file; V05 and the reading record arrive at T+28.
 *  - Private clue: V02 (page of the author) is a random_player event at T+7
 *    (code V02) with a complete PUBLIC copy at T+28 (same page, same image
 *    file, code V02 again) as the design asks for redundancy; Nicolás's
 *    testimony and F1 also give the original ending.
 *  - Doc-F1 (selection record) arrives with the expansion (T+7) because it is
 *    administrative context for N1 and the doc leaves it unscheduled; Doc-F2
 *    (reading record) arrives with V05 at T+28 because it connects the table
 *    photo to the specific copy (no. 7). C1 arrives with V04 at T+21.
 *  - Georgina's "testimonio ampliado" (T+28), and the secrets of Humberto and
 *    Valentina are modelled as text-only evidence with their own codes: DG
 *    (Georgina's written declaration), DH (notice about Humberto's rewrites)
 *    and DV (notice about Valentina's simultaneous submissions). They are the
 *    items the .md files wait on, and the stages wait on the same codes.
 *    The doc's fail-safes are cierre.md (crossed alibis of Nicolás, Humberto
 *    and Valentina; the table photo, the reading record) and the public copy
 *    of V02. The "any direct question about who closed the file confirms it at
 *    T+21" fail-safe is V04 itself plus Rosaura's confrontation 1.
 *  - The doc requires "accesso al archivo final" as the discriminator between
 *    Rosaura and the others. The print order (V04) therefore carries a line
 *    saying who had edit access to the final file (coordinación editorial);
 *    cierre.md repeats that Nicolás, Humberto and Valentina do not appear in it.
 *  - V01 (cover of the booklet) is an ordinary evidence image; it identifies
 *    the edition ("tirada numerada del 1 al 12") that the later documents
 *    (V04, F2, V05) refer to.
 *  - The page layout of V02/V03 puts the final paragraph on its own line
 *    ("Dentro, ...") in both pages; the design text is a single block and the
 *    line break is layout only. Both texts are exactly 105 words.
 *  - Questions: short case, so 3 to 4 ordinary questions per person (Rosaura
 *    4, Georgina 3+1, Nicolás 3, Humberto 3, Valentina 3) and the 3
 *    confrontation questions for the three people who have one (Rosaura 2+1,
 *    Humberto 3, Valentina 3).
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()). There is
 * NO audio of any kind: the ending is classic, so no confession_script either.
 * `audio` stays in mechanics like the other cases.
 */
return [

    'name' => 'El Libro de los Sustos',
    'version' => '1.0',
    'code' => 'Expediente del club — La lectura anual',
    'authority' => 'Comité del Club de Relatos de Terror de Halloween',

    // Georgina is the person the threat is aimed at, and the one who asked for
    // the comparison. She is also in the roster below as a witness.
    'victim' => [
        'name' => 'Georgina Valls',
        'label' => 'Amenazada',
        'incident' => 'la amenaza dirigida a {name}',
        'photo' => 'georgina-valls.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // Short case (35 min, 5 people): each person gets 3 or 4 ordinary
        // questions (see the stages below), never above this ceiling.
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
        'tagline' => 'El cuento era de Nicolás. El párrafo que asustó a todos, no.',

        'description' => <<<'TXT'
            En la lectura anual del Club de Relatos de Terror de Halloween, un
            cuento anónimo termina con un párrafo que hiela la sala: una
            advertencia disfrazada de ficción, dirigida a la presidenta del
            club, que está sentada a la misma mesa. El autor jura que ese final
            no es el suyo.

            Tu equipo (2 a 6 investigadores, ideal 4) recibe el expediente en
            tiempo real y podrá interrogar a cuatro sospechosos y a la
            presidenta, durante 35 minutos, con ocho piezas de evidencia: dos
            páginas del mismo cuento, la orden de impresión, un acta, una ficha,
            un mensaje y dos fotografías.

            Aquí no hay mensajes cifrados ni acertijos: solo dos páginas
            perfectamente legibles y una pregunta. ¿Quién pudo cambiarlas
            antes de que se leyeran en voz alta?
            TXT,

        // Archivo esperado en public/immersion/el-libro-de-los-sustos/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-libro-de-los-sustos).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'El cuento era de Nicolás. El párrafo que asustó a todos, no.',
            'bullets' => [
                '2 a 6 investigadores comparando dos páginas del mismo cuento, palabra por palabra',
                'Cuatro sospechosos y la presidenta del club a quienes interrogar',
                '35 minutos, sin cifrados ni acertijos: solo lo que se puede leer y comparar',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 35,
        'min_players' => 2,
        'max_players' => 6,

        'price_amount' => 39900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/el-libro-de-los-sustos/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 23,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | Georgina, the threatened person, is the only witness and is modelled like
    | a suspect entry with `accusable => false`.
    |
    */

    'suspects' => [
        'rosaura-mindel' => [
            'name' => 'Rosaura Mindel',
            'role' => 'sospechosa',
            'file' => 'suspects/rosaura-mindel.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                // Confrontation 1: the print order (V04).
                ['questions' => 2, 'after' => ['V04'], 'confrontation' => true],
                // Confrontation 2: the two pages, plus Georgina's declaration.
                ['questions' => 1, 'after' => ['V02', 'V03', 'V04', 'DG'], 'confrontation' => true],
            ]],
            'photo' => 'rosaura-mindel.png',
            'connection' => 'Coordinadora editorial del club; compañera de Georgina en el club',
            'motive' => 'Presionar a Georgina para que renunciara a disputarle la presidencia del club, aprovechando el anonimato de la lectura',
            'alibi' => 'Organizó la lectura y cerró el archivo de impresión; su rol organizativo es lo único que la separa del cambio',
            'public' => [
                'age' => '50 años',
                'profile' => 'Coordinadora editorial del club. Organizada y cordial, lleva años haciendo que la lectura anual salga a tiempo.',
                'facts' => [
                    'Coordina la preparación del cuadernillo de la lectura anual.',
                    'Es compañera de Georgina en el club desde hace años.',
                ],
            ],
        ],
        'nicolas-abarca' => [
            'name' => 'Nicolás Abarca',
            'role' => 'sospechoso',
            'file' => 'suspects/nicolas-abarca.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'nicolas-abarca.png',
            'connection' => 'Autor anónimo del cuento «La puerta del fondo»',
            'motive' => 'Ninguno; su cuento aprobado no contiene ninguna referencia a nadie del club',
            'alibi' => 'Entregó su cuento semanas antes y no tuvo acceso al archivo final de impresión',
            'public' => [
                'age' => '29 años',
                'profile' => 'Autor del cuento «La puerta del fondo». Tímido; prefiere que su nombre no se sepa hasta después de la lectura.',
                'facts' => [
                    'Presentó su cuento de forma anónima, como pide el club.',
                    'Asegura que el final que se leyó no es el que él escribió.',
                ],
            ],
        ],
        'humberto-casal' => [
            'name' => 'Humberto Casal',
            'role' => 'sospechoso',
            'file' => 'suspects/humberto-casal.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 3, 'after' => ['DH'], 'confrontation' => true],
            ]],
            'photo' => 'humberto-casal.png',
            'connection' => 'Miembro veterano del club; rival literario de Georgina',
            'motive' => 'Ninguno respecto a la amenaza; presenta como nuevos relatos que son reescrituras de cuentos propios antiguos',
            'alibi' => 'No tuvo acceso al archivo final de impresión; solo entrega su relato al club',
            'public' => [
                'age' => '52 años',
                'profile' => 'Miembro veterano del club. Seguro de sí mismo y algo condescendiente, defiende con orgullo su trayectoria.',
                'facts' => [
                    'Compite desde hace años con Georgina por el reconocimiento dentro del club.',
                    'Presenta cada año relatos propios a la lectura anual.',
                ],
            ],
        ],
        'valentina-ross' => [
            'name' => 'Valentina Ross',
            'role' => 'sospechosa',
            'file' => 'suspects/valentina-ross.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 3, 'after' => ['DV'], 'confrontation' => true],
            ]],
            'photo' => 'valentina-ross.png',
            'connection' => 'Nueva integrante del club; aspirante a un premio literario del que Georgina es jurado',
            'motive' => 'Ninguno respecto a la amenaza; envía su cuento a varios concursos a la vez',
            'alibi' => 'No tuvo acceso al archivo final de impresión',
            'public' => [
                'age' => '33 años',
                'profile' => 'Nueva integrante del club. Entusiasta y ambiciosa, siempre con una libreta de notas a mano.',
                'facts' => [
                    'Aspira a un premio literario del que Georgina es jurado.',
                    'Se incorporó al club hace poco.',
                ],
            ],
        ],
        'georgina-valls' => [
            'name' => 'Georgina Valls',
            'role' => 'testigo y persona amenazada (no es sospechosa oficial)',
            'file' => 'suspects/georgina-valls.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['DG']],
            ]],
            // Same portrait file as victim.photo.
            'photo' => 'georgina-valls.png',
            'connection' => 'Presidenta del club; la persona a la que apuntaba el párrafo leído',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '45 años',
                'profile' => 'Presidenta del club. Serena y de autoridad tranquila, pidió comparar las dos versiones del cuento la misma noche de la lectura.',
                'facts' => [
                    'Es la persona a la que apuntaba el último párrafo del cuento leído.',
                    'Pidió revisar el cuento original de Nicolás esa misma noche.',
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
        'culprit_slug' => 'rosaura-mindel',

        'headline' => 'Rosaura cambió el párrafo final del cuento de Nicolás al cerrar el archivo de impresión, para presionar a Georgina a no presentarse a la reelección.',

        'motive' => 'Presionar a Georgina para que renunciara a disputarle la presidencia del club en la elección próxima, aprovechando el anonimato de la lectura anual.',

        'method' => 'La víspera de la lectura, al cerrar el archivo final de impresión, sustituyó el último párrafo de la página de Nicolás por una carta vieja «firmada con un nombre que no era el de quien la había escrito» y una voz que advertía de que todos lo sabrían pronto a menos que ella se apartara primero: una referencia a la vieja sospecha de plagio sobre Georgina disfrazada de ficción.',

        'key_evidence' => [
            'Las páginas V02 y V03: idénticas hasta «la única que siempre permanecía cerrada»; desde ahí el párrafo final cambia por completo.',
            'La orden de tirada: Rosaura cerró el archivo de impresión a las 17:42 de la víspera, con edición a cargo de coordinación editorial.',
            'El testimonio de Georgina: el párrafo apuntaba a una vieja sospecha de plagio y el original de Nicolás no lo contenía.',
            'El acta de lectura y la fotografía de la mesa: el ejemplar n.º 7 fue el que se leyó esa noche.',
            'El mensaje de Rosaura del día de la lectura: esperaba un resultado concreto de esa noche.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including the witness (Georgina).
         */
        'exonerations' => [
            'nicolas-abarca' => 'Su propia página aprobada es idéntica a la leída hasta el párrafo final y no contiene ninguna amenaza, de modo que el cambio ocurrió después de que él entregara su cuento; la ficha de selección lo aprobó sin cambios semanas antes y no figura en la orden de tirada ni tuvo acceso al archivo final.',

            'humberto-casal' => 'Sí presentaba como nuevos relatos que eran reescrituras de cuentos suyos antiguos, una falta literaria real pero ajena a la amenaza. No aparece en la orden de tirada ni tuvo acceso de edición al archivo final de impresión, que era de coordinación editorial.',

            'valentina-ross' => 'Sí envió su cuento a varios concursos a la vez, violando la exclusividad de uno de ellos, un asunto distinto de la amenaza. No aparece en la orden de tirada ni tuvo acceso al archivo final de impresión.',

            'georgina-valls' => 'Es la persona a la que apuntaba el párrafo, no quien lo escribió: pidió comparar las dos versiones esa misma noche y su testimonio ayudó a probar el cambio. No tenía motivo alguno para amenazarse a sí misma ni acceso al archivo de impresión.',
        ],
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió realmente con el cuento?',
            'hint' => 'Cuenta qué cambió entre la versión aprobada y la que se leyó, y a quién apuntaba.',
            'points' => 20,
            'guidance' => 'Lo esencial: se cambió por completo el párrafo final del cuento de Nicolás antes de imprimir el ejemplar de lectura, insertando una referencia a la vieja sospecha de plagio de Georgina y una advertencia para que se apartara de la reelección. No exigir aquí cómo se demuestra: se pregunta aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué buscaba conseguir y qué ganaba con ello.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'como_se_demuestra',
                'label' => '¿Cómo se demuestra la intervención editorial?',
                'hint' => 'Qué dicen las dos páginas, la orden de tirada y el testimonio de Georgina.',
                'answer' => 'La página aprobada de Nicolás y la página leída esa noche son idénticas hasta un punto exacto, donde el párrafo final cambia por completo; la orden de tirada confirma que Rosaura cerró el archivo de impresión, y el testimonio de Georgina confirma a quién apunta la referencia insertada.',
                'points' => 30,
                'guidance' => 'Cero si la respuesta se apoya en una errata aislada o en una sola palabra, sin comparar el párrafo completo de las dos páginas y sin la orden de tirada. Puntos completos con la comparación de las dos páginas más la orden de tirada; el testimonio de Georgina suma pero no es indispensable.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+7/T+14/T+21/T+28/T+35 delivery plan
    | into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El libro de los sustos — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 7,
            'title' => 'Expansión — el club, la lectura y la ficha de selección',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['F1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 7,
            'title' => 'Pista privada — una página del archivo editorial',
            'source_file' => 'pista-privada-pagina-autor.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V02'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Desarrollo — la página que se leyó esta noche',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V03'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 21,
            'title' => 'Giro — la orden de tirada y un mensaje',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V04', 'C1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 28,
            'title' => 'Cierre — la mesa, el acta y lo que dijo Georgina',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // DG = Georgina's written (expanded) declaration; DH and DV = the two
            // notices received by the club about Humberto and Valentina. They
            // have no image, so each gets its own code (same device as DF in
            // punto-de-control).
            'evidence_codes' => ['V05', 'F2', 'DG', 'DH', 'DV'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 28,
            'title' => 'Copia pública — la página aprobada del cuento',
            'source_file' => 'copia-pagina-autor.md',
            'delivery_mode' => 'all',
            // Same page as the private clue of T+7: this is the redundancy the
            // design asks for, so no critical data rests on one private piece.
            'evidence_codes' => ['V02'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 35,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para comparar las dos páginas. Queda habilitado el formulario de acusación final: además de quién, qué ocurrió y por qué, el formulario pregunta cómo se demuestra la intervención editorial. Una errata aislada no lo demuestra: hay que comparar el párrafo completo de las dos páginas con la orden de tirada.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-libro-de-los-sustos/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-portada-cuadernillo.png', 'caption' => 'Portada del cuadernillo de la lectura de esta noche'],
        ],
        'expansion.md' => [
            ['file' => 'f1-ficha-de-seleccion.png', 'caption' => 'Ficha de selección del cuento «La puerta del fondo»'],
        ],
        'pista-privada-pagina-autor.md' => [
            ['file' => 'v02-pagina-version-aprobada.png', 'caption' => 'Página del cuento tal como quedó en el archivo editorial'],
        ],
        'desarrollo.md' => [
            ['file' => 'v03-pagina-ejemplar-lectura.png', 'caption' => 'Página del cuento en el ejemplar de lectura de esta noche'],
        ],
        'giro.md' => [
            ['file' => 'v04-orden-de-tirada.png', 'caption' => 'Orden de tirada del cuadernillo'],
            ['file' => 'c1-captura-mensaje.png', 'caption' => 'Captura de un mensaje del día de la lectura'],
        ],
        'cierre.md' => [
            ['file' => 'v05-mesa-de-lectura.png', 'caption' => 'La mesa de lectura, esa noche'],
            ['file' => 'f2-acta-de-lectura.png', 'caption' => 'Acta de la lectura: orden de los cuentos y ejemplar usado'],
        ],
        'copia-pagina-autor.md' => [
            ['file' => 'v02-pagina-version-aprobada.png', 'caption' => 'Página del cuento tal como quedó en el archivo editorial'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v02-pagina-version-aprobada.png', 'caption' => 'La página aprobada: el final es un espejo roto y una voz que repite un nombre'],
            ['file' => 'v03-pagina-ejemplar-lectura.png', 'caption' => 'La página leída: idéntica hasta la puerta del fondo; el párrafo final es otro'],
            ['file' => 'v04-orden-de-tirada.png', 'caption' => 'La orden de tirada: quién cerró el archivo de impresión'],
            ['file' => 'c1-captura-mensaje.png', 'caption' => 'El mensaje del día de la lectura'],
        ],
    ],
];
