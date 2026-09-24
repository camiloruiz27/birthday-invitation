<?php

/**
 * Case manifest: "Tres Minutos de Silencio"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this case separates three independent causal chains during
 * a three-minute blackout — the technical origin of the outage (Ignacio's real
 * negligence), an unrelated cover-up of a manipulated security log (Paola's
 * real misconduct), and the murder itself (Rodrigo). Nobody's lie is "because
 * they are the culprit" — every suspect's evasion protects a real, distinct,
 * non-homicidal fault of their own. The table must resist reading the night as
 * one coordinated conspiracy.
 *
 * Gap fixed during authoring, same precedent as kilometro-186 before it: the
 * source design brief's own evidence catalog (section 7) lists 18 visual
 * pieces (V1-V18), but its delivery timeline (section 6) only schedules
 * V1, V3, V4, V6, V7, V8, V9, V10, V13, V15, V16, V18. V2, V5, V11, V12, V14
 * and V17 were described but never scheduled. Rescheduled here by where they
 * are contextually useful:
 *  - V2 (rutas de evacuación) alongside V4/V5/V6 at T+12 — sets up the blocked
 *    exits later named in the audit report.
 *  - V5 (CCTV al restablecerse la luz) alongside V4/V6 at T+12 — the "after"
 *    half of the exact three-minute window.
 *  - V11/V12 (fotos de contexto de las coartadas de Ignacio y Paola) alongside
 *    V7/V8 at T+36 — right where their confrontations need them.
 *  - V17 (foto del trípode, sin daño) alongside V10/V13 at T+50 — the weapon
 *    is established once the group is reconstructing the blackout itself.
 *  - V14 (mensajes de Lucía) alongside V16/V18 at T+78 — it is one of the
 *    three cross-checked alibis the Cierre delivers together.
 *
 * The audit report is also delivered in two stages, same pattern as
 * kilometro-186's V8/V8-completo: a nameless private fragment at T+24 (code
 * `V9`) and the complete named version at T+78 (code `V9-completo`), needed
 * specifically by Rodrigo's confrontación 2.
 */
