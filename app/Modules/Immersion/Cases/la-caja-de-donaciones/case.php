<?php

/**
 * Free short case adapted from the supplied design document.
 * Narrative assets are intentionally external and listed in ASSET_PROMPTS.md.
 */
return array (
  'name' => 'La caja de donaciones',
  'version' => '1.0',
  'code' => 'GRATIS 914005',
  'authority' => 'Archivo de investigación · MisterioCode',
  'victim' => 
  array (
    'name' => 'Campaña vecinal de alimentos y confianza de sus donantes',
    'label' => 'Caso investigado',
    'photo' => 'campaña-vecinal.png',
    'incident' => 'Uso no autorizado de USD 300 de donaciones y denuncia falsa de un robo.',
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
    'tagline' => 'La caja estaba intacta. Faltaban USD 300, exactamente lo que costaba salvar los alimentos almacenados.',
    'tags' => 
    array (
      0 => 'Gratis',
      1 => 'Caso corto',
      2 => 'Ideal: 3 jugadores',
    ),
    'description' => 'Una campaña vecinal cuenta USD 960 en donaciones. Poco después, el organizador denuncia el robo de USD 300: solo quedan USD 660. La caja principal no está forzada y el almacén de alimentos tuvo una avería urgente ese día. Investigad quién usó el dinero, cómo y por qué se afirmó que había un ladrón.

Caso gratuito de 19 minutos, diseñado para 3 a 4 jugadores.',
    'cover' => 'cover.png',
    'ad' => 
    array (
      'hook' => 'La caja estaba intacta. Faltaban USD 300, exactamente lo que costaba salvar los alimentos almacenados.',
      'bullets' => 
      array (
        0 => '19 minutos de investigación',
        1 => 'Recomendado para 3 jugadores',
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
    'sort_order' => 902,
  ),
  'suspects' => 
  array (
    'julian' => 
    array (
      'name' => 'Julián Soto',
      'role' => 'Organizador de la campaña',
      'file' => 'suspects/julian.md',
      'photo' => 'julian-soto.png',
      'connection' => 'Organizador de la campaña',
      'motive' => 'Tenía acceso al dinero y fue quien denunció el robo.',
      'alibi' => 'Se presenta como quien descubrió el faltante.',
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
    'mara' => 
    array (
      'name' => 'Mara Peña',
      'role' => 'Tesorera y responsable del recuento',
      'file' => 'suspects/mara.md',
      'photo' => 'mara-pena.png',
      'connection' => 'Tesorera y responsable del recuento',
      'motive' => 'Firmó USD 960 y controlaba el dinero al inicio.',
      'alibi' => 'Tiene coartada de tareas de inventario desde las 16:10.',
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
    'olga' => 
    array (
      'name' => 'Olga Rivas',
      'role' => 'Voluntaria en recepción de donaciones',
      'file' => 'suspects/olga.md',
      'photo' => 'olga-rivas.png',
      'connection' => 'Voluntaria en recepción de donaciones',
      'motive' => 'Se alejó de la mesa durante la ausencia del dinero.',
      'alibi' => 'Su presencia en la mesa no cubre todos los minutos, pero no tuvo el sobre.',
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
      'answer' => 'Retiró USD 300 del sobre de efectivo ya contado, bajo su custodia para ingresarlo en el banco, y lo pagó en efectivo al almacén por una reparación necesaria. Después inventó que alguien había robado el dinero de la caja.',
      'points' => 33,
    ),
    'motive' => 
    array (
      'label' => '¿Por qué lo hizo?',
      'hint' => 'Explica el beneficio, presión o motivo.',
      'answer' => 'Temía perder alimentos donados si no reparaban el cierre del depósito ese día y sabía que el reglamento exigía dos autorizaciones para gastos de más de USD 100. Prefirió actuar solo y encubrir la decisión para conservar la confianza del grupo.',
      'points' => 33,
    ),
    'questions' => 
    array (
    ),
  ),
  'solution' => 
  array (
    'culprit_slug' => 'julian',
    'headline' => 'Julián Soto, organizador de la campaña fue responsable.',
    'motive' => 'Temía perder alimentos donados si no reparaban el cierre del depósito ese día y sabía que el reglamento exigía dos autorizaciones para gastos de más de USD 100. Prefirió actuar solo y encubrir la decisión para conservar la confianza del grupo.',
    'method' => 'Retiró USD 300 del sobre de efectivo ya contado, bajo su custodia para ingresarlo en el banco, y lo pagó en efectivo al almacén por una reparación necesaria. Después inventó que alguien había robado el dinero de la caja.',
    'key_evidence' => 
    array (
      0 => 'La caja no fue forzada y el efectivo ya estaba en un sobre contado.',
      1 => 'El faltante de USD 300 coincide con un pago auténtico de USD 300.',
      2 => 'Julián custodió el sobre durante el pago y luego denunció un robo de la caja.',
      3 => 'El pago urgente fue real, pero carecía de las dos autorizaciones requeridas.',
    ),
    'file' => 'solucion.md',
    'exonerations' => 
    array (
      'mara' => 'La firma de entrega y el nuevo conteo separan su función del gasto posterior.',
      'olga' => 'La fotografía del precinto y la custodia del sobre descartan que sacara USD 300 de la caja.',
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
      'title' => 'Informe: Aviso del almacén de alimentos',
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
      'title' => 'Evidencia visual: Foto de la caja precintada',
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
      'title' => 'Informe: Apunte de recepción',
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
      'title' => 'Evidencia visual: Hoja de recuento inicial',
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
      'title' => 'Informe: Norma de uso de donaciones',
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
      'title' => 'Evidencia visual: Recibo de reparación urgente',
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
      'title' => 'Evidencia visual: Acta de efectivo restante',
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
      'title' => 'Evidencia visual: Registro de custodia y reporte',
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
        'file' => 'v1-caja-precintada.png',
        'caption' => 'Foto de la caja precintada',
      ),
    ),
    'sobres/i-v2.md' => 
    array (
      0 => 
      array (
        'file' => 'v2-hoja-de-recuento.png',
        'caption' => 'Hoja de recuento inicial',
      ),
    ),
    'sobres/i-v3.md' => 
    array (
      0 => 
      array (
        'file' => 'v3-recibo-reparacion.png',
        'caption' => 'Recibo de reparación urgente',
      ),
    ),
    'sobres/i-v4.md' => 
    array (
      0 => 
      array (
        'file' => 'v4-acta-efectivo-restante.png',
        'caption' => 'Acta de efectivo restante',
      ),
    ),
    'sobres/i-v5.md' => 
    array (
      0 => 
      array (
        'file' => 'v5-registro-custodia.png',
        'caption' => 'Registro de custodia y reporte',
      ),
    ),
    'solucion.md' => 
    array (
      0 => 
      array (
        'file' => 'v1-caja-precintada.png',
        'caption' => 'Foto de la caja precintada',
      ),
      1 => 
      array (
        'file' => 'v2-hoja-de-recuento.png',
        'caption' => 'Hoja de recuento inicial',
      ),
      2 => 
      array (
        'file' => 'v3-recibo-reparacion.png',
        'caption' => 'Recibo de reparación urgente',
      ),
      3 => 
      array (
        'file' => 'v4-acta-efectivo-restante.png',
        'caption' => 'Acta de efectivo restante',
      ),
      4 => 
      array (
        'file' => 'v5-registro-custodia.png',
        'caption' => 'Registro de custodia y reporte',
      ),
    ),
  ),
);
