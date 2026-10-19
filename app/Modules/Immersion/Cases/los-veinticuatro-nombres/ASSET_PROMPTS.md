# Prompts de assets — Los Veinticuatro Nombres

Total: **1 portada + 13 retratos (12 del roster + la víctima) + 17 evidencias visuales (V01–V11 y los
6 extractos F1–F6) + 1 audio** (A1, la nota de voz de Rosario). La confesión de Marisol (A2) se
genera por partida y no se graba.

Todo se guarda en `public/immersion/los-veinticuatro-nombres/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 13 retratos |
| `gallery/` | las 17 evidencias visuales |
| `audio/` | `a1-nota-voz-rosario.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (13): son lo primero que ve el jugador y lo que exige el test de integridad. Fija el
   rostro de Marisol antes que nada: aparece en V03 como una figura más de la cuadrilla (sin rostro
   reconocible) y su credencial es la pieza de V10.
4. **Evidencias** en orden de entrega (ver tabla). Primero la **tabla de los 24** (sección propia, más
   abajo): V02 y los seis extractos F1–F6 salen de la misma maqueta; hazlos en una sola sesión para
   que la tipografía, las columnas y los nombres coincidan.
5. **Audio A1** y `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01, V10 |
| Apertura | 12 | V07 · y (a jugadores distintos, uno por correo) los extractos F1, F2, F3, F4, F5, F6 |
| Expansión | 25 | V02, V08 |
| Desarrollo | 40 | V03, V04 |
| Complicación | 55 | V05 · PP (registro de pagos completo, solo texto) · A1 (audio) · RA (resumen agregado, solo texto) |
| Giro | 70 | DD (declaración ampliada de Dora, solo texto), V06, V11 |
| Cierre | 85 | V09 · RE (registro de entregas del almacén, solo texto) |

Códigos sin imagen (PP, RA, DD, RE) no necesitan asset: su contenido ya está en los `.md`.

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres, cifras ni tablas**, y en este caso el dato
exacto ES la prueba (86 h ≠ 41 h; 16:34; la pizarra de la jornada). Para V01, V02, F1–F6, V03, V04, V05,
V06, V07, V08, V09, V10 (credencial) y V11: genera la imagen con la zona de texto **vacía o con texto de
relleno ilegible**, y compón después el texto exacto indicado en "TEXTO A COMPONER" con un editor
(Canva, Figma, Photoshop). Verifica cada cifra contra la tabla de abajo antes de guardar.

**Cronología maestra (todas las horas del caso salen de aquí).** Sin fechas de calendario: es "el día de
la gala". Gala 21:00 (Salón de Eventos) · junta previa 19:30 (sede) · tarjeta de Rosario en la puerta de
la planta de administración (P2): 08:52, 14:10, 16:34 · Dora (ronda de cierre) P2: 18:38 · Patricio en
contabilidad (C1): 08:30 entra / 13:10 sale / 14:02 entra / 18:47 sale · almacén (recepciones firmadas
por Hugo y Ramiro): 15:45, 16:40, 17:25 · Esteban en el salón 15:30–17:40 · Noelia con Camilo
16:00–17:30 · Marisol sube con Rosario ~16:34 y baja sola ~16:54 · Dora encuentra a Rosario 18:40.
Tiempos (V08): almacén ↔ sede 15 min en carro · salón ↔ sede 10 min a pie · R1 ↔ sede 20 min · R2 ↔ sede
25 min · almacén ↔ salón 20 min. Campaña de ocho semanas; V03 = R1, semana 3; V04 = R2, semana 5.

## La tabla de los 24 (TEXTO A COMPONER exacto, fuente de V01, V02, F1–F6, V05 y V07)

Son datos, **no personajes**: solo Marisol y Camilo son personas del roster; los otros 22 no tienen
retrato. "Declarado" es lo que la fundación certificó; "verificado" es lo que firmó cada uno en obra
(NO va en ninguna imagen de la mesa, solo en el registro de horas y en el acta y el resumen).

| Nombre | Rol declarado | Horas declaradas | Horas verificadas (rol) | Diferencia | Extracto |
|---|---|---|---|---|---|
| Marisol Ferreira | Jefa de cuadrilla | 86 | 41 (ayudante de obra) | 45, mayor | F1 |
| Antonia León | Jefa de acabados | 60 | 47 (acabados) | 13, moderada | F2 |
| Rubén Paredes | Ayudante de obra | 48 | 36 (ayudante de obra) | 12, menor | F3 |
| Camilo Serrano | Ayudante de obra | 52 | 44 (ayudante de obra) | 8, menor, caso aparte | F4 |
| Lucía Ibarra | Ayudante de obra | 38 | 38 | — | F1 |
| Martín Oyarzún | Jefe de cuadrilla | 54 | 54 | — | F2 |
| Beatriz Salcedo | Acabados | 40 | 40 | — | F3 |
| Joaquín Reyes | Ayudante de obra | 33 | 33 | — | F4 |
| Celia Franco | Logística | 29 | 29 | — | F5 |
| Agustín Moura | Ayudante de obra | 45 | 45 | — | F6 |
| Pilar Cuevas | Acabados | 36 | 36 | — | F1 |
| Nicolás Behar | Ayudante de obra | 31 | 31 | — | F2 |
| Yolanda Prat | Logística | 27 | 27 | — | F3 |
| Diego Lerma | Ayudante de obra | 42 | 42 | — | F4 |
| Carla Viana | Acabados | 35 | 35 | — | F5 |
| Sergio Botero | Jefe de cuadrilla | 50 | 50 | — | F6 |
| Teresa Alcántara | Ayudante de obra | 30 | 30 | — | F1 |
| Ignacio Duarte | Logística | 26 | 26 | — | F2 |
| Gabriela Soto | Ayudante de obra | 39 | 39 | — | F3 |
| Hernán Pacheco | Acabados | 33 | 33 | — | F4 |
| Milagros Vega | Ayudante de obra | 28 | 28 | — | F5 |
| Tomás Iriarte | Logística | 24 | 24 | — | F6 |
| Claudia Reinoso | Ayudante de obra | 37 | 37 | — | F5 |
| Felipe Mendoza | Acabados | 31 | 31 | — | F6 |

Totales: horas declaradas **954**; horas verificadas **876**; diferencia **78**. Los cuatro nombres con
diferencia (Marisol, Antonia, Rubén, Camilo) están siempre en extractos distintos (F1, F2, F3, F4).

**Reparto en extractos (texto exacto de cada imagen).** Cabecera común: *Hoja de certificación de horas —
voluntariado de obra — Extracto N de 6*. Columnas: *Nombre · Rol certificado · Horas certificadas*. Sin
columna de aprobación, sin observaciones y sin resaltar ninguna fila.

- **F1:** Marisol Ferreira · Jefa de cuadrilla · 86 / Lucía Ibarra · Ayudante de obra · 38 / Pilar Cuevas · Acabados · 36 / Teresa Alcántara · Ayudante de obra · 30
- **F2:** Antonia León · Jefa de acabados · 60 / Martín Oyarzún · Jefe de cuadrilla · 54 / Nicolás Behar · Ayudante de obra · 31 / Ignacio Duarte · Logística · 26
- **F3:** Rubén Paredes · Ayudante de obra · 48 / Beatriz Salcedo · Acabados · 40 / Yolanda Prat · Logística · 27 / Gabriela Soto · Ayudante de obra · 39
- **F4:** Camilo Serrano · Ayudante de obra · 52 / Joaquín Reyes · Ayudante de obra · 33 / Diego Lerma · Ayudante de obra · 42 / Hernán Pacheco · Acabados · 33
- **F5:** Celia Franco · Logística · 29 / Carla Viana · Acabados · 35 / Milagros Vega · Ayudante de obra · 28 / Claudia Reinoso · Ayudante de obra · 37
- **F6:** Agustín Moura · Ayudante de obra · 45 / Sergio Botero · Jefe de cuadrilla · 50 / Tomás Iriarte · Logística · 24 / Felipe Mendoza · Acabados · 31

**V02 (copia pública, anonimizada).** Mismos veinticuatro nombres con inicial y apellido, rol declarado y
horas declaradas, sin columna de aprobación ni observaciones, en el orden de las casillas del
calendario (orden alfabético por nombre de pila).

## Biblia visual de continuidad (pegar en los retratos)

- **Rosario Beltrán**, 58: cabello canoso corto, ropa de oficina sobria, gafas de lectura colgando del cuello.
- **Hugo Benavídez**, 45: complexión robusta, chaleco de trabajo con el logo genérico de la fundación, casco colgado al hombro.
- **Marisol Ferreira**, 34: cabello castaño recogido, ropa de trabajo cómoda, una pulsera sencilla que suele llevar siempre, credencial de voluntaria colgada al cuello.
- **Fernanda Ocampo**, 56: elegante pero cálida, pañuelo navideño, carpeta institucional bajo el brazo.
- **Valentina Ríos**, 27: ropa creativa, cámara compacta colgada al cuello, tablet con el diseño del calendario.
- **Patricio Núñez**, 50: aspecto meticuloso, chaqueta formal, calculadora de bolsillo.
- **Ramiro Casals**, 48: chaqueta de trabajo con un logo genérico de proveedor de materiales, carpeta de pedidos.
- **Camilo Serrano**, 38: ropa casual de trabajador social, mochila con carpetas de los voluntarios.
- **Dora Quintero**, 62: uniforme sencillo de conserjería, manojo de llaves al cinto.
- **Esteban Vidal**, 41: traje casual de relaciones públicas, tablet con la lista de donantes.
- **Noelia Camacho**, 29: ropa de obra, tablilla con el registro de turnos.
- **Fundación Calles Cálidas**: sede administrativa con decoración navideña discreta, dos obras activas con andamios y materiales, un almacén funcional, un salón de eventos en preparación para la gala. Logo genérico (casa y corazón estilizados), sin marcas reales.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos, sin logos
de marcas reales. Ambientación navideña discreta (luces, guirnaldas), sin fechas de calendario.

**Nota de continuidad general:** no hay veinticuatro retratos. El Calendario y las tarjetas se
presentan como un conjunto (tablero, mesa con tarjetas); los veintidós nombres sin ficha no tienen cara.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio de oficina y comunidad, en **formato vertical de póster de catálogo (2:3)**: en primer plano, sobre una mesa de madera cálida, una cuadrícula de veinticuatro tarjetas de cartulina artesanales dispuestas como un calendario de Adviento, todas con la zona de texto vacía, y unas gafas de lectura sobre su cordón junto a ellas; al fondo, desenfocada, una oficina pequeña con una lámpara de escritorio encendida y la puerta entreabierta, y luces navideñas suaves colgadas. Sensación de campaña de reconocimiento cálida con algo que no cuadra; ausencia, silencio, calidez fría. Estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror, rostros visibles, texto o números legibles en las tarjetas, logos.

## 2. Retratos

(`photos/rosario-beltran.png` — víctima)
**PROMPT:** Retrato diegético tipo foto de credencial institucional de una mujer de 58 años, cabello canoso corto, ropa de oficina sobria, gafas de lectura colgando del cuello, expresión serena, meticulosa y algo reservada, fondo neutro de oficina con una estantería. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales.

(`photos/hugo-benavidez.png` — Hugo Benavídez)
**PROMPT:** Retrato diegético de un hombre de 45 años, complexión robusta, chaleco de trabajo con el logo genérico de una fundación, casco colgado al hombro, expresión segura, técnica y algo seca, fondo de almacén de materiales con estanterías. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, dinero visible, logos de marcas reales.

(`photos/marisol-ferreira.png` — Marisol Ferreira)
**PROMPT:** Retrato diegético de una mujer de 34 años, cabello castaño recogido, ropa de trabajo cómoda, una pulsera sencilla en la muñeca, credencial de voluntaria colgada al cuello con la zona de texto vacía, expresión cálida, colaboradora y algo nerviosa, fondo de una vivienda en renovación con luz natural. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, llanto, dramatización, texto legible en la credencial. (Fija este rostro: es la persona de V10 y de la solución.)

(`photos/fernanda-ocampo.png` — Fernanda Ocampo)
**PROMPT:** Retrato diegético de una mujer de 56 años, elegante pero cálida, pañuelo navideño, carpeta institucional bajo el brazo, expresión institucional, amable y algo defensiva, fondo de salón de eventos con guirnaldas discretas. Formato retrato. **EVITAR:** villana obvia, gesto amenazante, texto legible.

(`photos/valentina-rios.png` — Valentina Ríos)
**PROMPT:** Retrato diegético de una mujer de 27 años, ropa creativa, cámara compacta colgada al cuello, tablet con un diseño de calendario en la mano (pantalla sin texto legible), expresión entusiasta con un asomo de incomodidad, fondo de salón de eventos en preparación. Formato retrato. **EVITAR:** apariencia de sospechosa, texto legible en la tablet, marcas reales.

(`photos/patricio-nunez.png` — Patricio Núñez)
**PROMPT:** Retrato diegético de un hombre de 50 años, aspecto meticuloso, chaqueta formal, calculadora de bolsillo en la mano, expresión orgullosa y defensiva, fondo de una oficina de contabilidad con archivadores. Formato retrato. **EVITAR:** villano obvio, dinero o sobres visibles, texto legible.

(`photos/ramiro-casals.png` — Ramiro Casals)
**PROMPT:** Retrato diegético de un hombre de 48 años, chaqueta de trabajo con un logo genérico de proveedor de materiales, carpeta de pedidos bajo el brazo, expresión comercial, afable y evasiva, fondo de almacén con palés de materiales. Formato retrato. **EVITAR:** villano obvio, logos de marcas reales, texto legible en la chaqueta.

(`photos/camilo-serrano.png` — Camilo Serrano)
**PROMPT:** Retrato diegético de un hombre de 38 años, ropa casual de trabajador social, mochila con carpetas de los voluntarios, expresión cercana y algo incómoda, fondo de un despacho pequeño con carpetas y una pizarra de turnos sin texto legible. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, texto legible.

(`photos/dora-quintero.png` — Dora Quintero)
**PROMPT:** Retrato diegético de una mujer de 62 años, uniforme sencillo de conserjería, manojo de llaves al cinto, expresión serena, práctica y observadora, fondo de la recepción de una sede con un mostrador y una escalera. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/esteban-vidal.png` — Esteban Vidal)
**PROMPT:** Retrato diegético de un hombre de 41 años, traje casual de relaciones públicas, tablet con una lista de donantes en la mano (pantalla sin texto legible), expresión sociable y algo agobiada, fondo de salón de eventos con mesas montadas. Formato retrato. **EVITAR:** apariencia de sospechoso, texto legible, marcas reales.

