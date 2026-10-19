<?php

/**
 * Case manifest: "El Año que no Cerró"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a documentary FRAUD case with no violence and no
 * physical victim. Its central trap is the instinct to look for a wrong sum.
 * The closed annual balance is CORRECT (Debe = Haber, difference 0). The fraud
 * is the TYPE OF ENTRY chosen for one credit note: Distribuidora Marconi's
 * US$ 5,000 was processed as "compensación directa" instead of
 * "reconocimiento de pasivo", dodging the director's approval that any note
 * above US$ 1,250 needs. Ignacio Beltrán (commercial manager) negotiated the
 * note and pressed Renata Solórzano to "resolve it fast, it's already talked
 * about"; Renata, trusting him and not wanting to look difficult in her first
 * full close, chose the entry type and did not confirm with Octavio. Ignacio
 * is the beneficiary and cannot touch the system; Renata is the culprit
 * (engine culprit_slug). Octavio, Marcela and Felipe are real-secret decoys.
 * Humberto Casas (external auditor) is a neutral source.
 *
 * Accounting sheet (fictional, ONLY two entry types and three accounts; every
 * figure of V01-V06 derives from here — do not change one without the others):
 *   Accounts:  410 Ventas a clientes externos
 *              520 Notas de crédito pendientes
 *              610 Pasivos reconocidos del nuevo ejercicio
 *   Entry types: "Compensación directa" (ordinary adjustment against 410, no
 *              special approval, for small routine amounts) and
 *              "Reconocimiento de pasivo" (formal obligation in 610; above the
 *              threshold of US$ 1,250 it needs the director's approval).
 *   Notes of the closed exercise (V03), amounts in USD:
 *     Ferretería Los Álamos      US$ 375   Compensación directa       410
 *     Agrícola El Sur            US$ 750   Compensación directa       410
 *     Inversiones Cumbre         US$ 1,875   Reconocimiento de pasivo   610
 *     Comercial Andes            US$ 500   Compensación directa       410
 *     Distribuidora Marconi     US$ 5,000   Compensación directa       410
 *     Total US$ 8,500 = US$ 6,625 compensated + US$ 1,875 recognised.
 *   V01 (closing summary):  410  US$ 319,125 → -US$ 6,625 → US$ 312,500
 *                           520     US$ 8,500 → -US$ 8,500 → US$ 0
 *                           610           US$ 0 → +US$ 1,875  → US$ 1,875
 *     Total Debe US$ 8,500 = total Haber US$ 8,500 (difference US$ 0): the
 *     balance is right. Had Marconi been recognised as liability, 610 would
 *     hold US$ 6,875, not US$ 1,875.
 *
 * Reference timeline (single source of truth for every file of this case; no
 * calendar dates on purpose, only days of the "cierre week"):
 *   - Late November: Marconi receives defective merchandise and demands a
 *     compensation. Early December: Ignacio privately negotiates the US$ 5,000 note.
 *   - Monday of the closing week, mid-afternoon (~16:30): Ignacio stops Renata
 *     in the corridor between sales and finance (Jorge overhears). 16:48-17:02
 *     (V06): the internal chat. Tuesday morning: Ignacio's voice message (A1).
 *   - Thursday, LAST DAY of the exercise (V04): 09:15 Ferretería Los Álamos
 *     and 09:40 Agrícola El Sur processed (compensación directa); 10:20
 *     Inversiones Cumbre sent to approval (reconocimiento de pasivo); 11:05
 *     Octavio approves it; 15:50 Comercial Andes (compensación directa);
 *     16:42 Distribuidora Marconi processed by R. Solórzano as compensación
 *     directa, NEVER sent to the approval queue.
 *   - First days of January: the closing board meets; the balance squares. The
 *     external auditor, in a routine review of the closing process, notes a
 *     directly-compensated movement above the usual threshold (V05).
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - NO PHYSICAL VICTIM. `victim` is the cooperative itself: name «Cooperativa
 *    Provisión Central», label «Afectada», photo = an image of its headquarters.
 *    `victim.incident` is «el cierre anual de {name}» so the shared AI prompts
 *    say "la investigación sobre el cierre anual de Cooperativa Provisión
 *    Central" (neutral, leaks nothing) instead of "la muerte de…". It is not
 *    interrogable.
 *  - The engine scores only the culprit (renata-solorzano). The design's five
 *    components (30/25/20/15/10) became culprit_points 30 + weapon (cómo) 25 +
 *    motive (por qué) 20 + two `accusation.questions`: quién se benefició
 *    (Ignacio) 15 and qué prueba la intención 10. The design's penalty
 *    ("treat the balance as having a sum error / a copy-print error") became
 *    "cero" guidance in the weapon and evidence questions. The unlock text at
 *    T+75 announces the extra questions.
 *  - Questions: the design's 3 per person (+2/+1 confrontations) are folded into
 *    the engine rule: 5 (Octavio, Felipe, Ignacio, Renata), 4 (Marcela),
 *    witnesses 3+2 gated, Humberto 4+1 gated; +3 confrontation for each of the
 *    five accusable suspects (the design gives Octavio/Marcela/Felipe one; the
 *    engine only supports 0 or 3). Confrontations open with the delivered
 *    evidence: Octavio after V04 (T+40, as the design); Renata after V04+V06+A1
 *    and Ignacio after V04+DJ (both T+55); Marcela and Felipe after V05 (T+55,
 *    their design confrontation is "una pregunta directa de seguimiento" with
 *    no evidence, so V05 stands in for "tras el Giro").
 *  - DJ is a new evidence code: Jorge's expanded written declaration (body of
 *    giro.md, T+55). It gates Jorge's key fact and Ignacio's confrontation 1
 *    (same device as DF in punto-de-control).
 *  - Interrogation opens at T+12 (the expansion envelope carries
 *    `cta_interrogation`, like the other cases), not at T+0 as in the design.
 *    Humberto, with his explanation always available, is open from then.
 *  - V02 is redundant by design: two PRIVATE fragments at T+12
 *    (`random_player`: V02A manual interno, V02B correo de capacitación) that
 *    each state the WHOLE rule in different words, plus a complete PUBLIC copy
 *    at T+22 (V02), plus Humberto always explaining it. No fragment is
 *    indispensable. Each fragment carries its own code. The design's
 *    fail-safe ("Humberto la repite al ser preguntado de nuevo") is in his md.
 *  - V06 gained one line the design does not have: Renata asks Ignacio whether
 *    the note "ya pasó por Octavio" and he answers "eso ya lo hablé yo". It
 *    keeps the insinuation implicit (never "está aprobada") and supports
 *    "sabía que superaba el umbral" and the confrontation text.
 *  - V04 shows a contrast the design does not: the same user sent
 *    Inversiones Cumbre (US$ 1,875) to approval that morning. It proves she
 *    knew the rule, and it is the anchor for Octavio/Camila saying he approved
 *    only that one. It also carries a footer with the profile allowed to
 *    process notes of large external clients (R. Solórzano only), which
 *    anchors Felipe's and Ignacio's lack of access.
 *  - V05 names nobody (not even the client): the amount links it to V03.
 *  - A1 is a timeline audio event (audio_email, minute 55, mp3). Ignacio's
 *    voice message is Tuesday; the design script is verbatim.
 *  - The design's "no confession": ending = epílogos personalizados, so there
 *    is NO `solution.confession_script`. Each exoneration closes with a
 *    «Desenlace» sentence taken from the design's epilogue section so the
 *    personalised epilogue has authored material; Renata's and the rest of the
 *    outcomes are also in solucion.md ("Después").
 *  - Fail-safes: the three of the design live in cierre.md (T+65): what the
 *    approval log says and does not say, and the cross explanations of
 *    Octavio, Marcela and Felipe. Humberto's re-explanation of the rule is in
 *    his md.
 *  - Public ficha of Ignacio says "cuentas de clientes externos importantes",
 *    not "Marconi": the client's name appears only from V03 on.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'El Año que no Cerró',
    'version' => '1.0',
    'code' => 'Expediente de cierre — Cooperativa Provisión Central',
    'authority' => 'Comisión de Revisión del Cierre de Cooperativa Provisión Central',

    'victim' => [
        'name' => 'Cooperativa Provisión Central',
        'label' => 'Afectada',
        'photo' => 'cooperativa-provision-central.png',
        'incident' => 'el cierre anual de {name}',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design asks for 3 preguntas por persona + 2/1 de confrontación.
        // The engine rule is 3-5 normal + 3 confrontation, opened by stages.
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
        'tagline' => 'El balance cerró perfecto. Eso era, precisamente, el problema.',

        'description' => <<<'TXT'
            Cooperativa Provisión Central cierra su año sin una sola suma mal
            hecha. Pero el revisor de cuentas externo nota que algo no encaja:
            no en lo que se cerró, sino en lo que quedó arrastrado al nuevo
            ejercicio. Un fraude sin violencia, sin sangre, en una oficina.

            Tu equipo (3 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco sospechosos, dos testigos y
            al revisor de cuentas durante 75 minutos, cruzando un reglamento
            interno de dos líneas, listados contables, registros de aprobación,
            correspondencia y un mensaje de voz.

            No tienes que ser contable: solo entender una regla sencilla y
            compararla con lo que de verdad se hizo. Aquí nadie busca un error
            de suma.
            TXT,

        // Archivo esperado en public/immersion/el-ano-que-no-cerro/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-ano-que-no-cerro).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'El balance cerró perfecto. Eso era, precisamente, el problema.',
            'bullets' => [
                '3 a 7 investigadores cruzando un reglamento, listados contables y correspondencia',
                'Cinco sospechosos, dos testigos y un revisor de cuentas a quienes interrogar',
                '75 minutos con el expediente llegando en tiempo real, sin saber de contabilidad',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 75,
        'min_players' => 3,
        'max_players' => 7,

        'price_amount' => 59900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/el-ano-que-no-cerro/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 32,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Camila, Jorge) and the neutral source (Humberto) are
    | modeled exactly like a suspect entry, with 'accusable' => false. The
    | victim (the cooperative) is not interrogable and has no entry here.
    |
    */

    'suspects' => [
        'renata-solorzano' => [
            'name' => 'Renata Solórzano',
            'role' => 'sospechosa',
            'file' => 'suspects/renata-solorzano.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V04', 'V06', 'A1'], 'confrontation' => true],
            ]],
            'photo' => 'renata-solorzano.png',
            'connection' => 'Analista financiera: procesa las notas de crédito en el sistema contable',
            'motive' => 'Confianza mal depositada en Ignacio y temor a parecer conflictiva o inexperta en su primer cierre completo; no obtuvo ningún beneficio propio',
            'alibi' => 'Ninguna: procesó la nota con su propio usuario, el último día del ejercicio',
            'public' => [
                'age' => '36 años',
                'profile' => 'Analista financiera de la cooperativa. Cordial y aplicada; este es su primer cierre completo.',
                'alibi' => 'Dice haber seguido su rutina habitual de proceso de notas de crédito durante el cierre.',
                'facts' => [
                    'Es quien procesa las notas de crédito de clientes externos en el sistema contable.',
                    'Trabajó en las notas pendientes durante los últimos días del ejercicio.',
                ],
            ],
        ],
        'ignacio-beltran' => [
            'name' => 'Ignacio Beltrán',
            'role' => 'sospechoso',
            'file' => 'suspects/ignacio-beltran.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V04', 'DJ'], 'confrontation' => true],
            ]],
            'photo' => 'ignacio-beltran.png',
            'connection' => 'Gerente comercial responsable de cuentas de clientes externos importantes',
            'motive' => 'Proteger su evaluación comercial de fin de año evitando que un problema grande con una cuenta importante saliera a la luz antes del cierre',
            'alibi' => 'No tiene acceso al sistema de notas de crédito; su implicación es la presión ejercida sobre Renata',
            'public' => [
                'age' => '44 años',
                'profile' => 'Gerente comercial. Seguro y simpático, acostumbrado a cerrar acuerdos con clientes importantes.',
                'alibi' => 'Dice que su papel en el cierre fue atender a los clientes externos de su cartera.',
                'facts' => [
                    'Es responsable de la relación con varios clientes externos importantes.',
                    'Trabaja a diario con el equipo de ventas y es quien negocia con esos clientes.',
                ],
            ],
        ],
        'octavio-reyes' => [
            'name' => 'Octavio Reyes',
            'role' => 'sospechoso',
            'file' => 'suspects/octavio-reyes.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V04'], 'confrontation' => true],
            ]],
            'photo' => 'octavio-reyes.png',
            'connection' => 'Director financiero: quien debe aprobar el reconocimiento de pasivo sobre el umbral',
            'motive' => 'Ninguno respecto al caso; sopesa en privado salir de la cooperativa hacia otra organización, algo que no ha declarado',
            'alibi' => 'El registro de aprobación muestra que la nota nunca llegó a su cola de revisión',
            'public' => [
                'age' => '55 años',
                'profile' => 'Director financiero de la cooperativa. Profesional, algo distante, de pocas palabras.',
                'alibi' => 'Dice que su tarea durante el cierre fue revisar y aprobar lo que llegó a su cola.',
                'facts' => [
                    'Es la máxima autoridad de aprobación del departamento financiero.',
                    'Debe aprobar de forma explícita ciertos asientos por encima de un monto.',
                ],
            ],
        ],
        'marcela-ibanez' => [
            'name' => 'Marcela Ibáñez',
            'role' => 'sospechosa',
            'file' => 'suspects/marcela-ibanez.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['V05'], 'confrontation' => true],
            ]],
            'photo' => 'marcela-ibanez.png',
            'connection' => 'Presidenta de la junta que aprueba el cierre anual',
            'motive' => 'Ninguno respecto a la nota; presionó en general para cerrar rápido y sin sorpresas de cara a su reelección en la junta',
            'alibi' => 'No participa en el procesamiento de notas individuales ni tiene acceso al sistema donde se registran',
            'public' => [
                'age' => '50 años',
                'profile' => 'Presidenta de la junta de cierre. Institucional y segura de sí misma.',
                'alibi' => 'Dice que su papel es presidir la sesión de cierre y no interviene en asientos concretos.',
                'facts' => [
                    'Preside la junta que aprueba el cierre anual de la cooperativa.',
                    'Pidió públicamente un cierre ágil y sin sorpresas este año.',
                ],
            ],
        ],
        'felipe-londono' => [
            'name' => 'Felipe Londoño',
            'role' => 'sospechoso',
            'file' => 'suspects/felipe-londono.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V05'], 'confrontation' => true],
            ]],
            'photo' => 'felipe-londono.png',
            'connection' => 'Asistente contable, colega de Renata en el área financiera',
            'motive' => 'Ninguno respecto al caso; hace trabajos de contabilidad externos no autorizados para un negocio familiar',
            'alibi' => 'No tiene permiso de sistema para procesar notas de crédito de clientes externos grandes',
            'public' => [
                'age' => '29 años',
                'profile' => 'Asistente contable. Relajado y cercano; apoya al equipo en tareas menores.',
                'alibi' => 'Dice que durante el cierre apoyó con listados y archivo de soportes.',
                'facts' => [
                    'Trabaja en el mismo departamento que Renata y tiene acceso general al área.',
                    'Su perfil en el sistema es de apoyo, para tareas menores.',
                ],
            ],
        ],
        'camila-duarte' => [
            'name' => 'Camila Duarte',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/camila-duarte.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V04']],
            ]],
            'photo' => 'camila-duarte.png',
            'connection' => 'Asistente de dirección financiera: gestiona la cola de aprobación del director',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '31 años',
                'profile' => 'Asistente de la dirección financiera. Ordenada, precisa y discreta.',
                'facts' => [
                    'Gestiona la cola de aprobación del director financiero.',
                    'Lleva el control de lo que se aprueba cada semana.',
                ],
            ],
            'accusable' => false,
        ],
        'jorge-patino' => [
            'name' => 'Jorge Patiño',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/jorge-patino.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DJ']],
            ]],
            'photo' => 'jorge-patino.png',
            'connection' => 'Coordinador de ventas, con el puesto junto al pasillo que va de ventas a finanzas',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '46 años',
                'profile' => 'Coordinador de ventas. Veterano, campechano y algo cansado de los cierres.',
                'facts' => [
                    'Su puesto queda junto al pasillo que comunica ventas con finanzas.',
                    'Vivió de cerca la presión comercial de las semanas de cierre.',
                ],
            ],
            'accusable' => false,
        ],
        'humberto-casas' => [
            'name' => 'Humberto Casas',
            'role' => 'fuente neutral (revisor de cuentas externo)',
            'file' => 'suspects/humberto-casas.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 1, 'after' => ['V05']],
            ]],
            'photo' => 'humberto-casas.png',
            'connection' => 'Revisor de cuentas externo que audita el proceso de cierre',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '60 años',
                'profile' => 'Revisor de cuentas externo. Sereno y didáctico; explica el reglamento sin opinar sobre personas.',
                'facts' => [
                    'Explica el reglamento de notas de crédito y la regla del umbral de aprobación.',
                    'Descubrió el hallazgo en una auditoría rutinaria del proceso de cierre.',
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
    | Ending type: personalised epilogues. No confession audio on purpose.
    |
    */

    'solution' => [
        'culprit_slug' => 'renata-solorzano',

        'headline' => 'El balance cerró bien; lo que no cerró fue una nota de US$ 5,000: Renata la procesó como compensación directa en vez de reconocerla como pasivo, confiando en la insinuación de Ignacio de que ya estaba hablado con dirección.',

        'motive' => 'Confió en la insinuación de Ignacio de que la nota ya estaba aprobada por dirección y no quiso parecer conflictiva ni inexperta en su primer cierre completo en la cooperativa. No obtuvo ningún beneficio propio; el beneficiado fue Ignacio, cuya evaluación comercial de fin de año se mantuvo intacta.',

        'method' => 'Procesó la nota de crédito de US$ 5,000 de Distribuidora Marconi como compensación directa contra la cuenta de ventas, en lugar de reconocerla como pasivo con la aprobación del director financiero, pese a superar ampliamente el umbral de US$ 1,250, y sin verificar la aprobación con Octavio. El balance cerrado quedó correcto, pero la obligación se arrastró al nuevo ejercicio sin revisión.',

        'key_evidence' => [
            'El resumen anual: el balance cerrado cuadra, no hay ningún error de suma; el fallo está en el tipo de asiento.',
            'La regla y el listado de notas: sobre US$ 1,250 se debe reconocer pasivo con aprobación del director, y la nota de Marconi (US$ 5,000) figura como compensación directa.',
            'El registro de aprobación: la nota la procesó el usuario de Renata y nunca llegó a la cola del director, mientras que esa misma mañana ella sí envió a aprobación la de US$ 1,875.',
            'La correspondencia comercial y el mensaje de voz de Ignacio, con lo que oyó Jorge: la urgencia y la insinuación de una aprobación inexistente vinieron de Ignacio.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit, plus the "Desenlace" of the design's epilogue section. One
         * entry per roster entry that is not the culprit, including both
         * witnesses and the neutral source.
         */
        'exonerations' => [
            'ignacio-beltran' => 'Sí negoció la nota con Marconi y presionó a Renata, dando a entender una aprobación que no existía, y con ello protegió su evaluación comercial; es el beneficiado. Pero no tiene acceso técnico al sistema de notas de crédito: no pudo elegir el tipo de asiento, y su responsabilidad es la presión y la insinuación, no la ejecución. Desenlace: enfrenta una revisión de su evaluación comercial del año y debe declarar formalmente cualquier negociación de notas de crédito que supere el umbral.',

            'octavio-reyes' => 'Sí guarda un secreto, pues sopesa en privado irse a otra organización, pero no tiene relación con el caso. El registro de aprobación y el testimonio de Camila confirman que la nota de Marconi nunca llegó a su cola ni la vio. Desenlace: acepta finalmente la posición externa y deja la cooperativa en buenos términos, con los protocolos de aprobación reforzados antes de irse.',

            'marcela-ibanez' => 'Sí presionó para cerrar rápido y sin sorpresas pensando en su reelección, pero su presión fue general y pública, no dirigida a esta nota, y no tiene acceso al sistema de notas de crédito ni conocía esa nota. Desenlace: gana su reelección, pero debe explicar ante la asamblea general el hallazgo de la auditoría, lo que modera su insistencia futura en cerrar rápido y sin sorpresas.',

            'felipe-londono' => 'Sí hace contabilidad externa no autorizada para un negocio familiar, pero no tiene relación con la nota, y su perfil de sistema no tiene el permiso necesario para procesar notas de crédito de clientes externos grandes. Desenlace: formaliza su situación laboral externa o decide dejarla, tras una conversación honesta con recursos humanos.',

            'camila-duarte' => 'Es una testigo, no una sospechosa: solo confirmó que la nota de Marconi nunca llegó a la cola de aprobación del director y que él no tenía conocimiento de ella. No procesó la nota ni tenía motivo alguno.',

            'jorge-patino' => 'Es un testigo, no un sospechoso: solo contó que oyó a Ignacio pedirle a Renata, días antes del cierre, que resolviera rápido lo de Marconi, que ya estaba hablado. No tiene nada que ver con las notas de crédito ni motivo alguno.',

            'humberto-casas' => 'Es una fuente neutral, no un sospechoso: explicó el reglamento y encontró el hallazgo en una auditoría rutinaria del cierre, sin opinar sobre personas. No es parte de la cooperativa ni intervino en la nota. Desenlace: recomienda a la cooperativa auditorías rutinarias más frecuentes sobre movimientos compensados directamente por encima de ciertos montos.',
        ],

        // No `confession_script` / `confession_voice` on purpose: this case
        // ends with personalised epilogues, not with a confession recording.
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Cómo lo hizo?',
            'hint' => 'Qué tipo de asiento se aplicó, a qué nota y qué trámite se evitó.',
            'points' => 25,
            'guidance' => 'Lo esencial: la nota de US$ 5,000 de Distribuidora Marconi se procesó como compensación directa en lugar de reconocimiento de pasivo, evitando la aprobación del director financiero que exige la regla para montos sobre US$ 1,250. Cero si dice que el balance cerrado tiene un error de suma o si lo explica como un simple error de copia o de impresión.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué la llevó a elegir ese tipo de asiento, y si ganó algo con ello.',
            'points' => 20,
            'guidance' => 'Lo esencial: confió en la insinuación de Ignacio de que la nota ya estaba aprobada por dirección y no quiso parecer conflictiva o inexperta en su primer cierre completo. No exigir que mencione un beneficio propio: no lo hubo. Cero si lo atribuye a un soborno del cliente o a un beneficio económico de Renata.',
        ],
        'questions' => [
            [
                'key' => 'quien_se_beneficio',
                'label' => '¿Quién se benefició de que la nota no se reconociera como obligación, y cómo?',
                'hint' => 'Nombra a la persona y lo que protegió.',
                'answer' => 'Ignacio Beltrán, gerente comercial: su evaluación comercial de fin de año se mantuvo intacta porque no apareció ningún problema grande con la cuenta de Distribuidora Marconi antes del cierre.',
                'points' => 15,
                'guidance' => 'Puntos completos con Ignacio y su evaluación comercial o su cuenta importante. Parcial si nombra a Ignacio sin el porqué. Cero si señala a Octavio, Marcela, Felipe o al cliente.',
            ],
            [
                'key' => 'prueba_de_la_intencion',
                'label' => '¿Qué pruebas muestran que no fue un simple error de criterio?',
                'hint' => 'Qué documentos y testimonios cruzados lo demuestran.',
                'answer' => 'El registro de aprobación (el director nunca vio la nota, y esa misma mañana la analista sí envió a aprobación otra nota por encima del umbral) junto con la correspondencia comercial y el mensaje de voz de Ignacio (la presión y la insinuación de una aprobación inexistente), apoyados por lo que oyó Jorge.',
                'points' => 10,
                'guidance' => 'Puntos completos si cita el registro de aprobación junto con la correspondencia o el mensaje de voz de Ignacio. Parcial si cita solo uno de ellos. Cero si la prueba que ofrece es únicamente el hallazgo contable (el listado o el acta) o un supuesto error de suma.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+26/T+40/T+55/T+75 plan into
    | trigger_offset_minutes (T+0 -> 1, same convention as the other cases).
    | The private V02 fragments (T+12) and the public copy (T+22) are the
    | design's "información privada redundante". The T+65 cierre carries the
    | design's fail-safes.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El año que no cerró — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — las personas del cierre',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — una página del manual interno',
            'source_file' => 'pista-privada-manual.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V02A'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — un correo de capacitación',
            'source_file' => 'pista-privada-capacitacion.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V02B'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 22,
            'title' => 'La regla, completa y para toda la mesa',
            'source_file' => 'regla-publica.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V02'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Desarrollo — el listado de notas de crédito',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V03'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 40,
            'title' => 'Complicación — quién procesó, y quién no llegó a ver',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V04'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Giro — lo que se dijo antes del cierre',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DJ = Jorge's written (expanded) declaration, the body of giro.md.
            // It has no image, so it gets its own code (same device as DF in
            // punto-de-control).
            'evidence_codes' => ['V05', 'V06', 'DJ'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 55,
            'title' => 'Audio — mensaje de voz de Ignacio a Renata',

            'audio_file' => 'a1-mensaje-voz-ignacio.mp3',
            'audio_scene' => 'Mensaje de voz de móvil, dejado por un hombre adulto desde un pasillo de oficina, con ambiente de oficina de fondo (teclas, conversación lejana). Sin música ni dramatismo.',
            'audio_context' => 'Ignacio, gerente comercial de 44 años, deja un mensaje breve a una colega pidiéndole que resuelva un trámite rápido antes de una reunión importante. Tono cordial pero apremiante, como un favor entre compañeros de trabajo; no debe sonar como una amenaza. Duración: 10-15 segundos.',
            'audio_speaker' => 'Speaker 1 - Ignacio Beltrán',
            'audio_voice' => 'Orus',
            'audio_script' => <<<'TXT'
                Renata, lo de Marconi: resuélvelo rápido antes de la junta, ¿sí? Ya está hablado por mi lado, no te compliques más de la cuenta. Gracias, en serio.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Cierre — lo que el registro dice y lo que no, y las explicaciones pendientes',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 75,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace dos preguntas más sobre el caso (quién se benefició de que la nota no se reconociera como obligación, y qué pruebas muestran que no fue un simple error de criterio).',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-ano-que-no-cerro/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-resumen-anual.png', 'caption' => 'Resumen anual del ejercicio cerrado'],
        ],
        'pista-privada-manual.md' => [
            ['file' => 'v02a-manual-interno.png', 'caption' => 'Una página del manual de procedimientos internos'],
        ],
        'pista-privada-capacitacion.md' => [
            ['file' => 'v02b-correo-capacitacion.png', 'caption' => 'Un correo de capacitación sobre notas de crédito'],
        ],
        'regla-publica.md' => [
            ['file' => 'v02-regla-compensacion.png', 'caption' => 'Reglamento interno: notas de crédito pendientes con clientes externos'],
        ],
        'desarrollo.md' => [
            ['file' => 'v03-notas-por-compensar.png', 'caption' => 'Listado de notas de crédito del cierre'],
        ],
        'complicacion.md' => [
            ['file' => 'v04-registro-aprobacion.png', 'caption' => 'Registro de aprobación del último día del ejercicio'],
        ],
        'giro.md' => [
            ['file' => 'v05-acta-auditoria.png', 'caption' => 'Acta de la revisión de cierre'],
            ['file' => 'v06-correspondencia-comercial.png', 'caption' => 'Correspondencia comercial interna de los días previos al cierre'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v02-regla-compensacion.png', 'caption' => 'La regla: sobre US$ 1,250, reconocimiento de pasivo con aprobación'],
            ['file' => 'v03-notas-por-compensar.png', 'caption' => 'La nota de Marconi, tratada como compensación directa'],
            ['file' => 'v04-registro-aprobacion.png', 'caption' => 'Procesada por Renata; nunca llegó a la cola del director'],
            ['file' => 'v06-correspondencia-comercial.png', 'caption' => 'La presión de Ignacio: «ya está hablado por mi lado»'],
        ],
    ],
];
