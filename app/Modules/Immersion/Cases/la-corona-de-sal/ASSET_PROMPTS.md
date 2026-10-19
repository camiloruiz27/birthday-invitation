# Prompts de assets — La Corona de Sal

Total: **1 portada + 9 retratos + 8 evidencias visuales + 1 audio** (la ficha de especies y la
captura de mensajes C1 cuentan como evidencias visuales; DT y CR, las declaraciones escritas y
los asuntos aparte del cierre, no llevan imagen). Este caso no tiene confesión en audio: el
final es de epílogos personalizados.

Todo se guarda en `public/immersion/la-corona-de-sal/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos (8 personas + la afectada) |
| `gallery/` | las 8 evidencias visuales |
| `audio/` | `a1-nota-voz-rosaura.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera primero V01 y la ficha de especies y
   **fija la apariencia de las dos plantas**: tienen que verse igual en V01, en la ficha y en V05.
5. **Audio A1** (una sola nota de voz, 10-15 s).
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 (coronas terminadas) |
| Expansión | 9 | V02 (mapa del humedal) |
| Desarrollo | 18 | FE (ficha de especies) |
| Pista privada | 18 | V03 (permiso presentado, a un jugador) |
| Complicación | 27 | V04 (registro de expedición) + copia pública de V03 |
| Giro | 36 | V05 (foto del taller) + A1 (nota de voz de Rosaura, correo aparte) |
| Cierre | 45 | V06 (pedido comercial), C1 (mensajes); además DT y CR (solo texto) |
| Acusación | 55 | (formulario) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien nombres, fechas ni cifras**, y en este caso el dato
exacto ES la prueba (Verbena de Sal ≠ Verbena de Sal y Junco Costero; Zona 1 ≠ Zonas 1 y 2).
Para V02, la ficha de especies, V03, V04, V05, V06 y C1: genera la imagen con la zona de texto
**vacía o con texto de relleno ilegible**, y compón después el texto exacto indicado en "TEXTO A
COMPONER" con un editor (Canva, Figma, Photoshop). Verifica cada nombre y cada fecha contra
`case.php` antes de guardar.

**Textos exactos de las dos piezas centrales (cópialos literales, sin cambiar una palabra):**

- **N06-V03 (permiso presentado):** «Se autoriza a Rosaura Benavides la recolección de Verbena de Sal y Junco Costero, en las Zonas 1 y 2 del humedal, en cantidades limitadas para uso artesanal, válido por la temporada.»
- **N06-V04 (registro de expedición):** «Solicitante: Rosaura Benavides. Especie autorizada: Verbena de Sal. Zona: 1. Vigencia: temporada navideña en curso.»

**Cronología maestra (todas las fechas del caso salen de aquí; son fechas de trama, sin año):**
permiso expedido **4 nov** (V03 y V04) · pedido de Hoteles Marejada emitido **2 dic** (V06 y C1) ·
material recibido en el taller **4 dic** (V05) · entrega urgente del pedido **12 dic** (V06).
No hay horas en ninguna pieza.

**Nombres y cifras fijos:** Reserva del Humedal · permiso **N.º 0412** (V03 y V04, mismo número) ·
Hoteles Marejada · pedido **N.º 2214** · **120** coronas · Verbena de Sal · Junco Costero ·
Zona 1 · Zona 2.

## Biblia visual de continuidad (pegar en los retratos)

- **Rosaura Benavides**, 55: ropa de trabajo artesanal cálida, delantal de taller, cabello canoso recogido, manos de artesana.
- **Mauricio Benavides**, 50: ropa casual de gestión comercial (camisa de lino arremangada, libreta o teléfono en la mano).
- **Esteban Lacroix**, 45: traje formal de representante comercial, sin corbata llamativa.
- **Herminia Casallas**, 48: ropa de trabajo artesanal, manos con fibras, pañuelo en el cuello.
- **Tobías Reyes**, 24: ropa casual de aprendiz, camiseta y delantal corto, cabello algo desordenado.
- **Leonor Izaguirre**, 52: ropa formal de oficina ambiental, carpeta de trámites bajo el brazo.
- **Araceli Montoya**, 40: ropa casual de visitante de feria, bolso de tela, sombrero ligero.
- **Petronila Vargas**, 58: ropa de trabajo artesanal, delantal, cabello gris, gesto cálido.
- **Amancio Ferrúa**, 55: uniforme de guardaparques (camisa y chaleco de campo), credencial de la reserva sin texto legible.
- **Las dos plantas (idénticas en V01, ficha de especies y V05):** **Verbena de Sal** = tallos cortos, flores pequeñas de tono grisáceo. **Junco Costero** = tallos largos y fibrosos, verde oscuro. Nada más: ninguna otra especie vegetal reconocible.
- **El pueblo costero:** costa cálida, taller artesanal con tonos naturales (madera, fibras, sal, verde apagado), feria navideña ambientada sin nieve forzada, papel y documentos de aspecto sencillo, señales y textos completamente ficticios y legibles.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas ni instituciones reales, sin escudos reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada vertical de póster de catálogo, sin texto, para un caso de misterio navideño costero: una corona navideña artesanal de fibras secas verde oscuro y grises, con una pequeña perla de sal cristalizada en un costado, colgada sobre la puerta de madera de un taller de artesanía junto a un humedal costero al atardecer cálido; al fondo, siluetas de juncos y una feria con luces tenues. Sensación de calidez con una grieta de inquietud: la corona está perfecta, pero la puerta está entreabierta y no hay nadie. Estilo realista premium, cinematográfico y sobrio, luz dorada baja. **EVITAR:** nieve forzada, personas visibles, sangre, daño a fauna, iconografía de alarma, estética de terror, texto o logos.

