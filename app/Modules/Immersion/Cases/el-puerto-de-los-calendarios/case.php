<?php

/**
 * Case manifest: "El puerto de los calendarios"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it: identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: TWO linked but distinct acts with TWO different people
 * responsible. Paulina Rengifo (customs agent) had the Lote 47 release permit
 * dated 31 December although the lot only moved on 2 January, under pressure
 * from her client (Importadora Rumbo Norte); Leonel Bravo (yard supervisor)
 * adjusted the declared-exit date in the movement register at her request, with
 * his own user. Aurelio Nieto (inspector, the victim) saw the mismatch and went
 * to talk to the person who could have touched the register: Leonel. There was a
 * brief scuffle and Aurelio, tripping over a lashing chain, hit his head on a
 * container. Leonel left without calling for help. The solution's culprit_slug
 * is Leonel (death); Paulina and the method of the fiscal irregularity are asked
 * for through accusation.questions. DESIGN PRINCIPLE: the fiscal change of regime
 * NEVER identifies the killer by itself. Naming Leonel needs three independent
 * supports: Wilson's testimony (DW), the corridor CCTV frame (V08) and the
 * register's user trail (V04 + V07 + A1) plus the confrontation. Three fictional
 * rules (V02) and four zones (V01) from T+0; no real customs rule is used.
 *
 * Reference timetable (single source of truth for every file of this case; all
 * dates, hours and names in evidence, md files and manifest derive from it):
 *   Mid-October: Lote 47 (industrial machinery, Importadora Rumbo Norte) enters
 *   the free zone under preferential transit.  Mid-December: free-zone inventory
 *   count, lot still there.  Late December: release request filed.  30 Dec: the
 *   release stays pending (no slot in the general warehouse; reduced shifts for
 *   the holidays).  31 Dec: the release permit is issued with release date
 *   "31 de diciembre" (V03).  2 Jan: the lot leaves the free zone ~06:40 and the
 *   warehouse gate reader logs its entry at 06:52 (V04, "movimiento fisico");
 *   at 18:41 user lbravo.patio edits the declared-exit date of Lote 47 to
 *   31 Dec (V04, "ultima modificacion").
 *   3 Jan (the death): ~07:10 Aurelio writes the inspection act (V06); ~07:35 he
 *   records the voice note at the yard (A1, draft never sent); 09:30 the "reunion
 *   de oficinas de Ano Nuevo" starts (Paulina, Rogelio, Ismael, Gustavo, Daniel;
 *   Claudia keeps the register and stands by the door; nobody leaves);
 *   09:47:20 corridor CCTV frame, two uniformed figures talking (V08, cam
 *   PATIO-C2); ~09:55 Leonel walks away alone, shaken (Wilson); ~10:05 Wilson
 *   finds the body; 10:20 the meeting is interrupted by the news.  Verónica was
 *   counting inventory alone in the free zone (no outside confirmation).
 *   Witnesses and suspects remember by places/people/events, never by clock
 *   hour; only registers (Claudia's, V04, V08) give hours.
 *
 * Adaptations made while porting the design doc to the engine (kept here so the
 * next author does not "fix" them back):
 *  - The engine scores only the culprit. The doc's 5-field form and point scoring
 *    (30/20/25/25 and the "fiscal change as automatic proof" penalty) are folded
 *    into culprit_points 30 + weapon ("que ocurrio", 10) + motive (10) + two
 *    accusation.questions (Paulina 25, method 25) = 100; the penalty is expressed
 *    as "cero" guidance on the weapon and motive criteria. The T+110 unlock text
 *    asks for the second responsible and the method.
 *  - Questions: 5 per person (up to), plus 3 for those with confrontations (8).
 *    The doc's "4 + 2 / 1 confrontations" is folded into that engine rule.
 *  - The doc says V04 shows the real movement date AND that Leonel adjusted the
 *    register "so that it matched the paper": contradictory if the register just
 *    showed one date. Resolved with a register that has three fields per lot:
 *    "salida declarada" (editable, edited to 31 Dec), "movimiento fisico" (read
 *    by the gate reader, 2 Jan 06:52) and "ultima modificacion" (user + time).
 *    Only Lote 47 has a user edit; the other lots are "Sistema (automatica)".
 *  - The doc places V08 on "the morning of the discovery" (3 Jan) yet lists it as
 *    support of N2 (physical movement 2 Jan). It cannot be both: V08 stays the
 *    3 Jan frame (N6/N7) and N2 is supported by V04, V05 (lot in the warehouse)
 *    and Camilo (after V04).
 *  - The doc's summary says Aurelio found the issue "days before"; its timeline
 *    and A1 say it was the morning of 3 Jan. The timeline wins (A1: "esta manana").
 *  - Aurelio's act (V06) cites only permit and register, no CCTV (the doc says he
 *    also crossed a frame; that frame would be an extra unlisted image).
 *  - V07 lists two cargos with permission to adjust: Supervisor del patio de
 *    contenedores and Gerencia general (administrator permission, not used in
 *    routine). Everything else is "solo consulta". V04 shows the user handle
 *    lbravo.patio, never a full name.
 *  - Wilson is interrogable from T+14 (3 questions) but does not name anyone until
 *    DW, his written expanded declaration (T+85 "testimonio ampliado de Wilson",
 *    the fail-safe). V08's figures cannot be told apart; DW is what confirms them.
 *  - Because Leonel's two confrontations hinge on DW (T+85) they open at T+85, not
 *    at T+56 like the rest of the table. Paulina's open at T+56 / T+85; everyone
 *    else's wait for CRON (T+56, the aggregated chronology that also announces the
 *    confrontations). CRON doubles as the doc's T+56 fail-safe: the public,
 *    complete copy of the private fragments, naming no one.
 *  - Private clues: the Lote 47 chronology in four fragments (CR1-CR4, two entries
 *    each; the permit date and the real movement date sit in CR3 and CR4) and V02
 *    in three redundant pocket cards (two of the three rules each, no codes, and
 *    the public V02 at T+1). Each fragment is complete; nothing critical depends
 *    on one of them.
 *  - Verónica has no outside alibi (Claudia's register does not cover her): the
 *    doc lists Claudia as her support, which is not what her section says. Her
 *    exoneration rests on V07 (her cargo is "solo consulta"), on Wilson and on V10.
 *  - The doc's N8 and fail-safe cross-check "Rogelio, Ismael, Verónica, Gustavo and
 *    Daniel" with Claudia; Verónica is explicitly outside the meeting and Paulina
 *    inside, in cierre.md.
 *  - Claudia gets one extra harmless fact (Rogelio only receives monthly totals)
 *    so Rogelio's confrontation has something to hinge on; Mariana's testimony
 *    mentions the reduced holiday shifts (a cause beyond Paulina's control).
 *  - A2 (Leonel's confession) is NOT in the timeline: it is solution.confession_script
 *    (+ confession_voice), generated per game. A1 is a timeline audio event.
 *  - Yolanda and Camilo are roster entries with 'accusable' => false.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()). DW is
 * Wilson's written declaration (no image); CRON is the aggregated chronology (no
 * image); CR1-CR4 are the private chronology fragments.
 */