(`photos/noelia-camacho.png` — Noelia Camacho)
**PROMPT:** Retrato diegético de una mujer de 29 años, ropa de obra, tablilla con hojas de registro de turnos en la mano (sin texto legible), expresión práctica, ordenada y directa, fondo de una vivienda en renovación con andamio. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, texto legible.

(`photos/registro-horas-obra.png` — registro de horas y control de obra)
**PROMPT:** Imagen diegética tipo retrato funcional de una tablilla de obra con hojas de firmas de entrada y salida junto a una tablet con una hoja de cálculo sobria de control de horas, sobre una mesa plegable en una obra, usada como avatar de la fuente de registro. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, resaltados, texto legible.

(`photos/contabilidad-pagos.png` — contabilidad / registro de pagos)
**PROMPT:** Imagen diegética tipo retrato funcional de una pantalla de software contable sobrio, con un libro de pagos a proveedores y una calculadora sobre un escritorio de oficina, usada como avatar de la fuente contable. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, dinero visible, estética futurista, texto legible.

## 3. Evidencias visuales

(`gallery/v01-calendario-de-los-24.png` — V01, calendario; entrega a T+1)
**PROMPT:** Fotografía diegética de un calendario de Adviento comunitario de gran formato, tipo tablero físico con veinticuatro casillas numeradas del 1 al 24, cada una con un espacio para un nombre y una pequeña ilustración o icono de una tarea de obra (brocha, martillo, caja de herramientas), en un diseño cálido y artesanal, expuesto en la sede de una asociación comunitaria con luces navideñas suaves. Dejar vacía la zona de texto de cada casilla y la de números. Personajes: ninguno. Luz natural combinada con luces navideñas. Formato vertical. **EVITAR:** veinticuatro retratos individuales, iconografía de alerta, texto generado al azar.
**TEXTO A COMPONER:** título *Calendario de los 24 — Fundación Calles Cálidas*. Una casilla por nombre, **solo nombre completo, sin rol ni horas**, en este orden (orden alfabético por nombre de pila, no revela quién tiene discrepancia): **1 Agustín Moura · 2 Antonia León · 3 Beatriz Salcedo · 4 Camilo Serrano · 5 Carla Viana · 6 Celia Franco · 7 Claudia Reinoso · 8 Diego Lerma · 9 Felipe Mendoza · 10 Gabriela Soto · 11 Hernán Pacheco · 12 Ignacio Duarte · 13 Joaquín Reyes · 14 Lucía Ibarra · 15 Marisol Ferreira · 16 Martín Oyarzún · 17 Milagros Vega · 18 Nicolás Behar · 19 Pilar Cuevas · 20 Rubén Paredes · 21 Sergio Botero · 22 Teresa Alcántara · 23 Tomás Iriarte · 24 Yolanda Prat.**

