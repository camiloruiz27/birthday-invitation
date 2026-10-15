<?php

/**
 * Case manifest: "El Último Lote"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "false forgery" case. The table will spend a good
 * part of the game looking for an artistic forgery (a wrong pigment, a dubious
 * signature) that does not exist: the painting is genuine and the lab report
 * proves it. What is false is the provenance — the 2006 bill of sale refers to
 * a succession that never existed, hiding that the piece was stolen from the
 * Vega family in 1978. The artist (Augusto Ferrán) and the painting are
 * fictional: no image prompt may imitate a real artist or a real work; the
 * deduction rests on frames, labels, dates and documents, never on style.
 * The lab, the insurance registry and the access log never fail: whatever
 * they show is exact.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 5-field form and point
 *    scoring are NOT implemented; the two extra items (which part of the
 *    story was falsified, and who benefited) are asked for inside the free-text
 *    motive (see the T+85 unlock text) and explained in solucion.md.
 *  - Question budget: every interrogable person has 5 questions, plus 3 more
 *    (8) when the case has confrontations for them; the 3 extra are a separate
 *    pool, and each person's questions can be opened in stages
 *    (interrogation.stages) that wait on delivered evidence codes. The
 *    stage that opens a confrontation waits on the evidence its md requires.
 *  - A2 (Gabriel's confession) is NOT a timeline audio: it is
 *    solution.confession_script, generated per game. The only timeline audio
 *    is A1 (the auction-room ambience, Gancho).
 *  - V5 and V6 were only ever scheduled as partial private fragments at T+26;
 *    their complete versions were never delivered (although N3 rests on them
 *    and Federico's confrontation needs V6). The fragments keep their own
 *    files (archivo-foto-salon / archivo-documento-antiguo, no evidence code); the complete V5 and V6 are delivered
 *    at T+52 with the provenance documents.
 *  - V15 (the private agreement between Mateo and Margarita) was never
 *    scheduled although Mateo's confrontation requires it. Delivered at T+52,
 *    in its own email, the moment confrontations open.
 *  - Gabriel's confrontation 1 was tied to T+52 but requires V13 (access log),
 *    which the design delivers at T+65. The md gates it on the evidence itself,
 *    so it effectively opens at T+65 together with confrontation 2.
 *  - The doc says the two confrontations unlock "after T+39" in section 5/8
 *    and at T+52/T+65 in section 6 and in the JSON. T+52/T+65 prevail: that is
 *    where the evidence that sustains them arrives, and the engine does not
 *    gate by time anyway.
 *  - Section 3.8 / the JSON let the lab-and-insurance system answer "yes, the
 *    work is authentic" from the first interrogation (T+13), which would kill
 *    the false-forgery lead 26 minutes before the design reveals it (T+39).
 *    The system only confirms the lab result once V10 is delivered, and the
 *    access log once V13 is delivered. Same for Sofía, who says "a serious
 *    problem with the lot" until the table holds V10/V12, and only then
 *    "provenance". Olga mentions "old labels" in general terms until V9.
 *  - The doc never gives clock times for the night. They were fixed here and
 *    kept identical in every file: reunions end at 19:00; the death window is
 *    21:30–22:45; Lucía is alive at 20:05 (Don Ramiro's round); Gabriel is seen
 *    heading for the archive about 21:45, enters at 21:47 and leaves at 22:34;
 *    Don Ramiro finds the body at 23:21. Cross alibis (T+78): Teresa (office,
 *    Sofía until 22:45), Margarita (doorman + video call 21:15–22:50), Mateo
 *    (night train 20:40–23:40), Federico (dinner, card payment 22:58), Olga
 *    (workshop card reader 20:10–23:10 + two client calls).
 *  - The doc is ambiguous about WHO wrote the 2006 bill of sale. Resolved as:
 *    the 2006 paper was vague, and Teresa, pressed by Gabriel, added the name
 *    of the (non-existent) succession and the extra exhibition records. This
 *    is what she admits in confrontation 1 ("fabriqué esa parte del
 *    documento"). The invented names (the succession of Aurelio Ibarra Cantú,
 *    Margarita's late husband Ernesto Roldán, Lot 27, the frame defect and the
 *    label code V-62/147) are continuity devices, not plot.
 *  - Don Ramiro Castillo's "testimony" at T+65 (Giro) is not a delivery: he is
 *    interrogable from T+13 together with the other witnesses. The Giro text
 *    simply sends the table back to him.
 *  - The T+65/T+78 fail-safes are written into the personas: Teresa highlights
 *    Gabriel's pressure when asked again once V11 is delivered, and the T+78
 *    email delivers the cross alibis to everyone.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'El Último Lote',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Casa Belmonte',
    'authority' => 'Comité independiente de la Junta de Casa Belmonte',

    'victim' => [
        'name' => 'Lucía Montalvo',
        'label' => 'Víctima',
        'photo' => 'lucia-montalvo.png',
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
        'tagline' => 'La noche antes de la venta, la especialista que certificó la obra aparece muerta. Su nota solo dice una cosa.',

        'description' => <<<'TXT'
            Casa Belmonte prepara la venta privada de su lote estrella: un
            óleo atribuido al pintor Augusto Ferrán. La noche antes, Lucía
            Montalvo, la especialista que certificó la obra, aparece muerta
            en la sala de archivo, junto a una breve nota.

            Tu equipo (5 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos y a dos testigos,
            y consultar los registros de la casa, durante 85 minutos. En el
            mundo del arte, autenticidad, reputación, herencia y propiedad
            son cosas distintas que a menudo se confunden.

            Aquí hay más de un secreto y más de una mentira. Pero solo una
            persona mató. Saber dónde buscar es lo que resuelve el caso.
            TXT,

        // Archivo esperado en public/immersion/el-ultimo-lote/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-ultimo-lote).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Una casa de subastas prepara la venta de su lote estrella. La especialista que lo certificó aparece muerta, con una nota que no explica nada.',
            'bullets' => [
                '5 a 8 investigadores cruzando documentos, registros de acceso y testimonios',
                'Seis sospechosos, dos testigos y los registros de la casa a quienes interrogar',
                '85 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 85,
        'min_players' => 4,
        'max_players' => 8,

        'price_amount' => 69900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/el-ultimo-lote/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 17,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Sofía, Don Ramiro) and the secondary source (the lab
    | and insurance registry) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'gabriel-oquendo' => [
            'name' => 'Gabriel Oquendo',
            'role' => 'sospechoso',
            'file' => 'suspects/gabriel-oquendo.md',
            'photo' => 'gabriel-oquendo.png',
            'connection' => 'Director de Casa Belmonte; superior jerárquico de Lucía',
            'motive' => 'Impedir que Lucía detuviera la venta y expusiera la procedencia fabricada que él mismo había presionado a crear',
            'alibi' => 'Sostiene que no vio a Lucía después de la reunión de la tarde',
            'public' => [
                'age' => '54 años',
                'profile' => 'Director de Casa Belmonte. Elegante, sereno y encantador en público; lleva años al frente de la casa y vive de cerca la presión por cerrar esta venta.',
                'alibi' => 'Dice que terminó su jornada y se fue a casa, y que no volvió a ver a Lucía tras la reunión de la tarde.',
                'facts' => [
                    'Es el superior jerárquico de Lucía y de todo el equipo de la casa.',
                    'La comisión de esta venta pesa mucho en las cuentas de la casa tras unos meses comerciales difíciles.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V13'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V7', 'V8', 'V11'], 'confrontation' => true],
            ]],
        ],
        'teresa-andrade' => [
            'name' => 'Teresa Andrade',
            'role' => 'sospechosa',
            'file' => 'suspects/teresa-andrade.md',
            'photo' => 'teresa-andrade.png',
            'connection' => 'Especialista en procedencia; colega de Lucía en el mismo departamento',
            'motive' => 'Ninguno respecto a la muerte; fabricó parte de la documentación de procedencia bajo presión, por miedo a perder su puesto (falta distinta)',
            'alibi' => 'En su oficina revisando otros lotes, confirmada por Sofía Reyes',
            'public' => [
                'age' => '41 años',
                'profile' => 'Especialista en procedencia: reúne y verifica los documentos que respaldan la historia de cada pieza. Meticulosa, siempre con una carpeta de archivo en la mano.',
                'alibi' => 'Dice haber pasado la noche en su oficina revisando otros lotes.',
                'facts' => [
                    'Compiló la documentación de procedencia del lote estrella.',
                    'Trabaja en el mismo departamento que Lucía.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V7', 'V8'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V11'], 'confrontation' => true],
            ]],
        ],
        'margarita-soler' => [
            'name' => 'Margarita Soler',
            'role' => 'sospechosa',
            'file' => 'suspects/margarita-soler.md',
            'photo' => 'margarita-soler.png',
            'connection' => 'Consignataria: propietaria actual de la obra; cliente de la casa',
            'motive' => 'Ninguno respecto a la muerte; sospechaba que algo no cuadraba en la adquisición de su difunto esposo y prefirió no indagar',
            'alibi' => 'En su domicilio esa noche, confirmable por el portero y por una videollamada con su hija',
            'public' => [
                'age' => '62 años',
                'profile' => 'Consignataria y propietaria actual de la obra. Elegante, reservada y muy protectora de la memoria de su difunto esposo.',
                'alibi' => 'Dice haber pasado la noche en su domicilio.',
                'facts' => [
                    'Recibió la obra como herencia de su esposo.',
                    'La venta es hoy una de sus principales fuentes de ingresos.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V7', 'V8'], 'confrontation' => true],
            ]],
        ],
        'mateo-vega' => [
            'name' => 'Mateo Vega',
            'role' => 'sospechoso',
            'file' => 'suspects/mateo-vega.md',
            'photo' => 'mateo-vega.png',
            'connection' => 'Descendiente de la familia que dice haber tenido un vínculo con la obra décadas atrás',
            'motive' => 'Ninguno respecto a la muerte; necesitaba que la verdad avanzara, y negocia en privado un acuerdo económico con Margarita',
            'alibi' => 'En un viaje nocturno en tren, con billete y registros de la estación',
            'public' => [
                'age' => '37 años',
                'profile' => 'Descendiente de la familia que dice haber tenido un vínculo con la obra. Decidido, de aspecto cuidado pero sin la elegancia formal del resto, y receloso de la casa de subastas.',
                'alibi' => 'Dice que esa noche estaba de viaje.',
                'facts' => [
                    'Escribió a la casa semanas antes de la venta para plantear dudas sobre la obra.',
                    'Tendría un interés directo en reclamarla si se confirmara el relato de su familia.',
                ],
            ],
            'interrogation' => ['stages' => [
                // His family history (1962 photo, 1978 theft and insurance)
                // is only told once V5 and V6 are on the table (T+52).
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V5', 'V6']],
                ['questions' => 3, 'after' => ['V15'], 'confrontation' => true],
            ]],
        ],
        'federico-lara' => [
            'name' => 'Federico Lara',
            'role' => 'sospechoso',
            'file' => 'suspects/federico-lara.md',
            'photo' => 'federico-lara.png',
            'connection' => 'Representante de la aseguradora vinculada a la obra',
            'motive' => 'Ninguno respecto a la muerte; negocia en privado una comisión personal a cambio de facilitar una resolución discreta',
            'alibi' => 'En una cena de negocios, confirmable por el restaurante y el pago con tarjeta',
            'public' => [
                'age' => '49 años',
                'profile' => 'Representante de una aseguradora con interés histórico en la pieza. Calculador, cuidadoso con las palabras y con los números.',
                'alibi' => 'Dice que cenaba con colegas esa noche.',
                'facts' => [
                    'Mantuvo contacto profesional con Lucía en los días previos por la revisión de la historia de la pieza.',
                    'Su compañía tendría derechos sobre la obra bajo ciertas condiciones.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V6', 'V15'], 'confrontation' => true],
            ]],
        ],
        'olga-fischer' => [
            'name' => 'Olga Fischer',
            'role' => 'sospechosa',
            'file' => 'suspects/olga-fischer.md',
            'photo' => 'olga-fischer.png',
            'connection' => 'Restauradora de la casa; colega técnica de Lucía',
            'motive' => 'Ninguno respecto a la muerte; realiza trabajos de restauración no autorizados para clientes privados con materiales de la casa',
            'alibi' => 'En su taller, con un registro de horas parcialmente incompleto por su propio trabajo extra',
            'public' => [
                'age' => '56 años',
                'profile' => 'Restauradora de la casa. Trabajó directamente sobre la obra y conoce su estado físico mejor que nadie. Técnica, concreta y reservada sobre su propio trabajo.',
                'alibi' => 'Dice haber estado en su taller.',
                'facts' => [
                    'Fue quien restauró el lote estrella antes de la venta.',
                    'Fotografía todo el proceso de restauración de cada pieza.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V9'], 'confrontation' => true],
            ]],
        ],
        'sofia-reyes' => [
            'name' => 'Sofía Reyes',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-reyes.md',
            'photo' => 'sofia-reyes.png',
            'connection' => 'Asistente de autenticación; trabajaba con Lucía',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '28 años',
                'profile' => 'Asistente de autenticación: trabajaba codo con codo con Lucía en el expediente del lote estrella.',
                'facts' => [
                    'Habló con Lucía durante la tarde previa a su muerte.',
                    'Esa noche se quedó trabajando hasta tarde en el corredor del departamento.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V10']],
                // Her "ficha cerrada" overheard line is only told with V11.
                ['questions' => 1, 'after' => ['V11']],
            ]],
            'accusable' => false,
        ],
        'ramiro-castillo' => [
            'name' => 'Don Ramiro Castillo',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/ramiro-castillo.md',
            'photo' => 'ramiro-castillo.png',
            'connection' => 'Guardia de seguridad del turno de noche',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '60 años',
                'profile' => 'Guardia de seguridad del turno de noche. Metódico y formal; conoce las rondas y los accesos de la casa.',
                'facts' => [
                    'Hizo la ronda en la que se encontró a Lucía.',
                    'Recorre las salas de la casa en rondas fijas durante toda la noche.',
                ],
            ],
            // His md only names whom he saw heading for the archive room once
            // V13 (access log, delivered with V11 at T+65) is on the table.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V13']],
            ]],
            'accusable' => false,
        ],
        'sistema-laboratorio-y-aseguramiento' => [
            'name' => 'Laboratorio de análisis material y registro de aseguramiento',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-laboratorio-y-aseguramiento.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-produccion") — a shot of
            // a lab/registry terminal works as its "portrait".
            'photo' => 'sistema-laboratorio-y-aseguramiento.png',
            'connection' => 'Informe de análisis material, reclamos históricos de seguro y registro de accesos',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente técnica de la casa: reúne el informe de análisis material de la obra, el histórico de reclamos de seguro y el registro de accesos a las salas, sin interpretar nada.',
                'facts' => [
                    'Registra la hora exacta de entrada y de salida de cada tarjeta en la sala de archivo.',
                    'No puede decir quién es el propietario legítimo de la obra.',
                ],
            ],
            'interrogation' => ['stages' => [
                ['questions' => 2],
                ['questions' => 1, 'after' => ['V6']],
                ['questions' => 1, 'after' => ['V10']],
                ['questions' => 1, 'after' => ['V13']],
            ]],
            'accusable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The ending
    |--------------------------------------------------------------------------
    */

    'solution' => [
        'culprit_slug' => 'gabriel-oquendo',

        'headline' => 'La obra era auténtica; lo falso era su historia. Gabriel Oquendo presionó a Teresa para fabricar una procedencia limpia que ocultaba que el óleo fue robado a la familia Vega en 1978, y mató a Lucía Montalvo en la sala de archivo cuando ella iba a detener la venta.',

        'motive' => 'Impedir que Lucía detuviera la venta y expusiera la fabricación de la procedencia que él mismo había presionado a crear, de la que dependían la comisión y la estabilidad de Casa Belmonte.',

        'method' => 'Golpe con un busto decorativo de los que sirven de sujetalibros en la sala de archivo, entre las 21:47 y las 22:34, mientras Gabriel estaba a solas con ella. Después limpió el busto y lo dejó en otro estante.',

        'key_evidence' => [
            'Don Ramiro Castillo vio a Gabriel dirigirse hacia la sala de archivo pasadas las 21:45, mucho después del horario habitual de reuniones.',
            'El registro de acceso confirma su entrada a las 21:47 y su salida a las 22:34, dentro de la franja de la muerte.',
            'El busto apareció fuera de su posición y notablemente más limpio que los objetos que lo rodeaban.',
            'La sucesión del documento de compra de 2006 nunca existió, y la etiqueta antigua del reverso de la obra coincide con el inventario familiar de 1962 y con el reclamo de seguro de 1978.',
            'El chat guardado con Teresa muestra la presión de Gabriel para que cerrara los vacíos de la procedencia antes de la venta.',
            'La nota de Lucía era literalmente cierta: la obra no era falsa; el problema era su procedencia. Ni Teresa, ni Margarita, ni Mateo, ni Federico, ni Olga pudieron golpear a Lucía: cada uno tiene coartada verificable y un secreto propio que no requería su muerte.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster person that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'teresa-andrade' => 'Sí fabricó parte de la documentación de procedencia: añadió al documento de compra de 2006 el nombre de una sucesión inexistente y redactó registros de exhibición que no eran reales, presionada por Gabriel y por miedo a perder su puesto. Es una falta real, pero ajena a la muerte: su motivo se satisface con el silencio, y la muerte de Lucía solo atraía más escrutinio sobre su trabajo. Estuvo en su oficina toda la franja, con Sofía Reyes en el mismo corredor hasta las 22:45.',

            'margarita-soler' => 'Tenía dudas sobre cómo su difunto esposo adquirió la obra y prefirió no indagar; es una omisión, no un crimen. Heredó una historia de propiedad ya construida sobre una base falsa que nunca cuestionó a fondo. Estuvo en su domicilio toda la franja: el portero confirma que no salió y estuvo en videollamada con su hija de 21:15 a 22:50. Ni siquiera conocía a Lucía.',

            'mateo-vega' => 'Negociaba en privado con Margarita una compensación a cambio de no reclamar públicamente, algo que prefería ocultar. Pero necesitaba que la investigación de Lucía avanzara, no que se detuviera con un asesinato. Viajaba en un tren nocturno, con billete y registros de la estación que lo sitúan a bordo de 20:40 a pasadas las 23:40.',

            'federico-lara' => 'Negociaba una comisión personal a cambio de facilitar una resolución discreta entre las partes, y por eso ocultó su interés. Un asesinato era lo peor que podía pasarle a ese arreglo. Cenaba con dos directivos de otra compañía: la reserva del restaurante y el pago con tarjeta a las 22:58 lo confirman.',

            'olga-fischer' => 'Hacía trabajos de restauración no autorizados para clientes privados usando materiales de la casa, y por eso era evasiva con su horario. Es una falta menor, sin relación con la muerte; de hecho su fotografía de la etiqueta antigua en el reverso de la obra fue una de las claves del caso. Estuvo en su taller: el lector de tarjeta de la puerta registra su entrada a las 20:10 y su salida a las 23:10, y mantuvo dos llamadas de trabajo con un cliente externo.',

            'sofia-reyes' => 'Es una testigo, no una sospechosa: su aporte fue confirmar que Lucía iba a detener la venta y que Teresa estuvo en su oficina toda la noche. No tenía motivo alguno ni acceso a la sala de archivo esa noche; salió del edificio a las 22:45.',

            'ramiro-castillo' => 'Es un testigo, no un sospechoso: su aporte fue declarar que vio a Gabriel dirigirse hacia la sala de archivo después del horario habitual y que encontró a Lucía en su ronda de las 23:21. No tenía motivo alguno, y el registro de accesos coincide con lo que contó.',

            'sistema-laboratorio-y-aseguramiento' => 'Es un sistema automatizado de registros y análisis, no una persona: no puede haber intervenido. Su función fue confirmar, con exactitud, que la obra es físicamente auténtica, que el reclamo de seguro de 1978 existió y se pagó, y la hora de entrada y salida de cada tarjeta en la sala de archivo, sin interpretar propiedad ni intenciones. Nunca falló: lo que mostró era cierto.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Orus',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            La casa llevaba dos años de pérdidas. Esta venta lo era todo. Le pedí a
            Teresa que cerrara los vacíos de la procedencia, sin preguntar demasiado cómo.

            Cuando Lucía me dijo que sabía de dónde venía realmente la obra, y que
            pensaba detenerlo todo esa misma noche, no lo pensé con calma. La golpeé ahí
            mismo, en el archivo, con el busto que sostenía los libros. Después limpié el
            busto y lo dejé en otro estante. Traté de dejarlo todo como si nada hubiera
            pasado.

            Me repetí que lo hacía por la casa, por toda la gente que trabaja aquí. Que
            podía arreglarlo después. Nada de eso era verdad.

            No debí presionar a Teresa así. No debí hacer nada de esto.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Con qué y dónde la mataron?',
            'hint' => 'El objeto usado, el lugar y qué se hizo después con él.',
            'points' => 12,
            'guidance' => 'Lo esencial: golpe con el busto decorativo (sujetalibros) en la sala de archivo. No exigir el horario exacto.',
        ],
        'motive' => [
            'label' => '¿Por qué la mataron?',
            'hint' => 'Qué iba a hacer Lucía esa noche y qué quedaba expuesto si lo hacía.',
            'points' => 13,
        ],
        'questions' => [
            [
                'key' => 'parte_falsificada',
                'label' => '¿Qué se falsificó exactamente en torno a la obra?',
                'hint' => 'Sé preciso: señala qué objeto o documento es falso y en qué consiste la falsedad.',
                'answer' => 'La pintura es auténtica (lo confirma el laboratorio); lo falsificado es su procedencia: el documento de compra de 2006, completado con la referencia a una sucesión que nunca existió, y unos registros de exhibición inventados. Ocultaba que el óleo fue robado a la familia Vega en 1978.',
                'points' => 25,
                'guidance' => 'Cero si sostiene que la obra es falsa. Puntos completos con: obra auténtica + procedencia/documento de 2006 fabricado (sucesión inexistente). El robo de 1978 suma, pero no es obligatorio.',
            ],
            [
                'key' => 'beneficiarios',
                'label' => '¿Quién se beneficiaba de esa falsificación?',
                'hint' => 'Nombra a las personas o entidades y qué ganaba cada una.',
                'answer' => 'Gabriel Oquendo y Casa Belmonte, cuya comisión era crucial para su estabilidad; Teresa Andrade, que conservaba su puesto; y, de forma indirecta y sin saberlo con certeza, Margarita Soler, que había heredado una historia de propiedad que nunca cuestionó.',
                'points' => 20,
                'guidance' => 'Repartir por beneficiario: Gabriel/Casa Belmonte es lo principal; Teresa y Margarita (indirecta) completan. Restar si presenta a Margarita como cómplice consciente.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+13/T+26/T+39/T+52/T+65/T+78/T+85
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as
    | the other cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El Último Lote — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V3', 'V4'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — ensayo en la sala de subasta',

            'audio_file' => 'a1-ambiente-sala-subasta.wav',
            'audio_scene' => 'Ambiente sonoro de una sala de subasta elegante durante un ensayo previo a la venta: murmullo distante, un golpe suave de martillo de subasta a lo lejos y una voz profesional que ensaya la presentación del lote final. Sin música.',
            'audio_context' => 'La voz de un subastador ensaya con tono profesional, elegante y neutro la presentación del lote final, ajeno por completo a lo que ocurrirá esa noche. Sin tensión ni dramatismo.',
            'audio_speaker' => 'Speaker 1 - Subastador (ensayo)',
            'audio_voice' => 'Sadaltager',
            'audio_script' => <<<'TXT'
                Lote final: atribuido a Augusto Ferrán. Comenzamos la puja en...
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 13,
            'title' => 'Expansión — la obra, la venta y quienes la rodean',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V16'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — una fotografía antigua',
            'source_file' => 'pista-privada-fotografia.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: partial private glimpses (V5
            // fragment) must not satisfy any gate on their own. The complete
            // V5 arrives at T+52.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — un documento de archivo',
            'source_file' => 'pista-privada-reporte.md',
            'delivery_mode' => 'random_player',
            // V6 PARTIAL (no description, no outcome). The complete V6 arrives
            // at T+52 and is what Federico's confrontation requires.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 39,
            'title' => 'Complicación — lo que la obra sí es',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V10', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Confrontación de procedencia — se habilitan las confrontaciones',
            'source_file' => 'procedencia.md',
            'delivery_mode' => 'all',
            // The complete V5 and V6 are added here during authoring: the
            // design only ever delivered their fragments.
            'evidence_codes' => ['V5', 'V6', 'V7', 'V8', 'V9'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Un papel mezclado entre los documentos de titularidad',
            'source_file' => 'acuerdo-privado.md',
            'delivery_mode' => 'all',
            // V15 was never scheduled in the design, but Mateo's confrontation
            // (open from T+52) requires it.
            'evidence_codes' => ['V15'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Giro — la presión y la presencia',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V11', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 78,
            'title' => 'Cierre — el objeto y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V14'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 85,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para separar la obra, su historia y su asesinato. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-ultimo-lote/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-casa-belmonte.png', 'caption' => 'Plano de Casa Belmonte'],
            ['file' => 'v3-sala-de-archivo.png', 'caption' => 'La sala de archivo, tal como se encontró'],
            ['file' => 'v4-nota-hallada.png', 'caption' => 'La nota hallada junto a Lucía'],
        ],
        'expansion.md' => [
            ['file' => 'v2-obra-catalogo.png', 'caption' => 'El Lote 27, fotografía oficial del catálogo'],
            ['file' => 'v16-catalogo-subasta.png', 'caption' => 'El catálogo de la subasta, abierto en el lote estrella'],
        ],
        'pista-privada-fotografia.md' => [
            ['file' => 'archivo-foto-salon.png', 'caption' => 'Fragmento de una fotografía de archivo familiar'],
        ],
        'pista-privada-reporte.md' => [
            ['file' => 'archivo-documento-antiguo.png', 'caption' => 'Fragmento de un documento administrativo antiguo'],
        ],
        'complicacion.md' => [
            ['file' => 'v10-informe-laboratorio.png', 'caption' => 'Informe del laboratorio de análisis material'],
            ['file' => 'v12-notas-lucia.png', 'caption' => 'Notas de trabajo de Lucía'],
        ],
        'procedencia.md' => [
            ['file' => 'v5-inventario-familiar-1962.png', 'caption' => 'Fotografía de inventario familiar, 1962'],
            ['file' => 'v6-reporte-robo-seguro-1978.png', 'caption' => 'Reporte de reclamo de seguro, 1978'],
            ['file' => 'v7-compra-2006.png', 'caption' => 'Documento de compra de 2006'],
            ['file' => 'v8-busqueda-sucesion.png', 'caption' => 'Resultado de la búsqueda de la sucesión citada'],
            ['file' => 'v9-etiqueta-reverso.png', 'caption' => 'El reverso de la obra, fotografiado durante la restauración'],
        ],
        'acuerdo-privado.md' => [
            ['file' => 'v15-borrador-acuerdo.png', 'caption' => 'Borrador de acuerdo privado'],
        ],
        'giro.md' => [
            ['file' => 'v11-chat-gabriel-teresa.png', 'caption' => 'Conversación de mensajería interna'],
            ['file' => 'v13-registro-acceso-archivo.png', 'caption' => 'Registro de acceso a la sala de archivo'],
        ],
        'cierre.md' => [
            ['file' => 'v14-busto-sujetalibros.png', 'caption' => 'Un busto decorativo fuera de su posición habitual'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v8-busqueda-sucesion.png', 'caption' => 'La sucesión del documento de 2006 nunca existió'],
            ['file' => 'v9-etiqueta-reverso.png', 'caption' => 'La etiqueta antigua: la obra ya tenía dueño'],
            ['file' => 'v11-chat-gabriel-teresa.png', 'caption' => 'La presión para cerrar los vacíos'],
            ['file' => 'v13-registro-acceso-archivo.png', 'caption' => 'Quién estaba en el archivo esa noche'],
            ['file' => 'v14-busto-sujetalibros.png', 'caption' => 'El arma, limpiada y cambiada de sitio'],
        ],
    ],
];
