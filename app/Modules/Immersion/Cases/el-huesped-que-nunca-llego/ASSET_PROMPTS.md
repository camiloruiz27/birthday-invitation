# Prompts de assets — El huésped que nunca llegó

Total: **1 portada + 10 retratos + 13 evidencias visuales + 2 audios** (este caso no tiene audio de
confesión: el audio revelador es A2, el mensaje de voz de Carmen).

Todo se guarda en `public/immersion/el-huesped-que-nunca-llego/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos |
| `gallery/` | las 13 evidencias visuales |
| `audio/` | `a1-llamada-confirmacion.wav`, `a2-mensaje-voz-carmen.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias en orden de entrega** (ver tabla). V3, V4 y V9 deben salir de la misma "sesión"
   visual (misma habitación, misma luz cálida, mismo tipo de interior). V8, V8a y V8b son la
   MISMA ficha: haz primero V8 y recorta/rehaz las otras dos con el mismo diseño.
5. **Audios** A1 y A2.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V3, V7, A1 |
| Expansión | 10 | V2, V5, V10 |
| Pistas privadas | 22 | V8a (columna del mesero), V8b (columna del técnico) |
| Complicación | 34 | V8 (ficha completa), V4, V9 |
| Giro | 45 | V6, V11, A2 |
| Acusación | 55 | (formulario; sin evidencia nueva) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el dato
exacto ES la prueba (check-in «no completado», pago en efectivo, una columna de la ficha que no
coincide con otra). Para V1, V2, V5, V6, V8, V8a, V8b y V10: genera la imagen con la zona de texto
**vacía o con texto de relleno ilegible**, y compón después el texto exacto indicado en «TEXTO A
COMPONER» con un editor (Canva, Figma, Photoshop). Verifica cada dato contra `case.php` y los `.md`
antes de guardar. Fechas del caso: llegada y cena el **14/11**; segundo día **15/11**; llamadas del
hotel el **06/11** y el **11/11**.

## Biblia visual de continuidad (pegar en los retratos y en las escenas)

- **«Daniel» (el hombre real, autor del engaño)**: complexión delgada, alto (~1,85 m), cabello
  oscuro corto, sin barba, chaqueta clara beige, unos 35 años. **Nunca se le ve la cara** y su
  ficha del tablero usa un avatar neutro sin rasgos (ver sección 2).
- **Carmen Otero**, 35: cabello castaño ondulado, chaqueta de punto color vino, mochila pequeña.
- **Ramón Aguilera**, 50: complexión robusta, más bajo (~1,70 m), gafas, cabello canoso, camisa y
  pantalón formal sencillo (ropa de viaje de trabajo).
- **Mercedes Quintanilla**, 58: cabello canoso corto, chaqueta de punto sobre ropa de trabajo de
  recepción.
- **Pablo Andrade**, 26: uniforme sencillo de camarero.
- **Germán Figueroa**, 45: mono de trabajo con herramientas.
- **Olga Reyes**, 63: ropa cómoda de viaje, bastón.
- **Iván Cáceres**, 34: chaqueta ligera, gorra.
- **Lorena Otero**, 39: ropa casual de viaje; se parece levemente a Carmen (hermanas).
- **El hotel**: edificio rural pequeño, recepción modesta con un casillero de llaves, restaurante de
  pocas mesas, pasillo con puertas numeradas, fuera de temporada (pocos huéspedes, ambiente
  tranquilo). Luz cálida de tarde/atardecer.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos, sin
logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio íntimo: el pasillo de un hotel rural pequeño, fuera de temporada, visto desde el fondo hacia una puerta de madera con el número 4 en latón, luz cálida y tenue de atardecer entrando por una ventana lateral, una llave con llavero numerado «4» colgando de la cerradura y un equipaje cerrado apoyado junto a la puerta. Atmósfera extraña, tranquila y levemente inquietante, de memoria imperfecta; sin personas visibles. Debe sugerir que lo importante es quién estuvo realmente detrás de esa puerta. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, escena de crimen, siluetas amenazantes, iconografía policial, terror, texto o logos.

## 2. Retratos