return [

    'name' => 'El puerto de los calendarios',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Puerto Altamar, Lote 47',
    'authority' => 'Comisión de Revisión de la Autoridad Aduanera Portuaria',

    'victim' => [
        'name' => 'Aurelio Nieto',
        'label' => 'Víctima',
        'photo' => 'aurelio-nieto.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 4 questions per suspect and 3 per witness, plus
        // confrontations. The engine rule is up to 5 + 3; see the stages below.
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
        'tagline' => 'La mercancía nunca estuvo en un lugar ilegal. Solo cambió de fecha en el papel equivocado.',

        'description' => <<<'TXT'
            Puerto Altamar, un puerto comercial con cuatro zonas y un calendario
            que lo cambia todo. Aurelio Nieto, inspector de la Autoridad Aduanera
            Portuaria, aparece sin vida en el patio de contenedores en plena
            revisión de cierre de año. Entre sus pendientes, una carga de
            maquinaria industrial: el Lote 47.

            Tu equipo (4 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a siete sospechosos, tres testigos y
            dos fuentes técnicas durante 110 minutos, cruzando documentos,
            registros, un fotograma de cámara de seguridad y una nota de voz.
            Todo el caso se sostiene sobre solo tres reglas y cuatro zonas,
            entregadas desde el principio: la dificultad no está en la
            normativa, sino en conectar la evidencia.

            Hay un papel con una fecha. Hay un hecho con otra. Y no todo el que
            movió un calendario movió también lo que pasó en el patio.
            TXT,

        // Archivo esperado en public/immersion/el-puerto-de-los-calendarios/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-puerto-de-los-calendarios).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'La mercancía nunca estuvo en un lugar ilegal. Solo cambió de fecha en el papel equivocado.',
            'bullets' => [
                '4 a 8 investigadores cruzando documentos, registros y un fotograma de cámara',
                'Siete sospechosos, tres testigos y dos fuentes técnicas a quienes interrogar',
                '110 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 110,
        'min_players' => 4,
        'max_players' => 8,

        'price_amount' => 79900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/el-puerto-de-los-calendarios/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 33,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | Wilson, Claudia and Mariana are witnesses; Yolanda (external inspector) and
    | Camilo (logistics agent) are delimited sources. Aurelio, the victim, is not
    | interrogable.
    |
    */

    'suspects' => [
        'leonel-bravo' => [
            'name' => 'Leonel Bravo',
            'role' => 'sospechoso',
            'file' => 'suspects/leonel-bravo.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['DW'], 'confrontation' => true],
                ['questions' => 2, 'after' => ['DW', 'V04', 'V08', 'A1'], 'confrontation' => true],
            ]],
            'photo' => 'leonel-bravo.png',
            'connection' => 'Supervisor del patio de contenedores; el patio administra los registros de movimiento que Aurelio revisaba',
            'motive' => 'Miedo a ser la única persona con una prueba técnica directa en su contra: un registro ajustado con su propio usuario',
            'alibi' => 'Dice que esa mañana estaba en otra zona del patio; sin coartada que lo confirme',
            'public' => [
                'age' => '41 años',
                'profile' => 'Supervisor del patio de contenedores. Directo y técnico, de pocas palabras.',
                'alibi' => 'Dice haber estado trabajando en el patio de contenedores esa mañana.',
                'facts' => [
                    'Su cargo tiene acceso al sistema de movimiento de carga.',
                    'Conoce el patio de contenedores mejor que nadie.',
                ],
            ],
        ],
        'paulina-rengifo' => [
            'name' => 'Paulina Rengifo',
            'role' => 'sospechosa',
            'file' => 'suspects/paulina-rengifo.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V03', 'V04', 'CRON'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V09'], 'confrontation' => true],
            ]],
            'photo' => 'paulina-rengifo.png',
            'connection' => 'Agente de aduanas responsable de la cuenta de la Importadora Rumbo Norte',
            'motive' => 'Presión comercial de un cliente importante que amenazaba con cambiar de agencia',
            'alibi' => 'En la reunión de oficinas de Año Nuevo esa mañana, confirmada por Claudia',
            'public' => [
                'age' => '46 años',
                'profile' => 'Agente de aduanas de la terminal de agentes. Profesional y segura de sí misma.',
                'alibi' => 'Dice haber estado en la reunión de oficinas de Año Nuevo.',
                'facts' => [
                    'Lleva la cuenta de la Importadora Rumbo Norte, dueña del Lote 47.',
                    'Rumbo Norte es uno de los clientes más importantes del puerto.',
                ],
            ],
        ],
        'rogelio-campuzano' => [
            'name' => 'Rogelio Campuzano',
            'role' => 'sospechoso',
            'file' => 'suspects/rogelio-campuzano.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['CRON'], 'confrontation' => true],
            ]],
            'photo' => 'rogelio-campuzano.png',
            'connection' => 'Gerente general de Puerto Altamar',
            'motive' => 'Ninguno respecto al caso; prefiere no indagar en los trámites de sus agentes mientras los números generales se vean bien',
            'alibi' => 'En la reunión de oficinas de Año Nuevo, que preside, confirmada por Claudia',
            'public' => [
                'age' => '58 años',
                'profile' => 'Gerente general del puerto. Institucional y cordial, muy atento a la reputación de Puerto Altamar.',
                'alibi' => 'Dice haber presidido la reunión de oficinas de Año Nuevo.',
                'facts' => [
                    'Es el máximo responsable institucional del puerto.',
                    'Rumbo Norte es un cliente que le interesa mantener contento.',
                ],
            ],
        ],
        'ismael-duarte' => [
            'name' => 'Ismael Duarte',
            'role' => 'sospechoso',
            'file' => 'suspects/ismael-duarte.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['CRON'], 'confrontation' => true],
            ]],
            'photo' => 'ismael-duarte.png',
            'connection' => 'Jefe de agentes aduaneros; supervisor directo de Paulina',
            'motive' => 'Ninguno respecto al caso; ha aceptado obsequios de otro cliente a cambio de prioridad en sus trámites',
            'alibi' => 'En la reunión de oficinas de Año Nuevo, confirmada por Claudia',
            'public' => [
                'age' => '50 años',
                'profile' => 'Jefe de los agentes aduaneros. Correcto, algo evasivo en lo personal, siempre con documentos bajo el brazo.',
                'alibi' => 'Dice haber estado en la reunión de oficinas de Año Nuevo.',
                'facts' => [
                    'Supervisa el trabajo de los agentes, incluida la cuenta de Rumbo Norte.',
                    'Revisa una muestra mensual de los permisos que firman los agentes.',
                ],
            ],
        ],
        'veronica-salas' => [
            'name' => 'Verónica Salas',
            'role' => 'sospechosa',
            'file' => 'suspects/veronica-salas.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V04']],
                ['questions' => 3, 'after' => ['CRON'], 'confrontation' => true],
            ]],
            'photo' => 'veronica-salas.png',
            'connection' => 'Administradora de la zona franca, donde estuvo almacenado el Lote 47',
            'motive' => 'Ninguno respecto al caso; postula en privado para ser inspectora de la Autoridad Aduanera Portuaria',
            'alibi' => 'Dice haber estado haciendo el conteo de cierre en la zona franca; nadie lo confirma',
            'public' => [
                'age' => '39 años',
                'profile' => 'Administradora de la zona franca. Profesional, algo tensa cuando se habla de su futuro.',
                'alibi' => 'Dice haber estado en la zona franca con el conteo de inventario de cierre de año.',
                'facts' => [
                    'El Lote 47 estuvo almacenado en su zona durante meses.',
                    'Lleva el inventario de la zona franca.',
                ],
            ],
        ],
        'gustavo-henao' => [
            'name' => 'Gustavo Henao',
            'role' => 'sospechoso',
            'file' => 'suspects/gustavo-henao.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V08']],
                ['questions' => 1, 'after' => ['V10']],
                ['questions' => 3, 'after' => ['CRON'], 'confrontation' => true],
            ]],
            'photo' => 'gustavo-henao.png',
            'connection' => 'Jefe de seguridad portuaria; administra las cámaras y los accesos',
            'motive' => 'Ninguno respecto al caso; se ha quedado dormido en turnos nocturnos y lo ha ocultado',
            'alibi' => 'En la reunión de oficinas de Año Nuevo, confirmada por Claudia',
            'public' => [
                'age' => '47 años',
                'profile' => 'Jefe de seguridad del puerto. Seguro de su trabajo, con uniforme, radio y llavero de accesos.',
                'alibi' => 'Dice haber estado en la reunión de oficinas de Año Nuevo.',
                'facts' => [
                    'Administra el sistema de cámaras y de accesos del puerto.',
                    'Tiene el control del grabador de las cámaras.',
                ],
            ],
        ],
        'daniel-osorio' => [
            'name' => 'Daniel Osorio',
            'role' => 'sospechoso',
            'file' => 'suspects/daniel-osorio.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 1, 'after' => ['V09']],
                ['questions' => 3, 'after' => ['CRON'], 'confrontation' => true],
            ]],
            'photo' => 'daniel-osorio.png',
            'connection' => 'Ejecutivo de cuentas comerciales; transmitió a Paulina la presión de Rumbo Norte',
            'motive' => 'Ninguno respecto al caso; busca en secreto una posición en otro puerto',
            'alibi' => 'En la reunión de oficinas de Año Nuevo, confirmada por Claudia',
            'public' => [
                'age' => '35 años',
                'profile' => 'Ejecutivo de cuentas comerciales. Cordial y comercial, siempre con el móvil en la mano.',
                'alibi' => 'Dice haber estado en la reunión de oficinas de Año Nuevo.',
                'facts' => [
                    'Es el enlace entre el puerto y los clientes grandes, entre ellos Rumbo Norte.',
                    'Habla a diario con los agentes sobre las exigencias de los clientes.',
                ],
            ],
        ],
        'wilson-paredes' => [
            'name' => 'Wilson Paredes',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/wilson-paredes.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DW']],
            ]],
            'photo' => 'wilson-paredes.png',
            'connection' => 'Operario de grúa del patio de contenedores; encontró a Aurelio',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '52 años',
                'profile' => 'Operario de grúa del patio de contenedores. Sencillo y serio, con casco de grúa y overol de trabajo.',
                'facts' => [
                    'Trabaja desde la cabina de una grúa con vista al patio.',
                    'Fue quien encontró a Aurelio.',
                ],
            ],
            'accusable' => false,
        ],
        'claudia-reyes' => [
            'name' => 'Claudia Reyes',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/claudia-reyes.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
            ]],
            'photo' => 'claudia-reyes.png',
            'connection' => 'Asistente de gerencia; llevó el registro de la reunión de oficinas de Año Nuevo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '33 años',
                'profile' => 'Asistente de gerencia. Ordenada y precisa, siempre con su tablet y el cronograma de la reunión.',
                'facts' => [
                    'Lleva la agenda y los registros de la gerencia.',
                    'Registró quién asistió a la reunión de oficinas de Año Nuevo.',
                ],
            ],
            'accusable' => false,
        ],
        'mariana-quiroga' => [
            'name' => 'Mariana Quiroga',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/mariana-quiroga.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'mariana-quiroga.png',
            'connection' => 'Agente de aduanas de otra cuenta; tramitó bien una liberación bajo el mismo plazo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '34 años',
                'profile' => 'Agente de aduanas de otro cliente. Práctica y amable, explica los trámites con ejemplos.',
                'facts' => [
                    'Tramitó una liberación bajo el mismo plazo que el Lote 47, sin problemas.',
                    'Conoce el procedimiento normal de fin de año.',
                ],
            ],
            'accusable' => false,
        ],
        'yolanda-prieto' => [
            'name' => 'Yolanda Prieto',
            'role' => 'fuente delimitada (inspectora externa de cumplimiento)',
            'file' => 'suspects/yolanda-prieto.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                // Her persona applies the rules to Lote 47 only once the permit
                // (V03) and the register (V04) are on the table.
                ['questions' => 1, 'after' => ['V03', 'V04']],
            ]],
            'photo' => 'yolanda-prieto.png',
            'connection' => 'Inspectora externa que visita el puerto de forma rutinaria; explica el reglamento',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '49 años',
                'profile' => 'Inspectora externa de cumplimiento. Formal y didáctica, con la carpeta del reglamento de regímenes.',
                'facts' => [
                    'Explica el contenido técnico de las tres reglas fiscales.',
                    'No opina sobre personas ni sobre lo ocurrido.',
                ],
            ],
            'accusable' => false,
        ],
        'camilo-restrepo' => [
            'name' => 'Camilo Restrepo',
            'role' => 'fuente delimitada (agente logístico)',
            'file' => 'suspects/camilo-restrepo.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V04']],
            ]],
            'photo' => 'camilo-restrepo.png',
            'connection' => 'Agente logístico; confirma movimientos objetivos de contenedores entre zonas',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '43 años',
                'profile' => 'Agente logístico. Técnico y ordenado, con la tablet de movimientos y chaleco de logística.',
                'facts' => [
                    'Sigue qué lote se mueve, cuándo y entre qué zonas.',
                    'No puede confirmar quién ejecutó un ajuste de registro.',
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
        'culprit_slug' => 'leonel-bravo',

        'headline' => 'Leonel, el supervisor del patio, causó la muerte de Aurelio en un forcejeo no premeditado por miedo a su propio registro; Paulina, presionada por su cliente, fechó la liberación del Lote 47 el 31 de diciembre, dos días antes de que el lote se moviera.',

        'motive' => 'Miedo a ser la única persona con una prueba técnica directa e innegable en su contra: un registro de movimiento ajustado con su propio usuario, a diferencia de quienes gestionaron el papeleo, cuya responsabilidad podía diluirse como una decisión administrativa.',

        'method' => 'Aurelio lo confrontó en el corredor del patio de contenedores y le dijo que lo reportaría esa misma mañana. En un forcejeo breve, Aurelio tropezó con una cadena de amarre y se golpeó la cabeza contra el borde de un contenedor. Leonel no pidió ayuda y se alejó.',

        'key_evidence' => [
            'El testimonio de Wilson: vio a Leonel conversando con Aurelio y alejándose solo y alterado, y encontró el cuerpo junto a una cadena de amarre.',
            'El fotograma del corredor del patio: dos figuras de uniforme conversando a las 09:47:20, sin rostros reconocibles.',
            'El registro de movimiento: el usuario lbravo.patio modificó la salida declarada del Lote 47 el 2 de enero a las 18:41.',
            'La lista de autorizantes: solo dos cargos podían ajustar un registro, y el de Leonel es uno.',
            'La nota de voz de Aurelio: iría al patio a hablar con quien hubiera ajustado el registro físico antes de reportarlo.',
            'El permiso (31 de diciembre) frente al movimiento físico (2 de enero) y el contrato con Rumbo Norte: la irregularidad fiscal fue de Paulina, un hecho distinto.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including the witnesses and the delimited sources.
         */
        'exonerations' => [
            'paulina-rengifo' => 'Sí fechó el permiso del Lote 47 el 31 de diciembre sabiendo que el lote se movería después, presionada por Rumbo Norte, y le pidió a Leonel que ajustara el registro: esa es la irregularidad fiscal. Pero estuvo en la reunión de oficinas desde las 09:30, confirmada por Claudia, y a ella le convenía que Aurelio siguiera investigando en silencio: su muerte provocó la auditoría a fondo que quería evitar.',

            'rogelio-campuzano' => 'Sí prefería no revisar los trámites de sus agentes mientras los números generales se vieran bien, una negligencia real pero no un acto. Estuvo en la reunión de oficinas desde las 09:30, confirmada por Claudia, y su forma de responder a un problema es institucional, no violenta.',

            'ismael-duarte' => 'Sí aceptó obsequios de otro cliente a cambio de prioridad, una falta real pero sin relación con el Lote 47 ni con Aurelio. Estuvo en la reunión de oficinas, confirmada por Claudia.',

            'veronica-salas' => 'Sí ocultaba su postulación a inspectora y el lote estuvo en su zona, pero su cargo es administrativo sobre el inventario y solo consulta el registro de movimiento, que no puede ajustar. Su secreto no tiene relación con el Lote 47 y Wilson vio a otra persona con Aurelio.',

            'gustavo-henao' => 'Sí se ha quedado dormido en turnos nocturnos y lo ocultó, un problema real pero ajeno a la muerte. Estuvo en la reunión de oficinas, confirmada por Claudia, y el fotograma del corredor no fue alterado: solo muestra dos figuras sin rostro reconocible.',

            'daniel-osorio' => 'Sí transmitió a Paulina la presión de Rumbo Norte y buscaba trabajo en otro puerto, dos cosas sin relación con la muerte. Estuvo en la reunión de oficinas, confirmada por Claudia.',

            'wilson-paredes' => 'Es un testigo, no un sospechoso: contó lo que vio desde la grúa y dejó claro que no vio cómo cayó Aurelio. No tenía motivo ni relación con el registro de movimiento.',

            'claudia-reyes' => 'Es una testigo, no una sospechosa: llevó el registro de la reunión de oficinas y confirmó quién estuvo en ella. No tenía motivo ni acceso al registro de movimiento.',

            'mariana-quiroga' => 'Es una testigo, no una sospechosa: tramitó bien otra liberación bajo el mismo plazo y sirvió de contraste. No conocía el Lote 47 ni tenía relación con lo ocurrido.',

            'yolanda-prieto' => 'Es una fuente neutral, no una sospechosa: solo explicó el contenido técnico de las tres reglas fiscales y no opinó sobre nadie.',

            'camilo-restrepo' => 'Es una fuente técnica, no un sospechoso: confirmó movimientos de contenedores, y su cargo solo consulta el registro, sin permiso para ajustarlo ni forma de saber quién estaba en el patio.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Fenrir',

        // Used by the confession audio (design A2). Written to be played as-is:
        // the model only reads it aloud and lightly weaves in 1-2 real questions
        // from the table, it never composes it.
        'confession_script' => <<<'TXT'
            No fui a buscarlo para hacerle daño. Me dijo que iba a reportarlo esa
            misma mañana, y pensé en lo que eso significaba para mí: el registro quedó
            con mi usuario, no el de nadie más. Los demás podían decir que fue una
            decisión administrativa. Yo no tenía esa salida.

            Intenté explicarle, hubo un forcejeo, muy breve, y tropezó con una cadena
            en el suelo. Se golpeó al caer. No supe qué hacer. Me alejé. No fue lo que
            planeé, pero fue lo que hice, y tengo que responder por eso.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió en el patio de contenedores?',
            'hint' => 'Cuenta cómo murió Aurelio y qué había pasado justo antes entre él y la persona que señalas.',
            'points' => 10,
            'guidance' => 'Lo esencial: Aurelio confrontó a Leonel por el registro ajustado, hubo un forcejeo breve y no premeditado, Aurelio tropezó con una cadena de amarre y se golpeó la cabeza contra un contenedor, y Leonel se fue sin pedir ayuda. Cero si dice que el cambio de régimen fiscal por sí solo prueba quién lo mató, sin apoyarse en el testimonio, el fotograma o el registro con usuario.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué era lo que más lo comprometía a él, y no a otros, y qué iba a pasar esa misma mañana.',
            'points' => 10,
            'guidance' => 'Lo esencial: miedo a ser la única persona con una prueba técnica directa en su contra (el registro con su propio usuario), frente a quienes podían presentar lo suyo como una decisión comercial. Cero si da como motivo el dinero de la maniobra fiscal o la presión de Rumbo Norte, que no eran de Leonel.',
        ],
        'questions' => [
            [
                'key' => 'responsable_fiscal',
                'label' => '¿Quién es responsable de la irregularidad fiscal del Lote 47 y bajo qué presión actuó?',
                'hint' => 'Es un hecho distinto de la muerte. Nombra a la persona y a quién respondía.',
                'answer' => 'Paulina Rengifo, agente de aduanas, presionada por la Importadora Rumbo Norte, que amenazaba con cambiarse de agencia si el trámite no salía a más tardar el 31 de diciembre.',
                'points' => 25,
                'guidance' => 'Cero si atribuye también a Paulina la muerte de Aurelio o si nombra a otra persona. Puntos completos con Paulina y la presión de Rumbo Norte (el contrato o lo que transmitió Daniel).',
            ],
            [
                'key' => 'metodo_fiscal',
                'label' => '¿Cómo se hizo parecer que el Lote 47 cumplía el plazo?',
                'hint' => 'Qué fecha dice el papel, qué ocurrió de verdad y qué se tocó en el registro del puerto.',
                'answer' => 'El permiso de liberación se fechó el 31 de diciembre, dentro del plazo del régimen de tránsito preferencial, aunque el lote solo se movió el 2 de enero, cuando ya debía caer bajo el régimen ordinario. Para que el registro acompañara al papel se ajustó después la fecha de salida declarada.',
                'points' => 25,
                'guidance' => 'Lo esencial: permiso fechado el 31 de diciembre con movimiento físico el 2 de enero, y por tanto régimen ordinario (reglas 2 y 3). Mencionar el ajuste del registro suma, pero no es imprescindible. Cero si dice que la mercancía estuvo en un lugar ilegal o que el movimiento ocurrió antes del 31 de diciembre.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+14/T+28/T+42/T+56/T+70/T+85/T+110
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as the
    | other cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El puerto de los calendarios — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01', 'V02'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Apertura — interrogatorios y cronología del Lote 47',
            'source_file' => 'apertura.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Fragmento privado — cronología del Lote 47 (1)',
            'source_file' => 'pista-privada-cronologia-1.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['CR1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Fragmento privado — cronología del Lote 47 (2)',
            'source_file' => 'pista-privada-cronologia-2.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['CR2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Fragmento privado — cronología del Lote 47 (3)',
            'source_file' => 'pista-privada-cronologia-3.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['CR3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Fragmento privado — cronología del Lote 47 (4)',
            'source_file' => 'pista-privada-cronologia-4.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['CR4'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Tarjeta de reglas — reglas 1 y 2',
            'source_file' => 'pista-privada-tarjeta-reglas-1.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: the pocket cards are redundant
            // copies of V02 (already public since T+1).
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Tarjeta de reglas — reglas 2 y 3',
            'source_file' => 'pista-privada-tarjeta-reglas-2.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 14,
            'title' => 'Tarjeta de reglas — reglas 1 y 3',
            'source_file' => 'pista-privada-tarjeta-reglas-3.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 28,
            'title' => 'Expansión — el permiso y la lista de autorizantes',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V03', 'V07'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 42,
            'title' => 'Desarrollo — el registro de movimiento y la bodega',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V04', 'V05'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 56,
            'title' => 'Complicación — la cronología completa y un fotograma',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // CRON = the aggregated chronology (the T+56 fail-safe, public copy
            // of the private fragments); it also opens the confrontations.
            'evidence_codes' => ['V08', 'CRON'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 70,
            'title' => 'Giro — lo que Aurelio dejó escrito',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V06'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 70,
            'title' => 'Audio — nota de voz de Aurelio, hallada en su teléfono',

            'audio_file' => 'a1-nota-de-voz-aurelio.mp3',
            'audio_scene' => 'Nota de voz grabada con el teléfono, al aire libre, junto al patio de contenedores a primera hora; ruido de fondo de un patio portuario (motores lejanos, metal, alguna grúa), sin música. Una pausa breve antes de la última frase.',
            'audio_context' => 'Aurelio graba una nota de voz para un colega de confianza: tiene claro lo del Lote 47 y piensa hablar primero con quien pudo ajustar el registro físico, antes de reportarlo formalmente. La nota quedó en el borrador de su teléfono, sin enviar.',
            'audio_speaker' => 'Speaker 1 - Aurelio Nieto (hombre de 54 años)',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                Ya tengo claro lo del Lote 47. El permiso dice 31 de diciembre, pero
                el registro de movimiento es de otro día. Esto no lo decidió solo quien
                hizo el papeleo: alguien tuvo que ajustar el registro físico también.
                Voy a ir al patio esta mañana a hablar directamente con quien pudo
                haber hecho ese ajuste, antes de reportarlo formalmente.

                Si hay una explicación razonable, prefiero escucharla primero.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 85,
            'title' => 'Cierre — contrato, acceso y declaración de Wilson',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // DW = Wilson's written (expanded) declaration, the body of cierre.md.
            // It has no image, so it gets its own code (same device as DF in
            // punto-de-control).
            'evidence_codes' => ['V09', 'V10', 'DW'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 110,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final. Recuerden que en este caso hay dos hechos distintos: además de quién es el responsable de la muerte de Aurelio, qué ocurrió y por qué, el formulario pregunta quién es el responsable de la irregularidad fiscal del Lote 47 y cómo se logró que el papel pareciera cumplir el plazo.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-puerto-de-los-calendarios/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-mapa-zonas.png', 'caption' => 'Mapa esquemático de las cuatro zonas de Puerto Altamar'],
            ['file' => 'v02-tabla-regimenes.png', 'caption' => 'Las tres reglas de régimen fiscal'],
        ],
        'expansion.md' => [
            ['file' => 'v03-permiso-lote.png', 'caption' => 'Permiso de liberación del Lote 47'],
            ['file' => 'v07-lista-autorizantes.png', 'caption' => 'Cargos autorizados para ajustar registros de movimiento'],
        ],
        'desarrollo.md' => [
            ['file' => 'v04-movimiento-carga.png', 'caption' => 'Extracto del registro de movimiento de carga'],
            ['file' => 'v05-foto-almacen.png', 'caption' => 'La bodega general, tras el traslado del lote'],
        ],
        'complicacion.md' => [
            ['file' => 'v08-cctv-corredor.png', 'caption' => 'Fotograma de la cámara fija del corredor del patio de contenedores'],
        ],
        'giro.md' => [
            ['file' => 'v06-acta-inspeccion.png', 'caption' => 'Acta de inspección de la revisión de cierre de año'],
        ],
        'cierre.md' => [
            ['file' => 'v09-contrato-comercial.png', 'caption' => 'Contrato de servicios entre la agencia y la Importadora Rumbo Norte'],
            ['file' => 'v10-acceso-exterior.png', 'caption' => 'El acceso exterior del puerto esa mañana'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v03-permiso-lote.png', 'caption' => 'El permiso dice 31 de diciembre'],
            ['file' => 'v04-movimiento-carga.png', 'caption' => 'El registro: movimiento físico el 2 de enero y un ajuste con usuario'],
            ['file' => 'v08-cctv-corredor.png', 'caption' => 'Dos figuras en el corredor del patio, a las 09:47'],
            ['file' => 'v09-contrato-comercial.png', 'caption' => 'La presión económica detrás de la fecha del papel'],
        ],
    ],
];
