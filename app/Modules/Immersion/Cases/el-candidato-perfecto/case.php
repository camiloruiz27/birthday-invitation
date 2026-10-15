<?php

/**
 * Case manifest: "El Candidato Perfecto"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "two questions, not one" case. The table tends to
 * melt two questions into a single answer; the case only resolves when they
 * are pulled apart:
 *  - Who altered Diego's dossier? THREE different hands, with different
 *    interests: Diego (an inflated job title he asked for himself), Ramón (a
 *    fully fabricated reference letter, behind Diego's back) and Teresa (an
 *    interview score raised after the panel's evaluation, behind Diego's back).
 *  - Who made Mariana disappear? Only Ramón, out of panic when she told him she
 *    would put his letter in her final report. Mariana is ALIVE: she was not
 *    harmed, she is being held off-site. Teresa had nothing to do with it.
 * The selection system (logs, access records) NEVER fails for narrative
 * convenience: whatever it shows is exact.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text method. The doc's 4-component point scoring
 *    is NOT implemented; the three alterations and who benefited from each are
 *    asked for inside the free-text motive (see the T+65 unlock text) and
 *    explained in solucion.md and in each exoneration.
 *  - Question budget: the engine gives every interrogable person 5 questions
 *    (suspects, witnesses and the system alike) and 3 more when the case has
 *    confrontations for them (8 in total), instead of the doc's "4 per suspect /
 *    3 per witness + 2 confrontations". They open in stages
 *    (`interrogation.stages`): confrontation stages wait on the evidence that
 *    justifies them, and Paola's / the system's base questions wait on TP / V8 /
 *    V11 (see each entry).
 *  - The JSON's timeline_de_juego never scheduled V5, V10, V12, V13 or A1
 *    (all described in the doc's section 7). Placed here: V5 (the reference
 *    letter) at T+13 inside Diego's official dossier, so Diego's second
 *    confrontation (V5 + V6) has its material and V6 can later prove it fake;
 *    A1 (Diego's interview) at T+13 as character context; V12 (Mariana's desk)
 *    at T+1, because it frames the disappearance; V13 (Teresa–Ramón message) at
 *    T+39, because Teresa's second confrontation unlocks at T+39 in the JSON;
 *    V10 (agenda) at T+52 together with V9, which it is "coherent with".
 *  - V4 was a "partial" fragment at T+26 and complete at T+39. The partial is
 *    delivered as two random_player fragments (v4a = only the "original"
 *    column, v4b = only the "presented" column), with NO evidence code, so a
 *    private glimpse never satisfies a confrontation gate; the complete V4 is
 *    delivered at T+39. V7 is likewise partial at T+13 (v7a: final scores only,
 *    no change history, no code) and complete at T+39 (history of changes).
 *  - Ramón's first confrontation is gated on V6 AND V8: V6 proves the letter is
 *    false but only V8 (system log) shows who marked it "verified". Teresa's
 *    first confrontation is gated on V7 AND V8 for the same reason (V7 shows the
 *    change, V8 names the user).
 *  - Ramón's second confrontation requires "Paola's testimony + V11 (T+52)".
 *    A testimony is not a coded item the engine can verify, so the T+52
 *    delivery includes Paola's written statement and tags it with the free-form
 *    code "TP" (declaración ampliada de Paola). TP gates both Ramón's second
 *    confrontation and what Paola herself can say about the archive room:
 *    before TP she only gives general, true answers (paola-jimenez.md), and
 *    her interrogation stages are 3, then 2 after TP.
 *  - The doc says Mariana left "outside the office" with Ramón, yet Paola saw
 *    Ramón leave the archive room ALONE and the access log shows only his exit.
 *    Resolution: Ramón left first and alone, alterado; Mariana left a few
 *    minutes later through the archive's service door (opens from inside, no
 *    card reader, no log), toward the parking garage. Paola did not see her
 *    leave by the main door. The system md explains the service door only if
 *    asked about other exits. Ramón's afternoon absences (seen by Fernando and
 *    Hugo) are his trips to where he is keeping her; his ficha gives him a
 *    vague lie for them ("diligencias personales") and he only admits it once
 *    V11 + TP break him. His confession says "sé dónde está", not "está
 *    conmigo": he is at the office in the afternoon, she is not with him.
 *  - The doc names only three of the five finalists. The other two are named
 *    as structural filler in V1/V7 (Andrés Lozano, Beatriz Salcedo): they were
 *    not at the offices that day and have no role. Diego's "antiguo contacto
 *    que ayudaría con una referencia" is Ramón, who OFFERED the help (one
 *    version everywhere: ficha de Ramón, ficha de Diego, solucion.md); Diego
 *    only names him when shown V5 + V6 (second confrontation). The forged
 *    letter's signer, Rodrigo Valdés Arango, is in both fichas, gated on V5/V6.
 *  - Claudia's and Javier's confrontations had no evidence in the doc. Claudia's
 *    is gated on V8, whose log (verified by Ramón) includes one neutral entry
 *    "Expansión Norte: confirmada parcialmente". Javier's is gated on V4 or V9;
 *    V9 (Mariana's notes) mentions, without a name, that "un candidato" warned
 *    her about the title.
 *  - Teresa's second confrontation (conflict of interest) is gated on V13 plus
 *    V7. V13 is deliberately ambiguous: Teresa only asks Ramón to close the
 *    verifications cleanly; it never mentions a letter or any harm.
 *  - The doc's fail-safe "if nobody gathered the cross-alibis, deliver them
 *    anyway" is implemented as a global delivery (cierre.md) at T+60, before the
 *    T+65 accusation unlock. Paola's fail-safe is the T+52 statement itself.
 *  - The doc leaves the A2 confession as a diegetic audio in the timeline. Here
 *    it is NOT an asset: it is solution.confession_script, generated per game.
 *  - Dates and times fixed for consistency (not in the doc): the day is 14/11;
 *    critical window 12:00–13:00; archive log 12:11 entry (Ramón and Mariana),
 *    12:19 exit (Ramón); Mariana's meeting with the directorio 15:00; the
 *    announcement of the winner is planned for 18:00.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'El Candidato Perfecto',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Grupo Cordillera',
    'authority' => 'Comité de Selección Ejecutiva, Grupo Cordillera',

    'victim' => [
        'name' => 'Mariana Ospina',
        'label' => 'Desaparecida',
        'photo' => 'mariana-ospina.png',
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
        'tagline' => 'El expediente perfecto tenía más de un autor. Y no todos querían lo mismo.',

        'description' => <<<'TXT'
            Grupo Cordillera está a punto de anunciar al ganador de su proceso
            de selección para la Dirección General de su filial más importante.
            Horas antes, Mariana Ospina, la coordinadora del proceso, desaparece
            sin dejar rastro.

            Tu equipo (4 a 6 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco sospechosos, tres testigos y
            al sistema de selección durante 65 minutos, comparando versiones de
            documentos, puntuaciones y registros de acceso.

            El candidato mejor evaluado tiene demasiado a favor para ser casual.
            Pero aquí hay más de una irregularidad, de más de una mano, y no
            todas tienen que ver con lo que le pasó a Mariana. Separarlas es lo
            que resuelve el caso.
            TXT,

        // Archivo esperado en public/immersion/el-candidato-perfecto/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-candidato-perfecto).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Horas antes de anunciar al nuevo director general, desaparece la coordinadora del proceso de selección.',
            'bullets' => [
                '4 a 6 investigadores comparando versiones de un expediente y sus registros',
                'Cinco sospechosos, tres testigos y el sistema de selección a quienes interrogar',
                '65 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 65,
        'min_players' => 3,
        'max_players' => 6,

        'price_amount' => 59900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/el-candidato-perfecto/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => true,
        'sort_order' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The three witnesses (Fernando, Hugo, Paola) and the secondary source (the
    | selection system) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'ramon-quiroga' => [
            'name' => 'Ramón Quiroga',
            'role' => 'sospechoso',
            'file' => 'suspects/ramon-quiroga.md',
            'photo' => 'ramon-quiroga.png',
            'connection' => 'Analista de verificación de referencias; colega de Mariana en el equipo de selección',
            'motive' => 'Pánico ante la exposición inminente de la carta de referencia de Diego, que él mismo fabricó',
            'alibi' => 'Dice que habló un momento con Mariana y que ella siguió con su trabajo; sin coartada confirmada para el mediodía',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V6', 'V8'], 'confrontation' => true, 'note' => 'Con la comparación de membretes y el historial del sistema'],
                ['questions' => 1, 'after' => ['V11', 'TP'], 'confrontation' => true, 'note' => 'Con el registro de acceso y la declaración ampliada'],
            ]],
            'public' => [
                'age' => '36 años',
                'profile' => 'Analista de verificación de referencias del equipo de selección. Técnico, discreto y metódico; trabaja bajo la supervisión funcional de Mariana.',
                'alibi' => 'Dice que tuvo una conversación breve con Mariana a media jornada y que no sabe más.',
                'facts' => [
                    'Tiene acceso técnico a las referencias de los finalistas y al sistema de selección.',
                    'Trabajaba a diario con Mariana y conocía el calendario del proceso.',
                ],
            ],
        ],
        'diego-montalvo' => [
            'name' => 'Diego Montalvo',
            'role' => 'sospechoso',
            'file' => 'suspects/diego-montalvo.md',
            'photo' => 'diego-montalvo.png',
            'connection' => 'Candidato mejor evaluado; estaba bajo la coordinación de Mariana',
            'motive' => 'Ninguno respecto a la desaparición; pidió un ajuste menor de título, pero desconocía la carta fabricada y la alteración de su puntaje',
            'alibi' => 'En la sala de espera de candidatos durante toda la ventana crítica, confirmado por Claudia y Javier',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V4', 'V5', 'V6'], 'confrontation' => true, 'note' => 'Con la comparación de versiones y la carta de referencia'],
            ]],
            'public' => [
                'age' => '42 años',
                'profile' => 'Finalista con la mejor evaluación del proceso. Seguro de sí mismo, articulado y con una trayectoria directiva en operaciones regionales.',
                'alibi' => 'Dice haber esperado en la sala de candidatos junto a otros finalistas.',
                'facts' => [
                    'Es el principal beneficiario del resultado del proceso.',
                    'Su expediente es el que Mariana revisaba antes del anuncio.',
                ],
            ],
        ],
        'teresa-uribe' => [
            'name' => 'Teresa Uribe',
            'role' => 'sospechosa',
            'file' => 'suspects/teresa-uribe.md',
            'photo' => 'teresa-uribe.png',
            'connection' => 'Miembro del directorio y patrocinadora institucional del proceso',
            'motive' => 'Ninguno respecto a la desaparición; alteró la puntuación de Diego para asegurar un director menos propenso a examinar un conflicto de interés propio',
            'alibi' => 'En una reunión externa con inversionistas durante toda la ventana crítica, confirmable por asistentes ajenos a la empresa',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V7', 'V8', 'V13'], 'confrontation' => true, 'note' => 'Con la matriz con historial, el registro del sistema y los mensajes'],
            ]],
            'public' => [
                'age' => '55 años',
                'profile' => 'Miembro del directorio de Grupo Cordillera y patrocinadora institucional del proceso. Ejecutiva, controlada y muy interesada en el resultado.',
                'alibi' => 'Dice haber estado fuera de la oficina en una reunión con inversionistas.',
                'facts' => [
                    'Supervisa el proceso de selección desde el directorio.',
                    'Tiene acceso al sistema de selección y a la matriz de puntuación.',
                ],
            ],
        ],
        'claudia-reyes' => [
            'name' => 'Claudia Reyes',
            'role' => 'sospechosa',
            'file' => 'suspects/claudia-reyes.md',
            'photo' => 'claudia-reyes.png',
            'connection' => 'Finalista rival de Diego; estaba bajo la coordinación de Mariana',
            'motive' => 'Ninguno respecto a la desaparición; exageró levemente su propio currículum (falta menor y ajena al caso)',
            'alibi' => 'En la sala de espera de candidatos durante toda la ventana crítica, confirmada por Diego y Javier',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V8'], 'confrontation' => true, 'note' => 'Con el historial del sistema'],
            ]],
            'public' => [
                'age' => '45 años',
                'profile' => 'Finalista y segunda en la clasificación. Profesional, algo resentida con el resultado y a la defensiva cuando se habla de su propio expediente.',
                'alibi' => 'Dice haber esperado en la sala de candidatos con otros finalistas.',
                'facts' => [
                    'Competía directamente con Diego por la Dirección General.',
                    'Considera que el proceso la ha tratado de forma injusta.',
                ],
            ],
        ],
        'javier-pena' => [
            'name' => 'Javier Peña',
            'role' => 'sospechoso',
            'file' => 'suspects/javier-pena.md',
            'photo' => 'javier-pena.png',
            'connection' => 'Finalista rival de Diego; habló con Mariana antes de la desaparición',
            'motive' => 'Ninguno respecto a la desaparición; señaló a Mariana una duda legítima sobre Diego y le avergüenza haberlo hecho',
            'alibi' => 'En la sala de espera de candidatos durante toda la ventana crítica, confirmado por Diego y Claudia',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V4'], 'confrontation' => true, 'note' => 'Con la comparación de versiones'],
            ]],
            'public' => [
                'age' => '48 años',
                'profile' => 'Finalista de complexión robusta y trato franco, algo más informal que el resto. Muy interesado en ganar y visiblemente nervioso.',
                'alibi' => 'Dice haber esperado en la sala de candidatos con los demás finalistas.',
                'facts' => [
                    'Habló con Mariana sobre Diego antes del día de la desaparición.',
                    'Tiene contactos propios en el sector donde trabajó Diego.',
                ],
            ],
        ],
        'fernando-acosta' => [
            'name' => 'Lic. Fernando Acosta',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/fernando-acosta.md',
            'photo' => 'fernando-acosta.png',
            'connection' => 'Evaluador senior del comité de selección',
            'motive' => null,
            'alibi' => null,
            // The panel's 7,4 is gated on V7; his note about Ramón's absences on V11 + TP.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V7'], 'note' => 'Con la matriz y su historial de cambios'],
                ['questions' => 1, 'after' => ['V11', 'TP'], 'note' => 'Con el registro de acceso y la declaración de Paola'],
            ]],
            'public' => [
                'age' => '50 años',
                'profile' => 'Evaluador senior del comité. Participó en la evaluación original de todos los candidatos y recuerda con detalle los puntajes del panel.',
                'facts' => [
                    'Participó en las entrevistas de los cinco finalistas.',
                    'Estuvo en las oficinas durante toda la jornada.',
                ],
            ],
            'accusable' => false,
        ],
        'hugo-bravo' => [
            'name' => 'Lic. Hugo Bravo',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/hugo-bravo.md',
            'photo' => 'hugo-bravo.png',
            'connection' => 'Evaluador; consultor externo de recursos humanos',
            'motive' => null,
            'alibi' => null,
            // His note about Ramón's behaviour that afternoon is gated on V11 + TP.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V11', 'TP'], 'note' => 'Con el registro de acceso y la declaración de Paola'],
            ]],
            'public' => [
                'age' => '58 años',
                'profile' => 'Consultor externo de recursos humanos que participa como evaluador. Sabe explicar, sin tecnicismos, cómo funciona un proceso normal de verificación de referencias.',
                'facts' => [
                    'Habló con Mariana esa misma mañana.',
                    'Conoce el procedimiento habitual de verificación de referencias.',
                ],
            ],
            'accusable' => false,
        ],
        'paola-jimenez' => [
            'name' => 'Paola Jiménez',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/paola-jimenez.md',
            'photo' => 'paola-jimenez.png',
            'connection' => 'Asistente administrativa del proceso de selección',
            'motive' => null,
            'alibi' => null,
            // What she saw at the archive room is gated on her written
            // statement (TP, delivered at T+52 with the giro).
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['TP'], 'note' => 'Con su declaración ampliada'],
            ]],
            'public' => [
                'age' => '33 años',
                'profile' => 'Asistente administrativa del proceso. Maneja la agenda y su escritorio está en el pasillo del piso donde se guardan los expedientes físicos.',
                'facts' => [
                    'Conoce la agenda de Mariana y la reunión que tenía pendiente.',
                    'Su puesto de trabajo da a una de las puertas del piso.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-seleccion' => [
            'name' => 'Sistema de selección / verificador externo de antecedentes',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-seleccion.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-control-buque") — a
            // shot of the platform's dashboard works as its "portrait".
            'photo' => 'sistema-seleccion.png',
            'connection' => 'Historial de versiones, verificaciones, puntuaciones y accesos a la sala de archivo',
            'motive' => null,
            'alibi' => null,
            // Every record this source confirms is gated on delivered evidence
            // (see sistema-seleccion.md): the history on V8, the archive log on V11.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V8'], 'note' => 'Con el historial del sistema'],
                ['questions' => 1, 'after' => ['V11'], 'note' => 'Con el registro de acceso al archivo'],
            ]],
            'public' => [
                'profile' => 'Fuente técnica del proceso: registra quién sube cada documento, quién marca cada referencia, quién toca la matriz de puntuación y quién cruza la puerta del archivo, sin interpretar nada.',
                'facts' => [
                    'Guarda usuario y fecha de cada acción sobre un expediente.',
                    'No puede decir qué se dijeron dos personas ni con qué intención actuaron.',
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
        'culprit_slug' => 'ramon-quiroga',

        'headline' => 'El expediente de Diego tenía tres alteraciones de tres autores distintos, pero la desaparición de Mariana es un acto aparte: Ramón Quiroga, en pánico porque ella iba a exponer la carta de referencia que él mismo fabricó, le quitó el teléfono y la convenció de irse con él. Mariana está viva.',

        'motive' => 'Pánico ante la inminente exposición de la carta de referencia fabricada por él, con consecuencias legales y laborales graves: Mariana la había descubierto y pensaba incluirla esa misma tarde en su informe al directorio.',

        'method' => 'La citó en privado en la sala de archivo al mediodía (entrada conjunta a las 12:11, salida suya, solo, a las 12:19), le quitó el teléfono y, presionándola emocionalmente, la convenció de acompañarlo fuera de la oficina, sin llegar a hacerle daño físico.',

        'key_evidence' => [
            'El registro de acceso a la sala de archivo muestra la entrada conjunta de Ramón y Mariana a las 12:11 y la salida de Ramón solo a las 12:19.',
            'Paola los vio entrar juntos y vio salir a Ramón solo, visiblemente alterado.',
            'La comparación de membretes demuestra que la carta de referencia de Diego es falsa, y el registro del sistema muestra que fue Ramón quien la subió y la marcó como verificada.',
            'Las notas de Mariana ya documentaban la irregularidad, y su agenda tenía el informe al directorio a las 15:00: Ramón tenía horas, no días.',
            'La mentira de Ramón ("hablamos un momento y se fue a seguir con su trabajo") choca con el registro de acceso y con el testimonio de Paola.',
            'Diego (que solo pidió un ajuste de título), Teresa (que solo alteró una puntuación) y los demás tienen coartadas o motivos que no necesitan la desaparición.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per suspect that is not the culprit,
         * including the three witnesses and the secondary source.
         */
        'exonerations' => [
            'diego-montalvo' => 'Sí tocó su expediente: pidió que su cargo se presentara con un título más senior, un embellecimiento menor que admite al ver la comparación. Pero no sabía que la carta de referencia sería completamente falsa ni que su puntuación sería subida a sus espaldas, y durante la ventana crítica estaba en la sala de espera con Claudia y Javier, que lo confirman. No tuvo nada que ver con la desaparición de Mariana.',

            'teresa-uribe' => 'Sí alteró el proceso: subió la puntuación de liderazgo estratégico de Diego después de la evaluación del panel, para asegurar un director menos propenso a examinar su conflicto de interés con un proveedor. Es una falta grave, pero su interés se satisface con el silencio del proceso, no con dañar a Mariana, y durante toda la ventana crítica estaba en una reunión externa con inversionistas, confirmada por asistentes ajenos a la empresa. Tampoco sabía de la carta fabricada por Ramón.',

            'claudia-reyes' => 'Su motivo aparente era haber perdido el puesto de forma injusta, pero su única falta es una exageración menor sobre su papel en un proyecto, sin relación con Mariana. Estuvo en la sala de espera durante toda la ventana crítica, con Diego y Javier como testigos.',

            'javier-pena' => 'Era el más desesperado por ganar, pero su única participación fue señalar de buena fe una duda legítima sobre el cargo de Diego, lo que sin saberlo aceleró la revisión de Mariana. Estuvo en la sala de espera durante toda la ventana crítica, con Diego y Claudia como testigos.',

            'fernando-acosta' => 'Es un evaluador, no un sospechoso: su aporte fue recordar el puntaje original del panel y notar que Ramón se ausentaba de su puesto esa tarde. No tuvo motivo ni oportunidad, y no tenía nada que ocultar.',

            'hugo-bravo' => 'Es un consultor externo, no un sospechoso: su aporte fue explicar cómo funciona una verificación normal de referencias y recordar que Mariana tenía "algo importante que revisar" esa mañana. No tuvo motivo ni oportunidad, y no tenía nada que ocultar.',

            'paola-jimenez' => 'Es la asistente del proceso, no una sospechosa: su aporte fue ver entrar juntos a Ramón y a Mariana en la sala de archivo y ver salir a Ramón solo y alterado, además de confirmar la reunión pendiente con el directorio. No tuvo motivo ni oportunidad.',

            'sistema-seleccion' => 'Es un sistema automatizado, no una persona: no puede haber intervenido. Su función fue confirmar, con exactitud, quién subió cada documento, quién marcó la referencia como verificada, quién modificó la puntuación y quién entró y salió de la sala de archivo, sin interpretar intenciones. Nunca falló: lo que mostró era cierto.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Iapetus',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Fabriqué esa carta yo. Nadie me lo ordenó directamente, pero entendí
            que se esperaba que Diego avanzara, y quise ayudar a que pasara sin
            complicaciones.

            Cuando Mariana me dijo que ya lo sabía y que lo iba a incluir en su
            informe, no lo pensé con calma. Le pedí que no lo hiciera, le quité
            el teléfono, y la convencí de venir conmigo a hablarlo con calma en
            otro lugar.

            No le he hecho daño. Está bien, sé dónde está y la he ido a ver. Pero no sé cómo
            deshacer esto sin que se entere todo el mundo, y eso me tiene
            paralizado.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Accusation form and rubric
    |--------------------------------------------------------------------------
    |
    | From the doc's scoring: responsable 30 / method + motive 25 (split
    | 12/13) / the three alterations and their authors 30 / who benefited
    | from each 15. The doc's single field "who made each part and for whose
    | benefit" is asked as two questions so each component is graded apart.
    |
    */

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué le hicieron a Mariana y cómo?',
            'hint' => 'Describe dónde y cómo la abordaron, y cómo salió de la oficina.',
            'points' => 12,
            'guidance' => 'Basta con: la abordó en privado en la sala de archivo, le quitó el teléfono y la convenció bajo presión emocional de irse con él. Cero si afirma que está muerta o fue herida.',
        ],
        'motive' => [
            'label' => '¿Por qué la hizo desaparecer?',
            'hint' => 'Explica qué temía el responsable y por qué tenía tan poco tiempo.',
            'points' => 13,
            'guidance' => 'Lo esencial es el pánico a que Mariana expusiera la carta de referencia que él mismo fabricó; el informe al directorio de esa tarde es un plus.',
        ],
        'questions' => [
            [
                'key' => 'autores_alteraciones',
                'label' => '¿Quién alteró cada parte del expediente de Diego?',
                'hint' => 'Nombra al autor de cada alteración: el título, la carta de referencia y la puntuación.',
                'answer' => 'El título lo infló el propio Diego Montalvo; la carta de referencia la fabricó por completo Ramón Quiroga, que además la marcó como verificada; la puntuación de liderazgo la subió Teresa Uribe después de la evaluación del panel.',
                'points' => 30,
                'guidance' => 'Diez puntos por cada autor correcto. Cero en toda la pregunta si culpa a Diego de las tres alteraciones.',
            ],
            [
                'key' => 'beneficiarios',
                'label' => '¿En beneficio de quién se hizo cada alteración?',
                'hint' => 'Para cada alteración, di a quién favorecía y si Diego sabía de ella.',
                'answer' => 'El título, en beneficio del propio Diego. La carta, en beneficio de Diego pero a sus espaldas: él esperaba una recomendación honesta. La puntuación, en beneficio de Teresa, que quería un director menos propenso a examinar su conflicto de interés con un proveedor; Diego no lo sabía.',
                'points' => 15,
                'guidance' => 'Lo clave es que la puntuación beneficiaba a Teresa y no a Diego, y que Diego no sabía de la carta ni de la puntuación. Cero si presenta a Diego como autor o cómplice de todo.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+13/T+26/T+39/T+52/T+65 delivery
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases), plus one extra global delivery at T+60 (cross-alibis fail-safe).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El Candidato Perfecto — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V2', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 13,
            'title' => 'Expansión — el proceso, los candidatos y el expediente de Diego',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            // V7 partial (v7a: final scores only) carries no code on purpose.
            'evidence_codes' => ['V3', 'V5'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 13,
            'title' => 'Audio — fragmento de la entrevista final de Diego',

            'audio_file' => 'a1-entrevista-diego.wav',
            'audio_scene' => 'Sala de entrevistas corporativa, ambiente silencioso con un leve eco de sala amplia; fragmento grabado de una entrevista de selección ejecutiva. Sin música.',
            'audio_context' => 'Diego responde con seguridad genuina y sin nerviosismo: describe su experiencia liderando equipos regionales como alguien que sabe de lo que habla. Tono competente, articulado y natural; no debe sonar sospechoso.',
            'audio_speaker' => 'Speaker 1 - Diego Montalvo',
            'audio_voice' => 'Orus',
            'audio_script' => <<<'TXT'
                Creo que mi experiencia liderando equipos regionales me da una
                perspectiva muy concreta de los desafíos operativos que enfrenta
                esta filial. No vengo con teoría, vengo con resultados medibles.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — la versión original del expediente',
            'source_file' => 'pista-privada-version-original.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: this is a partial private glimpse
            // (V4 fragment, "original" column only) and must not satisfy any
            // confrontation gate on its own.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — la versión presentada del expediente',
            'source_file' => 'pista-privada-version-presentada.md',
            'delivery_mode' => 'random_player',
            // V4 fragment ("presented" column only). The complete V4 arrives
            // at T+39 and is what Diego's and Javier's confrontations need.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 39,
            'title' => 'Complicación — tres versiones, tres manos',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4', 'V6', 'V7', 'V8', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Giro — lo que Mariana ya sabía y quién la vio por última vez',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // TP = Paola's extended written statement, included in giro.md.
            // It is the gate for Ramón's second confrontation, together with V11.
            'evidence_codes' => ['V9', 'V10', 'V11', 'TP'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 60,
            'title' => 'Cierre — coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 65,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene lo necesario para separar las dos preguntas del caso: quién hizo qué sobre el expediente de Diego y quién hizo desaparecer a Mariana. Queda habilitado el formulario de acusación final: además de a quién acusan, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-candidato-perfecto/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-organigrama-proceso.png', 'caption' => 'Organigrama del proceso de selección'],
            ['file' => 'v2-sala-de-archivo.png', 'caption' => 'La sala de archivo del piso 8'],
            ['file' => 'v12-escritorio-mariana.png', 'caption' => 'El escritorio de Mariana, tal como lo dejó'],
        ],
        'expansion.md' => [
            ['file' => 'v3-expediente-diego.png', 'caption' => 'Carátula del expediente de Diego Montalvo'],
            ['file' => 'v5-carta-referencia.png', 'caption' => 'Carta de referencia incluida en el expediente de Diego'],
            ['file' => 'v7a-matriz-evaluacion.png', 'caption' => 'Matriz de evaluación de los finalistas'],
        ],
        'pista-privada-version-original.md' => [
            ['file' => 'v4a-version-original.png', 'caption' => 'Fragmento de una comparación de versiones del expediente'],
        ],
        'pista-privada-version-presentada.md' => [
            ['file' => 'v4b-version-presentada.png', 'caption' => 'Otro fragmento de una comparación de versiones del expediente'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-comparacion-titulo.png', 'caption' => 'Comparación completa de versiones del expediente'],
            ['file' => 'v6-comparacion-membrete.png', 'caption' => 'Membrete de la carta presentada frente a una muestra real'],
            ['file' => 'v7-matriz-historial-cambios.png', 'caption' => 'Matriz de evaluación con historial de cambios'],
            ['file' => 'v8-registro-sistema.png', 'caption' => 'Historial de actividad del sistema de selección'],
            ['file' => 'v13-mensaje-ramon-teresa.png', 'caption' => 'Mensajes entre Teresa y Ramón, días antes del anuncio'],
        ],
        'giro.md' => [
            ['file' => 'v9-notas-informe-mariana.png', 'caption' => 'Notas de Mariana para su informe final'],
            ['file' => 'v10-agenda-reunion-directorio.png', 'caption' => 'La agenda de Mariana para esa tarde'],
            ['file' => 'v11-registro-acceso-archivo.png', 'caption' => 'Registro de acceso a la sala de archivo'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v4-comparacion-titulo.png', 'caption' => 'Alteración 1 — el título que pidió Diego'],
            ['file' => 'v6-comparacion-membrete.png', 'caption' => 'Alteración 2 — la carta fabricada'],
            ['file' => 'v7-matriz-historial-cambios.png', 'caption' => 'Alteración 3 — la puntuación subida'],
            ['file' => 'v8-registro-sistema.png', 'caption' => 'Quién hizo cada cosa y cuándo'],
            ['file' => 'v11-registro-acceso-archivo.png', 'caption' => 'La desaparición — quién salió solo a las 12:19'],
        ],
    ],
];
