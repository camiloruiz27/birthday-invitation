# Prompts de assets — La primera firma

Total: **1 portada + 11 retratos + 9 evidencias visuales + 1 audio** (la confesión de Patricia,
A2, se genera por partida y no se graba; el único audio de la línea de tiempo es A1, el recuerdo
parcial de Leonor).

Todo se guarda en `public/immersion/la-primera-firma/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 11 retratos (10 personas del roster + la afectada) |
| `gallery/` | las 9 evidencias visuales |
| `audio/` | `a1-testimonio-leonor.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (11): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera primero V03 y V04: son las que
   sostienen la discrepancia de horas (00:05 frente a 10:00).
5. **Audio A1** (voz de Leonor, ver sección 4).
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Fragmentos privados | 12 | v02a (fragmento del acta, a un jugador); las cuatro cronologías son solo texto |
| Desarrollo | 24 | V02 (acta completa), V03, V05 |
| Complicación | 36 | V04 (más la cronología consolidada, solo texto) |
| Giro | 50 | V06 (más la declaración de Fabiola, solo texto) |
| Cierre | 64 | V07, V08, A1 (más la declaración de Rubén, solo texto) |
| Cruces pendientes | 72 | (solo texto) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el dato
exacto ES la prueba (00:05 ≠ 10:00). Para V01, v02a, V02, V03, V04, V05, V06, V07 y V08: genera
la imagen con la zona de texto **vacía o con texto de relleno ilegible**, y compón después el
texto exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica
cada hora contra `case.php` antes de guardar.

**Regla de la firma:** en V03 la firma de Ariana es siempre **genérica y auténtica**: un trazo
normal y pequeño, sin acercamientos, sin comparación con otra firma, sin tachaduras ni señales
de duda. Ninguna imagen del caso debe invitar a analizar caligrafía.

**Cronología maestra (todas las horas del caso salen de aquí):** correo de Gonzalo 21:47 ·
brindis 23:30 · cambio de año 00:00 · **firma y sello de la venta 00:05** · cierre de la sede
01:30 · apertura 07:45 · Leonor al archivo 08:00 · desayuno 08:30 · discurso de despedida 09:15 ·
Patricia entra al archivo 09:22 y sale 09:31 · hallazgo 09:40 · llaves 09:45 · **Acta de Posesión
10:00**. Documento antiguo de V07: radicado 14/03.

## Biblia visual de continuidad (pegar en los retratos)

- **Leonor Campuzano**, 60: cabello gris corto, ropa de oficina sobria, gafas de lectura.
- **Patricia Lemus**, 41: ropa de oficina práctica, carpeta de archivo siempre a mano y un termo de café.
- **Ariana Folgado**, 45: aspecto ejecutivo moderno, solapa con el logo (genérico) de la cooperativa.
- **Horacio Belmonte**, 63: traje formal clásico, aspecto digno.
- **Gonzalo Prada**, 52: aspecto persuasivo y cuidado, móvil siempre cerca.
- **Marisela Duarte**, 48: ropa de oficina directa, sin adornos.
- **Esteban Rueda**, 54: aspecto correcto pero algo tenso.
- **Fabiola Santos**, 33: ropa de asistente, tablet en mano.
- **Rubén Castaño**, 50: uniforme de seguridad, radio al cinto.
- **Yesenia Roldán**, 37: ropa de coordinación de eventos, tablilla con el cronograma de la celebración.
- **Mauricio Ibarra**, 58: traje formal, carpeta con el reglamento de la cooperativa.
- **Cooperativa Horizonte**: sede administrativa contemporánea, archivo ordenado con estanterías, salón de celebración con decoración discreta de fin de año (guirnaldas sobrias, sin carteles con texto).

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales. El logo de la cooperativa es un símbolo genérico (un sol naciente
simple), sin letras legibles.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un thriller de oficina: una mesa de presidencia de sala de juntas, de noche, con un documento sellado y una pluma apoyada encima, un reloj de pared al fondo marcando las doce y cinco, y a un lado una estantería de archivo con una sola carpeta asomando fuera de lugar; decoración discreta de fin de año (guirnaldas sobrias, confeti apagado sobre una silla vacía). Sensación de poder provisional, ironía callada, luz cálida de lámpara de escritorio y azul nocturno por la ventana. Formato vertical de póster de catálogo 2:3, estilo realista premium, sin texto integrado. **EVITAR:** sangre, violencia, personas visibles, texto o logos, firmas legibles, iconografía policial, estética de terror.

