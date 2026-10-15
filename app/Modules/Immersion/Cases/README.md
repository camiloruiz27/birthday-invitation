# Casos de misterio

Cada caso es **una carpeta**, no una clase. El motor no tiene código por caso:
descubre los casos buscando carpetas que contengan un `case.php`.

```
Cases/
  CaseRegistry.php        descubrimiento (slug -> definicion)
  CaseDefinition.php      accesores tipados sobre el manifiesto
  CaseContent.php         lector de markdown de UN caso
  steve-jacobs/
    case.php              el manifiesto: identidad, mecanicas, sospechosos,
                          linea de tiempo, galeria
    content/              narrativa verbatim (no se parafrasea en ningun lado)
      victima.md
      sobres/sobre-1.md   sobre-2.md  sobre-3.md
      suspects/*.md       un archivo por persona interrogable
```

Los medios viven aparte, en `public/immersion/<slug>/`:

```
public/immersion/steve-jacobs/
  gallery/    recortes del PDF original que acompañan cada Sobre
  photos/     retratos de sospechosos y de la victima
  audio/      las grabaciones de la linea de tiempo
```

## El audio del caso

**No se genera en tiempo de juego.** Los guiones de la línea de tiempo son los
mismos para todas las mesas, así que se graban una vez fuera de la plataforma y
se despliegan con el caso, igual que las fotos. Eso saca al TTS del camino
crítico: una mesa nunca puede quedarse sin su nota de voz porque el modelo esté
saturado.

Para obtener los guiones listos para pegar en el estudio, con su Scene y su
Sample Context:

```bash
php artisan immersion:export-audio-scripts steve-jacobs
php artisan immersion:export-audio-scripts --missing   # solo los que faltan
```

Sube los `.wav` a `public/immersion/<slug>/audio/` y apúntalos en el evento:

```php
'audio_file' => 'lab-goddard.wav',
```

Los campos `audio_scene`, `audio_context`, `audio_speaker` y `audio_voice` son
**solo para el estudio**: describen cómo grabar y no llegan a la base de datos.
El texto hablado va en `audio_script` y **solo** debe contener lo que se dice —
las acotaciones ("la llamada se corta") van en `audio_scene`, o el sintetizador
las lee en voz alta.

Un `audio_file` declarado cuyo archivo no exista lo detecta `CaseAssetsTest`;
sin esa comprobación el correo saldría sin adjunto y nadie se enteraría.

La **confesión** del final premium es la excepción: se genera por partida,
porque menciona las preguntas que esa mesa le hizo al culpable.

## Crear un caso nuevo

1. `mkdir Cases/mi-caso` y escribe `Cases/mi-caso/case.php` (copia
   `steve-jacobs/case.php` como referencia de la forma esperada).
2. Pon la narrativa en `Cases/mi-caso/content/`.
3. Pon las imágenes en `public/immersion/mi-caso/{gallery,photos}`.
4. Listo: el registry ya lo ve. No hay que registrar nada ni tocar el motor.

Para que sea el caso por defecto de las partidas nuevas:
`IMMERSION_DEFAULT_CASE=mi-caso` en `.env`.

## Qué puede variar por caso

| Clave del manifiesto | Efecto |
|---|---|
| `name`, `version` | Identidad; `version` se estampa en cada partida |
| `code` | Referencia corta ("SF 554301"): asunto del correo y encabezados |
| `authority` | Quién "envía" el material: pie del correo y también el nombre del remitente (`From`) del sobre de la línea de tiempo. La dirección de correo nunca cambia, solo este nombre |
| `victim` | Nombre, foto y `label` (sello en el tablero; por defecto "Víctima", usa "Desaparecida/o" si sigue con vida o el caso lo oculta). El `label` también decide cómo hablan los prompts de la IA: si dice «desaparec…» hablan de «la desaparición de X»; si no, de «la muerte de X». Se puede forzar con `victim.incident` («el secuestro de», o una frase con `{name}`) |
| `mechanics` | Qué mecánicas existen en este caso |
| `limits.interrogation_questions` | Tope de preguntas normales por persona (5) y lo que hereda quien no declara `stages`. Cada persona puede tener menos: entre 3 y este tope |
| `suspects` | Roster interrogable (ficha corta + foto + archivo de testimonio). `'accusable' => false` lo saca de la acusación (testigos, sistemas); `'interrogable' => false` lo saca del tablero de interrogatorios pero se puede acusar (un culpable al que nadie localizó) |
| `accusation` | Opcional. Preguntas extra del formulario de acusación con su respuesta oficial y puntos, más los puntos del culpable, del método y del motivo. Ver "La acusación y su calificación" |
| `timeline` | Eventos que se adjuntan a toda partida nueva del caso |
| `gallery` | Imágenes que acompañan el texto de cada `source_file` |
| `gallery_excluded_headings` | Secciones que ya se ven como imagen y se omiten del texto |
| `solution` | El desenlace: quién fue, cómo, por qué (ver abajo) |

