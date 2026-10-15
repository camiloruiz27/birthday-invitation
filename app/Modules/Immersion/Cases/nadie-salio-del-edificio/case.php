<?php

/**
 * Case manifest: "Nadie Salió del Edificio"
 *
 * This file is the whole case as far as the engine is concerned. The narrative
 * text itself lives in content/ and is rendered verbatim; what follows is the
 * structure around it — identity, mechanics, roster, timeline and gallery.
 *
 * Authoring note: this is a "digital traces" case. EVERY record in the building
 * (cards, elevators, printers, sessions, mail, video calls) is genuine and
 * exact; nothing is hacked, spoofed or manipulated. The twist is that almost
 * none of them proves PHYSICAL presence:
 *  - Gonzalo left a video call connected (camera off, no audio) and went
 *    upstairs by the internal stairs, which log nothing. He is the only
 *    deliberate lie.
 *  - Valentina answered two e-mails from Rodolfo's account (delegated access).
 *  - Esteban lent his card to an outside contractor.
 *  - Patricia had a scheduled report fire while she took a private call.
 *  - Daniela left her session open and a colleague used it.
 *  - Sergio joined a virtual meeting passively while meeting a recruiter.
 * Every AI persona must keep "card used / session active / call connected" and
 * "physically present" apart. The building system (sistema-control-edificio)
 * NEVER fails: whatever it shows is exact.
 *
 * Adaptations made while porting the design doc to the engine (kept here so
 * the next author does not "fix" them back):
 *  - The engine scores only the culprit, and its accusation form is suspect +
 *    free-text motive + free-text weapon. The doc's 5-field form and point
 *    scoring (including the penalty for accusing from a digital trace alone)
 *    are NOT implemented; the real route, and which alibis were deliberate vs.
 *    misread, are asked for inside the free-text motive (see the T+90 unlock
 *    text) and explained in solucion.md and in each exoneration.
 *  - Each person has 5 base questions, plus 3 more in `confrontation` stages
 *    for those with confrontations (8 in all; the doc's "5 + 2" becomes 5 + 3).
 *    The stages in `interrogation.stages` wait on the evidence each md names,
 *    not on a separate pool or on the clock.
 *  - V18 (Gonzalo's later message to a colleague) was described in the
 *    evidence bible but never scheduled. Delivered at T+68 with the motive
 *    documents. The design typo "¿not me viste raro?" was fixed.
 *  - A1 (video-call fragment) was in the bible but never scheduled either
 *    (the JSON lists it only inside the real-events timeline). Delivered at
 *    T+52 together with V8 (the mic-activity log), which it illustrates. The
 *    design mixes up the audio numbers: the doc's gancho audio is A2 (closure
 *    announcement) and the call fragment is A1; kept as in the doc/JSON.
 *  - Gonzalo's confrontation 1 needs V8, which the doc delivers at T+52, but
 *    confrontations "unlock" at T+38. Gated on V8 itself in his md (the
 *    system reminds the table of the discrepancy once V8 is in hand — the
 *    doc's T+52 fail-safe).
 *  - Gonzalo's confrontation 2 cites Hugo's testimony + V4 + V16/V17; V4 comes
 *    at T+52 and V16/V17 at T+68. Gated on V4, V16 and V17; Hugo's "expanded
 *    testimony" is delivered as text inside reconstruccion.md (T+52) so the
 *    whole table has it, and Hugo's own md only gives exact minutes once the
 *    player holds V4.
 *  - V2 is delivered summarized at T+12 and as private fragments at T+24
 *    (three random_player events, two suspects each, files v2a/v2b/v2c). The
 *    fragments carry NO evidence code and hide every in-window row behind
 *    "pendiente de verificación"; the complete matrix is V6–V13 at T+38.
 *  - V14 (scene) showed an empty slot on the SHELF while the doc says the
 *    trophy stood on the DESK. Fixed minimally: the trophy sat on the desk, so
 *    V14 shows the empty, dust-free base mark on the desk, and V15 says it was
 *    found back among the shelf awards, wiped clean.
 *  - V16/V17: the design says V16 reserves the name of whoever is responsible.
 *    It names the approver of the payments ("Gonzalo Prada, Subdirector
 *    Financiero"), which is what links the invoices (V17) to him.
 *  - Hugo's sighting is 15:11–15:12 (going up), a few minutes BEFORE the attack
 *    (15:14–15:18); the doc's "ventana exacta del ataque" is read as "en la
 *    ruta y el momento del ataque". Hugo does not see him come back down.
 *  - V9: the doc says the two e-mails make Rodolfo look alive "más tiempo del
 *    real" but gives no times. Fixed at 15:19 and 15:24 (both after the real
 *    attack, the second one after Gonzalo's camera is back at 15:23). The doc
 *    also says Valentina's confrontation 2 "fecha la hora real de la muerte"
 *    without any anchor: authored here as Rodolfo's last own message to her at
 *    15:10 ("dame un rato sin interrupciones") plus, once V9 is delivered, the
 *    building system's record that his workstation shows no activity after
 *    15:10. The 15:19 / 15:24 e-mails are Valentina's, so his death dates
 *    from 15:10 on, which fits exactly Gonzalo's absence (15:08-15:23).
 *    Gonzalo's card swipe after the window is at 15:34 (V6), not 15:24: his
 *    camera is back on the call at 15:23, so a swipe at the piso 4 door a
 *    minute later contradicted the video.
 *  - Valentina and Daniela were circular alibis (each "confirmed" by the other
 *    while Daniela was away from her desk). Resolved: both sit on piso 5 in an
 *    open plan; Daniela took her call in the glass call room facing the
 *    office, from where she could see Valentina typing at her desk, and
 *    Valentina saw her inside the glass room. The building system also shows
 *    the e-mails left from Valentina's workstation on piso 5 under a
 *    delegated session (a technical fact, not a proof of who typed).
 *  - Patricia's and Sergio's rooms (sala de descanso, sala de reuniones de
 *    cristal) are on the ground floor next to the lobby, which is why Carmen
 *    (reception) saw them pass. They have no card reader, so V6 does not
 *    include them. Esteban's lent card appears in V6 (piso 3 sala técnica
 *    15:03) and V10 (impresora piso 3, 15:12), both made by the contractor.
 *  - The outside contractor (Esteban) and the recruiter (Sergio) were already
 *    inside before the closure; V5 (lobby after the closure) rules out any
 *    irregular entry or exit.
 *  - V1 shows only the floors the case needs (PB, 2, 3, 4, 5, 6) and labels
 *    them as described in the md files; floors/rooms beyond the design doc
 *    were kept to the minimum needed for the cross-alibis.
 *  - V3/V4/V5 are CCTV frames of low resolution in which nobody is
 *    identifiable by face; identification is only by clothing.
 *
 * This case opts into "evidence_codes" per timeline event (see
 * CaseDefinition::evidenceCodeMap() / Game::deliveredEvidenceCodes()).
 */