## 2. Retratos

(`photos/leonor-campuzano.png` — la afectada)
**PROMPT:** Retrato diegético tipo foto de credencial de una mujer de 60 años, cabello gris corto, gafas de lectura, ropa de oficina sobria, expresión serena, atenta y algo reservada, fondo de un archivo ordenado con estanterías. Formato retrato. **EVITAR:** apariencia de víctima, vendajes, heridas, dramatización, sangre.

(`photos/patricia-lemus.png` — Patricia Lemus)
**PROMPT:** Retrato diegético de una mujer de 41 años, ropa de oficina práctica, una carpeta de archivo bajo el brazo y un termo de café en la mano, expresión eficiente, cordial y muy ligeramente tensa, fondo de una oficina administrativa contemporánea. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto agresivo.

(`photos/ariana-folgado.png` — Ariana Folgado)
**PROMPT:** Retrato diegético de una mujer de 45 años, aspecto ejecutivo moderno, traje sobrio con un pequeño pin genérico de sol naciente en la solapa, expresión segura y entusiasta con un asomo de prisa, fondo de la sala de juntas de una cooperativa. Formato retrato. **EVITAR:** apariencia de culpable, arrogancia exagerada, logos de marcas reales, texto legible.

(`photos/horacio-belmonte.png` — Horacio Belmonte)
**PROMPT:** Retrato diegético de un hombre de 63 años, traje formal clásico, cabello canoso, expresión digna y algo dolida, fondo del salón de celebración con decoración discreta de fin de año. Formato retrato. **EVITAR:** apariencia de villano, ira, dramatización.

(`photos/gonzalo-prada.png` — Gonzalo Prada)
**PROMPT:** Retrato diegético de un hombre de 52 años, aspecto persuasivo y cuidado, traje bien cortado, móvil en la mano, sonrisa cordial, fondo de pasillo de una sede administrativa. Formato retrato. **EVITAR:** aspecto de estafador, mirada siniestra, dinero o documentos visibles, texto legible.

(`photos/marisela-duarte.png` — Marisela Duarte)
**PROMPT:** Retrato diegético de una mujer de 48 años, ropa de oficina directa y sin adornos, expresión franca y apasionada, brazos cruzados con calma, fondo de sala de juntas. Formato retrato. **EVITAR:** gesto hostil, apariencia de sospechosa, dramatización.

(`photos/esteban-rueda.png` — Esteban Rueda)
**PROMPT:** Retrato diegético de un hombre de 54 años, aspecto correcto pero algo tenso, camisa y chaqueta formales, mirada que se desvía ligeramente, fondo de oficina administrativa. Formato retrato. **EVITAR:** apariencia de culpable, sudor o nerviosismo exagerado, dinero visible.

(`photos/fabiola-santos.png` — Fabiola Santos)
**PROMPT:** Retrato diegético de una mujer de 33 años, ropa de asistente ejecutiva, tablet en la mano, expresión precisa, ordenada y algo preocupada, fondo de la oficina de presidencia. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, texto legible en la tablet.

(`photos/ruben-castano.png` — Rubén Castaño)
**PROMPT:** Retrato diegético de un hombre de 50 años, uniforme de seguridad, radio al cinto, expresión serena, metódica y algo incómoda, de pie junto a una mesa en un pasillo de oficinas. Formato retrato. **EVITAR:** apariencia de sospechoso, armas, estética policial agresiva.