(`gallery/v10-oficina-administracion.png` — V10, oficina de administración; entrega a T+1)
**PROMPT:** Fotografía diegética funcional de una oficina de administración pequeña y ordinaria, con un escritorio, una estantería de archivadores cuyo borde se ve, y documentos dispersos de forma leve pero no dramática, sin ningún elemento amenazante ni señal de violencia explícita, ni cuerpo; incluir de forma discreta y natural, en el suelo junto al escritorio, una credencial de voluntaria con cordón, como un objeto más del entorno, no resaltada. Fotografía de interior, luz artificial de oficina. Formato horizontal. **EVITAR:** señales de forcejeo evidentes, elementos amenazantes, indicios de violencia, sangre, personas, resaltar la credencial de forma artificial.
**TEXTO A COMPONER:** en la credencial, pequeño y sin resaltar: **VOLUNTARIA · M. FERREIRA** con el logo genérico de la fundación. La credencial debe ser descubrible pero no obvia; no se compone ningún otro texto.

(`gallery/v07-tarjetas-reconocimiento.png` — V07, tarjetas; entrega a T+12)
**PROMPT:** Fotografía diegética de un conjunto de seis tarjetas de reconocimiento navideño extendidas sobre una mesa, cada una con un nombre, un rol y una breve frase de agradecimiento, en un diseño cálido y artesanal coherente con el calendario de V01; otras tarjetas fuera de cuadro. Fotografía de mesa, luz cálida de interior con luces navideñas. Dejar vacío el texto. Formato horizontal. **EVITAR:** representar las veinticuatro tarjetas, iconografía de alerta.
**TEXTO A COMPONER:** seis tarjetas, cada una con nombre, rol declarado y la frase *Gracias por tu entrega.*: **Marisol Ferreira — Jefa de cuadrilla · Antonia León — Jefa de acabados · Martín Oyarzún — Jefe de cuadrilla · Lucía Ibarra — Ayudante de obra · Celia Franco — Logística · Beatriz Salcedo — Acabados.**