## 2. Retratos

(`photos/rosaura-benavides.png` — la afectada)
**PROMPT:** Retrato diegético tipo foto de carné de socia de gremio artesanal de una mujer de 55 años, cabello canoso recogido, ropa de trabajo artesanal cálida y delantal de taller, expresión serena, firme y algo cansada, fondo neutro del interior de un taller con fibras colgadas. Formato retrato. **EVITAR:** apariencia de víctima, vendajes, heridas, dramatización, texto legible.

(`photos/mauricio-benavides.png` — Mauricio Benavides)
**PROMPT:** Retrato diegético de un hombre de 50 años, ropa casual de gestión comercial (camisa de lino arremangada), libreta o teléfono en la mano, expresión cordial y segura con un asomo de cansancio, fondo de oficina comercial sencilla de un taller. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto agresivo.

(`photos/esteban-lacroix.png` — Esteban Lacroix)
**PROMPT:** Retrato diegético de un hombre de 45 años, traje formal de representante comercial de una cadena hotelera, expresión profesional, segura y algo defensiva, fondo de vestíbulo de hotel de costa con luz natural. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, logos de hoteles reales, texto legible.

(`photos/herminia-casallas.png` — Herminia Casallas)
**PROMPT:** Retrato diegético de una mujer de 48 años, ropa de trabajo artesanal, pañuelo en el cuello, fibras entre las manos, expresión cordial y algo competitiva, fondo de su puesto de la feria navideña con coronas en exhibición. Formato retrato. **EVITAR:** apariencia de sospechosa, gesto de culpa obvio, texto legible.

(`photos/tobias-reyes.png` — Tobías Reyes)
**PROMPT:** Retrato diegético de un hombre de 24 años, ropa casual de aprendiz con delantal corto, cabello algo desordenado, expresión joven, aplicada y algo nerviosa, fondo de la mesa de trabajo de un taller artesanal. Formato retrato. **EVITAR:** apariencia de culpable, dramatización, dinero visible.

(`photos/leonor-izaguirre.png` — Leonor Izaguirre)
**PROMPT:** Retrato diegético de una mujer de 52 años, ropa formal de oficina ambiental, carpeta de trámites bajo el brazo, expresión administrativa, cordial y algo defensiva, fondo de ventanilla de una oficina pública sencilla. Formato retrato. **EVITAR:** villana obvia, regalos o dinero visibles, sellos de instituciones reales, texto legible.

(`photos/araceli-montoya.png` — Araceli Montoya)
**PROMPT:** Retrato diegético de una mujer de 40 años, ropa casual de visitante de feria, bolso de tela y sombrero ligero, expresión amable, curiosa y observadora, fondo de puestos de una feria navideña costera. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/petronila-vargas.png` — Petronila Vargas)
**PROMPT:** Retrato diegético de una mujer de 58 años, ropa de trabajo artesanal y delantal, cabello gris, expresión cálida, leal y algo preocupada, fondo de un taller artesanal con coronas a medio hacer. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/amancio-ferrua.png` — Amancio Ferrúa)
**PROMPT:** Retrato diegético de un hombre de 55 años, uniforme de guardaparques (camisa y chaleco de campo), credencial de la reserva colgada al cuello sin texto legible, expresión sobria, exacta y cortés, fondo de la caseta de acceso de un humedal costero. Formato retrato. **EVITAR:** apariencia de sospechoso, escudos o sellos de instituciones reales, texto legible.

## 3. Evidencias visuales

