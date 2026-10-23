<?php

/**
 * Case manifest: "La Cera de los Invitados"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: a homicide case whose method is NOT the wax. Leonor Aguirre
 * died from a push and a fall against a work table; the wax is only the trace
 * of the cover-up Rodolfo did afterwards. The table reads the scene with three
 * simple, chemistry-free notes (the work sheet V04): a freshly poured candle is
 * smooth; one that burned uninterrupted has an even pool; one that was put out
 * and re-poured shows a seam between two layers. The cover-up has THREE
 * independent supports: (1) the seam in the central candle (V03 + V04),
 * (2) the closing-log window that leaves Rodolfo alone in the altar area (V08 +
 * V05 + Walter), (3) the displaced support (V02 vs V09, with V06 and V01). The
 * wax is never the weapon and nothing here is a recipe for harm.
 *
 * Reference timetable (single source of truth for every file of this case; all
 * hours that appear in evidence, md files and the manifest derive from it; the
 * persona files never quote hours, witnesses recall places and events):
 *   Day of the ceremony. 11:20 Walter (Casa de Insumos) delivers molds M-03,
 *   M-08, M-12 and the large M-20 plus 40 kg of block wax; Rodolfo signs.
 *   V05 (molds log, afternoon): 14:30 Esteban M-12 (personalised candles, lot 1) /
 *   15:15 Leonor M-08 (smaller altar candles) / 15:50 Rodolfo M-20 (central altar
 *   candle) / 16:40 Esteban M-12 (lot 2) / 17:15 Leonor M-03 (closing gifts).
 *   17:05 Leonor records A1 on her phone; ~17:40 she tells Fermina she found
 *   "something serious" and will talk to that person when the ceremony ends.
 *   18:40 Nazario photographs the finished altar (V02): six numbered supports
 *   (brass tags 1-6) in a ring around the central candle.
 *   19:30 guests arrive; 20:00 opening; 20:15 the guests' candles and the central
 *   candle are lit together (the central one must burn all night, untouched).
 *   21:30 official end of the ceremony act; the velada (service, music) goes on in
 *   the main hall until ~23:00 and the altar area is closed to the public with a
 *   cord. ~21:40 Araceli sees Rodolfo cross to the back corridor (prep area); a
 *   few minutes later, Leonor. ~21:45-21:58 confrontation; Leonor dies ~21:58.
 *   V08 (closing log): 22:05-22:45 Rodolfo, altar area, alone (the only person
 *   assigned there); 21:45-22:45 Nazario + Esteban, main hall teardown; 21:30-
 *   23:00 Delfina, service (bar/kitchen); 21:30-22:50 Perpetua, administrative
 *   office (accounting validations 22:05 and 22:40). Rodolfo does the cover-up
 *   inside his 22:05-22:45 window (candle snuffed, re-poured with wax from his
 *   own afternoon batch, relit; support 5 taken to the side table, support 4 slid
 *   toward the gap), then bids farewell to the guests. ~23:15 Delfina sends
 *   Nazario for the gift boxes Leonor had to hand out; he finds her.
 *   V07 (messages): sent on a Monday, days before; the misaddressed one comes
 *   from Rodolfo ("Rodolfo C.") to Leonor's contact by mistake.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 30/20/20/30 point scoring is
 *    mapped to culprit_points 30 + weapon ("qué ocurrió realmente") 20 + motive
 *    20 + one extra question about the three independent supports of the
 *    cover-up (30). The doc's penalty for accusing from a single material piece
 *    is not an engine feature: it lives in that question's `guidance` (partial
 *    credit only if the answer does not rest on one support alone), and the
 *    T+90 unlock text asks for the altar-photo vs supports comparison.
 *  - Questions: 5 per person at most, plus 3 for those with confrontations (8),
 *    opened by `interrogation.stages` according to the evidence delivered. The
 *    doc's "4 + 3 for Rodolfo, 4 + 1 for the rest" is folded into that rule:
 *    Rodolfo has three one-question confrontation stages (C1 after V03+V04+V06,
 *    C2 after V08+V09, C3 after V07+DT, the doc's T+52/T+65/T+78), and each of
 *    the five secondary suspects has one 3-question confrontation stage that
 *    waits for its own antecedent code (SM/SE/SD/SP/SI, all delivered at T+78
 *    in testimonios.md, where the doc says their secrets arrive).
 *  - CONFRONTATION TIMING. The doc says Leonor confronted Rodolfo "durante la
 *    ceremonia", while also placing the cleaning-log window "after the official
 *    closing" and A1 says "cuando termine la ceremonia". Resolved by treating the
 *    ceremony ACT as ending at 21:30 and the confrontation as happening right
 *    after (during the velada). That keeps A1, Fermina, V08 and the cover-up
 *    window mutually consistent. The doc's "inmediatamente después" for the
 *    cover-up is therefore "within the closing window, minutes after".
 *  - Why Rodolfo touched the altar is left as the doc says it: so that the
 *    altar looked untouched and nobody noticed how long Leonor had been away
 *    (his confession script says "solo pensé en que nadie notara cuánto tiempo
 *    había pasado"). Leonor tended the central candle during the ceremony, so
 *    her long absence would have shown there.
 *  - A1 is a timeline audio event at T+78 (the json places it there; the md
 *    omits it). It is NOT the confession: the confession of Rodolfo (A2) is
 *    solution.confession_script, generated per game. Final type: combinacion.
 *  - Support geometry (the doc leaves V06/V09 loose): V02 shows SIX supports in a
 *    ring with brass tags 1-6; V01 marks their positions. After: V09 shows FIVE
 *    at the altar (tags 1, 2, 3, 4, 6) with number 4 slid toward the gap; V06
 *    shows number 5 among the leftover props on the side table. The displaced
 *    support is thus deducible with V02 vs V09 and cross-checked with V06 + V01.
 *  - Private clues: V03 (T+26, with V04 public) and V07 (T+39) are
 *    random_player, each a complete piece with its own evidence code. Their
 *    public copies arrive later (V03 inside complicacion.md at T+39; V07 in its
 *    own event at T+52) and carry no code of their own: the code is the
 *    private event's, so no critical datum depends on a single private tray.
 *  - Fermina's key fact (it was Rodolfo, and why) waits for DT (the written
 *    declarations of T+78); before DT she only says "algo grave sobre alguien
 *    del taller", which is what the doc's witness fact says. Araceli's sighting
 *    of Rodolfo and then Leonor also waits for DT; before it she is true and
 *    vague. Nazario's impression about the candle waits for V03.
 *  - The doc names the forensic seam "visible al examinarla de cerca"; V03 is the
 *    close-up and is never highlighted. No wax chemistry anywhere.
 *  - V07 shows the sender of the misaddressed message as "Rodolfo C." (the
 *    doc only says "enviado por error a Leonor") so that the table can tie the
 *    "acuerdo con la otra marca" to him at T+39; no company name appears.
 *  - Alibi sources the doc left open: Delfina (service comanda + closing log) and
 *    Perpetua (accounting validations + closing log) are listed in the T+78
 *    "coartadas cruzadas"; Esteban's doc alibi "montaje" is the post-ceremony
 *    teardown with Nazario. Walter is a non-accusable neutral source.
 *  - Nazario is who finds Leonor (the doc says only "se descubre el cuerpo").
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()). The only
 * recording in the timeline is A1 (Leonor's voice note); the confession is
 * generated per game. `audio` is in mechanics like the other cases.
 */
