<?php

/**
 * Case manifest: "La Última Postal"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a robbery preceded by a threat, and its central
 * trap is a false premonition. The postal Hermelinda receives seems to
 * photograph the theft of the Estrella de Plata before it happens. It does not:
 * it is a photograph of the workshop's MAQUETA (scale model of the plaza),
 * taken by Osvaldo during a legitimate planning session in which the miniature
 * star had been lifted off its mast. Recognising the maqueta is only the first
 * step; the table must also prove who had access to photograph it at that
 * moment and why. Nothing is supernatural and nothing is coded. The AI rule
 * that matters: nobody ever calls the postal a "premonition" or a "prediction"
 * as if it were real; it is an image whose source has to be identified by
 * comparing images and documents.
 *
 * Reference timetable (single source of truth for every file of this case; all
 * hours that appear in evidence, md files and the manifest derive from it).
 * Days are weekday names only, no calendar dates:
 *   Weeks before: Osvaldo agrees the sale with a private buyer (C1, message).
 *   Same weeks: Hermelinda notices irregularities in the committee's contract
 *     with Osvaldo's supplier and tells Araceli.
 *   LUNES (planning session in the taller de maquetas):
 *     15:00 maqueta set up with the miniature star lifted off its mast
 *     (Rosalinda, Casimiro). 16:00–17:30 planning session (convened:
 *     Rosalinda, Casimiro, Felisa, Wenceslao, Araceli). Osvaldo is NOT
 *     convened: his habitual slot is the Thursday review (10:00).
 *     Taller register: Rosalinda 14:50–18:40 / Casimiro 15:00–18:45 /
 *     Wenceslao 15:55–17:32 / Felisa 15:58–17:36 / Araceli 16:02–17:40 /
 *     Osvaldo 17:02–17:31 ("Consulta"). He photographs the maqueta from the
 *     camera mark on the table at ~17:15 (Casimiro does not give hours).
 *   MARTES: Osvaldo mails the printed photo as an anonymous postal.
 *   MIÉRCOLES: Hermelinda receives it at her office.
 *   Following days: Hermelinda keeps investigating.
 *   SÁBADO (depósito municipal): 18:05 Benjamín enters (closing round);
 *     19:25 he checks shelf B-3, the star is there; 19:30 he leaves (master key
 *     back on the security board at 19:32); 20:35 entry with comité "llave de
 *     montaje n.º 3" (Osvaldo's), alone; 21:05 exit. The star is gone.
 *     Alibis for 20:35–21:05: Nazario in his external taller (night-shift
 *     production register, his signature on a truck delivery at 20:50);
 *     Felisa in a meeting with two lighting providers 19:30–22:00 (receipt
 *     21:48); Wenceslao at the committee office, system session 19:05–22:40 and
 *     entrance camera without exit; Benjamín out of the depósito since 19:30.
 *   DOMINGO 08:10: Benjamín enters and finds the gap (photo V7, shelf B-3).
 *   Llaves de montaje: n.º 1 Felisa, n.º 2 Wenceslao, n.º 3 Osvaldo.
 *   Value of the star in the inventory (V5): US$ 15,000.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text "weapon". The doc's 4-component scoring
 *    (30/20/20/30) maps to culprit_points 30 + weapon ("¿Qué ocurrió
 *    realmente?") 20 + motive 20 + one extra question (`origen_postal`) 30.
 *    The doc's penalty (accusing only for recognising the maqueta, without
 *    access and motive) is encoded in that question's `guidance`.
 *  - Victim: the Estrella de Plata is not a person. Hermelinda (the threatened
 *    one) is the `victim` (label «Amenazada», `incident` overridden so the AI
 *    prompts speak of the theft and the threat, never "la muerte de") AND an
 *    interrogable, `accusable => false` witness with the same portrait file
 *    (hermelinda-casas.png, a single asset).
 *  - Questions: 3–5 per person plus 3 for the five people with confrontations
 *    (the doc's "3 + 2 confrontations / 1 each" is folded into the engine rule).
 *    Osvaldo's two doc confrontations are two confrontation stages (2 + 1).
 *  - The doc's T+0/11/22/33/44/55/70 become offsets 1/11/22/33/44/55/70. The
 *    private maqueta photo (V2, T+11) travels in a `random_player` event with
 *    NO evidence code (so no interrogation stage opens on V2 while only one
 *    player holds it); the public copy arrives in the T+22 desarrollo with the
 *    code V2.
 *  - New text-only codes (same device as DF in punto-de-control): DC (Casimiro's
 *    written declaration, T+44, with V6), DA (Araceli's, T+55), RD (depósito
 *    access log + llaves de montaje control, T+55; the doc's "órdenes de acceso
 *    al depósito") and HS (summary of the four side findings, T+55). The doc has
 *    no hours; the taller register, the depósito log and the alibis were
 *    invented from the doc's facts so every confrontation has a verifiable
 *    anchor, and are fixed in the timetable above.
 *  - Casimiro's "explicit on any direct question at T+44" fail-safe: his key
 *    fact waits for DC (delivered at T+44 with V6), so from that moment any
 *    direct question gets it.
 *  - Rosalinda's doc fact "los accesos incluyen a Osvaldo" is modelled as: she
 *    first names only the convened list; she confirms Osvaldo signed in (not
 *    convened) once V6 is delivered. Her scale/angle confirmation of the postal
 *    waits for V1+V2+V3.
 *  - The replica: the doc says Osvaldo's usual provider "could" make it. The
 *    case never says it was ordered; Nazario says no replica was ever
 *    commissioned from him. C1 deliberately names neither the star nor the
 *    theft nor the provider.
 *  - Nazario/Felisa/Benjamín/Wenceslao confrontations (doc: one each at T+55)
 *    wait for HS (Wenceslao also for C2). The crossed alibis (fail-safe) are a
 *    separate T+63 cierre (coartadas.md).
 *  - Osvaldo's confession (A1) is `solution.confession_script`, generated per
 *    game; it is NOT a timeline event, so the case has no audio/ folder.
 *  - V1 and V2 share angle, scale and background (the same camera mark) and
 *    differ only in light/context; V3 differs subtly but really (stone façade,
 *    five lamp posts vs four, slightly shifted viewpoint). See ASSET_PROMPTS.md.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'La Última Postal',
    'version' => '1.0',
    'code' => 'Expediente — Estrella de Plata, pesebre municipal',
    'authority' => 'Oficina de Patrimonio Municipal',

    'victim' => [
        'name' => 'Hermelinda Casas',
        'label' => 'Amenazada',
        'photo' => 'hermelinda-casas.png',
        'incident' => 'el robo de la Estrella de Plata y la amenaza a {name}',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 3 preguntas por sospechoso en la fase 1 y 2
        // confrontaciones para Osvaldo (1 para los demás). The engine rule is
        // 3–5 base questions per person plus 3 for confrontations.
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
        'tagline' => 'La postal mostraba el robo antes de que ocurriera. Días después, la estrella desapareció de verdad.',

        'description' => <<<'TXT'
            Una inspectora de patrimonio recibe una postal sin remitente: una
            fotografía de la Estrella de Plata del pesebre municipal siendo
            retirada de su soporte, en una plaza navideña. Días después, la
            estrella desaparece del depósito. La imagen parecía mostrar algo que
            todavía no había ocurrido.

            Tu equipo (3 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a nueve personas (cinco sospechosos,
            tres testigos y la diseñadora de la maqueta del taller) durante 70
            minutos, comparando imágenes, agendas y registros de acceso.

            Nada es sobrenatural y nada está cifrado: cada detalle se resuelve
            mirando con atención y probando quién pudo hacerlo. Reconocer de
            dónde viene una imagen es solo el primer paso.
            TXT,

        // Archivo esperado en public/immersion/la-ultima-postal/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-ultima-postal).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'La postal mostraba el robo antes de que ocurriera.',
            'bullets' => [
                '3 a 7 investigadores comparando una postal, una maqueta y una plaza real',
                'Cinco sospechosos, tres testigos y una diseñadora de maquetas a interrogar',
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

        // Mantener en false hasta que las imágenes estén en
        // public/immersion/la-ultima-postal/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 27,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md. Hermelinda is the "victim" of the case (threatened,
    | alive) and also a witness: same portrait file, `accusable => false`.
    |
    */

    'suspects' => [
        'osvaldo-reinares' => [
            'name' => 'Osvaldo Reinares',
            'role' => 'sospechoso',
            'file' => 'suspects/osvaldo-reinares.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V6', 'DC'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['DA', 'C1', 'V7', 'RD'], 'confrontation' => true],
            ]],
            'photo' => 'osvaldo-reinares.png',
            'connection' => 'Miembro del Comité de Decoración, encargado de los contactos con proveedores',
            'motive' => 'Vender la Estrella de Plata original a un comprador privado, sustituyéndola por una réplica, antes de que la investigación de Hermelinda expusiera el acuerdo',
            'alibi' => 'Dice no haber tenido nunca acceso al taller fuera de las reuniones oficiales del comité',
            'public' => [
                'age' => '55 años',
                'profile' => 'Miembro del Comité de Decoración, encargado de los contactos con proveedores. Cordial y respetado en el comité; maneja el contrato con el proveedor habitual.',
                'alibi' => 'Dice que su acceso al taller se limita a las reuniones oficiales del comité.',
                'facts' => [
                    'Maneja el contrato del comité con el proveedor de decoraciones.',
                    'Coordina los pedidos y las entregas de material con los proveedores.',
                ],
            ],
        ],
        'nazario-prieto' => [
            'name' => 'Nazario Prieto',
            'role' => 'sospechoso',
            'file' => 'suspects/nazario-prieto.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['HS'], 'confrontation' => true],
            ]],
            'photo' => 'nazario-prieto.png',
            'connection' => 'Proveedor de decoraciones del comité',
            'motive' => 'Ninguno respecto a la postal ni al robo; subcontrata parte del trabajo a una fábrica de menor calidad sin informar al comité',
            'alibi' => 'En su taller externo la noche del sábado, con registro de producción',
            'public' => [
                'age' => '50 años',
                'profile' => 'Proveedor de decoraciones del Comité de Decoración. Profesional y cordial; se pone a la defensiva cuando se habla de cómo ejecuta sus contratos.',
                'alibi' => 'Dice haber estado en su taller externo la noche del sábado.',
                'facts' => [
                    'Su contrato con el comité es el que Osvaldo coordina.',
                    'Su negocio se beneficia directamente de ese contrato.',
                ],
            ],
        ],
        'felisa-orozco' => [
            'name' => 'Felisa Orozco',
            'role' => 'sospechosa',
            'file' => 'suspects/felisa-orozco.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['HS'], 'confrontation' => true],
            ]],
            'photo' => 'felisa-orozco.png',
            'connection' => 'Presidenta del Comité de Decoración',
            'motive' => 'Ninguno respecto a la postal ni al robo; desvía pequeñas cantidades del presupuesto navideño',
            'alibi' => 'En una reunión con dos proveedores de iluminación la noche del sábado',
            'public' => [
                'age' => '58 años',
                'profile' => 'Presidenta del Comité de Decoración. Ejecutiva y cordial; supervisa el presupuesto y los contratos en general.',
                'alibi' => 'Dice haber estado reunida con proveedores de iluminación la noche del sábado.',
                'facts' => [
                    'Es la máxima autoridad del comité.',
                    'Preside las reuniones y revisa los informes de presupuesto.',
                ],
            ],
        ],
        'benjamin-ustariz' => [
            'name' => 'Benjamín Ustariz',
            'role' => 'sospechoso',
            'file' => 'suspects/benjamin-ustariz.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['RD']],
                ['questions' => 3, 'after' => ['HS'], 'confrontation' => true],
            ]],
            'photo' => 'benjamin-ustariz.png',
            'connection' => 'Encargado del depósito municipal',
            'motive' => 'Ninguno respecto a la postal ni al robo; presta piezas menores del depósito sin autorización a cambio de pagos informales',
            'alibi' => 'Su registro de acceso al depósito no coincide con el momento de la sustracción',
            'public' => [
                'age' => '45 años',
                'profile' => 'Encargado del depósito municipal. Práctico y algo nervioso cuando se habla del control de las piezas.',
                'alibi' => 'Dice que cerró el depósito la tarde del sábado y que descubrió el hueco el domingo a primera hora.',
                'facts' => [
                    'Es responsable de la custodia física de las piezas decorativas, incluida la Estrella de Plata.',
                    'Tiene acceso directo al depósito municipal.',
                ],
            ],
        ],
        'wenceslao-ibarra' => [
            'name' => 'Wenceslao Ibarra',
            'role' => 'sospechoso',
            'file' => 'suspects/wenceslao-ibarra.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['HS', 'C2'], 'confrontation' => true],
            ]],
            'photo' => 'wenceslao-ibarra.png',
            'connection' => 'Secretario y tesorero del comité',
            'motive' => 'Ninguno respecto a la postal ni al robo; acepta comisiones no declaradas de proveedores distintos a Nazario',
            'alibi' => 'En la oficina del comité revisando otros contratos la noche del sábado',
            'public' => [
                'age' => '52 años',
                'profile' => 'Secretario y tesorero del comité. Meticuloso y cordial; se pone a la defensiva si se cuestiona su gestión de proveedores.',
                'alibi' => 'Dice haber estado en la oficina del comité revisando contratos la noche del sábado.',
                'facts' => [
                    'Maneja las finanzas del comité y la relación con distintos proveedores.',
                    'Lleva las actas y las cuentas del comité.',
                ],
            ],
        ],
        'hermelinda-casas' => [
            'name' => 'Hermelinda Casas',
            'role' => 'testigo (amenazada; no es sospechosa)',
            'file' => 'suspects/hermelinda-casas.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V5']],
            ]],
            'photo' => 'hermelinda-casas.png',
            'connection' => 'Inspectora de patrimonio municipal; recibió la postal',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '50 años',
                'profile' => 'Inspectora de patrimonio municipal. Formal, precisa y serena; recibió la postal y no la tomó como una broma.',
                'facts' => [
                    'Recibió la postal sin remitente en su oficina.',
                    'Investigaba el contrato del comité con su proveedor de decoraciones.',
                ],
            ],
            'accusable' => false,
        ],
        'casimiro-pena' => [
            'name' => 'Casimiro Peña',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/casimiro-pena.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['DC']],
            ]],
            'photo' => 'casimiro-pena.png',
            'connection' => 'Ayudante del taller de maquetas',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '30 años',
                'profile' => 'Ayudante del taller de maquetas. Joven, directo y algo tímido; habla de las piezas con cariño.',
                'facts' => [
                    'Trabaja con Rosalinda en el taller de maquetas.',
                    'Estuvo en el taller durante la sesión de planificación del lunes.',
                ],
            ],
            'accusable' => false,
        ],
        'araceli-fuentes' => [
            'name' => 'Araceli Fuentes',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/araceli-fuentes.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['DA']],
            ]],
            'photo' => 'araceli-fuentes.png',
            'connection' => 'Miembro del Comité de Decoración',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '44 años',
                'profile' => 'Miembro del Comité de Decoración. Casual y cercana; trabaja a menudo con Hermelinda.',
                'facts' => [
                    'Asistió a la sesión de planificación del lunes en el taller.',
                    'Habla con frecuencia con Hermelinda sobre su trabajo.',
                ],
            ],
            'accusable' => false,
        ],
        'rosalinda-antunez' => [
            'name' => 'Rosalinda Antúnez',
            'role' => 'testigo técnica (no es sospechosa)',
            'file' => 'suspects/rosalinda-antunez.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['V1', 'V2', 'V3']],
                ['questions' => 1, 'after' => ['V6']],
            ]],
            'photo' => 'rosalinda-antunez.png',
            'connection' => 'Encargada del diseño de la maqueta del taller',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '48 años',
                'profile' => 'Encargada del diseño de la maqueta de la plaza. Metódica y orgullosa de su trabajo; explica con paciencia a quien no es del oficio.',
                'facts' => [
                    'Diseñó la maqueta a escala de la plaza navideña.',
                    'Organizó la sesión de planificación del lunes en el taller.',
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
        'culprit_slug' => 'osvaldo-reinares',

        'headline' => 'La postal no predijo nada: era una fotografía de la maqueta del taller, tomada por Osvaldo para intimidar a Hermelinda, y cuando no funcionó, él mismo robó la Estrella de Plata.',

        'motive' => 'Vender la Estrella de Plata original a un comprador privado, sustituyéndola por una réplica, antes de que la investigación de Hermelinda sobre el contrato del comité expusiera el acuerdo.',

        'method' => 'Fotografió la maqueta del taller con su teléfono durante la sesión de planificación del lunes, cuando la réplica miniatura de la estrella estaba levantada de su mástil, y envió la imagen impresa como postal anónima. Al ver que Hermelinda no se detenía, adelantó sus planes: el sábado entró al depósito con la llave de montaje n.º 3 y se llevó la Estrella de Plata real.',

        'key_evidence' => [
            'La postal comparada con la fotografía de la maqueta y la de la plaza real: encuadre, escala y fondo de la maqueta; la plaza real tiene fachada de piedra y una farola más.',
            'El registro del taller: Osvaldo firmó una consulta el lunes, de 17:02 a 17:31, sin estar convocado a la sesión de planificación.',
            'La declaración de Casimiro: lo vio fotografiando la maqueta con su propio teléfono.',
            'La declaración de Araceli: Hermelinda ya sospechaba del contrato de Osvaldo con su proveedor antes de recibir la postal.',
            'El registro de accesos del depósito: entrada con la llave de montaje n.º 3, la de Osvaldo, de 20:35 a 21:05 del sábado.',
            'El intercambio de mensajes con un contacto de inicial única: «la pieza estará lista para el intercambio antes de fin de temporada».',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS person could not be the
         * culprit. One entry per roster entry that is not the culprit,
         * including the witnesses and the technical witness.
         */
        'exonerations' => [
            'nazario-prieto' => 'Sí subcontrataba parte del trabajo del comité a una fábrica de menor calidad sin informar, una falta real pero ajena a la postal y al robo. Estuvo en su taller externo la noche del sábado: el registro de producción del turno de la noche y su firma de recepción de un camión a las 20:50 lo ubican allí cuando se abrió el depósito.',

            'felisa-orozco' => 'Sí desviaba pequeñas cantidades del presupuesto navideño, un desvío menor y sin relación con la estrella. Estuvo en una reunión con dos proveedores de iluminación de 19:30 a 22:00 la noche del sábado, que ambos confirman, y no estuvo en la sesión ni en el depósito con nada que la señale.',

            'benjamin-ustariz' => 'Sí prestaba piezas decorativas menores del depósito sin autorización a cambio de pagos informales, pero nunca la Estrella de Plata. Su registro de acceso es de 18:05 a 19:30 del sábado, con la llave maestra devuelta al tablero a las 19:32; la entrada de las 20:35 se hizo con la llave de montaje n.º 3, que no es suya.',

            'wenceslao-ibarra' => 'Sí aceptaba comisiones no declaradas de otros proveedores, un conflicto de interés ajeno al caso. Estuvo en la oficina del comité la noche del sábado: su sesión del sistema (19:05 a 22:40) y la cámara de la entrada lo ubican allí cuando se abrió el depósito.',

            'hermelinda-casas' => 'Es la persona amenazada, no una sospechosa: recibió la postal e investigaba el contrato del comité, y no tiene motivo ni secreto relacionado con el robo. Su versión coincide con la de Araceli.',

            'casimiro-pena' => 'Es un testigo, no un sospechoso: solo contó lo que vio en el taller el lunes, incluido que Osvaldo fotografiaba la maqueta. No tuvo motivo ni oportunidad respecto a la estrella.',

            'araceli-fuentes' => 'Es una testigo, no una sospechosa: solo confirmó que Hermelinda ya sospechaba del contrato de Osvaldo con su proveedor antes de recibir la postal. Estuvo en la sesión, convocada, y no tuvo motivo alguno.',

            'rosalinda-antunez' => 'Es una testigo técnica, no una sospechosa: solo confirmó cómo está construida la maqueta y quiénes tuvieron acceso a la sesión de planificación. No tuvo motivo alguno ni interés en la estrella.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio (design A1). Written to be played as-is:
        // the model only reads it aloud and lightly weaves in 1-2 real
        // questions from the table, it never composes it.
        'confession_script' => <<<'TXT'
            Solo quería que Hermelinda dejara de hacer preguntas. Pensé que la foto bastaría
            para asustarla sin que nadie saliera lastimado. Cuando vi que no se iba a detener,
            adelanté todo y me llevé la pieza antes de que fuera demasiado tarde. Sé que no hay
            forma de hacer que esto suene mejor de lo que es.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió realmente?',
            'hint' => 'Cuenta qué se hizo con la postal, qué hizo el responsable cuando ella no se detuvo y dónde está la estrella.',
            'points' => 20,
            'guidance' => 'Lo esencial: el responsable fotografió la maqueta del taller, usó la imagen como amenaza a Hermelinda y, al no detenerse ella, robó la Estrella de Plata real del depósito municipal. No exigir aquí cómo se demuestra el origen de la postal: se pregunta aparte.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué buscaba el responsable con la amenaza y con el robo, y qué lo amenazaba a él.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'origen_postal',
                'label' => '¿Cómo se demuestra que la postal es la maqueta y que fue Osvaldo quien la fotografió?',
                'hint' => 'Compara la postal con la maqueta y con la plaza real, y explica cómo se ubica al responsable en el momento de la foto.',
                'answer' => 'El encuadre y la escala de la postal coinciden con los de la maqueta del taller, no con los de la plaza real, que tiene la fachada de piedra, más detalle y una farola más. El registro del taller ubica a Osvaldo el lunes de 17:02 a 17:31, sin estar convocado a la sesión de planificación, y Casimiro lo vio fotografiando la maqueta con su propio teléfono.',
                'points' => 30,
                'guidance' => 'Puntos completos solo si hace las dos cosas: compara la postal con la maqueta (encuadre y escala, frente a la plaza real) Y ubica a Osvaldo en el taller en ese momento (registro del taller y/o testimonio de Casimiro). Cero si solo dice que la postal es la maqueta sin ligarla al acceso de Osvaldo, o si trata la postal como una premonición real.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+11/T+22/T+33/T+44/T+55/T+70
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as
    | the other cases). The extra T+63 cierre carries the doc's crossed-alibi
    | fail-safe.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Última Postal — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 11,
            'title' => 'Expansión — el comité, la plaza y el taller',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 11,
            'title' => 'Pista privada — una fotografía del taller',
            'source_file' => 'pista-privada-maqueta.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: V2 is delivered to ONE player here
            // and to everybody (with its code) in the T+22 desarrollo.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 22,
            'title' => 'Desarrollo — la maqueta, la plaza real y el valor de la pieza',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            // V2 = public copy of the privately delivered maqueta photo.
            'evidence_codes' => ['V2', 'V3', 'V5'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 33,
            'title' => 'Complicación — la agenda del taller',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 44,
            'title' => 'Giro — quién estuvo en el taller',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // DC = Casimiro's written declaration, the body of giro.md. It has no
            // image, so it gets its own code (same device as DF in
            // punto-de-control).
            'evidence_codes' => ['V6', 'DC'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Cierre — el depósito y el motivo',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            // DA = Araceli's written declaration; RD = depósito access log and
            // llaves de montaje control. Both are text inside cierre.md.
            'evidence_codes' => ['V7', 'C1', 'DA', 'RD'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Cierre — los hilos secundarios',
            'source_file' => 'subtramas.md',
            'delivery_mode' => 'all',
            // HS = summary of the four side findings (text), inside subtramas.md.
            'evidence_codes' => ['C2', 'HS'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 63,
            'title' => 'Cierre — coartadas cruzadas',
            'source_file' => 'coartadas.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 70,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, qué ocurrió y por qué, el formulario hace una pregunta más: cómo se demuestra que la postal es la maqueta y quién la fotografió. Reconocer la maqueta no basta: hay que probar quién tuvo acceso a ella en ese momento.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-ultima-postal/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-postal-recibida.png', 'caption' => 'La postal recibida por Hermelinda'],
        ],
        'pista-privada-maqueta.md' => [
            ['file' => 'v2-maqueta-del-pueblo.png', 'caption' => 'Fotografía del archivo del taller de maquetas'],
        ],
        'desarrollo.md' => [
            ['file' => 'v2-maqueta-del-pueblo.png', 'caption' => 'La maqueta del taller (copia para toda la mesa)'],
            ['file' => 'v3-plaza-real.png', 'caption' => 'La plaza real, con la estrella en su mástil'],
            ['file' => 'v5-inventario-decorativo.png', 'caption' => 'Inventario de ornamentación patrimonial: la Estrella de Plata'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-plano-de-montaje.png', 'caption' => 'Agenda semanal del taller de maquetas'],
        ],
        'giro.md' => [
            ['file' => 'v6-registro-del-taller.png', 'caption' => 'Registro de entradas y salidas del taller, lunes'],
        ],
        'cierre.md' => [
            ['file' => 'v7-deposito-estanteria.png', 'caption' => 'Estantería B-3 del depósito municipal, el domingo'],
            ['file' => 'c1-mensaje-osvaldo.png', 'caption' => 'Intercambio de mensajes recuperado durante la investigación'],
        ],
        'subtramas.md' => [
            ['file' => 'c2-mensaje-wenceslao.png', 'caption' => 'Intercambio de mensajes entre Wenceslao Ibarra y un proveedor'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v1-postal-recibida.png', 'caption' => 'La postal'],
            ['file' => 'v2-maqueta-del-pueblo.png', 'caption' => 'La maqueta: mismo encuadre y escala'],
            ['file' => 'v3-plaza-real.png', 'caption' => 'La plaza real: otra fachada, otra farola'],
            ['file' => 'v6-registro-del-taller.png', 'caption' => 'Osvaldo estuvo en el taller, sin estar convocado'],
        ],
    ],
];
