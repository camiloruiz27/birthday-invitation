<?php

/**
 * Case manifest: "La Última Función"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this case's twist is dates, not lies. Laura, Carla and
 * Mario's testimonies about "that night" are sincere — they describe the
 * dress-rehearsal-with-press two nights earlier, not tonight, because both
 * nights followed an almost identical routine. The AI for each of them must
 * internally track which night a given memory belongs to and only reveal the
 * correct date once confronted with the right dating evidence (V7/V8/V9/V10,
 * Pablo's or the technical log's testimony) — never by spontaneous "convenient
 * amnesia" and never before that confrontation.
 *
 * Gap fixed during authoring, same precedent as kilometro-186 and
 * tres-minutos-de-silencio before it: the source design brief's own evidence
 * catalog (section 7) lists 13 visual pieces (V1-V13), but its delivery
 * timeline (section 6) never schedules V11 (mensaje del contrato de cine) or
 * V12 (hoja de llamada / plan de escenas). Rescheduled here:
 *  - V12 alongside V2/V7 at T+13 — it belongs with the other production
 *    documents that first frame tonight's schedule.
 *  - V11 alongside V6/V9/V10/A2 at T+52 (Giro) — the brief's own deduction
 *    graph (N5) uses it as one of the two supports for Mario's real motive,
 *    delivered right where the group reconstructs his lie.
 *
 * V11 is also the evidence the brief's own JSON export gates Mario's
 * confrontación 2 on; it must be in hand by T+52, which this scheduling
 * satisfies exactly.
 */
