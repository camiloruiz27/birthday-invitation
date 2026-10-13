<?php

/**
 * Test fixture for the engine features added on 2026-10-12:
 *  - a victim `label` other than "Víctima";
 *  - a suspect who can be accused but not questioned (`interrogable` false);
 *  - three private clues (`random_player`) to spread across the table;
 *  - an `accusation` block with an extra graded question.
 *
 * Lives outside app/Modules/Immersion/Cases for the same reason as
 * caso-resuelto: the app's registry must not discover it.
 */
return [
    'name' => 'Caso calificado',
    'version' => '1.0',
    'code' => 'TEST 002',
    'authority' => 'Departamento de Pruebas',

    'victim' => ['name' => 'La desaparecida', 'label' => 'Desaparecida', 'photo' => 'victima.jpg'],

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
            // 3 now, 2 more with V9, and a confrontation round of 3 with V10: 8.
            'interrogation' => ['stages' => [
                ['questions' => 3],
                ['questions' => 2, 'after' => ['V9']],
                ['questions' => 3, 'after' => ['V10'], 'confrontation' => true],
            ]],
        ],
        'el-inocente' => [
            'name' => 'El Inocente',
            'role' => 'sospechoso',
            'file' => 'suspects/el-inocente.md',
            'photo' => 'el-inocente.jpg',
            'connection' => 'Vecino',
        ],
        'el-ausente' => [
            'name' => 'El Ausente',
            'role' => 'sospechoso (no localizado)',
            'file' => 'suspects/el-ausente.md',
            'photo' => 'el-ausente.jpg',
            'connection' => 'Nadie lo encontró',
            'interrogable' => false,
        ],
    ],

    'accusation' => [
        'culprit_points' => 30,
        'weapon' => ['label' => '¿Cómo lo hizo?', 'points' => 15],
        'motive' => ['points' => 20],
        'questions' => [
            [
                'key' => 'complice',
                'label' => '¿Quién la ayudó, si alguien?',
                'hint' => 'Nombra a la persona o di que nadie.',
                'answer' => 'Nadie: actuó sola.',
                'points' => 10,
                'guidance' => 'Cero si la trata como una conspiración.',
            ],
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
            'el-ausente' => 'Nunca estuvo cerca.',
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
        ['type' => 'email', 'trigger_offset_minutes' => 2, 'title' => 'Pista A', 'body_markdown' => 'A', 'delivery_mode' => 'random_player', 'evidence_codes' => ['V9']],
        ['type' => 'email', 'trigger_offset_minutes' => 2, 'title' => 'Pista B', 'body_markdown' => 'B', 'delivery_mode' => 'random_player'],
        ['type' => 'email', 'trigger_offset_minutes' => 2, 'title' => 'Pista C', 'body_markdown' => 'C', 'delivery_mode' => 'random_player'],
        ['type' => 'email', 'trigger_offset_minutes' => 3, 'title' => 'Pista D', 'body_markdown' => 'D', 'delivery_mode' => 'random_player', 'evidence_codes' => ['V10']],
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
