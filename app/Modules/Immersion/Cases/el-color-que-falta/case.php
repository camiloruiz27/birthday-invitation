<?php

/**
 * Case manifest: "El Color que Falta"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "two acts, one story" disappearance. The table
 * tends to treat the missing colour as an aesthetic detail, then to fuse the
 * colour with the disappearance. They are separate acts of separate people,
 * tied by one truth (who really designed the collection):
 *  - Daniela removed the "verde Aurora" from the final production, citing a
 *    vague instruction from Valeria, so the colour would not reveal Camila's
 *    authorship on the runway.
 *  - Marco, who discovered the appropriation on his own, confronted Valeria
 *    that afternoon and, when she tried to leave, kept her (and her phone) in
 *    a backstage office. Valeria is alive and safe, held without physical
 *    violence.
 * The colour is identified ONLY by its written name and by redundant documents
 * (V1, V2, V4, V11): no evidence may depend on fine chromatic perception.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text weapon/method + free-text motive. The doc's 5-component form and
 *    point scoring are NOT implemented; what happened to Valeria goes in
 *    "método", and the authorship and the colour removal are asked for inside
 *    the free-text motive (see the T+60 unlock text). The doc's penalty for
 *    "colour as an aesthetic decision" is likewise not implemented.
 *  - Question budget: every interrogable person has 5 questions and 3 more
 *    when the case has confrontations for them (8), instead of the doc's
 *    "4 + 2/1 confrontations" (the JSON even mixes 2/2/1/1/1 confrontations).
 *    They open in stages (`interrogation.stages`): confrontation stages wait
 *    on the evidence that justifies them; Camila's and Sofía's authorship
 *    answers wait on V4; Paola's testimony on DECL-PAOLA / V8 / V9.
 *  - V6 does not exist: the design omits it on purpose to keep the numbering
 *    of the original evidence plan (the list is V1-V5 and V7-V12). It is NOT
 *    invented nor renumbered.
 *  - V10 (official press credits crediting only Valeria) was described in the
 *    evidence bible and listed in the JSON, but never scheduled. Delivered at
 *    T+32 with the complete V2/V4, where authorship becomes the topic.
 *  - V2 and V4 only existed as private fragments at T+20 and their complete
 *    versions were never scheduled, although Camila's confrontation needs the
 *    complete V4 and the authorship deduction (N1/N2) needs both. The complete
 *    versions go to everyone at T+32 ("autoria.md"). The fragments and the
 *    complete files are different images (v2a/v2, v4a/v4).
 *  - V3 arrives as an initial first page at T+10 (v3a) and as the complete
 *    sheet with revision history at T+32 (v3). Different files: the content
 *    changes. Partial/private deliveries carry no evidence code.
 *  - Camila's confrontation (V4 + V1) and Daniela's (V3 + V7) are satisfied by
 *    T+32; Marco's (testimony of Paola, V9) by T+45. Paola's expanded
 *    testimony is delivered as a written declaration with the made-up code
 *    DECL-PAOLA so the AI can tell it has been delivered.
 *  - The cross alibis ("fail-safe" of the design: delivered globally before
 *    the accusation) were never on the timeline. Added at T+52.
 *  - Contradiction in the design: section 3.2/3.4 and the exonerations say
 *    Daniela's alibi is Hugo (and vice versa) while 3.6 says Sofía also
 *    verifies Daniela. Resolved as Daniela <-> Hugo, and Sofía verifies only
 *    Camila and Leticia (as in the JSON).
 *  - Hugo's and Leticia's confrontations have no evidence requirement in the
 *    design (only "T+32 reached"). They are gated on V3, which is delivered
 *    exactly at T+32, plus a direct question about their secret.
 *  - Contradiction in the design: V8/V9 show the office where Valeria is held
 *    as empty, with her phone on the desk, taken "during the investigation".
 *    Resolved without changing the solution: the office has a closed inner
 *    archive room, locked by Marco, where Valeria is; the photos were taken by
 *    Paola when she passed by the open, empty-looking outer office (V8 shows
 *    "a closed door"). Marco had left the phone on the desk in plain sight.
 *  - V1 shows a small "C. R." initial next to the handwritten "Verde Aurora"
 *    so the sketches' handwriting (support 2 of N1) can be told to be Camila's.
 *  - V9 shows the phone in a case with the initials "V. M." (Valeria Montes), and
 *    Paola's declaration says she recognised it as Valeria's. Without that nothing
 *    would tie the phone to Valeria. What Paola saw is gated on DECL-PAOLA / V8 /
 *    V9 (her stages: 3, then 2 once they are delivered at T+45).
 *  - Sofía (N1/N4 support) and Hugo (N3 support) were given the few facts the
 *    deduction graph attributes to them; nothing contradicting the design.
 *  - The design's clock is relative ("esa tarde", "días antes"); the dates in
 *    the documents (V3/V7/V12) are a consistent fiction: initial order 06/02,
 *    samples 20/02, fitting 27/02, chat 13/03, change 14/03, final delivery
 *    17/03, runway 20/03.
 *  - The victim photo is shown under the case's own label ("Desaparecida",
 *    `victim.label`); Valeria is missing and alive, so it reveals nothing.
 *  - A1 is NOT in the timeline: it is only Marco's confession, generated per
 *    game from `confession_script` (no audio_email event, no recorded file).
 *    `audio` stays in `mechanics`, as in la-casa-de-cristal.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'El Color que Falta',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Casa Solari',
    'authority' => 'Equipo de crisis externo de Casa Solari',

    'victim' => [
        'name' => 'Valeria Montes',
        'label' => 'Desaparecida',
        'photo' => 'valeria-montes.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // Base of 5 questions per person; +3 when the person has a
        // confrontation stage (8). See the docblock and `interrogation.stages`.
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
        'tagline' => 'El color está en cada boceto. En cada prenda final, no.',

        'description' => <<<'TXT'
            Horas antes de un desfile decisivo, Valeria Montes, diseñadora
            principal de Casa Solari, una casa de moda independiente,
            desaparece. Al revisar la colección final, el equipo nota algo
            extraño: un color muy particular, presente en todos los bocetos y
            en las primeras pruebas, no está en ninguna de las prendas de
            producción.

            Tu equipo (4 a 6 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco sospechosos, dos testigos y
            al registro del taller y del proveedor de telas, durante 60
            minutos, cruzando bocetos, hojas de producción, fotografías y
            mensajes.

            No hace falta distinguir tonos a ojo: el color se identifica por su
            nombre y por varios documentos que se repiten. Lo difícil es
            entender qué desapareció primero, y por qué.
            TXT,

        // Archivo esperado en public/immersion/el-color-que-falta/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-color-que-falta).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'La diseñadora principal de una casa de moda desaparece horas antes del desfile. Y un color ha desaparecido de toda la colección.',
            'bullets' => [
                '4 a 6 investigadores cruzando bocetos, hojas de producción y mensajes',
                'Cinco sospechosos, dos testigos y el registro del taller a quienes interrogar',
                '60 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 60,
        'min_players' => 3,
        'max_players' => 6,

        'price_amount' => 49900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/el-color-que-falta/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 18,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Sofía, Paola) and the secondary source (the workshop /
    | fabric supplier log) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'marco-iturri' => [
            'name' => 'Marco Iturri',
            'role' => 'sospechoso',
            'file' => 'suspects/marco-iturri.md',
            'photo' => 'marco-iturri.png',
            'connection' => 'Principal inversionista de Casa Solari; financista de Valeria',
            'motive' => 'Proteger su inversión y su reputación ante el riesgo de que la apropiación de autoría de Valeria se convirtiera en un escándalo público',
            'alibi' => 'Ninguna: se reunió a solas con Valeria esa tarde; dice que ella descansa antes del desfile',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['DECL-PAOLA'], 'confrontation' => true, 'note' => 'Con la declaración ampliada de Paola'],
                ['questions' => 2, 'after' => ['V9'], 'confrontation' => true, 'note' => 'Con la fotografía de la oficina'],
            ]],
            'public' => [
                'age' => '52 años',
                'profile' => 'Principal inversionista de Casa Solari. Elegante, seguro y encantador en público; sigue de cerca el desarrollo de la colección.',
                'alibi' => 'Asegura que todo está bajo control y que Valeria descansa antes del desfile.',
                'facts' => [
                    'Su inversión sostiene a la casa y este desfile es decisivo para recuperarla.',
                    'Mantiene una relación cercana y de mucho interés personal con el éxito de Valeria.',
                ],
            ],
        ],
        'daniela-suarez' => [
            'name' => 'Daniela Suárez',
            'role' => 'sospechosa',
            'file' => 'suspects/daniela-suarez.md',
            'photo' => 'daniela-suarez.png',
            'connection' => 'Directora de producción; mano derecha de Valeria en la fabricación',
            'motive' => 'Ninguno respecto a la desaparición; retiró el verde Aurora de la producción final para proteger la narrativa de visión única de la casa (acto distinto)',
            'alibi' => 'En el taller toda la tarde, confirmada por Hugo',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['V3', 'V7'], 'confrontation' => true, 'note' => 'Con la hoja de producción completa y el registro del proveedor'],
                ['questions' => 2, 'after' => ['V12'], 'confrontation' => true, 'note' => 'Con el chat con Valeria'],
            ]],
            'public' => [
                'age' => '45 años',
                'profile' => 'Directora de producción de Casa Solari y mano derecha de Valeria en la fabricación de la colección. Meticulosa, y a la defensiva con las decisiones de su área.',
                'alibi' => 'Dice haber pasado la tarde en el taller.',
                'facts' => [
                    'Firma las órdenes de cambio de la producción final.',
                    'Coordina a diario con el taller y con el proveedor de telas.',
                ],
            ],
        ],
        'camila-rojas' => [
            'name' => 'Camila Rojas',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-rojas.md',
            'photo' => 'camila-rojas.png',
            'connection' => 'Diseñadora júnior; colega directa de Valeria',
            'motive' => 'Ninguno respecto a la desaparición; documenta en privado su autoría real y planea hacerla pública (necesita a Valeria presente)',
            'alibi' => 'En su estación de trabajo toda la tarde, confirmada por Sofía',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V4'], 'note' => 'Con la página de revista de la marca «Aurora»'],
                ['questions' => 3, 'after' => ['V1', 'V4'], 'confrontation' => true, 'note' => 'Con el recorte de la marca y los bocetos originales'],
            ]],
            'public' => [
                'age' => '29 años',
                'profile' => 'Diseñadora júnior de Casa Solari. Contenida y profesional, trabaja codo a codo con Valeria en los bocetos.',
                'alibi' => 'Dice haber estado en su estación de trabajo toda la tarde.',
                'facts' => [
                    'Llegó a Casa Solari hace unos meses, desde su propia marca independiente.',
                    'Guarda un archivo personal de sus trabajos anteriores.',
                ],
            ],
        ],
        'hugo-pena' => [
            'name' => 'Hugo Peña',
            'role' => 'sospechoso',
            'file' => 'suspects/hugo-pena.md',
            'photo' => 'hugo-pena.png',
            'connection' => 'Patronista y jefe de taller; colaborador técnico en la producción',
            'motive' => 'Ninguno respecto a la desaparición; colabora en secreto con una marca competidora (sin relación con el caso)',
            'alibi' => 'En el taller toda la tarde, confirmado por Daniela',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V3'], 'confrontation' => true, 'note' => 'Con la hoja de producción completa'],
            ]],
            'public' => [
                'age' => '36 años',
                'profile' => 'Patronista y jefe de taller. Técnico, algo hosco y de pocas palabras fuera del trabajo.',
                'alibi' => 'Dice haber estado en el taller toda la tarde.',
                'facts' => [
                    'Traduce los bocetos en patrones y supervisa el corte y la confección.',
                    'Ve pasar las hojas de producción antes que nadie en el taller.',
                ],
            ],
        ],
        'leticia-vargas' => [
            'name' => 'Leticia Vargas',
            'role' => 'sospechosa',
            'file' => 'suspects/leticia-vargas.md',
            'photo' => 'leticia-vargas.png',
            'connection' => 'Fotógrafa de la casa; documenta fittings y lookbook',
            'motive' => 'Ninguno respecto a la desaparición; filtra fotos anticipadas del lookbook a un blog de moda a cambio de dinero (sin relación con el caso)',
            'alibi' => 'En el estudio fotográfico toda la tarde, confirmada por Sofía',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V3'], 'confrontation' => true, 'note' => 'Con la hoja de producción completa'],
            ]],
            'public' => [
                'age' => '41 años',
                'profile' => 'Fotógrafa de la casa: documenta los fittings y el lookbook. Profesional, aunque se tensa cuando se habla de confidencialidad.',
                'alibi' => 'Dice haber estado en el estudio fotográfico toda la tarde.',
                'facts' => [
                    'Fotografió las pruebas de fitting de la colección.',
                    'Tiene acceso a las imágenes de la colección antes que nadie fuera del equipo.',
                ],
            ],
        ],
        'sofia-mejia' => [
            'name' => 'Sofía Mejía',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-mejia.md',
            'photo' => 'sofia-mejia.png',
            'connection' => 'Asistente de diseño; cercana a Valeria y a Camila',
            'motive' => null,
            'alibi' => null,
            // Authorship (Camila's brand and the real working dynamic) is gated
            // on V4, delivered at T+32.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V4'], 'note' => 'Con la página de revista de la marca «Aurora»'],
            ]],
            'public' => [
                'age' => '24 años',
                'profile' => 'Asistente de diseño, cercana tanto a Valeria como a Camila. Se mueve entre la estación de diseño, el taller y el estudio fotográfico.',
                'facts' => [
                    'Trabaja en los fittings y conoce el día a día del equipo de diseño.',
                    'Estuvo yendo y viniendo entre las distintas áreas durante la tarde.',
                ],
            ],
            'accusable' => false,
        ],
        'paola-ibarra' => [
            'name' => 'Paola Ibarra',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/paola-ibarra.md',
            'photo' => 'paola-ibarra.png',
            'connection' => 'Coordinadora de backstage',
            'motive' => null,
            'alibi' => null,
            // What she saw that afternoon is gated on her written declaration
            // (DECL-PAOLA) and her two photos (V8, V9), all delivered at T+45.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DECL-PAOLA', 'V8', 'V9'], 'note' => 'Con su declaración ampliada y sus fotografías de la oficina'],
            ]],
            'public' => [
                'age' => '33 años',
                'profile' => 'Coordinadora de backstage: organiza el orden de los looks con auriculares y tablilla en mano.',
                'facts' => [
                    'Estuvo toda la tarde en el backstage coordinando los looks.',
                    'Conoce las oficinas y las zonas de trabajo del backstage.',
                ],
            ],
            'accusable' => false,
        ],
        'registro-taller-proveedor' => [
            'name' => 'Registro del taller y del proveedor de telas',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/registro-taller-proveedor.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-produccion"): a shot of
            // a supplier order screen works as its "portrait".
            'photo' => 'registro-taller-proveedor.png',
            'connection' => 'Fechas de pedidos, modificaciones y entregas de telas y muestras',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente documental del taller y del proveedor de telas: registra fechas, colores y solicitantes de pedidos y entregas, sin interpretar nada.',
                'facts' => [
                    'Registra la fecha exacta de cada pedido, modificación y entrega.',
                    'Registra el nombre de quien solicitó cada cambio, pero no puede decir por qué se pidió.',
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
        'culprit_slug' => 'marco-iturri',

        'headline' => 'Marco Iturri tiene retenida a Valeria Montes, viva y a salvo, en una oficina del backstage. El color no fue un ajuste estético: Daniela lo retiró de la producción final para que no delatara que la verdadera autora de la colección es Camila Rojas, y Marco, al descubrir esa apropiación, confrontó a Valeria y no la dejó irse.',

        'motive' => 'Proteger su inversión y su propia reputación ante el riesgo de que la apropiación de autoría de Valeria se convirtiera en un escándalo público justo después del desfile.',

        'method' => 'Esa tarde confrontó a Valeria en privado, en una oficina del backstage, con un ultimátum: reconocer públicamente la autoría de Camila antes del desfile o perder su financiamiento. Cuando ella intentó irse, le retuvo el teléfono y la mantuvo en esa oficina, en el cuarto de archivo interior, sin violencia física. Después dijo que estaba descansando.',

        'key_evidence' => [
            'Paola los vio entrar juntos a la oficina y nunca vio salir a Valeria; Marco explicó su ausencia diciendo que descansaba.',
            'El teléfono de Valeria (con sus iniciales «V. M.» en la funda, que Paola reconoce) estaba sobre el escritorio de esa oficina: no se lo llevó, así que no salió a descansar por su cuenta.',
            'El verde Aurora, firma personal de Camila documentada antes de llegar a Casa Solari, está en los bocetos y en el fitting y fue retirado de la producción final: la comparación de las dos prendas, la hoja de producción y el registro del proveedor lo prueban.',
            'El recorte de la marca «Aurora» acredita a Camila como diseñadora, mientras el material oficial de prensa solo acredita a Valeria.',
            'El chat entre Valeria y Daniela («simplificar la paleta, ya sabes por qué») muestra de dónde vino la indicación que Daniela ejecutó.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'daniela-suarez' => 'Sí retiró el verde Aurora de la producción final, por una indicación ambigua de Valeria y para proteger la narrativa de visión única de la casa; la hoja de producción y el registro del proveedor la señalan como quien pidió el cambio. Pero ese acto es independiente y anterior a la desaparición, y estuvo en el taller toda la tarde, con Hugo como testigo.',

            'camila-rojas' => 'Es la verdadera autora de gran parte de la colección y ha estado documentando su autoría para hacerla pública: un resentimiento comprensible. Pero estuvo en su estación de trabajo toda la tarde, con Sofía como testigo, y su plan necesitaba a Valeria presente para enfrentarla en público: sin ella, no hay a quién señalar.',

            'hugo-pena' => 'Su evasividad venía de colaborar en secreto con una marca competidora, un asunto real pero sin relación con Valeria. Estuvo en el taller toda la tarde, y Daniela lo confirma.',

            'leticia-vargas' => 'Su nerviosismo venía de haber filtrado fotos anticipadas del lookbook a un blog de moda a cambio de dinero, un asunto real pero sin relación con Valeria. Estuvo en el estudio fotográfico toda la tarde, y Sofía lo confirma.',

            'sofia-mejia' => 'Es una testigo, no una sospechosa: su aporte fue confirmar la tensión entre Valeria y Camila, la dinámica real de trabajo y las coartadas de Camila y Leticia. No tiene motivo alguno ni vio qué ocurrió entre Marco y Valeria.',

            'paola-ibarra' => 'Es una testigo, no una sospechosa: vio entrar juntos a Marco y a Valeria a la oficina, nunca vio salir a Valeria y fotografió la oficina y el teléfono, nada más. No tiene motivo alguno.',

            'registro-taller-proveedor' => 'Es una fuente documental, no una persona: no puede haber intervenido. Su función fue confirmar, con exactitud, las fechas de pedidos, modificaciones y entregas, y que Daniela figura como solicitante del cambio de color, sin interpretar intenciones. Nunca falló: lo que mostró era cierto.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Orus',

        // Used by the confession audio (design A1). Written to be played
        // as-is: the model only reads it aloud and lightly weaves in 1-2 real
        // questions from the table, it never composes it.
        'confession_script' => <<<'TXT'
            Descubrí que buena parte de esta colección era de Camila, no de Valeria. No
            podía dejar que eso saliera después del desfile, en medio de un escándalo.
            Quería que ella lo arreglara antes, que diera la cara.

            Cuando se lo dije, quiso irse, dijo que no podía con todo eso justo hoy. No la
            dejé salir así. Le pedí que se quedara a pensarlo conmigo, le tomé el teléfono
            un momento... y ese momento se volvió toda la tarde.

            Está bien, está en esa oficina. No le he hecho daño. Pero no supe cómo parar
            esto sin arruinar todo lo que habíamos construido.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué le ocurrió a Valeria y cómo?',
            'hint' => 'Cuenta qué pasó esa tarde, dónde está ahora y en qué estado.',
            'points' => 12,
            'guidance' => 'Lo esencial: tras una confrontación con ultimátum, cuando intentó irse, le retuvieron el teléfono y la mantienen en una oficina del backstage, viva y sin violencia física.',
        ],
        'motive' => [
            'label' => '¿Por qué la retuvieron?',
            'hint' => 'Qué quería evitar el responsable y qué arriesgaba.',
            'points' => 13,
        ],
        'questions' => [
            [
                'key' => 'creadora_coleccion',
                'label' => '¿Quién creó realmente la colección?',
                'hint' => 'Nombra a la persona y la prueba que la identifica como autora.',
                'answer' => 'Camila Rojas. Su firma, el verde Aurora (el color de su propia marca independiente, documentado antes de llegar a Casa Solari), aparece en sus bocetos, con su letra, y en las primeras pruebas de fitting; Valeria presentó ese trabajo como propio y la prensa oficial solo la acredita a ella.',
                'points' => 25,
                'guidance' => 'Puntos completos con Camila + el verde Aurora como firma o los bocetos como prueba. Cero si señala a Valeria como autora.',
            ],
            [
                'key' => 'eliminacion_del_color',
                'label' => '¿Por qué desapareció el color de la producción final?',
                'hint' => 'Explica quién lo retiró, por qué razón y si tiene relación con la desaparición.',
                'answer' => 'Daniela Suárez lo retiró días antes del desfile, a partir de una indicación vaga de Valeria («simplificar la paleta, ya sabes por qué»), para que el verde Aurora no apareciera en la pasarela y delatara que la verdadera autora era Camila. Es un acto anterior e independiente de la desaparición de Valeria.',
                'points' => 20,
                'guidance' => 'Cero si lo trata como una decisión estética o de producción. Lo central es que se retiró para ocultar la autoría; restar si lo funde con la desaparición como una sola trama.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+10/T+20/T+32/T+45/T+60 delivery
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases). There is no audio event: A1 is only the generated confession.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El Color que Falta — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V5', 'V11'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 10,
            'title' => 'Expansión — el equipo y la colección',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            // V3 initial first page is a partial delivery: no code, so it does
            // not satisfy Daniela's confrontation, which needs the complete V3.
            'evidence_codes' => ['V1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 20,
            'title' => 'Fragmento privado — una foto de fitting',
            'source_file' => 'pista-privada-fitting.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V2 fragment. The complete V2 is
            // delivered to everyone at T+32.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 20,
            'title' => 'Fragmento privado — una página de revista',
            'source_file' => 'pista-privada-prensa.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V4 fragment (no credit). The
            // complete V4 is delivered to everyone at T+32.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 32,
            'title' => 'Complicación — el color no se perdió, se retiró',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V3', 'V7', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 32,
            'title' => 'Las piezas completas — lo que se vio y lo que se dijo',
            'source_file' => 'autoria.md',
            'delivery_mode' => 'all',
            // V2 and V4 complete, plus V10 (official credits): none of them
            // was ever scheduled in the design (see the docblock).
            'evidence_codes' => ['V2', 'V4', 'V10'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 45,
            'title' => 'Giro — dos hechos distintos, una sola tarde',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DECL-PAOLA = Paola's expanded declaration, written in giro.md;
            // Marco's confrontación 1 needs it.
            'evidence_codes' => ['DECL-PAOLA', 'V8', 'V9'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Cierre — las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 60,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para separar los dos hechos de esta tarde: qué le ocurrió a Valeria Montes y qué le ocurrió al color. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-color-que-falta/gallery. Fragment and
    | complete versions are different files (v2a/v2, v3a/v3, v4a/v4).
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v5-backstage.png', 'caption' => 'El backstage esa tarde, horas antes del desfile'],
            ['file' => 'v11-comparacion-prendas.png', 'caption' => 'La misma prenda: muestra y producción final'],
        ],
        'expansion.md' => [
            ['file' => 'v1-bocetos-verde-aurora.png', 'caption' => 'Hoja de bocetos originales de la colección'],
            ['file' => 'v3a-hoja-produccion-inicial.png', 'caption' => 'Hoja de producción, primera página (versión inicial)'],
        ],
        'pista-privada-fitting.md' => [
            ['file' => 'v2a-fitting-fragmento.png', 'caption' => 'Fragmento de una fotografía de fitting'],
        ],
        'pista-privada-prensa.md' => [
            ['file' => 'v4a-recorte-aurora-fragmento.png', 'caption' => 'Fragmento de una página de revista'],
        ],
        'complicacion.md' => [
            ['file' => 'v3-hoja-produccion-historial.png', 'caption' => 'Hoja de producción completa, con historial de revisión'],
            ['file' => 'v7-registro-proveedor-telas.png', 'caption' => 'Registro de pedidos del proveedor de telas'],
            ['file' => 'v12-chat-daniela-valeria.png', 'caption' => 'Chat entre Daniela y Valeria'],
        ],
        'autoria.md' => [
            ['file' => 'v2-fitting-verde-aurora.png', 'caption' => 'Fotografía de fitting, completa'],
            ['file' => 'v4-recorte-aurora-completo.png', 'caption' => 'Página de revista de la marca «Aurora», completa'],
            ['file' => 'v10-creditos-coleccion.png', 'caption' => 'Créditos del material oficial de prensa de la colección'],
        ],
        'giro.md' => [
            ['file' => 'v8-oficina-backstage.png', 'caption' => 'La oficina pequeña del backstage'],
            ['file' => 'v9-objeto-sobre-mesa.png', 'caption' => 'Un teléfono sobre el escritorio de la oficina'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v11-comparacion-prendas.png', 'caption' => 'El color retirado de la producción final'],
            ['file' => 'v12-chat-daniela-valeria.png', 'caption' => 'La indicación vaga que Daniela ejecutó'],
            ['file' => 'v4-recorte-aurora-completo.png', 'caption' => 'La firma de Camila, antes de esta colección'],
            ['file' => 'v10-creditos-coleccion.png', 'caption' => 'La autoría que se mostraba al público'],
            ['file' => 'v9-objeto-sobre-mesa.png', 'caption' => 'Valeria no salió a descansar por su cuenta'],
        ],
    ],
];