(`gallery/f1-extracto-hoja-horas.png` … `f6-extracto-hoja-horas.png` — F1 a F6, extractos privados; entrega a T+12, uno por jugador)
**PROMPT (los seis, misma maqueta):** Captura de pantalla diegética de un recorte de una hoja de cálculo sencilla de certificación de horas de voluntariado, con tres columnas y exactamente cuatro filas de datos, interfaz sobria, fondo blanco, encabezado de la fundación; es un recorte, con la barra de filas visible para sugerir que hay más. Dejar vacías las celdas de texto. Ninguna fila resaltada. Formato horizontal. **EVITAR:** resaltados artificiales, iconografía de alarma, columnas de aprobación u observaciones, más de cuatro filas.
**TEXTO A COMPONER:** cabecera *Hoja de certificación de horas — voluntariado de obra — Extracto N de 6* (N = 1 a 6). Columnas *Nombre · Rol certificado · Horas certificadas*. Filas, exactamente las del reparto de la sección "La tabla de los 24":
- F1 (`f1-extracto-hoja-horas.png`): Marisol Ferreira · Jefa de cuadrilla · 86 / Lucía Ibarra · Ayudante de obra · 38 / Pilar Cuevas · Acabados · 36 / Teresa Alcántara · Ayudante de obra · 30
- F2 (`f2-extracto-hoja-horas.png`): Antonia León · Jefa de acabados · 60 / Martín Oyarzún · Jefe de cuadrilla · 54 / Nicolás Behar · Ayudante de obra · 31 / Ignacio Duarte · Logística · 26
- F3 (`f3-extracto-hoja-horas.png`): Rubén Paredes · Ayudante de obra · 48 / Beatriz Salcedo · Acabados · 40 / Yolanda Prat · Logística · 27 / Gabriela Soto · Ayudante de obra · 39
- F4 (`f4-extracto-hoja-horas.png`): Camilo Serrano · Ayudante de obra · 52 / Joaquín Reyes · Ayudante de obra · 33 / Diego Lerma · Ayudante de obra · 42 / Hernán Pacheco · Acabados · 33
- F5 (`f5-extracto-hoja-horas.png`): Celia Franco · Logística · 29 / Carla Viana · Acabados · 35 / Milagros Vega · Ayudante de obra · 28 / Claudia Reinoso · Ayudante de obra · 37
- F6 (`f6-extracto-hoja-horas.png`): Agustín Moura · Ayudante de obra · 45 / Sergio Botero · Jefe de cuadrilla · 50 / Tomás Iriarte · Logística · 24 / Felipe Mendoza · Acabados · 31

