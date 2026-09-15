<?php

/**
 * Case manifest: "Desaparecida en Directo"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: every piece of evidence that "proves" Naiara planned to
 * disappear voluntarily (the suitcase, the cash withdrawal, the chat with Carla
 * about the cabin) is real and stays real from start to finish. The mystery is
 * not whether the plan existed — it did — but whether she ever got to execute it
 * herself. No confrontation should ever suggest the plan itself was fabricated.
 *
 * Three gaps in the source design brief were fixed during authoring rather than
 * reproduced, same precedent as habitacion-314 fixing its own scheduling gaps:
 *
 *  - The brief's evidence list (V1, V2, V3, V8, V11, V14, V15, audio A2) was
 *    never actually scheduled anywhere in its own delivery timeline. Rescheduled
 *    here: V1/V2 alongside V9 at T+0, V3/V8 alongside V4/V5 at T+8, A2 at T+16,
 *    V11 alongside V6 at T+28, and V14/V15 alongside V12/V13 at T+50.
 *  - "Every player gets a fragment of the cabin chat" is not a delivery mode the
 *    engine supports — only `random_player` (one recipient per timeline entry)
 *    exists. Resolved the same way habitacion-314 resolved its own two-clue T+20
 *    slot: four separate `random_player` entries at T+16, one per chat fragment.
 *  - Darío's confrontation was specified in the brief as unlocking at T+28 together
 *    with V6 and V10 — but V10 (the network metadata) is not delivered until the
 *    Giro at T+40. The real gate, encoded in content/suspects/dario-quintana.md,
 *    is V6 AND V10 AND Paula's testimony, which is only ever complete from T+40
 *    onward.
 */
