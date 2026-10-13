# Prompts de assets — El Color que Falta

Total: **1 portada + 9 retratos + 14 evidencias visuales + 0 audios**. No hay audio dentro del
cronograma: el único audio del caso es la confesión de Marco (A1), que se genera por partida y
no se graba.

Todo se guarda en `public/immersion/el-color-que-falta/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos |
| `gallery/` | las 14 evidencias visuales |
| `audio/` | (vacía: el caso no lleva audios grabados) |

**Nota de numeración:** no existe V6. El diseño la omite a propósito para mantener la
numeración del plan original (las evidencias son V1–V5 y V7–V12). No generar ni renumerar.
Las versiones parciales y completas de una misma pieza son archivos distintos: V2a/V2, V3a/V3
y V4a/V4.

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Regla del color** (abajo): fija el tono del «verde Aurora» antes de generar V1, V2, V2a,
   V4, V4a y V11.
3. **Portada** (para empezar a vender y a probar el catálogo).
4. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
5. **Evidencias** en orden de entrega (ver tabla). V2 y V2a deben salir de la misma sesión de
   fitting; V4 y V4a, de la misma página; V3 y V3a, del mismo diseño de hoja; V8 y V9, de la
   misma oficina.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V5, V11 |
| Expansión | 10 | V1, V3a (hoja inicial, página 1) |
| Pistas privadas | 20 | V2a (fragmento), V4a (fragmento) |
| Complicación | 32 | V3 (completa), V7, V12 |
| Piezas completas | 32 | V2, V4, V10 |
| Giro | 45 | V8, V9 |
| Cierre | 52 | (sin imágenes: coartadas cruzadas) |
| Acusación | 60 | (formulario) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien fechas, nombres ni tablas**, y aquí el dato exacto
ES la prueba (14/03 ≠ 13/03; «Beige neutro»; el crédito a Camila Rojas). Para V1, V2, V2a, V3a,
V3, V4, V4a, V7, V9 (iniciales «V. M.»), V10, V11 y V12: genera la imagen con la zona de texto **vacía o con texto de
relleno ilegible**, y compón después el texto exacto indicado en "TEXTO A COMPONER" con un
editor (Canva, Figma, Photoshop). Verifica cada fecha y cada nombre contra `case.php` y
contra los `.md` antes de guardar.

## Regla del color: el «verde Aurora»

El color se identifica **siempre por su nombre escrito** y por documentos redundantes, nunca
por percepción cromática fina. Por eso:

- Un solo tono para todas las piezas: **verde esmeralda frío, saturado, con un toque de
  turquesa** (aprox. #0E9F6E). Es el «verde Aurora». Fíjalo con una muestra de referencia y
  úsalo idéntico en V1, V2, V2a, V4, V4a y V11.
- El color de reemplazo es **«Beige neutro»**: arena clara, apagada (aprox. #D8CBB5). El
  contraste con el verde debe ser inconfundible a simple vista.
- En toda imagen donde aparezca el verde Aurora debe haber también **texto que lo nombre**
  (anotación, tarjeta de fitting, pie de foto o etiqueta). Si el generador no lo escribe bien,
  se compone después.
- Evitar en cualquier otra imagen (V5, V8, V9, retratos) objetos verdes que compitan con él.

## Biblia visual de continuidad (pegar en los retratos)

- **Valeria Montes**, 38: cabello rubio ceniza corto, ropa de diseñador elegante y minimalista.
- **Marco Iturri**, 52: complexión elegante, traje casual de inversionista, reloj discreto de lujo. Calmado y confiable, sin aspecto ominoso.
- **Daniela Suárez**, 45: cabello negro recogido, ropa de trabajo práctica con cinta métrica al cuello.
- **Camila Rojas**, 29: cabello castaño con mechas de color, ropa creativa y cómoda, manchas de tela en las manos.
- **Hugo Peña**, 36: complexión robusta, delantal de taller, alfileres en el puño.
- **Leticia Vargas**, 41: ropa cómoda de trabajo, cámara profesional colgada al cuello.
- **Sofía Mejía**, 24: ropa casual de asistente, tablet en mano.
- **Paola Ibarra**, 33: auriculares de coordinación backstage, tablilla con el orden de looks.
- **Casa Solari**: taller luminoso con mesas de corte y maniquíes, backstage funcional con racks de ropa organizados por look, oficinas discretas, luz natural combinada con luz de estudio fotográfico.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio en el mundo de la moda: el taller luminoso de una casa de moda independiente al caer la tarde, con una fila de maniquíes vestidos con prendas en tonos neutros (beige, crema, gris) bajo una luz cálida de estudio, y en primer plano, sobre una mesa de corte, un único boceto a mano con una pincelada de verde esmeralda como el único toque de color de toda la escena; sensación de tiempo que se acaba antes de un desfile, tensión creativa contenida y de algo que falta. Sin personas visibles. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** personas desaparecidas dramatizadas, sangre, iconografía policial, estética de terror, tijeras amenazantes, texto o logos, marcas de moda reales.

## 2. Retratos

(`photos/valeria-montes.png` — víctima: la diseñadora desaparecida)
**PROMPT:** Retrato diegético tipo foto de prensa de una mujer de 38 años, cabello rubio ceniza corto, ropa de diseñador elegante y minimalista, expresión segura y serena de diseñadora principal, fondo neutro de taller luminoso de una casa de moda. Formato retrato. **EVITAR:** apariencia de víctima, miedo, dramatización, logos de marcas reales.

(`photos/marco-iturri.png` — Marco Iturri)
**PROMPT:** Retrato diegético de un hombre de 52 años, complexión elegante, traje casual de inversionista, reloj discreto de lujo, expresión calmada, segura y cordial, fondo de oficina discreta o backstage con luz cálida. Formato retrato. **EVITAR:** apariencia de villano, mirada siniestra, iluminación dramática, gesto amenazante.

(`photos/daniela-suarez.png` — Daniela Suárez)
**PROMPT:** Retrato diegético de una mujer de 45 años, cabello negro recogido, ropa de trabajo práctica con una cinta métrica al cuello, expresión meticulosa y levemente a la defensiva, fondo de taller con mesas de corte. Formato retrato. **EVITAR:** gesto de culpa obvio, sospecha teatral.

(`photos/camila-rojas.png` — Camila Rojas)
**PROMPT:** Retrato diegético de una mujer de 29 años, cabello castaño con mechas de color, ropa creativa y cómoda, manchas de tela en las manos, expresión contenida y profesional con un matiz de resentimiento que no se muestra del todo, fondo de estación de diseño con bocetos. Formato retrato. **EVITAR:** apariencia de villana, gesto agresivo, prendas verdes protagonistas.

(`photos/hugo-pena.png` — Hugo Peña)
**PROMPT:** Retrato diegético de un hombre de 36 años, complexión robusta, delantal de taller, alfileres en el puño de la camisa, expresión técnica, algo hosca y evasiva, fondo de taller con patrones colgados. Formato retrato. **EVITAR:** villano obvio, gesto agresivo, herramientas en actitud amenazante.

(`photos/leticia-vargas.png` — Leticia Vargas)
**PROMPT:** Retrato diegético de una mujer de 41 años, ropa cómoda de trabajo, cámara profesional colgada al cuello, expresión profesional y levemente nerviosa, fondo de estudio fotográfico con luces y fondo de papel. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, blogs o teléfonos con fotos visibles.

(`photos/sofia-mejia.png` — Sofía Mejía)
**PROMPT:** Retrato diegético de una mujer de 24 años, ropa casual de asistente, tablet en mano, expresión joven, atenta y algo incómoda, fondo de estación de diseño con bocetos. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/paola-ibarra.png` — Paola Ibarra)
**PROMPT:** Retrato diegético de una mujer de 33 años, auriculares de coordinación backstage, tablilla con el orden de looks, expresión práctica, rápida y concreta, fondo de backstage con racks de ropa. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/registro-taller-proveedor.png` — registro del taller y del proveedor de telas)
**PROMPT:** Imagen diegética tipo retrato funcional de una pantalla de un sistema simple de pedidos de un proveedor textil, con una tabla sobria de pedidos y entregas, usada como avatar de la fuente documental. Formato retrato. **EVITAR:** marcas reales, alertas rojas, texto legible crítico, estética futurista.

## 3. Evidencias visuales

(`gallery/v5-backstage.png` — V5, backstage esa tarde)
**PROMPT:** Fotografía diegética de ambiente de backstage de un desfile de moda, con racks de ropa organizados por look, maquillistas y asistentes trabajando de forma genérica al fondo, sin rostros protagonistas reconocibles, con la tensión productiva propia de las horas previas a un desfile. Fotografía documental de moda, luz de estudio funcional combinada con luz cálida ambiental. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, marcas reales, dramatizar la ausencia de nadie, objetos verdes llamativos.

(`gallery/v11-comparacion-prendas.png` — V11, la misma prenda con y sin el color)
**PROMPT:** Fotografía diegética de catálogo o comparación directa: dos versiones de un mismo vestido cruzado una junto a otra, en maniquí, fotografiadas en las mismas condiciones de estudio con iluminación neutra uniforme. A la izquierda, el cinturón en verde esmeralda frío saturado (el «verde Aurora»); a la derecha, el mismo cinturón en beige neutro. Misma prenda, mismo punto exacto. Formato horizontal, comparación lado a lado. Dejar vacías las dos etiquetas. **EVITAR:** iconografía de alerta, diferencia de color sutil, flechas o círculos rojos.
**TEXTO A COMPONER:** etiqueta izquierda *MUESTRA — Look 07 — cinturón Verde Aurora*; etiqueta derecha *PRODUCCIÓN FINAL — Look 07 — cinturón Beige neutro*.

(`gallery/v1-bocetos-verde-aurora.png` — V1, bocetos originales)
**PROMPT:** Fotografía diegética de una hoja de bocetos de moda hecha a mano en un cuaderno de diseño: figurines de prendas con anotaciones de color al margen y, en un boceto específico, una muestra de color verde esmeralda frío saturado junto a una anotación manuscrita. Luz natural cálida de taller, aspecto de página de cuaderno funcional. Formato vertical. Dejar vacía la zona de la anotación. **EVITAR:** iconografía de alerta, estilo de una marca real, destacar la anotación con círculos o flechas.
**TEXTO A COMPONER:** anotación manuscrita junto a la muestra: *Verde Aurora*. Rótulo de la hoja: *Colección — desarrollo · 30/01*. Pequeña firma/inicial en la esquina inferior, con la misma letra: *C. R.*

(`gallery/v3a-hoja-produccion-inicial.png` — V3 parcial: primera página de la hoja de producción, versión inicial, entrega a T+10)
**PROMPT:** Documento diegético tipo hoja de producción textil («tech pack») de una casa de moda, página 1 de 2: una tabla simple de especificaciones por prenda con columnas de prenda, tela y color, en formato de documento de producción funcional y sobrio, aspecto de impreso o documento digital simple. La columna de historial de revisión queda vacía o cortada. Formato horizontal. Dejar vacías las celdas de texto. **EVITAR:** iconografía de alerta, dramatizar nada.
**TEXTO A COMPONER:** encabezado *Casa Solari — Hoja de producción — Colección actual — Versión inicial (Rev. 0) · 06/02*. Tabla *Look · Prenda · Tela · Detalle de color*: *03 · Abrigo largo · Lana fría · Forro Verde Aurora* — *07 · Vestido cruzado · Crepé · Cinturón Verde Aurora* — *11 · Chaqueta corta · Gabardina · Ribete Verde Aurora*. Columna *Historial de revisión:* vacía. Pie: *Pág. 1 de 2*.

(`gallery/v2a-fitting-fragmento.png` — V2 parcial: fragmento de la foto de fitting, entrega privada a T+20)
**PROMPT:** Recorte parcial de una fotografía de fitting: solo el detalle de la cintura de una prenda con un cinturón en verde esmeralda frío saturado y, en el borde de la imagen, la esquina de una tarjeta de fitting con una línea de texto. Sin rostro, sin fecha, sin contexto de la prenda completa. Luz funcional de taller. Formato 4:5. Dejar vacía la línea de la tarjeta. **EVITAR:** rostros, marcas reales, mostrar la prenda completa.
**TEXTO A COMPONER:** en la esquina de la tarjeta, cortada: *…ing — Verde Aurora*.

(`gallery/v4a-recorte-aurora-fragmento.png` — V4 parcial: fragmento de la página de revista, entrega privada a T+20)
**PROMPT:** Fragmento de una página de revista de moda independiente, escaneada: encabezado con el nombre de una marca, y una prenda de una colección pequeña con varias piezas repitiendo un mismo verde esmeralda frío saturado como rasgo distintivo. La parte inferior de la página, donde iría el pie de foto, está cortada. Formato vertical. **EVITAR:** marcas reales, rostros reconocibles, mostrar el pie de foto o el crédito.
**TEXTO A COMPONER:** encabezado: *AURORA*. Sobre la revista, en pequeño: *Trama — moda independiente*. Sin pie de foto (cortado).

(`gallery/v3-hoja-produccion-historial.png` — V3 completa, entrega a T+32)
**PROMPT:** La misma hoja de producción que V3a (mismo diseño, mismas filas), ahora completa, con la columna de historial de revisión llena: en cada fila se ve el color original sustituido por un tono neutro, con fecha y una nota breve. Formato horizontal, aspecto de «tech pack». Dejar vacías las celdas de texto. **EVITAR:** cambiar el diseño respecto a V3a, iconografía de alerta, resaltar o dramatizar el cambio.
**TEXTO A COMPONER:** encabezado *Casa Solari — Hoja de producción — Colección actual — Rev. 1 · 14/03 · Revisó: D. Suárez*. Tabla *Look · Prenda · Tela · Detalle de color · Historial de revisión*: *03 · Abrigo largo · Lana fría · Forro Beige neutro · Rev. 0 (06/02): Verde Aurora → Rev. 1 (14/03): Beige neutro. Nota: por indicación de Valeria — simplificar paleta* — *07 · Vestido cruzado · Crepé · Cinturón Beige neutro · Rev. 0 (06/02): Verde Aurora → Rev. 1 (14/03): Beige neutro. Nota: por indicación de Valeria — simplificar paleta* — *11 · Chaqueta corta · Gabardina · Ribete Beige neutro · Rev. 0 (06/02): Verde Aurora → Rev. 1 (14/03): Beige neutro. Nota: por indicación de Valeria — simplificar paleta*. Pie: *Pág. 2 de 2*.

(`gallery/v7-registro-proveedor-telas.png` — V7, registro de pedidos del proveedor)
**PROMPT:** Captura de pantalla diegética de un sistema simple de gestión de pedidos de un proveedor textil: tabla funcional de pedidos con fecha, operación, color y solicitante, interfaz sobria, sin ninguna fila resaltada. Formato horizontal. Dejar vacías las celdas de texto. **EVITAR:** iconografía de alarma, resaltados artificiales, marcas reales.
**TEXTO A COMPONER:** columnas *Fecha · Operación · Color · Solicitante*. Filas: *06/02 · Pedido inicial · Verde Aurora · D. Suárez* — *20/02 · Entrega de muestras · Verde Aurora · Casa Solari (taller)* — *14/03 · Modificación de pedido · Verde Aurora → Beige neutro · Daniela Suárez* — *17/03 · Entrega de producción final · Beige neutro · Casa Solari (taller)*.

(`gallery/v12-chat-daniela-valeria.png` — V12, chat entre Daniela y Valeria)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería profesional breve entre dos personas, interfaz simple y genérica de móvil, dos mensajes. Formato vertical. Dejar vacío el cuerpo de los mensajes. **EVITAR:** iconografía de alarma, mención explícita de autoría o de Camila, logos de apps reales.
**TEXTO A COMPONER:** encabezado del chat *Daniela Suárez*, fecha *13/03*. Mensaje recibido de *Valeria Montes* (18:42): *Daniela, simplifiquemos la paleta antes de producción final. Ya sabes por qué.* Respuesta de Daniela (18:47): *Entendido. Lo reviso con el proveedor.*

(`gallery/v2-fitting-verde-aurora.png` — V2 completa, entrega a T+32)
**PROMPT:** Fotografía diegética funcional de una sesión de fitting de moda: una modelo genérica, sin rasgos reconocibles, probándose un vestido cruzado de la colección con un cinturón en verde esmeralda frío saturado (el «verde Aurora»), en un taller con iluminación funcional neutra con un toque cálido. Se ve completa la prenda y, sobre una mesa o colgada, la tarjeta de fitting. Formato 4:5. Dejar vacía la tarjeta. Mismo cinturón que en V2a. **EVITAR:** rostros reconocibles, marcas reales, dramatización.
**TEXTO A COMPONER:** tarjeta de fitting: *Fitting 27/02 · Look 07 · Detalle: cinturón Verde Aurora*.

(`gallery/v4-recorte-aurora-completo.png` — V4 completa, entrega a T+32)
**PROMPT:** La misma página de revista que V4a (mismo diseño y misma prenda), ahora completa, con el pie de foto visible: página de una publicación de moda independiente, editorial sobria, con una prenda de una colección pequeña bajo el nombre de marca «Aurora» y varias piezas repitiendo el mismo verde esmeralda frío saturado. Modelo genérica sin rasgos reconocibles. Formato vertical. Dejar vacío el pie de foto. **EVITAR:** marcas reales, rostros reconocibles, cambiar el diseño respecto a V4a.
**TEXTO A COMPONER:** encabezado *AURORA*; sobre la revista *Trama — moda independiente*. Pie de foto: *Cápsula «Amanecer» — Diseño: Camila Rojas. Cada pieza repite el verde Aurora, el color firma de la marca.*

(`gallery/v10-creditos-coleccion.png` — V10, créditos del material oficial de prensa)
**PROMPT:** Documento diegético tipo página de créditos oficiales de una colección de moda, formato de comunicado de prensa o página de catálogo, editorial elegante y sobrio, sin fotos de personas. Formato vertical. Dejar vacío el cuerpo de créditos. **EVITAR:** iconografía de alerta, resaltar el crédito, mencionar colaboradores de diseño.
**TEXTO A COMPONER:** encabezado *Casa Solari — Colección de pasarela — Material de prensa*. Créditos: *Diseño: Valeria Montes · Patronaje: Hugo Peña · Producción: Daniela Suárez · Fotografía: Leticia Vargas*. Nada más sobre diseño: ninguna mención a Camila Rojas.

(`gallery/v8-oficina-backstage.png` — V8, la oficina pequeña del backstage)
**PROMPT:** Fotografía diegética funcional de una oficina pequeña y discreta de backstage, con un escritorio, dos sillas y, al fondo, una puerta interior cerrada, vista desde la puerta de entrada. Luz artificial de oficina, neutra y funcional, el mismo backstage ordinario que V5 en el exterior. Formato horizontal. **EVITAR:** elementos amenazantes, señales de forcejeo, llaves a la vista, dramatización, objetos verdes llamativos.

(`gallery/v9-objeto-sobre-mesa.png` — V9, el teléfono de Valeria)
**PROMPT:** Fotografía diegética funcional de un teléfono móvil con la pantalla bloqueada, dentro de una funda lisa de color neutro (gris o beige) que lleva grabadas las iniciales «V. M.» en la parte trasera, bien legibles (el teléfono se ve de lado o con la funda a la vista, apoyado sobre el escritorio de la misma oficina que V8), junto a una taza de café fría y algunos papeles de trabajo, sin ninguna persona presente. Luz de oficina neutra. Formato 4:5. **EVITAR:** indicios de violencia, resaltados artificiales, notificaciones legibles en pantalla, objetos verdes llamativos, el nombre completo de Valeria.
**TEXTO A COMPONER:** en la funda, grabado en pequeño: *V. M.* (es lo que permite que Paola reconozca el teléfono como de Valeria Montes). Nada más de texto.

## 4. Audio

Este caso **no tiene audios grabados**: no hay audio dentro del cronograma y la carpeta
`audio/` queda vacía.

(A1 — confesión de Marco) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Orus) para incluir las preguntas que esa mesa le hizo a Marco. Dirección de voz: hombre de mediana edad que empieza racional y se justifica, y se quiebra progresivamente hasta un reconocimiento genuino; debe sentirse como alguien al que la presión financiera y reputacional llevó a un control excesivo, no como un plan calculado. 30–40 segundos, sin música, silencio de backstage de fondo. **EVITAR:** música manipuladora, sobreactuación, contenido violento, cualquier detalle que la evidencia no hubiera permitido deducir antes.