(`photos/daniel-rivas.png` — «víctima»: la persona por la que se denunció la desaparición; NO es un retrato)
**PROMPT:** Fotografía diegética funcional de la puerta cerrada de la habitación 4 de un hotel rural pequeño, vista de frente y recortada en formato retrato: puerta de madera pintada, número «4» en latón, luz cálida de pasillo, sin persona alguna, sin llave puesta. Debe verse como una foto de inspección del cuarto, no como el retrato de alguien. **EVITAR:** cualquier persona, cara o silueta humana; cinta policial; dramatización; objetos personales.

(`photos/mercedes-quintanilla.png` — Mercedes Quintanilla)
**PROMPT:** Retrato diegético de una mujer de 58 años, cabello canoso corto, chaqueta de punto sobre ropa de trabajo de recepción, expresión profesional, amable y algo ansiosa de gerente de hotel pequeño, fondo del mostrador de recepción de un hotel rural con un casillero de llaves detrás. Formato retrato. **EVITAR:** apariencia de culpable, dramatización, iluminación siniestra.

(`photos/pablo-andrade.png` — Pablo Andrade)
**PROMPT:** Retrato diegético de un hombre de 26 años, uniforme sencillo de camarero, expresión juvenil, algo nerviosa y evasiva, fondo de un pequeño restaurante de hotel rural con pocas mesas. Formato retrato. **EVITAR:** dinero o caja visibles, gesto de culpa obvio, dramatización.

(`photos/german-figueroa.png` — Germán Figueroa)
**PROMPT:** Retrato diegético de un hombre de 45 años, mono de trabajo con herramientas colgadas del cinturón, expresión directa, algo hosca y reservada, fondo de pasillo de hotel rural con una escalera de mano y una caja de herramientas. Formato retrato. **EVITAR:** villano obvio, gesto agresivo, aspecto sospechoso teatral.

(`photos/olga-reyes.png` — Olga Reyes)
**PROMPT:** Retrato diegético de una mujer de 63 años, ropa cómoda de viaje, apoyada en un bastón, expresión reservada y serena de quien guarda algo, fondo del pasillo de un hotel rural con una puerta numerada. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, ancianidad caricaturizada.

(`photos/ivan-caceres.png` — Iván Cáceres)
**PROMPT:** Retrato diegético de un hombre de 34 años, chaqueta ligera y gorra, expresión relajada y conversadora con un punto de evasiva, apoyado junto a un coche común aparcado frente a la entrada de un hotel rural al atardecer. Formato retrato. **EVITAR:** marcas reales de coche o de taxi, rótulos, gesto amenazante.

(`photos/huesped-habitacion-4.png` — «Daniel Rivas (huésped de la habitación 4)», no localizado; sin rasgos)
**PROMPT:** Avatar neutro de «persona sin foto disponible»: silueta de busto genérica e indistinta, en tonos grises cálidos sobre fondo liso, sin rostro, sin ropa reconocible, sin altura, complexión, cabello ni color de prenda que se puedan leer (NO debe parecerse al hombre alto, delgado y de chaqueta beige de la biblia visual: esa descripción es la solución y no puede verse en el tablero desde el minuto 10). Formato retrato. **EVITAR:** cualquier rasgo físico, chaqueta clara, cabello oscuro corto, cara visible, expresión siniestra, silueta de amenaza, estética de thriller, cartel de «se busca».

(`photos/ramon-aguilera.png` — Ramón Aguilera)
**PROMPT:** Retrato diegético de un hombre de 50 años, complexión robusta, más bajo, gafas, cabello canoso, camisa y pantalón formal sencillo de viaje de trabajo, expresión cordial, tranquila y algo cansada, fondo del pasillo de un hotel rural. Formato retrato. **EVITAR:** apariencia de sospechoso, parecido con el hombre alto y delgado de la biblia visual, dramatización.

(`photos/lorena-otero.png` — Lorena Otero)
**PROMPT:** Retrato diegético de una mujer de 39 años, ropa casual de viaje, cabello castaño con un leve parecido con Carmen, expresión preocupada pero serena y cooperativa, fondo de la recepción de un hotel rural. Formato retrato. **EVITAR:** llanto exagerado, dramatización, apariencia de sospechosa.

(`photos/sistema-reservas.png` — sistema de reservas)
**PROMPT:** Imagen diegética tipo retrato funcional de un ordenador de recepción de un hotel pequeño con una pantalla sobria de gestión de reservas (calendario y lista de habitaciones, texto ilegible), usada como avatar del sistema. Formato retrato. **EVITAR:** marcas reales, alertas rojas, estética futurista, personas.

