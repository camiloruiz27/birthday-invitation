<?php

/**
 * Case manifest: "El Brindis de las 22:14"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: the source design doc for this case (knowledge_topics with
 * difficulty tiers, per-topic lies/leaks, evidence-gated confrontations) is
 * richer than what this engine enforces in code today. The engine only sends
 * one flat markdown file per suspect to the AI gateway as testimony (see
 * GatewayInterrogationProvider) and has no evidence-shown tracking. So all of
 * that structure has been folded into instructional prose inside each
 * content/suspects/*.md file instead — the model is trusted to follow it, the
 * same way steve-jacobs trusts it to keep a suspect in character. Nothing here
 * is enforced by code beyond the single global question cap below.
 */
return [

    'name' => 'El Brindis de las 22:14',
    'version' => '1.0',
    'code' => 'Expediente privado — Restaurante Fermento',
    'authority' => 'Investigación entre los propios invitados (sin intervención policial formal)',

    'victim' => [
        'name' => 'Tomás Ferrán',
        'photo' => 'tomas-ferran.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // The design doc asks for "4 preguntas iniciales + 1 de confrontación"
        // per suspect, and a separate (looser) budget for the witness and the
        // paramedic. The engine only supports one flat number for the whole
        // case (CaseDefinition::interrogationQuestions()), so 5 is used for
        // everyone — it matches the suspect budget exactly, but caps the
        // witness/paramedic tighter than the original design intended.
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
        'tagline' => 'Un brindis, una copa envenenada y un cambio de asientos que nadie planeó.',

        'description' => <<<'TXT'
            Seis comensales cenan en un restaurante cerrado al público para
            celebrar la venta de una bodega familiar. En el brindis de las
            22:14, uno de ellos muere envenenado. La copa fatal no estaba
            destinada a la víctima: estaba destinada a otra persona.

            Un cambio de asientos, hecho por cariño minutos antes de la cena,
            puso a la víctima real en el lugar del objetivo real. Tu equipo
            recibe el expediente en tiempo real —fotos, documentos, audios— y
            podrá interrogar a los cinco sospechosos y a la testigo, uno por
            uno, mientras el reloj corre.

            Al final, una sola acusación: quién, con qué y por qué.
            TXT,

        // Archivo esperado en public/immersion/el-brindis-22-14/cover/portada.png
        'cover' => 'portada.png',

        'difficulty' => 'medium',
        'duration_minutes' => 45,
        'min_players' => 4,
        'max_players' => 6,

        'price_amount' => 49900,
        'currency' => 'COP',

        'published' => true,
        'sort_order' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The witness (Sofía Luna) and the secondary source (the paramedic) are
    | modeled exactly like a suspect entry, following the same pattern
    | steve-jacobs uses for its witness — the engine has no separate code path
    | for "witness" vs "suspect", only free-text in `role`.
    |
    */

    'suspects' => [
        'margarita-ferran' => [
            'name' => 'Margarita Ferrán',
            'role' => 'sospechosa',
            'file' => 'suspects/margarita-ferran.md',
            'photo' => 'margarita-ferran.png',
            'connection' => 'Madre de la víctima, anfitriona',
            'motive' => 'Ninguno directo; el cambio de asientos la hace parecer sospechosa',
            'alibi' => 'A la vista de todos casi toda la noche',
        ],
        'clara-vega' => [
            'name' => 'Clara Vega',
            'role' => 'sospechosa',
            'file' => 'suspects/clara-vega.md',
            'photo' => 'clara-vega.png',
            'connection' => 'Socia minoritaria (30%) de la bodega',
            'motive' => 'Se beneficia de la venta; discutió con Diego esa noche',
            'alibi' => 'Con Margarita revisando papeles (20:35–20:50)',
        ],
        'diego-salazar' => [
            'name' => 'Diego Salazar',
            'role' => 'sospechoso',
            'file' => 'suspects/diego-salazar.md',
            'photo' => 'diego-salazar.png',
            'connection' => 'Representante de Altavista Capital, comprador',
            'motive' => 'Ninguno propio; es el objetivo real del veneno',
            'alibi' => 'Sin acceso a cocina ni a las copas',
        ],
        'irene-morales' => [
            'name' => 'Irene Morales',
            'role' => 'sospechosa',
            'file' => 'suspects/irene-morales.md',
            'photo' => 'irene-morales.png',
            'connection' => 'Encargada de la finca, asistente personal de Margarita',
            'motive' => 'Venganza contra Altavista Capital (ruina de Viña Morales)',
            'alibi' => 'Sola en el pase de cocina (20:35–20:50)',
        ],
        'hector-paredes' => [
            'name' => 'Héctor Paredes',
            'role' => 'sospechoso',
            'file' => 'suspects/hector-paredes.md',
            'photo' => 'hector-paredes.png',
            'connection' => 'Sommelier retirado, amigo de la familia',
            'motive' => null,
            'alibi' => 'Llegó a las 21:00, después de la preparación de las copas',
        ],
        'sofia-luna' => [
            'name' => 'Sofía Luna',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/sofia-luna.md',
            'photo' => 'sofia-luna.png',
            'connection' => 'Camarera de sala',
            'motive' => null,
            'alibi' => null,
        ],
        'paramedico' => [
            'name' => 'Paramédico de emergencias',
            'role' => 'fuente secundaria (personal de emergencias)',
            'file' => 'suspects/paramedico.md',
            'photo' => 'paramedico.png',
            'connection' => 'Equipo de emergencias que atendió a la víctima',
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
    | in the epilogue or the confession audio, which only ever put THIS
    | material into a suspect's mouth.
    |
    | `content/solucion.md` must never reach the AI gateway. Suspect
    | testimonies are sent verbatim during interrogations; the solution is not.
    |
    */

    'solution' => [
        // Must be a key of 'suspects' above.
        'culprit_slug' => 'irene-morales',

        'headline' => 'La encargada de la finca. Un extracto de adelfa en el digestivo, un asiento que cambió sin que ella lo supiera, y el hombre que amaba en secreto pagando el error de otra persona.',

        'motive' => 'Venganza contra Diego Salazar y Altavista Capital, la firma que arruinó a la familia de Irene (Viña Morales) más de una década atrás. Irene no cambió su vida para vengarse de un desconocido: cambió su vida para poder, algún día, acercarse al mundo que destruyó a su padre.',

        'method' => 'Con acceso legítimo y sin supervisión a las copas antiguas de la familia, entre las 20:35 y las 20:50, Irene disolvió un extracto de adelfa en el digestivo servido en la copa del asiento 2 — el puesto de honor, asignado esa mañana a Diego Salazar. A las 21:05, sin que ella lo supiera, Margarita cambió los asientos por cariño hacia su hijo: Tomás pasó al asiento 2, Diego al 6. En el brindis de las 22:14, Tomás bebió por error el digestivo envenenado.',

        'key_evidence' => [
            'Irene tuvo acceso exclusivo y sin supervisión a las seis copas entre las 20:35 y las 20:50 — confirmado por la testigo Sofía Luna y contradicho solo por el propio intento de Irene de minimizarlo.',
            'El registro del sistema de reservas del restaurante fecha el cambio de asientos a las 21:05, después de que las copas ya estaban preparadas y asignadas por puesto.',
            'La comparación entre la foto del aperitivo (tarjetas originales) y la foto del brindis muestra que el puesto de honor —asiento 2, originalmente de Diego— terminó ocupado por Tomás.',
            'Irene niega con demasiada firmeza conocer "Altavista Capital", una negación que se cae en cuanto se le pregunta con detalle por su apellido, su origen o el nombre de la bodega en la que creció.',
            'Un recorte de más de una década de antigüedad conecta a Altavista Capital con la liquidación de Viña Morales, la bodega familiar de Irene.',
            'Su reacción de horror genuino al saber que la víctima fue Tomás, y no Diego, es incompatible con la de alguien cuyo plan salió exactamente como esperaba.',
        ],

        // The long reveal, rendered verbatim like any other case content.
        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue (later delivery): why THIS
         * innocent suspect could not have done it. One entry per suspect that
         * is not the culprit, including the witness and the secondary source.
         */
        'exonerations' => [
            'margarita-ferran' => 'Estuvo a la vista de todos casi toda la noche. Su único momento a solas fue el cambio de las tarjetas de sitio con el metre, a las 21:05 — fuera de la ventana de envenenamiento, y sin acceso a las copas ya preparadas por Irene. No tenía motivo contra nadie: su único error fue querer a su hijo cerca esa noche.',

            'clara-vega' => 'Su coartada está confirmada por la propia Margarita durante la ventana crítica de las 20:35 a las 20:50. Y su interés real —que la venta se cerrara y que su desfalco personal quedara cubierto— exigía que Diego siguiera vivo, no muerto.',

            'diego-salazar' => 'Nunca tuvo acceso a la cocina ni a las copas. No es el autor del veneno: es la persona para la que estaba destinado, sin saberlo hasta el final.',

            'hector-paredes' => 'Llegó al restaurante a las 21:00, después de que las seis copas ya estuvieran preparadas y colocadas por puesto. Nunca tuvo acceso individual a ninguna de ellas. Su secreto real —la paternidad de Tomás— no tiene ninguna relación con el veneno.',

            'sofia-luna' => 'Sirvió la mesa toda la noche sin acceso previo a la preparación de las copas, y fue precisamente su testimonio sobre los horarios de Irene en el pase de cocina lo que ayudó a fechar la ventana de envenenamiento.',

            'paramedico' => 'Llegó después del colapso, como parte del equipo de emergencias. Su única función fue confirmar, con datos técnicos, que se trató de un envenenamiento y no de un episodio médico natural.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name; null
        // falls back to the gateway's default.
        'confession_voice' => 'Kore',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud, it never composes it. Stage directions from
        // the original design doc ("(pausa)", "(la voz se rompe)") are left
        // out of the script itself — they belong in tone/pacing, not in text
        // a TTS engine would read aloud literally.
        'confession_script' => <<<'TXT'
            Ustedes no lo entienden. Esa firma... Altavista, antes tenía otro
            nombre, pero es la misma gente. Se llevaron todo lo que mi familia
            construyó. Mi padre nunca se recuperó.

            Preparé esa copa para el asiento dos. Para él. Para Diego. Llevaba
            años esperando una oportunidad así.

            Nadie me dijo que cambiaron los sitios. Nadie me dijo que
            Tomás... que Tomás iba a sentarse ahí.

            Yo lo quería. Dios, yo lo quería de verdad. Y lo maté yo. Sin
            querer, pero lo maté yo.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+8/T+16/T+24/T+32/T+40/T+45
    | minute-offset delivery plan into trigger_offset_minutes, using the same
    | mechanic steve-jacobs already runs (immersion:process-timeline). Where
    | the design doc delivered a photo/document and an audio at the same
    | moment (T+32, T+40), they are split into two events one minute apart,
    | since 'email' and 'audio_email' are separate timeline event types.
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Apertura del caso',
            'source_file' => 'apertura.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 8,
            'title' => 'Expansión — sospechosos y coartadas',
            'source_file' => 'desarrollo.md',
            'delivery_mode' => 'all',
            // This is the moment the design doc opens interrogations and the
            // witness to all players.
            'cta_interrogation' => true,
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 16,
            'title' => 'Hallazgo confidencial',
            'source_file' => 'memo-confidencial.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Complicación — confirmación forense',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 32,
            'title' => 'Giro — el asiento equivocado',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 33,
            'title' => 'Grabación del brindis',

            'audio_file' => 'brindis-audio.wav',
            'audio_scene' => 'Un teléfono grabando unos segundos de video durante un brindis en una cena privada elegante. Ambiente de copas chocando, risas suaves de fondo.',
            'audio_context' => 'Margarita, la anfitriona, propone el brindis frente a los seis comensales. Voz cálida, emocionada, ligeramente temblorosa por la ocasión.',
            'audio_speaker' => 'Speaker 1 - Margarita Ferrán',
            'audio_voice' => 'Kore',
            'audio_script' => <<<'TXT'
                Antes de brindar... quiero tener a mi hijo cerca esta noche.
                Diego, no te ofendas, pero necesitaba a Tomás a mi lado para
                esto. Por la nueva etapa. Por todos nosotros.
                TXT,
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 40,
            'title' => 'Cierre — motivo, oportunidad y error',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 41,
            'title' => 'Llamada de emergencia',

            'audio_file' => 'llamada-emergencia.wav',
            'audio_scene' => 'Llamada telefónica de emergencia realista, ruido de fondo de comedor alterado, voces lejanas.',
            'audio_context' => 'Un invitado llama a emergencias dos minutos después del colapso de Tomás. Voz agitada pero funcional; un operador calmado pregunta por los síntomas.',
            'audio_speaker' => 'Speaker 1 - Invitado que llama a emergencias',
            'audio_voice' => 'Fenrir',
            'audio_script' => <<<'TXT'
                Necesito una ambulancia, por favor, en el restaurante
                Fermento. Un hombre se ha desplomado, hace apenas dos
                minutos, justo después de un brindis. Está consciente pero
                muy mal, no reacciona bien... no, no hay heridas, no se ha
                golpeado, simplemente se desplomó. No sé, ¿veinte minutos?
                Dijo que se mareaba, que sudaba frío, y de repente se cayó de
                la silla.
                TXT,
            'delivery_mode' => 'all',
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 45,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El sistema habilitó el formulario de acusación final.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/el-brindis-22-14/gallery.
    |
    | E4 exists in two distinct files: the design doc delivers it once without
    | the seat-change log visible (T+8) and once with it visible (T+32) —
    | these must be two different images, not the same file shown twice.
    |
    */

    'gallery' => [
        'apertura.md' => [
            ['file' => 'evidencia-9-escena-colapso.png', 'caption' => 'Escena del comedor poco después del colapso'],
            ['file' => 'evidencia-3-plano-asientos-original.png', 'caption' => 'Plano de asientos original del evento'],
        ],
        'desarrollo.md' => [
            ['file' => 'evidencia-1-aperitivo-tarjetas.png', 'caption' => 'Aperitivo — mesa montada con las tarjetas de sitio originales'],
            ['file' => 'evidencia-4-reserva-parcial.png', 'caption' => 'Sistema de reservas del restaurante — ficha del evento'],
        ],
        'memo-confidencial.md' => [
            ['file' => 'evidencia-7-memo-auditoria.png', 'caption' => 'Fragmento de memo de auditoría'],
        ],
        'complicacion.md' => [
            ['file' => 'evidencia-5-cocina-copas.png', 'caption' => 'Pase de cocina — copas antiguas en preparación'],
            ['file' => 'evidencia-6-mesa-copas-servidas.png', 'caption' => 'Mesa ya servida, copa por puesto, antes del brindis'],
        ],
        'giro.md' => [
            ['file' => 'evidencia-2-brindis.png', 'caption' => 'El brindis, 22:13–22:14'],
            ['file' => 'evidencia-4-reserva-completo.png', 'caption' => 'Sistema de reservas — registro del cambio de asientos de las 21:05'],
        ],
        'cierre.md' => [
            ['file' => 'evidencia-8-vina-morales.png', 'caption' => 'Recorte de archivo — Viña Morales'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'evidencia-1-aperitivo-tarjetas.png', 'caption' => 'El asiento 2 — originalmente de Diego Salazar'],
            ['file' => 'evidencia-4-reserva-completo.png', 'caption' => 'El cambio de asientos, fechado a las 21:05'],
            ['file' => 'evidencia-8-vina-morales.png', 'caption' => 'El origen del motivo — Viña Morales'],
        ],
    ],

];
