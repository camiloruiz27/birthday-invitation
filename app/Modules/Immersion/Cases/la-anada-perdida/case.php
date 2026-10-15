<?php

/**
 * Case manifest: "La Añada Perdida"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "the fraud is older than the murder" case. The
 * table first notices that the Cosecha 1979 does not add up and only later
 * understands that the fraud was born in 1986 (earthquake, Horacio Larrea's
 * decision, executed by Walter Espinoza) and that Emilio, the victim, was
 * morally dragged into it without knowing. The murder does not protect the old
 * fraud: it protects Ramona from the consequences of Emilio revealing it NOW.
 * Every other suspect hides a real but non-homicidal secret.
 *
 * Fictional calendar (kept here so documents stay coherent): the closing
 * dinner is on Friday 20 September, 20:00 (38 years after 1986). Emilio found
 * his father's letter on Monday 16/09, told Ramona on Tuesday 17/09 (V10) and,
 * on Friday, told Sofía at ~17:40 that he would go down to the barrel room
 * "a buscar algo importante". Emilio's entry in the access log: 17:46.
 * Ramona's: 17:53. Critical window used for every alibi: 17:45–18:45.
 * The body is found shortly before 20:00, when the dinner was about to start.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's five-component scoring is
 *    NOT implemented; the extra elements (when/why the fraud began, what Emilio
 *    wanted to reveal) are asked for inside the free-text motive (see the T+70
 *    unlock text) and explained in solucion.md and in each exoneration.
 *  - Question budget: every interrogable person has 5 questions, plus 3 more
 *    (8) when the case has confrontations for them (a separate pool). Each
 *    person's questions may be opened in stages (interrogation.stages) that
 *    wait on delivered evidence codes; confrontation stages wait on the
 *    evidence their md requires.
 *  - V9 (buyer's appraisal) and V12 (the mallet) were never scheduled in the
 *    design doc. V9 goes to T+24 as a global delivery (Federico's confrontation
 *    needs it by T+36); V12 goes to T+62 next to V14 (the weapon is confirmed
 *    once the motive is on the table).
 *  - V4 and V5 were only delivered "parcial" (T+24) and never in full, although
 *    node N1/N2 relies on them. They now arrive as fragments at T+24 (private,
 *    different files v4a/v5a, no evidence code) and complete at T+36.
 *  - Ramona's first confrontation needs Sofía's testimony + V13 (access log),
 *    but V13 was scheduled at T+62, after the T+36 unlock. V13 moved to T+50
 *    (Giro) and the md gates the confrontation on the evidence itself.
 *  - Ramona's second confrontation is tied to "T+50" but needs V14, which the
 *    design delivers at T+62. The md gates it on V8 + V14, not on the minute.
 *  - Tomás's confrontation asks for "documento financiero o testimonio
 *    indirecto" but no document about his loans exists. V9 now carries a second,
 *    discreet observation (financial commitments to be reconciled with the
 *    statements presented) and Federico mentions it in his own confrontation.
 *  - Constanza's confrontation names no evidence at all. It is gated on what
 *    Jorge saw (the two of them together all afternoon), which is delivered by
 *    his own testimony, not by a code.
 *  - The design says Sofía's testimony is "ampliado" at T+50. The engine cannot
 *    time-gate a testimony, so her md hands the extra material only to a player
 *    who already holds V8 or V10 (both delivered at T+50).
 *  - Emilio's moral complicity (final component of the accusation) had no
 *    supporting evidence. V8 therefore lists some sales managed by E. Larrea
 *    next to R. Larrea's, and Sofía (extended) quotes Emilio saying he sold
 *    those bottles himself.
 *  - Letter location: Sofía says Emilio found the letter in the ARCHIVE, N4 and
 *    V7 say it was found in the BARREL ROOM, and the design says Emilio kept it
 *    "escondida allí". Resolved: he found it in the archive on Monday, hid the
 *    original in the barrel room on Wednesday (away from the buyer's auditors)
 *    and was going down to retrieve it when he was killed; the letter was still
 *    in its hiding place when the barrel room was searched.
 *  - "Oficina principal" (Federico's alibi) and "oficina de archivo" (V1) are the
 *    same room here: Federico worked on the operation papers in the archive
 *    office with Sofía, whose desk faces the corridor to the barrel-room stairs.
 *    That is how Sofía saw Emilio and then Ramona go by. Jorge, instead, moves
 *    between the cava de añadas ("la cava principal") and the sala de cata
 *    through an internal passage, so he never crosses that corridor.
 *  - The access log (V13) is an entry-only book: it records when each person
 *    went in, not when they left. It also lists morning entries by Jorge, Walter
 *    and Constanza (routine, hours before the window) so Emilio's and Ramona's
 *    lines are not the only ones.
 *  - Dinner time, window hours and every clock time are not in the design; they
 *    were chosen here and must stay identical across V13, the md files and the
 *    exonerations.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'La Añada Perdida',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Bodega Larrea',
    'authority' => 'Comité de Revisión Independiente de la operación de venta de Bodega Larrea',

    'victim' => [
        'name' => 'Emilio Larrea',
        'label' => 'Víctima',
        'photo' => 'emilio-larrea.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // Base of 5 questions per person; the 3 confrontation questions and
        // the stages are declared per person under 'interrogation' below.
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
        'tagline' => 'La noche antes de vender el apellido, su dueño muere. Y la joya de la colección no es lo que todos creían.',

        'description' => <<<'TXT'
            Bodega Larrea, viña familiar histórica del Valle de Piedra Alta,
            se prepara para venderse a un fondo de inversión extranjero. La
            joya de la operación es una caja de la legendaria Cosecha 1979.
            Horas antes de la cena de cierre, Emilio Larrea, dueño y
            anfitrión, aparece muerto en la sala de barricas.

            Tu equipo (4 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco sospechosos, dos testigos y
            al registro notarial de la bodega durante 70 minutos, cruzando
            documentos, inventarios, etiquetas y accesos.

            Al revisar los papeles de la venta aparecen preguntas incómodas
            sobre la colección estrella. Para resolver el caso hay que
            entender qué ocurrió esa tarde y también cuánto tiempo hace que
            esa historia se está contando mal.
            TXT,

        // Archivo esperado en public/immersion/la-anada-perdida/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-anada-perdida).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'La noche antes de vender la bodega familiar, su dueño aparece muerto en la sala de barricas.',
            'bullets' => [
                '4 a 7 investigadores cruzando inventarios, etiquetas, cartas y accesos',
                'Cinco sospechosos, dos testigos y el registro notarial a quienes interrogar',
                '70 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 70,
        'min_players' => 3,
        'max_players' => 7,

        'price_amount' => 59900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/la-anada-perdida/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Sofía, Jorge) and the secondary source (the notarial
    | inventory register) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'ramona-larrea' => [
            'name' => 'Ramona Larrea',
            'role' => 'sospechosa',
            'file' => 'suspects/ramona-larrea.md',
            'photo' => 'ramona-larrea.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['V13'], 'confrontation' => true, 'note' => 'Con el libro de acceso a la sala de barricas'],
                ['questions' => 2, 'after' => ['V8', 'V14'], 'confrontation' => true, 'note' => 'Con los documentos finales del caso'],
            ]],
            'connection' => 'Hermana de Emilio y socia minoritaria (30 %) de la bodega',
            'motive' => 'Necesita que la venta se cierre a precio completo por una situación financiera personal delicada, y teme quedar expuesta por ventas fraudulentas de la Cosecha 1979 que gestionó ella misma',
            'alibi' => 'Afirma haber estado en la casa con los preparativos de la cena y no haber hablado con Emilio esa tarde',
            'public' => [
                'age' => '61 años',
                'profile' => 'Hermana de Emilio y socia minoritaria de la bodega. Cálida y muy protectora con la familia; se ocupó personalmente de la organización de la cena de cierre.',
                'alibi' => 'Dice haber estado en la casa con los preparativos de la cena, cada uno en lo suyo.',
                'facts' => [
                    'Posee el 30 % de la sociedad y lleva décadas vinculada a la bodega.',
                    'La venta al fondo inversor afectaba directamente a su participación.',
                ],
            ],
        ],
        'tomas-larrea' => [
            'name' => 'Tomás Larrea',
            'role' => 'sospechoso',
            'file' => 'suspects/tomas-larrea.md',
            'photo' => 'tomas-larrea.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                // V9 arrives at T+24, but confrontations open at T+36 (the
                // complicacion email, which also delivers V7).
                ['questions' => 3, 'after' => ['V9', 'V7'], 'confrontation' => true, 'note' => 'Con el informe de tasación del comprador'],
            ]],
            'connection' => 'Hijo de Emilio; trabaja en el área comercial',
            'motive' => 'Ninguno respecto a la muerte; ha endeudado la empresa con préstamos sin autorización plena (secreto sin relación con el asesinato)',
            'alibi' => 'En la sala de cata preparando la recepción con Constanza, confirmado por Jorge',
            'public' => [
                'age' => '35 años',
                'profile' => 'Hijo de Emilio y cara comercial de la bodega. Profesional, algo ansioso, muy pendiente de la imagen de la familia ante el comprador.',
                'alibi' => 'Dice haber estado preparando la recepción de esa noche en la sala de cata.',
                'facts' => [
                    'Impulsó la modernización de las instalaciones de la bodega en los últimos años.',
                    'Es el heredero natural del negocio familiar.',
                ],
            ],
        ],
        'walter-espinoza' => [
            'name' => 'Don Walter Espinoza',
            'role' => 'sospechoso',
            'file' => 'suspects/walter-espinoza.md',
            'photo' => 'walter-espinoza.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V6', 'V7'], 'confrontation' => true, 'note' => 'Con la comparación de etiquetas y la carta'],
            ]],
            'connection' => 'Maestro bodeguero; empleado de máxima confianza de la familia desde hace cuarenta años',
            'motive' => 'Ninguno respecto a la muerte; ejecutó el reetiquetado de 1986 y teme perder el respeto ganado en cuatro décadas de oficio',
            'alibi' => 'En la cava de añadas seleccionando botellas para la cena, visto por Jorge durante toda la ventana crítica',
            'public' => [
                'age' => '70 años',
                'profile' => 'Maestro bodeguero de la casa. Reservado, orgulloso de su oficio y de las manos que lo aprendieron; conoce cada rincón de la cava de añadas.',
                'alibi' => 'Dice haber estado en la cava de añadas eligiendo botellas para la cena.',
                'facts' => [
                    'Trabaja en la bodega desde hace cuatro décadas, con la confianza personal de Emilio.',
                    'Conoce mejor que nadie en activo cómo se produjo y se almacenó el vino de la casa.',
                ],
            ],
        ],
        'constanza-ibarra' => [
            'name' => 'Constanza Ibarra',
            'role' => 'sospechosa',
            'file' => 'suspects/constanza-ibarra.md',
            'photo' => 'constanza-ibarra.png',
            // Su confrontación no espera a ningún documento: se apoya en lo que
            // Jorge vio, que se obtiene interrogando a Jorge, no en un código.
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'confrontation' => true],
            ]],
            'connection' => 'Enóloga actual; responsable de la producción en curso',
            'motive' => 'Ninguno respecto a la muerte; mantiene una relación personal con Tomás que prefiere mantener discreta',
            'alibi' => 'En la sala de cata con Tomás preparando la recepción, confirmado por Jorge',
            'public' => [
                'age' => '44 años',
                'profile' => 'Enóloga de la bodega desde hace una década. Profesional y precisa en el trabajo, reservada cuando la conversación se vuelve personal.',
                'alibi' => 'Dice haber estado en la sala de cata dejando lista la recepción de esa noche.',
                'facts' => [
                    'Dirige la producción de los vinos recientes de la bodega.',
                    'Sus vinos dependen del buen nombre de la casa.',
                ],
            ],
        ],
        'federico-rojas' => [
            'name' => 'Federico Rojas',
            'role' => 'sospechoso',
            'file' => 'suspects/federico-rojas.md',
            'photo' => 'federico-rojas.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                // Same as Tomás: opens with the T+36 confrontations, not at T+24.
                ['questions' => 3, 'after' => ['V9', 'V7'], 'confrontation' => true, 'note' => 'Con el informe de tasación del comprador'],
            ]],
            'connection' => 'Representante del fondo comprador; contraparte de Emilio en la operación',
            'motive' => 'Ninguno respecto a la muerte; sospechaba irregularidades desde la revisión documental y quería usarlas para renegociar el precio en privado',
            'alibi' => 'En la oficina de archivo revisando documentos de la operación, confirmado por Sofía',
            'public' => [
                'age' => '52 años',
                'profile' => 'Representante del fondo que iba a comprar la bodega. Formal, calculador y cuidadoso con cada palabra que dice sobre la negociación.',
                'alibi' => 'Dice haber estado revisando documentos de la operación en la oficina de archivo.',
                'facts' => [
                    'Lleva semanas revisando la documentación de la bodega para el fondo.',
                    'Su comisión depende de que la venta se firme.',
                ],
            ],
        ],
        'sofia-mella' => [
            'name' => 'Sofía Mella',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-mella.md',
            'photo' => 'sofia-mella.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                // Her md only names Ramona's comings and goings (and the
                // extended account) once V13 / V8 / V10 are in; all arrive at T+50.
                ['questions' => 2, 'after' => ['V8', 'V10', 'V13'], 'note' => 'Con el libro de acceso, el registro de ventas o los mensajes'],
            ]],
            'connection' => 'Asistente y archivista de Emilio',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '28 años',
                'profile' => 'Asistente y archivista de Emilio. Conoce el archivo histórico de la bodega y la agenda de su jefe.',
                'facts' => [
                    'Trabajaba a diario con Emilio y conocía sus movimientos de esa semana.',
                    'Su escritorio, en la oficina de archivo, da al pasillo principal.',
                ],
            ],
            'accusable' => false,
        ],
        'jorge-paillao' => [
            'name' => 'Jorge Paillao',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/jorge-paillao.md',
            'photo' => 'jorge-paillao.png',
            'connection' => 'Trabajador de bodega',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '45 años',
                'profile' => 'Trabajador de bodega. Esa tarde hacía sus rondas habituales y ayudaba a trasladar material para la recepción.',
                'facts' => [
                    'Se movió esa tarde entre la cava de añadas y la sala de cata.',
                    'Conoce el trabajo de todos los equipos de la bodega.',
                ],
            ],
            'accusable' => false,
        ],
        'registro-notarial-inventario' => [
            'name' => 'Registro notarial de inventario',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/registro-notarial-inventario.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-control-buque") — a shot
            // of a notarial ledger works as its "portrait".
            'photo' => 'registro-notarial-inventario.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V5'], 'note' => 'Con la hoja de inventario histórico'],
            ]],
            'connection' => 'Conteos oficiales de existencias y tasaciones notariales de la bodega',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente documental: conserva los conteos oficiales de existencias de la bodega en distintas fechas y la fecha de la última tasación notarial, sin interpretar nada.',
                'facts' => [
                    'Registra cantidades y fechas de los conteos de botellas.',
                    'No puede confirmar la autenticidad de las botellas ni la intención de nadie.',
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
        'culprit_slug' => 'ramona-larrea',

        'headline' => 'La Cosecha 1979 no era la que todos creían desde hacía casi cuatro décadas: tras el terremoto de 1986, Horacio Larrea reetiquetó botellas de una cosecha posterior para ocultar la pérdida, con la ejecución de Walter Espinoza. Emilio descubrió el fraude completo, incluida su propia complicidad involuntaria, y quiso confesarlo todo esa noche. Su hermana Ramona, que conocía el fraude desde hacía años y había vendido botellas falsas, lo siguió a la sala de barricas y lo mató.',

        'motive' => 'Miedo a las consecuencias financieras y a su propia exposición: Ramona conocía el fraude desde hacía años, había gestionado personalmente ventas de botellas falsas de la Cosecha 1979 y necesitaba que la venta se cerrara a precio completo por una situación financiera personal delicada. Emilio se negó a esperar.',

        'method' => 'Golpe con un mazo de embutir barricas de la propia sala de barricas, a la que Ramona entró a las 17:53, siete minutos después que Emilio. Después limpió el mazo y lo dejó fuera de su soporte.',

        'key_evidence' => [
            'Sofía vio a Emilio dirigirse a la sala de barricas y, pocos minutos después, a Ramona ir hacia el mismo lugar; el registro de acceso lo confirma (17:46 y 17:53) y contradice su "no hablé con Emilio esa tarde".',
            'El registro de ventas antiguas la muestra gestionando personalmente ventas de la Cosecha 1979 a coleccionistas, y el requerimiento del banco explica por qué necesitaba el cierre a precio completo.',
            'El mensaje de Emilio y Ramona, días antes, muestra el desacuerdo sobre decirlo todo en la cena.',
            'El mazo estaba fuera de su soporte y sin el polvo que tienen las demás herramientas.',
            'La carta de Horacio, la foto del terremoto, la comparación de etiquetas y el inventario histórico prueban que el fraude nació en 1986, no esa noche.',
            'Tomás, Walter, Constanza y Federico tienen coartada verificable para la ventana del ataque y secretos sin relación con la muerte.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per person that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'tomas-larrea' => 'Su secreto era real: había endeudado la empresa con préstamos sin autorización plena para modernizar las instalaciones, y su padre estaba a punto de descubrirlo. Pero la muerte de Emilio no lo resolvía, lo complicaba, y durante toda la ventana crítica estuvo en la sala de cata con Constanza, como confirma Jorge.',

            'walter-espinoza' => 'Ejecutó el reetiquetado de 1986 por orden de Horacio, y de ahí venían su vergüenza y su evasividad. Pero Emilio le había dicho que no lo señalaría y que cargaría con la responsabilidad de la familia, así que no tenía motivo para matarlo; y Jorge lo vio en la cava de añadas durante toda la ventana crítica.',

            'constanza-ibarra' => 'Mentía sobre su relación con Tomás porque quería mantenerla privada, algo sin ninguna relación con el fraude antiguo ni con la muerte de Emilio. Estuvo con Tomás en la sala de cata toda la ventana crítica, como confirma Jorge.',

            'federico-rojas' => 'Sí tenía dudas sobre la añada desde la revisión documental y pensaba usarlas para renegociar el precio en privado, y por eso dijo que "se enteró recién". Pero necesitaba a Emilio vivo para firmar la venta, y estuvo en la oficina de archivo con Sofía durante toda la ventana crítica.',

            'sofia-mella' => 'Es la testigo, no una sospechosa: su aporte fue ver a Emilio y después a Ramona dirigirse a la sala de barricas y conocer el plan de Emilio de contarlo todo. No tenía motivo alguno y estuvo en su escritorio, con Federico, durante toda la ventana crítica.',

            'jorge-paillao' => 'Es un testigo, no un sospechoso: su aporte fue situar a Walter en la cava de añadas y a Tomás y Constanza en la sala de cata durante la ventana crítica. No tenía motivo alguno ni relación con el fraude.',

            'registro-notarial-inventario' => 'Es un registro documental, no una persona: no puede haber intervenido. Solo confirmó cantidades y fechas de los conteos y de la última tasación, sin interpretar autenticidad ni intenciones, y nunca se equivocó.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Kore',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it. Based on the design doc's audio A2.
        'confession_script' => <<<'TXT'
            Nuestro padre me lo contó hace años, cuando ya estaba enfermo. Me dijo que algún
            día alguien tendría que decidir qué hacer con eso, y que probablemente sería yo.

            Yo también vendí esas botellas, sabiendo lo que eran. Y necesitaba que esta venta
            se cerrara, por cosas que nadie en la familia sabía.

            Cuando Emilio me dijo que iba a contarlo todo esa misma noche, sin esperar, sin
            protegerme a mí también, no pude aceptarlo. Le pedí tiempo. Se negó.

            Lo seguí a la sala de barricas para insistirle una vez más. No fue premeditado.
            Fue un momento, y ya no hubo vuelta atrás.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Accusation form and rubric
    |--------------------------------------------------------------------------
    |
    | Straight from the doc's scoring: asesina 30 / method 15 / motive 20 /
    | origin and date of the fraud 20 / the victim's own moral complicity 15.
    |
    */

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Con qué y dónde lo mató?',
            'hint' => 'Nombra el objeto y el lugar del crimen.',
            'points' => 15,
            'guidance' => 'Basta con el golpe con el mazo de embutir barricas en la sala de barricas. Que lo limpiara después es un plus.',
        ],
        'motive' => [
            'label' => '¿Por qué lo mató?',
            'hint' => 'Explica qué tenía que perder la persona culpable si Emilio hablaba esa noche.',
            'points' => 20,
            'guidance' => 'Lo esencial: miedo a quedar expuesta por haber vendido ella misma botellas falsas y a perder la venta a precio completo que necesitaba por su situación financiera. Puntos parciales si solo menciona proteger la venta o el nombre de la familia.',
        ],
        'questions' => [
            [
                'key' => 'origen_fraude',
                'label' => '¿Cuándo y por qué comenzó el fraude de la Cosecha 1979, y quién lo llevó a cabo?',
                'hint' => 'Sitúa el origen en el tiempo y explica qué lo provocó.',
                'answer' => 'En 1986, después de que un terremoto destruyera la mayor parte de las botellas de la Cosecha 1979 ya prometidas a los clientes. Horacio Larrea, el padre, decidió ocultar la pérdida reetiquetando como 1979 botellas de una cosecha posterior, y Walter Espinoza ejecutó el reetiquetado por orden suya.',
                'points' => 20,
                'guidance' => 'Cero si trata el fraude como reciente (de esa noche, de esta generación o una invención de Emilio). Lo esencial es 1986, el terremoto y la decisión de Horacio; nombrar a Walter como ejecutor es un plus.',
            ],
            [
                'key' => 'revelacion_victima',
                'label' => '¿Qué pretendía revelar Emilio esa noche?',
                'hint' => 'Explica qué iba a contar y qué papel había tenido él mismo en ello.',
                'answer' => 'El fraude completo de la Cosecha 1979, con la carta de su padre como prueba, antes de que se firmara la venta, incluida su propia complicidad moral involuntaria: él mismo había vendido, sin saberlo, botellas falsas a coleccionistas.',
                'points' => 15,
                'guidance' => 'Puntos completos solo si menciona que Emilio también había vendido botellas falsas sin saberlo; la mitad si solo dice que iba a revelar el fraude.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+24/T+36/T+50/T+62/T+70
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as
    | the other cases). A1 is the Gancho's ambience audio; A2 is the culprit's
    | confession and is NOT a timeline event (it is solution.confession_script).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Añada Perdida — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V11'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — la sala de barricas, poco antes de la cena',

            'audio_file' => 'a1-ambiente-bodega.wav',
            'audio_scene' => 'Interior de una sala de barricas de bodega vinícola: silencio cálido, un leve goteo distante y crujidos suaves de madera. A lo lejos, desde otra sala, una voz masculina llama con tono normal y cotidiano; después, silencio sin respuesta. Sin música.',
            'audio_context' => 'Alguien llama con naturalidad a Emilio, el anfitrión, para avisarle de que la cena está casi lista. No hay prisa ni alarma en su voz. El silencio posterior debe resultar inquietante solo en retrospectiva.',
            'audio_speaker' => 'Speaker 1 - Voz distante desde otra sala',
            'audio_voice' => 'Orus',
            'audio_script' => <<<'TXT'
                ¿Don Emilio? La cena está casi lista, lo esperamos arriba.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — la familia, el negocio y la cosecha',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — una hoja de inventario',
            'source_file' => 'pista-privada-inventario.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: partial private glimpses (V5
            // fragment, file v5a) must not satisfy any gate on their own. The
            // complete V5 arrives at T+36.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — una fotografía de archivo',
            'source_file' => 'pista-privada-terremoto.md',
            'delivery_mode' => 'random_player',
            // V4 PARTIAL (file v4a, no caption). The complete V4 arrives at T+36.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Desarrollo — la mirada del comprador',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            // V9 was never scheduled in the design doc. Federico's
            // confrontation (open from T+36) needs it, so it goes here.
            'evidence_codes' => ['V9'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Complicación — el intercambio no fue reciente',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // Complete V4 and V5 (the design only ever delivered fragments),
            // plus V6 and V7. Confrontations open here; each md gates on the
            // concrete evidence it needs.
            'evidence_codes' => ['V4', 'V5', 'V6', 'V7'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Giro — lo que Emilio iba a decir esa noche',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // V13 moved here from T+62 so Ramona's first confrontation
            // (Sofía + V13) works soon after the T+36 unlock.
            'evidence_codes' => ['V8', 'V10', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 62,
            'title' => 'Cierre — el arma, el dinero y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // V12 was never scheduled in the design doc.
            'evidence_codes' => ['V12', 'V14'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 70,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para resolver el caso. Queda habilitado el formulario de acusación final: además de a quién acusan, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-anada-perdida/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-bodega.png', 'caption' => 'Plano de la bodega'],
            ['file' => 'v11-sala-de-barricas.png', 'caption' => 'La sala de barricas, fotografiada al hacer el hallazgo'],
        ],
        'expansion.md' => [
            ['file' => 'v2-arbol-familiar.png', 'caption' => 'Estructura familiar y de propiedad de la bodega'],
            ['file' => 'v3-vendimia-1979.png', 'caption' => 'Fotografía de archivo de la vendimia de 1979'],
        ],
        'pista-privada-inventario.md' => [
            ['file' => 'archivo-hoja-inventario.png', 'caption' => 'Fragmento de una hoja de inventario de la bodega'],
        ],
        'pista-privada-terremoto.md' => [
            ['file' => 'archivo-foto-cava.png', 'caption' => 'Fragmento de una fotografía de archivo'],
        ],
        'desarrollo.md' => [
            ['file' => 'v9-informe-tasacion.png', 'caption' => 'Informe de tasación preliminar del comprador'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-terremoto-cava-1986.png', 'caption' => 'La cava de añadas tras el sismo de 1986'],
            ['file' => 'v5-inventario-historico.png', 'caption' => 'Hoja de inventario histórico de la bodega'],
            ['file' => 'v6-comparacion-etiquetas.png', 'caption' => 'Dos etiquetas de la Cosecha 1979, lado a lado'],
            ['file' => 'v7-carta-horacio.png', 'caption' => 'Carta manuscrita de Horacio Larrea'],
        ],
        'giro.md' => [
            ['file' => 'v8-registro-ventas-antiguas.png', 'caption' => 'Registro de ventas históricas de la Cosecha 1979'],
            ['file' => 'v10-mensaje-emilio-ramona.png', 'caption' => 'Mensajes entre Emilio y Ramona, días antes'],
            ['file' => 'v13-registro-acceso-barricas.png', 'caption' => 'Libro de acceso a la sala de barricas'],
        ],
        'cierre.md' => [
            ['file' => 'v12-mazo-embutir.png', 'caption' => 'Mazo de embutir barricas, fuera de su soporte'],
            ['file' => 'v14-requerimiento-financiero.png', 'caption' => 'Requerimiento de pago de una entidad financiera'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v7-carta-horacio.png', 'caption' => 'El origen — la decisión de 1986'],
            ['file' => 'v8-registro-ventas-antiguas.png', 'caption' => 'Quién vendió las botellas'],
            ['file' => 'v13-registro-acceso-barricas.png', 'caption' => 'Quién entró tras Emilio'],
            ['file' => 'v12-mazo-embutir.png', 'caption' => 'El arma'],
            ['file' => 'v14-requerimiento-financiero.png', 'caption' => 'Por qué necesitaba que la venta se cerrara'],
        ],
    ],
];