## 3. Evidencias visuales

(`gallery/v1-plano-hotel.png` — V1, plano del hotel)
**PROMPT:** Documento diegético tipo plano funcional simple de un hotel rural pequeño, en líneas esquemáticas claras: entrada principal, recepción (con un casillero de llaves señalado), restaurante de pocas mesas, un pasillo con habitaciones numeradas del 1 al 6 (la 4 visible). Aspecto de plano de orientación del hotel, líneas limpias, iluminación neutra. Formato horizontal. Dejar vacías las etiquetas. **EVITAR:** iconografía de alerta, accesos no mencionados (sin puertas traseras ni salidas extra), código de colores arbitrario.
**TEXTO A COMPONER:** etiquetas simples: *Entrada principal · Recepción · Casillero de llaves · Restaurante · Pasillo · Hab. 1 · Hab. 2 · Hab. 3 · Hab. 4 · Hab. 5 · Hab. 6*.

(`gallery/v7-fachada-hotel.png` — V7, foto ambiental del hotel)
**PROMPT:** Fotografía diegética de ambiente de la fachada de un hotel rural pequeño, fuera de temporada, con pocos coches en el estacionamiento y luz cálida de atardecer, sin personas visibles, sensación de tranquilidad y baja ocupación. Fotografía documental de exterior. Formato horizontal. **EVITAR:** personas, marcas reales, estética de póster promocional, rótulos legibles.

(`gallery/v3-habitacion-4.png` — V3, habitación 4)
**PROMPT:** Fotografía diegética funcional de una habitación de hotel rural modesta, con una bolsa de viaje abierta sobre la cama mostrando ropa genérica y sin ningún objeto personal identificable (sin fotos, documentos ni objetos con iniciales), luz cálida de tarde entrando por una ventana pequeña. Debe verse como una habitación ocupada en apariencia pero con un contenido genérico e impersonal. Formato 4:5. **EVITAR:** notas de despedida, dramatización, objetos personales identificables, desorden dramático.

(`gallery/v2-registro-reservas.png` — V2, registro de reservas)
**PROMPT:** Captura de pantalla diegética de un sistema simple de reservas de hotel, con una ficha de reserva en una interfaz sobria y funcional; dejar vacía la zona de texto (campos con relleno ilegible). Iluminación neutra de pantalla. Formato vertical, aspecto de ficha de reserva. **EVITAR:** iconografía de alerta, mensajes de error en rojo, interpretación escrita en el propio documento.
**TEXTO A COMPONER:** *FICHA DE RESERVA — Nº R-0412 · Huésped: Daniel Rivas · Habitación: 4 · Entrada: 14/11 · Salida: 16/11 (2 noches) · Origen: teléfono (registrada el 06/11) · Garantía: tarjeta · Cargo final: pendiente · Check-in: NO COMPLETADO · Documento de identidad: sin escaneo.* Todos los campos con el mismo peso visual, sin resaltar el check-in.

(`gallery/v5-recibo-restaurante.png` — V5, recibo del restaurante)
**PROMPT:** Documento diegético tipo recibo de restaurante de hotel, formato de recibo pequeño impreso, fotografiado sobre una mesa de madera con iluminación neutra, con una anotación manuscrita breve en bolígrafo junto al número de mesa. Dejar vacío el cuerpo del texto. Formato vertical. **EVITAR:** iconografía de alerta, logos reales, subrayados o círculos que resalten el nombre.
**TEXTO A COMPONER:** *RESTAURANTE — HOTEL RURAL · Fecha: 14/11 · Hora: 21:12 · Mesa 3 (2 personas) · 2 × Plato del día · 1 × Botella de vino de la casa · 2 × Café · Total: 64.500 · Pago: EFECTIVO.* Anotación manuscrita junto a «Mesa 3»: **«Rivas»**.

