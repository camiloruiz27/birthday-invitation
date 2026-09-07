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
