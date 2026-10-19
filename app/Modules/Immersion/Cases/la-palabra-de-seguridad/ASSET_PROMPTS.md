# Prompts de assets — La Palabra de Seguridad

Total: **1 portada + 9 retratos + 9 evidencias visuales + 1 audio**. La confesión de Marcelo
(A2 del diseño) se genera por partida y no se graba: es `solution.confession_script`.

Todo se guarda en `public/immersion/la-palabra-de-seguridad/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos (Darío Lezcano es a la vez el herido del caso y un testigo: un solo retrato) |
| `gallery/` | las 9 evidencias visuales |
| `audio/` | `a1-radio-ivan.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Fija el mismo formato y tipografía de manual
   para V02 y V03: son la pareja que se compara en el caso.
5. **Audio** A1 (voz de Iván).
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Expansión | 12 | V02 (pista privada: la recibe un solo jugador) |
| Desarrollo | 24 | V03, V06 |
| Complicación | 36 | V04, V08 · y la copia pública de V02 (la misma imagen) |
| Giro | 48 | V05 · DN (declaración escrita de Noelia, sin imagen) |
| Cierre | 60 | A1 (audio) · V07, C1 · hilos secundarios (SR, SY, ST, SC, SI: solo texto, sin imagen) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres, códigos ni tablas**, y en este caso
el dato exacto ES la prueba (MO-3.0 ≠ MO-3.1; 16:41; 17:52). Para V01, V02, V03, V05, V07, V08
y C1: genera la imagen con la zona de texto **vacía o con texto de relleno ilegible**, y compón
después el texto exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop).
Verifica cada texto contra `case.php` antes de guardar. **Ningún documento resalta la excepción
ni el código de edición distinto: van con el mismo peso visual que el resto.**

**Cronología maestra (todas las horas del caso salen de aquí):** Cámara del pasillo 16:41 ·
Briefing de apertura: Sara Montoya 17:50 · Candela 17:51 · Iván 17:52 · Hugo Paternina 17:53 ·
Lucía Rondón 17:55 · Palabra de seguridad y llamada de radio 21:47 · Retraso en la detención
21:48 · Recorrido suspendido 21:49 · Coordinación de Actores informada 21:52 · Gerencia 22:05 ·
Reporte generado 08:10 del día siguiente. Ediciones: manual de actores **MA-3.0**; manual de
operadores oficial **MO-3.0**; edición del puesto 2 y de Iván **MO-3.1**. No se usa ninguna fecha
de calendario.

## Biblia visual de continuidad (pegar en los retratos)

- **Marcelo Ibaceta**, 44: complexión media, cabello corto con canas en las sienes, uniforme de supervisión de La Cripta del Insomnio (camisa gris carbón con franja roja discreta en el hombro y credencial de supervisor).
- **Iván Bazcuñán**, 30: delgado, cabello oscuro corto, uniforme de operador de control (camiseta negra con franja roja discreta y auriculares colgados al cuello).
- **Renata Ojeda**, 48: ropa formal de gerencia (blazer oscuro y blusa clara), cabello recogido, porte ejecutivo.
- **Yesenia Prado**, 36: ropa práctica de coordinación de actores (pantalón negro, camiseta oscura, chaqueta ligera), cabello en moño, carpeta de turnos en la mano.
- **Tadeo Ferraris**, 39: overol técnico de mantenimiento gris, barba corta, herramientas en el cinturón.
- **Candela Riestra**, 33: uniforme de operadora de control (mismo diseño que el de Iván), cabello largo recogido en cola, mirada atenta.
- **Darío Lezcano**, 27: vestuario de actor de la Sala del Péndulo (camisa antigua de época algo gastada, chaleco oscuro), maquillaje sencillo sin gore, una venda discreta en la muñeca.
- **Noelia Chamorro**, 26: ropa de oficina sencilla (suéter claro), cabello suelto, gafas.
- **Osmán Villagra**, 55: uniforme de prevención (chaleco reflectivo sobre camisa clara) con credencial, cabello canoso, gesto pausado.
- **La Cripta del Insomnio**: recorrido y carteles siempre idénticos en todas las piezas; luz de servicio con negro y rojo moderados; utilería teatral artesanal; **sin estética de póster ni gore**. Salas, en orden: Vestíbulo, Sala de los Espejos, Sala del Péndulo, Galería de las Sombras, Sala del Eco, Salida. Misma tipografía sobria de manual corporativo en V02, V03 y V05.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos, sin
logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio en una atracción de horror inmersivo: un pasillo oscuro de recorrido teatral con luz de servicio en negro y rojo moderados, y en primer plano, sobre una consola de control, un auricular de radio y una carpeta de manual abierta con una página sin texto legible; al fondo, el vaivén desenfocado de un gran péndulo teatral detenido a medias en un arco oscuro. Sensación de tensión contenida y de procedimiento, no de monstruo ni de gore. Formato vertical de póster de catálogo (2:3), estilo realista premium, sin texto integrado. **EVITAR:** sangre, heridos visibles, cadáver, monstruos, iconografía policial, rostros nítidos, texto, logos, marcas reales.