(`gallery/v10-registro-llamadas.png` — V10, registro de llamadas)
**PROMPT:** Captura de pantalla diegética de un registro simple de llamadas entrantes de un sistema telefónico de hotel, interfaz sobria y funcional, tabla con cinco filas, ninguna resaltada. Dejar vacías las celdas. Iluminación neutra de pantalla. Formato horizontal. **EVITAR:** iconografía de alarma, números de teléfono identificables de una persona real, resaltar las filas del número oculto.
**TEXTO A COMPONER:** columnas *Fecha · Hora · Tipo · Duración · Origen* — 03/11 · 09:40 · Entrante · 00:35 · Proveedor (lácteos) / **06/11 · 12:10 · Entrante · 03:40 · Número oculto** / 08/11 · 18:20 · Entrante · 01:15 · Proveedor (panadería) / **11/11 · 17:35 · Entrante · 00:52 · Número oculto** / 12/11 · 10:05 · Entrante · 02:10 · Proveedor (panadería). (El negrita es solo para esta guía: en la imagen todas las filas se ven igual.)

(`gallery/v8a-fragmento-mesero.png` — V8 parcial, columna del mesero; entrega privada a los 22 min)
**PROMPT:** La misma ficha comparativa que V8 (mismo diseño, mismo encabezado), pero cortada o rota como fragmento: solo se ve **una columna**, la de Pablo Andrade, con el resto de la tabla cortado por el borde. Documento impreso simple de investigación. Formato horizontal. **EVITAR:** resaltados, flechas, conclusiones, cualquier otra columna visible.
**TEXTO A COMPONER:** encabezado *Ficha de descripciones — persona identificada como «Daniel»*. Columna *Testigo: Pablo Andrade (mesero)* — Altura aprox.: alto (≈ 1,85 m) · Complexión: delgada · Cabello: oscuro, corto · Rasgos distintivos: sin barba · Ropa: chaqueta clara · Edad aprox.: unos 35.

(`gallery/v8b-fragmento-tecnico.png` — V8 parcial, columna del técnico; entrega privada a los 22 min)
**PROMPT:** La misma ficha comparativa que V8, cortada como fragmento: solo se ve **una columna**, la de Germán Figueroa, con el resto de la tabla cortado. Mismo estilo y diseño que V8a. Formato horizontal. **EVITAR:** resaltados, flechas, conclusiones, cualquier otra columna visible.
**TEXTO A COMPONER:** encabezado *Ficha de descripciones — persona identificada como «Daniel»*. Columna *Testigo: Germán Figueroa (técnico)* — Altura aprox.: más bajo (≈ 1,70 m) · Complexión: robusta · Cabello: canoso · Rasgos distintivos: gafas · Ropa: camisa y pantalón formal sencillo · Edad aprox.: unos 50.

(`gallery/v8-ficha-comparativa.png` — V8, ficha comparativa completa; entrega a los 34 min)
**PROMPT:** Documento diegético tipo ficha comparativa simple, con una tabla de tres columnas (una por testimonio) y seis filas, referidas a «la persona que identificaron como Daniel», sin resolver ni marcar ninguna columna como correcta o incorrecta, en un formato de ficha de investigación funcional y sobrio, impreso. Dejar vacías las celdas. Formato horizontal. **EVITAR:** resaltados o marcas que revelen la incompatibilidad, colores distintos por columna, interpretación escrita.
**TEXTO A COMPONER:** encabezado *Ficha de descripciones — persona identificada como «Daniel»*.
| | Pablo Andrade (mesero) | Iván Cáceres (conductor) | Germán Figueroa (técnico) |
|---|---|---|---|
| Altura aprox. | alto (≈ 1,85 m) | alto | más bajo (≈ 1,70 m) |
| Complexión | delgada | delgada | robusta |
| Cabello | oscuro, corto | oscuro, corto | canoso |
| Rasgos distintivos | sin barba | — | gafas |
| Ropa | chaqueta clara | chaqueta clara | camisa y pantalón formal sencillo |
| Edad aprox. | unos 35 | treinta y tantos | unos 50 |

(`gallery/v4-chaqueta-bolsa.png` — V4, objeto de la bolsa)
**PROMPT:** Fotografía diegética funcional en primer plano de una chaqueta genérica sin etiquetas visibles ni marcas personales, doblada dentro de la bolsa de viaje de V3, con el mismo tipo de luz cálida e interior. Debe verse como un objeto de aspecto nuevo o poco usado, sin desgaste personal. Formato 4:5. **EVITAR:** etiquetas con nombres, iniciales, marcas personales, manchas o dramatización.

