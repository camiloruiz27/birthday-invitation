<?php

/**
 * Case manifest: "La Boda de los Ausentes"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "disappearance with a real plan" case. Every piece
 * of evidence that proves Sebastián planned to slip away that night (the
 * messages with Lucía, the cash withdrawal, the messages with Mateo) is real
 * and stays real from start to finish. The twist is not that the plan was
 * fake, but that the plan does NOT explain what happened to him: Rodrigo,
 * who knew nothing of the plan, misread Sebastián's evasive behaviour through
 * an old, unrelated grudge, followed him and shoved him. Sebastián was hurt
 * but is alive and is hiding by his own decision. No evidence or confrontation
 * may suggest a romantic flight or that anyone is holding him.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - Accusation: culprit 30 + method 10 + motive 20 are mandatory, and two
 *    optional graded questions (what happened after the disappearance 15, the
 *    prior plan and who knew which version 25) complete the doc's scoring:
 *    100 points, see the `accusation` block.
 *  - Question budget: 5 per person, plus 3 more for whoever has confrontations
 *    (8). The doc's "4 + 2 (or 1) confrontations" became 5 + 3, and the
 *    confrontation stages open by evidence delivered (see each `stages`).
 *  - V7, V8 and V13 were never scheduled in the design doc. Scheduled here:
 *    V8 at T+1 next to V9 (the "before" photo of the car next to the "after"),
 *    V7 and V13 at T+36 with the other photos (V13 is the photographer's
 *    time sheet that anchors the timestamps of V3–V8).
 *  - The complete V12 (Mateo's chat) was never scheduled either — only its
 *    fragment at T+24. The complete chat now arrives at T+50 with V10 and V11
 *    because Mateo's first confrontation is gated on it.
 *  - Rodrigo's second confrontation requires V14 (and V10). V14 stays at T+62,
 *    as in the doc (its own delivery, "hallazgo-camino.md", sent just before
 *    the cross alibis of "cierre.md"). It was briefly moved to T+50 and that
 *    handed over the confession 12 minutes early; the stage is gated by
 *    evidence, so nothing needed to move. The md gates it on V14 AND V10.
 *  - Don Rigoberto's and Mateo's second confrontations have no evidence in the
 *    doc/JSON. They are gated here: Rigoberto on V10 + V11 (the debt becomes
 *    visible), Mateo's loan on V11 (the cash withdrawal) after he already
 *    admitted knowing the plan.
 *  - Camila's confrontation is tied to "testimonio de Sofía", which is not a
 *    deliverable with a code. It is gated on V7 (the photo of her tense talk
 *    with Sebastián, T+36); the md also accepts the player citing what Sofía
 *    said, since that is the same fact.
 *  - JSON placeholder: Mateo's "plan_real" answer was "...". Written as his
 *    denial before the evidence (he knows the real plan but hides it); with V12
 *    in hand a direct question gets the real plan (the doc's fail-safe).
 *  - Sections 2 and 3 say both that Rodrigo "descubrió por accidente semanas
 *    atrás" the old episode and that he carried it "años" in silence (and the
 *    confession says "llevo años callándome"). Resolved in favour of years: he
 *    has always known; what the wedding did was revive it.
 *  - The doc places Andrés waiting "cerca del estacionamiento" at 20:52 while
 *    Sebastián's car left from the same parking lot at 21:05, and Lucía waited
 *    "cerca del cobertizo" while the shove happened on the path leading to it.
 *    Resolved with geography (documented in V1 and in the suspect md files):
 *    the parking lot is long; Andrés waited at the end next to the hall, left
 *    around 21:00 and never saw the back of the lot where Sebastián's car and
 *    the shed are; Lucía waited on the far side of the shed, with her back to
 *    the path, and heard nothing over the music. Sebastián's car was parked
 *    in the CENTRAL stretch of the lot (V1 labels it), hidden by hedges and
 *    other cars, far from the shed: otherwise Lucía would have seen or heard
 *    him drive off and the table would conclude that she lies.
 *  - V4 and V6 are guests' phone photos, yet the doc has the photographer
 *    confirm timestamps "de V3 a V8". Resolved: guests upload their photos to
 *    the event's shared album, which the photographer administers, and V13
 *    lists all of them (column "Origen") after checking every clock.
 *  - N3 of the deduction graph cites a "foto grupal posterior" that does not
 *    exist among the assets. Replaced by Sofía's testimony: nobody can place
 *    Rodrigo in the hall between 21:00 and 21:15.
 *  - N6 ("contexto indirecto sobre su divorcio") had no source. Don Rigoberto
 *    and Camila mention the divorce as family context, never its cause.
 *  - Lucía has no physical alibi for the attack window (she was near the
 *    shed). She is cleared by: A2 (she did not know anything had happened),
 *    V10 (she needed Sebastián available to collect), V5 (she was away from
 *    her table, consistent with her account) and Don Horacio, who saw a woman
 *    standing still at the corner of the shed during those minutes.
 *  - Provenance of V10, V11, V12 and A2: Valeria handed over the cloud backup
 *    of Sebastián's phone (messages, voicemail) and the receipt found among
 *    his things. Stated in the md files so the evidence has an origin.
 *  - Lucía's "por_que_cobertizo" topic is "fácil" in the JSON and "requiere
 *    buena pregunta" in the doc. The md follows the doc.
 *  - A3 (Rodrigo's confession) is not recorded: it is solution.confession_script
 *    and is generated per game.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'La Boda de los Ausentes',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Finca Los Almendros',
    'authority' => 'Investigación privada encargada por las familias Paz y Lagos',

    'victim' => [
        'name' => 'Sebastián Lagos',
        'label' => 'Desaparecido',
        'photo' => 'sebastian-lagos.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // Base budget per person; the 3 confrontation questions are extra
        // (8 in total) and open by stages, see each person's `interrogation`.
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
        'tagline' => 'Todos sabían que se iba a ausentar un rato. Nadie sabía a dónde, ni por qué.',

        'description' => <<<'TXT'
            En la finca Los Almendros, el novio desaparece de su propia boda
            minutos antes del primer baile. Horas después, su auto aparece
            abandonado, con manchas de sangre en el asiento.

            Tu equipo (4 a 7 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, dos testigos y
            al fotógrafo del evento durante 70 minutos, cruzando fotografías,
            mensajes y testimonios de una noche en la que varios invitados
            importantes se ausentaron un rato.

            Los más cercanos al novio creen saber por qué se fue. Pero nadie
            cuenta la misma historia, y lo que parece una fuga romántica
            esconde algo muy distinto.
            TXT,

        // Archivo esperado en public/immersion/la-boda-de-los-ausentes/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-boda-de-los-ausentes).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'El novio desaparece antes del primer baile. Horas después, su auto aparece abandonado, con sangre en el asiento.',
            'bullets' => [
                '4 a 7 investigadores cruzando fotos, mensajes y testimonios',
                'Seis sospechosos, dos testigos y el registro del fotógrafo a quienes interrogar',
                '70 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 70,
        'min_players' => 4,
        'max_players' => 7,

        'price_amount' => 59900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/la-boda-de-los-ausentes/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => true,
        'sort_order' => 11,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Sofía, Don Horacio) and the secondary source (the
    | event photographer's time sheet) are modeled exactly like a suspect
    | entry. The missing groom is the case's victim and is not interrogable.
    |
    */

    'suspects' => [
        'rodrigo-paz' => [
            'name' => 'Rodrigo Paz',
            'role' => 'sospechoso',
            'file' => 'suspects/rodrigo-paz.md',
            'photo' => 'rodrigo-paz.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['V4'], 'confrontation' => true, 'note' => 'Con la foto informal del jardín'],
                ['questions' => 2, 'after' => ['V10', 'V14'], 'confrontation' => true, 'note' => 'Con los documentos y el hallazgo finales'],
            ]],
            'connection' => 'Hermano de la novia; cuñado de Sebastián',
            'motive' => 'Resentimiento antiguo por un episodio entre Sebastián y su entonces esposa, que contribuyó a su divorcio; reavivado esa noche al malinterpretar la conducta evasiva del novio',
            'alibi' => 'Afirma haber estado en el baño entre las 21:00 y las 21:10',
            'public' => [
                'age' => '38 años',
                'profile' => 'Hermano mayor de la novia. Cordial y contenido; en la familia se le considera uno más de la casa. Asistió a la boda por su hermana.',
                'alibi' => 'Dice haber pasado un rato en el baño durante el lapso en que Sebastián se ausentó.',
                'facts' => [
                    'Aparece en la fotografía grupal de las 20:40, en la pista de baile.',
                    'Es cuñado de Sebastián desde que este se casó con su hermana.',
                ],
            ],
        ],
        'andres-rojas' => [
            'name' => 'Andrés Rojas',
            'role' => 'sospechoso',
            'file' => 'suspects/andres-rojas.md',
            'photo' => 'andres-rojas.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V6'], 'confrontation' => true, 'note' => 'Con la foto del estacionamiento'],
            ]],
            'connection' => 'Padrino de bodas y mejor amigo de Sebastián desde la infancia',
            'motive' => 'Ninguno respecto a la desaparición; le avergüenza haberle pedido dinero prestado a Sebastián hace poco',
            'alibi' => 'Esperando a Sebastián cerca del estacionamiento, como este le pidió, y luego de vuelta en el salón',
            'public' => [
                'age' => '35 años',
                'profile' => 'Padrino de bodas y amigo de toda la vida de Sebastián. Afectado y leal, habla de él con cariño.',
                'alibi' => 'Dice haber salido a esperar a Sebastián cerca de la zona de estacionamiento.',
                'facts' => [
                    'Es la persona con quien Sebastián más tiempo ha compartido en la vida.',
                    'Fue de los últimos en hablar con el novio antes de que desapareciera.',
                ],
            ],
        ],
        'camila-lagos' => [
            'name' => 'Camila Lagos',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-lagos.md',
            'photo' => 'camila-lagos.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V7'], 'confrontation' => true, 'note' => 'Con la foto del rincón del salón'],
            ]],
            'connection' => 'Hermana menor de Sebastián',
            'motive' => 'Ninguno respecto a la desaparición; desaprueba en privado el matrimonio por la actitud de la familia de Valeria hacia la suya',
            'alibi' => 'En el salón con la novia y las damas de honor durante la ventana crítica',
            'public' => [
                'age' => '29 años',
                'profile' => 'Hermana de Sebastián. Afectada y protectora de su familia, algo evasiva cuando se le pregunta por sus sentimientos.',
                'alibi' => 'Dice haber estado en el salón, cerca de la novia, mientras todos esperaban el primer baile.',
                'facts' => [
                    'Habló con su hermano en privado durante la recepción.',
                    'Es la familiar directa más cercana a Sebastián presente en la boda.',
                ],
            ],
        ],
        'mateo-silva' => [
            'name' => 'Mateo Silva',
            'role' => 'sospechoso',
            'file' => 'suspects/mateo-silva.md',
            'photo' => 'mateo-silva.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V10'], 'confrontation' => true, 'note' => 'Con las conversaciones completas'],
                ['questions' => 1, 'after' => ['V11'], 'confrontation' => true, 'note' => 'Con el comprobante de retiro'],
            ]],
            'connection' => 'Amigo íntimo de Sebastián',
            'motive' => 'Ninguno respecto a la agresión; prestó parte del dinero de la deuda de Sebastián y teme que no se lo devuelvan y verse implicado cuando la deuda salga a la luz',
            'alibi' => 'En el salón durante toda la ventana crítica, cubriendo la ausencia de Sebastián ante otros invitados',
            'public' => [
                'age' => '33 años',
                'profile' => 'Amigo cercano de Sebastián. Leal y algo nervioso.',
                'alibi' => 'Dice haber estado con los invitados en el salón durante buena parte de la noche.',
                'facts' => [
                    'Es de los amigos con más confianza con el novio.',
                ],
            ],
        ],
        'rigoberto-paz' => [
            'name' => 'Don Rigoberto Paz',
            'role' => 'sospechoso',
            'file' => 'suspects/rigoberto-paz.md',
            'photo' => 'rigoberto-paz.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V10', 'V11'], 'confrontation' => true, 'note' => 'Con los mensajes completos y el comprobante de retiro'],
            ]],
            'connection' => 'Padre de la novia; suegro de Sebastián',
            'motive' => 'Ninguno respecto a la agresión; conocía la deuda de Sebastián desde antes de la boda y había exigido en privado que se resolviera',
            'alibi' => 'En la mesa principal durante toda la ventana crítica, con varios invitados alrededor',
            'public' => [
                'age' => '60 años',
                'profile' => 'Padre de la novia. Formal, protector de su hija y distante en el trato; la boda también sella una relación de negocios entre familias.',
                'alibi' => 'Dice haber permanecido en la mesa principal toda la noche.',
                'facts' => [
                    'Protege con celo a su hija y a su apellido.',
                    'Aprobó el compromiso de Valeria tras conocer a la familia de Sebastián.',
                ],
            ],
        ],
        'lucia-fonseca' => [
            'name' => 'Lucía Fonseca',
            'role' => 'sospechosa',
            'file' => 'suspects/lucia-fonseca.md',
            'photo' => 'lucia-fonseca.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V10'], 'confrontation' => true, 'note' => 'Con los mensajes completos'],
            ]],
            'connection' => 'Antigua socia de negocios de Sebastián',
            'motive' => 'Ninguno respecto a la agresión; esperaba cobrar parte de una deuda esa misma noche y necesitaba que Sebastián siguiera disponible',
            'alibi' => 'Esperando cerca del cobertizo, en el lado opuesto al camino, durante la ventana crítica',
            'public' => [
                'age' => '31 años',
                'profile' => 'Antigua socia de Sebastián en un proyecto ya terminado. Profesional, algo incómoda y cuidadosa con su reputación.',
                'alibi' => 'Dice haber salido un momento a tomar aire cerca de los jardines.',
                'facts' => [
                    'Una fotografía de las 21:00 muestra su silla vacía en la mesa de postres.',
                    'Mantuvo trato profesional con Sebastián hasta que el proyecto en común se disolvió.',
                ],
            ],
        ],
        'sofia-mendoza' => [
            'name' => 'Sofía Mendoza',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-mendoza.md',
            'photo' => 'sofia-mendoza.png',
            'connection' => 'Dama de honor de la novia',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '24 años',
                'profile' => 'Dama de honor de Valeria. Estuvo pendiente de la novia y del movimiento de los invitados durante toda la recepción.',
                'facts' => [
                    'Estuvo en el salón y en la pista de baile durante toda la recepción.',
                    'Conoce los movimientos generales de la familia y de los invitados cercanos a la novia.',
                ],
            ],
            'accusable' => false,
        ],
        'horacio-vega' => [
            'name' => 'Don Horacio Vega',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/horacio-vega.md',
            'photo' => 'horacio-vega.png',
            'connection' => 'Jardinero y encargado de la finca',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '65 años',
                'profile' => 'Jardinero y encargado de la finca Los Almendros desde hace décadas. Conoce cada rincón del jardín y de sus herramientas.',
                'facts' => [
                    'Trabajó durante la fiesta atendiendo los jardines y el alumbrado.',
                    'Conoce el estado habitual del camino de piedra y del cobertizo.',
                ],
            ],
            'accusable' => false,
        ],
        'registro-fotografo-evento' => [
            'name' => 'Fotógrafo del evento / registro de fotografías',
            'role' => 'fuente secundaria (registro documental)',
            'file' => 'suspects/registro-fotografo-evento.md',
            // Not a suspect: the photographer's own time sheet. CaseAssetsTest
            // expects a portrait for every roster entry, so a shot of his
            // camera and contact sheet works as its "portrait".
            'photo' => 'registro-fotografo-evento.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V4', 'V5', 'V6', 'V7'], 'note' => 'Con las fotografías de la noche'],
            ]],
            'connection' => 'Fotógrafo contratado para cubrir la boda y administrador del álbum compartido de los invitados',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente técnica: lleva el registro de las fotografías del evento, con su hora exacta, sin interpretar lo que muestran.',
                'facts' => [
                    'Confirma la hora exacta de cada fotografía ya mostrada como evidencia.',
                    'No puede decir qué ocurrió fuera de lo que las imágenes muestran a simple vista.',
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
        'culprit_slug' => 'rodrigo-paz',

        'headline' => 'Sebastián sí planeaba ausentarse esa noche, para pagarle una deuda a Lucía Fonseca, y cada persona cercana conocía solo un fragmento distinto de ese plan. Pero lo que le pasó no salió de ese plan: Rodrigo Paz, hermano de la novia, malinterpretó su comportamiento evasivo, lo siguió por el camino del jardín y lo empujó. Sebastián huyó herido en su auto y sigue escondido por su propia decisión; nadie lo retiene.',

        'motive' => 'Un resentimiento antiguo hacia Sebastián por un episodio del pasado con la entonces esposa de Rodrigo, que contribuyó a su divorcio y que nunca se habló. Reavivado esa noche al ver a Sebastián escabullirse y malinterpretar fragmentos de una llamada suya como un encuentro impropio.',

        'method' => 'Un empujón durante una confrontación en el camino del jardín, hacia las 21:02, antes de que Sebastián llegara al cobertizo: Sebastián cayó contra el borde de una carretilla de jardín y se cortó. Después corrió a su auto y se marchó solo.',

        'key_evidence' => [
            'La foto candid de las 21:05 lo muestra desarreglado en el camino del jardín, fuera del salón, contradiciendo su "estuve en el baño todo el tiempo".',
            'El gemelo plateado de Sebastián apareció junto al borde del camino, cerca de la carretilla movida de su lugar: el incidente fue allí.',
            'Los mensajes con Lucía, el retiro de efectivo y los mensajes con Mateo prueban que el plan de Sebastián era real y que solo era una gestión de deuda; no explica la sangre.',
            'Lucía esperó en vano junto al cobertizo y su mensaje de voz confirma que no sabía que algo había pasado.',
            'Nadie puede situar a Rodrigo en el salón entre las 21:00 y las 21:15.',
            'Andrés, Camila, Mateo, Don Rigoberto y Lucía ocultaban secretos propios, pero ninguno requiere ni explica una agresión.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'andres-rojas' => 'Su evasiva sobre el dinero ocultaba su propia vergüenza: le había pedido dinero prestado a Sebastián. Esperó junto a la entrada del estacionamiento, como Sebastián le pidió, y una foto lo sitúa allí a las 20:52; volvió al salón hacia las 21:00 sin saber del plan real ni de lo ocurrido en el camino.',

            'camila-lagos' => 'Su "tonterías de familia" ocultaba su desaprobación privada del matrimonio. Su conversación tensa con Sebastián fue a las 20:00, antes de la ventana crítica, y durante esta estuvo en el salón con la novia y las damas de honor.',

            'mateo-silva' => 'Sabía el plan real y minimizó lo que sabía para no explicar la deuda ni su propio préstamo. No tenía motivo para dañar a Sebastián, a quien necesitaba disponible para recuperar su dinero, y estuvo en el salón cubriendo su ausencia durante toda la ventana crítica.',

            'rigoberto-paz' => 'Ya conocía la deuda desde antes de la boda y había exigido en privado que se resolviera, pero eso era justo lo que quería: que se arreglara discretamente. Estuvo en la mesa principal toda la noche, con varios invitados alrededor.',

            'lucia-fonseca' => 'Su "solo tomaba aire" ocultaba la deuda. Estuvo junto al cobertizo esperando el pago acordado, y necesitaba que Sebastián apareciera, no que desapareciera. Su mensaje de voz, dejado sin saber que algo había pasado, y la fotografía de su silla vacía coinciden con su relato.',

            'sofia-mendoza' => 'Es una testigo, no una sospechosa: su aporte fue confirmar la conversación tensa de Camila a las 20:00 y los movimientos generales en el salón. No tiene motivo ni secreto relevante.',

            'horacio-vega' => 'Es un testigo, no un sospechoso: su aporte fue confirmar que la carretilla estaba fuera de su lugar y que vio figuras sin poder identificarlas. No tuvo motivo alguno ni relación con Sebastián más allá de cuidar la finca.',

            'registro-fotografo-evento' => 'Es un registro documental, no una persona sospechosa: su función fue confirmar, con exactitud, la hora de cada fotografía mostrada, sin interpretar su contenido. Nunca falló: sus relojes estaban sincronizados.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Orus',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Llevo años callándome lo que pasó entre él y mi esposa de entonces. Vine
            a la boda por mi hermana, no por él. Pero esa noche lo vi escabullirse
            otra vez, con esa misma cara de siempre, y no lo pensé.

            Lo seguí. Le dije lo que pensaba de él, por fin, después de tanto tiempo
            callado. Lo empujé. No fue mi intención que cayera así.

            Se fue corriendo, herido, y no he sabido nada de él desde entonces. Ni
            siquiera sé si sigue enojado, o asustado, o las dos cosas.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Accusation form and rubric
    |--------------------------------------------------------------------------
    |
    | From the doc's scoring: responsable 30 / plan and who knew it 25 /
    | motive 20 / what happened after 25. The doc has no separate method
    | component, so "what happened after" is split: 10 for how he was hurt
    | (weapon) and 15 for where he is now.
    |
    */

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué le hicieron a Sebastián y cómo?',
            'hint' => 'Describe qué le ocurrió, dónde y a qué hora aproximada.',
            'points' => 10,
            'guidance' => 'Basta con un empujón o agresión física en el camino del jardín que lo dejó herido. No exigir la carretilla ni la hora exacta.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Explica qué movía al responsable y qué lo detonó esa noche.',
            'points' => 20,
            'guidance' => 'Lo esencial es el resentimiento antiguo por el episodio con su entonces esposa; la malinterpretación de esa noche es un plus. Cero si el motivo es la deuda o un celo romántico de la boda.',
        ],
        'questions' => [
            [
                'key' => 'que_ocurrio_despues',
                'label' => '¿Qué ocurrió con Sebastián después de desaparecer y dónde está ahora?',
                'hint' => 'Explica cómo salió de la finca y si alguien lo retiene.',
                'answer' => 'Huyó herido en su propio auto, solo, y sigue escondido por decisión propia mientras decide cómo enfrentar la deuda y lo ocurrido con Rodrigo. Nadie lo retiene y no corre peligro de muerte.',
                'points' => 15,
                'guidance' => 'Cero si afirma que alguien lo retiene o que se fugó con otra persona (fuga romántica). Puntos completos con «se fue solo, herido, y se esconde por voluntad propia».',
            ],
            [
                'key' => 'plan_previo',
                'label' => '¿Qué planeaba hacer Sebastián esa noche y qué sabía de ese plan cada persona cercana a él?',
                'hint' => 'Explica el propósito real de su ausencia y qué versión le contó a cada uno.',
                'answer' => 'Planeaba ausentarse un momento para entregarle a Lucía Fonseca, su antigua socia, parte del dinero de una deuda de negocios. A Andrés le dijo que iba a buscar un regalo sorpresa al auto, a Camila que debía atender una llamada de trabajo, y solo Mateo conocía la verdad y lo cubría.',
                'points' => 25,
                'guidance' => 'Cero si lo interpreta como una cita o fuga romántica. Repartir: el pago de la deuda a Lucía vale la mitad; las tres versiones (Andrés, Camila, Mateo) la otra mitad, proporcional a las que acierte.',
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
    | the other cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Boda de los Ausentes — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            // V8 added here during authoring: the "before" photo of the car,
            // so the table can compare it with V9 from the start.
            'evidence_codes' => ['V1', 'V8', 'V9'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — ambiente de la recepción',

            'audio_file' => 'a1-ambiente-recepcion.mp3',
            'audio_scene' => 'Recepción de una boda al aire libre en una finca: música festiva de fondo, risas y conversación distantes, tintineo de copas y un brindis breve a lo lejos. Sin música dramática.',
            'audio_context' => 'Voz de maestro de ceremonias, lejana, alegre y cálida, que anuncia el próximo primer baile de los novios. El contraste con lo que se sabrá después solo debe sentirse en retrospectiva: nada siniestro ni premonitorio.',
            'audio_speaker' => 'Speaker 1 - Maestro de ceremonias',
            'audio_voice' => 'Sadaltager',
            'audio_script' => <<<'TXT'
                ¡Y ahora, en unos minutos, el primer baile de los novios!
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — los invitados y la noche',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — unos mensajes',
            'source_file' => 'pista-privada-mensajes-lucia.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V10 PARTIAL (no contact name, no
            // mention of the debt). The complete V10 arrives at T+50 and is the
            // one Lucía's and Rodrigo's confrontations require.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — otra conversación',
            'source_file' => 'pista-privada-mensajes-mateo.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V12 PARTIAL. The complete V12
            // arrives at T+50 and is what Mateo's first confrontation requires.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Complicación — las fotos contradicen las versiones',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // V7 and V13 were never scheduled in the design doc; added here
            // (V13 is the photographer's time sheet for V3–V8).
            'evidence_codes' => ['V4', 'V5', 'V6', 'V7', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Giro — el plan de ausencia era real',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // Complete V12 added here (the doc only delivered its fragment).
            'evidence_codes' => ['V10', 'V11', 'V12'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 50,
            'title' => 'Audio — mensaje de voz de Lucía',

            'audio_file' => 'a2-mensaje-voz-lucia.mp3',
            'audio_scene' => 'Mensaje de voz grabado de noche al aire libre, cerca de una finca: grillos y un rumor lejano de fiesta al fondo, sin música. La mujer habla bajo, para que nadie la oiga.',
            'audio_context' => 'Lucía deja un mensaje a alguien que no llegó a una cita acordada: voz baja y discreta, con impaciencia contenida pero sin alarma; todavía no sabe que algo salió mal.',
            'audio_speaker' => 'Speaker 1 - Lucía Fonseca',
            'audio_voice' => 'Kore',
            'audio_script' => <<<'TXT'
                Sebastián, ya llevo un rato aquí. Entiendo si te demoraste con algo de la
                fiesta, pero no puedo quedarme mucho más sin que alguien note que no estoy
                en mi mesa. Avísame.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 62,
            'title' => 'Hallazgo — un gemelo junto al camino',
            'source_file' => 'hallazgo-camino.md',
            'delivery_mode' => 'all',
            // V14 is back at T+62, where the design doc has it. Rodrigo's second
            // confrontation (V10 + V14) is already gated by evidence, so there
            // was no need to bring the find forward and hand the confession
            // over 12 minutes early.
            'evidence_codes' => ['V14'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 62,
            'title' => 'Cierre — coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 70,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para separar el plan de Sebastián de lo que realmente le ocurrió. Queda habilitado el formulario de acusación final: además de a quién acusan, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-boda-de-los-ausentes/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-finca.png', 'caption' => 'Plano de la finca Los Almendros'],
            ['file' => 'v8-auto-antes.png', 'caption' => 'El auto de Sebastián en la cobertura de preparativos'],
            ['file' => 'v9-auto-abandonado.png', 'caption' => 'El auto, hallado horas después'],
        ],
        'expansion.md' => [
            ['file' => 'v2-plano-de-mesas.png', 'caption' => 'Plano de mesas de la recepción'],
            ['file' => 'v3-foto-grupal-2040.png', 'caption' => 'Fotografía grupal en la pista de baile'],
        ],
        'pista-privada-mensajes-lucia.md' => [
            ['file' => 'v10-conversacion-privada-1.png', 'caption' => 'Fragmento de una conversación de mensajería'],
        ],
        'pista-privada-mensajes-mateo.md' => [
            ['file' => 'v12-conversacion-privada-2.png', 'caption' => 'Fragmento de otra conversación de mensajería'],
        ],
        'complicacion.md' => [
            ['file' => 'v7-rincon-salon.png', 'caption' => 'Conversación entre dos familiares en un rincón del salón'],
            ['file' => 'v6-invitado-estacionamiento.png', 'caption' => 'Un invitado esperando cerca del estacionamiento'],
            ['file' => 'v5-mesa-postres-silla-vacia.png', 'caption' => 'La mesa de postres, con una silla vacía'],
            ['file' => 'v4-foto-candid-jardin.png', 'caption' => 'Foto informal de unos invitados, con el camino del jardín al fondo'],
            ['file' => 'v13-registro-fotografo.png', 'caption' => 'Registro de horarios del fotógrafo del evento'],
        ],
        'giro.md' => [
            ['file' => 'v10-mensajes-lucia.png', 'caption' => 'Conversación completa entre Sebastián y Lucía'],
            ['file' => 'v11-recibo-retiro.png', 'caption' => 'Comprobante de retiro de efectivo'],
            ['file' => 'v12-mensajes-mateo.png', 'caption' => 'Conversación completa entre Sebastián y Mateo'],
        ],
        'hallazgo-camino.md' => [
            ['file' => 'v14-gemelo-camino.png', 'caption' => 'Un gemelo de camisa, junto al borde del camino de piedra'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v10-mensajes-lucia.png', 'caption' => 'El plan real: pagar una deuda'],
            ['file' => 'v12-mensajes-mateo.png', 'caption' => 'Quién conocía la versión completa'],
            ['file' => 'v4-foto-candid-jardin.png', 'caption' => 'Rodrigo, fuera del salón y desarreglado, a las 21:05'],
            ['file' => 'v14-gemelo-camino.png', 'caption' => 'Dónde ocurrió el incidente'],
            ['file' => 'v9-auto-abandonado.png', 'caption' => 'Sebastián se fue herido, por su cuenta'],
        ],
    ],
];