(`gallery/v02-tabla-jornadas.png` — V02, copia pública anonimizada; entrega a T+25)
**PROMPT:** Captura de pantalla diegética de una hoja de cálculo sencilla de control de horas, con cuatro columnas (número, nombre abreviado, rol declarado, horas declaradas) y veinticuatro filas, interfaz funcional y sobria, mismo diseño que los extractos F1–F6 pero completo. Dejar vacías las celdas de texto. Ninguna fila resaltada. Formato vertical alto. **EVITAR:** resaltados artificiales, iconografía de alarma, columnas de aprobación u observaciones, horas verificadas.
**TEXTO A COMPONER:** título *Tabla de jornadas — copia de consulta*. Columnas *N.º · Nombre · Rol declarado · Horas declaradas*. Filas: **1 · A. Moura · Ayudante de obra · 45 / 2 · A. León · Jefa de acabados · 60 / 3 · B. Salcedo · Acabados · 40 / 4 · C. Serrano · Ayudante de obra · 52 / 5 · C. Viana · Acabados · 35 / 6 · C. Franco · Logística · 29 / 7 · C. Reinoso · Ayudante de obra · 37 / 8 · D. Lerma · Ayudante de obra · 42 / 9 · F. Mendoza · Acabados · 31 / 10 · G. Soto · Ayudante de obra · 39 / 11 · H. Pacheco · Acabados · 33 / 12 · I. Duarte · Logística · 26 / 13 · J. Reyes · Ayudante de obra · 33 / 14 · L. Ibarra · Ayudante de obra · 38 / 15 · M. Ferreira · Jefa de cuadrilla · 86 / 16 · M. Oyarzún · Jefe de cuadrilla · 54 / 17 · M. Vega · Ayudante de obra · 28 / 18 · N. Behar · Ayudante de obra · 31 / 19 · P. Cuevas · Acabados · 36 / 20 · R. Paredes · Ayudante de obra · 48 / 21 · S. Botero · Jefe de cuadrilla · 50 / 22 · T. Alcántara · Ayudante de obra · 30 / 23 · T. Iriarte · Logística · 24 / 24 · Y. Prat · Logística · 27.** Pie en letra pequeña: *Total de horas declaradas: 954.*

