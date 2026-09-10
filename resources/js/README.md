# Design system

Antes de escribir una vista nueva, lee esto. La regla de oro: **si ya existe un
componente, úsalo**; si necesitas una variante, agrégala al componente en vez de
escribir clases sueltas en la página.

## Dos superficies

Esto es lo primero que hay que entender.

| | **Plataforma** | **Caso** |
|---|---|---|
| Qué es | El producto: catálogo, cuenta, panel, consola del GM | La ficción: el expediente que lee el jugador |
| Se siente | Consola de agencia, oscura, moderna | Papel, mecanografiado, bordes duros |
| Tokens | `surface`, `ink`, `line`, `accent` | `paper`, `paper-ink`, `paper-line`, `paper-accent` |
| Tipografía | `--font-sans` (Inter), `--font-display` (Playfair Display) | `--font-case` (Courier Prime) |
| Bordes | Redondeados (`rounded-card`, `rounded-control`) | Rectos, `border-2` |
| Layouts | `AppLayout`, `GameMasterLayout`, `AuthLayout` | `PlayerLayout` |

El contraste es intencional: estás en una consola oscura y la evidencia que
abres es papel.

**Nunca mezcles los tokens de una superficie en la otra.** Un `text-ink` dentro
de una página del jugador es texto claro sobre papel claro. La superficie del
caso se activa con la clase `.case-surface`, que `PlayerLayout` ya pone.

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
Se usa en las dos superficies (`DataTag`, `.case-stamp`) — es lo que representa
"el sistema" incluso dentro del papel.

## Componentes

`components/ui/`

| Componente | Para |
|---|---|
| `Button` | Único botón de la plataforma. Con `href` renderiza un `Link` (debe poder abrirse en pestaña nueva). `loading` bloquea el control: así se evitan los envíos dobles. |
| `Card` + `CardHeader` | Panel elevado. `as="section"` cuando es estructura real de la página. |
| `Badge` | Estado corto. `GAME_STATUS_TONE` mapea estados de partida a tonos. |
| `Alert` | Mensaje inline. No renderiza nada si está vacío. |
| `EmptyState` | Lista vacía. **Siempre** di por qué está vacía y qué hacer. |
| `Skeleton` / `SkeletonList` / `SkeletonText` | Carga. |
| `Spinner` | Indicador inline. |
| `Modal` / `ConfirmModal` | Diálogo sobre `<dialog>` nativo. Toda acción destructiva pasa por `ConfirmModal`. |
| `Accordion` | Panel plegable. |
| `Container` | Ancho y gutters: `prose` \| `app` \| `wide`. |
| `Field` | `TextField`, `TextArea`, `SelectField`, `CheckboxField`. |
| `UserMenu` | Menú de cuenta. |
| `Brand` | El único mark+wordmark de MisterioCode. Todo layout lo usa en vez de repetirlo inline — así el logo real, cuando exista como archivo, se reemplaza en un solo sitio. |
| `DataTag` | Código, timestamp o estado en `--font-mono` (`MC-001`, `01:17:42`). Sin radio, a propósito — es la contraparte "dato" de `Badge` (control, con radio). |

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

## Prosa del caso

El markdown de sobres y testimonios se renderiza en el servidor y llega sin
clases. La clase `.case-prose` es lo que lo hace legible (títulos, listas,
tablas, separadores). Úsala en cualquier contenedor que reciba
`dangerouslySetInnerHTML` con contenido del caso.
