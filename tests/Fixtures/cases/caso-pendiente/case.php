<?php

/**
 * Test fixture: a case whose ending has NOT been written yet.
 *
 * The shipped case used to play this role, but its solution is now authored,
 * so "an unfinished case must never offer a reveal" needs a case that is
 * genuinely unfinished. Keeping it as a fixture also means the rule stays
 * covered no matter what the shipped catalog does later.
 *
 * Lives outside app/Modules/Immersion/Cases on purpose: the app's registry
 * must not discover it, or it would show up in the catalog.
 */
return [
    'name' => 'Caso sin final',
    'version' => '1.0',
    'code' => 'TEST 002',
    'authority' => 'Departamento de Pruebas',

    'victim' => ['name' => 'La otra victima', 'photo' => 'victima.jpg'],

    'mechanics' => ['inbox', 'accusation'],

    'catalog' => [
        'tagline' => 'Un caso cuyo desenlace todavia no se ha escrito.',
        'price_amount' => 0,
        'currency' => 'COP',
        'published' => false,
    ],

    'suspects' => [
        'alguien' => [
            'name' => 'Alguien',
            'role' => 'sospechoso',
            'file' => 'suspects/alguien.md',
            'connection' => 'Vecino',
        ],
    ],

    // The placeholder, exactly as a case author leaves it.
    'solution' => [
        'culprit_slug' => 'PENDIENTE',
        'headline' => 'PENDIENTE',
        'file' => 'solucion.md',
    ],

    'timeline' => [
        [
            'type' => 'unlock',
            'trigger_offset_minutes' => 5,
            'title' => 'Acusaciones habilitadas',
            'body_markdown' => 'Ya pueden acusar.',
            'delivery_mode' => 'all',
        ],
    ],

    'gallery' => [],
    'gallery_excluded_headings' => [],
];