## La solución

El final de un caso es **contenido autorado**. La IA nunca decide quién fue —
ni aquí, ni en el epílogo, ni en la confesión en audio: esas mecánicas solo
ponen este material en boca de un personaje.

```php
'solution' => [
    'culprit_slug' => 'rachel-miller',  // debe existir en 'suspects'
    'headline'     => 'La frase que cierra el caso.',
    'motive'       => 'Por qué lo hizo.',
    'method'       => 'Cómo lo hizo.',
    'key_evidence' => ['La prueba que lo señala.'],
    'file'         => 'solucion.md',    // la revelación larga, verbatim

    // Por qué CADA inocente no pudo ser. Lo usa el epílogo personalizado:
    // sin una línea autorada por sospechoso, el modelo tendría que razonar
    // el error del jugador, que es justo inventar el desenlace.
    'exonerations' => ['elizabeth-foster' => '…'],

    // Solo si el culpable tiene `'interrogable' => false`: quién escribe el
    // epílogo de quienes aciertan, contándolo desde fuera (alguien con
    // testimonio, que sí se pueda interrogar). Sin esto, el epílogo lo
    // «escribiría» alguien que nadie puede interrogar.
    'epilogue_speaker' => 'mercedes-quintanilla',

    // Guion de confesión, escrito para reproducirse tal cual, y la voz que
    // lo lee (nombre de voz de Gemini; null usa la del gateway).
    'confession_voice'  => 'Kore',
    'confession_script' => '…',
],
```

**Qué finales ofrece el caso lo decide este bloque**, no el código. Sin
`exonerations` completas no hay epílogo; sin `confession_script` no hay audio de
confesión. `CaseDefinition::supportedEndings()` lo calcula, y el formulario de
creación muestra el resto como "No disponible en este caso" en vez de
esconderlo — para que se vea qué le falta al caso.

**Mientras `culprit_slug` sea `PENDIENTE`, o nombre a alguien que no está en
`suspects`, el caso se considera sin solución**: no se ofrece revelar nada. Es
deliberado — una mesa nunca debe ver "PENDIENTE" como respuesta. Un slug mal
escrito lo detecta el test de integridad del manifiesto, no los jugadores.

Dos reglas más las verifica `CaseRegistryTest` sobre **todos** los casos
instalados, no solo el actual:

- Todo sospechoso que no sea el culpable necesita su `exoneration` escrita. El
  epílogo personalizado no tiene plan B: sin esa línea, el modelo tendría que
  deducir en qué se equivocó el jugador.
- El culpable **no** lleva `exoneration`. Sería darle al epílogo un argumento de
  por qué no pudo haber sido quien fue.

`solucion.md` también aparece en `gallery`, como cualquier otro `source_file`:
así la revelación puede reusar las mismas fotos de evidencia que la mesa ya vio,
sin schema nuevo.

> **`solucion.md` nunca debe llegar al servicio de IA.** Los testimonios de
> `content/suspects/` sí se le envían verbatim durante los interrogatorios; la
> solución no.

No todos los casos necesitan todas las mecánicas ni todas las claves: lo que
falte cae a un valor por defecto sensato (ver `CaseDefinition`).

## La acusación y su calificación

El formulario siempre pide **quién**, **cómo** y **por qué**. Un caso puede
añadir sus propias preguntas y fijar cuánto vale cada parte:

```php
'accusation' => [
    'culprit_points' => 30,                       // acertar al culpable (exacto, no lo decide la IA)
    'weapon' => ['label' => '¿Con qué?', 'hint' => '…', 'points' => 15],   // answer: por defecto solution.method
    'motive' => ['label' => '¿Por qué?', 'hint' => '…', 'points' => 20],   // answer: por defecto solution.motive
    'questions' => [
        [
            'key' => 'origen_averia',             // [a-z0-9_]
            'label' => '¿Quién originó la avería y por qué?',
            'hint' => 'Nombra a la persona y su razón.',
            'answer' => 'Ramiro, por miedo a los recortes, sin intención de matar.',
            'points' => 15,
            'guidance' => 'Cero si lo trata como parte de una conspiración con el asesinato.',
        ],
    ],
],
```

