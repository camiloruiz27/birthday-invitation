<?php

/**
 * Free short case adapted from the supplied design document.
 * Narrative assets are intentionally external and listed in ASSET_PROMPTS.md.
 */
return array (
  'name' => 'El bingo del jueves',
  'version' => '1.0',
  'code' => 'GRATIS 130D51',
  'authority' => 'Archivo de investigación · MisterioCode',
  'victim' => 
  array (
    'name' => 'Fondo de premios del bingo comunitario',
    'label' => 'Caso investigado',
    'photo' => 'afectado.jpg',
    'incident' => 'Fraude de validación en un bingo vecinal; el premio discutido es USD 120.',
  ),
  'mechanics' => 
  array (
    0 => 'inbox',
    1 => 'timeline',
    2 => 'gallery',
    3 => 'interrogation',
    4 => 'accusation',
  ),
  'limits' => 
  array (
    'interrogation_questions' => 3,
  ),
  'catalog' => 
  array (
    'tagline' => 'Las bolas salieron como todos vieron. Lo que apareció después fue un cartón que no existía en el registro cerrado.',
    'tags' => 
    array (
      0 => 'Gratis',
      1 => 'Caso corto',
      2 => 'Ideal: 4 jugadores',
    ),
    'description' => 'Al terminar el bingo del jueves, Clara Ríos recibe un premio de USD 120 con el cartón B-118. Un voluntario recuerda que el listado de cartones se cerró antes del sorteo y pide verificar el pago. La investigación debe aclarar quién validó un cartón inexistente, cómo y por qué.

Caso gratuito de 19 minutos, diseñado para 3 a 4 jugadores.',
    'cover' => 'cover.jpg',
    'ad' => 
    array (
      'hook' => 'Las bolas salieron como todos vieron. Lo que apareció después fue un cartón que no existía en el registro cerrado.',
      'bullets' => 
      array (
        0 => '19 minutos de investigación',
        1 => 'Recomendado para 4 jugadores',
        2 => '3 interrogatorios con créditos incluidos',
      ),
      'cta' => 'Jugar gratis',
    ),
    'difficulty' => 'easy',
    'duration_minutes' => 19,
    'min_players' => 3,
    'max_players' => 4,
    'price_amount' => 0,
    'currency' => 'COP',
    'published' => false,
    'sort_order' => 905,
  ),
  'suspects' => 
  array (
    'clara' => 
    array (
      'name' => 'Clara Ríos',
      'role' => 'Participante y supuesta ganadora',
      'file' => 'suspects/clara.md',
      'photo' => 'clara.jpg',
      'connection' => 'Participante y supuesta ganadora',
      'motive' => 'Presentó el cartón que originó la discusión.',
      'alibi' => 'No puede demostrar inscripción antes de las 19:00.',
      'interrogation' => 
      array (
        'stages' => 
        array (
          0 => 
          array (
            'questions' => 3,
          ),
        ),
      ),
    ),
    'elena' => 
    array (
      'name' => 'Elena Vivas',
      'role' => 'Encargada de inscripciones',
      'file' => 'suspects/elena.md',
      'photo' => 'elena.jpg',
      'connection' => 'Encargada de inscripciones',
      'motive' => 'Custodiaba el listado y el sello.',
      'alibi' => 'No tuvo beneficio del premio.',
      'interrogation' => 
      array (
        'stages' => 
        array (
          0 => 
          array (
            'questions' => 3,
          ),
        ),
      ),
    ),
    'diego' => 
    array (
      'name' => 'Diego Beltrán',
      'role' => 'Organizador que autorizó el pago',
      'file' => 'suspects/diego.md',
      'photo' => 'diego.jpg',
      'connection' => 'Organizador que autorizó el pago',
      'motive' => 'Firmó el comprobante del premio.',
      'alibi' => 'Solo validó el pago a partir de papeles ya modificados.',
      'interrogation' => 
      array (
        'stages' => 
        array (
          0 => 
          array (
            'questions' => 3,
          ),
        ),
      ),
    ),
  ),
  'accusation' => 
  array (
    'culprit_points' => 34,
    'weapon' => 
    array (
      'label' => '¿Cómo lo hizo?',
      'hint' => 'Reconstruye el método usando las pruebas.',
      'answer' => 'Tomó el cartón de reserva B-118 después del sorteo, marcó los números que ya habían salido, aprovechó el sello de validación desatendido e incorporó una línea al listado de inscritos ya cerrado.',
      'points' => 33,
    ),
    'motive' => 
    array (
      'label' => '¿Por qué lo hizo?',
      'hint' => 'Explica el beneficio, presión o motivo.',
      'answer' => 'Debía reponer USD 120 que había adelantado al pequeño club de actividades que coordinaba y quiso recuperarlos sin reconocer el error.',
      'points' => 33,
    ),
    'questions' => 
    array (
    ),
  ),
  'solution' => 
  array (
    'culprit_slug' => 'clara',
    'headline' => 'Clara Ríos, participante que anunció la victoria fue responsable.',
    'motive' => 'Debía reponer USD 120 que había adelantado al pequeño club de actividades que coordinaba y quiso recuperarlos sin reconocer el error.',
    'method' => 'Tomó el cartón de reserva B-118 después del sorteo, marcó los números que ya habían salido, aprovechó el sello de validación desatendido e incorporó una línea al listado de inscritos ya cerrado.',
    'key_evidence' => 
    array (
      0 => 'Las bolas y los números anunciados fueron auténticos.',
      1 => 'El cartón B-118 no estaba inscrito cuando el sorteo empezó.',
      2 => 'Se utilizó una validación posterior para cobrar USD 120.',
      3 => 'Clara recibió el beneficio y sostenía una inscripción previa incompatible con los registros.',
    ),
    'file' => 'solucion.md',
    'exonerations' => 
    array (
      'elena' => 'El original cerrado que ella fotografió demuestra que B-118 no fue vendido por ella.',
      'diego' => 'No tuvo acceso al sello durante la adición ni relación con el club de Clara.',
    ),
  ),
  'timeline' => 
  array (
    0 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 0,
      'title' => 'Apertura del expediente',
      'source_file' => 'sobres/i0.md',
      'delivery_mode' => 'all',
    ),
    1 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 0,
      'title' => 'Informe: Declaración del cantor',
      'source_file' => 'sobres/i-m1.md',
      'delivery_mode' => 'all',
      'evidence_codes' => 
      array (
        0 => 'M1',
      ),
    ),
    2 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 0,
      'title' => 'Evidencia visual: Foto del registro al cierre',
      'source_file' => 'sobres/i-v1.md',
      'delivery_mode' => 'all',
      'evidence_codes' => 
      array (
        0 => 'V1',
      ),
    ),
    3 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 5,
      'title' => 'Informe: Apunte de una voluntaria',
      'source_file' => 'sobres/i-m2.md',
      'delivery_mode' => 'random_player',
      'evidence_codes' => 
      array (
        0 => 'M2',
      ),
      'cta_interrogation' => true,
    ),
    4 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 5,
      'title' => 'Evidencia visual: Cartón exhibido como ganador',
      'source_file' => 'sobres/i-v2.md',
      'delivery_mode' => 'random_player',
      'evidence_codes' => 
      array (
        0 => 'V2',
      ),
    ),
    5 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 10,
      'title' => 'Informe: Norma de cierre de inscripción',
      'source_file' => 'sobres/i-m3.md',
      'delivery_mode' => 'all',
      'evidence_codes' => 
      array (
        0 => 'M3',
      ),
    ),
    6 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 10,
      'title' => 'Evidencia visual: Fotografía de la mesa tras el sorteo',
      'source_file' => 'sobres/i-v3.md',
      'delivery_mode' => 'all',
      'evidence_codes' => 
      array (
        0 => 'V3',
      ),
    ),
    7 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 14,
      'title' => 'Evidencia visual: Registro actualizado después del sorteo',
      'source_file' => 'sobres/i-v4.md',
      'delivery_mode' => 'all',
      'evidence_codes' => 
      array (
        0 => 'V4',
      ),
    ),
    8 => 
    array (
      'type' => 'email',
      'trigger_offset_minutes' => 14,
      'title' => 'Evidencia visual: Comprobante de pago del premio',
      'source_file' => 'sobres/i-v5.md',
      'delivery_mode' => 'random_player',
      'evidence_codes' => 
      array (
        0 => 'V5',
      ),
    ),
    9 => 
    array (
      'type' => 'unlock',
      'trigger_offset_minutes' => 16,
      'title' => 'Presenta tu acusación',
      'source_file' => 'sobres/i-end.md',
      'delivery_mode' => 'all',
    ),
  ),
  'gallery' => 
  array (
    'sobres/i-v1.md' => 
    array (
      0 => 
      array (
        'file' => 'v1-foto-del-registro-al-cierre.png',
        'caption' => 'Foto del registro al cierre',
      ),
    ),
    'sobres/i-v2.md' => 
    array (
      0 => 
      array (
        'file' => 'v2-cart-on-exhibido-como-ganador.png',
        'caption' => 'Cartón exhibido como ganador',
      ),
    ),
    'sobres/i-v3.md' => 
    array (
      0 => 
      array (
        'file' => 'v3-fotograf-ia-de-la-mesa-tras-el-sorteo.png',
        'caption' => 'Fotografía de la mesa tras el sorteo',
      ),
    ),
    'sobres/i-v4.md' => 
    array (
      0 => 
      array (
        'file' => 'v4-registro-actualizado-despu-es-del-sorteo.png',
        'caption' => 'Registro actualizado después del sorteo',
      ),
    ),
    'sobres/i-v5.md' => 
    array (
      0 => 
      array (
        'file' => 'v5-comprobante-de-pago-del-premio.png',
        'caption' => 'Comprobante de pago del premio',
      ),
    ),
    'solucion.md' => 
    array (
      0 => 
      array (
        'file' => 'v1-foto-del-registro-al-cierre.png',
        'caption' => 'Foto del registro al cierre',
      ),
      1 => 
      array (
        'file' => 'v2-cart-on-exhibido-como-ganador.png',
        'caption' => 'Cartón exhibido como ganador',
      ),
      2 => 
      array (
        'file' => 'v3-fotograf-ia-de-la-mesa-tras-el-sorteo.png',
        'caption' => 'Fotografía de la mesa tras el sorteo',
      ),
      3 => 
      array (
        'file' => 'v4-registro-actualizado-despu-es-del-sorteo.png',
        'caption' => 'Registro actualizado después del sorteo',
      ),
      4 => 
      array (
        'file' => 'v5-comprobante-de-pago-del-premio.png',
        'caption' => 'Comprobante de pago del premio',
      ),
    ),
  ),
);
