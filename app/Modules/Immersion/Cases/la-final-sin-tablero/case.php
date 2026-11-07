<?php

/**
 * Free short case adapted from the supplied design document.
 * Narrative assets are intentionally external and listed in ASSET_PROMPTS.md.
 */
return array (
  'name' => 'La final sin tablero',
  'version' => '1.0',
  'code' => 'GRATIS 06B2CE',
  'authority' => 'Archivo de investigación · MisterioCode',
  'victim' => 
  array (
    'name' => 'Alba Reyes, finalista perjudicada, y la transparencia del torneo',
    'label' => 'Caso investigado',
    'photo' => 'alba-reyes.png',
    'incident' => 'Fraude de acta tras una final de juego de mesa; premio USD 200 y patrocinio USD 1,000.',
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
    'tagline' => 'El tablero se retiró. En la foto original gana Alba por 47 a 43; en el acta oficial, esos mismos números favorecen a Bruno.',
    'tags' => 
    array (
      0 => 'Gratis',
      1 => 'Caso corto',
      2 => 'Ideal: 4 jugadores',
    ),
    'description' => 'El café Tablero Abierto cerró su final y retiró el tablero. El resultado oficial anuncia a Bruno como vencedor y le asigna USD 200, pero la fotógrafa del torneo asegura conservar una imagen tomada al acabar. Un patrocinio de USD 1,000 dependía del resultado. Aclarad quién cambió el acta, cómo y por qué.

Caso gratuito de 25 minutos, diseñado para 3 a 5 jugadores.',
    'cover' => 'cover.png',
    'ad' => 
    array (
      'hook' => 'El tablero se retiró. En la foto original gana Alba por 47 a 43; en el acta oficial, esos mismos números favorecen a Bruno.',
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
    'sort_order' => 901,
  ),
  'suspects' => 
  array (
    'hugo' => 
    array (
      'name' => 'Hugo Serrano',
      'role' => 'Organizador y custodio del acta final',
      'file' => 'suspects/hugo.md',
      'photo' => 'hugo-serrano.png',
      'connection' => 'Organizador y custodio del acta final',
      'motive' => 'Fue el último en tener la hoja que define el resultado oficial.',
      'alibi' => 'Sostiene que el tablero ya estaba retirado y que nadie podía discutir el acta.',
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
    'mireya' => 
    array (
      'name' => 'Mireya Campos',
      'role' => 'Árbitra de la final',
      'file' => 'suspects/mireya.md',
      'photo' => 'mireya-campos.png',
      'connection' => 'Árbitra de la final',
      'motive' => 'Firmó la hoja y pudo equivocarse al contar puntos.',
      'alibi' => 'No volvió a tocar el acta final después de las 19:07.',
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
      'name' => 'Bruno Costa',
      'role' => 'Finalista declarado ganador',
      'file' => 'suspects/bruno.md',
      'photo' => 'bruno-costa.png',
      'connection' => 'Finalista declarado ganador',
      'motive' => 'Recibió USD 200 y estaba vinculado al patrocinador.',
      'alibi' => 'No tuvo acceso al documento entre las 19:07 y 19:12.',
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
      'answer' => 'Recibió la hoja arbitral original con Alba 47 y Bruno 43 y, al transcribir el acta oficial después de retirar el tablero, invirtió los puntos: Alba 43, Bruno 47. No alteró el tablero ni falsificó la foto original.',
      'points' => 33,
    ),
    'motive' => 
    array (
      'label' => '¿Por qué lo hizo?',
      'hint' => 'Explica el beneficio, presión o motivo.',
      'answer' => 'El convenio ofrecía USD 1,000 al café si ganaba Bruno, finalista asociado al patrocinador; Hugo quiso asegurar ese ingreso y redirigió el premio individual de USD 200 a Bruno.',
      'points' => 33,
    ),
    'questions' => 
    array (
    ),
  ),
  'solution' => 
  array (
    'culprit_slug' => 'hugo',
    'headline' => 'Hugo Serrano, organizador del torneo fue responsable.',
    'motive' => 'El convenio ofrecía USD 1,000 al café si ganaba Bruno, finalista asociado al patrocinador; Hugo quiso asegurar ese ingreso y redirigió el premio individual de USD 200 a Bruno.',
    'method' => 'Recibió la hoja arbitral original con Alba 47 y Bruno 43 y, al transcribir el acta oficial después de retirar el tablero, invirtió los puntos: Alba 43, Bruno 47. No alteró el tablero ni falsificó la foto original.',
    'key_evidence' => 
    array (
      0 => 'El resultado original fue Alba 47, Bruno 43.',
      1 => 'El acta oficial invirtió 47–43 a 43–47 después de la partida.',
      2 => 'Hugo controlaba el documento justo en la transcripción.',
      3 => 'Hugo tenía incentivo financiero por declarar a Bruno ganador.',
    ),
    'file' => 'solucion.md',
    'exonerations' => 
    array (
      'mireya' => 'La foto y la cadena de entrega respaldan su hoja, no el acta invertida.',
      'bruno' => 'La custodia y la foto original prueban que su beneficio no demuestra que ejecutara el fraude.',
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
      'title' => 'Informe: Reglas básicas de la final',
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
      'title' => 'Evidencia visual: Mesa de final después del retiro',
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
      'title' => 'Informe: Nota de la fotógrafa',
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
      'title' => 'Evidencia visual: Fotografía de la hoja de puntuación original',
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
      'trigger_offset_minutes' => 14,
      'title' => 'Informe: Aclaración del procedimiento',
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
      'trigger_offset_minutes' => 14,
      'title' => 'Evidencia visual: Acta oficial publicada',
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
      'trigger_offset_minutes' => 19,
      'title' => 'Evidencia visual: Cadena de custodia de puntuaciones',
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
      'trigger_offset_minutes' => 19,
      'title' => 'Evidencia visual: Acuerdo de patrocinio del café',
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
        'file' => 'v1-mesa-de-final.png',
        'caption' => 'Mesa de final después del retiro',
      ),
    ),
    'sobres/i-v2.md' => 
    array (
      0 => 
      array (
        'file' => 'v2-hoja-de-puntuacion-original.png',
        'caption' => 'Fotografía de la hoja de puntuación original',
      ),
    ),
    'sobres/i-v3.md' => 
    array (
      0 => 
      array (
        'file' => 'v3-acta-oficial.png',
        'caption' => 'Acta oficial publicada',
      ),
    ),
    'sobres/i-v4.md' => 
    array (
      0 => 
      array (
        'file' => 'v4-cadena-de-custodia.png',
        'caption' => 'Cadena de custodia de puntuaciones',
      ),
    ),
    'sobres/i-v5.md' => 
    array (
      0 => 
      array (
        'file' => 'v5-acuerdo-de-patrocinio.png',
        'caption' => 'Acuerdo de patrocinio del café',
      ),
    ),
    'solucion.md' => 
    array (
      0 => 
      array (
        'file' => 'v1-mesa-de-final.png',
        'caption' => 'Mesa de final después del retiro',
      ),
      1 => 
      array (
        'file' => 'v2-hoja-de-puntuacion-original.png',
        'caption' => 'Fotografía de la hoja de puntuación original',
      ),
      2 => 
      array (
        'file' => 'v3-acta-oficial.png',
        'caption' => 'Acta oficial publicada',
      ),
      3 => 
      array (
        'file' => 'v4-cadena-de-custodia.png',
        'caption' => 'Cadena de custodia de puntuaciones',
      ),
      4 => 
      array (
        'file' => 'v5-acuerdo-de-patrocinio.png',
        'caption' => 'Acuerdo de patrocinio del café',
      ),
    ),
  ),
);
