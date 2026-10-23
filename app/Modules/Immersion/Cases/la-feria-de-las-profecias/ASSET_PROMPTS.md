# Prompts de assets — La Feria de las Profecías

Total: **1 portada + 7 retratos + 6 evidencias visuales + 1 audio** (la nota de voz de Dalia; este caso
tiene final clásico, así que no hay audio de confesión).

Dalia Rengifo es a la vez la afectada (`victim`) y una testigo del roster: **las dos entradas usan el
mismo archivo, `dalia-rengifo.png`**. Es un solo retrato, no dos.

Todo se guarda en `public/immersion/la-feria-de-las-profecias/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 7 retratos |
| `gallery/` | las 6 evidencias visuales |
| `audio/` | `a1-nota-de-voz-dalia.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (7): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera V02 y V03 con el mismo diseño de formulario y
   los mismos nombres; V03 y V06 comparten el nombre del proveedor.
5. **Audio** A1.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Expansión | 8 | (solo texto) |
| Pista privada (a un jugador) | 8 | V02 |
| Desarrollo | 16 | V03 |
| Complicación | 24 | V04 + copia pública de V02 (mismo archivo) + audio A1 |
| Giro | 32 | V05, DT (declaración escrita de Tobías, sin imagen) |
| Cierre | 40 | V06, HL (hallazgos laterales, solo texto) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien nombres, cifras ni tablas**, y aquí el dato exacto ES
la prueba (cuotas mensuales en la ficha, pago atrasado en la hoja del proveedor, el almacén norte
compartido en el mapa, el lote de sobres en el registro). Para V02, V03, V05 y V06: genera la imagen
con la zona de texto **vacía o con texto de relleno ilegible**, y compón después el texto exacto indicado
en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). V01 y V04 no llevan texto legible.
Verifica cada dato contra `case.php` antes de guardar.

**Datos maestros (todo sale de aquí; el caso no usa fechas ni horas de reloj):**
Proveedor: **Montajes Cedro** (mobiliario). Puestos 1–6 fila norte, 7–12 fila sur. **Madame Yolanda =
puesto 4**, Ezequiel = puesto 8, **Dalia = puesto 9**. **Almacén norte** (puestos 1–6 + Montajes Cedro) y
Almacén sur (puestos 7–12 + Textiles Calamar). Dalia: 4 cuotas mensuales, **cuota 2 de 4 atrasada**.
Lote de ambientación (sobres y papel artesanal): Montajes Cedro, destino Almacén norte.

## Biblia visual de continuidad (pegar en los retratos)

- **Dalia Rengifo**, 34: ropa de tarotista otoñal en tonos tierra y burdeos, sin excesos de utilería fantástica, cabello castaño oscuro suelto, expresión inquieta pero contenida.
- **Rogelio Fonseca**, 45: ropa de trabajo práctica, chaleco de montaje con bolsillos, cinta métrica en la cintura, manos de quien carga mesas; complexión fuerte, bigote corto.
- **Hortensia Casals**, 50: atuendo teatral de "Madame Yolanda": velos, anillos y collares; cabello recogido bajo un pañuelo oscuro; sin estética sobrenatural real.
- **Perla Andrade**, 42: ropa de coordinación de evento, blazer oscuro, radio portátil al cinto, carpeta bajo el brazo.
- **Ezequiel Darío**, 38: atuendo astrológico vistoso pero terrenal: chaqueta de terciopelo con motivos de estrellas, barba cuidada.
- **Tobías Reinoso**, 28: chaleco de acreditaciones, credencial plastificada al cuello, gesto atento y algo nervioso.
- **Walter Núñez**, 50: chaleco de acreditaciones con credencial de supervisor, cabello canoso, gesto sereno y formal.
- **La Feria del Velo**: puestos otoñales con telas oscuras y luz ámbar, velas, cartas de tarot genéricas; mismas credenciales, mismos formularios y misma paleta en todas las piezas; utilería artesanal, sin imaginería fantástica ni elementos sobrenaturales reales.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio, en formato vertical de póster de catálogo: una mesa de lectura de tarot en un mercado esotérico de temporada, de noche, con telas oscuras y velas encendidas de luz ámbar; en el centro, un sobre de papel envejecido artesanalmente con un sello de cera, cerrado, junto a unas cartas boca abajo; al fondo, puestos de la feria desenfocados con luces cálidas. Sensación de misterio cotidiano, irónico y sin terror: el sobre parece importante sin que nada sea sobrenatural. Estilo realista premium. **EVITAR:** texto o logos, rostros visibles, calaveras, apariciones, humo mágico, iconografía de terror, símbolos religiosos o culturales específicos.

