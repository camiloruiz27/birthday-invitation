# Design system

Antes de escribir una vista nueva, lee esto. La regla de oro: **si ya existe un
componente, úsalo**; si necesitas una variante, agrégala al componente en vez de
escribir clases sueltas en la página.

## Dos superficies

Esto es lo primero que hay que entender.

| | **Plataforma** | **Caso** |
|---|---|---|
| Qué es | El producto: catálogo, cuenta, panel, consola del GM, y **todo el marco de la partida** | La ficción: el documento que lee el jugador |
| Se siente | Consola de agencia, oscura, moderna | Papel, mecanografiado, bordes duros |
| Tokens | `surface`, `ink`, `line`, `accent` | `paper`, `paper-ink`, `paper-line`, `paper-accent` |
| Tipografía | `--font-sans` (Inter), `--font-display` (Outfit) | `--font-case` (Courier Prime) |
| Bordes | Redondeados (`rounded-card`, `rounded-control`) | Rectos, `border-2` |
| Dónde vive | Todos los layouts, `PlayerLayout` incluido | `components/player/paper/Sheet.jsx` |

El contraste es intencional: estás en una consola oscura y la evidencia que
abres es papel.

**El papel es el documento, no la pantalla.** `PlayerLayout` ponía
`.case-surface` en la raíz, así que la partida entera era papel; hoy el marco
del jugador (header, navegación, listas de sobres, fichas, chat, formularios,
alertas) es plataforma como todo lo demás, y el papel aparece únicamente donde
hay un documento de ficción que leer: el cuerpo de un sobre, la declaración de
un sospechoso, la reconstrucción final. Eso se consigue con `Sheet`, que es lo
único que aplica `.case-surface`.

La razón no es solo estética: mientras el jugador estaba sobre papel, ninguna de
sus 5 pantallas podía usar `Button`, `Field`, `Badge`, `Alert` ni `EmptyState`
— todos construidos con tokens oscuros — así que cada una los reimplementaba a
mano, y ya habían divergido entre sí.

**Nunca mezcles los tokens de una superficie en la otra.** Un `text-ink` dentro
de un `Sheet` es texto claro sobre papel claro.

Un caso podrá traer su propia paleta más adelante; por eso nada del chrome de la
plataforma depende de los tokens `paper-*`.

## Tokens

Definidos en [`../css/app.css`](../css/app.css). Usa siempre los nombres
semánticos, no colores crudos — así un retema se hace ahí y en ningún otro lado.

```
surface / surface-raised / surface-sunken / surface-overlay   fondos
line / line-strong                                            bordes
ink / ink-muted / ink-subtle / ink-inverse                    texto
accent / accent-strong / accent-dim                           latón: acentos
danger / success / warning / info (+ -dim)                    estados
```

`--font-mono` (IBM Plex Mono) es la voz de datos: códigos, timestamps, estados.
Se usa en `.case-stamp` — es lo que representa "el sistema" incluso dentro del
papel.

## Componentes

`components/ui/`

| Componente | Para |
|---|---|
| `Button` | Único botón de la plataforma. Con `href` renderiza un `Link` (debe poder abrirse en pestaña nueva); con `href` + `external`, un `<a>` normal — el `Link` de Inertia intercepta el clic aunque lleve `target="_blank"`, así que un archivo o un sitio de fuera necesita `external`. `loading` bloquea el control: así se evitan los envíos dobles. |
| `Card` + `CardHeader` | Panel elevado. `as="section"` cuando es estructura real de la página. |
| `Badge` | Estado corto. `GAME_STATUS_TONE` mapea estados de partida a tonos. |
| `Alert` | Mensaje inline. No renderiza nada si está vacío. |
| `EmptyState` | Lista vacía. **Siempre** di por qué está vacía y qué hacer. |
| `Skeleton` / `SkeletonList` / `SkeletonText` | Carga. |
| `Spinner` | Indicador inline. |
| `Modal` / `ConfirmModal` | Diálogo sobre `<dialog>` nativo. Toda acción destructiva pasa por `ConfirmModal`. `size="lg"` para algo que se mira (un escaneo, una foto); las confirmaciones se quedan en `sm`. |
| `Accordion` | Panel plegable. **Desmonta lo que cierra**, así que no sirve para nada que el usuario pueda querer buscar con `Ctrl+F` — para documentos de caso está `CaseDocument`. |
| `Tabs` | Pestañas. Lleva dentro el `role="tablist"`, las flechas y el `aria-controls`: una fila de botones a mano pierde las tres cosas. |
| `Container` | Ancho y gutters: `prose` \| `app` \| `wide`. |
| `Field` | `TextField`, `TextArea`, `SelectField`, `CheckboxField`. |
| `UserMenu` | Menú de cuenta. |
| `Brand` | El único mark+wordmark de MisterioCode. Todo layout lo usa en vez de repetirlo inline — así el logo real, cuando exista como archivo, se reemplaza en un solo sitio. |
| `Reveal` | Aparece con un desplazamiento suave la primera vez que entra en pantalla al hacer scroll. `delay` escalona un grupo de hermanos. |