(`gallery/v08-mapa-intervenciones.png` — V08, mapa; entrega a T+25)
**PROMPT:** Diagrama vectorial simple tipo mapa esquemático de un barrio, señalando la ubicación de las dos viviendas en renovación (R1 y R2), el almacén de materiales, la sede de la fundación y el salón de eventos, con iconos sobrios, líneas de desplazamiento entre ellos y una leyenda clara. Dejar vacías las etiquetas. Formato horizontal. **EVITAR:** nombres de calles o barrios reales, iconografía de alerta, coordenadas.
**TEXTO A COMPONER:** nodos: *Sede de la fundación · Almacén de materiales · Vivienda R1 — familia Correa · Vivienda R2 — familia Giraldo · Salón de Eventos (gala)*. Tiempos en las líneas: **Sede ↔ Almacén: 15 min en carro · Sede ↔ Salón de Eventos: 10 min a pie · Sede ↔ R1: 20 min en carro · Sede ↔ R2: 25 min en carro · Almacén ↔ Salón de Eventos: 20 min en carro.** Título *Campaña — mapa de intervenciones*.

(`gallery/v03-obra-r1-correa.png` — V03, obra R1; entrega a T+40)
**PROMPT:** Fotografía diegética documental de una vivienda en renovación comunitaria, con seis voluntarios genéricos sin rasgos reconocibles trabajando en tareas visibles (pintura, carpintería básica, carga con carretilla), en horario diurno; al fondo, bien visible, una pizarra de turno de la cuadrilla con la zona de texto vacía. Luz natural cálida de exterior. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, más o menos de seis personas, marcas reales.
**TEXTO A COMPONER:** en la pizarra: *R1 · Vivienda familia Correa · Semana 3* / *Jefe de cuadrilla: M. Oyarzún* / *Ayudantes: L. Ibarra · R. Paredes · J. Reyes · M. Ferreira* / *Acabados: P. Cuevas*. Pie de foto: *Jornada de la semana 3 — 6 voluntarios en obra.*

