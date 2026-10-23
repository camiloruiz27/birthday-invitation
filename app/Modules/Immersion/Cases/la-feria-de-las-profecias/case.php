<?php

/**
 * Case manifest: "La Feria de las Profecías"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a fraud case (a "prediction" that is really a
 * crossing of two administrative lists), classic ending, no confession audio.
 * Hortensia Casals ("Madame Yolanda") is the innocent liar: she lies about
 * having a real gift (cold reading is her trade secret) but she did NOT write
 * the card and read it without knowing its content. Her md keeps the two things
 * apart on purpose. Rogelio Fonseca is the culprit: he saw Dalia's inscription
 * form (pays the stall fee in monthly installments) and his own company's
 * payments sheet (a late installment), crossed them, wrote the card and gave
 * the sealed envelope to Hortensia as a "promotional dynamic".
 *
 * Master sheet (single source of truth for every file of this case). The design
 * has NO clock times, so none are invented: everything is relative.
 *   ~4 semanas antes   Dalia rellena su ficha: cuotas mensuales (4 cuotas), puesto 9.
 *   esas semanas       Rogelio pide a Tobías ver "algunas fichas para verificar
 *                      asignaciones" en el mostrador de acreditaciones.
 *   semana previa      Corte del control de Montajes Cedro: Dalia, puesto 9,
 *                      "Pago atrasado (cuota 2 de 4)".
 *   días antes         Registro de materiales: lote de sobres y papel artesanal de
 *                      Montajes Cedro, destino Almacén norte. Rogelio prepara la
 *                      tarjeta y el sobre.
 *   día de la feria    Mediodía: Rogelio da el sobre sellado a Hortensia (puesto 4),
 *                      "dinámica promocional". Tarde: Hortensia lo abre y lo lee a
 *                      Dalia; Dalia manda su nota de voz (A1); luego habla con
 *                      Tobías y con Walter.
 *   Recinto            Puestos 1–6 (fila norte) y 7–12 (fila sur). Madame Yolanda =
 *                      puesto 4, Ezequiel = puesto 8, Dalia = puesto 9. Almacén
 *                      norte (puestos 1–6 + Montajes Cedro) y Almacén sur (puestos
 *                      7–12 + Textiles Calamar). Caseta de acreditaciones junto a la
 *                      oficina de coordinación.
 *   La tarjeta         "Una carga de números te sigue de cerca, paciente como una
 *                      sombra. Debes resolverla pronto, antes de que todos lo
 *                      sepan." — Madame Yolanda.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - Scoring: the doc's 30/20/20/30 (+ Hortensia penalty) maps to culprit_points
 *    30, weapon ("¿Qué ocurrió realmente?") 20, motive 20 and one extra
 *    question (cruce_de_fuentes) 30. The Hortensia penalty lives in the
 *    `guidance` of the weapon criterion.
 *  - Questions: 5 base for the culprit; 4–5 for the other suspects; witnesses
 *    3–5; confrontations always 3 (the doc's "2 for Rogelio, 1 for the rest" is
 *    folded into the engine rule: Rogelio 2 + 1 in two stages; the rest 3 in one).
 *  - Dalia is both the `victim` (label "Afectada", incident overridden) and a
 *    non-accusable witness of the roster; both entries point to the SAME portrait
 *    file (dalia-rengifo.png), which is one asset.
 *  - New declaration code DT = Tobías's written declaration (the "testimonio de
 *    Tobías ampliado" of T+32), in giro.md. It is the key that gates Tobías,
 *    Walter, Perla, Hortensia (naming Rogelio) and Rogelio's confrontation 1.
 *  - New code HL = "hallazgos laterales" in cierre.md: the doc says T+40 delivers
 *    "los secretos de Perla y Ezequiel" without saying what; they are three
 *    hearsay lines (Perla, Ezequiel and Hortensia's cold reading), text only, no
 *    image. The three confrontations of those suspects wait on HL.
 *  - The design's confrontation of Hortensia admitted two things at once (cold
 *    reading + envelope from Rogelio). Naming Rogelio is now an ordinary stage
 *    after DT (T+32), because her honest answer about the envelope must not be
 *    locked behind her lie; the confrontation (HL, T+40) is only the cold
 *    reading. Before DT she says "alguien del equipo de montaje", no name.
 *  - The private tray (V02, random_player at T+8) delivers the complete ficha,
 *    so it carries the code V02 (as in punto-de-control). The doc puts the public
 *    copy at the Cierre; it is delivered at T+24 instead (complicacion.md), the
 *    moment the doc itself asks the table to compare the three papers, so the
 *    whole table has it long before the confrontations. The same image is
 *    listed twice in the gallery (private event and public copy): one asset.
 *  - The prediction card text is not an image: V04 is the photo of the sobre and
 *    the exact text is transcribed in complicacion.md (the doc asks for the text
 *    "as a separate document with controlled composition").
 *  - The doc names the proveedor only as "el proveedor de mobiliario"; it is
 *    "Montajes Cedro" everywhere (V03, V06, md files). Its sobres and artisan
 *    paper are a "lote de ambientación" managed by the same company, stored in
 *    the Almacén norte, which is the zone shared with Madame Yolanda's stall (V05).
 *  - Hortensia opens the sealed envelope in front of Dalia and learns the content
 *    then; Rogelio hands it over at midday (before the afternoon sessions), so her
 *    "all afternoon in her stall" alibi does not clash with the handover.
 *  - No calendar dates and no clock times are used in any document.
 *  - Walter confirms Rogelio's occasional presence in the accreditation booth only
 *    after DT; before it he only gives the separation of the two accesses.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 * Audio: A1 is Dalia's voice note, delivered at T+24 as an audio_email. There is
 * NO confession audio (classic ending), so `confession_script` is omitted.
 */