(`photos/yesenia-roldan.png` — Yesenia Roldán)
**PROMPT:** Retrato diegético de una mujer de 37 años, ropa de coordinación de eventos, tablilla con el cronograma en el brazo, expresión organizada, rápida y amable, fondo del salón de celebración con decoración discreta. Formato retrato. **EVITAR:** apariencia de sospechosa, texto legible en la tablilla.

(`photos/mauricio-ibarra.png` — Mauricio Ibarra)
**PROMPT:** Retrato diegético de un hombre de 58 años, traje formal, carpeta gruesa con el reglamento bajo el brazo, expresión formal, pausada y neutral, fondo de una oficina jurídica con estantes de libros. Formato retrato. **EVITAR:** apariencia de juez o de villano, texto legible, símbolos legales exagerados (balanzas, mazos).

## 3. Evidencias visuales

(`gallery/v01-tabla-poderes.png` — V01, entrega a T+1)
**PROMPT:** Documento diegético tipo tabla de reglamento interno, de tres filas simples y claras, en formato de manual corporativo sobrio, fácil de leer, impreso o digital funcional; luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** lenguaje legal complejo, iconografía de alerta, más de tres filas, resaltados.
**TEXTO A COMPONER:** título *Cooperativa Horizonte — Reglamento interno — Tabla de poderes y matriz de autorizaciones*. Columnas *Regla · Quién firma · Cuándo*. Fila 1: **Operaciones ordinarias · Cualquier miembro vigente de la junta, saliente o entrante · En cualquier momento del día de transición.** Fila 2: **Operaciones patrimoniales · Quien ocupe el cargo de presidente en el momento exacto de la firma, no cualquier miembro de la junta · En el momento de la firma.** Fila 3: **Paso del cargo de presidente · De la junta saliente a la entrante solo con la firma del Acta de Posesión · En la fecha y hora programadas, no de forma automática a medianoche.**

(`gallery/v02a-acta-relevo-fragmento.png` — fragmento del acta de relevo; entrega privada a T+12)
**PROMPT:** Documento diegético tipo acta protocolaria de relevo de junta directiva, recortado, mostrando solo la parte superior con la lista breve de cargos salientes y entrantes y una línea final; formato institucional sobrio, papel con membrete genérico (sol naciente simple); luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** iconografía de alerta, resaltados, mostrar la hora del Acta (este fragmento NO la lleva).
**TEXTO A COMPONER:** título *Acta de relevo de la junta directiva — Cooperativa Horizonte (fragmento)*. Tabla *Cargo · Saliente · Entrante*: **Presidente · Horacio Belmonte · Ariana Folgado** / **Vocal · Marisela Duarte · Gonzalo Prada** / **Vocal · Esteban Rueda · Esteban Rueda (continúa)**. Línea final: **«La posesión formal de los cargos entrantes se perfecciona con la firma del Acta de Posesión, en la fecha y hora programadas.»** (Sin hora.)

(`gallery/v02-acta-relevo.png` — V02, acta completa; entrega a T+24)
**PROMPT:** El mismo documento de `v02a` (mismo membrete, mismo diseño), ahora completo, con la tabla de cargos, la línea sobre la posesión formal y un último renglón con la hora programada del Acta de Posesión, en formato institucional sobrio. Formato vertical. **EVITAR:** iconografía de alerta, resaltados artificiales, cambiar el diseño respecto a v02a.
**TEXTO A COMPONER:** igual que v02a, más: **«Acta de Posesión programada: 1 de enero, 10:00 — Salón de celebración.»** y, al pie, espacios de firma vacíos para las dos juntas.

