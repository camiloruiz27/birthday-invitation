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

Las imágenes viven aparte, en `public/immersion/<slug>/`:

```
public/immersion/steve-jacobs/
  gallery/    recortes del PDF original que acompañan cada Sobre
  photos/     retratos de sospechosos y de la victima
```

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
| `authority` | Quién "envía" el material, en el pie del correo |
| `victim` | Nombre y foto de la víctima |
| `mechanics` | Qué mecánicas existen en este caso |
| `limits.interrogation_questions` | Presupuesto de preguntas por sospechoso |
| `suspects` | Roster interrogable (ficha corta + foto + archivo de testimonio) |
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
    'culprit_slug' => 'kevin-huang',   // debe existir en 'suspects'
    'headline'     => 'La frase que cierra el caso.',
    'motive'       => 'Por qué lo hizo.',
    'method'       => 'Cómo lo hizo.',
    'key_evidence' => ['La prueba que lo señala.'],
    'file'         => 'solucion.md',   // la revelación larga, verbatim

    // Por qué CADA inocente no pudo ser. Lo usa el epílogo personalizado:
    // sin una línea autorada por sospechoso, el modelo tendría que razonar
    // el error del jugador, que es justo inventar el desenlace.
    'exonerations' => ['elizabeth-foster' => '…'],

    // Guion de confesión, escrito para reproducirse tal cual.
    'confession_script' => null,
],
```

**Mientras `culprit_slug` sea `PENDIENTE`, o nombre a alguien que no está en
`suspects`, el caso se considera sin solución**: no se ofrece revelar nada. Es
deliberado — una mesa nunca debe ver "PENDIENTE" como respuesta. Un slug mal
escrito lo detecta el test de integridad del manifiesto, no los jugadores.

> **`solucion.md` nunca debe llegar al servicio de IA.** Los testimonios de
> `content/suspects/` sí se le envían verbatim durante los interrogatorios; la
> solución no.

No todos los casos necesitan todas las mecánicas ni todas las claves: lo que
falte cae a un valor por defecto sensato (ver `CaseDefinition`).

## Contenido faltante

Un archivo `.md` que no exista se renderiza como cadena vacía **a propósito**:
un sobre sin escribir deja ese mensaje en blanco en vez de romper el resto de
la línea de tiempo.

## Versionado

Una partida guarda `case_slug` y `case_version` al crearse. El contenido se
resuelve a través de esa versión, así que publicar una corrección de un caso
no reescribe una investigación en curso.
