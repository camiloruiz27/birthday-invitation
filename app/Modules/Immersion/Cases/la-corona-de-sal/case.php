<?php

/**
 * Case manifest: "La Corona de Sal"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a fraud case with a derived, non-grave assault. The
 * permit of Rosaura Benavides is AUTHENTIC and nobody forged it from scratch:
 * what changed is the SCOPE that circulated commercially. The reserve's
 * issuance register (V04) says Verbena de Sal, Zona 1, and nothing else; the
 * copy Mauricio (her son, the workshop's commercial manager) sent to suppliers
 * and buyers (V03) adds Junco Costero and the Zona 2, never approved. He did
 * it to fulfil an urgent hotel order (V06, C1) and cover a personal debt;
 * when Rosaura found Junco Costero among the fibres, compared both versions
 * and demanded he cancel the order, he pushed her and she hurt her arm. The
 * environmental rules and the reserve are fictional: no real law is named.
 *
 * Reference timetable (single source of truth for every file of this case).
 * The design gives no clock times, only relative moments, so the case runs on
 * these short DATES (day and month, no year, no weekday) and on "esa tarde":
 *   ~4 nov    Permit issued to Rosaura (V04 register row: "Expedición 4 nov";
 *             Verbena de Sal, Zona 1). "Semanas antes".
 *   Weeks before, separately: Mauricio takes on a personal debt.
 *   2 dic     Hoteles Marejada order (V06: issued 2 dic, delivery 12 dic, 120
 *             coronas, no extension) and the chat C1 (Esteban, dated 2 dic).
 *   2-4 dic   Mauricio circulates the widened copy (V03; it keeps the original
 *             number and issue date, only the scope changes) and has Junco
 *             Costero gathered in Zona 2 with a hired day labourer.
 *   4 dic     The lot arrives at the workshop (V05 label: "Recepción 4 dic").
 *             Tobías unloads it; Rosaura is not there. Rosaura tells Petronila
 *             "un par de días antes" of the injury that something does not fit.
 *   6 dic     "Esa tarde" (afternoon, the feria is open): Rosaura notices the
 *             Junco, records A1, compares V03 vs her own register, confronts
 *             Mauricio at the workshop door (Araceli sees the argument, then
 *             Rosaura leaving holding her arm) and he pushes her.
 *   12 dic    Urgent delivery date of the hotel order.
 * Alibis for "esa tarde": Esteban in a meeting in the capital; Herminia at her
 * feria stall; Tobías setting up the workshop's stall at the feria; Leonor in
 * the environmental office (Amancio confirms). No clock times anywhere.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 4-component score
 *    (30/20/20/30 and the "simple environmental infraction" penalty) becomes
 *    culprit_points 30 + weapon ("¿qué ocurrió realmente?") 20 + motive 20 +
 *    one extra question ("cómo se demuestra la manipulación del alcance") 30;
 *    the penalty is a `guidance` line on the motive. The unlock event asks for
 *    these elements inside the free-text fields.
 *  - Rosaura is the `victim` with label "Afectada" (she is alive, injured, no
 *    death) and `incident` "la lesión de {name}", so the gateway prompts speak
 *    of "la lesión de Rosaura Benavides". She is NOT interrogable and not in
 *    the roster; her voice is audio A1, a timeline event. The doc never says
 *    why she cannot testify: the opener says she is under medical observation
 *    and cannot declare for now (invented, harmless).
 *  - Questions: 3-5 normal per person plus 3 of confrontation for the five
 *    suspects, opened by `interrogation.stages`. The doc's "Mauricio 2
 *    confrontations, the others 1" becomes 2+1 stages for Mauricio (2 after
 *    V04+V05, 1 after DT+V06) and a 3-question confrontation stage for each of
 *    the other four, waiting on CR (the T+45 cierre).
 *  - The doc gates nothing on codes; the following codes were created so the
 *    .md files can condition facts on delivered evidence: FE (species sheet,
 *    no image-bearing event of its own), DT (the written declarations of
 *    Araceli and Petronila, in cierre.md; the doc's "testimonios ampliados"),
 *    CR (the cierre's cross-alibis and "asuntos aparte"; the doc's "secretos
 *    de Esteban, Herminia, Tobías y Leonor"). Before DT, Araceli and Petronila
 *    answer vaguely and truthfully; before V04, Amancio only says that the
 *    register is what counts (fail-safe after V04 is in his md).
 *  - V03 private tray: one random_player event at T+18 carrying the complete
 *    piece (code V03), plus the public copy inside the T+27 `complicacion` (all
 *    players, codes V03 + V04), as the doc asks for redundancy. Nothing
 *    critical depends on the private copy.
 *  - The ficha de especies is delivered public at T+18 (event FE). The doc's
 *    text said Junco Costero is "NO incluido en el registro original de
 *    Rosaura"; that phrase would give away the discrepancy nine minutes before
 *    V04, so the sheet says where each plant grows (Verbena: Zona 1; Junco:
 *    Zona 2) and nothing about the register.
 *  - Map V02: the doc says it marks "cuál zona está autorizada". Marking Zona 2
 *    as plainly forbidden would make V03 look wrong at T+18, so the map states
 *    the general rule: Zona 1 "uso artesanal con permiso", Zona 2 "protección:
 *    recolección solo con autorización especial por zona" (no mention of
 *    Rosaura, no list of permits).
 *  - A1 (Rosaura's voice note, "esa misma tarde") is not in the doc's
 *    timeline: it is delivered at T+36 as an audio_email next to the V05
 *    photo. It has no confession: the ending is `epilogos_personalizados`, so
 *    there is NO confession_script (exonerations are complete because the
 *    epilogue uses them).
 *  - C1 (chat Mauricio-Esteban) and the secrets of the four suspects are
 *    delivered at T+45 in cierre.md together with V06 and the written
 *    declarations (the doc's Cierre list). The cierre also carries the doc's
 *    fail-safe of crossed alibis; the other fail-safe (Amancio confirming the
 *    scope on any direct question) lives in his md after V04.
 *  - N5 cites Petronila's testimony for the debt, but the doc gives Petronila
 *    only "something does not fit". Her declaration (DT) now adds that Rosaura
 *    told her Mauricio was worried about a debt and in a hurry to close the
 *    big order; it is the only place outside his confession where the motive
 *    shows up, and it appears at T+45.
 *  - Esteban "no tuvo acceso al documento": in his md he never saw the text
 *    of the permit (Mauricio told him by phone that all was in order); V03
 *    circulated among suppliers. Who gathered the Junco is not in the doc:
 *    Mauricio says he hired a day labourer who knew nothing of the permit
 *    (V05 explicitly does not show who collected it).
 *  - Dates: the doc fixes none, but V04, V05, V06 and C1 need them ("fecha de
 *    recepción", "fecha de entrega urgente"); they follow the table above and
 *    are plot dates, short, without year.
 *  - Difficulty: the doc says "baja"; it becomes 'medium' (only medium/hard).
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'La Corona de Sal',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Taller Benavides, permiso de recolección',
    'authority' => 'Mesa de Verificación de la Feria Navideña',

    'victim' => [
        'name' => 'Rosaura Benavides',
        'label' => 'Afectada',
        'incident' => 'la lesión de {name}',
        'photo' => 'rosaura-benavides.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 3 preguntas por sospechoso + 2 confrontaciones
        // para Mauricio y 1 para los demás. The engine uses 3-5 normal questions
        // per person plus 3 of confrontation (see the notes above).
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
        'tagline' => 'El permiso es real. Lo que dice, no siempre fue lo mismo.',

        'description' => <<<'TXT'
            En un pueblo de costa, en plena feria navideña, una artesana
            reconocida sale de su taller con el brazo lastimado. Lo único claro
            es un papel: el permiso con el que el taller recolecta en el
            humedal vecino. Es auténtico, está a su nombre y circula entre
            proveedores y compradores. Pero no todas las copias dicen lo mismo.

            Tu equipo (3 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco personas del entorno del
            taller, a dos testigos y al responsable de la reserva, durante 55
            minutos, comparando documentos, fotografías y una ficha de dos
            plantas. No hace falta saber de botánica ni de leyes.

            Cada uno tiene algo que callar, pero no todos callan lo mismo.
            Descubrir cuál de esos secretos importa es lo que lleva hasta ella.
            TXT,

        // Archivo esperado en public/immersion/la-corona-de-sal/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-corona-de-sal).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'El permiso es real. Lo que dice, no siempre fue lo mismo.',
            'bullets' => [
                '3 a 7 investigadores comparando un permiso, un registro y una ficha de plantas',
                'Cinco sospechosos, dos testigos y la reserva del humedal a quienes interrogar',
                '55 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 55,
        'min_players' => 3,
        'max_players' => 7,

        'price_amount' => 44900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/la-corona-de-sal/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 26,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | Araceli and Petronila (witnesses) and Amancio (the reserve) are modeled
    | like a suspect entry with 'accusable' => false. Rosaura, the injured
    | woman, is the "victim" and is not interrogable.
    |
    */

    'suspects' => [
        'mauricio-benavides' => [
            'name' => 'Mauricio Benavides',
            'role' => 'sospechoso',
            'file' => 'suspects/mauricio-benavides.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V04', 'V05'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['DT', 'V06'], 'confrontation' => true],
            ]],
            'photo' => 'mauricio-benavides.png',
            'connection' => 'Hijo de Rosaura y encargado comercial del Taller Benavides',
            'motive' => 'Cubrir una deuda personal urgente con las ganancias del pedido grande, que dependía de material no autorizado',
            'alibi' => 'Dice haber estado esa tarde entre la feria y los clientes; sin coartada que lo confirme',
            'public' => [
                'age' => '50 años',
                'profile' => 'Hijo de Rosaura y encargado comercial del taller. Cordial, familiar, muy ligado a la marcha del negocio.',
                'alibi' => 'Dice que esa tarde estuvo atendiendo la feria y a los clientes.',
                'facts' => [
                    'Se encarga de los compradores, los proveedores y los pedidos del taller.',
                    'Envía copia del permiso del taller a quienes se la piden.',
                ],
            ],
        ],
        'esteban-lacroix' => [
            'name' => 'Esteban Lacroix',
            'role' => 'sospechoso',
            'file' => 'suspects/esteban-lacroix.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['C1']],
                ['questions' => 3, 'after' => ['CR'], 'confrontation' => true],
            ]],
            'photo' => 'esteban-lacroix.png',
            'connection' => 'Representante de una cadena hotelera; compró el pedido grande de coronas',
            'motive' => 'Ninguno respecto al caso; negocia en secreto con un taller competidor como plan alternativo',
            'alibi' => 'En una reunión comercial en la capital toda la tarde',
            'public' => [
                'age' => '45 años',
                'profile' => 'Representante de una cadena hotelera y comprador del pedido grande de la temporada. Profesional y seguro de sí mismo.',
                'alibi' => 'Dice haber estado esa tarde en una reunión comercial en la capital.',
                'facts' => [
                    'Hizo al taller un pedido grande, con fecha de entrega urgente.',
                    'Su cadena depende de que las coronas lleguen a tiempo para la temporada.',
                ],
            ],
        ],
        'herminia-casallas' => [
            'name' => 'Herminia Casallas',
            'role' => 'sospechosa',
            'file' => 'suspects/herminia-casallas.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['CR'], 'confrontation' => true],
            ]],
            'photo' => 'herminia-casallas.png',
            'connection' => 'Artesana del pueblo; compite con el taller de Rosaura por el mercado de temporada',
            'motive' => 'Ninguno respecto al caso; usa pequeñas cantidades de material sin permiso, por necesidad',
            'alibi' => 'En su propio puesto de la feria toda la tarde',
            'public' => [
                'age' => '48 años',
                'profile' => 'Artesana del pueblo con puesto propio en la feria. Cordial, algo competitiva.',
                'alibi' => 'Dice haber estado esa tarde en su puesto de la feria.',
                'facts' => [
                    'Vende en la feria el mismo tipo de coronas que el taller de Rosaura.',
                    'Se beneficiaría de cualquier tropiezo del taller en plena temporada.',
                ],
            ],
        ],
        'tobias-reyes' => [
            'name' => 'Tobías Reyes',
            'role' => 'sospechoso',
            'file' => 'suspects/tobias-reyes.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V05']],
                ['questions' => 3, 'after' => ['CR'], 'confrontation' => true],
            ]],
            'photo' => 'tobias-reyes.png',
            'connection' => 'Aprendiz del Taller Benavides',
            'motive' => 'Ninguno respecto al caso; vende coronas propias hechas con material sobrante del taller',
            'alibi' => 'Montando el puesto del taller en la feria con otros expositores toda la tarde',
            'public' => [
                'age' => '24 años',
                'profile' => 'Aprendiz del taller. Joven, algo nervioso, muy aplicado en el oficio.',
                'alibi' => 'Dice haber pasado la tarde montando el puesto del taller en la feria.',
                'facts' => [
                    'Descarga y ordena el material que llega al taller.',
                    'Trabaja cada día con las mismas fibras con las que se hacen las coronas.',
                ],
            ],
        ],
        'leonor-izaguirre' => [
            'name' => 'Leonor Izaguirre',
            'role' => 'sospechosa',
            'file' => 'suspects/leonor-izaguirre.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['CR'], 'confrontation' => true],
            ]],
            'photo' => 'leonor-izaguirre.png',
            'connection' => 'Inspectora de permisos de la oficina ambiental; su oficina tramitó el permiso de Rosaura',
            'motive' => 'Ninguno respecto al caso; acepta pequeñas atenciones de varios talleres a cambio de agilizar trámites',
            'alibi' => 'En la oficina ambiental toda la tarde, atendiendo otros trámites',
            'public' => [
                'age' => '52 años',
                'profile' => 'Inspectora de permisos de la oficina ambiental. Formal, cordial, muy apegada al procedimiento.',
                'alibi' => 'Dice haber estado esa tarde en su oficina atendiendo otros trámites.',
                'facts' => [
                    'Su oficina tramitó el permiso de recolección del taller de Rosaura.',
                    'Trata a diario con los talleres de la zona que recolectan en el humedal.',
                ],
            ],
        ],
        'araceli-montoya' => [
            'name' => 'Araceli Montoya',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/araceli-montoya.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DT']],
            ]],
            'photo' => 'araceli-montoya.png',
            'connection' => 'Visitante de la feria navideña; pasó junto al taller esa tarde',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '40 años',
                'profile' => 'Visitante de la feria navideña. Amable y observadora, ajena a la familia del taller.',
                'facts' => [
                    'Paseó por la feria esa tarde y pasó junto al taller.',
                    'No conoce a la familia del taller más que de vista.',
                ],
            ],
            'accusable' => false,
        ],
        'petronila-vargas' => [
            'name' => 'Petronila Vargas',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/petronila-vargas.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DT']],
            ]],
            'photo' => 'petronila-vargas.png',
            'connection' => 'Amiga y colega artesana de Rosaura',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '58 años',
                'profile' => 'Amiga de muchos años y colega artesana de Rosaura. Cálida, leal y de pocas palabras.',
                'facts' => [
                    'Habló con Rosaura en los días previos a lo ocurrido.',
                    'Conoce bien cómo trabaja Rosaura y cuánto cuida sus papeles.',
                ],
            ],
            'accusable' => false,
        ],
        'amancio-ferrua' => [
            'name' => 'Amancio Ferrúa',
            'role' => 'fuente de la reserva (no es sospechoso oficial)',
            'file' => 'suspects/amancio-ferrua.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V04']],
            ]],
            'photo' => 'amancio-ferrua.png',
            'connection' => 'Responsable de la Reserva del Humedal',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '55 años',
                'profile' => 'Responsable de la Reserva del Humedal. Sobrio, exacto, de trato cortés.',
                'facts' => [
                    'Conoce las dos zonas del humedal y qué se puede recolectar en cada una.',
                    'Custodia el registro de expedición de los permisos de la reserva.',
                ],
            ],
            'accusable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The ending
    |--------------------------------------------------------------------------
    |
    | Epílogos personalizados: no confession_script (the design has no audio
    | confession). Exonerations are complete because the epilogue uses them.
    |
    */

    'solution' => [
        'culprit_slug' => 'mauricio-benavides',

        'headline' => 'El permiso de Rosaura es real, pero Mauricio amplió su alcance para cumplir un pedido urgente y pagar una deuda; cuando ella lo descubrió, la empujó.',

        'motive' => 'Cubrir una deuda personal urgente con las ganancias del pedido grande de la cadena hotelera, que dependía de material no autorizado; no quiso cancelar el pedido cuando Rosaura se lo exigió.',

        'method' => 'Hizo circular una copia del permiso con Junco Costero y la Zona 2 añadidos (el registro de la reserva solo autoriza Verbena de Sal en la Zona 1), mandó recolectar ese material sin decírselo a su madre y, cuando ella lo confrontó junto a la puerta del taller, la empujó: Rosaura cayó y se lesionó el brazo, una lesión real pero no grave.',

        'key_evidence' => [
            'El registro de expedición de la reserva: Rosaura Benavides, Verbena de Sal, Zona 1; Amancio lo confirma y no consta ninguna ampliación.',
            'El permiso presentado: mismo permiso, mismo número y fecha, pero con Junco Costero y las Zonas 1 y 2.',
            'Las coronas terminadas y la ficha de especies: hay Junco Costero entre las piezas.',
            'La foto del taller: el material con Junco Costero llegó dos días después del pedido urgente.',
            'El pedido comercial y los mensajes con Esteban: plazo de diez días sin prórroga y un comprador que no admitía retrasos.',
            'La nota de voz de Rosaura y las declaraciones de Araceli y Petronila: Rosaura sospechó, comparó los papeles, discutió con Mauricio esa tarde y salió sujetándose el brazo.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including both witnesses and the reserve.
         */
        'exonerations' => [
            'esteban-lacroix' => 'Sí presionó por el plazo y sí negociaba en secreto con otro taller como plan alternativo, una falta real pero ajena al fraude. Estuvo toda la tarde en una reunión comercial en la capital, confirmada por la agenda de su cadena, y nunca tuvo el documento del permiso en sus manos.',

            'herminia-casallas' => 'Sí usa pequeñas cantidades de material sin permiso, por necesidad, y compite con el taller de Rosaura; son un asunto distinto y menor, sin relación con el permiso de Rosaura. Estuvo toda la tarde en su puesto de la feria, a la vista de sus vecinas de puesto.',

            'tobias-reyes' => 'Sí vende por su cuenta coronas hechas con material sobrante del taller, una falta menor y aparte. No recolectó nada y solo descargó el lote con las indicaciones de Mauricio; esa tarde montaba el puesto del taller en la feria con otros expositores.',

            'leonor-izaguirre' => 'Sí acepta pequeñas atenciones de varios talleres para agilizar trámites en general, un favoritismo administrativo menor y sin relación específica con este permiso. Estuvo toda la tarde en la oficina ambiental, como confirma Amancio, y el registro de la reserva coincide con lo que ella tramitó.',

            'araceli-montoya' => 'Es una testigo, no una sospechosa: solo contó que vio a Mauricio y a Rosaura discutiendo junto al taller y, poco después, a Rosaura alterada y sujetándose el brazo. No oyó de qué discutían ni tenía motivo alguno.',

            'petronila-vargas' => 'Es una testigo, no una sospechosa: solo repitió lo que Rosaura le dijo, que algo no cuadraba con el material y que Mauricio andaba preocupado por una deuda y con prisa por el pedido. No estuvo en el taller ni tenía motivo alguno.',

            'amancio-ferrua' => 'Es el responsable de la reserva, no un sospechoso: se limitó a confirmar lo anotado en el registro de expedición (Verbena de Sal, Zona 1, sin ampliación) y que Leonor estuvo en la oficina. No interpretó intenciones ni tuvo relación con el pedido.',
        ],
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió realmente?',
            'hint' => 'Cuenta qué hizo el responsable con el permiso, qué descubrió Rosaura y qué pasó cuando ella lo confrontó.',
            'points' => 20,
            'guidance' => 'Lo esencial: amplió el alcance del permiso (Junco Costero y Zona 2) para cumplir el pedido urgente, Rosaura lo descubrió y lo confrontó, y él la empujó, lesionándole el brazo sin gravedad. No exigir aquí la demostración con los documentos: se pregunta aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué necesitaba el responsable y de qué dependía. Nombra el motivo personal, no solo la infracción.',
            'points' => 20,
            'guidance' => 'Puntos completos con: deuda personal urgente cubierta con las ganancias del pedido comercial, que dependía del material no autorizado. Cero si lo trata como una simple infracción ambiental o como evitar ser descubierto, sin nombrar el motivo personal.',
        ],
        'questions' => [
            [
                'key' => 'demostracion_alcance',
                'label' => '¿Cómo se demuestra que se manipuló el alcance del permiso?',
                'hint' => 'Qué documentos se comparan, qué dice cada uno y qué prueba que la recolección adicional ocurrió para cumplir el pedido.',
                'answer' => 'El registro de expedición de la reserva confirma que el permiso original solo autorizaba Verbena de Sal en la Zona 1; el permiso presentado comercialmente incluye además Junco Costero y la Zona 2, nunca aprobados; y las coronas terminadas, junto con la fecha de llegada del material en la foto del taller (días después del pedido urgente), confirman que esa recolección adicional ocurrió para cumplir ese pedido.',
                'points' => 30,
                'guidance' => 'Pedir la comparación de los dos documentos (registro vs. permiso presentado) y al menos otra prueba (coronas o ficha de especies, foto del taller o pedido). Cero si dice que el permiso era falso desde cero: es auténtico, solo cambió su alcance presentado.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+9/T+18/T+27/T+36/T+45/T+55 plan
    | into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La corona de sal — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 9,
            'title' => 'Expansión — el humedal, el taller y la gente alrededor',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V02'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 18,
            'title' => 'Desarrollo — la ficha de dos plantas',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['FE'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 18,
            'title' => 'Pista privada — el permiso, tal como circuló',
            'source_file' => 'pista-privada-permiso.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V03'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 27,
            'title' => 'Complicación — dos documentos sobre el mismo permiso',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V03', 'V04'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Giro — lo que había sobre la mesa del taller',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V05'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 36,
            'title' => 'Audio — la nota de voz de Rosaura',

            'audio_file' => 'a1-nota-voz-rosaura.mp3',
            'audio_scene' => 'Nota de voz grabada con el teléfono en el interior de un taller artesanal costero, con un leve ambiente de taller de fondo (fibras, una mesa de trabajo, el rumor lejano de la feria); sin música.',
            'audio_context' => 'Rosaura, una mujer adulta de unos 55 años, graba para sí misma, con calma pero algo intrigada, que algo no cuadra con el material que llegó esta semana. Suena como quien nota una discrepancia administrativa en su propio negocio y decide investigarla con calma. Ningún tono de miedo, de peligro ni de presagio. Duración: 10-15 segundos.',
            'audio_speaker' => 'Speaker 1 - Rosaura Benavides',
            'audio_voice' => 'Vindemiatrix',
            'audio_script' => <<<'TXT'
                Algo no cuadra con el material que llegó esta semana. Voy a comparar el permiso con lo que realmente tengo registrado, antes de que esto siga creciendo.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 45,
            'title' => 'Cierre — motivo, testimonios por escrito y asuntos pendientes',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // DT = the written declarations of Araceli and Petronila, CR = the
            // crossed alibis and the four side matters; neither has an image
            // of its own (same device as DF/TP/DECL-PAOLA in other cases).
            'evidence_codes' => ['V06', 'C1', 'DT', 'CR'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 55,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace algunas preguntas sobre el caso. En el campo "por qué" nombren el motivo personal de quien acusan, no solo la infracción; y la última pregunta pide explicar cómo se demuestra, con los documentos y las pruebas, que se manipuló el alcance del permiso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-corona-de-sal/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-coronas-terminadas.png', 'caption' => 'Las coronas terminadas que el taller exhibe esta temporada'],
        ],
        'expansion.md' => [
            ['file' => 'v02-mapa-humedal.png', 'caption' => 'Mapa de orientación del humedal, con sus dos zonas'],
        ],
        'desarrollo.md' => [
            ['file' => 'ficha-especies.png', 'caption' => 'Ficha de especies: Verbena de Sal y Junco Costero'],
        ],
        'pista-privada-permiso.md' => [
            ['file' => 'v03-permiso-presentado.png', 'caption' => 'Copia del permiso de recolección, tal como circuló entre proveedores'],
        ],
        'complicacion.md' => [
            ['file' => 'v04-registro-expedicion.png', 'caption' => 'Registro de expedición de la Reserva del Humedal'],
            ['file' => 'v03-permiso-presentado.png', 'caption' => 'Permiso de recolección presentado a proveedores y compradores'],
        ],
        'giro.md' => [
            ['file' => 'v05-foto-taller.png', 'caption' => 'El taller, con el material recién llegado'],
        ],
        'cierre.md' => [
            ['file' => 'v06-pedido-comercial.png', 'caption' => 'Pedido comercial de temporada'],
            ['file' => 'c1-mensajes-pedido.png', 'caption' => 'Intercambio de mensajes sobre el pedido, de días antes'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v04-registro-expedicion.png', 'caption' => 'Lo que la reserva aprobó: Verbena de Sal, Zona 1'],
            ['file' => 'v03-permiso-presentado.png', 'caption' => 'Lo que circuló: Junco Costero y la Zona 2 añadidos'],
            ['file' => 'v05-foto-taller.png', 'caption' => 'El material llegó con fecha, días después del pedido'],
            ['file' => 'v06-pedido-comercial.png', 'caption' => 'El pedido que no admitía retrasos'],
        ],
    ],
];
