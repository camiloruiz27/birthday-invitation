<?php

/**
 * Case manifest: "Kilómetro 186"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: nobody in this case lies about what they saw or heard. The
 * mystery is that several honest testimonies are anchored to the passenger
 * information screens, which were quietly reset 13 minutes behind real time by
 * Simón during a legitimate, routine technical reset. The internal train system
 * (door log, kilometer markers) is independent and stayed exact throughout. No
 * confrontation should ever resolve by "catching a lie" from Olga or Hugo — only
 * by re-anchoring their honest testimony once the offset (V12) is presented to
 * them. Until then they must keep repeating their screen-based hour exactly as
 * given, never self-correcting.
 *
 * Two gaps in the source design brief were fixed during authoring, same
 * precedent as habitacion-314 and desaparecida-en-directo before it:
 *
 *  - The brief's own evidence list (V4, V9, V10, V11) was never scheduled in its
 *    delivery timeline. Rescheduled here: V9 alongside V7/V2 at T+10, V4/V11
 *    alongside V5/V6 at T+35, V10 alongside V13 at T+65.
 *  - The audit report is delivered in two stages (a redacted T+20 private
 *    fragment, then the complete named version at T+65) but the brief tags both
 *    as plain "V8". The engine has no sub-codes, so the redacted fragment keeps
 *    code `V8` (available from T+20 — enough for Simón's confrontación 2, which
 *    only needs to know irregularities exist, not yet the name) and the named
 *    version at T+65 uses a distinct code `V8-completo` (needed specifically by
 *    Daniel's confrontation, which the brief explicitly gates on the complete
 *    report).
 */