return [

    'name' => 'La Cera de los Invitados',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Atelier de Cera',
    'authority' => 'Comité de Investigación del Atelier de Cera',

    'victim' => [
        'name' => 'Leonor Aguirre',
        'label' => 'Víctima',
        'photo' => 'leonor-aguirre.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 4 questions per suspect plus 3 confrontations
        // for Rodolfo and 1 for the rest. The engine takes one flat ceiling per
        // case plus per-person stages, same precedent as the other cases.
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
        'tagline' => 'El altar estaba intacto. La cera, no.',

        'description' => <<<'TXT'
            Atelier de Cera: un taller boutique de velas y su ceremonia privada
            de Halloween. Al terminar la velada, la dueña del taller aparece
            muerta en el área de preparación de moldes. El altar, a primera
            vista, está intacto. Pero la cera conserva un rastro de lo que
            ocurrió esa noche, y se lee sin química, solo mirando.

            Tu equipo (4 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, tres testigos y
            al proveedor de materiales del taller, durante 90 minutos,
            cruzando fotografías del altar, registros del taller y
            testimonios.

            Todos tenían algo que ocultar, pero no todos tenían que ver con la
            muerte. Separar cada secreto del resto es lo que lleva hasta quien
            la causó.
            TXT,

        // Archivo esperado en public/immersion/la-cera-de-los-invitados/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/la-cera-de-los-invitados).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'El altar estaba intacto. La cera, no.',
            'bullets' => [
                '4 a 8 investigadores leyendo la escena por el estado de la cera, sin química',
                'Seis sospechosos, tres testigos y un proveedor a quienes interrogar',
                '90 minutos con el expediente llegando en tiempo real',
            ],
            'cta' => 'Quiero este caso',
        ],

        'difficulty' => 'hard',
        'duration_minutes' => 90,
        'min_players' => 4,
        'max_players' => 8,

        'price_amount' => 69900,
        'currency' => 'COP',

        // Mantener en false hasta que las imágenes y el audio estén en
        // public/immersion/la-cera-de-los-invitados/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The three witnesses and the supplier (Walter) are modeled exactly like a
    | suspect entry. Leonor, the dead victim, is not interrogable.
    |
    */

    'suspects' => [
        'rodolfo-casares' => [
            'name' => 'Rodolfo Casares',
            'role' => 'sospechoso',
            'file' => 'suspects/rodolfo-casares.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 1, 'after' => ['V03', 'V04', 'V06'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V08', 'V09'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V07', 'DT'], 'confrontation' => true],
            ]],
            'photo' => 'rodolfo-casares.png',
            'connection' => 'Socio minoritario del Atelier de Cera y responsable técnico del taller',
            'motive' => 'Proteger la venta secreta de la fórmula exclusiva de cera del taller a una marca competidora, que Leonor iba a exponer',
            'alibi' => 'Dice haber atendido a los invitados en el salón principal durante toda la noche; sin coartada sólida',
            'public' => [
                'age' => '42 años',
                'profile' => 'Socio minoritario del Atelier de Cera y responsable técnico del taller. Cordial y meticuloso; muy respetado por su oficio.',
                'alibi' => 'Dice haber estado toda la noche atendiendo a los invitados en el salón principal.',
                'facts' => [
                    'Prepara las velas de gran formato y cuida las fórmulas del taller.',
                    'Es socio de Leonor desde hace varios años y ella confiaba en su criterio técnico.',
                ],
            ],
        ],
        'micaela-aguirre' => [
            'name' => 'Micaela Aguirre',
            'role' => 'sospechosa',
            'file' => 'suspects/micaela-aguirre.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['SM'], 'confrontation' => true],
            ]],
            'photo' => 'micaela-aguirre.png',
            'connection' => 'Hija de Leonor y co-heredera del taller',
            'motive' => 'Ninguno respecto a la muerte; negociaba en secreto vender su parte del taller a una cadena más grande',
            'alibi' => 'En el salón principal atendiendo a los invitados toda la velada; la confirma Araceli',
            'public' => [
                'age' => '28 años',
                'profile' => 'Hija de Leonor y co-heredera del Atelier de Cera. Afectuosa con el recuerdo de su madre; defensiva cuando se habla del futuro del taller.',
                'alibi' => 'Dice haber atendido a los invitados en el salón principal durante toda la velada.',
                'facts' => [
                    'Heredaría el taller si su madre faltara.',
                    'Era la invitada de honor de la ceremonia.',
                ],
            ],
        ],
        'esteban-riofrio' => [
            'name' => 'Esteban Riofrío',
            'role' => 'sospechoso',
            'file' => 'suspects/esteban-riofrio.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['SE'], 'confrontation' => true],
            ]],
            'photo' => 'esteban-riofrio.png',
            'connection' => 'Aprendiz del taller; compañero de trabajo de Rodolfo',
            'motive' => 'Ninguno respecto a la muerte; sacaba cera y moldes del taller para vender sus propias velas',
            'alibi' => 'Recogiendo el montaje del salón con Nazario durante la velada; lo confirma Nazario',
            'public' => [
                'age' => '30 años',
                'profile' => 'Aprendiz del taller. Profesional y algo resentido; siente que su trabajo no siempre se reconoce.',
                'alibi' => 'Dice haber estado recogiendo el montaje del salón durante la velada.',
                'facts' => [
                    'Aprendió el oficio con Leonor y trabaja codo a codo con Rodolfo.',
                    'Prepara los moldes de las velas personalizadas de cada ceremonia.',
                ],
            ],
        ],
        'delfina-casal' => [
            'name' => 'Delfina Casal',
            'role' => 'sospechosa',
            'file' => 'suspects/delfina-casal.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['SD'], 'confrontation' => true],
            ]],
            'photo' => 'delfina-casal.png',
            'connection' => 'Organizadora de eventos; coordinó la ceremonia privada',
            'motive' => 'Ninguno respecto a la muerte; cobraba comisiones no declaradas a los proveedores que recomendaba',
            'alibi' => 'En la barra y la cocina con el personal de catering durante la velada',
            'public' => [
                'age' => '45 años',
                'profile' => 'Organizadora de eventos que coordinó la ceremonia. Pulida y mediática, muy atenta a la reputación de la velada.',
                'alibi' => 'Dice haber supervisado el servicio de la velada en el salón principal.',
                'facts' => [
                    'Es la responsable de la logística y del programa de la noche.',
                    'Recomienda proveedores a los talleres y clientes con los que trabaja.',
                ],
            ],
        ],
        'perpetua-salinas' => [
            'name' => 'Perpetua Salinas',
            'role' => 'sospechosa',
            'file' => 'suspects/perpetua-salinas.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['SP'], 'confrontation' => true],
            ]],
            'photo' => 'perpetua-salinas.png',
            'connection' => 'Contadora y administradora del taller',
            'motive' => 'Ninguno respecto a la muerte; cubría con un asiento contable un préstamo personal no autorizado',
            'alibi' => 'En la oficina administrativa haciendo el cierre de caja del evento, con dos validaciones en el sistema contable',
            'public' => [
                'age' => '50 años',
                'profile' => 'Contadora y administradora del taller. Meticulosa y reservada con las cuentas.',
                'alibi' => 'Dice haber hecho el cierre de caja del evento en la oficina administrativa.',
                'facts' => [
                    'Maneja las finanzas del taller y conoce sus cuentas mejor que nadie.',
                    'La oficina administrativa queda separada del salón.',
                ],
            ],
        ],
        'ignacio-davo' => [
            'name' => 'Ignacio Davó',
            'role' => 'sospechoso',
            'file' => 'suspects/ignacio-davo.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 3, 'after' => ['SI'], 'confrontation' => true],
            ]],
            'photo' => 'ignacio-davo.png',
            'connection' => 'Expareja de Leonor',
            'motive' => 'Ninguno respecto a la muerte; reclamaba en secreto una parte de los bienes compartidos con Leonor',
            'alibi' => 'En el salón principal conversando con otros invitados; lo confirma Araceli',
            'public' => [
                'age' => '53 años',
                'profile' => 'Expareja de Leonor. Cordial y algo evasivo cuando se habla de la ruptura.',
                'alibi' => 'Dice haber estado conversando con otros invitados en el salón principal.',
                'facts' => [
                    'Terminó su relación con Leonor hace poco, en términos tensos.',
                    'Fue invitado a la ceremonia por la propia Leonor.',
                ],
            ],
        ],
        'araceli-huertas' => [
            'name' => 'Araceli Huertas',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/araceli-huertas.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DT']],
            ]],
            'photo' => 'araceli-huertas.png',
            'connection' => 'Invitada a la ceremonia y clienta habitual del taller',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '38 años',
                'profile' => 'Invitada a la ceremonia y clienta habitual del taller. Amable y observadora.',
                'facts' => [
                    'Pasó la velada en el salón principal.',
                    'Conocía a Leonor del taller.',
                ],
            ],
            'accusable' => false,
        ],
        'nazario-bello' => [
            'name' => 'Nazario Bello',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/nazario-bello.md',
            'interrogation' => ['stages' => [
                ['questions' => 4],
                ['questions' => 1, 'after' => ['V03']],
            ]],
            'photo' => 'nazario-bello.png',
            'connection' => 'Asistente de montaje del evento',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '26 años',
                'profile' => 'Asistente de montaje del evento. Joven, práctico y atento.',
                'facts' => [
                    'Montó el altar y el salón antes de la ceremonia.',
                    'Recogió el montaje al terminar la velada.',
                ],
            ],
            'accusable' => false,
        ],
        'fermina-ubilla' => [
            'name' => 'Fermina Ubilla',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/fermina-ubilla.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['DT']],
            ]],
            'photo' => 'fermina-ubilla.png',
            'connection' => 'Amiga cercana de Leonor',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '55 años',
                'profile' => 'Amiga cercana de Leonor. Cálida y afectada por la pérdida.',
                'facts' => [
                    'Habló con Leonor esa misma tarde.',
                    'Conoce a Leonor desde hace muchos años.',
                ],
            ],
            'accusable' => false,
        ],
        'walter-niembro' => [
            'name' => 'Walter Niembro',
            'role' => 'fuente secundaria (proveedor de materiales)',
            'file' => 'suspects/walter-niembro.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'photo' => 'walter-niembro.png',
            'connection' => 'Encargado de entregas de la Casa de Insumos Cerámicos y Cereros',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '48 años',
                'profile' => 'Encargado de entregas de la Casa de Insumos Cerámicos y Cereros. Práctico; habla con datos.',
                'facts' => [
                    'Conoce los materiales y moldes que se entregaron al taller.',
                    'Solo puede hablar de las entregas, no de lo que pasó en la ceremonia.',
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
        'culprit_slug' => 'rodolfo-casares',

        'headline' => 'Rodolfo mató a Leonor al ser descubierto vendiendo en secreto la fórmula del taller, y la propia cera delató el encubrimiento del altar.',

        'motive' => 'Proteger el fraude de la fórmula exclusiva de cera del taller, que llevaba meses vendiendo en secreto a una marca competidora y que Leonor, que lo descubrió por un mensaje mal enviado, iba a exponer esa misma noche.',

        'method' => 'Al terminar el acto de la ceremonia, Leonor lo confrontó a solas en el área de preparación de moldes. Él intentó que esperara; ella se negó, y en la discusión Rodolfo la empujó: cayó contra el borde de la mesa de trabajo y murió. Después, solo en el cierre del área del altar, apagó y volvió a verter la vela central, la reencendió y desplazó un soporte para que el altar pareciera intacto.',

        'key_evidence' => [
            'La costura de doble capa de la vela central, leída con las tres notas de la ficha de trabajo: una vela que ardió de corrido no la tiene.',
            'El registro de cierre: Rodolfo, solo en el área del altar, y el registro de moldes: él preparó esa vela.',
            'El soporte desplazado: seis soportes en la foto original del altar, cinco en la de detalle, y el número 5 en la mesa lateral.',
            'El mensaje mal enviado a Leonor, que habla del acuerdo con la otra marca, y el testimonio de Fermina.',
            'Araceli lo vio cruzar al pasillo del fondo antes que a Leonor, contra su versión de haber estado todo el tiempo en el salón.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster entry that is not the culprit,
         * including the three witnesses and the supplier.
         */
        'exonerations' => [
            'micaela-aguirre' => 'Sí negociaba en secreto vender su parte del taller a una cadena más grande, sin decírselo a su madre: una deslealtad filial, pero sin relación con la muerte. Estuvo en el salón principal atendiendo a los invitados toda la velada, como confirma Araceli.',

            'esteban-riofrio' => 'Sí sacaba pequeñas cantidades de cera y algunos moldes del taller para vender sus propias velas, una falta real pero ajena a la muerte. Estuvo recogiendo el montaje del salón junto a Nazario durante toda la velada, y el registro de cierre lo sitúa allí.',

            'delfina-casal' => 'Sí cobraba comisiones no declaradas a los proveedores que recomendaba, un conflicto de interés real pero sin relación con la muerte. Estuvo supervisando el servicio en la barra y la cocina con el personal de catering, como muestran la comanda y el registro de cierre.',

            'perpetua-salinas' => 'Sí cubría con un asiento contable un préstamo personal no autorizado del fondo del taller, un fraude real pero distinto y sin relación con la muerte. Estuvo en la oficina administrativa haciendo el cierre de caja, con dos validaciones en el sistema contable y la anotación del registro de cierre.',

            'ignacio-davo' => 'Sí reclamaba en secreto, por vía legal, una parte de los bienes compartidos con Leonor, algo que ella no sabía, pero eso no tiene relación con la muerte. Estuvo en el salón principal conversando con otros invitados, como confirma Araceli.',

            'araceli-huertas' => 'Es una testigo, no una sospechosa: solo contó lo que vio, que Rodolfo y después Leonor cruzaron al pasillo del fondo, y confirmó dónde estaban Micaela e Ignacio. No tenía ningún motivo ni relación con el taller más allá de ser clienta.',

            'nazario-bello' => 'Es un testigo, no un sospechoso: montó el altar, recogió el salón con Esteban y encontró a Leonor. Solo contó lo que vio y lo que Leonor le explicó del protocolo. No tenía motivo alguno.',

            'fermina-ubilla' => 'Es una testigo, no una sospechosa: solo contó lo que Leonor le dijo esa tarde sobre lo que había descubierto y a quién pensaba confrontar. No estuvo cerca del área de preparación ni tenía motivo alguno.',

            'walter-niembro' => 'Es una fuente neutral, no un sospechoso: repartió los materiales y moldes que el taller había pedido y solo puede hablar de las entregas. No estuvo en la ceremonia ni tuvo relación con lo ocurrido.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it. Verbatim from the design (A2).
        'confession_script' => <<<'TXT'
            Necesitaba el dinero extra, y pensé que nadie lo notaría. Cuando Leonor me dijo que
            lo sabía todo, intenté que esperara, que lo resolviéramos con calma. Se negó, y en la
            discusión la empujé. Cayó contra la mesa, y no... no reaccionó. Después, solo pensé
            en que nadie notara cuánto tiempo había pasado. No debí tocar nada de lo que toqué
            después.
            TXT,
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Qué ocurrió realmente?',
            'hint' => 'Cuenta dónde y cómo murió Leonor, qué hizo el responsable después y qué quería lograr con ello.',
            'points' => 20,
            'guidance' => 'Lo esencial: Leonor lo confrontó en el área de preparación de moldes, él la empujó o golpeó, ella cayó contra la mesa de trabajo y murió; después él alteró el altar (la vela central y un soporte) para encubrirlo. No exigir aquí el detalle de las pruebas materiales: se pregunta aparte. La cera nunca fue el arma: cero si la presenta como causa de la muerte.',
        ],
        'motive' => [
            'label' => '¿Por qué lo hizo?',
            'hint' => 'Qué había descubierto Leonor y qué perdía el responsable si ella lo exponía.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'apoyos_encubrimiento',
                'label' => '¿Cómo se demuestra el encubrimiento del altar?',
                'hint' => 'Compara la fotografía original del altar con las de después, y apoya cada conclusión en la ficha de trabajo y en los registros del taller.',
                'answer' => 'Tres apoyos independientes. 1) La vela central muestra una costura de doble capa: según la ficha de trabajo, es la marca de una vela que se apagó y se volvió a verter, imposible si hubiera ardido de corrido toda la noche como manda el protocolo. 2) El registro de cierre deja a Rodolfo solo a cargo del área del altar tras el cierre del acto, y el registro de moldes muestra que él preparó esa vela con el molde grande. 3) Un soporte fue desplazado: en la fotografía original hay seis soportes numerados en círculo, en la de detalle solo cinco con el 4 corrido hacia el hueco, y el 5 aparece en la mesa lateral.',
                'points' => 30,
                'guidance' => 'Puntos completos solo si nombra los tres apoyos (costura, ventana del registro de cierre, soporte desplazado). Dar la mitad como máximo si se apoya en una sola prueba material (solo la vela, solo el registro o solo el soporte) sin reconocer las otras; cero si no explica ninguna con las reglas de la ficha ni con los registros. No exigir los números exactos de los soportes.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+13/T+26/T+39/T+52/T+65/T+78/T+90
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as
    | the other cases). The private trays (V03, V07) are random_player events
    | with their public copies later in the line.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Cera de los Invitados — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V01'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 13,
            'title' => 'Expansión — las personas del Atelier y el altar original',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V02'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Desarrollo — una ficha del taller',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V04'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Pista privada — la vela central, de cerca',
            'source_file' => 'pista-privada-restos-cera.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V03'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 39,
            'title' => 'Complicación — quién preparó qué esa tarde',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            // Also carries the public copy of V03 (the private tray at T+26
            // already declares the code).
            'evidence_codes' => ['V05'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 39,
            'title' => 'Pista privada — dos mensajes de invitación',
            'source_file' => 'pista-privada-invitaciones.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V07'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Giro — la mesa lateral',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V06'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Copia para toda la mesa — dos mensajes de invitación',
            'source_file' => 'copia-publica-invitaciones.md',
            'delivery_mode' => 'all',
            // Public copy of V07; the code is declared by the private tray.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Cierre — la ventana de la noche y los soportes',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V08', 'V09'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 78,
            'title' => 'Cierre final — declaraciones ampliadas, antecedentes y coartadas',
            'source_file' => 'testimonios.md',
            'delivery_mode' => 'all',
            // DT = the written (expanded) declarations of Araceli and Fermina;
            // SM/SE/SD/SP/SI = the antecedent documents of each secondary
            // suspect. None has an image, so each gets its own code (same
            // device as DF in punto-de-control).
            'evidence_codes' => ['DT', 'SM', 'SE', 'SD', 'SP', 'SI'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 78,
            'title' => 'Audio — la nota de voz de Leonor',

            'audio_file' => 'a1-nota-de-voz-leonor.mp3',
            'audio_scene' => 'Nota de voz de móvil grabada en el área de trabajo de un taller artesanal de velas, con un leve ambiente de taller de fondo; sin música.',
            'audio_context' => 'Leonor, una mujer de 50 años, graba para sí misma una nota de voz esa tarde. Tono calmado y decidido, como quien ha tomado una decisión difícil sobre un asunto profesional y está resuelta a afrontarlo esa misma noche. Sin ninguna señal de miedo ni de presagio: suena como alguien que procesa una decisión, no como alguien en peligro. No menciona ningún nombre propio. Duración: 15-20 segundos.',
            'audio_speaker' => 'Speaker 1 - Leonor Aguirre',
            'audio_voice' => 'Vindemiatrix',
            'audio_script' => <<<'TXT'
                Ya até los cabos. Esta noche, cuando termine la ceremonia, voy a hablar con la persona involucrada, cara a cara, antes de decidir qué hacer con el taller.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 90,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, el formulario hace una pregunta sobre cómo se demuestra el encubrimiento del altar. Al responderla, comparen la fotografía del altar original con la de los soportes y la de la mesa lateral, usen las tres notas de la ficha de trabajo para leer la vela central y contrasten los registros del taller: no basta con una sola prueba.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-cera-de-los-invitados/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v01-plano-de-mesas.png', 'caption' => 'Plano de mesas de la ceremonia'],
        ],
        'expansion.md' => [
            ['file' => 'v02-altar-original.png', 'caption' => 'El altar artesanal antes de que comenzara la ceremonia'],
        ],
        'desarrollo.md' => [
            ['file' => 'v04-ficha-de-trabajo.png', 'caption' => 'Ficha de trabajo del taller'],
        ],
        'pista-privada-restos-cera.md' => [
            ['file' => 'v03-vela-central-detalle.png', 'caption' => 'La superficie de la vela central, de cerca'],
        ],
        'complicacion.md' => [
            ['file' => 'v05-registro-de-moldes.png', 'caption' => 'Registro de moldes y materiales de esa tarde'],
            ['file' => 'v03-vela-central-detalle.png', 'caption' => 'La superficie de la vela central, de cerca (copia pública)'],
        ],
        'pista-privada-invitaciones.md' => [
            ['file' => 'v07-invitaciones.png', 'caption' => 'Dos mensajes de invitación a la ceremonia'],
        ],
        'copia-publica-invitaciones.md' => [
            ['file' => 'v07-invitaciones.png', 'caption' => 'Dos mensajes de invitación a la ceremonia (copia pública)'],
        ],
        'giro.md' => [
            ['file' => 'v06-mesa-lateral.png', 'caption' => 'La mesa lateral junto al altar, después de lo ocurrido'],
        ],
        'cierre.md' => [
            ['file' => 'v08-registro-de-cierre.png', 'caption' => 'Registro de limpieza y cierre del taller, esa noche'],
            ['file' => 'v09-soportes-altar.png', 'caption' => 'Detalle de los soportes del altar, después de lo ocurrido'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v03-vela-central-detalle.png', 'caption' => 'La costura de la vela central: no arde de corrido una vela que tiene dos capas'],
            ['file' => 'v04-ficha-de-trabajo.png', 'caption' => 'La ficha de trabajo: cómo se lee una vela'],
            ['file' => 'v08-registro-de-cierre.png', 'caption' => 'La ventana del registro de cierre: Rodolfo, solo en el altar'],
            ['file' => 'v02-altar-original.png', 'caption' => 'El altar original: seis soportes en círculo'],
            ['file' => 'v09-soportes-altar.png', 'caption' => 'Después: cinco soportes, y uno corrido hacia el hueco'],
        ],
    ],
];
