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
    | Every claim below is anchored in material the table already received: the
    | forensic report and the chess piece (sobre 1), the nine testimonies and
    | Sarah Collins' access log (sobre 2), the Chessmith receipt, the CCTV mail
    | and the "Torre Blanca" channel (sobre 3). Nothing here is new evidence.
    |
    */

    'solution' => [
        // Must be a key of 'suspects' above.
        'culprit_slug' => 'rachel-miller',

        'headline' => 'La amiga de la esposa. Una dentista con acceso a digitoxina, un uniforme robado y una torre blanca por firma.',

        'motive' => 'Justicia por mano propia. HelixCare retrasó y negó tratamientos críticos hasta que la gente se murió esperando, y ningún ejecutivo renunció ni pidió perdón. Rachel decidió cobrar esa cuenta ella misma, y Steve Jacobs no fue el primer nombre de su lista.',

        'method' => 'Con un uniforme de empleada y una tarjeta maestra robados del propio hotel, entró a la habitación 803 a las 9:12 p.m. — mientras Steve seguía en la gala — y cambió con guantes las cápsulas de su frasco de suplementos por otras cargadas con digitoxina. Dejó la torre blanca en el lavabo y se fue antes de que él subiera.',

        'key_evidence' => [
            'Su coartada se cae sola: Rachel declaró que estuvo en casa viendo una película, pero Elizabeth Foster la vio pasar por la calle lateral del hotel esa noche. El correo de CCTV confirma que Elizabeth no se bajó del coche entre las 7:06 y las 10:02 p.m., así que la testigo estaba exactamente donde dice, mirando exactamente hacia allá.',
            'Sarah Collins reportó un uniforme de empleada y una tarjeta maestra desaparecidos, que nunca se recuperaron. Con esa tarjeta maestra alguien entró a la 803 a las 9:12 p.m., cuando Steve todavía estaba en la cena y Emily Johnson ya había hecho la preparación nocturna a las 6:03 p.m.',
            'Rachel es dentista con práctica privada: la única persona del expediente con acceso legal a compuestos controlados, y por lo tanto a digitoxina, sin dejar detrás un rastro de compra.',
            'La llamada del supuesto "equipo de seguridad" a las 8:45 p.m. sacó a Jeremy Burt de la recepción trasera para revisar el sótano — quince minutos antes de la entrega del ajedrez, pedida para las 9:00 p.m. en esa misma puerta. Nadie del hotel hizo esa llamada.',
            'El pedido a Chessmith a nombre de "Robin Good", pagado con AnonyPay tras una VPN, con la instrucción de incluir solo las piezas blancas y entregarlo en la entrada trasera del Hotel Altamira.',
            '«Estaba podrido hasta la médula», dijo Rachel en su interrogatorio, sin que nadie se lo preguntara. Es palabra por palabra el título del video del canal Torre Blanca: «10 hombres podridos hasta la médula. El mundo estaría mejor sin ellos».',
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
            'elizabeth-foster' => 'Estuvo ahí toda la noche, y por eso mismo no pudo ser ella. El correo de CCTV de Sarah Collins documenta su Bentley estacionado a 100 metros de la entrada desde las 7:06 hasta las 10:02 p.m., y deja constancia de que el conductor no salió del auto en ningún momento. Elizabeth nunca entró al Hotel Altamira. La celosa del expediente resultó ser la testigo que, sin saberlo, entregó al asesino.',

            'sofia-reyes' => 'Nunca pasó del vestíbulo. Daniel Blake la interceptó a las 10:00 p.m. y el recibo del San Francisco Taxi VIP la saca del hotel a las 10:05 p.m. rumbo a South Beach. Para entonces las cápsulas llevaban casi una hora cambiadas: la habitación ya estaba envenenada antes de que ella cruzara la puerta. Y estaba embarazada de él — lo que quería esa noche era una respuesta, no un cadáver.',

            'lucas-jacobs' => 'El registro de entrada y salida del estudio de RADA lo tiene ensayando de 19:01 a 22:00, con siete compañeros firmando el mismo turno, y el recibo del bar Prince Albert lo pone en Mission a las 22:24. El collar con sus iniciales estaba en esa habitación porque él mismo se lo devolvió a su padre en el desayuno de esa mañana y a Steve se le cayó del bolsillo. Tenía motivo y herencia; no tuvo un solo minuto de oportunidad.',

            'emily-johnson' => 'Su tarjeta abrió la 803 a las 6:03 p.m. para la preparación nocturna, tres horas antes de que las cápsulas fueran cambiadas, y terminó su turno a las 11:00 p.m. El uniforme y la tarjeta maestra que desaparecieron del hotel son precisamente la prueba de lo contrario: Emily no fue la asesina, fue la persona a la que suplantaron. Encontrar el cuerpo fue el turno que le tocó, no una confesión.',

            'kevin-huang' => 'Tenía el motivo más limpio del expediente: su padre murió esperando una autorización que HelixCare nunca firmó. Y tiene la coartada más completa. El Uber lo saca del hotel a las 19:46 hacia el St. Thomas Hospital y su propia publicación de Facebook, fechada ese 4 de febrero, lo muestra ahí con su hija recién nacida. Mientras Steve Jacobs moría, Kevin estaba siendo padre.',

            'daniel-blake' => 'Es culpable, pero de otra cosa. Falsificó registros clínicos por orden de Steve y repartía estimulantes sin receta entre el personal de HelixCare — por eso sus huellas están en el frasco que dejó en esa habitación días antes, y por eso hay una investigación separada abierta a su nombre. Lo que no tenía era acceso a digitoxina ni una sola razón para matar al socio que sostenía su imperio. Traicionó a Steve en los papeles; no lo envenenó.',

            'sarah-collins' => 'Es la razón por la que este caso se resolvió. Notó el uniforme y la tarjeta maestra faltantes cuando nadie se lo había pedido, revisó por iniciativa propia el registro de accesos de la 803, y entregó a la policía tanto la hora exacta de la entrada de las 9:12 p.m. como el material de CCTV. Un cómplice no aporta la cronología que condena a quien lo encubre.',

            'jeremy-burt-testigo' => 'Fue usado. La llamada de las 8:45 p.m. pidiéndole revisar las salidas de emergencia del sótano —una tarea que él mismo declaró que nunca le asignan— existió únicamente para dejar la recepción trasera vacía cuando llegara el ajedrez. Que no registrara ninguna entrega no fue negligencia: la entrega estaba diseñada desde el principio para no quedar registrada.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name; null
        // falls back to the gateway's default.
        'confession_voice' => 'Kore',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud, it never composes it.
        'confession_script' => <<<'TXT'
            ¿Sabe qué es lo que más me molesta, detective? Que me pregunte por mi
            integridad.

            Yo he pasado veinte años con las manos dentro de la boca de gente
            aterrada, diciéndoles que respiren, que ya casi. Firmo lo que receto.
            Respondo por cada cosa que hago. Steve Jacobs firmó cinco mil
            doscientas treinta y ocho negaciones de cobertura y no respondió por
            ninguna. Veintiséis personas se murieron esperando un correo suyo.
            Pagó trescientos cuarenta y dos millones, no admitió nada, y esa
            misma noche estaba bailando en un salón de gala.

            Sí. Fui yo.

            Le abrí la puerta con la tarjeta que me llevé del carrito de la
            lavandería, con el uniforme de ellos puesto, y nadie me miró la cara
            ni una vez. Así de invisible es una mujer con un uniforme de
            empleada. Cambié las cápsulas de su frasco, dejé la torre en el
            lavabo y bajé por las escaleras.

            No corrí. No tenía por qué.

            La torre blanca no es un juego. La torre es la pieza que protege al
            rey y nunca se mueve en diagonal: va de frente, hasta el final del
            tablero. Steve fue el cuarto. No voy a decirle cuántos faltan.

            Y no me llame asesina como si eso cerrara algo. Los verdaderos
            culpables son los que sostienen el sufrimiento sin que nadie los
            castigue. Yo solo dejé de esperar a que alguien más lo hiciera.

            Dígale a Elizabeth que lo siento. Ella no merecía enterarse así.
            TXT,
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

        // The reveal re-shows the four pieces of evidence that convict, so the
        // table can see the chain instead of taking the detective's word.
        'solucion.md' => [
            ['file' => 'evidencia-3-torre-blanca.jpg', 'caption' => 'La firma — torre blanca dejada en el lavabo de la 803'],
            ['file' => 'sobre3-recibo-chessmith.jpg', 'caption' => 'El pedido de "Robin Good" — solo piezas blancas, entrega trasera, 9:00 p.m.'],
            ['file' => 'sobre3-youtube-torreblanca.jpg', 'caption' => 'Canal "Torre Blanca" — "10 hombres podridos hasta la médula"'],
            ['file' => 'sobre3-correo-cctv.jpg', 'caption' => 'CCTV — Elizabeth Foster nunca salió de su coche'],
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