(`gallery/v04-obra-r2-giraldo.png` — V04, obra R2; entrega a T+40)
**PROMPT:** Fotografía diegética documental de una segunda vivienda en renovación comunitaria, con cinco voluntarios genéricos sin rasgos reconocibles, mayormente en tareas de acabados (lijado, pintura fina), con una pizarra de turno visible al fondo y la zona de texto vacía; luz natural de día con un tono ligeramente más frío que V03. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, más o menos de cinco personas, marcas reales; misma vestimenta de obra que V03.
**TEXTO A COMPONER:** en la pizarra: *R2 · Vivienda familia Giraldo · Semana 5* / *Jefe de cuadrilla: S. Botero* / *Ayudante: D. Lerma* / *Acabados: A. León · B. Salcedo · C. Viana*. Pie de foto: *Jornada de la semana 5 — 5 voluntarios en obra.*

(`gallery/v05-acta-revision-interna.png` — V05, acta; entrega a T+55)
**PROMPT:** Documento diegético tipo memorando interno breve, con membrete sobrio de una fundación, impreso, con una lista corta de cuatro puntos y una firma manuscrita al pie, en tono profesional y neutro. Dejar vacío el texto. Formato vertical. **EVITAR:** iconografía de alerta, lenguaje acusatorio hacia una sola persona, sellos de "confidencial" dramáticos.
**TEXTO A COMPONER:** membrete *Fundación Calles Cálidas — Control interno*. Título *Acta de revisión interna (preliminar)*. *Asunto: cotejo entre las horas y roles certificados para la subvención (Calendario de los 24) y lo registrado en obra.* *Revisados 24 nombres; 20 coinciden. Cuatro presentan diferencia:* **1. M. Ferreira — certificado: jefa de cuadrilla, 86 h · registrado: ayudante de obra, 41 h. 2. A. León — certificado: jefa de acabados, 60 h · registrado: acabados, 47 h. 3. R. Paredes — certificado: ayudante de obra, 48 h · registrado: ayudante de obra, 36 h. 4. C. Serrano — certificado: ayudante de obra, 52 h · registrado: ayudante de obra, 44 h.** *Observación: la diferencia no se concentra en una sola persona.* *Pendiente: determinar quién cargó y aprobó cada entrada en el módulo de certificación y contrastar con los pagos a proveedores.* *Para presentar a la junta de esta noche.* Firma manuscrita: *R. Beltrán — Auditoría interna.*

(`gallery/v06-registro-herramientas.png` — V06, registro de herramientas; entrega a T+70)
**PROMPT:** Captura diegética de un cuaderno o interfaz sencilla de préstamo de herramientas de obra, con columnas de semana, día, nombre, herramienta, hora de retiro y hora de devolución, funcional y sobria, presentada como extracto. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales.
**TEXTO A COMPONER:** título *Registro de herramientas — extracto*. Columnas *Semana · Día · Nombre · Herramienta · Retiro · Devolución*. Filas: **3 · miércoles · M. Oyarzún · Nivel y escuadra · 08:00 · 12:30 / 3 · miércoles · L. Ibarra · Taladro · 08:05 · 12:10 / 3 · miércoles · R. Paredes · Rodillo y bandeja · 08:10 · 12:20 / 3 · miércoles · M. Ferreira · Carretilla · 08:20 · 12:15 / 5 · sábado · S. Botero · Nivel y escuadra · 08:00 · 12:35 / 5 · sábado · D. Lerma · Pala · 08:10 · 12:20 / 5 · sábado · C. Viana · Espátulas · 08:12 · 12:30 / 5 · sábado · A. León · Lijadora · 08:15 · 12:25.** (En la semana 5 no debe aparecer M. Ferreira.)