## 2. Retratos

(`photos/dalia-rengifo.png` — Dalia Rengifo: **afectada y testigo, un solo archivo**)
**PROMPT:** Retrato diegético de una mujer de 34 años, tarotista de una feria otoñal, cabello castaño oscuro suelto, ropa en tonos tierra y burdeos con un chal sencillo, expresión inquieta pero contenida, mirada decidida, fondo de su puesto de lectura con telas oscuras y una vela, luz ámbar. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, lágrimas, sobrenatural real, logos.

(`photos/rogelio-fonseca.png` — Rogelio Fonseca)
**PROMPT:** Retrato diegético de un hombre de 45 años, complexión fuerte, bigote corto, chaleco de montaje con bolsillos sobre ropa de trabajo, cinta métrica en la cintura, expresión práctica, algo seca y reservada, fondo de la zona de proveedores y almacenamiento de una feria, con mesas plegables apiladas. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, texto legible.

(`photos/hortensia-casals.png` — Hortensia Casals, "Madame Yolanda")
**PROMPT:** Retrato diegético de una mujer de 50 años con atuendo teatral de lectora de tarot: velos, anillos y collares, cabello recogido bajo un pañuelo oscuro, expresión cálida y expresiva con un asomo de aprensión, fondo de su puesto de lectura con telas oscuras y velas, luz ámbar. Formato retrato. **EVITAR:** estética sobrenatural real, aspecto de bruja de caricatura, apariencia de culpable, símbolos religiosos o culturales específicos.

(`photos/perla-andrade.png` — Perla Andrade)
**PROMPT:** Retrato diegético de una mujer de 42 años, ropa de coordinación de evento, blazer oscuro, radio portátil al cinto, carpeta bajo el brazo, expresión organizada, cordial y algo a la defensiva, fondo de la oficina de coordinación de una feria con un plano pegado en la pared. Formato retrato. **EVITAR:** apariencia de culpable, gesto de soborno, dinero visible, texto legible.

(`photos/ezequiel-dario.png` — Ezequiel Darío)
**PROMPT:** Retrato diegético de un hombre de 38 años, barba cuidada, chaqueta de terciopelo con motivos de estrellas, atuendo astrológico vistoso pero terrenal, expresión carismática y algo pretenciosa, fondo de su puesto de astrología con un mapa celeste de cartón y velas. Formato retrato. **EVITAR:** apariencia de villano, teléfono o pantalla visible, sobrenatural real, texto legible.

(`photos/tobias-reinoso.png` — Tobías Reinoso)
**PROMPT:** Retrato diegético de un hombre de 28 años, chaleco de acreditaciones, credencial plastificada al cuello, expresión atenta y algo nerviosa, fondo del mostrador de la caseta de acreditaciones de una feria, con credenciales colgando de cordones. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, texto legible en la credencial.

(`photos/walter-nunez.png` — Walter Núñez)
**PROMPT:** Retrato diegético de un hombre de 50 años, cabello canoso, chaleco de acreditaciones con credencial de supervisor, expresión serena y formal, fondo de la zona de acreditación de una feria con un tablero de avisos. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, texto legible en la credencial.

## 3. Evidencias visuales