## 2. Retratos

(`photos/marcelo-ibaceta.png` — Marcelo Ibaceta)
**PROMPT:** Retrato diegético de un hombre de 44 años, complexión media, cabello corto con canas en las sienes, uniforme de supervisión de una atracción inmersiva (camisa gris carbón con franja roja discreta y credencial), expresión profesional, serena y controlada, fondo de pasillo administrativo con luz de servicio. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto amenazante.

(`photos/ivan-bazcunan.png` — Iván Bazcuñán)
**PROMPT:** Retrato diegético de un hombre de 30 años, delgado, cabello oscuro corto, uniforme de operador de control (camiseta negra con franja roja discreta), auriculares colgados al cuello, expresión tensa y aplicada, algo confundida, fondo de un puesto de control con pantallas de audio apagadas. Formato retrato. **EVITAR:** apariencia de culpable, gesto de culpa, dramatización.

(`photos/renata-ojeda.png` — Renata Ojeda)
**PROMPT:** Retrato diegético de una mujer de 48 años, ropa formal de gerencia (blazer oscuro y blusa clara), cabello recogido, porte ejecutivo, expresión controlada y fría, fondo de oficina administrativa sobria. Formato retrato. **EVITAR:** apariencia de villana, gesto amenazante, texto legible.

(`photos/yesenia-prado.png` — Yesenia Prado)
**PROMPT:** Retrato diegético de una mujer de 36 años, ropa práctica de coordinación de actores (camiseta oscura, chaqueta ligera), cabello en moño, carpeta de turnos en la mano, expresión cálida con un punto de cansancio, fondo de camerino de actores con espejos y percheros de vestuario. Formato retrato. **EVITAR:** apariencia de culpable, dramatización, texto legible en la carpeta.

(`photos/tadeo-ferraris.png` — Tadeo Ferraris)
**PROMPT:** Retrato diegético de un hombre de 39 años, overol técnico de mantenimiento gris, barba corta, herramientas en el cinturón, expresión práctica y algo seca, fondo de taller con piezas de mecanismos teatrales. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, herramientas en actitud de agresión, marcas reales.

(`photos/candela-riestra.png` — Candela Riestra)
**PROMPT:** Retrato diegético de una mujer de 33 años, uniforme de operadora de control (mismo diseño que el de Iván), cabello largo recogido en cola, mirada atenta y ambiciosa, fondo de un puesto de control con pantallas de audio. Formato retrato. **EVITAR:** apariencia de culpable, sonrisa maliciosa, dramatización.

(`photos/dario-lezcano.png` — Darío Lezcano, el herido y testigo; el mismo archivo se usa como imagen de la víctima)
**PROMPT:** Retrato diegético de un hombre de 27 años con vestuario de actor de una sala temática (camisa antigua algo gastada, chaleco oscuro), maquillaje escénico sencillo sin gore, una venda discreta en la muñeca, expresión directa y algo aturdida, fondo de la entrada lateral de una sala teatral con luz de servicio. Formato retrato. **EVITAR:** sangre, lesión visible o grave, apariencia de víctima dramática, maquillaje de terror excesivo.