(`gallery/v03-orden-patrimonial.png` — V03, orden patrimonial; entrega a T+24)
**PROMPT:** Documento diegético tipo orden de operación patrimonial, con un sello de radicación circular o rectangular que incluye fecha y hora exacta, una firma pequeña y genérica de la presidencia y una descripción breve de la venta de un inmueble, en un formato mercantil limpio y funcional; luz neutra de oficina. Dejar vacío el texto. Formato horizontal. **EVITAR:** iconografía de alerta, acercamientos o comparaciones de la firma, tachaduras, cualquier indicio de que la firma sea falsa, resaltar el sello.
**TEXTO A COMPONER:** título *Orden de operación patrimonial N.º OP-0112 — Cooperativa Horizonte*. Cuerpo: **Operación: venta de inmueble · Inmueble: Bodega Sur · Comprador: Grupo Insignia · Valor: US$ 340,000.** Línea de firma: *Firma por la Presidencia:* [firma genérica y auténtica, trazo normal] *Ariana Folgado — Presidenta electa.* Sello: **COOPERATIVA HORIZONTE · RADICADO · 01/01 · 00:05**.

(`gallery/v05-plano-sede.png` — V05, plano; entrega a T+24)
**PROMPT:** Diagrama vectorial simple tipo plano de planta de una sede administrativa pequeña, en diseño limpio y funcional: un salón de celebración grande a un lado, un pasillo central, y a lo largo del pasillo la oficina de presidencia, la secretaría general, oficinas administrativas, un puesto de seguridad con una mesa y, al fondo, el archivo con una única puerta de acceso que da al pasillo; un icono de cámara en el pasillo con un cono de visión orientado hacia la puerta del archivo. Dejar vacías las etiquetas y la nota. Formato horizontal. **EVITAR:** iconografía de alerta, más zonas de las indicadas, detalles excesivos, cono de cámara que entre al interior del archivo.
**TEXTO A COMPONER:** título *Sede administrativa — planta general*. Etiquetas de zonas: **Salón de celebración · Oficina de presidencia · Secretaría general · Oficinas administrativas · Puesto de seguridad · Pasillo de acceso · Archivo (único acceso)**. Nota de cobertura: **«Cámara 1: cubre el pasillo y la puerta del archivo. El interior del archivo queda fuera de cobertura, por confidencialidad de los documentos.»**

(`gallery/v04-agenda-transicion.png` — V04, agenda; entrega a T+36)
**PROMPT:** Captura maquetada de una agenda de actividades del día de la celebración, con horarios claros, en una interfaz de calendario simple y funcional, luz neutra de pantalla. Dejar vacío el texto. Ninguna fila resaltada. Formato horizontal. **EVITAR:** resaltados artificiales, iconografía de alarma, colores que destaquen una fila.
**TEXTO A COMPONER:** título *Agenda de transición — Jornada de celebración*. Filas: **08:30 Desayuno de las dos juntas — Salón de celebración** / **09:15 Discurso de despedida — Horacio Belmonte, presidente saliente** / **09:45 Entrega simbólica de llaves** / **10:00 Firma del Acta de Posesión — Salón de celebración** / **10:30 Palabras de la nueva presidenta — Ariana Folgado** / **11:00 Almuerzo de celebración**. Pie: *Coordinación: Yesenia Roldán*.

(`gallery/v06-correo-autorizacion.png` — V06, correo; entrega a T+50)
**PROMPT:** Captura maquetada de un correo electrónico breve, en una interfaz de correo simple y genérica, tono cordial y administrativo, luz neutra de pantalla. Dejar vacío el texto. Formato vertical. **EVITAR:** mención del nombre de la empresa compradora, iconografía de alarma, logos de clientes de correo reales.
**TEXTO A COMPONER:** *De: Gonzalo Prada · Para: Ariana Folgado · Enviado: 21:47 · Asunto: Bodega Sur — para esta noche.* Cuerpo: **«Ariana, buenas noches. Te escribo breve: el comprador ya tiene todo listo y no conviene dejarlo enfriar. Firma apenas se pueda, sin esperar la ceremonia de la mañana; la posesión es una formalidad que ya está decidida. Fabiola va a tener la orden impresa en presidencia. Que tengas buena noche. Gonzalo»**