return [

    'name' => 'La Feria de las Profecías',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Feria del Velo',
    'authority' => 'Comité de Convivencia de la Feria del Velo',

    'victim' => [
        'name' => 'Dalia Rengifo',
        'label' => 'Afectada',
        'photo' => 'dalia-rengifo.png',
        'incident' => 'el fraude de la falsa predicción contra {name}',
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
        'tagline' => 'La vidente no vio nada. Alguien más ya lo sabía todo.',

        'description' => <<<'TXT'
            En la Feria del Velo, un mercado esotérico de temporada, una
            tarotista recibe un sobre sellado con una predicción tan precisa
            sobre su vida privada que parece imposible. Quien se lo entregó
            jura que no sabía lo que decía. Nadie en la feria sabía lo que
            ella guardaba en silencio.

            Tu equipo (3 a 6 investigadores, ideal 4) recibe el expediente en
            tiempo real y podrá interrogar a cuatro sospechosos, dos testigos y
            a un encargado de acreditaciones durante 45 minutos, comparando
            fichas, listados, un mapa de puestos y un registro de materiales.

            No todo el que miente en una feria es culpable. Separar el oficio
            de cada uno de lo que realmente pasó es lo que lleva hasta el
            sobre.
            TXT,

        // Archivo esperado en public/immersion/la-feria-de-las-profecias/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-feria-de-las-profecias).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'La vidente no vio nada. Alguien más ya lo sabía todo.',
            'bullets' => [
                '3 a 6 investigadores comparando fichas, listados, un mapa y un sobre sellado',
                'Cuatro sospechosos, dos testigos y un encargado de acreditaciones a interrogar',
                '45 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 45,
        'min_players' => 3,
        'max_players' => 6,

        'price_amount' => 49900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/la-feria-de-las-profecias/ (ver ASSET_PROMPTS.md).
        'published' => false,
        'sort_order' => 21,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | The full testimony lives only in content/suspects/*.md. Dalia (the
    | "afectada") is the victim AND a non-accusable witness: both point to the
    | same portrait file.
    |
    */

    'suspects' => [
        'rogelio-fonseca' => [
            'name' => 'Rogelio Fonseca',
            'role' => 'sospechoso',
            'file' => 'suspects/rogelio-fonseca.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['DT', 'V03'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V05', 'V06'], 'confrontation' => true],
            ]],
            'photo' => 'rogelio-fonseca.png',
            'connection' => 'Encargado de montaje y logística de Montajes Cedro, el proveedor de mobiliario de la feria',
            'motive' => 'Presionar económicamente a vendedores con apuros de dinero visibles en los datos que él mismo administraba, cobrando por "resolverlos" discretamente',
            'alibi' => 'Supervisando el montaje por el recinto; sin testigos fijos',
            'public' => [
                'age' => '45 años',
                'profile' => 'Encargado de montaje y logística del proveedor de mobiliario. Práctico y algo brusco.',
                'alibi' => 'Dice haber estado supervisando el montaje durante la tarde.',
                'facts' => [
                    'Su empresa instala el mobiliario de todos los puestos de la feria.',
                    'Conoce el recinto y sus almacenes mejor que casi nadie.',
                ],
            ],
        ],
        'hortensia-casals' => [
            'name' => 'Hortensia Casals ("Madame Yolanda")',
            'role' => 'sospechosa',
            'file' => 'suspects/hortensia-casals.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DT']],
                ['questions' => 3, 'after' => ['HL'], 'confrontation' => true],
            ]],
            'photo' => 'hortensia-casals.png',
            'connection' => 'Lectora de tarot de la feria; colega de Dalia y quien le leyó la predicción',
            'motive' => 'Ninguno respecto al sobre; guarda un secreto de oficio que nada tiene que ver con el fraude',
            'alibi' => 'En su puesto atendiendo lecturas toda la tarde; Tobías y varios asistentes la vieron',
            'public' => [
                'age' => '50 años',
                'profile' => 'Lectora de tarot conocida como Madame Yolanda. Teatral y cálida con el público.',
                'alibi' => 'Dice haber estado en su puesto atendiendo lecturas toda la tarde.',
                'facts' => [
                    'Fue quien leyó la predicción a Dalia durante su sesión.',
                    'Su fama en la feria depende de la confianza de su público.',
                ],
            ],
        ],
        'perla-andrade' => [
            'name' => 'Perla Andrade',
            'role' => 'sospechosa',
            'file' => 'suspects/perla-andrade.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 1, 'after' => ['DT']],
                ['questions' => 3, 'after' => ['HL'], 'confrontation' => true],
            ]],
            'photo' => 'perla-andrade.png',
            'connection' => 'Coordinadora general de la feria',
            'motive' => 'Ninguno respecto al sobre; ha aceptado pagos informales por mejores ubicaciones de puesto',
            'alibi' => 'En la oficina de coordinación toda la tarde; Walter lo confirma',
            'public' => [
                'age' => '42 años',
                'profile' => 'Coordinadora general de la feria. Organizada y cordial, con la agenda siempre llena.',
                'alibi' => 'Dice haber estado en la oficina de coordinación durante toda la tarde.',
                'facts' => [
                    'Supervisa, en términos generales, la asignación de puestos y de accesos.',
                    'Conoce a casi todos los vendedores por su nombre.',
                ],
            ],
        ],
        'ezequiel-dario' => [
            'name' => 'Ezequiel Darío',
            'role' => 'sospechoso',
            'file' => 'suspects/ezequiel-dario.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['HL'], 'confrontation' => true],
            ]],
            'photo' => 'ezequiel-dario.png',
            'connection' => 'Astrólogo de la feria; rival profesional de Hortensia',
            'motive' => 'Ninguno respecto al sobre; usa una aplicación de horóscopos genéricos y la presenta como método ancestral',
            'alibi' => 'En su puesto atendiendo lecturas toda la tarde; varios asistentes lo confirman',
            'public' => [
                'age' => '38 años',
                'profile' => 'Astrólogo de la feria. Carismático y algo pretencioso.',
                'alibi' => 'Dice haber estado en su puesto atendiendo lecturas toda la tarde.',
                'facts' => [
                    'Compite con Hortensia por la atención del público.',
                    'Presume de lecturas personalizadas y ancestrales.',
                ],
            ],
        ],
        'dalia-rengifo' => [
            'name' => 'Dalia Rengifo',
            'role' => 'testigo (afectada, no es sospechosa)',
            'file' => 'suspects/dalia-rengifo.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V04']],
            ]],
            // Same portrait file as victim.photo: one asset, counted once.
            'photo' => 'dalia-rengifo.png',
            'connection' => 'Tarotista de la feria; la persona a quien iba dirigida la predicción',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '34 años',
                'profile' => 'Tarotista de la feria. Inquieta pero contenida, decidida a entender qué pasó.',
                'facts' => [
                    'Recibió la predicción durante su sesión con Madame Yolanda.',
                    'Pidió la investigación en cuanto terminó la sesión.',
                ],
            ],
            'accusable' => false,
        ],
        'tobias-reinoso' => [
            'name' => 'Tobías Reinoso',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/tobias-reinoso.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DT']],
            ]],
            'photo' => 'tobias-reinoso.png',
            'connection' => 'Asistente de acreditaciones; guarda las fichas de inscripción',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '28 años',
                'profile' => 'Asistente de acreditaciones. Joven y atento, algo nervioso al hablar de las fichas.',
                'facts' => [
                    'Entrega las credenciales en el mostrador de acreditaciones.',
                    'Vio pasar por el mostrador a vendedores, proveedores y personal de montaje.',
                ],
            ],
            'accusable' => false,
        ],
        'walter-nunez' => [
            'name' => 'Walter Núñez',
            'role' => 'fuente factual (no es sospechoso oficial)',
            'file' => 'suspects/walter-nunez.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DT']],
            ]],
            'photo' => 'walter-nunez.png',
            'connection' => 'Encargado de acreditaciones; confirma accesos y presencias',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '50 años',
                'profile' => 'Encargado de acreditaciones, con credencial de supervisor. Sereno y formal.',
                'facts' => [
                    'Confirma, sin interpretar, quién tiene acceso oficial a qué.',
                    'Sabe dónde estaba cada persona de coordinación durante la tarde.',
                ],
            ],
            'accusable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The ending (classic: no confession audio, no confession_script)
    |--------------------------------------------------------------------------
    */

    'solution' => [
        'culprit_slug' => 'rogelio-fonseca',

        'headline' => 'La predicción de Dalia no era un don: Rogelio cruzó su ficha de inscripción con el listado de pagos de su propia empresa y se la hizo llegar, sin que lo supiera, a través de Hortensia.',

        'motive' => 'Presionar económicamente a vendedores con apuros de dinero visibles en los datos que él mismo administraba, asustándolos con una "predicción" para después cobrarles por resolver discretamente lo que ella exponía.',

        'method' => 'Cruzó la casilla de pago en cuotas de la ficha de inscripción de Dalia con la entrada de pago atrasado del control interno de su empresa, escribió una tarjeta con ese dato en lenguaje místico, la guardó en un sobre de papel artesanal del lote de ambientación de Montajes Cedro y se la dio a Hortensia como una dinámica promocional pedida por la organización, para que la leyera sin saber qué decía.',

        'key_evidence' => [
            'La ficha de Dalia (cuotas mensuales) y la hoja del proveedor (cuota atrasada), cruzadas, coinciden con la tarjeta.',
            'La declaración de Tobías: Rogelio pidió revisar fichas de inscripción fuera de su rol habitual.',
            'El mapa de puestos: el almacén norte lo comparten el puesto de Madame Yolanda y el proveedor de mobiliario.',
            'El registro de materiales: el lote de sobres y papel artesanal es de Montajes Cedro, con destino a ese almacén.',
            'La nota de voz de Dalia: nunca le contó a nadie en la feria su situación.',
        ],

        'file' => 'solucion.md',

        'exonerations' => [
            'hortensia-casals' => 'Sí miente: dice que su don es real cuando se apoya en lectura fría y observación, pero ese es un secreto de oficio sin relación con el sobre. Leyó la tarjeta creyendo que era una dinámica promocional de la organización y sin saber qué decía; no tuvo acceso a la ficha ni al listado del proveedor, y estuvo en su puesto toda la tarde.',

            'perla-andrade' => 'Sí aceptó pagos informales por mejores ubicaciones de puesto, una falta real pero ajena al sobre. Estuvo en la oficina de coordinación toda la tarde, Walter lo confirma, y coordinación no tiene acceso al control interno del proveedor.',

            'ezequiel-dario' => 'Sí usa una aplicación de horóscopos genéricos y la presenta como lectura ancestral, una exageración de oficio sin relación con el sobre. Estuvo en su puesto toda la tarde y no tuvo acceso a ninguna de las dos listas.',

            'dalia-rengifo' => 'Es la afectada, no una sospechosa: recibió la predicción, no la preparó y nunca le contó a nadie en la feria su situación. Su ficha y su cuota atrasada son los datos que otra persona usó contra ella.',

            'tobias-reinoso' => 'Es un testigo, no un sospechoso: dejó ver fichas a Rogelio sin imaginar para qué y fue quien puso la petición inusual sobre la mesa. No tuvo motivo ni acceso al listado del proveedor.',

            'walter-nunez' => 'Es una fuente factual, no un sospechoso: confirmó quién tiene acceso oficial a qué y dónde estaba Perla. No tuvo motivo ni acceso al listado del proveedor.',
        ],
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió realmente con la predicción?',
            'hint' => 'Cuenta de dónde salió el contenido de la tarjeta, quién la preparó y cómo llegó a manos de Dalia sin que quien la leyó lo supiera.',
            'points' => 20,
            'guidance' => 'Lo esencial: el responsable fabricó la predicción a partir de datos reales de Dalia y se la hizo llegar a través de Hortensia con un pretexto promocional, sin que ella supiera su contenido ni su propósito. Cero si presenta a Hortensia como autora o cómplice consciente, o si trata su lectura fría como si fuera el delito.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué buscaba el responsable y a quién iba dirigido.',
            'points' => 20,
            'guidance' => 'Lo esencial: presionar económicamente a vendedores con apuros de dinero visibles en los datos que administraba, para cobrarles por "resolver" lo que la predicción exponía.',
        ],
        'questions' => [
            [
                'key' => 'cruce_de_fuentes',
                'label' => '¿Cómo se demuestra que la predicción salió de cruzar dos fuentes?',
                'hint' => 'Nombra los dos documentos, qué dato aporta cada uno y qué otras pruebas unen al responsable con ambos.',
                'answer' => 'La ficha de inscripción de Dalia (pago en cuotas) y el control de Montajes Cedro (pago atrasado), cruzados, coinciden con el contenido de la tarjeta. Tobías confirma que Rogelio pidió ver fichas fuera de su rol, y el registro de materiales liga el sobre y el papel con el lote de Montajes Cedro, guardado en el almacén que comparte con el puesto de Madame Yolanda.',
                'points' => 30,
                'guidance' => 'Puntos completos con las dos fuentes (ficha: cuotas; hoja del proveedor: cuota atrasada) y al menos una de las dos pruebas que ligan al responsable (declaración de Tobías o registro de materiales). Parcial con solo las dos fuentes. Cero si atribuye la predicción a un don o a la persona que la leyó.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/8/16/24/32/40/45 into
    | trigger_offset_minutes (T+0 -> 1, same convention as the other cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Feria de las Profecías — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 8,
            'title' => 'Expansión — quién es quién en la feria',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 8,
            'title' => 'Pista privada — una ficha de inscripción',
            'source_file' => 'pista-privada-ficha-dalia.md',
            'delivery_mode' => 'random_player',
            // The complete ficha: a full piece, so it carries its code. The public
            // copy arrives at T+24 (complicacion.md).
            'evidence_codes' => ['V02'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 16,
            'title' => 'Desarrollo — el control del proveedor de mobiliario',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V03'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Complicación — el sobre y lo que decía',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // V02 again: the public copy of the private tray.
            'evidence_codes' => ['V02', 'V04'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 24,
            'title' => 'Audio — la nota de voz de Dalia',

            'audio_file' => 'a1-nota-de-voz-dalia.mp3',
            'audio_scene' => 'Nota de voz grabada con el teléfono, en un rincón apartado de una feria nocturna: murmullo distante de gente y música ambiental tenue de fondo, sin música dramática.',
            'audio_context' => 'Dalia, una mujer adulta de 34 años, habla en voz baja y contenida a una amiga: está desconcertada por una predicción inexplicablemente precisa sobre un tema privado y decidida a averiguar qué pasa. Inquieta, no asustada hasta el pánico. Duración: 10–15 segundos.',
            'audio_speaker' => 'Speaker 1 - Dalia Rengifo',
            'audio_voice' => 'Kore',
            'audio_script' => <<<'TXT'
                No sé cómo lo supo. Nunca le dije a nadie aquí que estoy pagando el puesto en cuotas. La predicción lo dejó clarísimo, como una advertencia. Voy a averiguar qué está pasando.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 32,
            'title' => 'Giro — quién pudo mirar los dos papeles',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DT = Tobías's written declaration (the body of giro.md), no image.
            'evidence_codes' => ['V05', 'DT'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 40,
            'title' => 'Cierre — el registro de materiales y los hilos sueltos',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // HL = the three side findings (Perla, Ezequiel, Hortensia's cold
            // reading), text only.
            'evidence_codes' => ['V06', 'HL'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 45,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, qué ocurrió y por qué, el formulario pregunta cómo se demuestra que la predicción salió de cruzar dos fuentes de datos.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-feria-de-las-profecias/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-zona-acreditacion.png', 'caption' => 'La zona de acreditación, al atardecer'],
        ],
        'pista-privada-ficha-dalia.md' => [
            ['file' => 'v02-ficha-inscripcion-dalia.png', 'caption' => 'Ficha de inscripción de Dalia Rengifo'],
        ],
        'desarrollo.md' => [
            ['file' => 'v03-control-proveedor.png', 'caption' => 'Control de asignaciones de servicio de Montajes Cedro'],
        ],
        'complicacion.md' => [
            ['file' => 'v04-sobre-mesa-lectura.png', 'caption' => 'El sobre, sobre la mesa de lectura del puesto de Madame Yolanda'],
            ['file' => 'v02-ficha-inscripcion-dalia.png', 'caption' => 'Ficha de inscripción de Dalia Rengifo (copia para toda la mesa)'],
        ],
        'giro.md' => [
            ['file' => 'v05-mapa-puestos.png', 'caption' => 'Mapa de puestos de la Feria del Velo'],
        ],
        'cierre.md' => [
            ['file' => 'v06-registro-materiales.png', 'caption' => 'Registro de control de suministros de la feria'],
        ],

        // The reveal re-shows the chain of evidence that convicts.
        'solucion.md' => [
            ['file' => 'v02-ficha-inscripcion-dalia.png', 'caption' => 'La mitad del dato: Dalia paga su cuota en cuotas mensuales'],
            ['file' => 'v03-control-proveedor.png', 'caption' => 'La otra mitad: la cuota de Dalia figura atrasada'],
            ['file' => 'v05-mapa-puestos.png', 'caption' => 'El almacén norte lo comparten el puesto de Madame Yolanda y el proveedor'],
            ['file' => 'v06-registro-materiales.png', 'caption' => 'El lote de sobres y papel artesanal es del proveedor'],
        ],
    ],
];