(`photos/noelia-chamorro.png` — Noelia Chamorro)
**PROMPT:** Retrato diegético de una mujer de 26 años, ropa de oficina sencilla (suéter claro), cabello suelto, gafas, expresión discreta y observadora, fondo de un escritorio junto a un pasillo administrativo con una fotocopiadora desenfocada. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, texto legible.

(`photos/osman-villagra.png` — Osmán Villagra)
**PROMPT:** Retrato diegético de un hombre de 55 años, cabello canoso, uniforme de prevención (chaleco reflectivo sobre camisa clara) con credencial, expresión pausada y literal, fondo de una oficina de prevención con archivadores. Formato retrato. **EVITAR:** apariencia de villano, gesto severo exagerado, texto legible en la credencial.

## 3. Evidencias visuales

(`gallery/v01-plano-recorrido.png` — V01, plano del recorrido; entrega en el gancho)
**PROMPT:** Diagrama vectorial diegético, limpio e institucional, del plano esquemático de una atracción inmersiva de horror: seis salas en secuencia, una zona de actores, una oficina administrativa y cuatro puestos de control, con líneas discretas que unen cada puesto con las salas cuyo audio monitorea. Aspecto de documento técnico, neutro, sin jerarquía visual que destaque ninguna sala. Dejar vacías las etiquetas. Formato horizontal. **EVITAR:** iconografía de alarma, estética de póster de terror, marcas de atracciones reales, flechas explicativas, resaltados.
**TEXTO A COMPONER:** título *La Cripta del Insomnio — Plano del recorrido*. Salas en orden de recorrido: *Entrada y Vestíbulo → Sala de los Espejos → Sala del Péndulo → Galería de las Sombras → Sala del Eco → Salida*. Zonas aparte: *Zona de actores* y *Oficina administrativa* (con una pequeña sala rotulada *Impresión y archivo* en el mismo pasillo que la oficina). Puestos de control y sus líneas de audio: **Puesto de control 1 → Vestíbulo y Sala de los Espejos · Puesto de control 2 → Sala del Péndulo · Puesto de control 3 → Galería de las Sombras · Puesto de control 4 → Sala del Eco y Salida.** Nada indica qué manual usa cada puesto.

(`gallery/v02-manual-actores.png` — V02, página del manual de actores; pista privada del minuto 12 y copia pública del 36, la misma imagen)
**PROMPT:** Documento visual diegético: una página impresa de un manual corporativo de seguridad, sobria, de aspecto de manual encuadernado simple, fotografiada o escaneada de cerca con luz neutra de oficina, con un encabezado, una sección numerada con tres instrucciones breves y un pie de página con código de edición. Dejar vacío todo el texto. Formato vertical, aspecto de página de manual. **EVITAR:** iconografía de alarma, estética de póster, resaltados, técnicas reales de desactivación de sistemas de seguridad, texto generado libremente. (Mismo formato y tipografía que V03.)
**TEXTO A COMPONER:** encabezado *La Cripta del Insomnio — Manual de actores*. Sección: **2. Palabra de seguridad.** *2.1 Si cualquier visitante pronuncia la palabra de seguridad, el actor sale de personaje y se retira de inmediato de la zona de acción.* *2.2 Al retirarse, señala al puesto de control levantando la mano hacia la cámara de la sala.* *2.3 No retoma el personaje hasta que el puesto de control lo autorice.* Pie: *Ed. MA-3.0 · Prevención*.