(`gallery/v01-coronas-terminadas.png` — V01, coronas terminadas)
**PROMPT:** Fotografía diegética documental comercial de una colección de coronas navideñas artesanales terminadas, hechas con fibras vegetales secas y detalles decorativos de temporada, exhibidas sobre una mesa de taller con luz natural cálida de costa. Entre las coronas deben distinguirse con naturalidad las dos plantas: **Verbena de Sal** (tallos cortos, flores pequeñas de tono grisáceo) y **Junco Costero** (tallos largos y fibrosos, verde oscuro); algunas coronas llevan una sola especie y otras las dos, sin destacar ninguna. La colección debe verse como un producto comercial legítimo y atractivo. Formato horizontal. Mantener la pista visible pero no resaltada. **EVITAR:** nieve forzada, iconografía de alarma, daño a fauna, estética de póster, captions, flechas, personas, carteles o precios legibles.

(`gallery/v02-mapa-humedal.png` — V02, mapa del humedal)
**PROMPT:** Diagrama vectorial diegético, mapa simple tipo turístico de un humedal costero con dos zonas diferenciadas por color y trama (Zona 1 y Zona 2), con senderos, un puesto de acceso y una leyenda básica, estilo de mapa impreso o digital de orientación, neutro. Dejar vacías las etiquetas de zona y la leyenda. Formato horizontal. **EVITAR:** iconografía de alarma, nombres de reservas reales, flechas, resaltado de una zona sobre la otra, aspecto de póster.
**TEXTO A COMPONER:** título *Humedal — Mapa de orientación*. Etiqueta de la zona 1: **«Zona 1 — Uso artesanal con permiso»**. Etiqueta de la zona 2: **«Zona 2 — Protección: recolección solo con autorización especial por zona»**. Leyenda: *Sendero · Puesto de acceso · Límite de zona*. Ningún nombre de persona ni de permiso.

(`gallery/ficha-especies.png` — FE, ficha de especies)
**PROMPT:** Ficha documental sencilla de una página, estilo hoja informativa de reserva natural, con dos ilustraciones botánicas simples lado a lado para comparación directa: a la izquierda una planta de tallos cortos con flores pequeñas de tono grisáceo (Verbena de Sal), a la derecha una planta de tallos largos y fibrosos de verde oscuro (Junco Costero). Misma apariencia de las dos plantas que en V01. Dejar vacíos los textos. Formato vertical. **EVITAR:** terminología botánica compleja, iconografía de alarma, tachones o marcas rojas, la palabra «autorizada» o «prohibida», más de dos plantas.
**TEXTO A COMPONER:** título *Ficha de especies — Reserva del Humedal*. Izquierda: **«Verbena de Sal: tallos cortos, flores pequeñas de tono grisáceo. Crece en la Zona 1.»** Derecha: **«Junco Costero: tallos largos y fibrosos, tono verde oscuro. Crece en la Zona 2.»** (Nota para quien compone, NO va en la imagen: no añadir nada sobre el permiso de Rosaura ni sobre qué especie está autorizada.)

(`gallery/v03-permiso-presentado.png` — V03, permiso presentado)
**PROMPT:** Documento visual diegético de un permiso de recolección con formato oficial sencillo (encabezado, número de permiso, cuerpo de un párrafo, firma ilegible al pie), aspecto de documento impreso simple en papel claro, luz neutra, sin sellos ni escudos reales. Mismo diseño (encabezado, tipografía, colores) que V04. Dejar vacía la zona de texto. Formato vertical. Mantener la pista visible pero no resaltada. **EVITAR:** iconografía de alarma, normas ambientales reales, sellos o instituciones reales, marcas de «copia» o «falso», resaltados, aspecto de póster.
**TEXTO A COMPONER:** encabezado *Reserva del Humedal — Permiso de recolección N.º 0412*. Línea de fecha: *Expedido: 4 nov*. Cuerpo, **texto exacto, literal**: **«Se autoriza a Rosaura Benavides la recolección de Verbena de Sal y Junco Costero, en las Zonas 1 y 2 del humedal, en cantidades limitadas para uso artesanal, válido por la temporada.»** Firma ilegible al pie, sin nombre.

(`gallery/v04-registro-expedicion.png` — V04, registro de expedición)
**PROMPT:** Captura maquetada diegética de una pantalla de sistema interno o documento impreso simple: el registro oficial de expedición de permisos de una reserva natural, con una tabla funcional de columnas (solicitante, especie autorizada, zona, fecha, vigencia) y una fila principal más algunas filas de relleno ilegibles. Mismo diseño (encabezado, tipografía, colores) que V03. Ninguna fila resaltada. Formato horizontal. Mantener la pista visible pero no resaltada. **EVITAR:** iconografía de alarma, resaltados artificiales, nombres legibles en las filas de relleno, aspecto de póster.
**TEXTO A COMPONER:** encabezado *Reserva del Humedal — Registro de expedición de permisos*. Columnas: *Solicitante · Especie autorizada · Zona · Expedición · Vigencia*. Fila principal, **texto exacto, literal**: **«Solicitante: Rosaura Benavides. Especie autorizada: Verbena de Sal. Zona: 1. Vigencia: temporada navideña en curso.»** (maquetada en las columnas) con *Permiso N.º 0412 · Expedición: 4 nov*. Filas de relleno: texto gris ilegible, sin nombres reales ni legibles. Nada sobre Junco Costero ni sobre la Zona 2 en ninguna parte.