return [

    'name' => 'Nadie Salió del Edificio',
    'version' => '1.0',
    'code' => 'Expediente confidencial — Torre Andesol',
    'authority' => 'Dirección de Seguridad Corporativa, Andesol Technologies',

    'victim' => [
        'name' => 'Rodolfo Castaño',
        'label' => 'Víctima',
        'photo' => 'rodolfo-castano.png',
    ],

    'mechanics' => ['inbox', 'timeline', 'gallery', 'audio', 'interrogation', 'accusation'],

    'limits' => [
        // Base of 5 preguntas per person; the confrontation extras (3) are
        // declared per person in `interrogation.stages` (see the roster).
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
        'tagline' => 'Su tarjeta, su sesión y su cámara dicen tres cosas distintas. Ninguna es mentira. Ninguna es él.',

        'description' => <<<'TXT'
            Torre Andesol, sede de Andesol Technologies, entra en un cierre de
            seguridad total de cuarenta minutos por un bolso abandonado en el
            lobby. Nadie entra ni sale. Cuando el cierre termina, el director
            financiero aparece muerto en su oficina.

            Tu equipo (5 a 8 investigadores, ideal 6) recibe el expediente en
            tiempo real y podrá interrogar a seis sospechosos, tres testigos y
            al propio sistema del edificio, durante 90 minutos, cruzando
            registros de tarjetas, ascensores, impresoras, sesiones y
            videollamadas.

            Aquí todos los registros son auténticos, y casi todos parecen
            situar a alguien en un lugar incompatible con lo que otros dicen.
            Saber qué demuestra de verdad cada uno es lo que resuelve el caso.
            TXT,

        // Archivo esperado en public/immersion/nadie-salio-del-edificio/cover/portada.png
        'cover' => 'portada.png',

        // Texto para anuncios (opcional; lo usa /jugar/nadie-salio-del-edificio).
        // Solo hechos de este caso, sin revelar la solucion.
        'ad' => [
            'hook' => 'Un edificio cerrado durante cuarenta minutos. Un director muerto. Y cada registro dice algo distinto.',
            'bullets' => [
                '5 a 8 investigadores cruzando tarjetas, ascensores, sesiones y videollamadas',
                'Seis sospechosos, tres testigos y el sistema del edificio a quienes interrogar',
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
        // public/immersion/nadie-salio-del-edificio/ (ver ASSET_PROMPTS.md).
        // Se siembra solo en el primer sync; luego: cambiar a true y correr
        // `php artisan platform:sync-cases --prices`, o editar la fila en BD.
        'published' => false,
        'sort_order' => 13,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogable people
    |--------------------------------------------------------------------------
    |
    | Not new narrative content: the full testimony still lives only in
    | content/suspects/*.md (the single source of truth for the interrogation).
    | The three witnesses (Raúl, Carmen, Hugo) and the secondary source (the
    | building control system) are modeled exactly like a suspect entry.
    |
    */

    'suspects' => [
        'gonzalo-prada' => [
            'name' => 'Gonzalo Prada',
            'role' => 'sospechoso',
            'file' => 'suspects/gonzalo-prada.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V8'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V4', 'V16', 'V17'], 'confrontation' => true],
            ]],
            'photo' => 'gonzalo-prada.png',
            'connection' => 'Subdirector financiero; subordinado directo de Rodolfo',
            'motive' => 'Rodolfo descubrió que Gonzalo desviaba fondos mediante facturas de un proveedor ficticio y iba a presentarlo a la junta directiva apenas terminara el cierre',
            'alibi' => 'Conexión activa a la videollamada de equipo durante el cierre, sin presencia física confirmada frente a la pantalla',
            'public' => [
                'age' => '48 años',
                'profile' => 'Subdirector financiero y subordinado directo de Rodolfo. Profesional y controlado; la relación entre ambos se había vuelto tensa en los últimos meses.',
                'alibi' => 'Dice haber seguido la videollamada de equipo durante todo el cierre.',
                'facts' => [
                    'Reporta directamente a Rodolfo Castaño.',
                    'Su oficina está en el piso 4; la de Rodolfo, en el piso 6.',
                ],
            ],
        ],
        'valentina-rojas' => [
            'name' => 'Valentina Rojas',
            'role' => 'sospechosa',
            'file' => 'suspects/valentina-rojas.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V9'], 'confrontation' => true],
            ]],
            'photo' => 'valentina-rojas.png',
            'connection' => 'Asistente ejecutiva de Rodolfo, con acceso delegado a su correo',
            'motive' => 'Ninguno respecto a la muerte; respondió dos correos desde la cuenta de Rodolfo sin saber que ya no podía hacerlo (acceso delegado)',
            'alibi' => 'En su escritorio, en un piso distinto al de Rodolfo, a la vista de Daniela durante la ventana crítica',
            'public' => [
                'age' => '37 años',
                'profile' => 'Asistente ejecutiva de Rodolfo. Eficiente y leal, gestiona su agenda y, con su permiso habitual, parte de su correo.',
                'alibi' => 'Dice haber estado en su escritorio, en un piso distinto al de Rodolfo.',
                'facts' => [
                    'Fue quien encontró a Rodolfo al ir a confirmar la reunión de junta directiva.',
                    'Tiene acceso delegado a la cuenta de correo de Rodolfo.',
                ],
            ],
        ],
        'esteban-navas' => [
            'name' => 'Esteban Navas',
            'role' => 'sospechoso',
            'file' => 'suspects/esteban-navas.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 2, 'after' => ['V10'], 'confrontation' => true],
                ['questions' => 1, 'after' => ['V6', 'V10'], 'confrontation' => true],
            ]],
            'photo' => 'esteban-navas.png',
            'connection' => 'Técnico de sistemas; colega de otro departamento',
            'motive' => 'Ninguno respecto a la muerte; prestó su tarjeta a un contratista externo, contra la política interna (préstamo de credencial)',
            'alibi' => 'En su propio piso con compañeros de sistemas durante toda la ventana crítica',
            'public' => [
                'age' => '41 años',
                'profile' => 'Técnico de sistemas, con acceso técnico amplio al edificio. Competente, pero se pone tenso cuando se habla de la política interna.',
                'alibi' => 'Dice haber tenido su tarjeta consigo todo el tiempo.',
                'facts' => [
                    'Forma parte del equipo que administra los sistemas del edificio.',
                    'No tenía conflicto conocido con Rodolfo.',
                ],
            ],
        ],
        'patricia-vega' => [
            'name' => 'Patricia Vega',
            'role' => 'sospechosa',
            'file' => 'suspects/patricia-vega.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V11'], 'confrontation' => true],
            ]],
            'photo' => 'patricia-vega.png',
            'connection' => 'Gerente de Recursos Humanos; colega de otro departamento',
            'motive' => 'Ninguno respecto a la muerte; un reporte programado se envió solo mientras ella atendía una llamada personal (automatización)',
            'alibi' => 'En una sala de descanso poco transitada, vista por Carmen cuando se dirigía hacia allí',
            'public' => [
                'age' => '45 años',
                'profile' => 'Gerente de Recursos Humanos. Reservada y profesional; protege con cuidado su vida privada.',
                'alibi' => 'Dice haber estado trabajando en su escritorio.',
                'facts' => [
                    'Su computador registró actividad continua durante el cierre.',
                    'No tenía conflicto conocido con Rodolfo.',
                ],
            ],
        ],
        'daniela-mercado' => [
            'name' => 'Daniela Mercado',
            'role' => 'sospechosa',
            'file' => 'suspects/daniela-mercado.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V12'], 'confrontation' => true],
            ]],
            'photo' => 'daniela-mercado.png',
            'connection' => 'Analista de datos; colega de otro departamento',
            'motive' => 'Ninguno respecto a la muerte; negocia en secreto una oferta de trabajo externa y dejó su sesión abierta mientras atendía esa llamada (sesión abierta)',
            'alibi' => 'En una sala de llamadas con vidrio, a la vista de Valentina, durante parte de la ventana crítica',
            'public' => [
                'age' => '34 años',
                'profile' => 'Analista de datos. Profesional y algo evasiva cuando se habla de su futuro laboral.',
                'alibi' => 'Dice haberse levantado un rato a estirar las piernas.',
                'facts' => [
                    'Su sesión de computador registró actividad en un momento en que ella dice no estar en su puesto.',
                    'No tenía conflicto conocido con Rodolfo.',
                ],
            ],
        ],
        'sergio-lemos' => [
            'name' => 'Sergio Lemos',
            'role' => 'sospechoso',
            'file' => 'suspects/sergio-lemos.md',
            'interrogation' => ['stages' => [
                ['questions' => 5],
                ['questions' => 3, 'after' => ['V13'], 'confrontation' => true],
            ]],
            'photo' => 'sergio-lemos.png',
            'connection' => 'Director comercial; colega de igual rango de Rodolfo',
            'motive' => 'Ninguno respecto a la muerte; exploraba en secreto una salida profesional reuniéndose en persona con un reclutador mientras seguía una reunión virtual de forma pasiva (acción remota)',
            'alibi' => 'En una sala de reuniones del lobby con un visitante, visto por Carmen',
            'public' => [
                'age' => '50 años',
                'profile' => 'Director comercial. Seguro de sí mismo y reservado con su agenda; mantuvo durante años cierta rivalidad con Rodolfo por los recursos entre departamentos.',
                'alibi' => 'Dice haber seguido una reunión virtual durante todo el cierre.',
                'facts' => [
                    'Figura conectado a una reunión virtual programada durante el cierre.',
                    'Su rango en la empresa es el mismo que el de Rodolfo.',
                ],
            ],
        ],
        'raul-tellez' => [
            'name' => 'Raúl Téllez',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/raul-tellez.md',
            'photo' => 'raul-tellez.png',
            'connection' => 'Guardia de seguridad; supervisó el protocolo de cierre',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '55 años',
                'profile' => 'Guardia de seguridad. Supervisó el protocolo de cierre desde el lobby.',
                'facts' => [
                    'Estuvo en el lobby durante todo el cierre.',
                    'Conoce el origen del bolso que activó el protocolo.',
                ],
            ],
            'accusable' => false,
        ],
        'carmen-duque' => [
            'name' => 'Carmen Duque',
            'role' => 'testigo (no es sospechosa oficial)',
            'file' => 'suspects/carmen-duque.md',
            'photo' => 'carmen-duque.png',
            'connection' => 'Recepcionista; estuvo en el lobby durante el cierre',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '29 años',
                'profile' => 'Recepcionista. Estuvo en el lobby durante el cierre y ve pasar a casi todos los empleados.',
                'facts' => [
                    'Conoce de vista a la mayoría de los empleados de la torre.',
                    'Desde recepción ve las zonas comunes de la planta baja.',
                ],
            ],
            'accusable' => false,
        ],
        'hugo-saldana' => [
            'name' => 'Hugo Saldaña',
            'role' => 'testigo (no es sospechoso oficial)',
            'file' => 'suspects/hugo-saldana.md',
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V4']],
            ]],
            'photo' => 'hugo-saldana.png',
            'connection' => 'Técnico de mantenimiento; hacía su ronda durante el cierre',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'age' => '52 años',
                'profile' => 'Técnico de mantenimiento. Hizo su ronda habitual por el edificio mientras duró el cierre.',
                'facts' => [
                    'Recorre el edificio a pie en sus rondas de mantenimiento.',
                    'No tiene relación laboral con el departamento financiero.',
                ],
            ],
            'accusable' => false,
        ],
        'sistema-control-edificio' => [
            'name' => 'Sistema de control del edificio',
            'role' => 'fuente secundaria (sistema automatizado)',
            'file' => 'suspects/sistema-control-edificio.md',
            'interrogation' => ['stages' => [
                ['questions' => 2],
                ['questions' => 2, 'after' => ['V6']],
                ['questions' => 1, 'after' => ['V4', 'V8']],
            ]],
            // Not a person, but CaseAssetsTest expects a portrait for every
            // roster entry (same precedent as "sistema-control-buque") — a shot
            // of the security-room console works as its "portrait".
            'photo' => 'sistema-control-edificio.png',
            'connection' => 'Registros de tarjetas, ascensores, impresoras, sesiones y videollamadas',
            'motive' => null,
            'alibi' => null,
            'public' => [
                'profile' => 'Fuente técnica del edificio: registra tarjetas, ascensores, impresoras, sesiones de computador y videollamadas, sin interpretar nada.',
                'facts' => [
                    'Registra qué credencial abrió qué puerta y a qué hora.',
                    'Registra eventos técnicos: no puede decir quién estaba físicamente frente a cada equipo o puerta.',
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
        'culprit_slug' => 'gonzalo-prada',

        'headline' => 'Todos los registros del edificio eran reales, y casi ninguno demostraba presencia física: Gonzalo Prada dejó su videollamada conectada, subió por la escalera interna sin dejar rastro de tarjeta y mató a Rodolfo con un trofeo de su escritorio.',

        'motive' => 'Rodolfo descubrió en una auditoría interna que Gonzalo llevaba cerca de dos años desviando fondos mediante facturas de un proveedor ficticio, para pagar el tratamiento médico de su hija, y iba a presentarlo a la junta directiva apenas terminara el cierre de seguridad.',

        'method' => 'Golpe con el trofeo de reconocimiento que estaba sobre el escritorio de Rodolfo, en su oficina del piso 6, entre las 15:14 y las 15:18. Gonzalo salió de su oficina del piso 4 a las 15:10 por la escalera interna (sin tarjeta ni ascensor), volvió por la misma ruta a las 15:20, limpió el trofeo y reactivó su cámara en la videollamada a las 15:23.',

        'key_evidence' => [
            'El registro de la videollamada muestra a Gonzalo conectado todo el tiempo, pero sin actividad de audio ni de cámara entre las 15:08 y las 15:23.',
            'Hugo lo vio subir de prisa por la escalera entre los pisos 4 y 6 a las 15:11–15:12, y la cámara de la escalera (CCTV2) muestra a una figura con su misma ropa que el lobby de la mañana (CCTV1).',
            'Su tarjeta no se usó entre las 14:47 y las 15:34 y nadie usó el ascensor hacia el piso 6: la ruta fue la escalera interna, que no registra nada.',
            'El trofeo apareció inusualmente limpio, sin el polvo leve que tienen los demás objetos de la oficina.',
            'El borrador de auditoría y las facturas del proveedor ficticio señalan a Gonzalo como quien aprobaba los pagos.',
            'Los otros cinco rastros digitales engañosos tienen explicaciones humanas y ajenas a la muerte: acceso delegado, préstamo de tarjeta, automatización, sesión abierta y acción remota pasiva.',
        ],

        'file' => 'solucion.md',

        /*
         * Used by the personalised epilogue: why THIS suspect could not be
         * the culprit. One entry per roster member that is not the culprit,
         * including the three witnesses and the secondary source.
         */
        'exonerations' => [
            'valentina-rojas' => 'Sí respondió dos correos desde la cuenta de Rodolfo (15:19 y 15:24) con su acceso delegado habitual, sin saber que ya había muerto, y minimizó cuánto usaba esa cuenta por vergüenza profesional. Pero estuvo en su escritorio del piso 5, a la vista de Daniela, no hay registro de ascensor al piso 6 y sus correos, bien entendidos, muestran que Rodolfo ya no respondía por sí mismo desde las 15:10, lo que ayuda a fechar la muerte.',

            'esteban-navas' => 'Sí mintió sobre su tarjeta: la prestó a un contratista externo para ahorrarle el trámite de visitante, y por eso figura en la sala técnica del piso 3 y en la impresora de ese piso mientras él estaba en el suyo con sus compañeros de sistemas. Es una falta de política interna, sin relación con Rodolfo ni con el piso 6.',

            'patricia-vega' => 'Sí mintió sobre su actividad: el reporte que figura a las 15:15 es automático y ella atendía una llamada personal por un problema de salud familiar en una sala de descanso de la planta baja, donde Carmen la vio llegar. Es privacidad, no una coartada falsa contra Rodolfo.',

            'daniela-mercado' => 'Sí ocultó por qué se ausentó: tomaba en una sala de llamadas con vidrio una llamada sobre una oferta de trabajo externa y dejó su sesión desbloqueada, que un compañero usó brevemente para abrir un archivo compartido. Valentina la vio en esa sala y ella vio a Valentina en su escritorio.',

            'sergio-lemos' => 'Sí mintió sobre su reunión virtual: estaba conectado de forma pasiva (sin cámara ni audio) mientras se reunía en persona con un reclutador de otra empresa en una sala de cristal junto al lobby, donde Carmen lo vio pasar con un visitante. Su rivalidad con Rodolfo no tiene relación con su secreto, y estaba lejos del piso 6.',

            'raul-tellez' => 'Es el guardia de seguridad, no un sospechoso: su aporte fue confirmar el origen del bolso, los horarios del cierre y que nadie entró ni salió por el perímetro. Controlaba el lobby y no tuvo acceso ni motivo alguno respecto a la oficina de Rodolfo.',

            'carmen-duque' => 'Es la recepcionista, no una sospechosa: su aporte fue confirmar que vio a Patricia y a Sergio en la planta baja. No subió a los pisos altos ni tenía relación con Rodolfo más allá del saludo diario.',

            'hugo-saldana' => 'Es un testigo, no un sospechoso: su ronda lo cruzó con una figura que subía por la escalera a las 15:11–15:12, y eso es todo lo que aporta. Estuvo en su recorrido de mantenimiento y no tenía motivo alguno respecto a Rodolfo.',

            'sistema-control-edificio' => 'Es un sistema automatizado de registro, no una persona: no puede haber intervenido. Nunca falló ni fue manipulado: cada tarjeta, sesión, impresión y conexión que mostró era cierta. Lo que no puede hacer es decir quién estaba físicamente allí, y esa limitación es precisamente la clave del caso.',
        ],

        // Voice the confession is read in. Gemini prebuilt voice name.
        'confession_voice' => 'Charon',

        // Used by the confession audio. Written to be played as-is: the model
        // only reads it aloud and lightly weaves in 1-2 real questions from
        // the table, it never composes it.
        'confession_script' => <<<'TXT'
            Empecé con montos pequeños, pensando que podría devolverlos antes de que
            alguien notara algo. El tratamiento de mi hija no daba espera, y no sabía
            a quién más recurrir.

            Cuando Rodolfo me dijo que ya lo había encontrado todo, y que lo
            presentaría a la junta apenas terminara el cierre, no lo pensé con calma.
            Dejé la llamada conectada y subí a hablar con él.

            Le pedí tiempo. Se negó. Vi el trofeo ahí, en su escritorio, y no lo pensé
            más. Después volví a la llamada como si nada hubiera pasado, y no sé cómo
            pude hacer eso.
            TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Accusation form and rubric
    |--------------------------------------------------------------------------
    |
    | From the doc's scoring: asesino 30 / method + route 20 (split 10/10) /
    | motive 20 / the five non-homicidal alibis 20 + telling deliberate from
    | misread 10 (merged into one 30-point question).
    |
    */

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => [
            'label' => '¿Con qué y dónde lo mató?',
            'hint' => 'Nombra el objeto y el lugar exacto del crimen.',
            'points' => 10,
            'guidance' => 'Basta con el golpe con el trofeo del escritorio en la oficina de Rodolfo (piso 6). No exigir la franja horaria.',
        ],
        'motive' => [
            'label' => '¿Por qué lo mató?',
            'points' => 20,
            'hint' => 'Explica qué había descubierto la víctima y qué iba a hacer con ello.',
            'guidance' => 'Lo esencial es el desvío de fondos con facturas de un proveedor ficticio que Rodolfo iba a presentar a la junta. El tratamiento de la hija es un plus, no un requisito.',
        ],
        'questions' => [
            [
                'key' => 'ruta_fisica',
                'label' => '¿Qué ruta física siguió el culpable para llegar a la víctima y volver sin dejar rastro?',
                'hint' => 'Describe el recorrido entre pisos y por qué ningún registro lo captó.',
                'answer' => 'Salió de su oficina del piso 4 hacia las 15:10, subió por la escalera interna al piso 6 (oficina de Rodolfo) y volvió al piso 4 por la misma escalera hacia las 15:20. Durante el cierre la escalera no pide tarjeta y no usó el ascensor, así que no quedó ningún registro.',
                'points' => 10,
                'guidance' => 'Lo esencial: escalera interna entre los pisos 4 y 6, sin tarjeta ni ascensor. Cero si la ruta se apoya solo en un rastro digital sin presencia física confirmada (tarjeta, sesión o correo).',
            ],
            [
                'key' => 'coartadas_digitales',
                'label' => '¿Cuáles de los rastros digitales de esa tarde eran una coartada deliberada y cuáles eran reales pero mal interpretados? Explica cada uno.',
                'hint' => 'Repasa el rastro de cada persona del piso y di qué lo produjo realmente.',
                'answer' => 'Solo la videollamada de Gonzalo, conectada pero sin audio ni cámara entre las 15:08 y las 15:23, fue una coartada deliberada. Los demás eran rastros reales mal interpretados y ajenos a la muerte: Valentina respondió correos con el acceso delegado a la cuenta de Rodolfo; Esteban prestó su tarjeta a un contratista; el reporte de Patricia fue automático; Daniela dejó su sesión abierta y un compañero la usó; Sergio seguía la reunión de forma pasiva mientras veía a un reclutador.',
                'points' => 30,
                'guidance' => 'Veinte puntos por explicar los cinco rastros no homicidas (4 por cada uno: acceso delegado, préstamo de tarjeta, automatización, sesión abierta, acción remota pasiva) y diez por señalar que solo la conexión de Gonzalo era deliberada. Cero en esos diez si trata otro rastro como coartada falsa.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default timeline
    |--------------------------------------------------------------------------
    |
    | Translated from the design doc's T+0/T+12/T+24/T+38/T+52/T+68/T+80/T+90
    | delivery plan into trigger_offset_minutes (T+0 -> 1, same convention as
    | the other cases).
    |
    */

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Nadie Salió del Edificio — apertura del caso',
            'source_file' => 'gancho.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V1'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio — anuncio del cierre de seguridad',

            'audio_file' => 'a2-anuncio-cierre.wav',
            'audio_scene' => 'Anuncio de megafonía en el interior de un edificio corporativo: ambiente de oficina de fondo, un breve tono de aviso antes de la voz. Sin música.',
            'audio_context' => 'La voz de megafonía informa con tono profesional y calmado de la activación de un protocolo estándar de cierre de seguridad; sin alarma excesiva ni pánico.',
            'audio_speaker' => 'Speaker 1 - Voz de megafonía del edificio',
            'audio_voice' => 'Sadaltager',
            'audio_script' => <<<'TXT'
                Atención, se activa el protocolo de cierre de seguridad por un objeto
                no identificado en recepción. Por favor, permanezcan en sus puestos
                hasta nuevo aviso. El acceso y salida del edificio queda restringido
                temporalmente.
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A2'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 12,
            'title' => 'Expansión — sospechosos, testigos y la matriz de eventos',
            'source_file' => 'expansion.md',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
            'evidence_codes' => ['V2', 'V3'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — la matriz, filas de Gonzalo y Valentina',
            'source_file' => 'pista-privada-matriz-a.md',
            'delivery_mode' => 'random_player',
            // Deliberately no evidence_codes on the three T+24 fragments:
            // partial private glimpses of the matrix must not satisfy any
            // confrontation gate on their own.
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — la matriz, filas de Esteban y Patricia',
            'source_file' => 'pista-privada-matriz-b.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 24,
            'title' => 'Fragmento privado — la matriz, filas de Daniela y Sergio',
            'source_file' => 'pista-privada-matriz-c.md',
            'delivery_mode' => 'random_player',
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 38,
            'title' => 'Complicación — la matriz completa; se abren las confrontaciones',
            'source_file' => 'complicacion.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V6', 'V7', 'V9', 'V10', 'V11', 'V12', 'V13'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 52,
            'title' => 'Reconstrucción de la ruta — el micrófono, la escalera y Hugo',
            'source_file' => 'reconstruccion.md',
            'delivery_mode' => 'all',
            // V8 is what Gonzalo's confrontation 1 requires, V4 what the
            // second one starts from. Both arrive here.
            'evidence_codes' => ['V4', 'V8'],
        ],
        [
            'type' => 'audio_email',
            'trigger_offset_minutes' => 52,
            'title' => 'Audio — fragmento de la videollamada de equipo',

            // A1 was in the evidence bible but never scheduled in the design's
            // timeline. Delivered with V8, which it illustrates.
            'audio_file' => 'a1-fragmento-videollamada.wav',
            'audio_scene' => 'Fragmento de una videollamada corporativa grabado desde el lado de los otros participantes. Primero una colega habla con voz normal; después hay un silencio prolongado de unos 6 segundos, solo con el leve ruido de fondo de la sala de la otra persona que habla y nada del lado de Gonzalo; otro colega comenta extrañado; tras otro silencio breve, responde Gonzalo, apresurado y algo fuera de sincronía con la conversación. Sin música.',
            'audio_context' => 'Videollamada de coordinación de un equipo financiero. El silencio debe sentirse genuinamente extraño pero no alarmante en el momento; la respuesta de Gonzalo suena apresurada y ligeramente fuera de contexto, como quien llega corriendo a su sitio. No confirma ninguna culpabilidad.',
            'audio_speaker' => 'Speaker 1 - Colega (voz normal) · Speaker 2 - Otro colega (algo extrañado) · Speaker 3 - Gonzalo Prada (apresurado)',
            'audio_voice' => 'Charon',
            'audio_script' => <<<'TXT'
                Speaker 1: Gonzalo, ¿nos confirmas el número final para el reporte?
                Speaker 2: ¿Gonzalo? Se ve que sigue conectado...
                Speaker 3: Perdón, se me cortó el audio un momento. Les confirmo en un rato, ¿sí?
                TXT,
            'delivery_mode' => 'all',
            'evidence_codes' => ['A1'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 68,
            'title' => 'Giro — la oficina de Rodolfo y el motivo',
            'source_file' => 'giro.md',
            'delivery_mode' => 'all',
            // V18 was never scheduled in the design doc; added here with the
            // motive documents.
            'evidence_codes' => ['V14', 'V15', 'V16', 'V17', 'V18'],
        ],
        [
            'type' => 'email',
            'trigger_offset_minutes' => 80,
            'title' => 'Cierre — el lobby tras el cierre y las coartadas cruzadas',
            'source_file' => 'cierre.md',
            'delivery_mode' => 'all',
            'evidence_codes' => ['V5'],
        ],
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 90,
            'title' => 'Fase de acusaciones habilitada',
            'body_markdown' => 'El equipo ya tiene todo lo necesario para separar lo que demuestra cada registro de lo que solo parece demostrar. Queda habilitado el formulario de acusación final: además de a quién acusan, cómo y por qué, el formulario hace algunas preguntas sobre el caso.',
            'delivery_mode' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    |
    | Images shown next to the verbatim text of each timeline delivery. The
    | files live in public/immersion/nadie-salio-del-edificio/gallery.
    |
    */

    'gallery' => [
        'gancho.md' => [
            ['file' => 'v1-plano-vertical-edificio.png', 'caption' => 'Plano vertical de Torre Andesol'],
        ],
        'expansion.md' => [
            ['file' => 'v2-matriz-eventos-resumida.png', 'caption' => 'Matriz de eventos digitales (versión resumida)'],
            ['file' => 'v3-cctv-lobby-manana.png', 'caption' => 'Cámara del lobby, a primera hora'],
        ],
        'pista-privada-matriz-a.md' => [
            ['file' => 'v2a-fragmento-gonzalo-valentina.png', 'caption' => 'Fragmento de la matriz: dos personas'],
        ],
        'pista-privada-matriz-b.md' => [
            ['file' => 'v2b-fragmento-esteban-patricia.png', 'caption' => 'Fragmento de la matriz: dos personas'],
        ],
        'pista-privada-matriz-c.md' => [
            ['file' => 'v2c-fragmento-daniela-sergio.png', 'caption' => 'Fragmento de la matriz: dos personas'],
        ],
        'complicacion.md' => [
            ['file' => 'v6-registro-tarjetas.png', 'caption' => 'Registro de tarjetas durante el cierre'],
            ['file' => 'v7-registro-ascensores.png', 'caption' => 'Registro de ascensores durante el cierre'],
            ['file' => 'v9-correo-rodolfo.png', 'caption' => 'Correo enviado desde la cuenta de Rodolfo'],
            ['file' => 'v10-registro-impresora.png', 'caption' => 'Registro de trabajos de impresión'],
            ['file' => 'v11-reporte-automatico.png', 'caption' => 'Reporte corporativo de la tarde'],
            ['file' => 'v12-sesion-daniela.png', 'caption' => 'Registro de actividad de una sesión de computador'],
            ['file' => 'v13-videollamada-sergio.png', 'caption' => 'Panel de una reunión virtual'],
        ],
        'reconstruccion.md' => [
            ['file' => 'v4-cctv-escalera.png', 'caption' => 'Cámara de la escalera interna'],
            ['file' => 'v8-videollamada-gonzalo.png', 'caption' => 'Panel de la videollamada de equipo: conexión y micrófono'],
        ],
        'giro.md' => [
            ['file' => 'v14-escena-oficina.png', 'caption' => 'La oficina de Rodolfo al hacerse el hallazgo'],
            ['file' => 'v15-trofeo.png', 'caption' => 'El trofeo hallado en la estantería'],
            ['file' => 'v16-borrador-auditoria.png', 'caption' => 'Borrador del informe de auditoría de Rodolfo'],
            ['file' => 'v17-facturas-proveedor.png', 'caption' => 'Facturas de un proveedor, halladas junto a la auditoría'],
            ['file' => 'v18-mensaje-gonzalo.png', 'caption' => 'Mensaje enviado por la tarde a un colega de la videollamada'],
        ],
        'cierre.md' => [
            ['file' => 'v5-cctv-lobby-tras-cierre.png', 'caption' => 'Cámara del lobby tras levantarse el cierre'],
        ],

        // The reveal re-shows the chain of evidence that convicts, so the
        // table can see it instead of just taking the report's word.
        'solucion.md' => [
            ['file' => 'v8-videollamada-gonzalo.png', 'caption' => 'Conectado no es presente: el silencio de 15:08 a 15:23'],
            ['file' => 'v4-cctv-escalera.png', 'caption' => 'La ruta real: la escalera interna, sin tarjeta'],
            ['file' => 'v15-trofeo.png', 'caption' => 'El arma, limpiada'],
            ['file' => 'v17-facturas-proveedor.png', 'caption' => 'El motivo: facturas de un proveedor ficticio'],
        ],
    ],
];
