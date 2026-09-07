<?php

/**
 * Test fixture: a tiny case that HAS a written solution.
 *
 * The shipped case deliberately still carries the PENDIENTE placeholder, so
 * reveal behaviour cannot be exercised against it. This one exists purely so
 * the ending has something real to reveal.
 *
 * Lives outside app/Modules/Immersion/Cases on purpose: the app's registry
 * must not discover it, or it would show up in the catalog and in the
 * manifest-integrity tests.
 */
return [
    'name' => 'Caso de prueba',
    'version' => '1.0',
    'code' => 'TEST 001',
    'authority' => 'Departamento de Pruebas',

    'victim' => ['name' => 'La victima', 'photo' => 'victima.jpg'],

    'mechanics' => ['inbox', 'interrogation', 'accusation'],

    'limits' => ['interrogation_questions' => 2],

    'catalog' => [
        'tagline' => 'Un caso minimo para pruebas.',
        'difficulty' => 'easy',
        'duration_minutes' => 10,
        'min_players' => 1,
        'max_players' => 4,
        'price_amount' => 0,
        'currency' => 'COP',
        'published' => true,
    ],

    'suspects' => [
        'la-culpable' => [
            'name' => 'La Culpable',
            'role' => 'sospechosa',
            'file' => 'suspects/la-culpable.md',
            'photo' => 'la-culpable.jpg',
            'connection' => 'Socia',
        ],
        'el-inocente' => [
            'name' => 'El Inocente',
            'role' => 'sospechoso',
            'file' => 'suspects/el-inocente.md',
            'photo' => 'el-inocente.jpg',
            'connection' => 'Vecino',
        ],
    ],

    'solution' => [
        'culprit_slug' => 'la-culpable',
        'headline' => 'La socia lo hizo por dinero.',
        'motive' => 'Se quedaba con el seguro.',
        'method' => 'Le cambio las pastillas.',
        'key_evidence' => ['La firma en la poliza.'],
        'file' => 'solucion.md',
        'exonerations' => [
            'el-inocente' => 'El vecino estaba de viaje esa semana.',
        ],
        'confession_script' => null,
    ],

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Expediente',
            'body_markdown' => 'El expediente del caso de prueba.',
            'delivery_mode' => 'all',
        ],
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
