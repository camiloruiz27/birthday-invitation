# Prompts de assets — Entre Sueño y Vigilia

Total: **1 portada + 9 retratos + 14 evidencias visuales + 2 audios** (más la confesión de
Julián, que se genera por partida y no se graba).

Todo se guarda en `public/immersion/entre-sueno-y-vigilia/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos |
| `gallery/` | las 14 evidencias visuales |
| `audio/` | `a1-ambiente-clinica-noche.wav`, `a2-audio-ambiental-habitacion.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Hacer juntos V2 y V8 (misma sala, mismo
   carrito) y V3a/V3/V4a/V4 (mismo formato de gráfico; el parcial es el completo recortado y
   sin horas). V9 debe verse igual que la habitación 1 de V1.
5. **Audios** A1 y A2.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V2, A1 |
| Expansión | 13 | V9, V10, V11 |
| Pistas privadas | 26 | V3a (registro de Bruno parcial), V4a (registro de Camila parcial) |
| Complicación | 39 | V3 (completo), V5, V12, A2 |
| Giro | 52 | V4 (completo), V6, V7, V8 |
| Cierre | 58 | sin imágenes nuevas |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el
dato exacto ES la prueba (01:52 ≠ 02:00; 03:00 ≠ 02:00; entrada 01:44 / salida 02:03). Para V1,
V3a, V3, V4a, V4, V5, V6, V7, V10 y V12: genera la imagen con la zona de texto **vacía o con
texto de relleno ilegible**, y compón después el texto exacto indicado en "TEXTO A COMPONER" con
un editor (Canva, Figma, Photoshop). Verifica cada hora contra `case.php` antes de guardar.

## Biblia visual de continuidad (pegar en los retratos)

- **Dra. Renata Solís** (víctima), 48: cabello castaño con canas, bata clínica sobre ropa formal, gafas.
- **Julián Meza**, 44: complexión media, bata clínica, gafas de montura fina.
- **Bruno Castro**, 36: ropa cómoda de dormir, sensores de estudio visibles en sienes y pecho.
- **Camila Vega**, 29: ropa cómoda de dormir, cabello suelto, sensores de estudio visibles.
- **Héctor Paz**, 50: ropa cómoda de dormir, complexión robusta.
- **Laura Andrade**, 38: uniforme de enfermería sobrio.
- **Daniel Rojas**, 30: uniforme técnico clínico.
- **Dra. Patricia Luna**, 55: bata clínica formal, complexión media.
- **Clínica del Sueño Aurora**: interiores clínicos sobrios, habitaciones individuales con cama y equipos de monitoreo discretos, sala de monitoreo central con pantallas, iluminación nocturna tenue y cálida en pasillos, luz fría funcional en la sala técnica.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio psicológico-clínico: el pasillo de una clínica del sueño de noche, iluminación cálida y tenue, con las puertas de las habitaciones entreabiertas y, al fondo, una puerta de cristal con luz fría tras la que se adivinan pantallas de monitoreo; en primer plano, un gráfico de fases de sueño sobre un panel, desenfocado. Sensación de calma inquietante, íntima, entre el sueño y la vigilia; sin personas visibles. Debe sugerir que lo importante ocurrió a unos metros de quienes dormían. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror, tecnología futurista, texto o logos.

## 2. Retratos

(`photos/renata-solis.png` — víctima, Dra. Renata Solís)
**PROMPT:** Retrato diegético tipo foto de credencial profesional de una mujer de 48 años, cabello castaño con canas, gafas, bata clínica sobre ropa formal, expresión serena, atenta y exigente de directora de estudio, fondo neutro de oficina clínica. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales.

(`photos/julian-meza.png` — Julián Meza)
**PROMPT:** Retrato diegético de un hombre de 44 años, complexión media, bata clínica, gafas de montura fina, expresión profesional, segura y algo rígida de investigador veterano, fondo de la sala de monitoreo de una clínica del sueño con pantallas apagadas. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, estética de villano.

(`photos/bruno-castro.png` — Bruno Castro)
**PROMPT:** Retrato diegético de un hombre de 36 años, ropa cómoda de dormir, sensores de estudio visibles en sienes y pecho, expresión confundida, algo avergonzada y sincera, sentado en el borde de una cama sencilla de una habitación de estudio del sueño con luz cálida tenue. Formato retrato. **EVITAR:** aspecto de enfermo, dramatización, gesto de miedo.

