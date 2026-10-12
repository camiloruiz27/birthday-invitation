# Prompts de assets — Bajo la Línea de Flotación

Total: **1 portada + 10 retratos + 17 evidencias visuales + 2 audios** (más la confesión, que
se genera por partida y no se graba).

Todo se guarda en `public/immersion/bajo-la-linea-de-flotacion/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos |
| `gallery/` | las 17 evidencias visuales |
| `audio/` | `a1-alarma-entrada-agua.wav`, `a2-radio-horacio.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias V1 → V16** en orden de entrega (ver tabla). V2 y V12 deben salir de la misma
   "sesión" visual (misma sección, misma plataforma seca).
5. **Audios** A1 y A2.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V2, A1 |
| Expansión | 12 | V3, V5, V8 |
| Pistas privadas | 24 | V7 (fragmento), V10a (inventario parcial) |
| Complicación | 36 | V4, V6, V9 |
| Reconstrucción | 48 | V14, A2, V11, V15 |
| Giro | 62 | V12, V13 |
| Cierre | 75 | V10 (completo), V16 |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el
dato exacto ES la prueba (22:05 ≠ 22:10; salida 22:42). Para V4, V5, V6, V7, V10a, V10, V11,
V14 y V16: genera la imagen con la zona de texto **vacía o con texto de relleno ilegible**, y
compón después el texto exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma,
Photoshop). Verifica cada hora contra `case.php` antes de guardar.

## Biblia visual de continuidad (pegar en los retratos)

- **Horacio Valderrama**, 55: complexión robusta, cabello canoso corto, mono de trabajo azul marino con el logo del Instituto Oceanográfico Nacional.
- **Dra. Lucía Fernández Mora**, 42: cabello castaño recogido, chaqueta cortavientos con el logo de la expedición científica.
- **Ramiro Oyarzún**, 48: complexión media, mono de trabajo gris de máquinas, manos con manchas de grasa.
- **Constanza Riquelme**, 34: cabello negro corto, uniforme de oficial de guardia.
- **Capitán Eduardo Solís**, 56: complexión alta, barba canosa corta, uniforme de capitán con galones.
- **Tomás Andía**, 39: complexión robusta, chaleco de trabajo de cubierta.
- **Matías Uribe**, 28: cabello oscuro despeinado, chaqueta cortavientos de la expedición, claramente más joven que Lucía.
- **Daniel Pizarro**, 30: mono de trabajo gris de máquinas.
- **Rosa Navarro**, 50: delantal de intendencia sobre ropa de trabajo.
- **BIO Albatros**: buque de investigación de tamaño medio, interiores metálicos funcionales, puente sobrio, laboratorio con mesas de análisis, sala de máquinas, sección inferior con tuberías, bombas y una pequeña cámara de muestras refrigeradas.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio marítimo: el casco de un buque de investigación oceanográfica de tamaño medio al anochecer sobre un mar oscuro, visto desde la cubierta hacia el agua, con una escotilla de acceso iluminada por una luz de emergencia amarillenta en primer plano y la línea de flotación apenas visible; sensación de emergencia contenida, frío, claustrofóbico y de aislamiento, sin personas visibles. Debe sugerir que lo importante ocurre bajo la superficie. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror, naufragio dramático, tecnología futurista, texto o logos.

## 2. Retratos

(`photos/horacio-valderrama.png` — víctima)
**PROMPT:** Retrato diegético tipo foto de ficha de tripulante de un hombre de 55 años, complexión robusta, cabello canoso corto, mono de trabajo azul marino con el logo genérico de un instituto oceanográfico, expresión serena y cansada de oficial veterano, fondo neutro de sala de máquinas de un buque. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales.

(`photos/lucia-fernandez-mora.png` — Dra. Lucía Fernández Mora)
**PROMPT:** Retrato diegético de una mujer de 42 años, cabello castaño recogido, chaqueta cortavientos con el logo genérico de una expedición científica, expresión profesional, calmada y segura, fondo neutro de laboratorio a bordo. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, bata de laboratorio de ciencia ficción.

(`photos/ramiro-oyarzun.png` — Ramiro Oyarzún)
**PROMPT:** Retrato diegético de un hombre de 48 años, complexión media, mono de trabajo gris de máquinas, manos con manchas de grasa, expresión defensiva y orgullosa de técnico veterano, fondo de sala de máquinas. Formato retrato. **EVITAR:** villano obvio, gesto agresivo, herramientas en actitud amenazante.

(`photos/constanza-riquelme.png` — Constanza Riquelme)
**PROMPT:** Retrato diegético de una mujer de 34 años, cabello negro corto, uniforme de oficial de guardia, expresión meticulosa y levemente incómoda, fondo del puente de mando con instrumentación sobria. Formato retrato. **EVITAR:** gesto de culpa obvio, sospecha teatral, tecnología futurista.

