<?php

/**
 * Case manifest: "Proyecto Boreal"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: three independent guilty acts (sabotage, data manipulation,
 * murder), each with its own author and motive. The engine still only tracks
 * one "solution.culprit_slug" (the murder), so Marcos's and Camila's own acts
 * are resolved entirely through their confrontations (see their content/
 * files) and through the confession audio delivered on the timeline — never
 * through the accusation/scoring mechanic itself, which only ever asks "who
 * killed Elena".
 *
 * This case also opts into the "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()): every
 * evidence-gated confrontation in content/suspects/*.md is written to check
 * that the relevant code is already in the delivered-evidence list before a
 * suspect can be made to fold, instead of trusting the player's claim
 * outright.
 */
return [

    'name' => 'Proyecto Boreal',
    'version' => '1.0',
    'code' => 'Registro interno — Estación Boreal',
    'authority' => 'Dirección de Operaciones, Proyecto Boreal (Nordkern Energy)',

    'victim' => [
        'name' => 'Dra. Elena Roth',
        'photo' => 'elena-roth.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for 5 preguntas iniciales + hasta 2 de
        // confrontación por sospechoso. The engine only supports one flat
        // number for the whole case (CaseDefinition::interrogationQuestions()),
        // so 5 is used for everyone, same as the other two cases.
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
        'tagline' => 'Una tormenta polar, una estación aislada, y tres secretos que no tienen nada que ver entre sí.',

        'description' => <<<'TXT'
            Una estación de investigación remota, financiada por Nordkern Energy,
            queda aislada por una tormenta polar justo antes de una llamada
            satelital en la que su directora científica pensaba reportar hallazgos
            incómodos. Esa misma noche, aparece muerta en el túnel de conexión.

            Tu equipo recibe el expediente en tiempo real —fotos, documentos,
            audios— y podrá interrogar a los seis compañeros de estación y a los
            dos testigos, uno por uno, mientras la tormenta sigue afuera y nadie
            puede pedir ayuda.

            El giro central: lo que parece una sola conspiración resulta ser tres
            actos completamente independientes, cometidos por tres personas
            distintas, la misma noche. Al final, una sola acusación: quién mató a
            Elena, con qué, por qué, y qué fue en realidad cada una de las otras
            dos cosas que pasaron esa noche.
            TXT,

        // Archivo esperado en public/immersion/proyecto-boreal/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'hard',
        'duration_minutes' => 95,
        'min_players' => 5,
        'max_players' => 8,

        'price_amount' => 64900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The two witnesses (Sofía, Jonas) and the secondary source (the station's
    | automated system) are modeled exactly like a suspect entry — the engine
    | has no separate code path for "witness" vs "suspect", only free-text in
    | `role`. The system is not a person, so it opts out of the accusation
    | roster with `accusable => false`; everyone else stays accusable by
    | default, same precedent as the other two cases.
    |
    */

    'suspects' => [
        'raul-iturri' => [
            'name' => 'Raúl Iturri',
            'role' => 'sospechoso',
            'file' => 'suspects/raul-iturri.md',
            'photo' => 'raul-iturri.png',
            'connection' => 'Comandante de la estación',
            'motive' => 'Tensión por recortes de seguridad',
            'alibi' => 'Módulo de comunicaciones, confirmado parcialmente por Jonas',
        ],
        'marcos-vega' => [
            'name' => 'Marcos Vega',
            'role' => 'sospechoso',
            'file' => 'suspects/marcos-vega.md',
            'photo' => 'marcos-vega.png',
            'connection' => 'Técnico de comunicaciones y energía',
            'motive' => 'Fraude de suministros que Elena había descubierto',
            'alibi' => 'Módulo de energía durante el ataque, visto por Raúl',
        ],
        'camila-sosa' => [
            'name' => 'Dra. Camila Sosa',
            'role' => 'sospechosa',
            'file' => 'suspects/camila-sosa.md',
            'photo' => 'camila-sosa.png',
            'connection' => 'Bióloga, coautora de Elena',
            'motive' => 'Manipulación de datos bajo presión de Nordkern',
            'alibi' => 'Sola en el módulo científico durante el ataque',
        ],
        'hugo-prieto' => [
            'name' => 'Dr. Hugo Prieto',
            'role' => 'sospechoso',
            'file' => 'suspects/hugo-prieto.md',
            'photo' => 'hugo-prieto.png',
            'connection' => 'Médico de la estación',
            'motive' => 'Ninguno',
            'alibi' => 'Enfermería, confirmada por Sofía',
        ],
        'tatiana-kovac' => [
            'name' => 'Tatiana Kovac',
            'role' => 'sospechosa',
            'file' => 'suspects/tatiana-kovac.md',
            'photo' => 'tatiana-kovac.png',
            'connection' => 'Subordinada de Elena, ingeniera de perforación',
            'motive' => 'Años de crédito profesional robado',
            'alibi' => 'Túnel de conexión (parcialmente cierta, omite el encuentro)',
        ],
        'diego-almada' => [
            'name' => 'Diego Almada',
            'role' => 'sospechoso',
            'file' => 'suspects/diego-almada.md',
            'photo' => 'diego-almada.png',
            'connection' => 'Representante corporativo de Nordkern Energy',
            'motive' => 'Aparente (corporativo); real, invertido',
            'alibi' => 'Su habitación, confirmada por Sofía',
        ],
        'sofia-reyes' => [
            'name' => 'Sofía Reyes',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-reyes.md',
            'photo' => 'sofia-reyes.png',
            'connection' => 'Cocinera, logística interna',
            'motive' => 'Ninguno',
            'alibi' => null,
        ],
        'jonas-weber' => [
            'name' => 'Jonas Weber',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/jonas-weber.md',
            'photo' => 'jonas-weber.png',
            'connection' => 'Meteorólogo, técnico ambiental',
            'motive' => 'Ninguno',
            'alibi' => null,
        ],
        'sistema-estacion' => [
            'name' => 'Sistema Automatizado de la Estación',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-estacion.md',
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as El Brindis's "paramedico") — a
            // shot of the terminal/panel works as its "portrait".
            'photo' => 'sistema-estacion.png',
            'connection' => 'Registros objetivos de la estación',
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
        'culprit_slug' => 'tatiana-kovac',

        'headline' => 'Tatiana Kovac mató a Elena Roth de un golpe con la maza de extracción de núcleos, en el túnel de conexión, y simuló una caída.',

        'motive' => 'Venganza por años de crédito profesional robado, agravada por la negativa final de Elena a reconocerla en una publicación reciente.',

        'method' => 'Golpe con la maza de extracción de núcleos en el túnel de conexión exterior, entre las 21:20 y las 21:40; escena alterada para simular una caída sobre un escalón helado.',

        'key_evidence' => [
            'La maza de extracción de núcleos, anómalamente más limpia que el resto del equipo del rack.',
            'El testimonio indirecto de Jonas: una silueta saliendo del túnel hacia las 21:40, compatible con el equipo de perforación.',
            'El testimonio de Sofía: Elena dirigiéndose al módulo de perforación hacia las 21:15.',
            'El documento antiguo de la publicación, con la nota al pie que reconoce apenas una "contribución técnica" de Tatiana.',
            'La negativa inicial de Tatiana a admitir haber hablado con Elena esa noche, contradicha por los otros dos testimonios.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not have
         * killed Elena. One entry per suspect that is not the culprit,
         * including both witnesses and the secondary source. For Marcos and
         * Camila this is specifically about the murder — their own guilt
         * (sabotage, data manipulation) is a separate matter, already
         * resolved through their own confrontation and the confession audio,
         * never through this exoneration text.
         */
        'exonerations' => [
            'raul-iturri' => 'Su registro de accesos del sistema lo ubica en el módulo de comunicaciones durante buena parte de la ventana crítica, intentando restablecer el enlace. No tenía ningún motivo real contra Elena — solo un diagnóstico de salud personal que ocultaba por miedo profesional, sin ninguna relación con su muerte.',

            'marcos-vega' => 'Saboteó la antena a las 19:32 para ocultar un fraude de suministros, en el mástil exterior — un lugar y una hora distintos a los del ataque. Durante la ventana del asesinato (21:20–21:40) estaba en el módulo de energía, visto por Raúl. No sabía nada de lo que le pasó a Elena en el túnel.',

            'camila-sosa' => 'Su propia sesión en el servidor científico, registrada por el sistema entre las 20:03 y las 20:44, la ubica sola en el módulo científico durante esa hora — no en el túnel, y en un momento distinto al del ataque. Su motivo real (ocultar la manipulación de datos) nunca requirió la muerte de Elena; al contrario, la complicaba al atraer más atención sobre el proyecto.',

            'hugo-prieto' => 'Sofía confirma que estuvo en la enfermería toda la tarde, y le llevó té alrededor de las 21:30 — dentro de la ventana del ataque, pero en un lugar distinto. Su único secreto es una sanción profesional antigua, sin ninguna relación con el crimen; su propio conocimiento médico fue clave para que el grupo dudara de la versión del accidente.',

            'diego-almada' => 'Estuvo en su habitación preparando un informe la mayor parte de la noche, y se cruzó brevemente con Elena hacia las 20:50 en el pasillo principal, no en el túnel — confirmado por Sofía. Su motivo real era exactamente el opuesto a matarla: necesitaba que Elena siguiera viva y con credibilidad intacta para exponer juntos la manipulación de datos.',

            'sofia-reyes' => 'Pasó la noche moviéndose entre módulos llevando comida y bebida a distintos miembros de la tripulación, lo que la convierte en testigo de varias coartadas ajenas — nunca estuvo en el túnel durante la ventana del ataque, y no tenía ningún motivo contra Elena.',

            'jonas-weber' => 'Estuvo en el módulo meteorológico toda la noche registrando la tormenta; fue precisamente desde ahí, por una ventana lateral, que alcanzó a ver de lejos la silueta saliendo del túnel — su ubicación es la razón por la que es testigo del hecho, no sospechoso de haberlo cometido.',

            'sistema-estacion' => 'Es un sistema automatizado de registro, no una persona: no tiene forma física de haber estado en el túnel ni motivo alguno. Su única función esa noche fue registrar accesos y consumo de energía, lo cual ayudó a fechar el sabotaje y la sesión del servidor, pero no puede ver dentro del túnel, que no tiene lector de tarjeta.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Kore',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it. This is the same text as the
        // "Confrontación 2" quote in content/suspects/tatiana-kovac.md.
        'confession_script' => <<<'TXT'
            Ocho años. Ocho años callada mientras ella firmaba lo que yo había
            construido. Esta vez solo le pedí que me nombrara, una vez, en algo
            que también era mío. Me dijo que no, como siempre, como si yo no
            importara.

            Discutimos otra vez en el túnel. Tenía la maza en la mano, la
            llevaba de vuelta al módulo. No lo pensé. La golpeé, y después...
            después entré en pánico. Intenté que pareciera una caída.

            No quería matarla. Quería que, por una vez, dijera mi nombre.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+24/T+36/T+50/T+65/T+80/T+90
    | delivery plan into trigger_offset_minutes (T+0 becomes minute 1, same
    | convention el-brindis-22-14 uses). Where the design doc delivered a
    | photo/document and an audio at the same moment, they are split into two
    | events one minute apart, since 'email' and 'audio_email' are separate
    | timeline event types.
    |
    | `evidence_codes` opts this case into "Level 2": an interrogation can
    | check a player's claim about a piece of evidence against what this game
    | has actually delivered so far (see Game::deliveredEvidenceCodes()).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Proyecto Boreal — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1', 'V2'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 2,
            'title' => 'Radio interna — el apagón',

            'audio_file' => 'a1-radio-apagon.wav',
            'audio_scene' => 'Radio interna de la estación, minutos después del corte de comunicaciones. Estática de fondo, viento fuerte.',
            'audio_context' => 'Intercambio breve entre Raúl (por radio, tenso pero funcional) y Marcos (nervioso, hablando rápido para adelantarse a cualquier pregunta).',
            'audio_speaker' => 'Raúl Iturri / Marcos Vega (intercambio de radio)',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                ¿Alguien tiene visual del mástil? Se cayó todo, no tenemos enlace
                con el exterior.

                Reviso el generador, pero para mí que fue el viento, con esta
                tormenta no me extraña.

                Revisa igual la antena en cuanto puedas, con cuidado, hay
                ventisca fuerte allá afuera.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — sospechosos y primera duda forense',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            // This is the moment the design doc opens interrogations to
            // everyone.
            'cta_interrogation' => true,
            'evidence_codes' => ['V3', 'V15'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 13,
            'title' => 'Aviso de emergencia — el hallazgo',

            'audio_file' => 'a2-aviso-emergencia.wav',
            'audio_scene' => 'Radio interna, pasos rápidos y respiración agitada de fondo, algo de eco de túnel.',
            'audio_context' => 'Raúl, voz alterada pero funcional, pidiendo ayuda médica urgente para Elena, tratando de mantener el control de la situación.',
            'audio_speaker' => 'Raúl Iturri',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                Necesito a Hugo en el túnel de conexión, ya. Es Elena... creo
                que se cayó, no responde.

                Que nadie toque nada más de lo necesario, por favor.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — agenda de la llamada',
            'source_file' => 'pista-privada-agenda.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — un rumor viejo',
            'source_file' => 'pista-privada-tatiana.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 36,
            'title' => 'Complicación — el sabotaje confirmado',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V4', 'V7', 'V8', 'V10', 'V16'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 37,
            'title' => 'Confrontación grabada — Marcos Vega',

            'audio_file' => 'a3-confesion-marcos-sabotaje.wav',
            'audio_scene' => 'Interior silencioso, algo de viento de fondo lejano.',
            'audio_context' => 'Marcos, voz temblorosa entre el alivio y el miedo, confesando el sabotaje y el fraude que lo motivó tras ser confrontado con la evidencia por Raúl. Insiste en que no tocó a Elena.',
            'audio_speaker' => 'Marcos Vega',
            'audio_voice' => 'Fenrir',
            'audio_script' => <<<'TXT'
                Fui yo, ¿sí? Corté el cable de la antena. Elena había encontrado
                lo de los pedidos de repuestos, iba a reportarlo esa misma noche.
                Solo quería tiempo, arreglar los números antes de que alguien más
                los viera. Eso es todo. Yo no le hice nada a Elena, se lo juro.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 50,
            'title' => 'Giro — tres cosas distintas, la misma noche',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V5', 'V6', 'V9', 'V11'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 51,
            'title' => 'Confrontación grabada — Dra. Camila Sosa',

            'audio_file' => 'a4-confesion-camila-datos.wav',
            'audio_scene' => 'Silencio de laboratorio, zumbido leve de equipos.',
            'audio_context' => 'Camila, voz profesional que se quiebra levemente de vergüenza, confesando la manipulación de datos tras ser confrontada con la evidencia, negando con claridad cualquier relación con la muerte de Elena.',
            'audio_speaker' => 'Dra. Camila Sosa',
            'audio_voice' => 'Kore',
            'audio_script' => <<<'TXT'
                Alteré los registros, sí. Durante dos años. Nordkern necesitaba
                ciertos números, y yo... accedí. Cuando cayeron las
                comunicaciones, pensé que tenía tiempo para borrar lo que
                probaba la diferencia. No sabía nada de Elena entonces. Cuando
                me enteré, pensé que era por esto. Pero no fui yo. Yo solo
                quería que nadie viera esos archivos.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A4'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 65,
            'title' => 'Desarrollo final — alguien salió del túnel',
            'source_file' => 'desarrollo-final.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 80,
            'title' => 'Cierre — motivo y oportunidad',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V12', 'V14'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 90,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para reconstruir lo que pasó esa noche — y para distinguir, con claridad, los tres actos entre sí. Queda habilitado el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/proyecto-boreal/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-estacion-tormenta.png', 'caption' => 'Exterior de la estación durante la tormenta'],
            ['file' => 'v2-mapa-estacion.png', 'caption' => 'Mapa operativo de la estación'],
        ],
        'expansion.md' => [
            ['file' => 'v3-escena-tunel.png', 'caption' => 'Escena del hallazgo en el túnel de conexión'],
            ['file' => 'v15-escritorio-elena.png', 'caption' => 'Habitación y escritorio de Elena'],
        ],
        'complicacion.md' => [
            ['file' => 'v4-modulo-comunicaciones.png', 'caption' => 'Módulo de comunicaciones y energía'],
            ['file' => 'v7-registro-accesos.png', 'caption' => 'Registro de accesos por tarjeta'],
            ['file' => 'v8-registro-energia.png', 'caption' => 'Registro de consumo de energía — pico de las 19:32'],
            ['file' => 'v10-antena-cable-cortado.png', 'caption' => 'La antena, con el cable seccionado de forma limpia'],
            ['file' => 'v16-chat-interno.png', 'caption' => 'Chat interno de la tripulación durante el apagón'],
        ],
        'giro.md' => [
            ['file' => 'v5-modulo-cientifico.png', 'caption' => 'Módulo científico y laboratorio'],
            ['file' => 'v6-rack-herramientas.png', 'caption' => 'Rack de herramientas del módulo de perforación'],
            ['file' => 'v9-registro-servidor.png', 'caption' => 'Registro de acceso al servidor científico'],
            ['file' => 'v11-discrepancia-sensores.png', 'caption' => 'Discrepancia entre sensores e informe oficial'],
        ],
        'desarrollo-final.md' => [
            ['file' => 'v13-maza-limpia.png', 'caption' => 'El rack de herramientas — la maza anómalamente limpia'],
        ],
        'cierre.md' => [
            ['file' => 'v12-notas-elena.png', 'caption' => 'Notas personales de Elena'],
            ['file' => 'v14-publicacion-antigua.png', 'caption' => 'El documento antiguo — la publicación con la nota al pie'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v13-maza-limpia.png', 'caption' => 'El arma — anómalamente limpia'],
            ['file' => 'v14-publicacion-antigua.png', 'caption' => 'El origen del motivo — el crédito robado'],
            ['file' => 'v9-registro-servidor.png', 'caption' => 'La sesión de Camila — un acto aparte, no el asesinato'],
        ],
    ],
];
