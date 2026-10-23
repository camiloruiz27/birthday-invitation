# Prompts de assets — El Espejo de las Tres Caras

Total: **1 portada + 9 retratos + 9 evidencias visuales + 0 audios**.

- Los 9 retratos incluyen a Ignacio Bermúdez, que es la víctima (`victim.photo`) y a la vez testigo del
  roster: **es un único archivo** (`ignacio-bermudez.png`) usado en las dos entradas.
- Las 9 evidencias son: V01, el recorte privado de V02, V02, V03, V04, V05, V06, V07 y C1.
- Este caso **no tiene audio en la línea de tiempo**: la confesión de Dalila (A1 del diseño) es
  `solution.confession_script` y se genera por partida. Por eso no hay carpeta `audio/`.
- Hay cuatro códigos de evidencia **sin imagen** (texto dentro del sobre): DN (declaración de Norah), DP
  (declaración de Perla) y DR, DC, DE (expedientes). No hay que producir nada para ellos.

Todo se guarda en `public/immersion/el-espejo-de-las-tres-caras/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos |
| `gallery/` | las 9 evidencias visuales |

## ATENCIÓN: el giro óptico (leer antes de producir V01, V02, V03, V04, V07)

El caso entero depende de que **V02, V03 y V04 sean tres vistas de UNA misma persona** y de que **la
geometría del laberinto sea idéntica en todas**. El propio diseño exige que estas piezas **se compongan y
verifiquen a mano; no confíes en la generación libre de espejos de un generador de imágenes**.

Método recomendado: construir un bloqueo 3D muy simple del laberinto (SketchUp, Blender o similar) con
las coordenadas de abajo, colocar a una figura con chaleco azul en su posición real y renderizar las tres
vistas (A, B y C). Usar esos renders como base y pasarlos por el generador solo para dar textura, luz de
feria y acabado fotográfico, o componer a mano sobre ellos. Antes de aprobar, verificar con V01 que
cada vista sale de su punto de observación y atraviesa el panel que dice la tabla.

**Qué panel es "directo" y cuál "doble reflejo" (esto es lo que dice V07):**

| Panel | Sector | Tipo |
|---|---|---|
| P1, P2, P3, P4 | Corredor Central | **Reflexión directa**: la imagen está donde parece estar |
| P5, P6, P7, P8 | Octágono Central | **Reflexión doble**: refleja la imagen que ya reflejan P1–P4; duplica la figura en otra ubicación y con otra dirección aparente |

| Vista | Punto de observación | Panel que atraviesa | Naturaleza |
|---|---|---|---|
| V02 | Acceso A | **P2** | **Directa**: muestra la posición real |
| V03 | Acceso B | **P5** | **Doble reflejo**: la misma figura duplicada |
| V04 | Control C (cámara fija) | **P7** | **Doble reflejo**: la misma figura duplicada, por otro panel |

Reglas de continuidad de las tres vistas:

1. **Una sola figura en cada imagen**. Nunca dos figuras distintas en el mismo cuadro.
2. **La misma figura en las tres**: mismo chaleco técnico azul con banda reflectante, pantalón oscuro,
   zapatos oscuros, cabello recogido en un moño bajo, complexión media. Rostro **no identificable**
   (de espaldas, desenfocado o a distancia) en las tres.
3. **Cada panel lleva una placa numerada física en su marco**, legible en la imagen: **P2** en V02, **P5**
   en V03, **P7** en V04. Esa placa es lo que permite a la mesa cruzar cada vista con V07 por
   observación directa. La placa es texto crítico: compónla fuera del generador.
4. **Ninguna imagen, rótulo ni pie de foto debe decir que son la misma persona, ni cuál vista es directa**.
   Eso lo da V07 (y Norah). Tampoco debe haber flechas ni aclaraciones.
5. La geometría (corredor, octágono, accesos, posición de paneles) es la de V01 en todas las piezas.
6. Los reflejos deben ser **geométricamente posibles**: sin reflejos surrealistas ni fantásticos.

## Cuadrícula fija de la geometría (V01, V02, V03, V04, V05, V07)

Unidades en metros. x crece hacia el este, y hacia el norte. Todas las piezas deben respetar esta tabla.

| Elemento | Posición |
|---|---|
| **Acceso A** | extremo oeste del Corredor Central, abertura en (0, 10) |
| **Corredor Central** | rectángulo x 2→16, y 9→11 (2 m de ancho), recto, de oeste a este |
| **Octágono Central** | centro (22, 10), apotema 6; ocupa x 16→28, y 4→16; el corredor entra por su cara oeste en (16, 10) |
| **Acceso B** | lado norte del octágono: pasillo x 21→23, y 16→18, abertura en (22, 18) |
| **Control C (cabina)** | lado sur del octágono: cabina en (22, 2), ventana y cámara fija en (22, 3.5) |
| **Puerta de personal** | pared sur del corredor, en (6, 9): a un tercio del corredor desde el Acceso A |
| **P1** | pared norte del corredor, (4, 11) |
| **P2** | pared norte del corredor, (8, 11) |
| **P3** | pared sur del corredor, (12, 9) |
| **P4** | pared norte del corredor, (14.5, 11) |
| **P5** | cara noroeste del octágono, (18, 14) |
| **P6** | cara noreste del octágono, (26, 14) |
| **P7** | cara sureste del octágono, (26, 6) |
| **P8** | cara suroeste del octágono, (18, 6) |
| **Línea de visión A** | de (0, 10) hacia P2 |
| **Línea de visión B** | de (22, 18) hacia P5 |
| **Línea de visión C** | de (22, 3.5) hacia P7 |
| **Figura real (momento de V02 y V04)** | en el corredor hacia (11, 10), caminando hacia el este (hacia el octágono) |
| **Posición aparente en V03** | en el octágono, hacia (20, 13), caminando hacia el Acceso B (norte-noroeste) |
| **Posición aparente en V04** | en el octágono, hacia (24, 7), caminando hacia la cabina (sur-suroeste) |

Las posiciones aparentes son **una convención del caso**, no un cálculo óptico: lo importante es que
sean siempre las mismas en cada pieza y distintas entre sí.

## Orden de producción recomendado

1. **Geometría y bloqueo 3D** (arriba). Sin esto no se produce V02/V03/V04.
2. **Biblia visual** (abajo): fíjala y pégala al inicio de cada prompt de retrato.
3. **Portada** (para empezar a vender y a probar el catálogo).
4. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
5. **Evidencias**, en este orden: V01 (plano), V07 (ficha), V02, recorte de V02, V03, V04, V05, V06, C1.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Expansión | 10 | (pista privada, a un jugador) recorte de V02 |
| Desarrollo | 20 | V02 (copia para toda la mesa), V03, V07 |
| Complicación | 32 | V04 |
| Giro | 45 | V05; DN (declaración de Norah, solo texto) |
| Cierre | 55 | V06, C1; DP (declaración de Perla, solo texto) |
| Expedientes | 56 | DR, DC, DE (solo texto) |
| Cierre final | 60 | (solo texto: coartadas cruzadas) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y aquí el dato exacto ES la prueba
(la placa del panel, la hora del fotograma, la clasificación de la ficha). Para V01, V02, V03, V04, V05,
V06, V07 y C1: genera la imagen con la zona de texto **vacía o con texto de relleno ilegible**, y compón
después el texto exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica
cada hora y cada número de panel contra `case.php` antes de guardar.

**Cronología maestra (todas las horas del caso salen de aquí):** el turno de Dalila termina a las 21:30 ·
cierre al público 22:00 (el Corredor Central pasa a ser solo de personal) · Ignacio empieza su ronda de
cierre a las 22:00 (anotado en el cuaderno a las 21:41, al entrar a prepararse) · Dalila entra por la
puerta de personal a las **22:08** · fotograma de Control C **22:14:37** · empujón hacia las 22:16 ·
Dalila sale hacia las 22:18 · Ignacio avisa por radio hacia las 22:19. Mensaje de Dalila: "Hoy · 17:42".
Coartadas: Rogelio 21:40–22:35 (oficina) · Carolina 21:45–22:45 (reunión) · Esteban 21:35 y 22:40 (taller).

## Biblia visual de continuidad (pegar en los retratos)

- **Dalila Orozco**, 37: complexión media, cabello oscuro recogido en un moño bajo, polo oscuro de personal con credencial colgada; mirada profesional y segura. **NO mostrar el chaleco técnico azul en el retrato**: si el retrato lo mostrara, cualquier jugador la identificaría al compararlo con V02 desde el minuto 10 (decisión del dueño: se recomienda mantenerlo así; la figura de V02/V03/V04 sí lleva el chaleco).
- **Ignacio Bermúdez**, 34: camisa roja de operador con gorra, expresión serena y algo cansada; muñeca derecha con una venda discreta (el retrato sirve a la vez como víctima y como testigo).
- **Rogelio Fuenmayor**, 50: ropa formal de oficina (camisa y chaqueta), expresión ejecutiva y calmada, cabello canoso corto.
- **Carolina Sotillo**, 39: **chaleco naranja de supervisión de área** sobre camisa clara, expresión profesional y ambiciosa.
- **Esteban Vargas**, 44: **overol de mantenimiento gris** con manchas leves de trabajo, manos de técnico, expresión práctica.
- **Perla Issasi**, 28: vestuario temático de guía del laberinto (capa o chaleco con motivos de Halloween sobrios), expresión amigable y algo nerviosa.
- **Dámaris Lobo**, 30: ropa casual de visitante de feria, expresión cordial e impresionada.
- **Ulises Franco**, 42: ropa casual de visitante de feria, expresión tranquila.
- **Norah Esquivel**, 45: uniforme de vigilancia con credencial, expresión metódica y seca.
- **El chaleco técnico azul** (solo en V02, V03, V04): azul intenso con banda reflectante gris, sin logos legibles. Es el mismo en las tres vistas.
- **El laberinto:** corredores y octágono de paneles de espejo con marcos oscuros, luz ámbar de feria nocturna, decoración de Halloween sobria (calabazas, telarañas discretas). Sin reflejos surrealistas ni estética fantástica ni de terror.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos, sin logos de
marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada vertical de póster de catálogo para un misterio de feria de Halloween: el interior de un laberinto de espejos de marcos oscuros bajo una luz ámbar cálida, un corredor que se pierde entre paneles reflectantes, con farolillos de calabaza apagados en el suelo; en tres paneles distintos se ven tres siluetas oscuras, de pie y en poses diferentes, sin ningún rasgo ni color identificable. Sensación de percepción engañosa, suspense sereno, sin sangre ni violencia. Formato vertical 2:3, estilo realista premium, sin texto integrado. **EVITAR:** rostros visibles, chaleco azul, texto o logos, sangre, estética de terror, payasos, iconografía policial.

## 2. Retratos

(`photos/dalila-orozco.png` — Dalila Orozco)
**PROMPT:** Retrato diegético tipo foto de credencial de una mujer de 37 años, complexión media, cabello oscuro recogido en un moño bajo, polo oscuro de personal con una credencial colgada (sin texto legible), expresión profesional, segura y un poco reservada, fondo neutro de zona técnica de una feria. Formato retrato. **EVITAR:** chaleco azul de cualquier tipo, apariencia de culpable, mirada siniestra, iluminación dramática, gesto agresivo.

(`photos/rogelio-fuenmayor.png` — Rogelio Fuenmayor)
**PROMPT:** Retrato diegético de un hombre de 50 años, cabello canoso corto, camisa y chaqueta de oficina, expresión ejecutiva, calmada y algo defensiva, fondo de una oficina de administración con papeles y un monitor. Formato retrato. **EVITAR:** villano obvio, dinero o documentos legibles, gesto amenazante.

(`photos/carolina-sotillo.png` — Carolina Sotillo)
**PROMPT:** Retrato diegético de una mujer de 39 años, chaleco naranja de supervisión de área sobre una camisa clara, expresión profesional, ambiciosa y atenta a cómo se la ve, fondo de un pabellón de diseño con maquetas y planos sin texto legible. Formato retrato. **EVITAR:** chaleco azul, apariencia de culpable, texto legible en los planos.

(`photos/esteban-vargas.png` — Esteban Vargas)
**PROMPT:** Retrato diegético de un hombre de 44 años, overol de mantenimiento gris con manchas leves de trabajo, expresión práctica y algo defensiva, fondo de un taller con herramientas y un marco de espejo apoyado en la pared. Formato retrato. **EVITAR:** chaleco azul, villano obvio, texto legible, herramientas usadas como amenaza.

(`photos/perla-issasi.png` — Perla Issasi)
**PROMPT:** Retrato diegético de una mujer de 28 años, vestuario temático de guía de un laberinto de Halloween (capa o chaleco con motivos sobrios), expresión amigable y algo nerviosa, fondo de la puerta discreta de personal de una feria con luz tenue. Formato retrato. **EVITAR:** apariencia de culpable, disfraz de terror, chaleco azul, texto legible.

(`photos/ignacio-bermudez.png` — Ignacio Bermúdez, víctima y testigo; mismo archivo para las dos entradas)
**PROMPT:** Retrato diegético de un hombre de 34 años, camisa roja de operador de atracción con una gorra, expresión serena y algo cansada, la muñeca derecha con una venda discreta, fondo de la entrada de un laberinto de espejos con luz ámbar. Formato retrato. **EVITAR:** apariencia de víctima dramática, sangre, rastros de violencia, gesto de dolor exagerado, texto legible.

(`photos/damaris-lobo.png` — Dámaris Lobo)
**PROMPT:** Retrato diegético de una mujer de 30 años, ropa casual de visitante de feria, expresión cordial, observadora e impresionada, fondo de la zona de salida de un laberinto de espejos con luz ámbar. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/ulises-franco.png` — Ulises Franco)
**PROMPT:** Retrato diegético de un hombre de 42 años, ropa casual de visitante de feria, expresión tranquila y algo desconcertada, fondo de la zona de salida de un laberinto de espejos con luz ámbar. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/norah-esquivel.png` — Norah Esquivel)
**PROMPT:** Retrato diegético de una mujer de 45 años, uniforme de vigilancia con credencial (sin texto legible), expresión metódica, seca y atenta, fondo de una cabina de control con monitores sencillos que muestran vistas de pasillos de espejos. Formato retrato. **EVITAR:** apariencia de sospechosa, alarmas rojas, texto legible en los monitores.

## 3. Evidencias visuales

(`gallery/v01-planta-laberinto.png` — V01, plano del laberinto)
**PROMPT:** Crear un diagrama vectorial diegético para un juego de investigación: un plano preciso y sencillo del laberinto de espejos de una feria, visto desde arriba, en estilo de mapa turístico institucional limpio. Debe mostrar, con la geometría exacta de la cuadrícula del caso, el Corredor Central, el Octágono Central, los Accesos A y B, la cabina de Control C, la puerta de personal, ocho paneles de espejo numerados P1 a P8 marcados como segmentos sobre los muros, tres puntos de observación (A, B y C) y tres líneas finas de puntos de cada punto de observación hacia un panel. Dejar vacías las etiquetas. Formato horizontal. **EVITAR:** iconografía de alarma, estética de póster de terror, flechas explicativas, colores que distingan paneles directos de dobles, cualquier texto que diga qué panel es de qué tipo, geometría distinta a la cuadrícula del caso.
**TEXTO A COMPONER:** título *Laberinto de Espejos — Plano*. Etiquetas: **Acceso A · Acceso B · Control C (cabina) · Corredor Central · Octágono Central · Puerta de personal**. Paneles **P1, P2, P3, P4** en el corredor y **P5, P6, P7, P8** en el octágono, en las posiciones de la tabla. Puntos de observación **A**, **B**, **C** con sus líneas de puntos hacia **P2**, **P5** y **P7** respectivamente. Todos los paneles se dibujan igual (mismo trazo y color).

(`gallery/v02-recorte-vista-acceso-a.png` — recorte privado de V02, entrega privada a T+10)
**PROMPT:** Recorte estrecho de la fotografía V02: solo la figura y un poco de suelo y de panel a su alrededor, sin el corredor completo, sin la placa del panel, sin referencias de geometría. Mismo encuadre, luz y figura que V02, simplemente recortado. Formato vertical. **EVITAR:** rostro, la placa **P2**, el marco completo del panel, texto. (Se obtiene recortando V02 ya aprobada; no se genera aparte.)

(`gallery/v02-vista-acceso-a.png` — V02, vista desde el Acceso A; directa)
**PROMPT:** Fotografía diegética de ambiente de feria, tomada desde el Acceso A mirando por el Corredor Central de un laberinto de espejos: una figura con un chaleco técnico azul con banda reflectante camina de espaldas, alejándose, hacia el octágono del fondo, en una reflexión directa y clara (la imagen está donde parece estar). Cuadro vertical según la cuadrícula del caso: la figura hacia (11, 10), el panel **P2** en la pared norte a su lado, con su placa en el marco. Luz ámbar cálida de atracción nocturna. Una sola figura, sin rostro identificable. Formato vertical. **EVITAR:** rostro identificable, dos figuras, iconografía de alarma, reflejos surrealistas o imposibles, flechas, captions. Misma geometría que V01 y mismo chaleco que V03 y V04.
**TEXTO A COMPONER:** la placa del panel visible: **P2** (legible, discreta, en el marco).

(`gallery/v03-vista-acceso-b.png` — V03, vista desde el Acceso B; doble reflejo)
**PROMPT:** Fotografía diegética de ambiente de feria tomada desde el Acceso B mirando hacia el interior del octágono central: en un panel de reflexión doble se ve a la misma figura con el mismo chaleco técnico azul con banda reflectante, aparentemente de pie en el octágono, hacia (20, 13), caminando hacia el Acceso B, en una ubicación y dirección distintas a las de V02. Una sola figura, sin rostro identificable (desenfocado). Luz ámbar cálida de feria nocturna. Formato vertical. **EVITAR:** rostro identificable, dos figuras en la misma imagen, iconografía de alarma, reflejos surrealistas o geométricamente imposibles, cualquier señal de que es un reflejo (sin captions ni flechas). Misma geometría que V01 y mismo chaleco que V02 y V04.
**TEXTO A COMPONER:** la placa del panel visible: **P5** (legible, discreta, en el marco).

(`gallery/v07-guia-superficies.png` — V07, ficha de superficies)
**PROMPT:** Lámina esquemática diegética para un juego de investigación: una ficha técnica de mantenimiento de una sola página, vertical, aspecto de ficha impresa sobria, con un diagrama simplificado del laberinto (el mismo de V01, con la misma geometría y los paneles P1 a P8 en sus posiciones) y una tabla debajo con una fila por panel. Dejar vacías las etiquetas del diagrama y las celdas de la tabla. **EVITAR:** óptica avanzada real, fórmulas técnicas, flechas de rayos, iconografía de alarma, mencionar personas o fotografías, resaltar paneles distintos con colores llamativos.
**TEXTO A COMPONER:** título *Laberinto de Espejos — Ficha de superficies (Taller de mantenimiento)*. Tabla *Panel · Sector · Tipo de reflexión · Nota*: **P1 · Corredor Central · Reflexión directa · La imagen está donde parece estar** / **P2 · Corredor Central · Reflexión directa · La imagen está donde parece estar** / **P3 · Corredor Central · Reflexión directa · La imagen está donde parece estar** / **P4 · Corredor Central · Reflexión directa · La imagen está donde parece estar** / **P5 · Octágono Central · Reflexión doble · Refleja la imagen que ya reflejan los paneles del corredor: puede mostrar la misma figura en otra ubicación y con otra dirección aparente** / **P6 · Octágono Central · Reflexión doble · (misma nota)** / **P7 · Octágono Central · Reflexión doble · (misma nota)** / **P8 · Octágono Central · Reflexión doble · (misma nota)**. En el diagrama, rotular cada panel con su número y con "directa" (P1–P4) o "doble" (P5–P8). Pie: *Revisión de paneles: trimestral.*

(`gallery/v04-fotograma-control-c.png` — V04, fotograma de Control C; doble reflejo)
**PROMPT:** Fotograma estático diegético de una cámara fija de vigilancia situada en la cabina de Control C, ángulo ligeramente elevado y calidad funcional de cámara institucional, mirando hacia el octágono: en un panel de reflexión doble se ve a la misma figura con el mismo chaleco técnico azul con banda reflectante, aparentemente en el octágono hacia (24, 7), caminando hacia la cabina, en una ubicación distinta a V02 y a V03. Una sola figura, sin rostro identificable. Interior funcional, luz moderada. Formato horizontal, aspecto de cámara de seguridad. **EVITAR:** rostro identificable, dos figuras, iconografía de alarma, reflejos geométricamente imposibles, captions. Misma geometría que V01 y mismo chaleco que V02 y V03.
**TEXTO A COMPONER:** rótulo en la esquina: *CAM C-1 · CONTROL C · 22:14:37*. La placa del panel visible: **P7** (legible, discreta, en el marco).

(`gallery/v05-senaletica-corredor.png` — V05, cartel de la entrada del Corredor Central)
**PROMPT:** Fotografía diegética de un cartel de señalización en la entrada del Corredor Central de un laberinto de espejos, decoración de temporada (calabazas sobrias, telarañas discretas) y, de forma discreta pero legible, una indicación de acceso restringido a personal después del cierre. Luz ámbar cálida de atracción nocturna, el corredor de espejos al fondo. Dejar vacío el texto del cartel. Formato vertical. **EVITAR:** iconografía de alarma exagerada, estética de póster de terror, figuras humanas, reflejos surrealistas.
**TEXTO A COMPONER:** cartel decorativo con la frase **«No pierdas el camino»** en grande y, en un recuadro más pequeño debajo: **«ACCESO RESTRINGIDO A PERSONAL DESPUÉS DEL CIERRE (22:00)»**.

(`gallery/v06-entrada-personal.png` — V06, puerta de personal y su cuaderno)
**PROMPT:** Fotografía diegética de la puerta de personal de la feria, discreta y funcional, con luz exterior tenue de feria nocturna, y sobre una mesa cercana un cuaderno de registro en papel abierto, con una tabla de tres columnas escrita a mano; sin cámaras ni sistemas digitales visibles. Dejar vacía la escritura del cuaderno. Formato horizontal. **EVITAR:** cámaras digitales, sistemas de registro digital, iconografía de alarma, figuras humanas, resaltar ninguna fila.
**TEXTO A COMPONER:** a mano, en el cuaderno: encabezado *Registro de personal — Puerta de personal*; columnas *Hora · Nombre · Motivo*; filas: **21:20 · N. Esquivel · Turno de cabina** / **21:41 · I. Bermúdez · Ronda de cierre** / **22:08 · D. O. ·** (celda de motivo vacía). Ninguna fila resaltada.

(`gallery/c1-captura-mensaje.png` — C1, captura de un mensaje)
**PROMPT:** Captura de pantalla diegética de una conversación breve de mensajería en un móvil, interfaz simple y genérica, tono informal, fondo claro. Dejar vacíos los textos. Formato vertical, aspecto de captura de pantalla. **EVITAR:** iconografía de alarma, menciones explícitas de agresión, logos de apps reales, fotos de perfil identificables, el nombre completo del contacto.
**TEXTO A COMPONER:** encabezado *Contacto*. Etiqueta de día **Hoy**. Mensaje enviado (burbuja a la derecha) **«Esta noche voy a hablar con Ignacio de una vez por todas sobre lo del ascenso.»** con la hora *17:42*. Respuesta (burbuja a la izquierda) **«Suerte. Cuéntame cómo te va.»** *17:44*.

## 4. Audio

Este caso **no tiene audio en la línea de tiempo**. No hay carpeta `audio/`.

(A1 — confesión de Dalila) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Kore). Dirección de voz: tensa al inicio, con un reconocimiento gradual y contenido, de la justificación profesional a la aceptación pausada; sin sobreactuar. Sin música manipuladora, sin descripciones gráficas de la lesión; un leve ambiente de feria nocturna de fondo. Duración: 15 a 20 segundos.