(`photos/capitan-solis.png` — Capitán Eduardo Solís)
**PROMPT:** Retrato diegético de un hombre de 56 años, complexión alta, barba canosa corta, uniforme de capitán con galones, expresión calmada, autoritaria y algo distante, con un leve cansancio visible, fondo del puente de mando. Formato retrato. **EVITAR:** aspecto enfermo explícito, cliché de lobo de mar, gesto amenazante.

(`photos/tomas-andia.png` — Tomás Andía)
**PROMPT:** Retrato diegético de un hombre de 39 años, complexión robusta, chaleco de trabajo de cubierta, expresión nerviosa y evasiva, fondo de cubierta principal de un buque con cabos y equipo. Formato retrato. **EVITAR:** contrabando o cajas sospechosas visibles, villano obvio, gesto agresivo.

(`photos/matias-uribe.png` — Matías Uribe)
**PROMPT:** Retrato diegético de un hombre de 28 años, cabello oscuro despeinado, chaqueta cortavientos con el logo genérico de la expedición, expresión joven, reservada e incómoda, fondo de laboratorio de a bordo. Formato retrato. **EVITAR:** pose romántica, dramatización, aspecto de sospechoso teatral.

(`photos/daniel-pizarro.png` — Daniel Pizarro)
**PROMPT:** Retrato diegético de un hombre de 30 años, mono de trabajo gris de máquinas, expresión franca, atenta y servicial, fondo de pasillo técnico junto a una escotilla de acceso. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/rosa-navarro.png` — Rosa Navarro)
**PROMPT:** Retrato diegético de una mujer de 50 años, delantal de intendencia sobre ropa de trabajo, expresión cercana, práctica y directa, fondo de zona común o pañol del buque. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/sistema-control-buque.png` — sistema de control del buque)
**PROMPT:** Imagen diegética tipo retrato funcional de una consola de monitoreo del puente de un buque, con pantallas sobrias que muestran listas de sensores, estado de puertas estancas y registros, usada como avatar del sistema de control. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, fallos visibles, estética futurista.

## 3. Evidencias visuales

(`gallery/v1-plano-cubiertas.png` — V1, plano simplificado por cubiertas)
**PROMPT:** Documento diegético tipo plano funcional simple de un buque de investigación de tamaño medio, en corte lateral esquemático, mostrando las cubiertas (puente de mando, camarotes, laboratorio científico, cubierta principal, sala de máquinas y una sección inferior bajo la línea de flotación con una escotilla de acceso marcada), en líneas técnicas claras y etiquetas de cubierta simples y legibles. Formato vertical o cuadrado. **EVITAR:** código de colores arbitrario, accesos no mencionados, iconografía de alerta, tecnología futurista.

(`gallery/v2-seccion-inferior-inundada.png` — V2, sección inferior inundada)
**PROMPT:** Fotografía diegética funcional del interior de una sección técnica inferior de un buque, con tuberías, una bomba de achique y un nivel bajo de agua residual sobre el suelo metálico, iluminación de emergencia amarillenta combinada con luces técnicas, algo de vapor o humedad en el aire. Al fondo, una plataforma metálica elevada claramente seca. Sin cuerpo visible. Un soporte de pared para extintor visible y vacío. Formato horizontal. **EVITAR:** sangre, gore, cuerpo, dramatización.

(`gallery/v3-valvula-marcas.png` — V3, válvula con marcas)
**PROMPT:** Fotografía diegética funcional de primer plano de una válvula industrial de tubería, con marcas sutiles de desgaste forzado o manipulación con herramienta (un rayado leve e inusual en la unión, no una rotura violenta), luz artificial de trabajo, fotografiada durante una inspección técnica. Podría parecer desgaste normal a primera vista. Formato 4:5. **EVITAR:** daños espectaculares, chispas, fuego, resaltado artificial sobre la marca.

(`gallery/v4-registro-mantenimiento-valvula.png` — V4, registro de mantenimiento)
**PROMPT:** Documento diegético tipo hoja de registro de mantenimiento técnico naval, con una tabla funcional de revisiones periódicas de la válvula afectada, sin ninguna anomalía reportada, en un formato sobrio de documento operativo. Dejar vacía la zona de la última revisión y su resultado. Formato vertical. **EVITAR:** iconografía de alerta, jerga naval excesiva.
**TEXTO A COMPONER:** columnas *Fecha · Tarea · Resultado*. Última fila: **Revisión de válvula de achique — sin anomalías**, fecha 12 días antes del incidente (p. ej. «14/09»). Filas previas con revisiones mensuales también «sin anomalías».

