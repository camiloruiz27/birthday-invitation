<?php

/**
 * Case manifest: "La Palabra de Seguridad"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a PROCEDURAL-sabotage case. Nobody broke a rule that
 * night. The actor (Darío) followed the actors' manual; the operator (Iván)
 * followed the operators' manual he held, in good faith; the mechanism worked.
 * What differs is the manual: Marcelo (Jefe de Operaciones) prepared an altered
 * edition (MO-3.1) with a note for the Sala del Péndulo ("complete the cycle
 * before stopping") and handed it only to Iván, outside Osmán's channel, to tie
 * a safety incident to Iván's name before a promotion decision. The AI rules
 * that matter: never say Iván ignored or neglected the safe word; never imply
 * the exception was official before V03/V05/Osmán say so. The protocol and its
 * exception are FICTIONAL and generic; no real technique to defeat a safety
 * system appears anywhere.
 *
 * Reference timetable (single source of truth for every file of this case; all
 * hours that appear in evidence, md files and the manifest derive from it).
 * Incident night = a Saturday (never named in the texts; no calendar dates).
 *   Days before: Osmán distributes the official editions by e-mail + printed
 *     copy to the whole team: operators MO-3.0, actors MA-3.0.
 *   Wednesday 19:20 (days before): Marcelo's message "asegurándome de que la
 *     actualización del protocolo quede bien implementada para el turno de Iván
 *     esta semana" (C1). He prepares MO-3.1 that week.
 *   Shift day: 16:02 Renata enters the oficina administrativa (access card; no
 *     exit until 22:30; videocall with a supplier 16:30–17:40). 16:41 CCTV
 *     (PASILLO ADMIN · CAM 3): Marcelo, supervision uniform, folder under his
 *     arm, near the print/archive door (V08). 17:25 (approx., never given as an
 *     hour in any text) Marcelo hands Iván the stapled MO-3.1 packet in front of
 *     the office door; Noelia sees it from her desk. 17:30 Yesenia leads the
 *     cast call in the zona de actores and stays there. 17:38 Candela enters by
 *     the staff access. 17:50–17:55 briefing de apertura: operators sign the
 *     briefing sheet with the edition they hold (V05): Sara Montoya 17:50 MO-3.0,
 *     Candela 17:51 MO-3.0, Iván 17:52 MO-3.1, Hugo Paternina 17:53 MO-3.0, Lucía
 *     Rondón 17:55 MO-3.0. 19:00 doors open.
 *   Incident: 21:47 a visitor says the safe word in the Sala del Péndulo; Darío
 *     withdraws; Iván's radio call (A1) 21:47; 21:48 the mechanism completes its
 *     cycle and hits Darío's wrist while he leaves by the side exit; 21:49 the
 *     room is suspended and the cast member attended; 21:52 Coordinación de
 *     Actores informed (Yesenia attends Darío); 22:05 Gerencia informed.
 *     Next morning 08:10: the incident report is generated (V07).
 *   Venue: Vestíbulo → Sala de los Espejos → Sala del Péndulo → Galería de las
 *     Sombras → Sala del Eco → Salida; zona de actores (backstage); oficina
 *     administrativa with the print/archive room on the same corridor.
 *     Puestos de control: PC-1 Vestíbulo + Espejos (Candela), PC-2 Péndulo
 *     (Iván, the ONLY post that hears the Péndulo), PC-3 Galería (Sara Montoya),
 *     PC-4 Eco + Salida (Hugo Paternina).
 *   Editions: MA-3.0 actors (official); MO-3.0 operators (official, "sin
 *     excepción", no note for any room); MO-3.1 = Marcelo's alteration (note 4.2
 *     for the Péndulo), the code printed in V03's footer and signed by Iván in V05.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit. The doc's 4-component score (30 / 20 /
 *    20 / 30 and the "accuse Iván without noting his good faith" penalty) is
 *    mapped to culprit_points 30 + weapon (what really happened) 20 + motive 20
 *    + one extra question (how the circulation of the altered manual is proven)
 *    30. The penalty is expressed in the `guidance` of weapon: zero if the
 *    answer presents Iván as having ignored/neglected the safe word or blames a
 *    mechanical failure. The unlock event asks for the extras in the motive box.
 *  - Questions: base stages and 3 for confrontations (stages by evidence). The
 *    doc's "3 por sospechoso + 2 confrontaciones a Marcelo / 1 a los demás" is
 *    folded into the engine rule. Marcelo: 5 + confrontation stages {2 after V05,
 *    1 after DN+V08+C1}; Iván 5 + 3 after SI; the other four 4 + 3.
 *  - Darío is BOTH the `victim` (label «Herido», `victim.incident` «la lesión
 *    de») and an interrogable, non-accusable witness with the same portrait file
 *    (dario-lezcano.png, counted once in the assets).
 *  - V05's role: the doc says it records "entrega de manuales" and that Osmán's
 *    official register "no incluye ninguna entrega distinta a Iván". Those clash
 *    if V05 is Osmán's register. Resolved: V05 is the signed sheet of the
 *    briefing de apertura (what each operator holds at their post), archived by
 *    prevention; Osmán's own register (days before) lists MO-3.0 for everyone,
 *    Iván included, and has no MO-3.1.
 *  - V03 carries the edition code MO-3.1 in its footer so that V05 (T+48) is
 *    what exposes it as an edition prevention never issued. 4.1 deliberately
 *    omits "sin excepción" (the official text keeps it). The T+24 envelope only
 *    says the page comes from Puesto 2 and that every edition has a code.
 *  - The doc places the CCTV "la noche antes del turno" in V08 but "justo antes
 *    del turno" elsewhere. Resolved: shift-day afternoon, 16:41, 44 minutes
 *    before the handover Noelia saw and about an hour before the briefing.
 *  - The doc leaves the promotion unclear ("Marcelo favorece a otro candidato"
 *    vs. "compiten Marcelo, Iván y Candela"). Resolved: the candidates are Iván
 *    and Candela; Marcelo is not one, he writes the report that weighs on the
 *    decision and prefers another profile (left unnamed, off-stage). Candela's
 *    md only gives that animus after SC and denies any deal.
 *  - Doc fail-safe "coartadas de Renata, Yesenia, Tadeo y Candela" (T+60) plus
 *    their "secretos" had no evidence to hang their confrontations on. Added
 *    hilos-secundarios.md (T+60) with five text-only findings, each with its own
 *    code (SR, SY, ST, SC, and SI for Iván's parallel job, which the doc left
 *    without evidence) and the crossed alibis. Those alibis cover the afternoon
 *    window (print → briefing) as well as the incident night.
 *  - DN = Noelia's written, expanded declaration (body of giro.md; T+48), same
 *    device as DF in punto-de-control. Noelia, Osmán, Iván and Marcelo's md
 *    gate their key facts on it (see each file) and the stages wait on it.
 *  - Marcelo's confrontation 2 needs DN + V08 (doc) AND C1, so that it lands at
 *    T+60 as the doc wants and not at T+48.
 *  - Private clue: the actors' manual page (V02) is a random_player event at
 *    T+12; the public copy is a new event at T+36 (the doc puts it at T+60, too
 *    late for the V02/V03 comparison at T+24). The private event carries the
 *    code V02; the public copy carries none.
 *  - A1 (Iván's radio) is a timeline `audio_email` at T+60 (mp3). A2 (Marcelo's
 *    confession) is solution.confession_script (generated per game).
 *  - The solution's epilogues (doc section 10) are written at the end of
 *    solucion.md; the engine's personalised epilogue uses `exonerations`.
 *  - The doc's V07 "reporte en sistema" and "se reporta como posible
 *    negligencia del operador" is kept as one neutral row in V07 ("atribuido en
 *    primera instancia al operador de turno; pendiente de revisión").
 *  - Ficha/AI: Marcelo's md says he runs the briefing; the public ficha does not.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'La Palabra de Seguridad',
    'version' => '1.0',
    'code' => 'Expediente interno — La Cripta del Insomnio',
    'authority' => 'Comisión de Seguridad de La Cripta del Insomnio',

    'victim' => [
        'name' => 'Darío Lezcano',
        'label' => 'Herido',
        'incident' => 'la lesión de',
        'photo' => 'dario-lezcano.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 3 preguntas + confrontaciones por persona. The
        // engine uses 5 as the ceiling and per-person stages (see below).
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
        'tagline' => 'El actor hizo todo bien. El operador también. Y aun así, alguien salió lastimado.',

        'description' => <<<'TXT'
            La Cripta del Insomnio: una atracción inmersiva de horror con
            actores en personaje y efectos mecánicos. Una noche, un visitante
            pronuncia la palabra de seguridad en la Sala del Péndulo, tal como
            debe decirse. El actor se retira. Pero el mecanismo no se detiene a
            tiempo, y alguien sale con la muñeca lastimada. El primer reporte
            culpa al operador de turno.

            Tu equipo (3 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, dos testigos y
            al encargado de prevención, durante 75 minutos, comparando manuales,
            registros de briefing, fotografías, un mensaje y una grabación de
            radio.

            Aquí nadie rompió una regla a la vista. La pregunta es quién decidió
            que dos personas, en el mismo incidente, tuvieran reglas distintas.
            TXT,

        // Archivo esperado en public/immersion/la-palabra-de-seguridad/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-palabra-de-seguridad).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'El actor hizo todo bien. El operador también. Y aun así, alguien salió lastimado.',
            'bullets' => [
                '3 a 7 investigadores comparando manuales, registros y fotos de la atracción',
                'Seis sospechosos, dos testigos y el encargado de prevención a quienes interrogar',
                '75 minutos con el expediente llegando en tiempo real, con una grabación de radio',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 75,
        'min_players' => 3,
        'max_players' => 7,

        'price_amount' => 64900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/la-palabra-de-seguridad/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 22,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | Darío (the injured actor) and Noelia are witnesses and Osmán is a
    | secondary source on protocols; all three are `accusable => false`.
    | Darío is also the `victim`.
    |
    */

    'suspects' => [
        'marcelo-ibaceta' => [
            'name' => 'Marcelo Ibaceta',
            'role' => 'sospechoso',
            'file' => 'suspects/marcelo-ibaceta.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V05'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['DN', 'V08', 'C1'], 'confrontation' => true],
            ]],
            'photo' => 'marcelo-ibaceta.png',
            'connection' => 'Jefe de Operaciones; superior directo de los operadores de control',
            'motive' => 'Perjudicar la reputación de Iván antes de la decisión de ascenso a Coordinador de Turno de esa semana, sin buscar una lesión',
            'alibi' => 'Dice haber pasado la tarde entre el recorrido de supervisión y su oficina; sin coartada sólida para la zona de impresión',
            'public' => [
                'age' => '44 años',
                'profile' => 'Jefe de Operaciones de La Cripta del Insomnio. Profesional y controlado; supervisa los protocolos y el trabajo de los operadores.',
                'alibi' => 'Dice que esa tarde estuvo entre el recorrido de supervisión y su oficina.',
                'facts' => [
                    'Es el superior directo de los operadores de control.',
                    'Supervisa los protocolos de operación de toda la atracción.',
                ],
            ],
        ],
        'ivan-bazcunan' => [
            'name' => 'Iván Bazcuñán',
            'role' => 'sospechoso',
            'file' => 'suspects/ivan-bazcunan.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V03']],
                ['questions' => 1, 'after' => ['DN']],
                ['questions' => 3, 'after' => ['SI'], 'confrontation' => true],
            ]],
            'photo' => 'ivan-bazcunan.png',
            'connection' => 'Operador de turno del Puesto de control 2, el que oye la Sala del Péndulo',
            'motive' => 'Ninguno respecto al manual; siguió de buena fe la edición que le entregaron. Su secreto es un trabajo paralelo en otra atracción',
            'alibi' => 'En su puesto de control desde el briefing hasta el incidente',
            'public' => [
                'age' => '30 años',
                'profile' => 'Operador de control del Puesto 2, el que escucha la Sala del Péndulo. Aplicado, y visiblemente tenso desde el incidente.',
                'alibi' => 'Dice haber estado en su puesto durante toda la noche.',
                'facts' => [
                    'Era el operador a cargo de la Sala del Péndulo esa noche.',
                    'El primer reporte del incidente apunta a su puesto.',
                ],
            ],
        ],
        'renata-ojeda' => [
            'name' => 'Renata Ojeda',
            'role' => 'sospechosa',
            'file' => 'suspects/renata-ojeda.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['SR'], 'confrontation' => true],
            ]],
            'photo' => 'renata-ojeda.png',
            'connection' => 'Gerente General de la atracción',
            'motive' => 'Ninguno respecto al manual; recortó sin avisar el presupuesto de mantenimiento de efectos mecánicos',
            'alibi' => 'En la oficina administrativa toda la tarde y la noche, según el registro de acceso y una videollamada',
            'public' => [
                'age' => '48 años',
                'profile' => 'Gerente General de La Cripta del Insomnio. Ejecutiva y controlada; muy atenta a la reputación del lugar.',
                'alibi' => 'Dice haber estado en la oficina administrativa durante la tarde y la noche.',
                'facts' => [
                    'Aprueba el presupuesto de toda la atracción, mantenimiento incluido.',
                    'Responde por la seguridad y la reputación generales del lugar.',
                ],
            ],
        ],
        'yesenia-prado' => [
            'name' => 'Yesenia Prado',
            'role' => 'sospechosa',
            'file' => 'suspects/yesenia-prado.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['SY'], 'confrontation' => true],
            ]],
            'photo' => 'yesenia-prado.png',
            'connection' => 'Coordinadora de Actores; Darío Lezcano está a su cargo',
            'motive' => 'Ninguno respecto al manual de operadores; permite turnos excesivos al elenco por falta de personal',
            'alibi' => 'En la zona de actores dirigiendo el llamado de elenco, vista por tres actores',
            'public' => [
                'age' => '36 años',
                'profile' => 'Coordinadora de Actores. Cálida con el elenco, protectora, algo cansada de la temporada.',
                'alibi' => 'Dice haber estado con el elenco en la zona de actores.',
                'facts' => [
                    'Organiza los turnos y descansos de los actores.',
                    'Darío Lezcano trabaja bajo su coordinación.',
                ],
            ],
        ],
        'tadeo-ferraris' => [
            'name' => 'Tadeo Ferraris',
            'role' => 'sospechoso',
            'file' => 'suspects/tadeo-ferraris.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['ST'], 'confrontation' => true],
            ]],
            'photo' => 'tadeo-ferraris.png',
            'connection' => 'Encargado de Mantenimiento de Efectos; responsable técnico de los mecanismos',
            'motive' => 'Ninguno respecto al incidente; pospone reparaciones menores en otras salas para ahorrar tiempo',
            'alibi' => 'En la Galería de las Sombras revisando mecanismos, con hoja de verificación firmada',
            'public' => [
                'age' => '39 años',
                'profile' => 'Encargado de Mantenimiento de Efectos. Técnico, práctico y algo seco; vive entre motores y hojas de verificación.',
                'alibi' => 'Dice haber estado revisando mecanismos de otras salas.',
                'facts' => [
                    'Tiene acceso técnico a los mecanismos de todas las salas, la del Péndulo incluida.',
                    'Lleva las hojas de verificación de mantenimiento de la atracción.',
                ],
            ],
        ],
        'candela-riestra' => [
            'name' => 'Candela Riestra',
            'role' => 'sospechosa',
            'file' => 'suspects/candela-riestra.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['SC'], 'confrontation' => true],
            ]],
            'photo' => 'candela-riestra.png',
            'connection' => 'Operadora del Puesto de control 1; compañera de turno de Iván',
            'motive' => 'Ninguno respecto al manual; usa ideas de guion de otro escritor sin darle crédito',
            'alibi' => 'En su puesto de control durante la noche; entró por el acceso del personal poco antes del briefing',
            'public' => [
                'age' => '33 años',
                'profile' => 'Operadora de control del Puesto 1, en el mismo turno que Iván. Profesional y ambiciosa; mide cada palabra.',
                'alibi' => 'Dice haber estado en su propio puesto de control durante la noche.',
                'facts' => [
                    'Opera el Puesto 1, que cubre el Vestíbulo y la Sala de los Espejos.',
                    'Lleva varios años en el equipo de operadores de la atracción.',
                ],
            ],
        ],
        'dario-lezcano' => [
            'name' => 'Darío Lezcano',
            'role' => 'testigo (el herido; no es sospechoso oficial)',
            'file' => 'suspects/dario-lezcano.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
            ]],
            'photo' => 'dario-lezcano.png',
            'connection' => 'Actor de la Sala del Péndulo; es la persona lesionada',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '27 años',
                'profile' => 'Actor de la Sala del Péndulo. Se lesionó la muñeca durante el incidente. Directo y todavía algo aturdido.',
                'facts' => [
                    'Estaba en escena cuando el visitante dijo la palabra de seguridad.',
                    'Su lesión es real, aunque no grave.',
                ],
            ],
            'accusable' => false,
        ],
        'noelia-chamorro' => [
            'name' => 'Noelia Chamorro',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/noelia-chamorro.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DN']],
            ]],
            'photo' => 'noelia-chamorro.png',
            'connection' => 'Asistente administrativa; su escritorio da al pasillo de la oficina administrativa',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '26 años',
                'profile' => 'Asistente administrativa. Discreta, observadora y algo tímida; pasa el día entre la oficina y la zona de impresión.',
                'facts' => [
                    'Su escritorio da al pasillo de la oficina administrativa.',
                    'Se encarga de imprimir, archivar y repartir documentos internos.',
                ],
            ],
            'accusable' => false,
        ],
        'osman-villagra' => [
            'name' => 'Osmán Villagra',
            'role' => 'fuente secundaria (Encargado de Prevención)',
            'file' => 'suspects/osman-villagra.md',
            'interrogation' => ['stages' => [
                ['questions' => 2],
                ['questions' => 1, 'after' => ['V03']],
                ['questions' => 1, 'after' => ['V05']],
            ]],
            'photo' => 'osman-villagra.png',
            'connection' => 'Encargado de Prevención; emite y distribuye las ediciones oficiales de los manuales',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '55 años',
                'profile' => 'Encargado de Prevención. Metódico y literal: solo habla de lo que dicen sus documentos.',
                'facts' => [
                    'Emite y distribuye las ediciones oficiales de los manuales de la atracción.',
                    'Conserva el registro de distribución y archiva las hojas de briefing.',
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
        'culprit_slug' => 'marcelo-ibaceta',

        'headline' => 'Nadie rompió una regla: Darío siguió el manual de actores e Iván el de operadores que tenía, pero Marcelo le había entregado una edición alterada, con una excepción para la Sala del Péndulo, para dejarlo mal parado antes del ascenso.',

        'motive' => 'Perjudicar la reputación de Iván antes de la decisión de ascenso a Coordinador de Turno de esa semana, haciendo que un incidente de seguridad, aunque pareciera menor, quedara asociado a su nombre. No calculó que alguien saliera lastimado.',

        'method' => 'Al oír la palabra de seguridad, el actor se retiró de inmediato, como indica su manual. El operador, Iván, siguió de buena fe una edición alterada de su manual (MO-3.1), que incluía una nota para la Sala del Péndulo: dejar que el mecanismo completara su ciclo antes de detenerlo. Ese retraso hizo que el mecanismo, que funcionaba con normalidad, golpeara la muñeca de Darío. Marcelo había preparado esa edición y se la entregó en mano a Iván, fuera del canal oficial de Osmán, presentándola como "la actualización".',

        'key_evidence' => [
            'El manual de actores (regla que siguió Darío) y su testimonio: el actor cumplió su manual.',
            'El manual del puesto de control (MO-3.1, con una nota para la Sala del Péndulo) frente a la edición oficial (MO-3.0, sin excepción) que confirma Osmán.',
            'El registro de briefing: Iván es el único operador con la edición MO-3.1, que prevención nunca emitió.',
            'La declaración de Noelia y el fotograma del pasillo: Marcelo cerca de la impresión de manuales y entregando el paquete en mano a Iván.',
            'La llamada de radio de Iván: sigue "la excepción" de buena fe, sin saber que su manual era distinto.',
            'El mensaje de Marcelo, días antes: la actualización estaba pensada para el turno de Iván.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including the witnesses and the secondary source.
         */
        'exonerations' => [
            'ivan-bazcunan' => 'Siguió de buena fe la edición del manual que le entregaron, sin saber que era distinta de la oficial y de la de los demás operadores: el registro de briefing y lo que confirma Osmán muestran que él solo la recibió. Su secreto, trabajar algunas noches libres para otra atracción de la zona, es ajeno al manual.',

            'renata-ojeda' => 'Sí recortó sin avisar el presupuesto de mantenimiento de efectos mecánicos, una falta real pero ajena al incidente: el mecanismo del Péndulo funcionaba con normalidad. Estuvo en la oficina administrativa durante la tarde y la noche, según el registro de acceso y su videollamada, y no tuvo contacto con la distribución del manual.',

            'yesenia-prado' => 'Sí permitió turnos excesivos al elenco por falta de personal, una falta real pero distinta de la alteración del manual de operadores. Estuvo en la zona de actores durante la tarde y hasta el aviso, vista por Darío y otros dos actores, y no interviene en los manuales de los operadores.',

            'tadeo-ferraris' => 'Sí pospuso reparaciones menores en otras salas, pero el mecanismo de la Sala del Péndulo no tenía pendientes ni fallas y fue revisado antes de abrir. Estuvo en la Galería de las Sombras, con su hoja de verificación firmada por la operadora del puesto.',

            'candela-riestra' => 'Sí era rival de Iván por el ascenso y sí usó ideas de otro escritor sin darle crédito, pero no tuvo ningún contacto con la distribución del manual de Iván. Entró por el acceso del personal poco antes del briefing y su puesto tiene el registro de turno sin interrupciones.',

            'dario-lezcano' => 'Es el herido y un testigo, no un sospechoso: solo contó que se retiró de inmediato, como indica su manual, y que el mecanismo tardó más de lo normal en detenerse. No sabía nada de ediciones ni de entregas de manuales.',

            'noelia-chamorro' => 'Es una testigo, no una sospechosa: solo contó lo que vio desde su escritorio, que Marcelo le entregó en mano un paquete de manual a Iván. No sabe qué contenía ni tenía motivo alguno.',

            'osman-villagra' => 'Es una fuente sobre protocolos, no un sospechoso: confirmó que la edición oficial no tiene ninguna excepción para la Sala del Péndulo y que su registro de distribución no incluye ninguna edición MO-3.1. No opinó sobre nadie.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio (A2 of the design). Written to be played
        // as-is: the model only reads it aloud and lightly weaves in 1-2 real
        // questions from the table, it never composes it.
        'confession_script' => <<<'TXT'
            Quería que el incidente quedara asociado a Iván antes de la decisión de
            ascenso. Pensé que solo parecería una demora torpe, nada más.

            No calculé que alguien saliera lastimado de verdad. No fue lo que
            planeé, pero fue mi decisión, y asumo lo que pasó después.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió realmente en la Sala del Péndulo?',
            'hint' => 'Cuenta qué hizo el actor, qué hizo el operador y por qué el mecanismo se detuvo tarde.',
            'points' => 20,
            'guidance' => 'Lo esencial: el actor se retiró correctamente según su manual; el operador, Iván, siguió de buena fe una edición distinta de su manual, con una excepción para esa sala que retrasó la detención del mecanismo, y por eso Darío se lesionó. Cero si presenta a Iván como quien ignoró la palabra de seguridad, actuó con negligencia o a propósito, o si atribuye el incidente a una falla mecánica. No exigir aquí cómo se prueba la circulación del manual: se pregunta aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué buscaba el responsable y qué decisión estaba cerca.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'circulacion_manual',
                'label' => '¿Cómo se demuestra la circulación del manual alterado?',
                'hint' => 'Nombra las pruebas: qué edición tenía cada operador, quién la entregó y por qué canal.',
                'answer' => 'El registro de briefing muestra que Iván es el único operador con la edición MO-3.1, distinta de la MO-3.0 que todos los demás tenían y que es la única que prevención emitió (lo confirma Osmán, que además dice que la oficial no tiene excepción). La declaración de Noelia y el fotograma del pasillo ubican a Marcelo cerca de la impresión de manuales y entregándole el paquete en mano a Iván, fuera del canal oficial de Osmán.',
                'points' => 30,
                'guidance' => 'Puntos completos con: registro de briefing (Iván con una edición distinta), confirmación de Osmán de que la oficial no tiene la excepción, y Noelia/CCTV ubicando a Marcelo en la entrega. Puntos parciales si solo cita dos de esas tres líneas. Cero si solo dice que existían dos versiones sin explicar quién la entregó ni cómo se sabe.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+24/T+36/T+48/T+60/T+75 delivery
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Palabra de Seguridad — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — personas del expediente',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Pista privada — una página del manual de actores',
            'source_file' => 'pista-privada-manual-actores.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V02'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Desarrollo — la página del puesto y la sala en reposo',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V03', 'V06'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Complicación — el puesto de control y el pasillo',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V04', 'V08'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Copia pública — el manual de actores',
            'source_file' => 'copia-publica-manual-actores.md',
            'delivery_mode' => 'all',
            // No evidence_codes: V02 was already delivered (privately) at T+12.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 48,
            'title' => 'Giro — lo que vio Noelia y lo que firmaron los operadores',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DN = Noelia's written (expanded) declaration, the body of giro.md.
            // It has no image, so it gets its own code (same device as DF in
            // punto-de-control).
            'evidence_codes' => ['V05', 'DN'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 60,
            'title' => 'Audio — llamada de radio de Iván',

            'audio_file' => 'a1-radio-ivan.mp3',
            'audio_scene' => 'Canal de radio del Puesto de control 2: ligero eco de intercomunicador y leve ruido de fondo de sala de control, sin música.',
            'audio_context' => 'Iván, en el Puesto de control 2, responde por radio al aviso de la palabra de seguridad en la Sala del Péndulo. Tono profesional, tranquilo, de quien sigue un procedimiento que cree correcto; sin urgencia ni alarma. Duración aproximada: 10-15 segundos.',
            'audio_speaker' => 'Speaker 1 - Iván Bazcuñán',
            'audio_voice' => 'Orus',
            'audio_script' => <<<'TXT'
                Control a Sala del Péndulo, recibido el aviso. Esperando que el
                mecanismo complete el ciclo antes de detener, según la excepción.
                Deteniendo ahora.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 60,
            'title' => 'Cierre — el reporte y un mensaje recuperado',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V07', 'C1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 60,
            'title' => 'Hilos secundarios y coartadas cruzadas',
            'source_file' => 'hilos-secundarios.md',
            'delivery_mode' => 'all',
            // Text-only findings, one code each (see header).
            'evidence_codes' => ['SR', 'SY', 'ST', 'SC', 'SI'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 75,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace una pregunta sobre cómo se demuestra la circulación del manual alterado.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-palabra-de-seguridad/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-plano-recorrido.png', 'caption' => 'Plano del recorrido de La Cripta del Insomnio'],
        ],
        'pista-privada-manual-actores.md' => [
            ['file' => 'v02-manual-actores.png', 'caption' => 'Manual de actores: la palabra de seguridad'],
        ],
        'copia-publica-manual-actores.md' => [
            ['file' => 'v02-manual-actores.png', 'caption' => 'Manual de actores: la palabra de seguridad (copia para todos)'],
        ],
        'desarrollo.md' => [
            ['file' => 'v03-manual-operadores.png', 'caption' => 'Manual de operadores, tal como estaba en el Puesto de control 2'],
            ['file' => 'v06-utileria-pendulo.png', 'caption' => 'La Sala del Péndulo y su mecanismo, tras el cierre'],
        ],
        'complicacion.md' => [
            ['file' => 'v04-puesto-de-control.png', 'caption' => 'El Puesto de control 2, esa noche'],
            ['file' => 'v08-cctv-pasillo.png', 'caption' => 'Cámara del pasillo administrativo'],
        ],
        'giro.md' => [
            ['file' => 'v05-registro-briefing.png', 'caption' => 'Registro de briefing de apertura del turno de noche'],
        ],
        'cierre.md' => [
            ['file' => 'v07-captura-incidencias.png', 'caption' => 'Reporte de incidencias de esa noche'],
            ['file' => 'c1-chat-marcelo.png', 'caption' => 'Mensaje enviado días antes desde la línea de Marcelo Ibaceta'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v03-manual-operadores.png', 'caption' => 'La nota para la Sala del Péndulo, en la edición MO-3.1'],
            ['file' => 'v05-registro-briefing.png', 'caption' => 'Iván, el único operador con una edición distinta'],
            ['file' => 'v08-cctv-pasillo.png', 'caption' => 'Marcelo junto a la zona de impresión, antes del turno'],
            ['file' => 'c1-chat-marcelo.png', 'caption' => 'La actualización era para el turno de Iván'],
        ],
    ],
];