(`gallery/v01-zona-acreditacion.png` — V01, foto de acreditación)
**PROMPT:** Fotografía diegética informal de la zona de acreditación de una feria esotérica de temporada, al atardecer, con un pequeño mostrador donde el personal entrega credenciales plastificadas a vendedores que llegan; credenciales colgando de cordones sobre algunos vendedores genéricos de fondo, telas oscuras, luz ámbar natural de atardecer. Debe parecer simplemente una feria normal en montaje. Figuras genéricas sin rostros protagonistas. Formato horizontal. **EVITAR:** imaginería sobrenatural real, marcas o logotipos reales, iconografía de alarma, estética de póster, captions, flechas, texto legible.
**TEXTO A COMPONER:** ninguno crítico (las credenciales no deben llevar texto legible).

(`gallery/v02-ficha-inscripcion-dalia.png` — V02, ficha de inscripción; privada a T+8 y copia pública a T+24)
**PROMPT:** Documento diegético de una ficha de inscripción de vendedor para una feria esotérica, de una página, con campos administrativos simples (nombre, tipo de lectura, preferencia de ubicación y una casilla de modalidad de pago de la cuota del puesto), aspecto de documento escaneado o fotografiado de cerca con luz neutra de oficina. Dejar vacíos los campos de texto. La casilla de pago debe verse como un trámite rutinario, sin resaltar. Formato vertical. **EVITAR:** iconografía de alarma, resaltados artificiales, texto crítico generado libremente, captions, flechas.
**TEXTO A COMPONER:** título *Feria del Velo — Ficha de inscripción de vendedor*. *Solicitud n.º 27.* *Nombre:* **Dalia Rengifo.** *Tipo de lectura:* **Tarot.** *Preferencia de ubicación:* **Zona central.** *Modalidad de pago de la cuota del puesto:* ☐ Pago único ☒ **Cuotas mensuales (4 cuotas).** *Inscripción:* **cuatro semanas antes de la feria.** *Puesto asignado:* **9.** Firma manuscrita genérica. La casilla marcada se compone como el resto de los campos: sin resaltado.

(`gallery/v03-control-proveedor.png` — V03, control del proveedor)
**PROMPT:** Captura de pantalla diegética de una hoja de cálculo simple y sin estética corporativa: un control interno de asignaciones de servicio de una empresa de mobiliario para feria, con columnas de vendedor, puesto, mobiliario asignado y estado de pago de cuota. Dejar vacías las celdas de texto. Ninguna fila resaltada. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, logotipos de empresas reales, texto crítico generado libremente, captions.
**TEXTO A COMPONER:** título *Montajes Cedro — Control de asignaciones de servicio · Feria del Velo · Corte: semana previa a la feria.* Columnas *Vendedor · Puesto · Mobiliario · Estado de pago de cuota*. Filas: **L. Prada · 1 · Mesa + 2 sillas · Al día** / **M. Ortiz · 2 · Mesa + 2 sillas · Al día** / **H. Casals · 4 · Mesa + 3 sillas · Al día** / **S. Quiroga · 5 · Mesa + 2 sillas · Al día** / **J. Tapias · 6 · Mesa + 2 sillas · Al día** / **E. Darío · 8 · Mesa + 3 sillas · Al día** / **D. Rengifo · 9 · Mesa + 2 sillas · Pago atrasado (cuota 2 de 4)** / **N. Bravo · 10 · Mesa + 2 sillas · Al día**. Mismo estilo en todas las filas; la de D. Rengifo no se resalta.

(`gallery/v04-sobre-mesa-lectura.png` — V04, sobre de la predicción)
**PROMPT:** Fotografía diegética de cerca, con cámara de móvil, de un sobre de papel envejecido artesanalmente con un sello de cera y símbolos esotéricos genéricos (ni religiosos ni culturales específicos), apoyado sobre una mesa de lectura de tarot con telas oscuras y una vela, en una feria de temporada. Se ve una esquina de la tarjeta interior asomando, sin texto legible. Ligera imperfección de encuadre, exposición cálida de vela. Ninguna persona visible. Formato vertical. **EVITAR:** imaginería sobrenatural real, símbolos religiosos o culturales, texto legible, iconografía de alarma, captions, flechas, estética de póster.
**TEXTO A COMPONER:** ninguno. El texto de la tarjeta NO va en la imagen; se transcribe en `complicacion.md`.

