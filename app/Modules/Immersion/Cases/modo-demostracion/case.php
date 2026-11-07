<?php

/**
 * Free short case adapted from the supplied design document.
 * Narrative assets are intentionally external and listed in ASSET_PROMPTS.md.
 */
return array (
  'name' => 'Modo demostración',
  'version' => '1.0',
  'code' => 'GRATIS AAE870',
  'authority' => 'Archivo de investigación · MisterioCode',
  'victim' => 
  array (
    'name' => 'Equipo de la startup Vista Clara y confianza de los inversores',
    'label' => 'Caso investigado',
    'photo' => 'afectado.jpg',
    'incident' => 'Sabotaje de la presentación de una startup mediante restauración deliberada de una versión antigua.',
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
    'tagline' => 'La demo falló mostrando registros internos. El desarrollador había corregido precisamente ese defecto una hora antes.',
    'tags' => 
    array (
      0 => 'Gratis',
      1 => 'Caso corto',
      2 => 'Ideal: 4 jugadores',
    ),
    'description' => 'Durante una presentación para obtener USD 50,000 de inversión, la aplicación Vista Clara muestra datos de prueba internos en la pantalla. Se culpa a la última publicación del desarrollador. Sin embargo, el equipo conserva un registro de versiones. Investigad quién cambió la demo, mediante qué acción y con qué propósito.

Caso gratuito de 25 minutos, diseñado para 3 a 5 jugadores.',
    'cover' => 'cover.jpg',
    'ad' => 
    array (
      'hook' => 'La demo falló mostrando registros internos. El desarrollador había corregido precisamente ese defecto una hora antes.',
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
    'sort_order' => 904,
  ),
  'suspects' => 
  array (
    'nico' => 
    array (
      'name' => 'Nico León',
      'role' => 'Desarrollador responsable de publicar la versión 1.4',
      'file' => 'suspects/nico.md',
      'photo' => 'nico.jpg',
      'connection' => 'Desarrollador responsable de publicar la versión 1.4',
      'motive' => 'Su nombre aparece en el historial inmediatamente antes de la demo fallida.',
      'alibi' => 'El registro verifica que su última acción fue publicar 1.4 segura.',
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
    'lucia' => 
    array (
      'name' => 'Lucía Mendoza',
      'role' => 'Directora de producto',
      'file' => 'suspects/lucia.md',
      'photo' => 'lucia.jpg',
      'connection' => 'Directora de producto',
      'motive' => 'Tenía poder para decidir qué versión mostrar.',
      'alibi' => 'Sostiene que siguió la recomendación del equipo, sin respaldo en el historial.',
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
    'teo' => 
    array (
      'name' => 'Teo Rivas',
      'role' => 'Presentador de la demostración',
      'file' => 'suspects/teo.md',
      'photo' => 'teo.jpg',
      'connection' => 'Presentador de la demostración',
      'motive' => 'Prometió 98 % ante inversores y estuvo delante de la pantalla cuando falló.',
      'alibi' => 'Preparaba la presentación en la sala a la hora de la reversión.',
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
      'answer' => 'A las 17:52 autorizó desde su cuenta P-02 restaurar la versión 1.3, aunque Nico había publicado la 1.4 corregida a las 17:44. La reversión reactivó una pantalla interna de datos de prueba que apareció en público.',
      'points' => 33,
    ),
    'motive' => 
    array (
      'label' => '¿Por qué lo hizo?',
      'hint' => 'Explica el beneficio, presión o motivo.',
      'answer' => 'La inversión potencial de USD 50,000 dependía de mostrar al menos 90 % de precisión. La versión 1.3 simulaba 98 % con respuestas preparadas, mientras 1.4 ofrecía una medición real del 76 %; Lucía quiso conservar la cifra atractiva.',
      'points' => 33,
    ),
    'questions' => 
    array (
    ),
  ),
  'solution' => 
  array (
    'culprit_slug' => 'lucia',
    'headline' => 'Lucía Mendoza, directora de producto fue responsable.',
    'motive' => 'La inversión potencial de USD 50,000 dependía de mostrar al menos 90 % de precisión. La versión 1.3 simulaba 98 % con respuestas preparadas, mientras 1.4 ofrecía una medición real del 76 %; Lucía quiso conservar la cifra atractiva.',
    'method' => 'A las 17:52 autorizó desde su cuenta P-02 restaurar la versión 1.3, aunque Nico había publicado la 1.4 corregida a las 17:44. La reversión reactivó una pantalla interna de datos de prueba que apareció en público.',
    'key_evidence' => 
    array (
      0 => 'La versión 1.4 corregía el fallo y estaba activa antes de la demo.',
      1 => 'Una restauración activa 1.3 provocó que apareciera la tabla interna.',
      2 => 'Lucía aprobó la restauración desde P-02.',
      3 => 'La elección de 1.3 favorecía una cifra engañosa vinculada a USD 50,000.',
    ),
    'file' => 'solucion.md',
    'exonerations' => 
    array (
      'nico' => 'El historial y la solicitud P-02 separan el arreglo de la reversión.',
      'teo' => 'Registro de permisos y presencia en ensayo excluyen la restauración.',
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
      'title' => 'Informe: Declaración de la asistente',
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
      'title' => 'Evidencia visual: Captura de la pantalla durante el fallo',
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
      'title' => 'Informe: Apunte del ensayo técnico',
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
      'title' => 'Evidencia visual: Historial de versiones simplificado',
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
      'title' => 'Informe: Guía no técnica de versiones',
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
      'title' => 'Evidencia visual: Comparación de mediciones de demo',
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
      'title' => 'Evidencia visual: Solicitud de restauración',
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
      'title' => 'Evidencia visual: Condiciones de la inversión',
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
        'file' => 'v1-captura-de-la-pantalla-durante-el-fallo.png',
        'caption' => 'Captura de la pantalla durante el fallo',
      ),
    ),
    'sobres/i-v2.md' => 
    array (
      0 => 
      array (
        'file' => 'v2-historial-de-versiones-simplificado.png',
        'caption' => 'Historial de versiones simplificado',
      ),
    ),
    'sobres/i-v3.md' => 
    array (
      0 => 
      array (
        'file' => 'v3-comparaci-on-de-mediciones-de-demo.png',
        'caption' => 'Comparación de mediciones de demo',
      ),
    ),
    'sobres/i-v4.md' => 
    array (
      0 => 
      array (
        'file' => 'v4-solicitud-de-restauraci-on.png',
        'caption' => 'Solicitud de restauración',
      ),
    ),
    'sobres/i-v5.md' => 
    array (
      0 => 
      array (
        'file' => 'v5-condiciones-de-la-inversi-on.png',
        'caption' => 'Condiciones de la inversión',
      ),
    ),
    'solucion.md' => 
    array (
      0 => 
      array (
        'file' => 'v1-captura-de-la-pantalla-durante-el-fallo.png',
        'caption' => 'Captura de la pantalla durante el fallo',
      ),
      1 => 
      array (
        'file' => 'v2-historial-de-versiones-simplificado.png',
        'caption' => 'Historial de versiones simplificado',
      ),
      2 => 
      array (
        'file' => 'v3-comparaci-on-de-mediciones-de-demo.png',
        'caption' => 'Comparación de mediciones de demo',
      ),
      3 => 
      array (
        'file' => 'v4-solicitud-de-restauraci-on.png',
        'caption' => 'Solicitud de restauración',
      ),
      4 => 
      array (
        'file' => 'v5-condiciones-de-la-inversi-on.png',
        'caption' => 'Condiciones de la inversión',
      ),
    ),
  ),
);