### Formularios

Todo input de la plataforma va por `Field.jsx`. Ahí el label, el hint y el error
quedan asociados al control (`aria-describedby`, `aria-invalid`) — si cada
página arma su propio input, ese cableado se olvida campo por campo.

## Estados obligatorios

Una funcionalidad no está terminada si le falta alguno que aplique:

- **Carga** — `loading` en `Button`, `Skeleton*` para contenido.
- **Vacío** — `EmptyState`, nunca espacio en blanco.
- **Error** — `Alert variant="error"`. Nunca muestres un error técnico crudo.
- **Destructivo** — `ConfirmModal` antes de ejecutar.

Los flash y los errores de validación los pinta el layout (`AppLayout`,
`AuthLayout`), no cada página.

## Mobile first

No es "que quepa": es diseñar para el teléfono.

- Controles con área táctil cómoda: `Button` en `md`/`lg` pasa de 44px.
- Inputs a `text-base` (16px). Menos que eso hace que iOS haga zoom al enfocar.
- Tablas anchas: tarjetas en móvil y tabla desde `sm` (ver `GameMaster/Results`),
  o scroll dentro de su propio contenedor. La página nunca scrollea en
  horizontal.
- `PlayerLayout` pone la navegación en una barra inferior alcanzable con el
  pulgar en móvil, y la sube al header en escritorio. Los jugadores están de pie
  con el teléfono en la mano.

## Accesibilidad

- Foco visible global (`:focus-visible`). No lo quites sin reemplazo.
- Cada layout tiene "saltar al contenido".
- `aria-current="page"` en la navegación activa.
- Imágenes con `alt` real; iconos decorativos con `aria-hidden`.
- `prefers-reduced-motion` ya está respetado en `app.css`.
- Números que cambian en su sitio (reloj, contadores): clase `.tabular`.

## Las pantallas del jugador

`components/player/`

| Componente | Para |
|---|---|
| `paper/Sheet` + `SheetHeader` | La hoja de papel sobre la mesa oscura. Lo único que aplica `.case-surface`. Es el `Card` de la superficie de ficción. |
| `paper/Stamp` | El `Badge` de la superficie de ficción: cuadrado, mono, con letra espaciada. Usa los tonos `danger`/`success` **base** (los `-strong` son para fondo oscuro y sobre papel se ven lavados). |
| `CaseDocument` | Parte un documento del caso en secciones navegables. **Todo HTML del caso pasa por aquí**, nunca un `dangerouslySetInnerHTML` suelto. |
| `InboxItem` + `EnvelopeRow` | El sobre, en sus dos estados: fila de la lista y cosa que se lee. |
| `SuspectCard` | Una persona en el tablero. `suspectState()` es el único sitio que decide en qué estado está. |
| `ChatMessage` / `ChatComposer` | El interrogatorio. Conversación en vivo, así que va en consola, no en papel. |
| `GalleryGrid` | Escaneos de evidencia; se abren en un `Modal`, no en otra pestaña. |
| `AudioPlayer` | `<audio>` nativo con `preload="none"`. |

### `CaseDocument` y la prosa del caso

El markdown de sobres, testimonios y solución se renderiza en el servidor y
llega sin clases. `.case-prose` es lo que lo hace legible (títulos, listas,
tablas, separadores), y `CaseDocument` es lo que lo hace **navegable**: parte el
HTML por encabezados y presenta cada sección como una hoja aparte, plegable.
Sin él, un solo sobre puede ser un muro de 4.000 palabras.

Tres cosas que hay que saber antes de tocarlo:

- **Corta por `##`, no por `---`.** Los `---` no están puestos de forma
  confiable en los archivos de caso y las declaraciones no tienen ninguno.
- **Parsea con un `<template>`, no con una expresión regular.** Partir el
  string puede cortar a mitad de un elemento; mover nodos ya parseados no.
- **Nunca desmonta una sección cerrada** (`hidden="until-found"`), porque la
  conducta dominante de un jugador es buscar con `Ctrl+F` y un acordeón normal
  rompería eso en silencio. Ese atributo hay que ponerlo con `setAttribute`:
  React 18 trata `hidden` como booleano y lo convertiría en `hidden=""`.

Si escribes contenido nuevo de caso, dale a cada sección su `## `: eso es lo que
el jugador va a ver como fila del índice.