return [

    'name' => 'La Última Función',
    'version' => '1.0',
    'code' => 'Expediente interno — Teatro Auriga',
    'authority' => 'Teatro Auriga — Dirección de Producción',

    'victim' => [
        'name' => 'Héctor Delgado',
        'photo' => 'hector-delgado.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // El brief pide 4 preguntas por sospechoso, hasta 2 confrontaciones
        // por sospechoso. El motor solo soporta un numero plano por caso
        // (CaseDefinition::interrogationQuestions()), mismo precedente que
        // los demas casos.
        'interrogation_questions' => 4,
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
        'tagline' => 'El trofeo de la caja no es el que debería estar ahí. Y nadie recuerda bien qué noche es esta.',

        'description' => <<<'TXT'
            Héctor Delgado, protagonista de "El Círculo", muere entre escenas
            durante la última función de la temporada, en el corredor junto a
            la sala de utilería. Algo en la caja no cuadra: el objeto que debía
            estar ahí ha sido sustituido por otro, mucho más pesado, que
            normalmente se exhibe en una vitrina del teatro.

            Tu equipo (4 a 7 investigadores, ideal 5) recibe el expediente en
            tiempo real y podrá interrogar a cinco sospechosos y a dos
            testigos, uno por uno, mientras se reconstruye la función de esta
            noche minuto a minuto.

            Varios recuerdos sinceros no encajan del todo entre sí. El reto no
            es descubrir quién miente — es aprender a fechar correctamente cada
            recuerdo antes de acusar.
            TXT,

        // Archivo esperado en public/immersion/la-ultima-funcion/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'medium',
        'duration_minutes' => 65,
        'min_players' => 4,
        'max_players' => 7,

        // PLACEHOLDER: precio sugerido, pendiente de confirmar con el usuario.
        // 59,900 COP iguala el nivel medium/60-65min de desaparecida-en-directo.
        'price_amount' => 59900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 8,
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
        'mario-calvo' => [
            'name' => 'Mario Calvo',
            'role' => 'sospechoso',
            'file' => 'suspects/mario-calvo.md',
            'photo' => 'mario-calvo.png',
            'connection' => 'Director de la obra',
            'motive' => 'Héctor le confesó esa misma noche que ya había firmado un contrato de cine y que esta era, de verdad, su última función con la compañía',
            'alibi' => 'Dice haber estado todo el tiempo en la cabina de dirección coordinando la función',
        ],
        'santi-robles' => [
            'name' => 'Santi Robles',
            'role' => 'sospechoso',
            'file' => 'suspects/santi-robles.md',
            'photo' => 'santi-robles.png',
            'connection' => 'Utilero de la producción',
            'motive' => 'Olvidó devolver el trofeo real a la vitrina tras el ensayo con prensa (error real, sin relación con la muerte)',
            'alibi' => 'En la sala de utilería revisando el resto del atrezo, visto por otros técnicos durante la ventana crítica',
        ],
        'laura-mendizabal' => [
            'name' => 'Laura Mendizábal',
            'role' => 'sospechosa',
            'file' => 'suspects/laura-mendizabal.md',
            'photo' => 'laura-mendizabal.jpg',
            'connection' => 'Coprotagonista de la obra',
            'motive' => 'Resentimiento por un crédito de guion no reconocido (real, sin relación con la muerte); su coartada es un recuerdo sincero mal fechado',
            'alibi' => 'En su propio camerino esta noche durante la ventana crítica, sin coartada de terceros hasta aclarar la confusión de fechas',
        ],
        'carla-iturri' => [
            'name' => 'Carla Iturri',
            'role' => 'sospechosa',
            'file' => 'suspects/carla-iturri.md',
            'photo' => 'carla-iturri.png',
            'connection' => 'Actriz secundaria',
            'motive' => 'Relación secreta con Héctor (real, sin relación con la muerte); su testimonio sobre Santi es un recuerdo sincero mal fechado',
            'alibi' => 'En su camerino y luego brevemente con Héctor antes del cambio de vestuario, sin llegar a la ventana exacta del ataque',
        ],
        'gustavo-pena' => [
            'name' => 'Gustavo Peña',
            'role' => 'sospechoso',
            'file' => 'suspects/gustavo-pena.md',
            'photo' => 'gustavo-pena.png',
            'connection' => 'Productor del teatro',
            'motive' => 'Negocia en secreto vender su participación en la producción (real, sin relación con la muerte)',
            'alibi' => 'En una reunión con patrocinadores desde las 21:00 hasta las 22:00, confirmable por varios asistentes externos',
        ],
        'pablo-soria' => [
            'name' => 'Pablo Soria',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/pablo-soria.md',
            'photo' => 'pablo-soria.png',
            'connection' => 'Regidor de escena',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'noelia-bravo' => [
            'name' => 'Noelia Bravo',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/noelia-bravo.md',
            'photo' => 'noelia-bravo.png',
            'connection' => 'Asistente de camerinos',
            'motive' => null,
            'alibi' => null,
            'accusable' => false,
        ],
        'registro-tecnico-funcion' => [
            'name' => 'Registro técnico de la función / hoja de llamadas de regiduría',
            'role' => 'fuente secundaria (registro documental)',
            'file' => 'suspects/registro-tecnico-funcion.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as kilometro-186's "sistema-tren").
            'photo' => 'registro-tecnico-funcion.png',
            'connection' => 'Cues y llamadas técnicas registradas exclusivamente para la función de esta noche',
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
        'culprit_slug' => 'mario-calvo',

        'headline' => 'Durante el cambio de vestuario y utilería antes de la escena final de esta noche, Mario Calvo confrontó a Héctor Delgado en el corredor junto a la sala de utilería por su salida repentina de la compañía, y lo golpeó con el trofeo real que —por un error de Santi de dos noches antes— seguía en la caja de utilería en lugar de la réplica.',

        'motive' => 'Esa misma noche, antes de la función, Héctor le confesó a Mario que ya había firmado un contrato de cine y que esta era, en efecto, su última función con la compañía, sin previo aviso formal.',

        'method' => 'La discusión escaló en el corredor de bambalinas; Mario vio el trofeo real, pesado y sólido, en la caja abierta junto a ellos, y lo golpeó con él en un arrebato.',

        'key_evidence' => [
            'El trofeo hallado en la caja de utilería es el real, no la réplica que la hoja de utilería de esta noche describe.',
            'Santi admite que olvidó devolver el trofeo real a la vitrina tras el ensayo general con prensa de dos noches antes.',
            'El registro técnico de esta noche muestra un hueco en las confirmaciones de cue de Mario durante la ventana crítica.',
            'Pablo confirma que Mario no respondió a sus llamadas de cue durante varios minutos, algo inusual en él.',
            'El vestuario nuevo de Héctor, estrenado la noche del ensayo con prensa, ancla con precisión que los recuerdos de Laura y Carla pertenecen a esa otra noche.',
            'Un mensaje confirma que Héctor firmó el contrato de cine esa misma tarde, dando a Mario un motivo urgente.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not have
         * been responsible. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source.
         */
        'exonerations' => [
            'santi-robles' => 'Estuvo en la sala de utilería revisando el resto del atrezo, visto por otros técnicos durante toda la ventana crítica. Su falta real es haber olvidado devolver el trofeo real a la vitrina tras el ensayo con prensa, un descuido honesto de dos noches antes, sin relación con el momento del ataque.',

            'laura-mendizabal' => 'Su recuerdo de repasar el diálogo del trofeo con Héctor en su camerino es sincero, pero pertenece al ensayo con prensa de dos noches antes, no a esta noche — algo que ella misma reconoce al ser confrontada con las fechas y el vestuario. Su único secreto real es el resentimiento por un crédito de guion no reconocido, sin relación con la muerte.',

            'carla-iturri' => 'Su recuerdo de ver a Santi puliendo el trofeo pertenece igualmente al ensayo con prensa, no a esta noche. Su secreto real es una relación en privado con Héctor, que temía que saliera a la luz, sin relación alguna con su muerte.',

            'gustavo-pena' => 'Estuvo en una reunión con patrocinadores desde las 21:00 hasta las 22:00, sin salir en ningún momento, confirmado por varios asistentes externos, sin ambigüedad de fechas. Su secreto real es una negociación en secreto para vender su participación en la producción, sin relación con la muerte de Héctor.',

            'pablo-soria' => 'Es el regidor de escena, sin relación alguna con Héctor más allá del trabajo técnico: no tiene motivo ni oportunidad. Su aporte fue justamente haber notado el hueco en las confirmaciones de cue de Mario durante la ventana crítica.',

            'noelia-bravo' => 'Es la asistente de camerinos, sin relación alguna con Héctor más allá del trabajo del teatro: no tiene motivo ni oportunidad. Su aporte fue haber oído voces alzadas cerca de la sala de utilería y haber notado que Héctor llevaba el vestuario estrenado la noche del ensayo con prensa, ayudando a fechar correctamente los hechos.',

            'registro-tecnico-funcion' => 'Es un registro documental, no una persona: no tiene forma de haber intervenido ni motivo alguno. Su función esa noche fue registrar con exactitud los cues y llamadas técnicas de la función de esta noche específicamente, sin mezclar datos de otras funciones — el hueco que muestra en las confirmaciones de Mario es uno de los apoyos centrales de la solución.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from the
        // table, it never composes it.
        'confession_script' => <<<'TXT'
            Me lo dijo así, sin más, minutos antes de salir a escena. Que ya
            había firmado la película, que esta función era literalmente la
            última vez que se subía a este escenario.

            Le pedí que habláramos con calma, que no podía irse así, después de
            todo lo que habíamos construido juntos. Me dijo que ya estaba
            decidido.

            Vi el trofeo ahí, en la caja, y no lo pensé. Fue un segundo.
            Después ya no hubo vuelta atrás.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design brief's T+0/T+13/T+26/T+39/T+52/T+65 delivery
    | plan into trigger_offset_minutes (T+0 becomes minute 1, same convention
    | as the other cases). See the authoring note at the top of this file for
    | the evidence-scheduling gap fixed here.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'La Última Función — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V3'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — ambiente de la función',

            'audio_file' => 'a1-ambiente-funcion.mp3',
            'audio_scene' => 'Ambiente de teatro durante una función, aplausos que se apagan gradualmente, murmullo cálido de público.',
            'audio_context' => 'La regiduría anuncia por megafonía interna el cambio de vestuario y utilería antes de la escena final, con tono profesional y rutinario.',
            'audio_speaker' => 'Speaker 1 - Pablo Soria (voz de regiduría)',
            'audio_voice' => 'Orus',
            'audio_script' => <<<'TXT'
                Cambio de vestuario y utilería, escena final en cinco minutos.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 13,
            'title' => 'Expansión — la utilería de esta noche',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V7', 'V12'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 26,
            'title' => 'Fragmento privado — el ensayo con prensa',
            'source_file' => 'pista-privada-ensayo.md',
            'delivery_mode' => 'random_player',
            'evidence_codes' => ['V8'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 39,
            'title' => 'Complicación — el trofeo que no debía estar ahí',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4', 'V5', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Giro — dos noches que se parecían demasiado',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V6', 'V9', 'V10', 'V11'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 52,
            'title' => 'Audio — discusión oída a través de la puerta',

            'audio_file' => 'a2-discusion-oida.mp3',
            'audio_scene' => 'Fragmento de audio grabado a través de una puerta o desde un corredor estrecho de teatro, con eco de bambalinas.',
            'audio_context' => 'Dos voces masculinas discuten en tono tenso pero contenido, palabras parcialmente ininteligibles, terminando en un golpe seco y silencio.',
            'audio_speaker' => 'Mario Calvo / Héctor Delgado (discusión, parcialmente audible)',
            'audio_voice' => 'Fenrir',
            'audio_script' => <<<'TXT'
                No puedes simplemente irte así, después de todo esto.

                Ya está decidido, Mario. Lo siento.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 65,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para fechar correctamente cada recuerdo de esta noche y descubrir cuál pertenece, en realidad, a otra: el ensayo general con prensa de dos noches antes. Nadie mintió sobre lo que recuerda — solo hace falta saber en qué noche ocurrió cada cosa. Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/la-ultima-funcion/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-bambalinas.png', 'caption' => 'Plano simple del escenario y las bambalinas del Teatro Auriga'],
            ['file' => 'v3-escena-corredor.png', 'caption' => 'El corredor de bambalinas, fotografiado al hacer el hallazgo'],
        ],
        'expansion.md' => [
            ['file' => 'v2-hoja-utileria.png', 'caption' => 'Hoja de utilería de la producción'],
            ['file' => 'v7-programa-funcion.png', 'caption' => 'Programa de mano de la función de esta noche'],
            ['file' => 'v12-hoja-llamada.png', 'caption' => 'Hoja de llamada — plan de escenas y cambios de vestuario'],
        ],
        'pista-privada-ensayo.md' => [
            ['file' => 'v8-registro-ensayo-prensa.png', 'caption' => 'Registro del ensayo general con prensa, de dos noches antes'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-trofeo-real.png', 'caption' => 'El trofeo real, hallado en la caja de utilería'],
            ['file' => 'v5-replica-trofeo.png', 'caption' => 'La réplica ligera del trofeo, para comparación'],
            ['file' => 'v13-vitrina-trofeos.png', 'caption' => 'La vitrina de trofeos del vestíbulo, con un hueco'],
        ],
        'giro.md' => [
            ['file' => 'v6-registro-tecnico-funcion.png', 'caption' => 'Registro técnico de la función — hueco en las confirmaciones de Mario'],
            ['file' => 'v9-camerino-vestuarios.png', 'caption' => 'Camerino de Héctor, con el vestuario habitual y el nuevo'],
            ['file' => 'v10-vestuario-hector-hoy.png', 'caption' => 'El vestuario que lleva Héctor esta noche'],
            ['file' => 'v11-mensaje-contrato-cine.png', 'caption' => 'Mensaje confirmando el contrato de cine de Héctor'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v4-trofeo-real.png', 'caption' => 'El trofeo real, el objeto que no debía estar en la caja'],
            ['file' => 'v6-registro-tecnico-funcion.png', 'caption' => 'El hueco de Mario, exacto e independiente de cualquier memoria'],
            ['file' => 'v11-mensaje-contrato-cine.png', 'caption' => 'El mensaje que detonó todo, esa misma tarde'],
        ],
    ],
];