return [

    'name' => 'Desaparecida en Directo',
    'version' => '1.0',
    'code' => 'Expediente privado — Estudio Lumen',
    'authority' => 'Producción del evento, Estudio Lumen',

    'victim' => [
        'name' => 'Naiara Robles',
        'photo' => 'naiara-robles.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The brief asks for 5 preguntas por sospechoso, 4 por testigo. The
        // engine only supports one flat number per case
        // (CaseDefinition::interrogationQuestions()), same precedent as the
        // other four cases.
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
        'tagline' => 'Ella sí planeaba desaparecer. Eso es verdad. Lo que no está probado es que lo hiciera sola.',

        'description' => <<<'TXT'
            Naiara Robles, creadora de contenido en ascenso, corta abruptamente una
            transmisión en directo durante un evento privado para creadores y marcas.
            Cuarenta minutos después, su cuenta publica un mensaje anunciando que
            necesita alejarse unos días. Toda la evidencia de que ella planeaba
            desaparecer es auténtica: una maleta lista, un retiro de efectivo, una
            conversación real con su mejor amiga sobre una cabaña donde desconectar.

            Tu equipo (4 a 6 investigadores, ideal 5) recibe el expediente en tiempo
            real y podrá interrogar a cinco personas cercanas a Naiara y a dos
            testigos del evento, uno por uno, mientras el reloj corre hacia la
            acusación final.

            El giro central: nada prueba que Naiara llegara a ejecutar su propio
            plan. La mesa pasará la primera mitad de la partida construyendo la
            teoría más razonable —se fue por su cuenta— y la segunda mitad
            entendiendo que esa nunca fue la pregunta correcta: quién estuvo con
            ella entre el final del directo y el mensaje que la "explicó" ante el
            público.
            TXT,

        // Archivo esperado en public/immersion/desaparecida-en-directo/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'medium',
        'duration_minutes' => 60,
        'min_players' => 4,
        'max_players' => 6,

        // PLACEHOLDER: precio sugerido, pendiente de confirmar con el usuario.
        // Ver entrega de precios en la conversación: 59,900 COP (interpolado por
        // duración entre el-brindis-22-14 y steve-jacobs) vs. 64,900 COP
        // (alineado con la complejidad mecánica de habitacion-314/proyecto-boreal).
        'price_amount' => 59900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 5,
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
        'dario-quintana' => [
            'name' => 'Darío Quintana',
            'role' => 'sospechoso',
            'file' => 'suspects/dario-quintana.md',
            'photo' => 'dario-quintana.png',
            'connection' => 'Mánager de Naiara desde hace cinco años',
            'motive' => 'Comisión personal decisiva ligada a un contrato de exclusividad, y deudas propias contraídas contra ella',
            'alibi' => 'Dice haber hablado con ella un momento y haberse ido; el registro de accesos lo contradice',
        ],
        'elisa-montes' => [
            'name' => 'Elisa Montes',
            'role' => 'sospechosa',
            'file' => 'suspects/elisa-montes.md',
            'photo' => 'elisa-montes.png',
            'connection' => 'Socia de negocio de Naiara',
            'motive' => 'Dependencia financiera del negocio conjunto (aparente)',
            'alibi' => 'En una reunión de inversores durante toda la ventana crítica, confirmada por varios asistentes',
        ],
        'tomas-egea' => [
            'name' => 'Tomás Egea',
            'role' => 'sospechoso',
            'file' => 'suspects/tomas-egea.md',
            'photo' => 'tomas-egea.png',
            'connection' => 'Representante de la marca patrocinadora',
            'motive' => 'Presión corporativa por el contrato de exclusividad (aparente)',
            'alibi' => 'En la zona de patrocinadores del evento, confirmado por el personal',
        ],
        'bruno-casal' => [
            'name' => 'Bruno Casal',
            'role' => 'sospechoso',
            'file' => 'suspects/bruno-casal.md',
            'photo' => 'bruno-casal.png',
            'connection' => 'Expareja de Naiara, también creador de contenido',
            'motive' => 'Historial de mensajes controladores (aparente)',
            'alibi' => 'Entrada registrada a las 22:35, posterior a la publicación del mensaje',
        ],
        'carla-nuno' => [
            'name' => 'Carla Nuño',
            'role' => 'sospechosa',
            'file' => 'suspects/carla-nuno.md',
            'photo' => 'carla-nuno.png',
            'connection' => 'Asistente y mejor amiga de Naiara',
            'motive' => 'Ninguno; ayudó a planear el traslado real',
            'alibi' => 'Esperando en el punto de encuentro acordado, vista por Paula',
        ],
        'ivan-soler' => [
            'name' => 'Iván Soler',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/ivan-soler.md',
            'photo' => 'ivan-soler.png',
            'connection' => 'Jefe técnico del evento',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'paula-rey' => [
            'name' => 'Paula Rey',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/paula-rey.md',
            'photo' => 'paula-rey.png',
            'connection' => 'Otra creadora asistente al evento',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'sistema-evento' => [
            'name' => 'Sistema del evento y de la plataforma de streaming',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-evento.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as habitacion-314's "sistema-hotel")
            // — una captura del panel de accesos/streaming funciona como su
            // "retrato".
            'photo' => 'sistema-evento.png',
            'connection' => 'Registros objetivos del evento (accesos, streaming, publicaciones)',
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
        'culprit_slug' => 'dario-quintana',

        'headline' => 'Darío Quintana interceptó a Naiara en el corredor de servicio, la coaccionó para que no se fuera, y usó su propio teléfono para publicar el mensaje que explicó su ausencia ante el público.',

        'motive' => 'Una comisión personal decisiva ligada a un contrato de exclusividad que debía firmarse esa misma semana, y deudas propias contraídas contra esa comisión futura.',

        'method' => 'Interceptación en el corredor de servicio justo después del directo, coacción para que no se fuera, y control de su teléfono para publicar un mensaje en su nombre y así ganar tiempo sin alarmar a nadie.',

        'key_evidence' => [
            'El registro de accesos sitúa a Darío entrando al corredor de servicio justo un minuto después que Naiara, confirmado también por Iván Soler.',
            'Paula Rey vio a Naiara subir al coche de Darío con gesto tenso, dudando, mirando hacia atrás dos veces.',
            'El mensaje público no encaja con el estilo real de Naiara: su plan, según Carla, era discreto y privado, nunca un anuncio público.',
            'Los metadatos de conexión de red del mensaje no coinciden con la ruta hacia la cabaña acordada con Carla.',
            'Los mensajes de Darío a Tomás esa noche describen gestión activa de una crisis, no preocupación pasiva.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not have
         * intervened. One entry per suspect that is not the culprit, including
         * both witnesses and the secondary source.
         */
        'exonerations' => [
            'elisa-montes' => 'Su coartada la ubica en la reunión de inversores durante toda la ventana crítica, confirmada por varios asistentes. Su desesperación financiera es real —hipotecó su apartamento por el negocio conjunto— pero nunca se tradujo en una acción contra Naiara; de hecho, es la persona con más que perder si el negocio se hunde por su desaparición.',

            'tomas-egea' => 'Estuvo en la zona de patrocinadores durante toda la ventana crítica, confirmado por el personal del evento. Presionó a Darío en los días previos por la cláusula de penalización que enfrentaba su empresa, pero esa presión fue indirecta y por canales normales de negocio — nunca tuvo contacto directo con Naiara esa noche.',

            'bruno-casal' => 'El registro de entrada lo sitúa llegando al evento a las 22:35, después de que el mensaje público ya se había publicado a las 22:20. Su historial de mensajes controladores es real pero superado; de hecho, buscaba reconciliar la amistad con Naiara tras un proceso personal de cambio, no seguir buscándola.',

            'carla-nuno' => 'Fue vista por Paula esperando en el punto de encuentro acordado, cada vez más preocupada al ver que Naiara nunca llegaba — es, de hecho, la persona con la información más completa y veraz sobre el plan real. Su único secreto es un desvío menor de fondos del presupuesto de gastos, sin relación alguna con la desaparición.',

            'ivan-soler' => 'Es el jefe técnico del evento, no un sospechoso: su único aporte es lo que vio desde su puesto (a Naiara salir hacia el corredor, a Darío seguirla un minuto después). No tuvo motivo ni oportunidad de intervenir él mismo.',

            'paula-rey' => 'Es una asistente al evento sin relación estrecha con Naiara: su aporte es lo que vio esa noche (a Naiara subir tensa al coche de Darío, a Carla esperando preocupada). No tuvo motivo ni oportunidad, y fue precisamente su testimonio el que ayudó a situar a Darío en el lugar correcto.',

            'sistema-evento' => 'Es un sistema automatizado de registro, no una persona: no tiene forma de haber intervenido ni motivo alguno. Su función esa noche fue registrar con exactitud la hora del corte de transmisión, los accesos al corredor de servicio, y los metadatos del mensaje público — precisamente el dato que, bien leído, desarma la lectura de que Naiara se fue por su cuenta.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Orus',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from the
        // table, it never composes it.
        'confession_script' => <<<'TXT'
            La seguí porque no podía dejar que se fuera así, no esa noche. Le dije
            que lo entendía, que la apoyaba, pero que necesitaba solo un par de
            días más para resolver lo del contrato. Ella no quería, se lo dije de
            una forma que no debí.

            Le tomé el teléfono. Escribí yo ese mensaje, no ella. Pensé que así
            nadie se alarmaría y yo tendría tiempo.

            Está bien, está a salvo, se lo juro. Pero no debí hacer lo que hice.
            Se me fue de las manos.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design brief's T+0/T+8/T+16/T+28/T+40/T+50/T+60
    | delivery plan into trigger_offset_minutes (T+0 becomes minute 1, same
    | convention as the other four cases). See the authoring note at the top of
    | this file for the three scheduling gaps fixed here.
    |
    | `evidence_codes` opts this case into "Level 2": an interrogation can check
    | a player's claim about a piece of evidence against what this game has
    | actually delivered so far (see Game::deliveredEvidenceCodes()).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Desaparecida en Directo — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V2', 'V9'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — los últimos segundos del directo',

            // Recorded outside the platform and shipped with the case: the same
            // clip for every table.
            'audio_file' => 'a1-corte-directo.wav',
            'audio_scene' => 'Últimos segundos de una transmisión en directo, justo antes de cortarse. Sonido ambiente de evento de producción audiovisual de fondo.',
            'audio_context' => 'Naiara, cansada, intenta mantener la compostura frente al público justo antes de cortar abruptamente el directo.',
            'audio_speaker' => 'Speaker 1 - Naiara Robles',
            'audio_voice' => 'Leda',
            'audio_script' => <<<'TXT'
                Chicos, perdón, tengo que... necesito parar un segundo. Ahora
                vuelvo, ¿vale? Ahora vuelvo.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 8,
            'title' => 'Expansión — sospechosos, testigos y el plan que empieza a asomar',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V3', 'V4', 'V5', 'V8'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 16,
            'title' => 'Fragmento privado — chat con Carla (1/4)',
            'source_file' => 'pista-privada-1.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V7'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 16,
            'title' => 'Fragmento privado — chat con Carla (2/4)',
            'source_file' => 'pista-privada-2.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V7'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 16,
            'title' => 'Fragmento privado — chat con Carla (3/4)',
            'source_file' => 'pista-privada-3.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V7'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 16,
            'title' => 'Fragmento privado — chat con Carla (4/4)',
            'source_file' => 'pista-privada-4.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V7'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 16,
            'title' => 'Mensaje de voz privado — Naiara a Carla',

            'audio_file' => 'a2-mensaje-carla.wav',
            'audio_scene' => 'Mensaje de voz privado, ambiente doméstico tranquilo de fondo, dirigido a una amiga cercana. Grabado días antes del evento.',
            'audio_context' => 'Naiara le pide ayuda discreta a Carla con un plan ya hablado previamente: alejarse unos días a una cabaña.',
            'audio_speaker' => 'Speaker 1 - Naiara Robles',
            'audio_voice' => 'Leda',
            'audio_script' => <<<'TXT'
                Carla, en serio, creo que necesito parar unos días. No sé si voy
                a volver como antes, la verdad, pero necesito intentarlo primero
                sin decírselo a nadie más. ¿Me ayudas con lo de la cabaña, como
                hablamos?
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 28,
            'title' => 'Complicación — el registro de accesos',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V6', 'V11'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 40,
            'title' => 'Giro — el mensaje no prueba lo que parece probar',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V10'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Cierre — coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V12', 'V13', 'V14', 'V15'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 60,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para reconstruir lo que ocurrió entre el final del directo y el último mensaje — y para entender que la pregunta correcta nunca fue "¿se fue por su cuenta?", sino "¿quién estuvo con ella?". Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/desaparecida-en-directo/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-fotograma-corte.png', 'caption' => 'Fotograma del livestream, justo antes del corte'],
            ['file' => 'v2-transmision-finalizada.png', 'caption' => 'Captura de "transmisión finalizada" — 21:40'],
            ['file' => 'v9-mensaje-publico.jpg', 'caption' => 'El mensaje público final publicado en su cuenta'],
        ],
        'expansion.md' => [
            ['file' => 'v3-ambiente-evento.png', 'caption' => 'Ambiente del evento privado para creadores y marcas'],
            ['file' => 'v4-camerino-maleta.png', 'caption' => 'El camerino de Naiara, con la maleta ya preparada'],
            ['file' => 'v5-plano-estudio-lumen.png', 'caption' => 'Plano funcional del Estudio Lumen'],
            ['file' => 'v8-recibo-retiro.png', 'caption' => 'Recibo de retiro de efectivo, dos días antes'],
        ],
        'pista-privada-1.md' => [
            ['file' => 'v7-chat-cabana.png', 'caption' => 'Chat con Carla sobre el plan real de la cabaña'],
        ],
        'pista-privada-2.md' => [
            ['file' => 'v7-chat-cabana.png', 'caption' => 'Chat con Carla sobre el plan real de la cabaña'],
        ],
        'pista-privada-3.md' => [
            ['file' => 'v7-chat-cabana.png', 'caption' => 'Chat con Carla sobre el plan real de la cabaña'],
        ],
        'pista-privada-4.md' => [
            ['file' => 'v7-chat-cabana.png', 'caption' => 'Chat con Carla sobre el plan real de la cabaña'],
        ],
        'complicacion.md' => [
            ['file' => 'v6-registro-accesos.png', 'caption' => 'Registro de accesos al corredor de servicio'],
            ['file' => 'v11-mensajes-bruno.png', 'caption' => 'Mensajes antiguos de Bruno Casal (más de un año)'],
        ],
        'giro.md' => [
            ['file' => 'v10-metadatos-mensaje.png', 'caption' => 'Metadatos técnicos del mensaje público'],
        ],
        'cierre.md' => [
            ['file' => 'v12-mensajes-dario-tomas.png', 'caption' => 'Mensajes de Darío a Tomás esa noche'],
            ['file' => 'v13-contrato-comision.png', 'caption' => 'Fragmento del contrato de exclusividad y comisión'],
            ['file' => 'v14-objeto-coche.png', 'caption' => 'Objeto personal de Naiara hallado en el coche de Darío'],
            ['file' => 'v15-registro-entrada-bruno.png', 'caption' => 'Registro de entrada tardía de Bruno Casal'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the table
        // can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v6-registro-accesos.png', 'caption' => 'Darío, un minuto detrás de Naiara'],
            ['file' => 'v10-metadatos-mensaje.png', 'caption' => 'La red que no lleva a la cabaña'],
            ['file' => 'v9-mensaje-publico.jpg', 'caption' => 'El mensaje que Naiara nunca habría publicado así'],
        ],
    ],
];