return [

    'name' => 'Tres Minutos de Silencio',
    'version' => '1.0',
    'code' => 'Expediente interno — Centro Cultural Meridiano',
    'authority' => 'Centro Cultural Meridiano — Seguridad e Incidentes',

    'victim' => [
        'name' => 'Martina Robledo',
        'photo' => 'martina-robledo.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // El brief pide 5 preguntas por sospechoso, 4 por testigo, hasta 2
        // confrontaciones por sospechoso. El motor solo soporta un numero
        // plano por caso (CaseDefinition::interrogationQuestions()), mismo
        // precedente que los otros siete casos.
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
        'tagline' => 'Tres minutos sin luz. Todos hicieron algo que preferirían no explicar.',

        'description' => <<<'TXT'
            Durante la gala de apertura de una exposición en el Centro Cultural
            Meridiano, un apagón parcial de exactamente tres minutos corta
            cámaras, comunicaciones y parte de las puertas del recinto. Cuando
            vuelve la energía, Martina Robledo, coordinadora general del
            evento, aparece muerta en una zona restringida.

            Tu equipo (5 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos y a dos testigos,
            uno por uno, mientras se reconstruye minuto a minuto lo ocurrido
            durante el corte.

            Casi todos hicieron esa noche algo que preferirían no explicar. El
            reto no es encontrar a alguien con algo que ocultar — es separar,
            entre varias faltas reales pero distintas, cuál de ellas terminó
            en un asesinato.
            TXT,

        // Archivo esperado en public/immersion/tres-minutos-de-silencio/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'hard',
        'duration_minutes' => 90,
        'min_players' => 5,
        'max_players' => 8,

        'price_amount' => 65900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 7,
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
        'rodrigo-alsina' => [
            'name' => 'Rodrigo Alsina',
            'role' => 'sospechoso',
            'file' => 'suspects/rodrigo-alsina.md',
            'photo' => 'rodrigo-alsina.png',
            'connection' => 'Director de operaciones, superior jerárquico de Martina',
            'motive' => 'El informe de auditoría de Martina documenta aforos excesivos y salidas de emergencia bloqueadas que él aprobó',
            'alibi' => 'Dice haber coordinado la respuesta de emergencia desde el punto de control todo el tiempo',
        ],
        'ignacio-farias' => [
            'name' => 'Ignacio Farías',
            'role' => 'sospechoso',
            'file' => 'suspects/ignacio-farias.md',
            'photo' => 'ignacio-farias.png',
            'connection' => 'Jefe de mantenimiento del recinto',
            'motive' => 'Conectó equipo de iluminación adicional sin verificar la capacidad del circuito (falta real, sin relación con la muerte)',
            'alibi' => 'En la sala de control técnico durante todo el apagón, confirmable por su equipo',
        ],
        'paola-irigoyen' => [
            'name' => 'Paola Irigoyen',
            'role' => 'sospechosa',
            'file' => 'suspects/paola-irigoyen.md',
            'photo' => 'paola-irigoyen.png',
            'connection' => 'Jefa de seguridad del recinto',
            'motive' => 'Desactivó sensores por un favor personal y editó después el registro (falta real, sin relación con la muerte)',
            'alibi' => 'Registro de acceso a la propia central de seguridad durante toda la ventana crítica',
        ],
        'camila-estevez' => [
            'name' => 'Camila Estévez',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-estevez.md',
            'photo' => 'camila-estevez.png',
            'connection' => 'Productora externa del evento',
            'motive' => 'Solicitó el equipo adicional que causó la sobrecarga (aparente); infla facturas de proveedores (real, sin relación)',
            'alibi' => 'En la sala principal con decenas de invitados durante todo el apagón',
        ],
        'bruno-castro' => [
            'name' => 'Bruno Castro',
            'role' => 'sospechoso',
            'file' => 'suspects/bruno-castro.md',
            'photo' => 'bruno-castro.png',
            'connection' => 'Patrocinador principal, invitado VIP',
            'motive' => 'Usó una ruta discreta facilitada por Paola durante el apagón (aparente); encuentro extramatrimonial (real, sin relación)',
            'alibi' => 'En una sala privada distinta a la zona restringida, aclarable por Paola',
        ],
        'lucia-font' => [
            'name' => 'Lucía Font',
            'role' => 'sospechosa',
            'file' => 'suspects/lucia-font.md',
            'photo' => 'lucia-font.png',
            'connection' => 'Asistente directa de Martina; encontró el cuerpo',
            'motive' => 'Acceso privilegiado a la agenda de Martina (aparente); distraída con una crisis familiar personal (real, sin relación)',
            'alibi' => 'Mensajes personales con marca de hora durante toda la ventana crítica',
        ],
        'elena-marti' => [
            'name' => 'Elena Martí',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/elena-marti.md',
            'photo' => 'elena-marti.png',
            'connection' => 'Azafata de sala',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'tomas-quiroga' => [
            'name' => 'Tomás Quiroga',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/tomas-quiroga.md',
            'photo' => 'tomas-quiroga.png',
            'connection' => 'Técnico de sonido',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'sistema-seguridad' => [
            'name' => 'Central de seguridad / sistema de control',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-seguridad.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as kilometro-186's "sistema-tren").
            'photo' => 'sistema-seguridad.png',
            'connection' => 'Registros objetivos del recinto (energía, puertas, sensores)',
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
        'culprit_slug' => 'rodrigo-alsina',

        'headline' => 'Durante los tres minutos exactos del apagón, Rodrigo Alsina siguió a Martina Robledo hasta la zona restringida de almacenamiento técnico y la golpeó con un trípode de iluminación de repuesto, para impedir que expusiera un informe de auditoría que lo incriminaba.',

        'motive' => 'El informe de auditoría de seguridad de Martina documentaba aforos excesivos y salidas de emergencia bloqueadas, aprobados por Rodrigo, que sería presentado ante la junta directiva el lunes siguiente.',

        'method' => 'La siguió hasta la zona restringida aprovechando la oscuridad y el caos de comunicaciones; ante su negativa a detener la presentación del informe, la golpeó con un trípode de iluminación de repuesto almacenado en la zona.',

        'key_evidence' => [
            'Elena lo vio dirigirse hacia el corredor de la zona restringida justo después del corte, antes de que las radios fallaran del todo.',
            'Tomás oyó pasos apresurados y un golpe sordo desde esa zona durante el apagón.',
            'El registro de puertas muestra que la puerta de la zona restringida se abrió en modo manual, no solo automático.',
            'El registro de radio muestra una ausencia de transmisión de Rodrigo durante toda la ventana crítica, pese a decir que nunca dejó el punto de control.',
            'El informe de auditoría, completo, lo nombra directamente como responsable de los aforos y salidas bloqueadas.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not have
         * been responsible. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'ignacio-farias' => 'Estuvo en la sala de control técnico intentando restablecer el sistema durante todo el apagón, confirmado por dos técnicos de su equipo. Su falta real es haber conectado equipo de iluminación adicional sin verificar la capacidad del circuito, lo que causó la sobrecarga — una negligencia profesional grave, pero sin motivo ni oportunidad para matar a Martina.',

            'paola-irigoyen' => 'El registro de acceso a la propia central de seguridad la sitúa allí durante toda la ventana crítica, intentando diagnosticar el apagón. Su falta real es haber desactivado sensores de un corredor por un favor personal a un VIP y haber editado después el registro para ocultarlo — un encubrimiento real, pero ajeno por completo al apagón y a la muerte.',

            'camila-estevez' => 'Permaneció en la sala principal, rodeada de decenas de invitados, durante todo el apagón. Su única falta es inflar facturas de proveedores para quedarse con la diferencia, un fraude menor sin relación alguna con Martina ni con el apagón.',

            'bruno-castro' => 'La ruta que usó esa noche, aclarada por Paola bajo confrontación, no coincide con la zona restringida donde murió Martina. La usaba para un encuentro privado con alguien con quien mantiene una relación extramatrimonial, sin relación alguna con la muerte.',

            'lucia-font' => 'El registro de sus propios mensajes personales, con marca de hora, la sitúa lejos de la zona restringida durante toda la ventana crítica. Estaba distraída con una crisis familiar, avergonzada de admitirlo, no encubriendo un crimen.',

            'elena-marti' => 'Es azafata de sala, sin relación alguna con Martina más allá del trabajo del evento: no tiene motivo ni oportunidad. Su aporte fue justamente haber visto a Rodrigo dirigirse hacia la zona restringida justo después del corte.',

            'tomas-quiroga' => 'Es técnico de sonido, sin relación alguna con Martina: no tiene motivo ni oportunidad. Su aporte fue haber oído, desde cerca, los pasos apresurados y el golpe sordo durante el apagón, sin poder ver nada en la oscuridad.',

            'sistema-seguridad' => 'Es un sistema automatizado de registro, no una persona: no tiene forma de haber intervenido ni motivo alguno. Su función esa noche fue registrar con exactitud la hora del corte y el restablecimiento de energía, el estado de las puertas, y la desactivación y posterior edición de un sensor — datos objetivos que, cruzados con los testimonios, permiten separar las tres cadenas de responsabilidad.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Fenrir',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from the
        // table, it never composes it.
        'confession_script' => <<<'TXT'
            Llevaba meses aprobando aforos que sabía que no debía aprobar. Cada
            evento grande significaba más ingresos, y siempre pensé que
            tendríamos tiempo de corregirlo antes de que pasara algo grave.

            Cuando supe que Martina iba a presentarlo el lunes, con nombres, con
            fechas, la seguí cuando se cortó la luz. Solo quería que me diera
            tiempo, que lo habláramos antes.

            No fue premeditado. Fue un segundo, en la oscuridad, y ya no hubo
            vuelta atrás. Ni siquiera sirvió para nada al final: ella ya lo
            había mandado todo, por si acaso. Maté a alguien... para nada.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design brief's T+0/T+12/T+24/T+36/T+50/T+65/T+78/T+90
    | delivery plan into trigger_offset_minutes (T+0 becomes minute 1, same
    | convention as the other seven cases). See the authoring note at the top
    | of this file for the evidence-scheduling gap fixed here.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Tres Minutos de Silencio — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V3'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — megafonía de emergencia',

            'audio_file' => 'a1-megafonia-emergencia.wav',
            'audio_scene' => 'Anuncio de megafonía en un recinto cultural durante un evento de gala, ambiente de fondo de cientos de personas murmurando con inquietud contenida.',
            'audio_context' => 'La voz de megafonía informa con tono calmado y profesional de un corte de energía temporal y pide a los asistentes mantener la calma.',
            'audio_speaker' => 'Speaker 1 - Voz de megafonía',
            'audio_voice' => 'Iapetus',
            'audio_script' => <<<'TXT'
                Atención, por favor. Estamos experimentando un corte de energía
                temporal. Manténganse en su lugar, el personal de seguridad los
                guiará si es necesario. Solucionaremos esto en unos minutos.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — la ventana exacta de tres minutos',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V4', 'V5', 'V6'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — informe de auditoría',
            'source_file' => 'pista-privada-informe.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V9'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Complicación — puertas, sensores y dos coartadas técnicas',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V7', 'V8', 'V11', 'V12'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 36,
            'title' => 'Audio — fragmento de radio durante el apagón',

            'audio_file' => 'a2-fragmento-radio.wav',
            'audio_scene' => 'Intercambio de radio diegético durante una emergencia de corte de energía en un recinto de eventos, estática realista de fondo.',
            'audio_context' => 'Paola e Ignacio se comunican por radio para diagnosticar el apagón; el intercambio termina en un llamado a Rodrigo que se queda sin respuesta.',
            'audio_speaker' => 'Paola Irigoyen / Ignacio Farías (intercambio de radio)',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                Central a todos los puntos, ¿alguien tiene visual de las puertas
                del sector técnico?

                Estamos en ello, dennos un par de minutos, el panel principal
                no responde.

                ¿Rodrigo, copiado? Rodrigo, ¿me copia?
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Reconstrucción del apagón — se habilitan las confrontaciones',
            'source_file' => 'reconstruccion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V10', 'V13', 'V17'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Giro — una puerta abierta a mano',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V15'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 78,
            'title' => 'Cierre — el informe completo y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V9-completo', 'V14', 'V16', 'V18'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 90,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para separar las tres cadenas de esa noche: quién provocó el apagón, quién manipuló después un registro para ocultar un desliz propio, y quién aprovechó los tres minutos exactos para matar. Casi todos hicieron algo cuestionable — pero solo una persona decidió matar. Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/tres-minutos-de-silencio/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-operativo.png', 'caption' => 'Plano operativo del Centro Cultural Meridiano'],
            ['file' => 'v3-escena-zona-restringida.png', 'caption' => 'La zona restringida de almacenamiento técnico, fotografiada al hacer el hallazgo'],
        ],
        'expansion.md' => [
            ['file' => 'v2-rutas-evacuacion.png', 'caption' => 'Esquema de rutas de evacuación del recinto'],
            ['file' => 'v4-cctv-antes-apagon.png', 'caption' => 'CCTV del salón principal, momento anterior al apagón'],
            ['file' => 'v5-cctv-restablecimiento.png', 'caption' => 'CCTV del salón principal, al restablecerse la energía'],
            ['file' => 'v6-registro-energia.png', 'caption' => 'Registro técnico de energía — caída y restablecimiento'],
        ],
        'pista-privada-informe.md' => [
            ['file' => 'v9-informe-parcial.png', 'caption' => 'Fragmento del informe de auditoría de Martina (sin nombre del responsable)'],
        ],
        'complicacion.md' => [
            ['file' => 'v7-registro-puertas.png', 'caption' => 'Registro de estado de puertas durante el apagón'],
            ['file' => 'v8-registro-sensores.png', 'caption' => 'Registro de sensores de la zona restringida'],
            ['file' => 'v11-sala-control-tecnico.png', 'caption' => 'Sala de control técnico, donde trabajaba Ignacio'],
            ['file' => 'v12-central-seguridad.png', 'caption' => 'Central de seguridad, donde trabajaba Paola'],
        ],
        'reconstruccion.md' => [
            ['file' => 'v10-correo-equipo-adicional.png', 'caption' => 'Correo de solicitud de equipo de iluminación adicional'],
            ['file' => 'v13-transcripcion-radio.png', 'caption' => 'Transcripción de comunicaciones de radio durante el apagón'],
            ['file' => 'v17-tripode-iluminacion.png', 'caption' => 'Trípode de iluminación de repuesto, sin daño visible'],
        ],
        'giro.md' => [
            ['file' => 'v15-puerta-modo-manual.png', 'caption' => 'La puerta de la zona restringida, abierta en modo manual'],
        ],
        'cierre.md' => [
            ['file' => 'v9-informe-completo.png', 'caption' => 'Informe de auditoría completo, con el nombre de Rodrigo Alsina'],
            ['file' => 'v14-mensajes-lucia.png', 'caption' => 'Mensajes personales de Lucía, con marca de hora'],
            ['file' => 'v16-agenda-martina.png', 'caption' => 'Agenda de Martina — presentación programada para el lunes'],
            ['file' => 'v18-correo-preliminar-martina.png', 'caption' => 'Correo preliminar de Martina a una colega, horas antes del apagón'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v15-puerta-modo-manual.png', 'caption' => 'La puerta abierta a mano, no por el sistema de seguridad'],
            ['file' => 'v13-transcripcion-radio.png', 'caption' => 'El silencio de Rodrigo en la radio, durante toda la ventana crítica'],
            ['file' => 'v9-informe-completo.png', 'caption' => 'El informe, ya con su nombre'],
        ],
    ],
];