(`gallery/v5-registro-puertas-estancas.png` — V5, registro de puertas estancas)
**PROMPT:** Captura de pantalla diegética de un sistema simple de control de puertas estancas de un buque, con una tabla funcional de cierres y aperturas de la sección afectada, interfaz sobria. Dejar vacías las celdas de hora. Formato horizontal. **EVITAR:** iconografía de alarma dramatizada.
**TEXTO A COMPONER:** *Sección inferior de proa — CIERRE 22:15 — APERTURA GENERAL 22:45.*

(`gallery/v6-registro-bombas-alarma.png` — V6, bombas y alarma)
**PROMPT:** Captura de pantalla diegética de un panel simple de monitoreo del sistema de achique de un buque, con una línea de «primera lectura anómala» y otra de «activación de alarma», y una nota técnica breve al pie, interfaz sobria. Dejar vacías las horas y la nota. Formato horizontal. **EVITAR:** iconografía de alarma dramatizada, interpretación escrita de la diferencia.
**TEXTO A COMPONER:** *Primera lectura anómala 22:05 — Activación de alarma 22:10 — Nota: «registro verificado posteriormente».*

(`gallery/v7-comunicado-recorte.png` — V7, comunicado de recorte; se entrega como fragmento a T+24)
**PROMPT:** Documento diegético tipo comunicado interno institucional breve, con membrete genérico de un instituto oceanográfico, tono administrativo y sobrio, aspecto de impreso o documento digital simple. Dejar vacío el cuerpo del texto. Formato vertical. **EVITAR:** iconografía de alerta, lenguaje dramatizado, logos reales.
**TEXTO A COMPONER:** *Asunto: Revisión presupuestaria del ejercicio. Se informa que el Instituto evaluará ajustes en dotación de personal y partidas de mantenimiento de las unidades de campo. Se comunicarán novedades próximamente.*

(`gallery/v8-puente-de-mando.png` — V8, puente de mando)
**PROMPT:** Fotografía diegética funcional del puente de mando de un buque de investigación de noche, instrumentación de navegación sobria, ventanales hacia el mar oscuro, iluminación técnica tenue, sin personas en primer plano (como mucho una silueta desenfocada de espaldas). Formato horizontal. **EVITAR:** estética futurista, dramatización.

(`gallery/v9-laboratorio.png` — V9, laboratorio científico)
**PROMPT:** Fotografía diegética funcional de un laboratorio científico de a bordo, con mesas de análisis, muestras etiquetadas en contenedores y equipo de medición sobrio, luz fluorescente fría, sin personas en primer plano. Formato horizontal. **EVITAR:** laboratorio de ciencia ficción, etiquetas legibles críticas.

(`gallery/v10a-inventario-parcial.png` — V10 parcial, entrega privada a T+24)
**PROMPT:** Documento diegético tipo hoja de inventario científico, con una tabla funcional de muestras, códigos y cantidades registradas, y una breve anotación manuscrita de verificación física que no coincide con una de las cantidades. La zona del responsable queda en blanco. Cortado o parcial, como fragmento. Formato vertical. **EVITAR:** iconografía de alerta, jerga científica innecesaria, nombre de responsable.
**TEXTO A COMPONER:** tabla *Código · Muestra · Cant. registrada*; una fila con registrado «24» y anotación manuscrita «verif.: 19». Campo *Responsable:* vacío.

(`gallery/v10-inventario-completo.png` — V10 completo, entrega a T+75)
**PROMPT:** La misma hoja de inventario que V10 parcial (mismo diseño, mismas filas y misma anotación manuscrita), ahora completa y sin cortes, con el nombre del responsable visible. Formato vertical. **EVITAR:** cambiar el diseño respecto a V10 parcial, iconografía de alerta, resaltados.
**TEXTO A COMPONER:** idéntico a V10 parcial, más *Responsable: Dra. L. Fernández Mora*.

(`gallery/v11-transcripcion-radio.png` — V11, transcripción de radio)
**PROMPT:** Documento diegético tipo transcripción simple de comunicaciones de radio interna de un buque, lista breve de transmisiones con hora y remitente, formato de registro funcional del sistema de comunicaciones. El mensaje de Horacio es una transmisión más, sin resaltar. Dejar vacío el texto. Formato vertical. **EVITAR:** iconografía de alarma, texto interpretativo añadido.
**TEXTO A COMPONER:**
- *22:12 PUENTE → MÁQUINAS: Confirmen presión de bombas de achique.*
- *22:14 MÁQUINAS → PUENTE: Bombas de achique en línea.*
- *22:35 H. VALDERRAMA → PUENTE: «Puente, aquí abajo. La válvula no se ve como un desgaste normal, hay algo raro. Y ya que estoy, encontré algo en el inventario de muestras que no cuadra. Hablamos cuando terminemos aquí.»*
- *22:36 PUENTE → H. VALDERRAMA: Recibido.*
- *22:39 PUENTE → H. VALDERRAMA: Confirme su estado. — sin respuesta*