(`photos/camila-vega.png` — Camila Vega)
**PROMPT:** Retrato diegético de una mujer de 29 años, ropa cómoda de dormir, cabello suelto, sensores de estudio visibles, expresión segura, atenta y colaboradora, en un pasillo de clínica con luz cálida tenue. Formato retrato. **EVITAR:** aspecto de mentirosa, sospecha teatral, dramatización.

(`photos/hector-paz.png` — Héctor Paz)
**PROMPT:** Retrato diegético de un hombre de 50 años, complexión robusta, ropa cómoda de dormir, expresión evasiva, nerviosa y defensiva, en el borde de una cama de una habitación de estudio del sueño con luz tenue. Formato retrato. **EVITAR:** villano obvio, gesto agresivo, aspecto de enfermo exagerado.

(`photos/laura-andrade.png` — Laura Andrade)
**PROMPT:** Retrato diegético de una mujer de 38 años, uniforme de enfermería sobrio, expresión profesional, algo orgullosa e incómoda, fondo de una estación de enfermería nocturna de clínica con luz cálida. Formato retrato. **EVITAR:** sospecha teatral, gesto de culpa obvio, dramatización.

(`photos/daniel-rojas.png` — Daniel Rojas)
**PROMPT:** Retrato diegético de un hombre de 30 años, uniforme técnico clínico, expresión franca, atenta y servicial, fondo de un puesto técnico de pasillo con un panel de fases de sueño. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/patricia-luna.png` — Dra. Patricia Luna)
**PROMPT:** Retrato diegético de una mujer de 55 años, bata clínica formal, complexión media, expresión sobria, institucional y afectada pero contenida, fondo de un despacho clínico sencillo. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, lágrimas.

(`photos/sistema-monitorizacion.png` — sistema de monitorización del sueño)
**PROMPT:** Imagen diegética tipo retrato funcional de una consola de monitorización de sueño de una clínica, con pantallas sobrias que muestran gráficos de fases de sueño por participante y una lista de lecturas de tarjeta, usada como avatar del sistema. Luz fría de sala técnica. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, fallos visibles, estética futurista.

## 3. Evidencias visuales

(`gallery/v1-plano-clinica.png` — V1, plano de la clínica)
**PROMPT:** Documento diegético tipo plano funcional simple de una clínica del sueño, en líneas esquemáticas claras. Un pasillo horizontal: en su extremo oeste, la sala de monitoreo central (con su puerta al pasillo) y, pegada a ella, el baño de participantes; la habitación 1 comparte pared con la sala de monitoreo; siguen la habitación 2 y la habitación 3 en el mismo lado del pasillo; en el lado opuesto, junto a la sala de monitoreo, la oficina de la directora. Tras un recodo del pasillo, en el extremo este: el puesto técnico, la estación de enfermería, la sala de personal y la entrada. Desde el extremo este no debe verse la puerta de la sala de monitoreo. Formato horizontal. **EVITAR:** iconografía de alerta, accesos o salidas no descritos, color arbitrario.
**TEXTO A COMPONER (etiquetas simples):** *Sala de monitoreo central · Baño de participantes · Hab. 1 · Hab. 2 · Hab. 3 · Oficina de la directora · Puesto técnico · Estación de enfermería · Sala de personal · Entrada.*

(`gallery/v2-sala-monitoreo-escena.png` — V2, sala de monitoreo al hacer el hallazgo)
**PROMPT:** Fotografía diegética funcional de una sala de monitoreo clínica, con pantallas de seguimiento apagadas o en reposo, un escritorio con documentos y un carrito de equipo médico ligero fuera de su posición habitual junto a la pared, una silla movida; el cuerpo, si se sugiere, queda fuera de cuadro. Foco en el carrito desplazado como único elemento claramente fuera de lugar. Luz fría de sala técnica. Mismo carrito y misma sala que en V8. Formato horizontal. **EVITAR:** sangre, gore, cuerpo en primer plano, dramatización.

(`gallery/v3a-registro-bruno-parcial.png` — V3 parcial, entrega privada a T+26)
**PROMPT:** Documento diegético tipo gráfico simplificado de fases de sueño de un reporte clínico, línea de tiempo horizontal con bloques de «sueño profundo» y «sueño ligero» y una marca breve distinta de «microdespertar» entre bloques de sueño profundo; recortado como fragmento, con el eje de horas cortado, sin hora ni duración legibles de la marca, sin la hoja completa. Mismo estilo exacto que V3. Formato horizontal. **EVITAR:** terminología médica compleja, iconografía de alerta, hora legible de la marca.
**TEXTO A COMPONER:** encabezado *Registro de monitorización — Participante: B. C.*; leyenda de bloques *Sueño profundo · Sueño ligero · Microdespertar*; eje de horas cortado (sin la marca de hora de la interrupción).

(`gallery/v3-registro-bruno.png` — V3 completo, entrega a T+39)
**PROMPT:** El mismo gráfico de fases de sueño que V3 parcial (mismo diseño y mismas barras), ahora completo, con la noche entera de 22:00 a 06:30 y la marca de microdespertar claramente rotulada con su hora y duración. Formato horizontal. **EVITAR:** cambiar el diseño respecto al parcial, terminología médica compleja, iconografía de alerta.
**TEXTO A COMPONER:** encabezado *Registro de monitorización — Participante: Bruno Castro (Hab. 1) — Noche de estudio 22:00–06:30*; leyenda *Sueño profundo · Sueño ligero · Microdespertar*; rótulo sobre la marca: **«Microdespertar 01:52 — duración aprox. 2 min»**. Eje de horas con 22:00, 00:00, 02:00, 04:00, 06:00. Resto de la noche: sueño profundo y ligero sin otra interrupción.

(`gallery/v4a-registro-camila-parcial.png` — V4 parcial, entrega privada a T+26)
**PROMPT:** Documento diegético tipo gráfico simplificado de fases de sueño, mismo formato que V3, de otra participante: sueño casi continuo y un despertar breve marcado cerca del final del tramo, recortado como fragmento, con el eje de horas cortado y sin la hora legible del despertar. Mismo estilo que V3a. Formato horizontal. **EVITAR:** terminología médica compleja, iconografía de alerta, hora legible del despertar.
**TEXTO A COMPONER:** encabezado *Registro de monitorización — Participante: C. V.*; leyenda *Sueño profundo · Sueño ligero · Despertar*; eje de horas cortado (sin la hora del despertar).

(`gallery/v4-registro-camila.png` — V4 completo, entrega a T+52)
**PROMPT:** El mismo gráfico que V4 parcial (mismo diseño y mismas barras), ahora completo, noche entera de 22:00 a 06:30, con el despertar real breve rotulado con su hora. Mismo estilo exacto que V3. Formato horizontal. **EVITAR:** cambiar el diseño respecto al parcial, terminología médica compleja, iconografía de alerta.
**TEXTO A COMPONER:** encabezado *Registro de monitorización — Participante: Camila Vega (Hab. 2) — Noche de estudio 22:00–06:30*; leyenda *Sueño profundo · Sueño ligero · Despertar*; rótulo sobre la marca: **«Despertar real — hacia las 03:00»**. Eje de horas con 22:00, 00:00, 02:00, 04:00, 06:00. El resto de la noche: sueño continuo (sin marca a las 02:00).

(`gallery/v5-datos-crudos-vs-procesados.png` — V5, comparación de datos)
**PROMPT:** Documento diegético tipo tabla comparativa simple de datos científicos de un estudio del sueño, dos columnas («registro crudo del sensor» y «valor reportado en el informe final») para un mismo periodo, mostrando una discrepancia notable explicada en términos simples, sin fórmulas complejas, aspecto de documento impreso o digital sencillo con luz neutra de oficina. Dejar vacía la zona de cifras y del pie. Formato vertical. **EVITAR:** fórmulas matemáticas complejas, jerga científica innecesaria, iconografía de alerta, nombre completo del responsable.
**TEXTO A COMPONER:** tabla *Medida · Registro crudo del sensor · Valor reportado en el informe final*: **Tiempo medio hasta dormirse (min): 41 · 24**; **Despertares por noche (media): 5,2 · 3,1**; **Eficiencia del sueño (%): 78 · 89**. Pie: *Procesamiento y reporte: usuario INV-02.* (No poner ningún nombre: V6 es la pieza que lo atribuye.)

(`gallery/v6-notas-renata.png` — V6, notas de Renata)
**PROMPT:** Documento diegético tipo notas de trabajo breves mecanografiadas, en una hoja sobre un escritorio de oficina clínica, tono profesional y preocupado de alguien documentando con cautela un hallazgo grave, fotografiado con luz neutra de oficina. Dejar vacío el cuerpo. Formato vertical. **EVITAR:** iconografía de alerta, texto acusatorio dramatizado, notas de despedida.
**TEXTO A COMPONER:** *Notas — revisión de datos, esta tarde.* · *Comparé el registro crudo de los sensores con el informe final. Las cifras reportadas son sistemáticamente mejores que las medidas.* · *Todo el procesamiento figura a nombre de INV-02 (J. Meza). Sin justificación documentada.* · *No es un error de redondeo.* · *Mañana 8:00: hablar con Patricia. Después, reporte formal al comité de ética.*

(`gallery/v7-registro-acceso-sala.png` — V7, registro de acceso a la sala de monitoreo)
**PROMPT:** Captura de pantalla diegética de un sistema simple de control de accesos clínico, tabla funcional de entradas y salidas por tarjeta a la sala de monitoreo, interfaz sobria y legible, como parte de un listado más amplio, sin resaltar ninguna fila. Dejar vacías las celdas de hora y nombre. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales.
**TEXTO A COMPONER:** columnas *Hora · Tarjeta · Evento*: **21:40 R. Solís ENTRADA** · **22:02 D. Rojas ENTRADA** · **22:10 D. Rojas SALIDA** · **23:15 L. Andrade ENTRADA** · **23:22 L. Andrade SALIDA** · **01:44 J. Meza ENTRADA** · **02:03 J. Meza SALIDA** · **02:52 J. Meza ENTRADA** · **03:01 J. Meza SALIDA** · **06:29 P. Luna ENTRADA** · **06:30 D. Rojas ENTRADA**. Pie: *R. Solís: sin registro de salida.*

(`gallery/v8-carrito-equipo.png` — V8, carrito de equipo)
**PROMPT:** Fotografía diegética funcional de un carrito de equipo médico ligero (con espacio para monitores portátiles o suministros menores), fuera de su posición habitual junto a una pared, con una superficie inusualmente limpia comparada con el desgaste natural visible en el resto del mobiliario cercano; sin sangre ni indicio de violencia. Luz fría de sala técnica. Mismo carrito y misma sala que en V2. Formato 4:5. **EVITAR:** sangre, daño, marcas de violencia, resaltados artificiales sobre la limpieza.

(`gallery/v9-habitacion-bruno.png` — V9, habitación 1)
**PROMPT:** Fotografía diegética funcional de una habitación individual de estudio del sueño (la habitación 1), con una cama sencilla pegada a una pared, equipo de monitoreo discreto junto a la cabecera y cables de sensores ordenados, en penumbra cálida nocturna, sin mostrar a ninguna persona de forma explícita (puede sugerirse una forma bajo las sábanas sin detalle). Debe verse que la cabecera da a una pared compartida con otra sala. Formato 4:5. **EVITAR:** rostro visible, contenido gráfico, dramatización.

(`gallery/v10-horario-estudio.png` — V10, agenda del estudio)
**PROMPT:** Documento diegético tipo agenda o cronograma simple de un estudio clínico nocturno, tabla de horarios esperados de rondas de supervisión y responsables, formato funcional y sobrio, impreso o digital, luz neutra de oficina. Dejar vacía la zona de filas. Formato vertical. **EVITAR:** iconografía de alerta, resaltar filas.
**TEXTO A COMPONER:** columnas *Hora · Actividad · Responsable*: **22:00** Inicio del estudio; conexión de sensores — J. Meza / L. Andrade · **22:02–22:10** Puesta en marcha del panel — D. Rojas · **23:15** Parte de enfermería a la dirección (sala de monitoreo) — L. Andrade · **23:30** Ronda de habitaciones — L. Andrade · **00:30** Ronda de habitaciones — D. Rojas · **01:40–02:05** Descanso programado del técnico de guardia (sin relevo; el panel automático sigue registrando) — D. Rojas · **02:00** Revisión de monitores centrales (aprox. 5 min) — J. Meza · **02:30** Ronda de habitaciones — L. Andrade · **04:00** Ronda de habitaciones — D. Rojas · **06:15** Llegada de la dirección clínica — P. Luna · **06:30** Chequeo matutino con la directora — R. Solís / P. Luna.

(`gallery/v11-escritorio-renata.png` — V11, escritorio de Renata)
**PROMPT:** Fotografía diegética funcional de un escritorio de oficina clínica ordenado, con una carpeta de notas abierta (el texto de la carpeta no debe ser legible), una taza de café a medio terminar y una credencial de directora colgada del respaldo de la silla, sin la persona presente. Sensación de una ausencia inesperada. Luz neutra de oficina. Formato 4:5. **EVITAR:** notas de despedida, indicios de violencia, texto legible en la carpeta.

(`gallery/v12-expediente-hector.png` — V12, expediente de Héctor)
**PROMPT:** Documento diegético tipo formulario clínico de autoevaluación de síntomas de sueño, con una escala simple de severidad marcada en un nivel alto y, al pie, una nota breve del investigador sobre una posible discrepancia con las observaciones objetivas del estudio; formato clínico funcional y sobrio, luz neutra de oficina. Dejar vacías las casillas marcadas y la nota. Formato vertical. **EVITAR:** iconografía de alerta, terminología médica compleja.
**TEXTO A COMPONER:** *Autoevaluación de síntomas de sueño — Participante: Héctor Paz (Hab. 3).* · Escala de severidad (1–10): casilla marcada **9**. · Dificultad para dormir: *Muy severa*. · Despertares por noche: *Más de 8*. · *Nota del investigador (R. Solís): Las mediciones objetivas de las noches 1 a 5 muestran sueño continuo, con 1–2 despertares breves por noche. Discrepancia importante con la autoevaluación; consultar con el participante.*

## 4. Audio

Los guiones están en `case.php`; se exportan listos para estudio con
`php artisan immersion:export-audio-scripts entre-sueno-y-vigilia`. Guardar en `audio/`.

(`audio/a1-ambiente-clinica-noche.wav` — A1, ambiente de la clínica de noche, 10–12 s)
**GUION:** *(silencio nocturno de interior clínico, un leve zumbido de equipos, pasos distantes y suaves)* Voz distante de Daniel: «Todo tranquilo por aquí, sigo las rondas normales.»
**PROMPT:** Ambiente sonoro diegético de una clínica durante la noche, silencio interior con un leve zumbido de equipos médicos y pasos distantes y suaves, seguido de una voz masculina adulta baja y profesional comentando que todo está tranquilo durante las rondas. Tono calmado, rutinario. Sin música. **EVITAR:** música de tensión, tono de alarma.

(`audio/a2-audio-ambiental-habitacion.wav` — A2, audio ambiental de la habitación 1, 15–18 s)
**GUION:** *(grabación muy tenue, filtrada a través de una pared)* Voz masculina tensa, apenas audible: «No puedes hacer esto, no así... dame tiempo.» La réplica femenina es solo un murmullo bajo y firme, sin palabras identificables: no lleva texto en el guion (la grabación es de una sola voz; si se quiere, mezclar en postproducción un murmullo bajo la voz masculina). *(un sonido sordo y breve, luego silencio)*. Ningún nombre debe oírse con claridad.
**PROMPT:** Fragmento de audio ambiental diegético grabado por el micrófono de monitorización clínica de una habitación contigua, con una voz masculina adulta tensa (la única con texto) y, de fondo, un murmullo femenino bajo y firme sin palabras identificables, discutiendo en tono tenso pero contenido, palabras parcialmente ininteligibles por la distancia y una pared de por medio, terminando en un sonido sordo y breve seguido de silencio. La ambigüedad debe venir del sonido real, no de ocultar información. Sin música, sin contenido gráfico. **EVITAR:** palabras completamente claras, nombres audibles, música de tensión, contenido gráfico.

(A3 — confesión de Julián) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Orus) para incluir las preguntas que esa mesa le hizo a Julián.
