# Prompts de assets — El Libro de los Sustos

Total: **1 portada + 5 retratos + 8 evidencias visuales + 0 audios**. Este caso no tiene audio
en la línea de tiempo y, al ser de final clásico, tampoco hay confesión en audio: no crees la
carpeta `audio/`.

Georgina Valls es a la vez la víctima del manifiesto («Amenazada») y una persona del roster
(testigo): usan **el mismo archivo** `photos/georgina-valls.png`, así que son 5 retratos, no 6.

Todo se guarda en `public/immersion/el-libro-de-los-sustos/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 5 retratos |
| `gallery/` | las 8 evidencias visuales |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (5): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **V01** (portada del cuadernillo): fija el diseño del cuadernillo, que reaparece en V05.
5. **V02 y V03 juntas**: mismo formato, mismo texto hasta el último párrafo. Componer las dos
   en la misma plantilla (es la prueba central del caso).
6. Resto de evidencias en orden de entrega (ver tabla).
7. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Expansión | 7 | F1 (ficha de selección) |
| Pista privada | 7 | V02 (a un jugador) |
| Desarrollo | 14 | V03 |
| Giro | 21 | V04, C1 |
| Cierre | 28 | V05, F2 (más DG, DH y DV, que son texto sin imagen) |
| Copia pública | 28 | V02 (la misma imagen, para toda la mesa) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni párrafos**, y en este caso el
texto exacto ES la prueba: la mesa compara dos páginas palabra por palabra. Para V02, V03, F1,
F2, V04, C1 y la portada V01: genera la imagen con la zona de texto **vacía o con texto de
relleno ilegible**, y compón después el texto exacto indicado en "TEXTO A COMPONER" con un editor
(Canva, Figma, Photoshop). Verifica cada palabra contra este archivo y contra
`content/solucion.md` antes de guardar. Para V02 y V03, copia el texto **literal**, sin corregir
ni reordenar nada: ambos tienen 105 palabras y solo difieren desde «Dentro,».

**Cronología maestra (todas las horas del caso salen de aquí):** semanas antes: Nicolás entrega
y el club aprueba su cuento (F1, V02) · **víspera de la lectura:** Rosaura cierra el archivo de
impresión **17:42**, lo envía a impresión **17:55** (12 ejemplares), entrega de las copias
**19:20** (V04) · **día de la lectura:** mensaje de Rosaura **18:47** (C1), inicio de la lectura
**20:00**, cierre **22:05** (F2); el cuento «La puerta del fondo» es el **3.º** en leerse, con el
**ejemplar n.º 7** (V03, F2, V05). No añadas fechas de calendario a ningún documento: se usan
solo «víspera de la lectura» y «día de la lectura».

## Biblia visual de continuidad

- **Rosaura Mindel**, 50: ropa cálida de biblioteca (cárdigan, bufanda ligera), carpeta de coordinación editorial, gafas de lectura colgadas, cabello recogido con algo de canas.
- **Nicolás Abarca**, 29: ropa casual (sudadera o camisa de cuadros), postura algo tímida, hombros ligeramente encogidos, cabello algo despeinado.
- **Humberto Casal**, 52: ropa casual elegante de escritor veterano (americana de pana, jersey de cuello alto), barba corta cuidada.
- **Valentina Ross**, 33: ropa casual moderna, libreta de notas en la mano, expresión entusiasta.
- **Georgina Valls**, 45: ropa casual cálida (chal o chaqueta de punto), aire de autoridad tranquila.
- **El club:** biblioteca contemporánea cálida, iluminación de lectura ambarina, mesa de lectura de madera con cuadernillos impresos, velas decorativas y tazas de té; decoración de Halloween discreta (calabazas pequeñas, hojas secas). Sin estética de terror gráfico ni elementos sobrenaturales reales.
- **El cuadernillo** (V01 y V05): cuadernillo A5 grapado de portada color crema, tipografía serif otoñal, un pequeño grabado decorativo (una puerta entreabierta rodeada de hojas secas) centrado sobre el título. Mismo diseño en todas las piezas.
- **Las páginas del cuento** (V02 y V03): misma plantilla exacta, papel blanco roto, tipografía serif de manuscrito limpio, márgenes amplios, una nota discreta en el encabezado.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio literario: una mesa de lectura de madera cálida en una biblioteca contemporánea, de noche, con un cuadernillo impreso cerrado de portada crema en el centro, rodeado de tazas de té, velas decorativas y hojas secas; al fondo, estanterías de libros y una puerta entreabierta al final de un pasillo, con una luz ámbar. Sensación de velada acogedora con algo inquietante en la quietud, sin dramatismo. Formato vertical de póster de catálogo (2:3), estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror gráfico, símbolos ocultistas reales, fantasmas, rostros visibles, texto o logos.

## 2. Retratos

(`photos/georgina-valls.png` — Georgina Valls; es también la imagen de la víctima «Amenazada»)
**PROMPT:** Retrato diegético de una mujer de 45 años, ropa casual cálida (chaqueta de punto o chal), aire de autoridad tranquila, expresión serena y algo afectada pero contenida, fondo de biblioteca cálida con luz de lectura ambarina. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, llanto, miedo exagerado, texto legible.

(`photos/rosaura-mindel.png` — Rosaura Mindel)
**PROMPT:** Retrato diegético de una mujer de 50 años, ropa cálida de biblioteca (cárdigan y bufanda ligera), gafas de lectura colgadas del cuello, carpeta de coordinación editorial bajo el brazo, expresión organizada y cordial, con una leve tensión en la sonrisa, fondo de zona de preparación editorial de una biblioteca. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto frío, texto legible en la carpeta.

(`photos/nicolas-abarca.png` — Nicolás Abarca)
**PROMPT:** Retrato diegético de un hombre de 29 años, ropa casual (sudadera o camisa de cuadros), postura algo tímida, hombros ligeramente encogidos, mirada que esquiva la cámara, expresión nerviosa y amable, fondo de biblioteca cálida con luz de lectura. Formato retrato. **EVITAR:** apariencia de sospechoso, mirada culpable, dramatización, texto legible.

(`photos/humberto-casal.png` — Humberto Casal)
**PROMPT:** Retrato diegético de un hombre de 52 años, ropa casual elegante de escritor veterano (americana de pana, jersey de cuello alto), barba corta cuidada, expresión segura y algo condescendiente, fondo de estanterías de una biblioteca cálida. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, sonrisa maliciosa, texto legible.

(`photos/valentina-ross.png` — Valentina Ross)
**PROMPT:** Retrato diegético de una mujer de 33 años, ropa casual moderna, libreta de notas en la mano, expresión entusiasta y ambiciosa con un asomo de nerviosismo, fondo de mesa de lectura de biblioteca con luz ambarina. Formato retrato. **EVITAR:** apariencia de culpable, gesto de culpa obvio, dramatización, texto legible en la libreta.

## 3. Evidencias visuales

(`gallery/v01-portada-cuadernillo.png` — V01, portada del cuadernillo)
**PROMPT:** Fotografía diegética casual de ambiente de la portada de un cuadernillo impreso de relatos anónimos de un club de lectura de Halloween: cuadernillo A5 grapado de portada color crema, con diseño sobrio de tipografía serif otoñal y un pequeño grabado decorativo (una puerta entreabierta rodeada de hojas secas), apoyado sobre una mesa de madera cálida de biblioteca; luz cálida ambarina de lectura. Dejar vacía la zona del título y de las líneas de texto para composición posterior. Formato vertical. Pista visible pero no resaltada. **EVITAR:** imaginería de terror gráfico, símbolos ocultistas reales, estética de póster, captions, flechas, texto generado libremente, personas.
**TEXTO A COMPONER:** título **El libro de los sustos**; debajo, en letra más pequeña, *Relatos anónimos · Lectura anual del Club de Halloween*; al pie, **Tirada numerada del 1 al 12**.

(`gallery/f1-ficha-de-seleccion.png` — Doc-F1, ficha de selección; entrega en el minuto 7)
**PROMPT:** Documento diegético tipo ficha administrativa breve de un comité de lectura: una hoja impresa con campos rellenados en tabla sencilla y una firma manuscrita al pie, sobre fondo neutro de documento. Dejar vacíos los textos. Formato vertical. **EVITAR:** sellos de alarma, resaltados artificiales, iconografía de aviso, texto generado libremente.
**TEXTO A COMPONER:** título *Club de Relatos de Terror de Halloween — Ficha de selección*. Campos: *Título:* **La puerta del fondo** · *Género:* relato breve de terror · *Autoría:* **Nicolás Abarca** (anónima hasta después de la lectura) · *Revisión:* revisión editorial inicial, tres semanas antes de la lectura · *Decisión:* **Aprobado sin cambios** · *Observaciones:* Pasa a la lectura anual tal como se recibió. Al pie, firma manuscrita ilegible con el nombre impreso debajo: **R. Mindel — coordinación editorial**.

(`gallery/v02-pagina-version-aprobada.png` — V02, página del autor; entrega privada al minuto 7 y copia pública al 28)
**PROMPT:** Documento tipográfico diegético: una página de manuscrito aprobado de un cuento de terror breve, formato vertical de página A5, papel blanco roto, texto limpio en tipografía serif con márgenes amplios y una nota discreta de «versión aprobada» en el encabezado. Aspecto de documento impreso o digital simple, iluminación neutra. Dejar vacía la caja de texto del cuento para composición exacta. Mismo formato y plantilla que V03. **EVITAR:** terror gráfico, símbolos ocultistas reales, texto crítico generado libremente por el motor de imagen, ilustraciones, captions, flechas, resaltados.
**TEXTO A COMPONER:** encabezado discreto, en pequeño: *Archivo editorial · Versión aprobada*. Título: **La puerta del fondo**. Cuerpo, en dos párrafos (el salto de línea antes de «Dentro,» es solo maquetación y debe ser idéntico en V03):

> Mara subió las escaleras del viejo caserón sin encender ninguna luz. Cada peldaño crujía como si protestara por su peso. Al llegar al segundo piso, escuchó un susurro que no pudo ubicar, como si viniera de todas las paredes a la vez. Empujó la puerta del fondo del pasillo, la única que siempre permanecía cerrada.
>
> Dentro, el polvo cubría un escritorio antiguo y un espejo roto reflejaba fragmentos de su propio rostro, uno distinto en cada pedazo. Mara retrocedió, pero el susurro se hizo más claro: una voz que repetía su nombre, cada vez más cerca, como si llevara años esperándola sola en esa habitación.

(`gallery/v03-pagina-ejemplar-lectura.png` — V03, página de la lectura; entrega en el minuto 14)
**PROMPT:** Documento tipográfico diegético: la misma página de cuento, en el formato exacto de V02 (misma plantilla, mismos márgenes, misma tipografía), con una nota discreta de «ejemplar de lectura» en el encabezado en lugar de «versión aprobada». Aspecto de documento impreso simple, iluminación neutra. Dejar vacía la caja de texto para composición exacta. **EVITAR:** terror gráfico, símbolos ocultistas reales, texto crítico generado libremente, mensajes cifrados o acertijos de palabras, resaltados que marquen la diferencia con V02, captions, flechas.
**TEXTO A COMPONER:** encabezado discreto, en pequeño: *Ejemplar de lectura · n.º 7*. Título: **La puerta del fondo**. Cuerpo, en dos párrafos (idéntico a V02 hasta «cerrada.»; el salto de línea antes de «Dentro,» igual que en V02):

> Mara subió las escaleras del viejo caserón sin encender ninguna luz. Cada peldaño crujía como si protestara por su peso. Al llegar al segundo piso, escuchó un susurro que no pudo ubicar, como si viniera de todas las paredes a la vez. Empujó la puerta del fondo del pasillo, la única que siempre permanecía cerrada.
>
> Dentro, encontró una carta vieja firmada con un nombre que no era el de quien la había escrito. El susurro se hizo más claro: una voz que repetía que todos lo sabrían pronto, a menos que ella misma decidiera apartarse primero, antes de que la casa entera hablara por ella.

(`gallery/v04-orden-de-tirada.png` — V04, orden de tirada; entrega en el minuto 21)
**PROMPT:** Captura maquetada diegética de un documento interno de logística editorial: una hoja o pantalla de sistema interno simple, con una tabla de tres filas (responsable, acción, hora, ejemplares) y un pie en letra pequeña; aspecto de trámite administrativo rutinario de última hora, iluminación neutra de documento. Dejar vacías las celdas. Ninguna fila resaltada. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, señalar visualmente el nombre de Rosaura, captions, flechas, texto generado libremente.
**TEXTO A COMPONER:** título *Orden de tirada — Cuadernillo de lectura anual*. Encabezado: *Fecha: víspera de la lectura · Lectura programada: día siguiente, 20:00 · Formato: cuadernillo A5 grapado, ejemplares numerados 1 a 12*. Tabla *Responsable · Acción · Hora · Ejemplares*: **R. Mindel (coordinación editorial) · Cierre del archivo final · 17:42 · —** / **R. Mindel (coordinación editorial) · Envío a impresión · 17:55 · 12** / **Reprografía de la biblioteca · Impresión y entrega · 19:20 · 12**. Pie en letra pequeña: *Archivo final de impresión — acceso de edición: coordinación editorial.*

(`gallery/c1-captura-mensaje.png` — C1, captura de mensaje; entrega en el minuto 21)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería breve, formato vertical de captura de móvil, interfaz simple y genérica, fondo neutro, tono informal; sin personajes visibles ni avatares con rostro. Dejar vacíos los textos. **EVITAR:** iconografía de alarma, mención del cuento o de una alteración, logos de apps reales, rostros.
**TEXTO A COMPONER:** encabezado del chat: **Rosaura Mindel**. Etiqueta de fecha: *Día de la lectura*. Mensaje enviado (burbuja de la persona que recibe, 18:45): «¿Nerviosa por esta noche?». Respuesta de Rosaura (18:47): **«Un poco. Después de esta noche, dudo que insista con lo de la reelección.»**

(`gallery/v05-mesa-de-lectura.png` — V05, fotografía de mesa; entrega en el minuto 28)
**PROMPT:** Fotografía diegética de la mesa de lectura del club esa misma noche: varios cuadernillos A5 de portada crema apilados y esparcidos (mismo diseño que V01), velas decorativas de Halloween, tazas de té y un par de calabazas pequeñas, en un ambiente acogedor y cálido; luz cálida ambarina de velas y lámparas de lectura; figuras genéricas de fondo, sin rostro protagonista. Algunos cuadernillos llevan una pequeña etiqueta blanca en la esquina de la portada con un número. Formato horizontal. Pista visible pero no resaltada. **EVITAR:** imaginería de terror gráfico, símbolos ocultistas reales, estética de póster, rostros reconocibles, captions, flechas, resaltar un cuadernillo.
**TEXTO A COMPONER:** etiquetas numeradas en la esquina de las portadas visibles: **2**, **4**, **7**, **9**, **11** (el cuadernillo con el **7** queda en primer plano, abierto sobre la mesa, con el resto de cuadernillos alrededor). Ningún otro texto crítico.

(`gallery/f2-acta-de-lectura.png` — Doc-F2, acta de lectura; entrega en el minuto 28)
**PROMPT:** Documento diegético tipo acta breve de una velada de lectura: hoja impresa con una tabla sencilla de cinco filas y una firma manuscrita al pie, sobre fondo neutro de documento. Dejar vacíos los textos. Formato vertical. **EVITAR:** sellos de alarma, resaltados artificiales, iconografía de aviso, texto generado libremente.
**TEXTO A COMPONER:** título *Club de Relatos de Terror de Halloween — Acta de lectura anual*. Encabezado: *Inicio: 20:00 · Cierre: 22:05 · Lugar: sala de lectura de la biblioteca*. Tabla *Orden · Cuento · Ejemplar n.º*: **1 · El invierno de las campanas · 2** / **2 · La casa de la señora Ávila · 4** / **3 · La puerta del fondo · 7** / **4 · Las luces de la cuesta · 9** / **5 · El último turno · 11**. Nota al pie: *La autoría se reveló al terminar la lectura.* Firma manuscrita ilegible con el nombre impreso debajo: **Comité de lectura**.

## 4. Audio

Este caso **no tiene audio**: ni grabaciones en la línea de tiempo ni confesión (final clásico,
sin `confession_script`). No crees la carpeta `audio/`.

## 5. Piezas sin imagen (para que el dueño las conozca)

DG (declaración ampliada de Georgina), DH (aviso sobre Humberto) y DV (aviso sobre Valentina)
son texto dentro de `content/cierre.md`: no necesitan imagen. Solo existen como códigos de
evidencia que abren las etapas de interrogatorio correspondientes.