(`gallery/v03-manual-operadores.png` — V03, página del manual de operadores del puesto 2; minuto 24)
**PROMPT:** Documento visual diegético: una página impresa de un manual corporativo de seguridad, en el mismo formato, papel y tipografía que el manual de actores (V02), con una sección numerada con tres instrucciones y un pie de página con código de edición. La segunda instrucción debe ser una nota técnica sin ningún resalte, del mismo tamaño y peso que el resto. Dejar vacío todo el texto. Formato vertical. **EVITAR:** iconografía de alarma, resaltados o subrayados sobre la nota, técnicas reales de desactivación de sistemas de seguridad, texto generado libremente.
**TEXTO A COMPONER:** encabezado *La Cripta del Insomnio — Manual de operadores de control*. Sección: **4. Palabra de seguridad.** *4.1 Al recibir la palabra de seguridad por el canal de audio de su sala, el operador detiene de inmediato cualquier efecto mecánico activo de su sala.* *4.2 Nota técnica (Sala del Péndulo): para evitar daños al motor, el mecanismo debe completar su ciclo natural antes de detenerse.* *4.3 Tras detener, el operador avisa a Coordinación de Actores y registra la hora en el parte de turno.* Pie: ***Ed. MO-3.1** · Prevención* (el código debe verse con el mismo peso que en V02).

(`gallery/v04-puesto-de-control.png` — V04, puesto de control 2; minuto 36)
**PROMPT:** Fotografía diegética documental del Puesto de control 2 de una atracción inmersiva: escritorio con pantallas de audio simples, un intercomunicador, auriculares y una carpeta de manual abierta, en una escenografía técnica funcional con luz de trabajo moderada y tonos negros y rojos discretos. Sin ninguna persona. Formato horizontal. **EVITAR:** iconografía de alarma, gore, daño visible, estética de póster de terror, texto legible salvo la portada de la carpeta.
**TEXTO A COMPONER:** en la portada de la carpeta: *Manual de operadores — Puesto de control 2*. Las páginas abiertas quedan sin texto legible. No compongas el código de edición en esta pieza.

(`gallery/v05-registro-briefing.png` — V05, registro de briefing; minuto 48)
**PROMPT:** Captura maquetada diegética de una hoja de cálculo o lista impresa simple de una oficina de prevención, con una tabla de cinco filas y cinco columnas, sobria y burocrática, con una columna de firmas manuscritas. Ninguna fila resaltada. Dejar vacío el texto. Formato horizontal. **EVITAR:** iconografía de alarma, resaltar o encuadrar la fila de Iván, texto generado libremente.
**TEXTO A COMPONER:** título *La Cripta del Insomnio — Registro de briefing de apertura · Turno de noche*. Columnas *Operador · Puesto · Edición del manual en el puesto · Hora · Firma*. Filas: **Sara Montoya · PC-3 · MO-3.0 · 17:50** / **Candela Riestra · PC-1 · MO-3.0 · 17:51** / **Iván Bazcuñán · PC-2 · MO-3.1 · 17:52** / **Hugo Paternina · PC-4 · MO-3.0 · 17:53** / **Lucía Rondón · Relevo · MO-3.0 · 17:55**. Una firma manuscrita genérica en cada fila. Pie: *Ediciones vigentes emitidas por Prevención: MO-3.0 (operadores) · MA-3.0 (actores).* El código MO-3.1 con el mismo peso visual que los otros.

(`gallery/v06-utileria-pendulo.png` — V06, Sala del Péndulo en reposo; minuto 24)
**PROMPT:** Fotografía diegética documental de una esquina de almacenamiento de utilería de la Sala del Péndulo después del cierre: telas, maquillaje de escena, piezas decorativas inocuas y el mecanismo de un péndulo teatral en reposo, visiblemente en buen estado y bien mantenido, sin ninguna señal de manipulación; luz de servicio tenue con tonos negros y rojos moderados. Formato horizontal. **EVITAR:** gore, daño visible, iconografía de alarma, estética de póster de terror, personas.
**TEXTO A COMPONER:** ninguno.

