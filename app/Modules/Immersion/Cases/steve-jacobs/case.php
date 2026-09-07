<?php

/**
 * Case manifest: "¿Que le sucedio a Steve Jacobs?"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Editing the timing or the wording of a mechanic-authored message is done
 * here, without touching controllers, jobs or views.
 */
return [

    'name' => '¿Qué le sucedió a Steve Jacobs?',
    'version' => '1.0',
    'code' => 'SF 554301',
    'authority' => 'Departamento de Policía de San Francisco',

    'victim' => [
        'name' => 'Steve Jacobs',
        'photo' => 'steve-jacobs.jpg',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
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
        'tagline' => 'Un ejecutivo farmacéutico muerto en la habitación 803. Nueve personas con algo que esconder.',

        'description' => <<<'TXT'
            Steve Jacobs aparece muerto en su habitación del Hotel Altamira. No hay
            señales de entrada forzada ni de violencia: el informe forense apunta a
            un frasco de suplementos manipulado. Junto al cuerpo, una pieza de
            ajedrez que nadie sabe explicar.

            Tu equipo recibe el expediente por correo, en tiempo real, mientras el
            reloj corre: recortes de prensa, reportes de laboratorio, capturas de
            redes, recibos, mensajes de voz. Podrán interrogar a los sospechosos y
            al testigo uno por uno — pero cada persona habla con un solo
            investigador, así que tendrán que repartirse y compartir lo que
            averigüen.

            Al final, una sola acusación: quién, con qué y por qué.
            TXT,

        // Sin cover art dedicado todavia: cae al retrato de la victima.
        'cover' => null,

        'difficulty' => 'medium',
        'duration_minutes' => 90,
        'min_players' => 3,
        'max_players' => 8,

        // PLACEHOLDER: precio de referencia, ajustar antes de publicar.
        // price_amount va en la unidad minima de la moneda; el COP no usa
        // centavos en la practica, asi que aqui son pesos enteros.
        'price_amount' => 89000,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | These are the short "Ficha" fields (connection, motive, alibi) exactly as
    | they already appear in those files, plus the photo cropped from the
    | original case PDF, so the suspect board can be visual instead of text-only.
    |
    */

    'suspects' => [
        'elizabeth-foster' => [
            'name' => 'Elizabeth Foster',
            'role' => 'sospechosa',
            'file' => 'suspects/elizabeth-foster.md',
            'photo' => 'elizabeth-foster.jpg',
            'connection' => 'Esposa',
            'motive' => 'Sospecha engaño',
            'alibi' => 'Estaba en su coche',
        ],
        'sofia-reyes' => [
            'name' => 'Sofía Reyes',
            'role' => 'sospechosa',
            'file' => 'suspects/sofia-reyes.md',
            'photo' => 'sofia-reyes.jpg',
            'connection' => 'Relación amorosa',
            'motive' => 'Despecho',
            'alibi' => 'Ticket Uber',
        ],
        'lucas-jacobs' => [
            'name' => 'Lucas Jacobs',
            'role' => 'sospechoso',
            'file' => 'suspects/lucas-jacobs.md',
            'photo' => 'lucas-jacobs.jpg',
            'connection' => 'Hijo',
            'motive' => 'Falta de apoyo en su carrera',
            'alibi' => 'Hoja de registro',
        ],
        'rachel-miller' => [
            'name' => 'Rachel Miller',
            'role' => 'sospechosa',
            'file' => 'suspects/rachel-miller.md',
            'photo' => 'rachel-miller.jpg',
            'connection' => 'Amiga de la esposa',
            'motive' => 'Enojo',
            'alibi' => null,
        ],
        'emily-johnson' => [
            'name' => 'Emily Johnson',
            'role' => 'sospechosa',
            'file' => 'suspects/emily-johnson.md',
            'photo' => 'emily-johnson.jpg',
            'connection' => 'Empleada del Hotel Altamira',
            'motive' => null,
            'alibi' => null,
        ],
        'kevin-huang' => [
            'name' => 'Kevin Huang',
            'role' => 'sospechoso',
            'file' => 'suspects/kevin-huang.md',
            'photo' => 'kevin-huang.jpg',
            'connection' => 'Contador',
            'motive' => 'El padre fue rechazado para un procedimiento cardíaco urgente y murió',
            'alibi' => 'Ticket Uber',
        ],
        'daniel-blake' => [
            'name' => 'Daniel Blake',
            'role' => 'sospechoso',
            'file' => 'suspects/daniel-blake.md',
            'photo' => 'daniel-blake.jpg',
            'connection' => 'Amigo/Socio',
            'motive' => 'Desacuerdos laborales',
            'alibi' => null,
        ],
        'sarah-collins' => [
            'name' => 'Sarah Collins',
            'role' => 'sospechosa',
            'file' => 'suspects/sarah-collins.md',
            'photo' => 'sarah-collins.jpg',
            'connection' => 'Gerente de Hotel Altamira',
            'motive' => null,
            'alibi' => null,
        ],
        'jeremy-burt-testigo' => [
            'name' => 'Jeremy Burt',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/jeremy-burt-testigo.md',
            'photo' => 'jeremy-burt.jpg',
            'connection' => 'Recepcionista del turno nocturno, Hotel Altamira',
            'motive' => null,
            'alibi' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Solution
    |--------------------------------------------------------------------------
    |
    | The ending, authored. The AI never decides who did it — not here and not
    | in the epilogue or the confession audio, which only ever put THIS material
    | into a suspect's mouth.
    |
    | `content/solucion.md` must never reach the AI gateway. Suspect testimonies
    | are sent verbatim during interrogations; the solution is not.
    |
    | >>> PENDIENTE: contenido por escribir. <<<
    | Hasta que se llene, la revelacion muestra este texto de plantilla. Lo que
    | se decida aqui tiene que ser coherente con los 9 testimonios ya escritos
    | en content/suspects/.
    |
    */

    'solution' => [
        // Must be a key of 'suspects' above.
        'culprit_slug' => 'PENDIENTE',

        'headline' => 'PENDIENTE: la frase que cierra el caso.',
        'motive' => 'PENDIENTE: por que lo hizo.',
        'method' => 'PENDIENTE: como lo hizo.',
        'key_evidence' => [
            'PENDIENTE: la prueba que lo señala.',
        ],

        // The long reveal, rendered verbatim like any other case content.
        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue (later delivery): why THIS innocent
         * suspect could not have done it. Without an authored line per suspect,
         * the model would have to reason out the mistake — which is exactly
         * "inventing the ending".
         *
         * One entry per suspect that is not the culprit.
         */
        'exonerations' => [
            'elizabeth-foster' => 'PENDIENTE',
            'sofia-reyes' => 'PENDIENTE',
            'lucas-jacobs' => 'PENDIENTE',
            'rachel-miller' => 'PENDIENTE',
            'emily-johnson' => 'PENDIENTE',
            'kevin-huang' => 'PENDIENTE',
            'daniel-blake' => 'PENDIENTE',
            'sarah-collins' => 'PENDIENTE',
            'jeremy-burt-testigo' => 'PENDIENTE',
        ],

        // Used by the confession audio (later delivery). Written to be played
        // as-is, so the table still gets an ending if the AI is unavailable.
        'confession_script' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Attached to every new game of this case. Events citing a "Sobre"
    | (source_file) use the original case material as-is. Events marked below
    | as "REDACTADO PARA LA MECANICA" are new text written for this game (not
    | from the original material), but stay consistent with the facts already
    | established in sobre-1/2/3 and introduce no new suspects, evidence or
    | alibis.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 10,
            'title' => 'Sobre 1 - Expediente del caso',
            'source_file' => 'sobres/sobre-1.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 30,
            'title' => 'Actualizacion de la Detective Reed',
            // REDACTADO PARA LA MECANICA: no existe palabra por palabra en el
            // material original. Solo recapitula hechos ya enviados en sobre-1.
            'body_markdown' => <<<'MD'
                **Actualizacion preliminar - Caso SF 554301**

                A quien corresponda,

                Les escribo para mantenerlos al tanto del estado de la investigacion
                sobre la muerte de Steve Jacobs. Como ya saben, el informe forense
                confirmo altos niveles de digitoxina en su sangre, y el frasco de
                suplementos hallado en el bano presenta manipulacion evidente. No hay
                senales de entrada forzada ni de violencia fisica, lo que sigue
                apuntando a alguien con acceso directo a la habitacion 803 o a los
                suplementos de la victima antes de esa noche.

                La pieza de ajedrez - una torre blanca - sigue sin una explicacion
                clara. No descarto que sea una firma deliberada de quien hizo esto,
                pero por ahora la tratamos como una pista abierta, no como una
                conclusion.

                Les pedire que revisen con cuidado cualquier comunicacion adicional
                que reciban en los proximos dias; en un caso con tantos involucrados
                en HelixCare, es facil perder de vista un detalle pequeno que termina
                siendo el que cierra el caso.

                Sgt. Det. Michelle Reed
                Departamento de Policia de San Francisco
                MD,
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 35,
            'title' => 'Sobre 2 - HelixCare e interrogatorios',
            'source_file' => 'sobres/sobre-2.md',
            'delivery_mode' => 'all',
            // Besides the verbatim envelope content, this email carries a
            // button straight into the suspect interrogation, if the Game
            // Master has that mechanic enabled.
            'cta_interrogation' => true,
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 45,
            'title' => 'Mensaje de voz - Laboratorio forense',
            // REDACTADO PARA LA MECANICA: guion de TTS, voz del Dr. Goddard
            // (ya nombrado en el informe de laboratorio de sobre-1). Solo
            // reafirma en audio hallazgos que ya estan por escrito.
            'audio_script' => <<<'TXT'
                Detective Reed, habla el doctor Goddard, del laboratorio forense.
                Queria darle un adelanto verbal de lo que ya esta en mi reporte
                escrito, para que el equipo lo tenga presente cuanto antes. Las
                huellas del frasco de suplementos corresponden a Steve Jacobs y a
                Daniel Blake; hay zonas borradas compatibles con el uso de guantes.
                La concentracion de digitoxina dentro de una de las capsulas
                coincide con lo que encontramos en el cuerpo de la victima, asi que
                no hay duda de que ese frasco es el origen. Sobre la pieza de
                ajedrez, seguimos sin huellas ni mensaje asociado. Le mando el
                informe completo por escrito en un momento.
                TXT,
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 50,
            'title' => 'Llamada de numero desconocido',
            // REDACTADO PARA LA MECANICA: llamada anonima ambigua (posible red
            // herring), solo para un jugador al azar. No contradice el material
            // original: se apoya en la torre blanca ya revelada en sobre-1.
            'audio_script' => <<<'TXT'
                Numero desconocido. La llamada suena distorsionada.

                ...hola? Se que estas investigando lo de Jacobs. La torre blanca no
                es un accidente ni una casualidad, y esta no es la primera vez que
                pasa. Si de verdad quieres entender que significa esa pieza, deja
                de mirar solo el hotel. Mira quien mas ha aparecido con una torre
                blanca cerca. No voy a decir nada mas por este medio.

                La llamada se corta.
                TXT,
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 55,
            'title' => 'Alerta de monitoreo - Canal "Torre Blanca"',
            // El encabezado de una linea es marco de la mecanica (redactado);
            // el bloque bajo "---" es cita verbatim de sobre-3.md, sin resumir.
            'body_markdown' => <<<'MD'
                Nuestro equipo de monitoreo de redes detecto actividad relevante
                para el caso en un canal de YouTube. Contenido capturado a
                continuacion, sin editar:

                ---

                ## Canal de YouTube "Torre Blanca"

                Canal: **TORRE BLANCA** (@torreblanca) - 100 suscriptores - 53 videos
                Descripcion: "Todas las noticias resumidas en un solo lugar..."

                Videos destacados:
                - "Steve Jacobs vendio informacion confidencial a cambio de millones. Pruebas claras." (Diciembre 2023)
                - "10 hombres podridos hasta la medula. El mundo estaria mejor sin ellos." (Enero 2024)
                - "Lista de 5,238 victimas afectadas por el fraude de HelixCare" (Marzo 2023)
                MD,
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 60,
            'title' => 'Sobre 3 - Redes, teorias y recibos',
            'source_file' => 'sobres/sobre-3.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 75,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El Game Master habilito el formulario de acusacion final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images from the original case PDF (evidence, press clippings, social
    | media and receipt screenshots) shown next to the verbatim text of each
    | envelope. The files live in public/immersion/steve-jacobs/gallery.
    |
    */

    'gallery' => [
        'sobres/sobre-1.md' => [
            ['file' => 'sobre1-post-policia.jpg', 'caption' => 'Publicación del Departamento de Policía de San Francisco'],
            ['file' => 'sobre1-prensa-suplementos.jpg', 'caption' => 'San Francisco Daily — suplementos adulterados'],
            ['file' => 'sobre1-titulares-articulo.jpg', 'caption' => 'Titulares de prensa y artículo del video del elevador'],
            ['file' => 'sobre1-recortes-prensa.jpg', 'caption' => 'Recortes de prensa'],
            ['file' => 'sobre1-comentarios.jpg', 'caption' => 'Comentarios en redes sociales'],
            ['file' => 'sobre1-autopsia.jpg', 'caption' => 'Reporte de autopsia — Oficina de Medicina Forense'],
            ['file' => 'sobre1-laboratorio.jpg', 'caption' => 'Laboratorio de Ciencias Forenses de San Francisco'],
            ['file' => 'evidencia-1-suplementos.jpg', 'caption' => 'Evidencia 1 — Suplementos manipulados'],
            ['file' => 'evidencia-2-huellas.jpg', 'caption' => 'Evidencia 2 — Huellas dactilares'],
            ['file' => 'evidencia-3-torre-blanca.jpg', 'caption' => 'Evidencia 3 — Pieza de ajedrez (torre blanca)'],
            ['file' => 'evidencia-4-collar.jpg', 'caption' => 'Evidencia 4 — Collar de hombre'],
        ],
        'sobres/sobre-2.md' => [
            ['file' => 'sobre2-prensa-helixcare.jpg', 'caption' => 'San Francisco Daily — demanda contra HelixCare'],
        ],
        'sobres/sobre-3.md' => [
            ['file' => 'sobre3-tweets.jpg', 'caption' => 'Publicaciones en X (Twitter)'],
            ['file' => 'sobre3-youtube-torreblanca.jpg', 'caption' => 'Canal de YouTube "Torre Blanca"'],
            ['file' => 'sobre3-recibo-chessmith.jpg', 'caption' => 'Recibo de pedido de ajedrez — Chessmith'],
            ['file' => 'sobre3-recibos-varios.jpg', 'caption' => 'Recibo de bar, publicación de Facebook, Uber y taxi'],
            ['file' => 'sobre3-registro-rada.jpg', 'caption' => 'Registro de entrada y salida — Estudio de Ensayo de RADA'],
            ['file' => 'sobre3-correo-cctv.jpg', 'caption' => 'Correo — Material solicitado de CCTV'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Headings already shown as images
    |--------------------------------------------------------------------------
    |
    | "## ..." section titles that the gallery already covers, so the text
    | version is omitted and the same content is not repeated as both text and
    | photo. The original .md files are never modified: this only affects the
    | rendered output the player sees (email and inbox).
    |
    */

    'gallery_excluded_headings' => [
        'sobres/sobre-1.md' => [
            'Reporte de Investigación',
            'Laboratorio de Ciencias Forenses',
            'Etiquetas de evidencia',
            'Recorte de prensa — San Francisco Daily (02 Febrero',
            'Titulares de prensa',
            'Artículo — Steve Jacobs, captado en video',
            'Publicación en redes sociales — Departamento de Policía',
            'Comentarios en redes sociales',
        ],
        'sobres/sobre-2.md' => [
            'Recorte de prensa — San Francisco Daily (09 Septiembre',
        ],
        'sobres/sobre-3.md' => [
            'Publicaciones en X (Twitter)',
            'Canal de YouTube',
            'Recibo de pedido de ajedrez',
            'Recibo de compra — Bar Prince Albert',
            'Publicación en Facebook',
            'Captura de app de transporte',
            'Recibo — San Francisco Taxi VIP',
            'Registro de Entrada y Salida',
            'Correo electrónico — Material solicitado de CCTV',
        ],
    ],

];