return [

    'name' => 'Kilómetro 186',
    'version' => '1.0',
    'code' => 'Expediente interno — Expreso Nocturno Meridian',
    'authority' => 'Ferrocarriles Meridian — Seguridad e Incidentes',

    'victim' => [
        'name' => 'Celia Ortuño',
        'photo' => 'celia-ortuno.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The brief asks for 5 preguntas por sospechoso, 4 por testigo, hasta 2
        // confrontaciones por sospechoso. The engine only supports one flat
        // number per case (CaseDefinition::interrogationQuestions()), same
        // precedent as the other five cases.
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
    | (price_amount, currency, published_at) are seeded only the first time,
    | so changing a price in the database is not undone by the next deploy.
    |
    */

    'catalog' => [
        'tagline' => 'Su bolso sigue en el asiento. Su abrigo, también. Ella no.',

        'description' => <<<'TXT'
            En el Expreso Nocturno Meridian, durante el largo tramo sin paradas
            alrededor del kilómetro 186, Celia Ortuño —auditora interna que viaja a
            entregar un informe sobre irregularidades de mantenimiento—
            desaparece de su asiento. Poco antes, las pantallas de información de
            los vagones sufrieron un reinicio técnico rutinario que todo el mundo
            presenció y nadie cuestionó.

            Tu equipo (4 a 7 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis pasajeros y tripulantes y a dos
            testigos, uno por uno, mientras el tren avanza hacia la terminal.

            El giro central: nadie miente sobre lo que vio u oyó esa noche, pero
            varios testimonios usan una referencia horaria que dejó de ser
            fiable en cuanto se restableció el sistema. La mesa deberá cruzar
            esos relatos con referencias independientes —un hito kilométrico, un
            registro interno de puertas— para descubrir que todos señalan, una
            vez corregidos, al mismo lugar y a la misma persona.
            TXT,

        // Archivo esperado en public/immersion/kilometro-186/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'hard',
        'duration_minutes' => 75,
        'min_players' => 4,
        'max_players' => 7,

        // PLACEHOLDER: precio sugerido, pendiente de confirmar con el usuario.
        // 64,900 COP iguala el nivel hard/75-95min de habitacion-314 y
        // proyecto-boreal.
        'price_amount' => 64900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    |
    */

    'suspects' => [
        'simon-andrade' => [
            'name' => 'Simón Andrade',
            'role' => 'sospechoso',
            'file' => 'suspects/simon-andrade.md',
            'photo' => 'simon-andrade.png',
            'connection' => 'Jefe de tren',
            'motive' => 'El informe de Celia lo implica en la falsificación de registros de mantenimiento',
            'alibi' => 'Dice haber hecho la ronda de rutina hasta las once; su coartada depende de la pantalla que él mismo alteró',
        ],
        'rebeca-duarte' => [
            'name' => 'Rebeca Duarte',
            'role' => 'sospechosa',
            'file' => 'suspects/rebeca-duarte.md',
            'photo' => 'rebeca-duarte.png',
            'connection' => 'Pasajera, excolega de Celia',
            'motive' => 'Conversación tensa poco antes de la desaparición (aparente)',
            'alibi' => 'Encuentro con Celia a las 22:40, antes del reinicio; permaneció después en su asiento, visible para otros pasajeros',
        ],
        'mateo-figueroa' => [
            'name' => 'Mateo Figueroa',
            'role' => 'sospechoso',
            'file' => 'suspects/mateo-figueroa.md',
            'photo' => 'mateo-figueroa.png',
            'connection' => 'Pasajero nervioso',
            'motive' => 'Comportamiento nervioso y cercanía al compartimento de Celia (aparente)',
            'alibi' => 'En el vagón bar la mayor parte de la noche, confirmable por Hugo',
        ],
        'hugo-pastor' => [
            'name' => 'Hugo Pastor',
            'role' => 'sospechoso',
            'file' => 'suspects/hugo-pastor.md',
            'photo' => 'hugo-pastor.png',
            'connection' => 'Camarero del vagón restaurante',
            'motive' => 'Acceso a pasillos y carros de servicio toda la noche (aparente)',
            'alibi' => 'Una vez corregido su testimonio (pantalla → hora real), su ubicación no coincide con el vagón de servicio',
        ],
        'ines-roman' => [
            'name' => 'Inés Román',
            'role' => 'sospechosa',
            'file' => 'suspects/ines-roman.md',
            'photo' => 'ines-roman.png',
            'connection' => 'Amiga de Celia',
            'motive' => 'Discusión previa al viaje (aparente)',
            'alibi' => 'En su propio asiento, varios vagones más allá, confirmada por otros pasajeros',
        ],
        'daniel-cortez' => [
            'name' => 'Daniel Cortez',
            'role' => 'sospechoso',
            'file' => 'suspects/daniel-cortez.md',
            'photo' => 'daniel-cortez.png',
            'connection' => 'Supervisor de Celia',
            'motive' => 'El informe también podría afectar su propia gestión (aparente)',
            'alibi' => 'En su compartimento trabajando; registro de puertas sin accesos irregulares',
        ],
        'olga-ventura' => [
            'name' => 'Olga Ventura',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/olga-ventura.md',
            'photo' => 'olga-ventura.png',
            'connection' => 'Pasajera, compartimento contiguo al de Celia',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'lucas-medina' => [
            'name' => 'Lucas Medina',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/lucas-medina.md',
            'photo' => 'lucas-medina.png',
            'connection' => 'Auxiliar de tren',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'sistema-tren' => [
            'name' => 'Sistema del tren y centro de control',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-tren.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as habitacion-314's "sistema-hotel"
            // y de desaparecida-en-directo's "sistema-evento").
            'photo' => 'sistema-tren.png',
            'connection' => 'Registros objetivos del tren (puertas, hitos kilométricos, corrección de desfase)',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The ending
    |--------------------------------------------------------------------------
    */

    'solution' => [
        'culprit_slug' => 'simon-andrade',

        'headline' => 'Simón Andrade condujo a Celia al vagón de servicio poco después de un reinicio técnico legítimo, en el que dejó el reloj de las pantallas 13 minutos por detrás de la hora real, y la retiene allí, viva pero contra su voluntad.',

        'motive' => 'El informe de auditoría de Celia documentaba registros de mantenimiento falsificados y firmados por Simón, arriesgando su empleo y responsabilidades mayores.',

        'method' => 'Pretexto de un problema de equipaje para conducirla al vagón de servicio; confrontación sobre el informe y retención tras negarse a modificarlo.',

        'key_evidence' => [
            'Su propia coartada depende de la pantalla que él mismo manipuló; corregida, no coincide con el registro de puertas.',
            'Lucas Medina usó el túnel del kilómetro 186, no la pantalla, como referencia para ubicar el momento exacto.',
            'El sistema de puertas es independiente del de pantallas y registró su acceso al vagón de servicio a las 22:58.',
            'Los testimonios de Olga y Hugo, corregidos con el desfase de 13 minutos, coinciden exactamente con esa ventana.',
            'El informe de auditoría, completo, lo nombra directamente como responsable de los registros falsificados.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not have
         * been responsible. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'rebeca-duarte' => 'Su encuentro con Celia fue a las 22:40, antes del reinicio de las pantallas, por lo que no necesita ninguna corrección horaria; después permaneció en su asiento, visible para otros pasajeros, durante toda la ventana crítica. Su único secreto es que está dejando a su pareja, sin relación alguna con la desaparición.',

            'mateo-figueroa' => 'Estuvo en el vagón bar la mayor parte de la noche, confirmado por Hugo. Su nerviosismo se debe a un billete irregular comprado con datos de otra persona, para evitar a un acreedor — un fraude menor propio, sin relación alguna con Celia.',

            'hugo-pastor' => 'Su testimonio sobre ver a Simón cruzar el vagón restaurante era veraz, pero estaba anclado en la pantalla desfasada: corregido con los 13 minutos, su propia ubicación real —cerca del vagón restaurante— no coincide con el vagón de servicio en ningún momento relevante. Su único secreto es un desvío menor de la caja del bar.',

            'ines-roman' => 'Su coartada de distancia física —su propio asiento, varios vagones más allá— está confirmada por otros pasajeros cercanos durante toda la ventana crítica. Su discusión previa con Celia fue un malentendido personal ya resuelto; su único peso es la culpa por no haber ido a verla esa noche.',

            'daniel-cortez' => 'El registro de puertas no muestra ningún acceso irregular a su nombre esa noche; estuvo en su compartimento trabajando en otros documentos. Sabía que el informe nombraría a Simón y no dijo nada, una omisión incómoda, pero su motivo real no requería en absoluto la desaparición de Celia.',

            'olga-ventura' => 'Es una pasajera sin relación con Celia: su único aporte es lo que oyó a través de la pared, anclado por costumbre en la pantalla desfasada. Corregida su hora, su testimonio confirma la ventana de Simón en lugar de contradecirla — no tuvo motivo ni oportunidad de intervenir ella misma.',

            'lucas-medina' => 'Es el auxiliar de tren, no un sospechoso: su único aporte es lo que vio desde su puesto, ubicado con precisión por el túnel del kilómetro 186, no por ninguna pantalla. No tuvo motivo ni oportunidad, y fue precisamente su referencia física la que ayudó a fechar el momento exacto.',

            'sistema-tren' => 'Es un sistema automatizado de registro, no una persona: no tiene forma de haber intervenido ni motivo alguno. Su función esa noche fue registrar con exactitud la hora del reinicio, el acceso al vagón de servicio y el hito del kilómetro 186 — y, más tarde, confirmar el desfase de 13 minutos que permite reinterpretar correctamente el resto de los testimonios.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Sadaltager',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from the
        // table, it never composes it.
        'confession_script' => <<<'TXT'
            Llevo quince años en esta línea. Firmé esos registros porque me
            dijeron que si no cerrábamos los números, cerraban la ruta y todos
            perdíamos el puesto. No fue la única vez, y lo sabía.

            Cuando ella me dijo que llevaba el informe completo a la terminal,
            no lo pensé con calma. Le dije que había un problema con su
            equipaje, la llevé al vagón de servicio, y le pedí que lo cambiara.
            Se negó.

            No le he hecho daño. Está bien, está ahí. Pero no sabía cómo parar
            esto sin que todo se derrumbara. Y ahora... ahora ya no hay forma
            de que no se derrumbe.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design brief's T+0/T+10/T+20/T+35/T+50/T+65/T+75
    | delivery plan into trigger_offset_minutes (T+0 becomes minute 1, same
    | convention as the other five cases). See the authoring note at the top of
    | this file for the two scheduling gaps fixed here.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Kilómetro 186 — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 10,
            'title' => 'Expansión — el reinicio y quién viajaba dónde',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V7', 'V9'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 10,
            'title' => 'Audio — anuncio de megafonía del reinicio',

            'audio_file' => 'a1-anuncio-reinicio.wav',
            'audio_scene' => 'Anuncio de megafonía de tren, ligero eco característico, sonido ambiente de vagón nocturno de fondo.',
            'audio_context' => 'Simón anuncia, con tono rutinario, un breve reinicio técnico del sistema de información de los vagones.',
            'audio_speaker' => 'Speaker 1 - Voz de megafonía (Simón Andrade)',
            'audio_voice' => 'Iapetus',
            'audio_script' => <<<'TXT'
                Atención, pasajeros. Vamos a reiniciar el sistema de
                información de los vagones por un breve fallo técnico.
                Disculpen las molestias, estará resuelto en unos minutos.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 20,
            'title' => 'Fragmento privado — informe de auditoría',
            'source_file' => 'pista-privada-informe.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V8'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 35,
            'title' => 'Complicación — dos registros que no dependen de la pantalla',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4', 'V5', 'V6', 'V11'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 35,
            'title' => 'Audio — grabación accidental de Olga',

            'audio_file' => 'a2-grabacion-olga.wav',
            'audio_scene' => 'Mensaje de voz grabado sin querer, sonido ambiente de tren en movimiento de fondo; a través de la pared, muy tenue, dos voces alteradas sin palabras distinguibles.',
            'audio_context' => 'Olga habla con normalidad a una amiga sobre su viaje, sin notar que de fondo se cuela el sonido apenas perceptible de una discusión al otro lado de la pared.',
            'audio_speaker' => 'Speaker 1 - Olga Ventura',
            'audio_voice' => 'Vindemiatrix',
            'audio_script' => <<<'TXT'
                Bueno, te cuento cómo va el viaje, la verdad es que muy
                tranquilo, aunque el vagón de al lado hace bastante... en fin,
                ya te contaré mañana con calma, ahora intento dormir un poco.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Giro — la pantalla no marcaba la hora real',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V12', 'V14'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Cierre — el informe completo y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V8-completo', 'V10', 'V13'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 75,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para reconstruir la cronología real de esa noche — y para entender que nadie mintió, solo usaban una hora que había dejado de ser fiable. Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/kilometro-186/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-vagones.png', 'caption' => 'Plano simplificado de los vagones del Expreso Nocturno Meridian'],
            ['file' => 'v3-asiento-vacio.png', 'caption' => 'El asiento vacío de Celia, con su bolso y su abrigo aún colocados'],
        ],
        'expansion.md' => [
            ['file' => 'v2-panel-post-reinicio.png', 'caption' => 'Panel de pasajeros, fotografiado poco después del reinicio'],
            ['file' => 'v7-manifiesto-pasajeros.png', 'caption' => 'Manifiesto de pasajeros y tickets'],
            ['file' => 'v9-vagon-restaurante.png', 'caption' => 'El vagón restaurante, donde trabaja Hugo'],
        ],
        'pista-privada-informe.md' => [
            ['file' => 'v8-informe-parcial.png', 'caption' => 'Fragmento del informe de auditoría (sin nombre del responsable)'],
        ],
        'complicacion.md' => [
            ['file' => 'v5-registro-puertas.png', 'caption' => 'Registro interno de puertas — apertura del vagón de servicio'],
            ['file' => 'v6-registro-km186.png', 'caption' => 'Registro de hitos kilométricos — entrada al túnel del km 186'],
            ['file' => 'v4-pasillo-conexion.png', 'caption' => 'Pasillo de conexión hacia el vagón de servicio'],
            ['file' => 'v11-puerta-servicio.png', 'caption' => 'Puerta del vagón de servicio (exterior, sin señales de forzado)'],
        ],
        'giro.md' => [
            ['file' => 'v12-correccion-desfase.png', 'caption' => 'Corrección oficial del desfase horario (13 minutos)'],
            ['file' => 'v14-panel-corregido.png', 'caption' => 'El mismo panel, ya con la hora corregida'],
        ],
        'cierre.md' => [
            ['file' => 'v8-informe-completo.png', 'caption' => 'Informe de auditoría completo, con el nombre de Simón Andrade'],
            ['file' => 'v13-objeto-celia.png', 'caption' => 'Objeto personal de Celia, hallado cerca del vagón de servicio'],
            ['file' => 'v10-chat-celia-daniel.png', 'caption' => 'Conversación entre Celia y Daniel, de días antes del viaje'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the table
        // can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v5-registro-puertas.png', 'caption' => 'Simón, en el vagón de servicio a las 22:58 — hora exacta, sin desfase'],
            ['file' => 'v12-correccion-desfase.png', 'caption' => 'Los 13 minutos que reubican a Olga y a Hugo en la ventana correcta'],
            ['file' => 'v8-informe-completo.png', 'caption' => 'El informe, ya con su nombre'],
        ],
    ],
];
