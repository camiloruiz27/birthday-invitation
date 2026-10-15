<?php

/**
 * Case manifest: "Bajo la Línea de Flotación"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "three acts" case. The table tends to melt three
 * separate wrongdoings into a single conspiracy; the case only resolves when
 * they are pulled apart:
 *  - Ramiro sabotaged the valve (no homicidal intent).
 *  - Constanza delayed the alarm and edited the log to hide it.
 *  - Lucía used the isolation of the lower section to kill Horacio.
 * The ship's systems (doors, pumps, comms) NEVER fail for narrative
 * convenience: whatever they show is exact. No evidence or confrontation may
 * suggest otherwise.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 6-field form and point
 *    scoring are NOT implemented; the three acts are asked for inside the
 *    free-text motive (see the T+85 unlock text) and explained in
 *    solucion.md and in each exoneration.
 *  - The engine has one flat question budget per suspect, so the doc's
 *    "5 + 2 confrontations" is 5 here (same precedent as the other cases).
 *    Confrontations are gated by evidence delivered, not by a separate pool.
 *  - V11 (radio transcript) was never scheduled in the design doc, although
 *    Lucía's second confrontation requires it. Delivered at T+48 together
 *    with A2, which it transcribes.
 *  - V8, V9 and V15 (context photos) were never scheduled either. V8/V9 go to
 *    T+12/T+36 as workplace context; V15 goes to T+48 next to the access sheet.
 *  - Lucía's second confrontation was tied to "T+62" but needs the complete
 *    V10, which only arrives at T+75. The md gates it on the evidence itself.
 *  - V14 lists three authorized entries. Ramiro's is short (22:16–22:27) and
 *    ends before the attack window (22:36–22:40) and the body's discovery
 *    at 22:45, so it coexists with his engine-room alibi for that window. Lucía enters at
 *    22:21 and the log shows her leaving at 22:42, far from her "subí
 *    enseguida"; Daniel saw her climb the hatch at that moment.
 *  - Daniel's N5 support ("lo vio revisando ambas cosas") is impossible from
 *    outside the section, so he instead HEARD Horacio's radio message from the
 *    engine-room hatch station.
 *  - Matías's alibi is confirmed discreetly by his partner (unnamed) in the T+75
 *    cross-alibis delivery.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'Bajo la Línea de Flotación',
    'version' => '1.0',
    'code' => 'Expediente confidencial — BIO Albatros',
    'authority' => 'Comisión de Investigación del Instituto Oceanográfico Nacional',

    'victim' => [
        'name' => 'Horacio Valderrama',
        'label' => 'Víctima',
        'photo' => 'horacio-valderrama.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 5 preguntas + 2 de confrontación por
        // sospechoso. The engine only supports one flat number per case, same
        // precedent as the other cases.
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
        'tagline' => 'La avería fue real. El aislamiento fue real. Lo que no fue casualidad es que alguien decidiera usarlo.',

        'description' => <<<'TXT'
            El BIO Albatros, un buque de investigación oceanográfica, regresa
            a puerto cuando una entrada de agua obliga a aislar una sección
            bajo la línea de flotación. Cuando la emergencia se controla, el
            jefe de máquinas aparece muerto en esa misma sección.

            Tu equipo (5 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, dos testigos y
            al propio sistema del buque, durante 85 minutos, cruzando
            registros de puertas, alarmas, accesos y comunicaciones.

            Aquí hay más de una falta a bordo y más de un culpable de algo.
            Pero solo una persona mató. Separar quién hizo qué es lo que
            resuelve el caso.
            TXT,

        // Archivo esperado en public/immersion/bajo-la-linea-de-flotacion/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/bajo-la-linea-de-flotacion).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Un buque de investigación aísla una sección inundada. Cuando se reabre, el jefe de máquinas está muerto.',
            'bullets' => [
                '5 a 8 investigadores cruzando registros de puertas, alarmas y accesos',
                'Seis sospechosos, dos testigos y el sistema del buque a quienes interrogar',
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
        // public/immersion/bajo-la-linea-de-flotacion/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => true,
        'sort_order' => 9,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Daniel, Rosa) and the secondary source (the ship's
    | control system / bridge log) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'lucia-fernandez-mora' => [
            'name' => 'Dra. Lucía Fernández Mora',
            'role' => 'sospechosa',
            'file' => 'suspects/lucia-fernandez-mora.md',
            'photo' => 'lucia-fernandez-mora.png',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V14'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V10', 'V11'], 'confrontation' => true],
            ]],
            'connection' => 'Jefa científica de la expedición',
            'motive' => 'Impedir que Horacio expusiera el falseamiento de datos científicos de la campaña',
            'alibi' => 'Presencia legítima en la sección inferior para revisar las muestras',
            'public' => [
                'age' => '42 años',
                'profile' => 'Jefa científica de la expedición. Dirige el trabajo científico de la campaña y el laboratorio de a bordo.',
                'alibi' => 'Dice haber bajado un momento a revisar las muestras y haber subido enseguida.',
                'facts' => [
                    'Tenía autorización para entrar en la sección aislada por las muestras almacenadas allí.',
                    'Es la responsable científica de la campaña que realiza el buque.',
                ],
            ],
        ],
        'ramiro-oyarzun' => [
            'name' => 'Ramiro Oyarzún',
            'role' => 'sospechoso',
            'file' => 'suspects/ramiro-oyarzun.md',
            'photo' => 'ramiro-oyarzun.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['A2', 'V11']],
                ['questions' => 3, 'after' => ['V3', 'V4', 'V14'], 'confrontation' => true],
            ]],
            'connection' => 'Segundo de máquinas; subordinado directo de Horacio',
            'motive' => 'Ninguno respecto a la muerte; provocó la avería por miedo a recortes (acto distinto, sin intención homicida)',
            'alibi' => 'En la sala de máquinas coordinando las bombas durante la ventana del ataque, confirmado por dos técnicos',
            'public' => [
                'age' => '48 años',
                'profile' => 'Segundo de máquinas y responsable técnico de la zona donde ocurrió la avería. Orgulloso de su experiencia y de su equipo.',
                'alibi' => 'Dice haber estado en la sala de máquinas con sus compañeros durante la emergencia.',
                'facts' => [
                    'Su área recibió, semanas antes, un aviso institucional sobre revisión presupuestaria.',
                    'Conoce el funcionamiento de la válvula y de las bombas de su sección.',
                ],
            ],
        ],
        'constanza-riquelme' => [
            'name' => 'Constanza Riquelme',
            'role' => 'sospechosa',
            'file' => 'suspects/constanza-riquelme.md',
            'photo' => 'constanza-riquelme.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V11']],
                ['questions' => 3, 'after' => ['V6', 'V14'], 'confrontation' => true],
            ]],
            'connection' => 'Oficial de guardia en el puente de mando',
            'motive' => 'Ninguno respecto a la muerte; alteró el registro para ocultar su demora en la alarma (acto distinto)',
            'alibi' => 'En el puente con el capitán durante toda la ventana crítica, confirmado por el registro de guardia',
            'public' => [
                'age' => '34 años',
                'profile' => 'Oficial de guardia esa noche: responsable de la respuesta inicial a la emergencia desde el puente. Meticulosa con los procedimientos.',
                'alibi' => 'Dice haber estado en el puente durante toda la emergencia.',
                'facts' => [
                    'Fue quien activó formalmente la alarma de entrada de agua.',
                    'El registro de guardia lleva su nombre.',
                ],
            ],
        ],
        'capitan-solis' => [
            'name' => 'Capitán Eduardo Solís',
            'role' => 'sospechoso',
            'file' => 'suspects/capitan-solis.md',
            'photo' => 'capitan-solis.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V11']],
            ]],
            'connection' => 'Comandante del buque',
            'motive' => 'Ninguno; oculta un problema de salud y una solicitud de traslado a tierra (sin relación con la muerte)',
            'alibi' => 'En el puente durante toda la ventana crítica, confirmado por Constanza y el registro de guardia',
            'public' => [
                'age' => '56 años',
                'profile' => 'Comandante del BIO Albatros. Calmado, autoritario y algo distante en lo personal. Mantenía con Horacio una relación de respeto profesional.',
                'alibi' => 'Dice haber dirigido la emergencia desde el puente.',
                'facts' => [
                    'Tiene la máxima responsabilidad sobre cualquier incidente a bordo.',
                    'Los últimos días se le ha visto más cansado de lo habitual.',
                ],
            ],
        ],
        'tomas-andia' => [
            'name' => 'Tomás Andía',
            'role' => 'sospechoso',
            'file' => 'suspects/tomas-andia.md',
            'photo' => 'tomas-andia.png',
            'connection' => 'Contramaestre; coordina la cubierta',
            'motive' => 'Ninguno; introduce mercancía personal sin declarar usando los suministros del buque (sin relación con la muerte)',
            'alibi' => 'Ayudando con el equipo de emergencia en cubierta, confirmado por Rosa',
            'public' => [
                'age' => '39 años',
                'profile' => 'Contramaestre: coordina la cubierta y tiene acceso a las zonas de carga y almacenamiento del buque. Nervioso cuando le preguntan por sus movimientos.',
                'alibi' => 'Dice haber hecho rondas de revisión y ayudado con el equipo de cubierta.',
                'facts' => [
                    'Tiene acceso a las zonas de almacenamiento durante cualquier emergencia.',
                    'Conocía a Horacio como colega de tripulación, sin conflictos conocidos.',
                ],
            ],
        ],
        'matias-uribe' => [
            'name' => 'Matías Uribe',
            'role' => 'sospechoso',
            'file' => 'suspects/matias-uribe.md',
            'photo' => 'matias-uribe.png',
            'connection' => 'Científico junior; asistente de Lucía',
            'motive' => 'Ninguno; mantiene una relación personal no declarada con otro tripulante (sin relación con la muerte)',
            'alibi' => 'Lejos de la sección inferior durante la ventana crítica, confirmado de forma indirecta',
            'public' => [
                'age' => '28 años',
                'profile' => 'Científico junior y asistente directo de la Dra. Fernández Mora en el laboratorio. Joven, reservado con su vida personal.',
                'alibi' => 'Dice haber estado ayudando en el laboratorio, asegurando equipo.',
                'facts' => [
                    'Trabaja directamente bajo las órdenes de Lucía.',
                    'No tenía autorización para entrar en la sección aislada.',
                ],
            ],
        ],
        'daniel-pizarro' => [
            'name' => 'Daniel Pizarro',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/daniel-pizarro.md',
            'photo' => 'daniel-pizarro.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 1, 'after' => ['A2', 'V11', 'V14']],
                ['questions' => 1, 'after' => ['V12']],
            ]],
            'connection' => 'Oficial de máquinas junior',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '30 años',
                'profile' => 'Oficial de máquinas junior. Durante la emergencia cubría el puesto junto a la escotilla de acceso a la sección inferior.',
                'facts' => [
                    'Trabajó junto a Ramiro y Horacio durante la emergencia.',
                    'Desde su puesto veía la escotilla de acceso a la sección aislada.',
                ],
            ],
            'accusable' => false,
        ],
        'rosa-navarro' => [
            'name' => 'Rosa Navarro',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/rosa-navarro.md',
            'photo' => 'rosa-navarro.png',
            'interrogation' => ['stages' => [
                ['questions' => 3],
            ]],
            'connection' => 'Encargada de intendencia',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '50 años',
                'profile' => 'Encargada de intendencia: se movía por las zonas comunes ayudando a coordinar al equipo de cubierta durante la emergencia.',
                'facts' => [
                    'Estuvo en las zonas comunes durante toda la emergencia.',
                    'Conoce los movimientos generales de la tripulación de cubierta.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-control-buque' => [
            'name' => 'Sistema de control del buque / registro del puente',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-control-buque.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-produccion") — a shot of
            // the bridge monitoring console works as its "portrait".
            'photo' => 'sistema-control-buque.png',
            'interrogation' => ['stages' => [
                ['questions' => 2],
                ['questions' => 1, 'after' => ['V6']],
                ['questions' => 2, 'after' => ['V11', 'V14']],
            ]],
            'connection' => 'Registros de puertas estancas, alarmas, accesos y comunicaciones',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente técnica del buque: registra puertas estancas, alarmas, accesos autorizados y transmisiones de radio, sin interpretar nada.',
                'facts' => [
                    'Registra la hora exacta de cada cierre y apertura de las puertas estancas.',
                    'No puede decir qué ocurrió dentro de la sección aislada.',
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
        'culprit_slug' => 'lucia-fernandez-mora',

        'headline' => 'La avería fue real y fue provocada, pero no para matar: Ramiro sabotó la válvula por miedo a los recortes y Constanza retrasó la alarma y alteró el registro por miedo a una sanción. El asesinato es un acto separado: la Dra. Lucía Fernández Mora aprovechó el aislamiento de la sección inferior para golpear a Horacio con un extintor y callarlo.',

        'motive' => 'Impedir que Horacio expusiera el falseamiento de datos científicos de la campaña, sobre el que descansaban una publicación y una beca internacional de gran visibilidad.',

        'method' => 'Golpe con un extintor de la propia sección inferior, entre las 22:36 y las 22:40, mientras las puertas estancas seguían cerradas. Lucía había bajado a las 22:21 con el pretexto legítimo de revisar las muestras y salió a las 22:42, poco antes de que se reabriera la sección.',

        'key_evidence' => [
            'El mensaje de radio de Horacio (22:35) menciona explícitamente "el inventario de muestras", el área de responsabilidad directa de Lucía.',
            'El inventario de muestras con su nombre muestra discrepancias entre lo registrado y la verificación física.',
            'La hoja de acceso registra su salida a las 22:42, muy lejos del "subí enseguida" que declara.',
            'Daniel la vio subir por la escotilla en ese momento, antes de que se reabrieran las puertas.',
            'El cuerpo apareció en una plataforma seca y elevada: incompatible con una muerte por inundación.',
            'Ni Ramiro ni Constanza pudieron golpear a Horacio: tienen coartada para la ventana del ataque y sus faltas se explican sin su muerte.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'ramiro-oyarzun' => 'Sí provocó la avería: sabotó la válvula por miedo a recortes de presupuesto y personal, y las marcas de manipulación y el registro de mantenimiento lo confirman. Pero buscaba un incidente controlado, no una muerte, y salió de la sección a las 22:27, antes de que Horacio notara nada; durante la ventana del ataque (22:36–22:40) estaba en la sala de máquinas con dos técnicos.',

            'constanza-riquelme' => 'Sí retrasó la alarma cinco minutos (lectura anómala a las 22:05, alarma a las 22:10) y editó el registro para ocultarlo, por miedo a una sanción. Es una falta real, pero ajena a la muerte: estuvo en el puente con el capitán toda la ventana crítica y su motivo se satisface sin que Horacio muera.',

            'capitan-solis' => 'Ocultaba un problema de salud y una solicitud de traslado a tierra, un secreto personal sin relación con el caso. Estuvo en el puente durante toda la ventana crítica, confirmado por Constanza y por el registro de guardia.',

            'tomas-andia' => 'Su evasividad venía de un contrabando menor de mercancía personal usando los suministros del buque, sin relación alguna con Horacio. Rosa confirma que estuvo ayudando con el equipo de cubierta todo el tiempo.',

            'matias-uribe' => 'Mentía sobre dónde estuvo porque estaba con su pareja, una relación personal que prefería no hacer pública. Estaba lejos de la sección inferior y no tenía autorización de acceso; su pareja lo confirmó con discreción.',

            'daniel-pizarro' => 'Es un testigo, no un sospechoso: su puesto junto a la escotilla le permitió ver a Lucía subir y oír el mensaje de radio de Horacio, nada más. No estuvo dentro de la sección ni tenía motivo alguno.',

            'rosa-navarro' => 'Es la encargada de intendencia, no una sospechosa: su aporte fue confirmar los movimientos de Tomás en cubierta. No tuvo acceso a la sección aislada ni motivo alguno.',

            'sistema-control-buque' => 'Es un sistema automatizado de registro, no una persona: no puede haber intervenido. Su función fue confirmar, con exactitud, la hora de la lectura anómala, de la alarma, del cierre y apertura de las puertas y de los accesos, sin interpretar intenciones. Nunca falló: lo que mostró era cierto.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Kore',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Llevaba dos años construyendo esta publicación. La beca dependía de estos
            resultados, y sabía que algunos datos no eran tan sólidos como los presenté.
            Pensé que tendría tiempo de corregirlo antes de que alguien lo notara.

            Cuando Horacio me dijo lo que había visto, no lo pensé con calma. Me acerqué
            a hablar con él, esperando que lo dejara pasar. No quiso. Discutimos, y ahí
            estaba el extintor.

            No fue planeado. Fue un segundo, en medio de todo ese caos, y ya no hubo
            vuelta atrás.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | The accusation and how it is graded
    |--------------------------------------------------------------------------
    |
    | Points from the design doc's scoring (30/15/20/15/10/10). The design's
    | "cronologia" field had no score of its own: it carries the 10 points for
    | keeping the three acts apart without a conspiracy.
    |
    */

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Cómo lo mataron y con qué?',
            'hint' => 'Dónde ocurrió y el objeto que se usó.',
            'points' => 15,
            'guidance' => 'Lo esencial: un golpe con un extintor en la sección inferior aislada. Cero si sostiene que murió ahogado por la inundación.',
        ],
        'motive' => [
            'label' => '¿Por qué?',
            'hint' => 'Qué quería impedir el culpable.',
            'points' => 20,
        ],
        'questions' => [
            [
                'key' => 'origen_averia',
                'label' => '¿Quién originó la avería y por qué?',
                'hint' => 'Nombra a la persona, qué hizo y con qué intención.',
                'answer' => 'Ramiro Oyarzún, el segundo de máquinas: días antes sabotó una válvula de la sección inferior por miedo a los recortes de presupuesto y personal de su área, buscando un incidente controlado, sin intención de dañar a nadie.',
                'points' => 15,
                'guidance' => 'Basta con nombrar a Ramiro, el sabotaje de la válvula y su miedo a los recortes. Restar si le atribuye intención homicida.',
            ],
            [
                'key' => 'encubrimiento_registro',
                'label' => '¿Quién alteró un registro de esa noche y qué intentaba ocultar?',
                'hint' => 'Nombra a la persona, qué alteró y qué quería ocultar.',
                'answer' => 'Constanza Riquelme: vio la lectura anómala a las 22:05, esperó cinco minutos antes de dar la alarma (22:10) y después editó el registro para ocultar esa demora, por miedo a una sanción. Sin relación con el asesinato.',
                'points' => 10,
                'guidance' => 'Basta con nombrar a Constanza y que alteró el registro para ocultar su demora con la alarma. No exigir las horas exactas.',
            ],
            [
                'key' => 'cronologia',
                'label' => 'Reconstruye la cronología: ¿qué descubrió la víctima, qué pasó después y cómo se relacionan entre sí los hechos de esa noche?',
                'hint' => 'Ordena lo ocurrido en la sección aislada y explica si los distintos hechos estaban o no coordinados.',
                'answer' => 'Durante su inspección, Horacio descubrió el sabotaje de la válvula y las discrepancias en el inventario de muestras de Lucía; lo comunicó de forma ambigua por radio a las 22:35, y Lucía, que estaba en la sección por una razón legítima, lo confrontó y lo golpeó entre las 22:36 y las 22:40, saliendo a las 22:42 antes de que se reabrieran las puertas. La avería (Ramiro), el encubrimiento del registro (Constanza) y el asesinato (Lucía) son tres actos separados, sin conspiración.',
                'points' => 10,
                'guidance' => 'Cero si trata la avería, el encubrimiento y el asesinato como una sola conspiración. No exigir horas exactas.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+24/T+36/T+48/T+62/T+75/T+85
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as
    | the other cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Bajo la Línea de Flotación — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V2'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — anuncio de entrada de agua',

            'audio_file' => 'a1-alarma-entrada-agua.mp3',
            'audio_scene' => 'Anuncio de megafonía en un buque de investigación durante una emergencia controlada: una breve alarma técnica naval y, de fondo, el sonido de motores y viento.',
            'audio_context' => 'La voz de megafonía del puente informa con tono profesional y firme de una entrada de agua confirmada en la sección inferior de proa y del cierre de puertas estancas según protocolo. Tensión contenida, no pánico.',
            'audio_speaker' => 'Speaker 1 - Voz de megafonía del puente',
            'audio_voice' => 'Iapetus',
            'audio_script' => <<<'TXT'
                Atención toda la tripulación: entrada de agua confirmada en sección
                inferior de proa. Se procede al cierre de puertas estancas según
                protocolo. Personal autorizado, diríjase a sus puestos de emergencia.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — sospechosos, testigos y el aislamiento',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V3', 'V5', 'V8'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — un aviso institucional',
            'source_file' => 'pista-privada-comunicado.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes: partial private glimpses (V7
            // fragment) must not satisfy any confrontation gate on their own.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — una hoja de inventario',
            'source_file' => 'pista-privada-inventario.md',
            'delivery_mode' => 'random_player',
            // V10 PARTIAL (no responsible name). The complete V10 only
            // arrives at T+75 and is what Lucía's confrontation 2 requires.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Complicación — la avería, hora por hora',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4', 'V6', 'V9'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 48,
            'title' => 'Reconstrucción del aislamiento — se habilitan las confrontaciones',
            'source_file' => 'reconstruccion.md',
            'delivery_mode' => 'all',
            // V11 and V15 added here during authoring: V11 transcribes A2 and
            // Lucía's confrontation 2 needs it; V15 explains her legitimate
            // reason to be in the section.
            'evidence_codes' => ['V11', 'V14', 'V15'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 48,
            'title' => 'Audio — fragmento de radio de Horacio',

            'audio_file' => 'a2-radio-horacio.mp3',
            'audio_scene' => 'Transmisión de radio interna desde una sección técnica de un buque durante una emergencia controlada: estática de radio y bombas funcionando de fondo.',
            'audio_context' => 'Horacio, cansado y funcional, reporta dos observaciones de trabajo como quien hace un informe rutinario, sin urgencia ni dramatismo. No es una amenaza.',
            'audio_speaker' => 'Speaker 1 - Horacio Valderrama',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                Puente, aquí abajo. La válvula no se ve como un desgaste normal, hay
                algo raro. Y ya que estoy, encontré algo en el inventario de muestras
                que no cuadra. Hablamos cuando terminemos aquí.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 62,
            'title' => 'Giro — tres actos, no uno',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V12', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 75,
            'title' => 'Cierre — el inventario completo y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V10', 'V16'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 85,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para reconstruir lo que pasó bajo la línea de flotación. Queda habilitado el formulario de acusación final: además de quién, cómo y por qué, les hará algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/bajo-la-linea-de-flotacion/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-cubiertas.png', 'caption' => 'Plano por cubiertas del BIO Albatros'],
            ['file' => 'v2-seccion-inferior-inundada.png', 'caption' => 'La sección inferior tras la emergencia'],
        ],
        'expansion.md' => [
            ['file' => 'v3-valvula-marcas.png', 'caption' => 'La válvula dañada, fotografiada durante la inspección'],
            ['file' => 'v5-registro-puertas-estancas.png', 'caption' => 'Registro de puertas estancas'],
            ['file' => 'v8-puente-de-mando.png', 'caption' => 'El puente de mando durante la noche'],
        ],
        'pista-privada-comunicado.md' => [
            ['file' => 'v7-comunicado-recorte.png', 'caption' => 'Fragmento de un comunicado interno del instituto'],
        ],
        'pista-privada-inventario.md' => [
            ['file' => 'v10a-inventario-parcial.png', 'caption' => 'Fragmento de una hoja de inventario de muestras'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-registro-mantenimiento-valvula.png', 'caption' => 'Registro de mantenimiento de la válvula'],
            ['file' => 'v6-registro-bombas-alarma.png', 'caption' => 'Panel de monitoreo: lectura anómala y alarma'],
            ['file' => 'v9-laboratorio.png', 'caption' => 'El laboratorio científico de a bordo'],
        ],
        'reconstruccion.md' => [
            ['file' => 'v14-hoja-acceso-seccion.png', 'caption' => 'Hoja de acceso a la sección aislada'],
            ['file' => 'v11-transcripcion-radio.png', 'caption' => 'Transcripción de las comunicaciones de radio'],
            ['file' => 'v15-camara-muestras.png', 'caption' => 'La cámara de muestras refrigeradas'],
        ],
        'giro.md' => [
            ['file' => 'v12-posicion-cuerpo-zona-seca.png', 'caption' => 'La plataforma seca donde se halló el cuerpo'],
            ['file' => 'v13-extintor.png', 'caption' => 'Un extintor fuera de su soporte'],
        ],
        'cierre.md' => [
            ['file' => 'v10-inventario-completo.png', 'caption' => 'Inventario de muestras completo, con su responsable'],
            ['file' => 'v16-mensaje-colega-externo.png', 'caption' => 'Mensaje de un colega externo sobre la publicación'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v3-valvula-marcas.png', 'caption' => 'Acto 1 — la manipulación de la válvula'],
            ['file' => 'v6-registro-bombas-alarma.png', 'caption' => 'Acto 2 — los cinco minutos de demora'],
            ['file' => 'v14-hoja-acceso-seccion.png', 'caption' => 'Acto 3 — quién seguía dentro a las 22:42'],
            ['file' => 'v10-inventario-completo.png', 'caption' => 'El motivo que Horacio había descubierto'],
        ],
    ],
];
