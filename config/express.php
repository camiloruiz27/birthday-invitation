<?php

/*
|--------------------------------------------------------------------------
| Express — demo de feria
|--------------------------------------------------------------------------
|
| Mini-casos de 5 minutos que juegan varios grupos a la vez, sin registro
| (ver app/Modules/Express/README.md). Un evento es una entrada de `events`:
| otra feria = otra entrada, sin tocar codigo ni tablas.
|
*/

return [

    // Interruptor general. Apagado, /express responde "no disponible".
    'enabled' => (bool) env('EXPRESS_ENABLED', true),

    // Evento al que va /express cuando no trae ?e=.
    'default_event' => env('EXPRESS_DEFAULT_EVENT', 'usb-cali-2026'),

    'events' => [
        'usb-cali-2026' => [
            'name' => 'Feria de Emprendimiento · Universidad San Buenaventura Cali',
            'tagline' => 'Un grupo, tres minutos y pistas que cada quien ve distinto: compárenlas, interroguen y acusen.',
            'active' => (bool) env('EXPRESS_USB_ACTIVE', true),
            // La feria es el 28-oct; queda abierto hasta el 31-oct (hora de
            // Colombia) por si hay que ensayar o alguien entra tarde. Despues de
            // esto Express responde "La feria ya termino".
            'ends_at' => env('EXPRESS_USB_ENDS_AT', '2026-10-31 23:59:59'),
            // Codigo oficial que se muestra al terminar la partida. Se crea aparte
            // en la base del sitio donde se compra (`platform:create-promo-code`,
            // o /admin/codigos); aqui solo se enlaza.
            'promo_code' => env('EXPRESS_USB_PROMO', 'FERIA-USBCALI2026'),
            'promo_note' => 'Entra con el botón: el código ya va cargado. Si no, cópialo y úsalo al registrarte o al pagar.',
            'utm_campaign' => 'feria_usb_20261028',
            // null = todos los casos instalados en app/Modules/Express/Cases.
            'case_slugs' => null,
            // A donde manda el boton final: la landing de la plataforma en
            // PRODUCCION, que ya muestra la oferta del codigo (descuento o regalo),
            // lo recuerda mientras la persona se registra y lo aplica al pagar.
            // Absoluta a proposito: Express puede correr en TEST y quien compre
            // debe hacerlo en el sitio real, donde la promo existe.
            'cta_path' => env('EXPRESS_USB_CTA_URL', 'https://misteriocode.com/jugar'),
        ],
    ],

    // Tamano del grupo: el GM tambien juega, asi que 2 = GM + 1 amigo.
    'min_players' => 2,
    'max_players' => 5,

    // El reloj de la demo, en segundos desde que el GM inicia.
    'schedule' => [
        'interrogation_opens_s' => 45,
        'accusations_open_s' => 220,
        'window_end_s' => 270,
        'hard_close_s' => 300,
        // Pasado este punto ya nadie nuevo puede unirse a la ronda.
        'join_cutoff_s' => 130,
    ],

    // El sospechoso lo interpreta la IA del gateway (lawxora-ai-service,
    // proyecto mystery-case, ruta /interrogate-express; URL y llaves son las del
    // juego pago: immersion.ai.*). Sin gateway configurado, o con esto en
    // false, contesta el texto del autor y nada falla. Express no usa
    // creditos: es una demo y no se cobra nada.
    'ai' => [
        'enabled' => (bool) env('EXPRESS_AI_ENABLED', true),
        // Segundos que se espera al gateway antes de responder con el texto
        // del autor. Con razonamiento minimo el gateway contesta en ~1 s; en
        // la prueba real (2026-10-22) hubo llamadas sueltas de 7-10 s, y en una
        // demo de 5 minutos esperar mas que esto es peor que la respuesta del
        // autor, asi que se corta antes que el limite del gateway (10-13 s).
        'timeout' => (int) env('EXPRESS_AI_TIMEOUT', 8),
    ],

    // Presupuesto comun del equipo y por sospechoso (cada pregunta cuenta 1).
    'questions' => [
        'team_budget' => 3,
        'per_suspect' => 3,
        'max_length' => 200,
    ],

    // Puntaje (suma 100).
    'scoring' => [
        'who' => 40,
        'how' => 30,
        'why' => 30,
    ],

    // Captura de contacto (Ley 1581 de 2012). Subir la version al cambiar el
    // texto: queda grabada junto a cada autorizacion.
    'consent' => [
        'version' => '2026-10-v1',
        'text' => 'Autorizo a MisterioCode a contactarme por el medio que dejé para enviarme información y la promoción de la feria. Puedo revocar esta autorización escribiendo al correo de contacto de la plataforma. Más detalles en la política de privacidad.',
        // Meses que se conserva el contacto antes de borrarlo.
        'retention_months' => (int) env('EXPRESS_LEAD_RETENTION_MONTHS', 12),
    ],

    // Antiabuso. Los topes por IP son generosos: en una feria todos los
    // celulares salen por la misma Wi-Fi y comparten IP.
    'rate_limits' => [
        'create' => env('EXPRESS_RATE_CREATE', '60,1'),
        'join' => env('EXPRESS_RATE_JOIN', '120,1'),
        // Por jugador (token), no por IP.
        'ask' => env('EXPRESS_RATE_ASK', '12,1'),
        'act' => env('EXPRESS_RATE_ACT', '30,1'),
        'state' => env('EXPRESS_RATE_STATE', '90,1'),
    ],

    // Salas abiertas (lobby o jugando) a la vez; frena el spam de salas.
    'max_open_rooms' => (int) env('EXPRESS_MAX_OPEN_ROOMS', 200),

    // Una sala sin terminar se da por vencida pasadas estas horas.
    'room_ttl_hours' => 2,

    // Cuanto vive la instantanea de una sala en cache (segundos).
    'snapshot_ttl' => 10800,
];