(`gallery/v07-captura-incidencias.png` — V07, reporte de incidencias; minuto 60)
**PROMPT:** Captura maquetada diegética de un reporte interno de incidencias de una atracción, con una tabla de tres columnas y un recuadro de observaciones al pie, aspecto de captura de sistema interno simple, neutra y administrativa. Dejar vacío el texto. Formato horizontal. **EVITAR:** iconografía de alarma, descripciones de lesión gráficas, resaltados, texto generado libremente.
**TEXTO A COMPONER:** título *La Cripta del Insomnio — Reporte de incidencias · Turno de noche · Generado 08:10*. Columnas *Hora · Sala / Zona · Descripción*. Filas: **21:47 · Sala del Péndulo · Palabra de seguridad pronunciada por un visitante. El actor se retira.** / **21:48 · Sala del Péndulo · Retraso en la detención del mecanismo.** / **21:49 · Sala del Péndulo · Recorrido de la sala suspendido. Se atiende a un miembro del elenco.** / **21:52 · Zona de actores · Coordinación de Actores informada.** / **22:05 · Oficina administrativa · Gerencia informada.** Observaciones: *Retraso atribuido en primera instancia al operador de turno. Pendiente de revisión.*

(`gallery/v08-cctv-pasillo.png` — V08, cámara del pasillo; minuto 36)
**PROMPT:** Fotograma estático diegético de una cámara fija de vigilancia institucional de un pasillo administrativo, calidad funcional y ángulo fijo, con una puerta de sala de impresión al fondo y una figura masculina de unos 44 años con uniforme de supervisión gris carbón, caminando con una carpeta bajo el brazo, sin rostro perfectamente nítido; luz de interior moderada. Dejar vacíos el rótulo de la cámara y el cartel de la puerta. Formato horizontal, aspecto de cámara de seguridad. **EVITAR:** rostro nítido, resaltados, círculos o flechas sobre la figura, iconografía de alarma.
**TEXTO A COMPONER:** rótulo de la cámara *PASILLO ADMIN · CAM 3 · 16:41:08*. Cartel junto a la puerta del fondo: *IMPRESIÓN Y ARCHIVO*. Uniforme de la figura según la biblia visual de Marcelo.

(`gallery/c1-chat-marcelo.png` — C1, mensaje de Marcelo; minuto 60)
**PROMPT:** Captura de pantalla diegética de un breve intercambio de mensajería en un teléfono, interfaz simple y genérica, luz neutra de pantalla, tono profesional. Dejar vacíos los textos. Formato vertical, aspecto de captura de pantalla. **EVITAR:** iconografía de alarma, menciones explícitas al ascenso o al sabotaje, logos de apps reales, fotos de perfil nítidas.
**TEXTO A COMPONER:** contacto *Contacto (sin nombre guardado)*. *Miércoles 19:20.* Mensaje enviado: **«Estoy asegurándome de que la actualización del protocolo quede bien implementada para el turno de Iván esta semana.»** Respuesta recibida: *«Perfecto. Avísame cómo va.»*

## 4. Audio

(`audio/a1-radio-ivan.mp3` — A1, llamada de radio de Iván; minuto 60; duración 10-15 s)
- **Scene:** canal de radio del Puesto de control 2: ligero eco de intercomunicador y leve ruido de fondo de sala de control, sin música.
- **Context:** Iván responde por radio al aviso de la palabra de seguridad en la Sala del Péndulo; tono profesional, tranquilo, de quien sigue un procedimiento que cree correcto; sin urgencia ni alarma.
- **Speaker:** Speaker 1 - Iván Bazcuñán · **Voz Gemini:** Orus (hombre adulto joven).
- **Script (solo lo que se dice):** «Control a Sala del Péndulo, recibido el aviso. Esperando que el mecanismo complete el ciclo antes de detener, según la excepción. Deteniendo ahora.»
- **EVITAR:** tono de pánico o urgencia, música, cualquier mención explícita de lesión o daño.

(A2 — confesión de Marcelo) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (voz Charon) para incluir las preguntas que esa mesa le hizo a Marcelo. Dirección de voz: grave, pausada, con un reconocimiento gradual; sin sobreactuación ni autocompasión excesiva; sin música y sin descripciones gráficas de la lesión.