(`gallery/v07-foto-archivo.png` — V07, foto del archivo; entrega a T+64)
**PROMPT:** Fotografía diegética funcional de una sala de archivo ordenada, con estanterías de carpetas, una carpeta visiblemente fuera de lugar, inclinada o asomando entre otras bien alineadas, con una etiqueta simple y legible en el lomo; sin personas, sin ningún elemento amenazante ni señal de violencia; luz artificial neutra de oficina. Dejar vacía la etiqueta. Formato horizontal. **EVITAR:** señales de forcejeo evidentes, desorden general, sangre, objetos caídos llamativos, elementos amenazantes, termos o tazas de café.
**TEXTO A COMPONER:** etiqueta del lomo de la carpeta fuera de lugar: **«INVENTARIO DE EQUIPOS · GERENCIA DE OPERACIONES · Rad. 14/03»**.

(`gallery/v08-lista-asistentes.png` — V08, lista de asistentes; entrega a T+64)
**PROMPT:** Documento diegético tipo lista de asistencia a un evento, con nombres, horarios de llegada y actividades asignadas durante la mañana de la celebración, en un formato simple y funcional de organización de eventos; luz neutra de oficina. Dejar vacío el texto. Formato horizontal. **EVITAR:** iconografía de alerta, resaltados artificiales, filas destacadas.
**TEXTO A COMPONER:** título *Celebración del relevo — Control de asistencia de la mañana*. Columnas *Nombre · Llegada · Actividad asignada*. Filas: **Horacio Belmonte · 08:10 · Discurso de despedida a las 09:15 (salón)** / **Ariana Folgado · 08:20 · Asistente al discurso y atención de invitados (salón)** / **Gonzalo Prada · 08:25 · Asistente al discurso y atención de invitados (salón)** / **Esteban Rueda · 08:15 · Mesa de bienvenida, 09:00–09:40 (puerta del salón)** / **Marisela Duarte · 08:40 · En el salón desde las 09:05** / **Leonor Campuzano · 08:00 · Preparación de la carpeta del acta (archivo)** / **Patricia Lemus · 07:50 · Apoyo de operaciones (oficinas administrativas)** / **Fabiola Santos · 07:55 · Apoyo a presidencia (oficina de presidencia)** / **Rubén Castaño · 07:45 · Seguridad (pasillo de acceso al archivo)** / **Mauricio Ibarra · 09:30 · Lectura del Acta (salón, 10:00)**. Pie: *Coordinación: Yesenia Roldán*.

## 4. Audio

(`audio/a1-testimonio-leonor.mp3` — A1, minuto 64; **15–20 segundos**)
- **Scene:** Habitación de descanso de la sede, ambiente silencioso, apenas un reloj de pared lejano; sin música.
- **Context:** Leonor, ya recuperada de la conmoción, cuenta con voz débil pero serena lo poco que recuerda, despacio y con pausas. Debe sonar como un recuerdo genuinamente parcial, no como una pista oculta a propósito. No identifica a nadie.
- **Speaker:** Speaker 1 - Leonor Campuzano
- **Voice:** `Vindemiatrix` (mujer adulta mayor)
- **Script (solo lo que se dice):**
  > No recuerdo bien su cara... todo pasó muy rápido. Pero recuerdo el olor del café que siempre lleva encima. Y un sonido, como de una carpeta cayendo contra el estante. Nada más. Lo siento, me gustaría recordar más.
- **EVITAR:** música dramática, una identificación clara de una persona, cualquier detalle que no se pueda deducir antes con la evidencia.

(A2 — confesión de Patricia) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Kore) para incluir las preguntas que esa mesa le hizo a Patricia. Dirección de voz: empieza nerviosa y defensiva, se estabiliza hacia un reconocimiento genuino; es alguien que reacciona por miedo a un error propio pequeño, no alguien con un plan. Sin música, sin sobreactuar, sin contenido violento.