(`gallery/v05-mapa-puestos.png` — V05, mapa de puestos)
**PROMPT:** Plano vectorial limpio y funcional de orientación de una feria esotérica de temporada, con la disposición de doce puestos de lectura en dos filas (norte y sur), una caseta de acreditaciones junto a una oficina de coordinación cerca de la entrada, y una zona de proveedores y almacenamiento al fondo dividida en dos almacenes (norte y sur). Dejar vacías las etiquetas. Ninguna zona resaltada. Formato horizontal. **EVITAR:** iconografía de alarma, marcas reales de ferias existentes, resaltados de la zona compartida, flechas, captions.
**TEXTO A COMPONER:** título *Feria del Velo — Mapa de puestos.* Entrada. *Caseta de acreditaciones* y *Oficina de coordinación* (contiguas). Fila norte: *Puestos 1 a 6* (el **4** rotulado *Madame Yolanda*). Fila sur: *Puestos 7 a 12* (el **8** rotulado *Ezequiel Darío*, el **9** rotulado *Dalia Rengifo*). Zona de proveedores y almacenamiento: **Almacén norte — puestos 1 a 6 · Montajes Cedro (mobiliario)** y **Almacén sur — puestos 7 a 12 · Textiles Calamar**. Mismas etiquetas de zona que en la hoja de materiales (V06).

(`gallery/v06-registro-materiales.png` — V06, registro de materiales)
**PROMPT:** Documento diegético de un registro de control de suministros de una feria esotérica, con columnas de material, cantidad, proveedor responsable y destino, y varias entradas rutinarias de telas, velas, mobiliario y papelería; formato de documento impreso o digital simple, luz neutra. Dejar vacías las celdas de texto. Ninguna fila resaltada. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, texto crítico generado libremente, captions, flechas.
**TEXTO A COMPONER:** título *Feria del Velo — Registro de control de suministros.* Columnas *Material · Cantidad · Proveedor responsable · Destino*. Filas: **Telas negras (rollos) · 24 · Textiles Calamar · Puestos de lectura** / **Velas (cajas) · 30 · Velas del Pinar · Puestos de lectura** / **Barajas de tarot genéricas · 20 · Papelería Arcana · Puestos de lectura** / **Mesas y sillas · 36 · Montajes Cedro · Puestos de lectura** / **Sobres y papel artesanal (lote de ambientación) · 40 sobres + 100 hojas · Montajes Cedro · Almacén norte** / **Faroles decorativos · 18 · Textiles Calamar · Almacén sur**. La fila de sobres se compone igual que las demás, sin resaltar.

## 4. Audio

(`audio/a1-nota-de-voz-dalia.mp3` — A1, nota de voz de Dalia; evento del minuto 24)
- **Scene:** nota de voz grabada con el teléfono en un rincón apartado de una feria nocturna: murmullo distante de gente y música ambiental tenue de fondo; sin música dramática.
- **Context:** Dalia, mujer adulta de 34 años, habla en voz baja y contenida a una amiga: desconcertada por una predicción inexplicablemente precisa sobre un tema privado y decidida a averiguar qué pasa. Inquieta, no asustada hasta el pánico.
- **Speaker:** Speaker 1 — Dalia Rengifo.
- **Voice (Gemini):** Kore.
- **Duración:** 10–15 segundos.
- **Guion (exacto):** «No sé cómo lo supo. Nunca le dije a nadie aquí que estoy pagando el puesto en cuotas. La predicción lo dejó clarísimo, como una advertencia. Voy a averiguar qué está pasando.»
- **EVITAR:** tono de terror o pánico, música dramática, cualquier nombre adicional mencionado directamente.

No hay audio de confesión: el caso termina con final clásico.
