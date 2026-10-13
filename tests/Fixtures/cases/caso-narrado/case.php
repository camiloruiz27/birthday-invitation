<?php

/**
 * Test fixture: a case whose culprit cannot be questioned, so the winners'
 * epilogue is told by someone else (`solution.epilogue_speaker`), and whose
 * person has gone missing (the shared AI prompts must say "desaparición").
 *
 * Lives outside app/Modules/Immersion/Cases for the same reason as
 * caso-resuelto: the app's registry must not discover it.
 */
return [
    'name' => 'Caso narrado',
    'version' => '1.0',
    'code' => 'TEST 003',
    'authority' => 'Equipo de Pruebas',

    'victim' => ['name' => 'El huésped', 'label' => 'Reportado como desaparecido', 'photo' => 'victima.jpg'],

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
        'el-alias' => [
            'name' => 'El Alias',
            'role' => 'sospechoso (no localizado)',
            'file' => 'suspects/el-alias.md',
            'photo' => 'el-alias.jpg',
            'connection' => 'Nadie lo encontró',
            'interrogable' => false,
        ],
        'la-duena' => [
            'name' => 'La Dueña',
            'role' => 'gerente del hotel',
            'file' => 'suspects/la-duena.md',
            'photo' => 'la-duena.jpg',
            'connection' => 'Dueña del hotel',
        ],
        'el-mesero' => [
            'name' => 'El Mesero',
            'role' => 'mesero',
            'file' => 'suspects/el-mesero.md',
            'photo' => 'el-mesero.jpg',
            'connection' => 'Trabajaba esa noche',
        ],
    ],

    'solution' => [
        'culprit_slug' => 'el-alias',
        'epilogue_speaker' => 'la-duena',
        'headline' => 'El alias lo hizo.',
        'motive' => 'Necesitaba desaparecer.',
        'method' => 'Cambio de identidad.',
        'key_evidence' => ['La reserva a nombre falso.'],
        'file' => 'solucion.md',
        'confession_script' => 'Fui yo. Necesitaba desaparecer.',
        'exonerations' => [
            'la-duena' => 'Estaba en recepcion toda la noche.',
            'el-mesero' => 'Estaba en la cocina, con testigos.',
        ],
    ],

    'timeline' => [
        [
            'type' => 'email',
            'trigger_offset_minutes' => 1,
            'title' => 'Expediente',
            'body_markdown' => 'El expediente.',
            'delivery_mode' => 'all',
            'cta_interrogation' => true,
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
];