(`gallery/v11-registro-control-acceso.png` — V11, control de acceso; entrega a T+70)
**PROMPT:** Captura diegética de un sistema simple de control de accesos por tarjeta, con dos bloques de lista de lecturas (nombre, hora, tipo de lectura) y una nota al pie, en una interfaz funcional y sobria. Dejar vacías las celdas. Formato vertical. **EVITAR:** iconografía de alarma, imágenes de cámaras de video, resaltados.
**TEXTO A COMPONER:** título *Control de acceso — Sede Calles Cálidas*. Bloque 1: *Puerta P2 — Planta de administración (solo entradas)*: **08:52 · R. Beltrán · ENTRADA / 09:30 · F. Ocampo · ENTRADA / 10:20 · V. Ríos · ENTRADA / 12:40 · H. Benavídez · ENTRADA / 13:48 · F. Ocampo · ENTRADA / 14:10 · R. Beltrán · ENTRADA / 16:34 · R. Beltrán · ENTRADA / 18:38 · D. Quintero · ENTRADA.** Nota al pie del bloque 1: *La salida de esta planta se abre con pulsador y no queda registrada.* Bloque 2: *Puerta C1 — Contabilidad (planta baja; entradas y salidas)*: **08:30 · P. Núñez · ENTRADA / 13:10 · P. Núñez · SALIDA / 14:02 · P. Núñez · ENTRADA / 18:47 · P. Núñez · SALIDA.**

(`gallery/v09-planilla-fondos.png` — V09, planilla de fondos; entrega a T+85)
**PROMPT:** Captura de pantalla diegética de una sencilla planilla de financiación de un convenio de voluntariado, con pocas líneas, interfaz funcional y sobria, sin gráficos. Dejar vacías las celdas. Formato horizontal. **EVITAR:** cifras reales de subvenciones públicas, normativas reales, iconografía de alerta, resaltados.
**TEXTO A COMPONER:** título *Planilla de fondos — cierre de campaña*. Líneas: **Valor reconocido por hora certificada de voluntariado de obra: US$ 10 / Incremento por rol de coordinación (jefatura de cuadrilla o de acabados): 25 % sobre el valor hora / Subvención municipal recibida por la campaña: aprox. US$ 13,000.** Ejemplo: *10 h de ayudante = US$ 100 · 10 h de jefatura = US$ 125.*

## 4. Audio

(`audio/a1-nota-voz-rosario.mp3` — A1, nota de voz de Rosario; entrega a T+55)
- **Scene:** nota de voz grabada en una oficina silenciosa, sin música; al final se oye, muy leve, ruido de papeles.
- **Context:** Rosario, auditora interna de 58 años, graba una nota para sí misma o para un colega de confianza: voz tranquila, profesional, con un ligero nerviosismo contenido y una duda genuina al final. Debe sonar como alguien a punto de hacer lo correcto, no como alguien que ya tiene todas las respuestas.
- **Speaker:** Speaker 1 - Rosario Beltrán.
- **Voz (Gemini):** Gacrux.
- **Duración:** 20–25 segundos.
- **Script:** el de `audio_script` en `case.php` (texto idéntico):

> Terminé de cruzar los números. No es solo un nombre, son varios. El de Marisol es el que más destaca, con diferencia, pero no creo que ella supiera nada de esto. Voy a hablar con ella esta tarde, con calma, antes de llevarlo a la junta esta noche. Si hay algo que no cuadra del todo con Hugo, prefiero confirmarlo antes de decir nada en público.
>
> Espero estar equivocada. Pero no creo que lo esté.

**EVITAR:** música dramática, tono acusatorio hacia una persona concreta, cualquier detalle que la evidencia no permita deducir.
Para exportar los guiones listos para el estudio: `php artisan immersion:export-audio-scripts los-veinticuatro-nombres`.

(A2 — confesión de Marisol) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (voz Leda) para incluir las preguntas que esa mesa le hizo. Dirección de voz: empieza quebrada, con pánico contenido, y se estabiliza hacia un reconocimiento genuino y doloroso; alguien que reaccionó por miedo, no con frialdad. Sin música, sin sobreactuar, sin contenido violento explícito.
