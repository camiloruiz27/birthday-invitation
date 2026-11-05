<?php

/**
 * Free short case adapted from the supplied design document.
 * Narrative assets are intentionally external and listed in ASSET_PROMPTS.md.
 */
return array (
  'name' => 'Bajo la cabeza del dragón',
  'version' => '1.0',
  'code' => 'GRATIS D46E43',
  'authority' => 'Archivo de investigación · MisterioCode',
  'victim' => 
  array (
    'name' => 'Colecta del taller juvenil',
    'label' => 'Caso investigado',
    'photo' => 'afectado.jpg',
    'incident' => 'Robo de USD 240 de una colecta solidaria durante un festival de barrio.',
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
    'tagline' => 'Una fotografía muestra al dragón junto al almacén en el minuto del robo. Dos voluntarios llevaron esa misma cabeza.',
    'tags' => 
    array (
      0 => 'Gratis',
      1 => 'Caso corto',
      2 => 'Ideal: 4 jugadores',
    ),
    'description' => 'A las 17:12, Lidia descubre que falta el sobre cerrado con USD 240 destinado al taller juvenil del barrio. La foto del dragón junto al pasillo del almacén circula como prueba. Debéis identificar a quien tomó el sobre, cómo aprovechó el disfraz y por qué.

Caso gratuito de 24 minutos, diseñado para 3 a 5 jugadores.',
    'cover' => 'cover.jpg',
    'ad' => 
    array (
      'hook' => 'Una fotografía muestra al dragón junto al almacén en el minuto del robo. Dos voluntarios llevaron esa misma cabeza.',
      'bullets' => 
      array (
        0 => '24 minutos de investigación',
        1 => 'Recomendado para 4 jugadores',
        2 => '2 interrogatorios con créditos incluidos',
      ),
      'cta' => 'Jugar gratis',
    ),
    'difficulty' => 'easy',
    'duration_minutes' => 24,
    'min_players' => 3,
    'max_players' => 5,
    'price_amount' => 0,
    'currency' => 'COP',
    'published' => false,
    'sort_order' => 906,
  ),
  'suspects' => 
  array (
    'ariel' => 
    array (
      'name' => 'Ariel Pardo',
      'role' => 'Primer portador del dragón',
      'file' => 'suspects/ariel.md',
      'photo' => 'ariel.jpg',
      'connection' => 'Primer portador del dragón',
      'motive' => 'La foto muestra al dragón y él lo llevó durante el festival.',
      'alibi' => 'A las 17:09 se encontraba descansando detrás del escenario.',
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
    'bruno' => 
    array (
      'name' => 'Bruno Navas',
      'role' => 'Segundo portador del dragón',
      'file' => 'suspects/bruno.md',
      'photo' => 'bruno.jpg',
      'connection' => 'Segundo portador del dragón',
      'motive' => 'Tuvo acceso al disfraz en el momento del robo.',
      'alibi' => 'Afirma haber seguido siempre el recorrido, contradicho por la fotografía y los horarios.',
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
      'answer' => 'A las 17:09, mientras llevaba el mismo traje de dragón que Ariel había usado antes, entró por la puerta de servicio abierta para transportar suministros, tomó el sobre de USD 240 y lo ocultó temporalmente en la cabeza desmontable del traje.',
      'points' => 33,
    ),
    'motive' => 
    array (
      'label' => '¿Por qué lo hizo?',
      'hint' => 'Explica el beneficio, presión o motivo.',
      'answer' => 'Quería cubrir ese día una deuda personal de USD 240 del local de ensayo de su agrupación y temía que le suspendieran el acceso.',
      'points' => 33,
    ),
    'questions' => 
    array (
    ),
  ),
  'solution' => 
  array (
    'culprit_slug' => 'bruno',
    'headline' => 'Bruno Navas, voluntario del segundo relevo fue responsable.',
    'motive' => 'Quería cubrir ese día una deuda personal de USD 240 del local de ensayo de su agrupación y temía que le suspendieran el acceso.',
    'method' => 'A las 17:09, mientras llevaba el mismo traje de dragón que Ariel había usado antes, entró por la puerta de servicio abierta para transportar suministros, tomó el sobre de USD 240 y lo ocultó temporalmente en la cabeza desmontable del traje.',
    'key_evidence' => 
    array (
      0 => 'La fotografía no permite identificar al portador solo por el disfraz.',
      1 => 'Bruno llevaba el DR-01 en el pasillo a las 17:09.',
      2 => 'El sobre desapareció durante una oportunidad real sin forzar la puerta.',
      3 => 'Bruno tenía un motivo económico inmediato y mintió sobre su recorrido.',
    ),
    'file' => 'solucion.md',
    'exonerations' => 
    array (
      'ariel' => 'Relevo firmado; foto de la costura posterior a su turno; testimonio de Nora.',
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
      'title' => 'Informe: Nota de logística del festival',
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
      'title' => 'Evidencia visual: Fotografía del dragón en el pasillo',
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
      'trigger_offset_minutes' => 6,
      'title' => 'Informe: Nota del equipo de vestuario',
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
      'trigger_offset_minutes' => 6,
      'title' => 'Evidencia visual: Detalle de vestuario: reparación',
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
      'trigger_offset_minutes' => 13,
      'title' => 'Informe: Informe de comprobación',
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
      'trigger_offset_minutes' => 13,
      'title' => 'Evidencia visual: Registro de relevos firmado',
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
      'trigger_offset_minutes' => 18,
      'title' => 'Evidencia visual: Ficha de custodia del sobre',
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
      'trigger_offset_minutes' => 18,
      'title' => 'Evidencia visual: Aviso de cobro de local de ensayo',
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
      'trigger_offset_minutes' => 21,
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
        'file' => 'v1-fotograf-ia-del-drag-on-en-el-pasillo.png',
        'caption' => 'Fotografía del dragón en el pasillo',
      ),
    ),
    'sobres/i-v2.md' => 
    array (
      0 => 
      array (
        'file' => 'v2-detalle-de-vestuario-reparaci-on.png',
        'caption' => 'Detalle de vestuario: reparación',
      ),
    ),
    'sobres/i-v3.md' => 
    array (
      0 => 
      array (
        'file' => 'v3-registro-de-relevos-firmado.png',
        'caption' => 'Registro de relevos firmado',
      ),
    ),
    'sobres/i-v4.md' => 
    array (
      0 => 
      array (
        'file' => 'v4-ficha-de-custodia-del-sobre.png',
        'caption' => 'Ficha de custodia del sobre',
      ),
    ),
    'sobres/i-v5.md' => 
    array (
      0 => 
      array (
        'file' => 'v5-aviso-de-cobro-de-local-de-ensayo.png',
        'caption' => 'Aviso de cobro de local de ensayo',
      ),
    ),
    'solucion.md' => 
    array (
      0 => 
      array (
        'file' => 'v1-fotograf-ia-del-drag-on-en-el-pasillo.png',
        'caption' => 'Fotografía del dragón en el pasillo',
      ),
      1 => 
      array (
        'file' => 'v2-detalle-de-vestuario-reparaci-on.png',
        'caption' => 'Detalle de vestuario: reparación',
      ),
      2 => 
      array (
        'file' => 'v3-registro-de-relevos-firmado.png',
        'caption' => 'Registro de relevos firmado',
      ),
      3 => 
      array (
        'file' => 'v4-ficha-de-custodia-del-sobre.png',
        'caption' => 'Ficha de custodia del sobre',
      ),
      4 => 
      array (
        'file' => 'v5-aviso-de-cobro-de-local-de-ensayo.png',
        'caption' => 'Aviso de cobro de local de ensayo',
      ),
    ),
  ),
);
