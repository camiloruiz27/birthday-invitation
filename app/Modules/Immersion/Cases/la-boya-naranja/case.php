<?php

/**
 * Free short case adapted from the supplied design document.
 * Narrative assets are intentionally external and listed in ASSET_PROMPTS.md.
 */
return array (
  'name' => 'La boya naranja',
  'version' => '1.0',
  'code' => 'GRATIS EEAD8A',
  'authority' => 'Archivo de investigación · MisterioCode',
  'victim' => 
  array (
    'name' => 'Raúl Mena, competidor perjudicado, y la integridad de la clasificación',
    'label' => 'Caso investigado',
    'photo' => 'afectado.jpg',
    'incident' => 'Sabotaje informativo de una carrera recreativa de kayak con premio USD 150.',
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
    'tagline' => 'La boya naranja cambió de lugar por seguridad. Una embarcación perdió tiempo porque siguió un mapa entregado después del cambio.',
    'tags' => 
    array (
      0 => 'Gratis',
      1 => 'Caso corto',
      2 => 'Ideal: 4 jugadores',
    ),
    'description' => 'En una carrera local de kayak, Raúl Mena acusa a la organización de haber movido la boya naranja a mitad del recorrido. Se sabe que hubo una modificación de seguridad antes de la salida. Su clasificación cayó y el premio de USD 150 quedó en disputa. Debéis decidir quién difundió el mapa equivocado, cómo y por qué.

Caso gratuito de 25 minutos, diseñado para 3 a 5 jugadores.',
    'cover' => 'cover.jpg',
    'ad' => 
    array (
      'hook' => 'La boya naranja cambió de lugar por seguridad. Una embarcación perdió tiempo porque siguió un mapa entregado después del cambio.',
      'bullets' => 
      array (
        0 => '25 minutos de investigación',
        1 => 'Recomendado para 4 jugadores',
        2 => '3 interrogatorios con créditos incluidos',
      ),
      'cta' => 'Jugar gratis',
    ),
    'difficulty' => 'medium',
    'duration_minutes' => 25,
    'min_players' => 3,
    'max_players' => 5,
    'price_amount' => 0,
    'currency' => 'COP',
    'published' => false,
    'sort_order' => 903,
  ),
  'suspects' => 
  array (
    'diego' => 
    array (
      'name' => 'Diego Soto',
      'role' => 'Competidor rival de Raúl',
      'file' => 'suspects/diego.md',
      'photo' => 'diego.jpg',
      'connection' => 'Competidor rival de Raúl',
      'motive' => 'Se beneficiaba de que Raúl se equivocara.',
      'alibi' => 'Dice que estuvo en su embarcación, pero nadie lo confirma en ese intervalo.',
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
    'eva' => 
    array (
      'name' => 'Eva Roldán',
      'role' => 'Responsable de recorrido y seguridad',
      'file' => 'suspects/eva.md',
      'photo' => 'eva.jpg',
      'connection' => 'Responsable de recorrido y seguridad',
      'motive' => 'Ordenó mover el objeto que causó la disputa.',
      'alibi' => 'La foto y aviso la sitúan en el traslado, no distribuyendo la hoja incorrecta.',
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
    'ines' => 
    array (
      'name' => 'Inés Vega',
      'role' => 'Voluntaria del puesto de registro',
      'file' => 'suspects/ines.md',
      'photo' => 'ines.jpg',
      'connection' => 'Voluntaria del puesto de registro',
      'motive' => 'Tenía mapas impresos y pudo repartir una versión incorrecta.',
      'alibi' => 'Hoja de entrega firmada por Raúl la sitúa en la mesa oficial.',
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
      'answer' => 'Imprimió a las 09:26 una copia del mapa obsoleto v2 y se la entregó a Raúl a las 09:32, afirmando falsamente que era la revisión más reciente, aunque el puesto oficial ya le había dado la v3.',
      'points' => 33,
    ),
    'motive' => 
    array (
      'label' => '¿Por qué lo hizo?',
      'hint' => 'Explica el beneficio, presión o motivo.',
      'answer' => 'Quería que Raúl perdiera tiempo buscando la boya en Punta Norte y así aumentar sus posibilidades de ganar el premio de USD 150.',
      'points' => 33,
    ),
    'questions' => 
    array (
    ),
  ),
  'solution' => 
  array (
    'culprit_slug' => 'diego',
    'headline' => 'Diego Soto, competidor rival fue responsable.',
    'motive' => 'Quería que Raúl perdiera tiempo buscando la boya en Punta Norte y así aumentar sus posibilidades de ganar el premio de USD 150.',
    'method' => 'Imprimió a las 09:26 una copia del mapa obsoleto v2 y se la entregó a Raúl a las 09:32, afirmando falsamente que era la revisión más reciente, aunque el puesto oficial ya le había dado la v3.',
    'key_evidence' => 
    array (
      0 => 'La boya se movió por seguridad antes de la salida.',
      1 => 'Raúl recibió v3 correctamente, y después otro mapa obsoleto.',
      2 => 'Diego imprimió v2 después de que fuera invalidado.',
      3 => 'La sustitución del mapa beneficiaba al rival que competía por USD 150.',
    ),
    'file' => 'solucion.md',
    'exonerations' => 
    array (
      'eva' => 'Aviso firmado y foto confirman decisión previa legítima.',
      'ines' => 'El registro prueba que entregó v3, no la copia posterior.',
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
      'title' => 'Informe: Boletín de seguridad previo a salida',
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
      'title' => 'Evidencia visual: Fotografía de la boya naranja',
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
      'trigger_offset_minutes' => 7,
      'title' => 'Informe: Nota de impresión de logística',
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
      'trigger_offset_minutes' => 7,
      'title' => 'Evidencia visual: Aviso de seguridad y mapa vigente v3',
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
      'trigger_offset_minutes' => 15,
      'title' => 'Informe: Testimonio del competidor afectado',
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
      'trigger_offset_minutes' => 15,
      'title' => 'Evidencia visual: Mapa antiguo recibido por Raúl',
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
      'trigger_offset_minutes' => 20,
      'title' => 'Evidencia visual: Control de entrega del puesto',
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
      'trigger_offset_minutes' => 20,
      'title' => 'Evidencia visual: Registro de impresión de hojas',
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
      'trigger_offset_minutes' => 22,
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
        'file' => 'v1-fotograf-ia-de-la-boya-naranja.png',
        'caption' => 'Fotografía de la boya naranja',
      ),
    ),
    'sobres/i-v2.md' => 
    array (
      0 => 
      array (
        'file' => 'v2-aviso-de-seguridad-y-mapa-vigente-v3.png',
        'caption' => 'Aviso de seguridad y mapa vigente v3',
      ),
    ),
    'sobres/i-v3.md' => 
    array (
      0 => 
      array (
        'file' => 'v3-mapa-antiguo-recibido-por-ra-ul.png',
        'caption' => 'Mapa antiguo recibido por Raúl',
      ),
    ),
    'sobres/i-v4.md' => 
    array (
      0 => 
      array (
        'file' => 'v4-control-de-entrega-del-puesto.png',
        'caption' => 'Control de entrega del puesto',
      ),
    ),
    'sobres/i-v5.md' => 
    array (
      0 => 
      array (
        'file' => 'v5-registro-de-impresi-on-de-hojas.png',
        'caption' => 'Registro de impresión de hojas',
      ),
    ),
    'solucion.md' => 
    array (
      0 => 
      array (
        'file' => 'v1-fotograf-ia-de-la-boya-naranja.png',
        'caption' => 'Fotografía de la boya naranja',
      ),
      1 => 
      array (
        'file' => 'v2-aviso-de-seguridad-y-mapa-vigente-v3.png',
        'caption' => 'Aviso de seguridad y mapa vigente v3',
      ),
      2 => 
      array (
        'file' => 'v3-mapa-antiguo-recibido-por-ra-ul.png',
        'caption' => 'Mapa antiguo recibido por Raúl',
      ),
      3 => 
      array (
        'file' => 'v4-control-de-entrega-del-puesto.png',
        'caption' => 'Control de entrega del puesto',
      ),
      4 => 
      array (
        'file' => 'v5-registro-de-impresi-on-de-hojas.png',
        'caption' => 'Registro de impresión de hojas',
      ),
    ),
  ),
);