Al revelarse la solución, `Jobs\GradeAccusations` hace **una sola** llamada al
gateway (`POST /grade`) con todas las acusaciones de la mesa, para que todas se
califiquen con el mismo criterio. Cada respuesta recibe puntos (0 al máximo del
criterio) y un comentario de una frase. El marcador ordena primero a quien
acertó al culpable y, entre ellos, por puntaje: ese es el desempate.

- Sin el bloque, el caso igual se califica: método y motivo contra la solución
  (40/30/30 puntos).
- Las respuestas oficiales nunca llegan al navegador del jugador.
- Si la IA no responde, cada acusación queda con los puntos del culpable y el
  marcador sigue funcionando.
- Costo en créditos: `IMMERSION_COST_GRADING` (por defecto 0); interruptor:
  `IMMERSION_AI_GRADING`.

## Preguntas del interrogatorio por etapas

Cada persona interrogable tiene entre **3 y 5 preguntas** normales, según
cuánto tenga que decir, y **3 más si su caso trae confrontaciones para ella**
(hasta 8). La partida reserva los créditos del total desde el inicio.

Cómo se decide el número de una persona (testigos y sistemas, sobre todo): se
cuentan los hechos distintos que su `.md` le deja confirmar, cada uno es una
pregunta que vale la pena hacer, y se da `clamp(hechos, 3, 5)`. Un testigo con
dos hechos tiene 3; uno con cuatro, 4; uno con cinco o más, 5. Menos de 3 no:
la tercera pregunta absorbe la que se gasta en un saludo. Los sospechosos con
historia completa (el culpable y los principales) se quedan en 5. Cada pregunta
cuesta una conexión a la base, así que no sobran preguntas que no llevan a nada.

Las preguntas no tienen por qué abrirse todas a la vez. Si la persona podría
admitir algo que solo se sabe más adelante en el cronograma, se reparten en
etapas que esperan a la evidencia que las justifica (`interrogation.stages`
dentro de su entrada de `suspects`):

```php
'interrogation' => ['stages' => [
    ['questions' => 3],                                   // desde que abre el interrogatorio
    ['questions' => 2, 'after' => ['V6']],                // cuando se entrega V6
    ['questions' => 3, 'after' => ['V14'], 'confrontation' => true],  // las 3 extra
]],
```

- `after`: códigos de `evidence_codes` de eventos del cronograma; la etapa se
  abre cuando **todos** se han entregado en esa partida.
- La suma de las etapas normales va de 3 a `limits.interrogation_questions`
  (5); las de `confrontation => true` suman exactamente 3. `CaseRegistryTest`
  lo valida, y que el primer bloque no espere a nadie y que todo código de
  `after` exista en el cronograma.
- Una etapa posterior que guarda una revelación clave se conserva al recortar
  (por ejemplo `2, 1@V15` y no `3`): si el jugador gasta todas las preguntas
  antes de que llegue la evidencia, la que la abre tiene que seguir existiendo.
- Sin bloque `stages`: una sola etapa de 5 (el tope del caso), abierta desde el
  inicio. Para dar menos, declara el bloque con una etapa de 3 o 4.
- `CaseDefinition::baseQuestionRange()` da el mínimo y el máximo de preguntas
  normales entre las personas del caso, y `questionsPerPersonLabel()` el texto
  que se le muestra al jugador («5 preguntas» / «entre 3 y 5 preguntas»).
- Cambiar los números de un caso publicado: las partidas aún sin reclamar usan
  los nuevos; una sesión ya abierta conserva su `max_questions`. Hazlo cuando no
  haya partidas en curso.
- Una etapa gastada deja la sesión **en espera**, no cerrada: el jugador que
  empezó el interrogatorio vuelve cuando se abre la siguiente (nadie más puede
  tomarlo). El correo/bandeja del evento que abre preguntas lo anuncia solo.
- Las preguntas de etapas posteriores deben coincidir con lo que el `.md` de la
  persona admite solo «con» una evidencia: si una confrontación del `.md`
  exige V14, la etapa de confrontación espera a V14.
- Reglas para decidir: lo que depende de una prueba posterior va a una etapa
  posterior; si nada depende de ellas, no hace falta bloque. No dividir en más
  etapas de las que el contenido pide.

## Contenido faltante

Un archivo `.md` que no exista se renderiza como cadena vacía **a propósito**:
un sobre sin escribir deja ese mensaje en blanco en vez de romper el resto de
la línea de tiempo.

## Versionado

Una partida guarda `case_slug` y `case_version` al crearse. El contenido se
resuelve a través de esa versión, así que publicar una corrección de un caso
no reescribe una investigación en curso.