(`gallery/v9-llave-casillero.png` — V9, llave de la habitación 4)
**PROMPT:** Fotografía diegética funcional de una llave física de hotel con llavero numerado «4», sobre el mostrador de un casillero de llaves de recepción de un hotel rural pequeño, sin personas presentes, luz cálida de recepción. El número «4» visible de forma natural. Formato 4:5. **EVITAR:** personas, iconografía de alerta, manos, otras llaves legibles.

(`gallery/v6-chat-carmen.png` — V6, chat de Carmen con «Daniel»)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería entre dos personas en una interfaz simple y genérica, formato de captura de móvil, tono cálido. Dejar vacías las burbujas de mensaje (relleno ilegible). Iluminación neutra de pantalla. Formato vertical. **EVITAR:** iconografía de alarma, contenido íntimo o explícito, logos de apps reales, fotos de perfil reconocibles.
**TEXTO A COMPONER:** contacto «Daniel» (sin foto).
- *14/11 11:20 — Carmen:* Ya estoy en el hotel. Es chiquito y precioso. Qué ganas de verte.
- *14/11 11:24 — Daniel:* Llego esta tarde. Tú relájate.
- *14/11 11:26 — Carmen:* Ya te mandé lo de tu pasaje, no te preocupes por eso.
- *14/11 11:31 — Daniel:* Gracias, de verdad. Ya lo arreglamos allá.
- *14/11 11:35 — Carmen:* Te voy a decir Daniel hasta que me cuentes cómo te dicen de verdad los tuyos.
- *14/11 11:40 — Daniel:* Daniel está bien por ahora. Los que me conocen de verdad me llaman de otra forma. Ya te cuento cuando nos veamos.
- *15/11 12:05 — Daniel:* Anoche fue perfecto. Hoy hablamos con calma, ¿sí?
- *15/11 12:07 — Carmen:* Sí. Te quiero.

(`gallery/v11-foto-carmen.png` — V11, foto de Carmen)
**PROMPT:** Fotografía diegética personal e informal de una mujer de unos 35 años, cabello castaño ondulado, chaqueta de punto color vino, sonriendo de forma natural en un entorno cotidiano (un parque o una cocina), tipo foto de galería de teléfono compartida por un familiar. Luz natural cálida, formato 4:5. Debe ser una imagen cálida y cotidiana, sin relación aparente con el caso. **EVITAR:** elementos dramáticos, texto superpuesto tipo «desaparecida», cartel de búsqueda.

## 4. Audio

Los guiones están en `case.php`; se exportan listos para estudio con
`php artisan immersion:export-audio-scripts el-huesped-que-nunca-llego`. Guardar en `audio/`.

(`audio/a1-llamada-confirmacion.wav` — A1, la llamada de confirmación, 12–15 s)
**GUION:** *Hombre:* «Buenas tardes, llamo para confirmar una reserva a nombre de Daniel Rivas para este fin de semana. Voy a llegar bastante tarde, ¿sería posible dejar la llave en el casillero de la entrada? No hace falta que nadie me espere despierto.» *Mercedes:* «Sin problema, señor Rivas, se la dejamos lista.»
**PROMPT:** Llamada telefónica diegética entre un hombre adulto de voz cálida y algo apresurada que confirma una reserva de hotel y pide que le dejen la llave en un casillero para una llegada tardía, y una mujer adulta de voz profesional y amable que acepta el arreglo. Calidad de línea telefónica normal, sin música. **EVITAR:** tono sospechoso o siniestro, música de tensión, ruidos de fondo exagerados.

(`audio/a2-mensaje-voz-carmen.wav` — A2, mensaje de voz de Carmen a su hermana, 12–15 s)
**GUION:** *Carmen:* «Lorena, no vas a creer lo bien que va todo. Es exactamente como me lo imaginaba hablando con él estos meses. Mañana seguimos hablando, ¿sí? Te quiero.»
**PROMPT:** Mensaje de voz diegético de una mujer adulta de 35 años, tono emocionado e ilusionado, que cuenta a su hermana que un encuentro presencial con alguien que conoció por internet va muy bien, sin ningún indicio de peligro o alarma en el tono; ambiente cálido y cotidiano de fondo, sin música. **EVITAR:** tono alarmante o premonitorio, música de tensión, llanto, temblor en la voz.

(Confesión del culpable) **No existe.** El hombre que usó el nombre «Daniel Rivas» no tiene confesión en este caso: el final ofrece solo la solución y los epílogos.