(`gallery/v12-posicion-cuerpo-zona-seca.png` — V12, plataforma seca)
**PROMPT:** Fotografía diegética funcional de la misma sección inferior de V2, con el foco en la plataforma metálica elevada y completamente seca, en contraste con el agua residual visible en otras partes de la sección, algún objeto personal caído (un guante de trabajo, un casco). Sin cuerpo explícito (sugerido fuera de cuadro). Luz de emergencia amarillenta, mismo estilo que V2. Formato 4:5. **EVITAR:** sangre, gore, cuerpo en primer plano, dramatización.

(`gallery/v13-extintor.png` — V13, extintor)
**PROMPT:** Fotografía diegética funcional de un extintor de incendios naval, fuera de su soporte de pared, apoyado en el suelo de la sección inferior, que se ve en el soporte vacío del mismo estilo que V2. Sin daño, sangre ni indicio de violencia: simplemente fuera de lugar. Luz de emergencia, mismo estilo que V2/V12. Formato 4:5. **EVITAR:** sangre, daño, indicios de violencia.

(`gallery/v14-hoja-acceso-seccion.png` — V14, hoja de acceso)
**PROMPT:** Documento diegético tipo registro de autorización de acceso a zona restringida durante una emergencia naval, con una tabla funcional de nombres y horas de entrada y salida, en formato operativo sobrio. Tres filas, ninguna resaltada. Dejar vacías las celdas. Formato vertical. **EVITAR:** iconografía de alarma, resaltar la fila de Lucía.
**TEXTO A COMPONER:** *Nombre · Entrada · Salida* — **H. Valderrama 22:16 — sin registro de salida** · **R. Oyarzún 22:16 — 22:27** · **L. Fernández Mora 22:21 — 22:42**.

(`gallery/v15-camara-muestras.png` — V15, cámara de muestras)
**PROMPT:** Fotografía diegética funcional de una pequeña cámara de almacenamiento refrigerado dentro de la sección inferior de un buque de investigación, contenedores de muestras etiquetados ordenadamente, sin personas, luz fría funcional. Formato 4:5. **EVITAR:** daños, indicios de violencia, etiquetas legibles críticas.

(`gallery/v16-mensaje-colega-externo.png` — V16, mensaje de colega externo)
**PROMPT:** Captura de pantalla diegética de un mensaje breve de correo o mensajería profesional, formato de captura de móvil, interfaz simple y genérica, tono cordial. Dejar vacío el cuerpo del mensaje. Formato vertical. **EVITAR:** iconografía de alarma, mención explícita del fraude, logos de apps reales.
**TEXTO A COMPONER:** *De: Dr. A. Vergara (colega externo). «Lucía, ¡qué ganas de ver los resultados de la campaña! El comité de la beca está muy atento y todos esperamos que la publicación salga a tiempo. Mucho ánimo, ya casi lo tienes.»*

## 4. Audio

Los guiones están en `case.php`; se exportan listos para estudio con
`php artisan immersion:export-audio-scripts bajo-la-linea-de-flotacion`. Guardar en `audio/`.

(`audio/a1-alarma-entrada-agua.wav` — A1, anuncio de entrada de agua, 12–15 s)
**PROMPT:** Anuncio de megafonía diegético en un buque, voz adulta profesional y firme, que informa de una entrada de agua confirmada en la sección inferior de proa y del cierre de puertas estancas según protocolo; tono de emergencia controlada, sin pánico. Una breve alarma técnica naval antes del anuncio y, de fondo, motores y viento. Sin música. **EVITAR:** tono de pánico, música de tensión, sonidos de catástrofe exagerados.

(`audio/a2-radio-horacio.wav` — A2, radio de Horacio, 15–18 s)
**PROMPT:** Transmisión de radio diegética de un hombre adulto, voz cansada y funcional, desde una sección técnica de un buque durante una emergencia controlada, que menciona que una válvula dañada no parece desgaste normal y que encontró algo que no cuadra en un inventario de muestras; tono de trabajo rutinario, sin urgencia dramática. Estática de radio y bombas funcionando de fondo. Sin música. **EVITAR:** tono de amenaza o revelación dramática, música de tensión.

(A3 — confesión de Lucía) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Kore) para incluir las preguntas que esa mesa le hizo a Lucía.
