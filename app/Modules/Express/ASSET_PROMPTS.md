# Express — prompts de assets

Los prompts de cada caso viven **junto al caso**, igual que en los casos normales:

| Caso | Prompts | Se sube a | Portada | Retratos | Fotos | Audio |
|---|---|---|---|---|---|---|
| El mensaje de las 11:03 | [`Cases/el-mensaje-de-las-11-03/ASSET_PROMPTS.md`](Cases/el-mensaje-de-las-11-03/ASSET_PROMPTS.md) | `public/express-assets/el-mensaje-de-las-11-03/` | 1 | 3 | 0 | 1 (`e1`) |
| El apagón que no existió | [`Cases/el-apagon-que-no-existio/ASSET_PROMPTS.md`](Cases/el-apagon-que-no-existio/ASSET_PROMPTS.md) | `public/express-assets/el-apagon-que-no-existio/` | 1 | 3 | 1 (`e1`) | 0 |
| La obra del marco azul | [`Cases/la-obra-del-marco-azul/ASSET_PROMPTS.md`](Cases/la-obra-del-marco-azul/ASSET_PROMPTS.md) | `public/express-assets/la-obra-del-marco-azul/` | 1 | 3 | 2 (`e1`, `e4`) | 0 |
| La firma de las 17:55 | [`Cases/la-firma-de-las-17-55/ASSET_PROMPTS.md`](Cases/la-firma-de-las-17-55/ASSET_PROMPTS.md) | `public/express-assets/la-firma-de-las-17-55/` | 1 | 3 | 1 (`e1`) | 0 |
| El pago perfecto | [`Cases/el-pago-perfecto/ASSET_PROMPTS.md`](Cases/el-pago-perfecto/ASSET_PROMPTS.md) | `public/express-assets/el-pago-perfecto/` | 1 | 3 | 1 (`e5`, CCTV) | 0 |
| El mural de medianoche | [`Cases/el-mural-de-medianoche/ASSET_PROMPTS.md`](Cases/el-mural-de-medianoche/ASSET_PROMPTS.md) | `public/express-assets/el-mural-de-medianoche/` | 1 | 3 | 3 (`e1`, `e3`, `e6`) | 0 |
| La última función | [`Cases/la-ultima-funcion/ASSET_PROMPTS.md`](Cases/la-ultima-funcion/ASSET_PROMPTS.md) | `public/express-assets/la-ultima-funcion/` | 1 | 3 | 0 | 1 (`e3`) |

**Total: 7 portadas · 21 retratos · 9 fotos · 2 audios.**

El juego funciona sin ninguno de estos archivos (iniciales en vez de retratos, evidencias solo con texto), así que se puede ensayar antes y subirlos conforme estén.

## Nombres de archivo que reconoce la app

| Archivo | Para qué | Formato |
|---|---|---|
| `hero.jpg` | portada del caso | 4:5, 1080×1350 |
| `portraits/<id>.jpg` | retrato del sospechoso | 3:4, 900×1200 |
| `e<n>.jpg` | foto de la evidencia `E<n>` (solo tipo foto o CCTV) | 4:5 |
| `audio/e<n>.mp3` | audio de la evidencia `E<n>` (solo tipo audio) | MP3, mono, 7–12 s |

Acepta también `.jpeg`, `.png` y `.webp` para imágenes, y `.m4a` u `.ogg` para audio. No hace falta ningún comando: la app los detecta por nombre. JPG de ~350 KB o menos por pieza (una feria con Wi-Fi compartida lo agradece).

## Reglas para todo lo que se genere

- **Nada de texto dentro de las imágenes** (horas, nombres, porcentajes, logos legibles). El texto exacto de cada pista lo muestra la app. Si el generador escribe letras, se descarta la imagen.
- Fotografía documental realista, luz creíble, sin estética de póster, sin flechas ni círculos, sin marcas reales, sin rostros de personas reales.
- Un caso = una apariencia consistente: genera primero la imagen maestra y deriva el resto conservando ángulo, objetos, ropa y luz (cada archivo de caso dice qué par de imágenes se comparan entre sí).
- Los 3 sospechosos de un caso deben leerse como personas distintas a simple vista.

## Piezas de la feria (no son de un caso)

**Cartel del stand.** No hace falta generarlo: abre `/express/usb-cali-2026/cartel` e imprime (el QR lleva los UTM de la feria). Si quieres un fondo de marca, genera una **textura oscura sin texto** (papel de expediente envejecido con luz cálida, 3:4) y úsala detrás del cartel en tu editor.

**Teaser de 12 s (opcional, uno por caso o uno general para redes).**

> Clip ambiental realista de 12 segundos, vertical 9:16, sin diálogo ni texto generado. Tres planos de 4 segundos: el lugar antes del incidente; un acercamiento a un objeto clave sin revelar el desenlace; un expediente que se cierra. Sensación de investigación social y de decisión, no terror. Tono fotográfico documental, sin culpable señalado, sin sangre. Los textos y la captura real de la app se añaden después en edición.

**Captura de pantalla real para redes.** Grabar solo la app real con datos ficticios: entrar con el QR → crear sala → un segundo celular se une → cada pantalla ve pistas distintas → compartir → preguntar a un sospechoso → acusar → pantalla final. No mostrar la solución en el tráiler.

## Antes del evento

Revisa en un celular que cada caso abra bien: portada en «Crear sala», retratos en «Interrogar», fotos en «Pistas».