(`gallery/v05-foto-taller.png` — V05, foto del taller)
**PROMPT:** Fotografía diegética documental de taller, luz natural cálida de interior de trabajo: el interior de un taller artesanal con fardos de material vegetal recién llegado sobre una mesa de trabajo, entre los que se distinguen fibras de **Junco Costero** (tallos largos, verde oscuro) junto a la **Verbena de Sal** habitual (tallos cortos, flores grisáceas), con una etiqueta de papel de fecha de recepción visible en un costado de un fardo. Mismas plantas que V01. Dejar en blanco la etiqueta. Formato horizontal. Mantener la pista visible pero no resaltada. **EVITAR:** personas, iconografía de alarma, daño a fauna, estética de póster, captions, flechas.
**TEXTO A COMPONER:** en la etiqueta del fardo: **«Recepción: 4 dic»**. Ninguna otra palabra legible.

(`gallery/v06-pedido-comercial.png` — V06, pedido comercial)
**PROMPT:** Documento visual diegético de un pedido comercial de temporada, formato de documento comercial simple y sobrio en papel claro (encabezado con el nombre de una cadena hotelera ficticia, tabla breve de cantidad y fecha), luz neutra. Dejar vacío el texto. Formato vertical. Mantener la pista visible pero no resaltada. **EVITAR:** iconografía de alarma, nombres de cadenas hoteleras reales, logos, sellos de «urgente» en rojo, resaltados, aspecto de póster.
**TEXTO A COMPONER:** encabezado *Hoteles Marejada — Pedido N.º 2214*. *Destinatario: Taller Benavides — Att. Mauricio Benavides.* *Emitido: 2 dic.* *Concepto: coronas navideñas artesanales de temporada.* *Cantidad: 120.* *Fecha de entrega: 12 dic. Sin prórroga.* *Contacto: Esteban Lacroix.*

(`gallery/c1-mensajes-pedido.png` — C1, mensajes sobre el pedido)
**PROMPT:** Captura de pantalla diegética de un breve intercambio de mensajes en una interfaz de mensajería simple y genérica, formato de captura de móvil, tono comercial y directo, sin avatares reconocibles. Dejar vacíos los textos. Formato vertical. **EVITAR:** iconografía de alarma, menciones al permiso o a la recolección, logos de apps reales, fotos de perfil con rostro.
**TEXTO A COMPONER:** separador de fecha *2 dic*. Mensajes, en este orden: **Esteban Lacroix:** «Mauricio, necesito que el pedido esté listo antes de la fecha. No hay margen para retrasos.» **Mauricio:** «Entendido. Se cumple.» **Esteban Lacroix:** «Eso espero. Confirmo con la central.»

## 4. Audio

Una sola grabación en la línea de tiempo (este caso no tiene confesión). Sube el `.mp3` a
`public/immersion/la-corona-de-sal/audio/`. Para obtener el guion con su Scene y su Sample
Context: `php artisan immersion:export-audio-scripts la-corona-de-sal`.

(`audio/a1-nota-voz-rosaura.mp3` — A1, minuto 36)
- **Scene:** nota de voz grabada con el teléfono en el interior de un taller artesanal costero, leve ambiente de taller de fondo (fibras, mesa de trabajo, rumor lejano de la feria), sin música.
- **Context:** Rosaura, mujer adulta de unos 55 años, graba para sí misma, con calma pero algo intrigada, que algo no cuadra con el material que llegó esta semana. Suena como quien nota una discrepancia administrativa en su propio negocio y decide investigarla con calma. Ningún tono de miedo, de peligro ni de presagio.
- **Speaker:** Speaker 1 - Rosaura Benavides.
- **Voz Gemini:** Vindemiatrix.
- **Guion (solo lo que se dice):** «Algo no cuadra con el material que llegó esta semana. Voy a comparar el permiso con lo que realmente tengo registrado, antes de que esto siga creciendo.»
- **Duración:** 10-15 segundos.
- **EVITAR:** tono de miedo o presagio, música, cualquier nombre mencionado, voz de persona en peligro.
