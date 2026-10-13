<?php

/**
 * Case manifest: "El huésped que nunca llegó"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is an "identity" case. Nobody lies about having seen
 * "Daniel": every witness sincerely describes a real encounter, but the
 * encounters belong to different people. A man used the name "Daniel Rivas"
 * to approach Carmen Otero; the technician mistook another real guest (Ramón)
 * for him. Every lie at the table protects a personal secret unrelated to the
 * deception, and each of those is explained in the exonerations.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - Victim: the person who is really missing is Carmen Otero, but the
 *    interrogation board shows `victim` (name + photo, stamped "Víctima") from
 *    minute 10, and putting Carmen there would hand the table the central twist
 *    at T+10. The victim is therefore "Daniel Rivas" — the name the report was
 *    filed under — with a neutral photo of the door of room 4 (no face), so it
 *    never suggests that he is one consistent person. Carmen appears only
 *    from the Giro (T+45) onward.
 *  - The culprit is a man outside the roster of interrogable people. As the
 *    engine needs a roster entry to accuse, he is modelled as
 *    "huesped-habitacion-4" (accusable, NOT interrogable). Everything the
 *    player sees about him (slug, name, role, connection, ficha, portrait)
 *    is deliberately neutral: "Daniel Rivas (huésped de la habitación 4)",
 *    "no localizado". That he is an alias is told only by the solution. His
 *    md is never read at the table (he cannot be questioned); the winners'
 *    epilogue is told by Mercedes (`solution.epilogue_speaker`).
 *  - Accusation: culprit + method (how the identity was built) + motive are
 *    mandatory; the doc's 4-component score (check-in 20 / incompatible
 *    descriptions 25 / Carmen 30 / identity 25) is re-balanced in the
 *    `accusation` block as culprit 10, method 20, motive 10 and three optional
 *    graded questions (Carmen 25, descriptions 20, records 15) = 100. The
 *    "Daniel is a real person" penalty is a zero in the guidance of those
 *    questions.
 *  - Question budget: 5 per person (suspects, witnesses and the system), plus
 *    3 more for whoever has confrontations (8). Stages open them as evidence
 *    arrives: Lorena 3 + 2 after V6+A2, Mercedes 4 + 1 after V6+A2, and every
 *    confrontation stage after V8 (T+34, the Complicación) or later.
 *  - Never scheduled in the doc's timeline_de_juego: V4, V7, V9, V10, V11 and
 *    A1. Placed so that each lands where it supports a testimony:
 *      A1 + V7 at T+1 (A1 is tagged "Gancho/evidencia" in the doc and Mercedes'
 *      confirmation call is the opening hook; V7 is atmosphere with V1);
 *      V10 at T+10 with the other administrative records (V2, V5);
 *      V4 + V9 at T+34, next to V8, because they reinforce the "staged room"
 *      reading and the key mechanism after the descriptions stop matching;
 *      V11 (Carmen's photo, supplied by Lorena) at T+45 with V6 and A2.
 *  - V8 (comparison sheet) is split: two random_player fragments at T+22 (the
 *    waiter's column and the technician's column, no evidence codes) and the
 *    complete sheet at T+34. The conflicting columns are never in the same
 *    private hand. Iván's column only appears in the complete V8.
 *  - Reservation channel: the doc says the reservation was made by phone
 *    (section 2, section 3.1, JSON) in one place and "en línea" in another
 *    (section 3.7). Resolved as "by phone, entered in the system by Mercedes
 *    with a card as guarantee"; plus a second, later call to confirm and ask for
 *    the key in the locker (A1). V10 therefore lists those two calls.
 *  - Arrival: the doc has the driver drop the man "día 1 tarde" while the call
 *    asks for a late arrival. Resolved as "al caer la tarde, con poca luz",
 *    Carmen waiting at the door (she had her own room): she pays the fare and
 *    says "gracias, Daniel". Mercedes was not at reception then.
 *  - Confrontations. Mercedes needs V2 and V10 and Pablo V5, all delivered at
 *    T+10, but the design opens confrontations at T+34 (the Complicación,
 *    V8), so their confrontation stages also wait for V8. Pablo, Iván and
 *    Germán need V8 (T+34); Germán also cites Ramón's
 *    expanded testimony, which is text inside complicacion.md (T+34). Olga's
 *    has no evidence in the doc, so it is gated on V8 as the marker that the
 *    confrontation phase is open. A second, secret-admission confrontation
 *    was added for Mercedes (V2+V10), Pablo (V5), Germán and Iván (V8): the
 *    doc's confrontations deal with the puzzle only and left their secrets
 *    undiscoverable.
 *  - Lorena: the roster opens at T+10 but her material (V6, V11, A2) arrives at
 *    T+45. Her public ficha is neutral, and until V6 and A2 are delivered her
 *    md only says she lost touch with someone close (no name, no kinship, no
 *    dinner, no chat); Carmen, the sister and the chat come after V6+A2, which
 *    is also when her second stage of questions opens.
 *  - Fail-safes that the engine cannot do: "V8 highlights the differences when
 *    consulted again" is not implemented (the sheet stays neutral); the
 *    Lorena fail-safe at T+45 is covered by giro.md and by her md.
 *  - There is no confession from the culprit: A2 (Carmen's voice message,
 *    delivered at T+45 as an audio_email) is the "audio revelador" of the
 *    doc. `confession_script` is therefore omitted, so the confession ending
 *    is not offered. `confession_voice` is declared only for completeness.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'El huésped que nunca llegó',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Hotel rural, habitación 4',
    'authority' => 'Equipo independiente de investigación de personas desaparecidas',

    'victim' => [
        // The name the report was filed under. NOT Carmen: see the note above.
        'name' => 'Daniel Rivas',
        'label' => 'Reportado como desaparecido',
        'photo' => 'daniel-rivas.png',
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
        'tagline' => 'Todos hablaron con Daniel. El problema es que ninguno vio lo mismo.',

        'description' => <<<'TXT'
            En un pequeño hotel rural, fuera de temporada, se denuncia la
            desaparición de Daniel Rivas, huésped de la habitación 4. Su cuarto
            tiene equipaje y varias personas recuerdan haber hablado con él.
            Pero nadie sabe dónde está, y los registros del hotel no cuadran
            del todo con lo que dicen los testigos.

            Tu equipo (3 a 6 investigadores, ideal 4) recibe el expediente en
            tiempo real durante 55 minutos y puede interrogar a huéspedes,
            personal del hotel, testigos y al propio sistema de reservas,
            cruzando descripciones, recibos, registros y mensajes.

            Aquí no gana quien busca al mentiroso. Gana quien se hace una sola
            pregunta, de mil formas distintas: qué vio realmente cada persona, y
            cómo supo de quién se trataba.
            TXT,

        // Archivo esperado en public/immersion/el-huesped-que-nunca-llego/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/el-huesped-que-nunca-llego).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Un huésped desaparece de la habitación 4 de un hotel rural. Todos dicen haber hablado con él.',
            'bullets' => [
                '3 a 6 investigadores cruzando testimonios, recibos, registros y mensajes',
                'Ocho fuentes a quienes interrogar: huéspedes, personal, testigos y reservas',
                '55 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'medium',
        'duration_minutes' => 55,
        'min_players' => 3,
        'max_players' => 6,

        'price_amount' => 49900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/el-huesped-que-nunca-llego/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => true,
        'sort_order' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Ramón, Lorena) and the secondary source (the booking
    | system) are modeled exactly like a suspect entry. The man who used the
    | alias is a roster entry that can only answer with a system note.
    |
    */

    'suspects' => [
        'mercedes-quintanilla' => [
            'name' => 'Mercedes Quintanilla',
            'role' => 'sospechosa',
            'file' => 'suspects/mercedes-quintanilla.md',
            'photo' => 'mercedes-quintanilla.png',
            'connection' => 'Dueña y gerente del hotel',
            'motive' => 'Ninguno respecto al engaño; oculta que considera vender el hotel por dificultades financieras (sin relación con el caso)',
            'alibi' => 'Solo trató con el huésped por teléfono, nunca en persona, y no estaba en recepción cuando llegó',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                // The only thing she can add about Carmen waits for V6 + A2 (T+45).
                ['questions' => 1, 'after' => ['V6', 'A2'], 'note' => 'Con el chat y el mensaje de voz'],
                // V2/V10 arrive at T+10 with the mail that opens the
                // interrogations; the design opens confrontations at T+34 (V8).
                ['questions' => 1, 'after' => ['V2', 'V8'], 'confrontation' => true, 'note' => 'Con el registro de reservas'],
                ['questions' => 2, 'after' => ['V2', 'V10', 'V8'], 'confrontation' => true, 'note' => 'Con el registro de reservas y el de llamadas'],
            ]],
            'public' => [
                'age' => '58 años',
                'profile' => 'Dueña y gerente del hotel. Lleva ella misma la recepción y las reservas; protectora de su negocio y de su equipo.',
                'alibi' => 'Dice haber atendido la reserva con normalidad, como cualquier otra.',
                'facts' => [
                    'Recibió la reserva de la habitación 4 y atendió la llamada con la que se confirmó.',
                    'Dejó la llave en el casillero de recepción, tal como le pidieron.',
                ],
            ],
        ],
        'pablo-andrade' => [
            'name' => 'Pablo Andrade',
            'role' => 'sospechoso',
            'file' => 'suspects/pablo-andrade.md',
            'photo' => 'pablo-andrade.png',
            'connection' => 'Mesero del restaurante del hotel',
            'motive' => 'Ninguno; ha tomado pequeñas cantidades de la caja del restaurante (sin relación con el caso)',
            'alibi' => 'Sirviendo en el restaurante la noche de la cena',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['V5', 'V8'], 'confrontation' => true, 'note' => 'Con el recibo del restaurante'],
                ['questions' => 2, 'after' => ['V8'], 'confrontation' => true, 'note' => 'Con la ficha comparativa de descripciones'],
            ]],
            'public' => [
                'age' => '26 años',
                'profile' => 'Mesero del restaurante del hotel. Joven y rápido con el servicio, pero se pone nervioso cuando la conversación se desvía.',
                'alibi' => 'Dice haber estado esa noche en su puesto, atendiendo las mesas.',
                'facts' => [
                    'Sirvió una cena para dos personas la primera noche.',
                    'Anotó un apellido junto al número de mesa en el recibo.',
                ],
            ],
        ],
        'german-figueroa' => [
            'name' => 'Germán Figueroa',
            'role' => 'sospechoso',
            'file' => 'suspects/german-figueroa.md',
            'photo' => 'german-figueroa.png',
            'connection' => 'Técnico de mantenimiento externo',
            'motive' => 'Ninguno; realiza trabajos de reparación sin declarar impuestos (sin relación con el caso)',
            'alibi' => 'Reparando un desperfecto en el pasillo; habló con alguien allí',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V8'], 'confrontation' => true, 'note' => 'Con la ficha comparativa de descripciones'],
            ]],
            'public' => [
                'age' => '45 años',
                'profile' => 'Técnico de mantenimiento externo al que el hotel llama cuando hay desperfectos. Directo y algo hosco con los extraños.',
                'alibi' => 'Dice haber ido solo a reparar lo que le encargaron.',
                'facts' => [
                    'Fue llamado para reparar un desperfecto en el pasillo, cerca de la habitación 4.',
                    'Habló con alguien en el pasillo durante su visita.',
                ],
            ],
        ],
        'olga-reyes' => [
            'name' => 'Olga Reyes',
            'role' => 'sospechosa',
            'file' => 'suspects/olga-reyes.md',
            'photo' => 'olga-reyes.png',
            'connection' => 'Huésped de la habitación contigua a la 4',
            'motive' => 'Ninguno; viajó para reencontrarse en secreto con un antiguo amor (sin relación con el caso)',
            'alibi' => 'En su habitación, sin ver a nadie; solo oyó una conversación a través de la pared',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V8'], 'confrontation' => true, 'note' => 'Con la ficha comparativa de descripciones'],
            ]],
            'public' => [
                'age' => '63 años',
                'profile' => 'Huésped de la habitación contigua a la 4. Viaja con bastón y ropa cómoda, y es reservada sobre los motivos de su visita.',
                'alibi' => 'Dice haberse limitado a descansar en su habitación.',
                'facts' => [
                    'Su habitación comparte pared con la habitación 4.',
                    'Se alojó sola en el hotel, fuera de temporada.',
                ],
            ],
        ],
        'ivan-caceres' => [
            'name' => 'Iván Cáceres',
            'role' => 'sospechoso',
            'file' => 'suspects/ivan-caceres.md',
            'photo' => 'ivan-caceres.png',
            'connection' => 'Conductor que dejó a un pasajero en la entrada del hotel',
            'motive' => 'Ninguno; opera un servicio de taxi no autorizado (sin relación con el caso)',
            'alibi' => 'Dejó al pasajero en la puerta y se marchó',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V8'], 'confrontation' => true, 'note' => 'Con la ficha comparativa de descripciones'],
            ]],
            'public' => [
                'age' => '34 años',
                'profile' => 'Conductor que hace viajes a la zona del hotel. Relajado y conversador, salvo cuando se habla de su trabajo.',
                'alibi' => 'Dice haber hecho solo su viaje y haberse marchado.',
                'facts' => [
                    'Dejó a un pasajero en la entrada del hotel el día de la llegada.',
                    'Suele recoger y dejar clientes frente al hotel.',
                ],
            ],
        ],
        // Neutral on purpose: the slug, name, role and connection all reach the
        // player's browser from minute 10 (board + accusation form). Nothing
        // here may say he is an alias; that belongs to the solution only.
        'huesped-habitacion-4' => [
            'name' => 'Daniel Rivas (huésped de la habitación 4)',
            'role' => 'no localizado',
            'file' => 'suspects/huesped-habitacion-4.md',
            // No real portrait exists: a neutral placeholder silhouette
            // (see ASSET_PROMPTS.md). CaseAssetsTest expects one per entry.
            'photo' => 'huesped-habitacion-4.png',
            'connection' => 'Titular de la reserva de la habitación 4; no se ha podido localizar',
            'motive' => 'Usar un nombre falso para acercarse a Carmen Otero con fines de manipulación afectiva y, probablemente, económica',
            'alibi' => 'No consta; no se puede contactar',
            'public' => [
                'profile' => 'Huésped a cuyo nombre figura la habitación 4. No se ha podido localizar ni contactar.',
                'facts' => [
                    'La habitación 4 estaba reservada a nombre de «Daniel Rivas».',
                    'Pidió que le dejaran la llave en el casillero de recepción para una llegada tardía.',
                    'No se ha podido localizar ni contactar.',
                ],
            ],
            // Can be accused, cannot be questioned: nobody can reach him. Kept
            // off the interrogation board so the table does not spend
            // questions on silence. His .md is only read by the epilogue.
            'interrogable' => false,
        ],
        'ramon-aguilera' => [
            'name' => 'Ramón Aguilera',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/ramon-aguilera.md',
            'photo' => 'ramon-aguilera.png',
            'connection' => 'Huésped; viajante de comercio hospedado esa semana por trabajo',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '50 años',
                'profile' => 'Viajante de comercio hospedado en el hotel esa semana por trabajo. Tranquilo, cordial y cansado de viaje.',
                'facts' => [
                    'Su habitación está en el mismo pasillo que la 4.',
                    'Se hospeda en el hotel por trabajo.',
                ],
            ],
            'accusable' => false,
        ],
        'lorena-otero' => [
            'name' => 'Lorena Otero',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/lorena-otero.md',
            'photo' => 'lorena-otero.png',
            'connection' => 'Visitante llegada al hotel días después de los hechos',
            'motive' => null,
            'alibi' => null,
            // Her md only names Carmen (and says who she is) once V6 and A2
            // have been delivered at T+45; the second stage opens with them.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V6', 'A2'], 'note' => 'Con el chat y el mensaje de voz'],
            ]],
            'public' => [
                'age' => '39 años',
                'profile' => 'Visitante que llegó al hotel por su cuenta, días después de los hechos. Tiene material en su teléfono que quiere entregar al expediente.',
                'facts' => [
                    'Llegó al hotel días después de los hechos.',
                    'Conserva mensajes y fotografías en su teléfono.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-reservas' => [
            'name' => 'Sistema de reservas / recepción externa',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-reservas.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-control-buque").
            'photo' => 'sistema-reservas.png',
            'connection' => 'Reservas, check-in, pagos y llamadas entrantes del hotel',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Registro administrativo del hotel: reservas, check-in, pagos y llamadas entrantes, sin interpretar nada.',
                'facts' => [
                    'Guarda la ficha de reserva de la habitación 4.',
                    'No puede decir quién ocupó físicamente cada habitación.',
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
        'culprit_slug' => 'huesped-habitacion-4',

        // The culprit cannot be questioned, so the winners' epilogue is told by
        // Mercedes: she took both calls and left the key out, and she can be
        // questioned (the alias cannot).
        'epilogue_speaker' => 'mercedes-quintanilla',

        'headline' => 'Daniel Rivas nunca existió como persona: fue el nombre que un hombre usó para acercarse a Carmen Otero. La persona realmente desaparecida es Carmen, y los testigos, todos sinceros, describieron a personas distintas bajo un mismo nombre.',

        'motive' => 'Usar un nombre falso para acercarse a Carmen Otero mediante una relación a distancia iniciada en internet, con fines de manipulación afectiva y, probablemente, económica, aprovechando la discreción de un hotel rural fuera de temporada.',

        'method' => 'El hombre que se hizo llamar «Daniel Rivas» (su nombre real no consta en ningún registro) reservó por teléfono la habitación 4 con el nombre «Daniel Rivas», pidió que le dejaran la llave en el casillero de recepción y nunca completó el check-in. Cenó con Carmen una noche pagando en efectivo y dejó en la habitación una bolsa con objetos genéricos para que el cuarto pareciera ocupado. Mientras tanto, un técnico confundió con él a otro huésped real, Ramón, generando un segundo testimonio sincero pero de otra persona. Después se marchó del hotel con Carmen.',

        'key_evidence' => [
            'El registro de reservas: el check-in de «Daniel Rivas» nunca se completó, no hay documento escaneado y el cargo final no se procesó.',
            'La ficha comparativa: la descripción del mesero y del conductor (alto, delgado, cabello oscuro) es incompatible con la que el técnico da de la persona con la que habló (robusta, con gafas, canosa).',
            'El recibo del restaurante: una cena real para dos, pagada en efectivo, sin rastro bancario.',
            'La bolsa de la habitación 4: ropa genérica, sin ninguna pertenencia personal que identifique a nadie.',
            'El chat y el mensaje de voz de Carmen: ella sí estuvo allí y la relación era real, él evita dar detalles sobre quién es, y es ella, no «Daniel», quien dejó de dar señales.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'mercedes-quintanilla' => 'Solo habló por teléfono con quien reservó y nunca lo vio ni le pidió un documento: confió en una reserva normal y dejó la llave en el casillero, como hace con cualquier llegada tardía. Mintió diciendo que el negocio iba bien porque está pensando en vender el hotel, un secreto sin relación con Carmen ni con el engaño.',

            'pablo-andrade' => 'Vio al hombre real y lo describió con precisión: alto, delgado, cabello oscuro corto, sin barba, chaqueta clara. Su nerviosismo venía de otra cosa: había tomado pequeñas cantidades de la caja del restaurante. Una falta real, pero ajena a la cena y a la desaparición de Carmen.',

            'german-figueroa' => 'Confundió con Daniel a otro huésped real, Ramón, porque en recepción le dijeron que «el huésped de la 4» le podía abrir. No tuvo ningún trato con el hombre del engaño. Evitaba hablar de sus horarios porque hace reparaciones sin declarar impuestos.',

            'olga-reyes' => 'Solo oyó, a través de la pared, una conversación tensa sobre dinero; nunca vio a nadie y no describe a nadie. Su evasiva sobre por qué viajaba sola ocultaba un reencuentro romántico, sin relación alguna con el caso.',

            'ivan-caceres' => 'Dejó al hombre real en la puerta y lo describió igual que el mesero, lo que refuerza que existió. Su evasiva venía de un servicio de taxi sin autorización, una irregularidad ajena a la desaparición de Carmen.',

            'ramon-aguilera' => 'Es otro huésped real, viajante de comercio, al que el técnico confundió con Daniel en el pasillo. Nunca dijo ser Daniel, no ocupó la habitación 4 y no tiene motivo ni relación alguna con Carmen: simplemente no corrigió un malentendido sin importancia.',

            'lorena-otero' => 'Es la hermana de Carmen, no una sospechosa: su aporte fue el chat, la foto y el último mensaje de voz de Carmen. Llegó al hotel después de los hechos, buscando a su hermana, y no tuvo contacto con el hombre.',

            'sistema-reservas' => 'Es un sistema administrativo de registro, no una persona: no puede haber intervenido. Su función fue confirmar, sin interpretar, que la reserva existía pero que el check-in nunca se completó y que el cargo final no se procesó. No puede decir quién llamó ni quién ocupó la habitación.',
        ],

        // The doc has no confession from the culprit (the "audio revelador" is
        // Carmen's voice message, A2, delivered in the timeline), so there is
        // deliberately NO confession_script: the confession ending is not
        // offered for this case. The voice is declared for completeness only.
        'confession_voice' => 'Orus',
    ],

    /*
    |--------------------------------------------------------------------------
    | Accusation form and rubric
    |--------------------------------------------------------------------------
    |
    | The doc scores check-in 20 / incompatible descriptions 25 / Carmen 30 /
    | how the identity was built 25 and has no separate culprit or motive
    | component. Re-balanced to fit the engine's form: culprit 10, method
    | (identity construction) 20, motive 10, Carmen 25, descriptions 20,
    | records 15.
    |
    */

    'accusation' => [
        'culprit_points' => 10,
        'weapon' => [
            'label' => '¿Cómo se construyó la identidad de «Daniel Rivas»?',
            'hint' => 'Describe, paso a paso, cómo ese nombre llegó a «ocupar» la habitación 4 sin que nadie comprobara quién era.',
            'points' => 20,
            'guidance' => 'Basta con la reserva telefónica y la llave en el casillero sin check-in, la cena pagada en efectivo y la habitación «decorada» con una bolsa genérica. No exigir el detalle de cada llamada.',
        ],
        'motive' => [
            'label' => '¿Para qué se usó ese nombre?',
            'hint' => 'Explica qué buscaba quien se hizo llamar así.',
            'points' => 10,
            'guidance' => 'Acercarse a Carmen con un nombre falso para manipularla afectivamente (y, probablemente, económicamente). Puntos parciales si solo menciona el engaño a Carmen sin el fin.',
        ],
        'questions' => [
            [
                'key' => 'desaparecida_real',
                'label' => '¿Quién desapareció realmente?',
                'hint' => 'Nombra a la persona que de verdad falta y explica por qué se denunció otro nombre.',
                'answer' => 'Carmen Otero. Se denunció la desaparición de «Daniel Rivas» porque la habitación 4 quedó con un equipaje sin reclamar, pero quien dejó de dar señales fue Carmen, que se marchó del hotel con el hombre del alias.',
                'points' => 25,
                'guidance' => 'Cero si sostiene que el desaparecido es Daniel Rivas como persona real. Puntos completos con nombrar a Carmen; la explicación de la denuncia es un plus.',
            ],
            [
                'key' => 'testimonios',
                'label' => '¿Por qué los testigos no describen igual a «Daniel»?',
                'hint' => 'Explica qué vio realmente cada testigo y a quién.',
                'answer' => 'Porque hablaron de personas distintas: Pablo e Iván describen al hombre real (alto, delgado, cabello oscuro), mientras que Germán habló en el pasillo con otro huésped, Ramón Aguilera (robusto, con gafas, canoso), al que confundió con el de la 4. Nadie mintió: fue una confusión sincera bajo un mismo nombre.',
                'points' => 20,
                'guidance' => 'Cero si trata a «Daniel» como una sola persona real y consistente (gemelos, disfraces o un testigo mintiendo). Basta con identificar que Germán habló con Ramón.',
            ],
            [
                'key' => 'registros',
                'label' => '¿Qué revelan los registros del hotel sobre la estancia de «Daniel Rivas»?',
                'hint' => 'Fíjate en lo que el sistema de reservas sí y no registró.',
                'answer' => 'Que el check-in nunca se completó: no hay documento de identidad escaneado y el cargo final de la tarjeta no se procesó. La reserva existió, pero nadie verificó jamás quién la ocupaba.',
                'points' => 15,
                'guidance' => 'Lo esencial es el check-in nunca completado; el documento ausente y el cargo no procesado son complementarios.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+10/T+22/T+34/T+45/T+55 delivery
    | plan into trigger_offset_minutes (T+0 -> 1, same convention as the other
    | cases). V4, V7, V9, V10, V11 and A1 were missing from the doc's plan and
    | are placed here (see the docblock).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'El huésped que nunca llegó — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V3', 'V7'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — la llamada de confirmación de la reserva',

            'audio_file' => 'a1-llamada-confirmacion.mp3',
            'audio_scene' => 'Llamada telefónica corriente a la recepción de un hotel rural pequeño: calidad de línea de teléfono, sin música y un leve ambiente de recepción tranquila al fondo.',
            'audio_context' => 'Un hombre adulto, de voz cálida y algo apresurada, confirma una reserva y pide que le dejen la llave en un casillero para llegar tarde. Una mujer adulta de voz profesional y amable acepta sin problema. Todo suena normal y cordial; nada debe sonar sospechoso en una primera escucha. Duración: 12–15 segundos.',
            'audio_speaker' => 'Speaker 1 - Hombre que llama / Speaker 2 - Mercedes Quintanilla (recepción)',
            'audio_voice' => 'Orus',
            'audio_script' => <<<'TXT'
                Buenas tardes, llamo para confirmar una reserva a nombre de Daniel Rivas para este fin de semana. Voy a llegar bastante tarde, ¿sería posible dejar la llave en el casillero de la entrada? No hace falta que nadie me espere despierto.

                Sin problema, señor Rivas, se la dejamos lista.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 10,
            'title' => 'Expansión — lo que dicen los registros',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V5', 'V10'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 22,
            'title' => 'Fragmento privado — la versión del mesero',
            'source_file' => 'pista-privada-mesero.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V8 fragments must not satisfy
            // any confrontation gate on their own (the complete V8 does).
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 22,
            'title' => 'Fragmento privado — la versión del técnico',
            'source_file' => 'pista-privada-tecnico.md',
            'delivery_mode' => 'random_player',
            // See above: no evidence_codes for partial private fragments.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 34,
            'title' => 'Complicación — ¿la misma persona? Se abren las confrontaciones',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V8', 'V4', 'V9'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 45,
            'title' => 'Giro — la persona que de verdad falta',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V6', 'V11'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 45,
            'title' => 'Audio — el último mensaje de voz de Carmen',

            'audio_file' => 'a2-mensaje-voz-carmen.mp3',
            'audio_scene' => 'Mensaje de voz de móvil grabado en un ambiente cálido y cotidiano, con un murmullo suave de fondo; sin música.',
            'audio_context' => 'Carmen, una mujer de 35 años, le cuenta a su hermana, emocionada y algo nerviosa de la buena manera, que el encuentro con alguien a quien conoció por internet va muy bien. Tono ilusionado y cálido; ningún indicio de peligro ni de alarma en la voz. Duración: 12–15 segundos.',
            'audio_speaker' => 'Speaker 1 - Carmen Otero',
            'audio_voice' => 'Kore',
            'audio_script' => <<<'TXT'
                Lorena, no vas a creer lo bien que va todo. Es exactamente como me lo imaginaba hablando con él estos meses. Mañana seguimos hablando, ¿sí? Te quiero.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 55,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para saber quién desapareció realmente y quién construyó la identidad de «Daniel Rivas». Queda habilitado el formulario de acusación final: además de a quién acusan, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-huesped-que-nunca-llego/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-hotel.png', 'caption' => 'Plano del hotel'],
            ['file' => 'v7-fachada-hotel.png', 'caption' => 'La fachada del hotel, fuera de temporada'],
            ['file' => 'v3-habitacion-4.png', 'caption' => 'La habitación 4'],
        ],
        'expansion.md' => [
            ['file' => 'v2-registro-reservas.png', 'caption' => 'Ficha de la reserva de la habitación 4'],
            ['file' => 'v5-recibo-restaurante.png', 'caption' => 'Recibo de consumo del restaurante'],
            ['file' => 'v10-registro-llamadas.png', 'caption' => 'Registro de llamadas entrantes del hotel'],
        ],
        'pista-privada-mesero.md' => [
            ['file' => 'v8a-fragmento-mesero.png', 'caption' => 'Fragmento de la ficha de descripciones: la versión del mesero'],
        ],
        'pista-privada-tecnico.md' => [
            ['file' => 'v8b-fragmento-tecnico.png', 'caption' => 'Fragmento de la ficha de descripciones: la versión del técnico'],
        ],
        'complicacion.md' => [
            ['file' => 'v8-ficha-comparativa.png', 'caption' => 'Ficha comparativa de descripciones físicas'],
            ['file' => 'v4-chaqueta-bolsa.png', 'caption' => 'Una chaqueta doblada dentro de la bolsa de la habitación 4'],
            ['file' => 'v9-llave-casillero.png', 'caption' => 'La llave de la habitación 4 en el casillero de recepción'],
        ],
        'giro.md' => [
            ['file' => 'v6-chat-carmen.png', 'caption' => 'Captura del chat de Carmen con «Daniel»'],
            ['file' => 'v11-foto-carmen.png', 'caption' => 'Carmen, en una fotografía que conserva su hermana'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v2-registro-reservas.png', 'caption' => 'El check-in nunca se completó'],
            ['file' => 'v8-ficha-comparativa.png', 'caption' => 'Dos descripciones incompatibles: dos personas distintas'],
            ['file' => 'v5-recibo-restaurante.png', 'caption' => 'La cena real, pagada en efectivo'],
            ['file' => 'v3-habitacion-4.png', 'caption' => 'Un equipaje sin una sola cosa personal'],
            ['file' => 'v6-chat-carmen.png', 'caption' => 'Quien de verdad estaba en juego: Carmen'],
        ],
    ],
];
