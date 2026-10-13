# Prompts de assets — El Candidato Perfecto

Total: **1 portada + 10 retratos + 16 evidencias visuales + 1 audio** (más la confesión de
Ramón, que se genera por partida y no se graba).

Todo se guarda en `public/immersion/el-candidato-perfecto/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos |
| `gallery/` | las 16 evidencias visuales |
| `audio/` | `a1-entrevista-diego.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). V2 y V11 deben salir de la misma "sesión"
   visual (el mismo pasillo del piso 8). V4a, V4b y V4 comparten diseño; V7a y V7 comparten
   diseño; V5 y V6 comparten membrete.
5. **Audio** A1.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V2, V12 |
| Expansión | 13 | V3, V5, V7a (matriz parcial), A1 (audio) |
| Pistas privadas | 26 | V4a (solo columna original), V4b (solo columna presentada) |
| Complicación | 39 | V4, V6, V7 (con historial), V8, V13 |
| Giro | 52 | V9, V10, V11 (+ la declaración de Paola, que es solo texto) |
| Cierre | 60 | (solo texto) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres, cifras ni tablas**, y en este caso
el dato exacto ES la prueba (7,4 ≠ 9,3; NIT ...118-3 ≠ ...181-3; salida 12:19; "verificada"
por r.quiroga). Para V1, V3, V4a, V4b, V4, V5, V6, V7a, V7, V8, V9, V10, V11 y V13: genera la
imagen con la zona de texto **vacía o con texto de relleno ilegible**, y compón después el
texto exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica
cada hora, cifra y nombre contra `case.php` antes de guardar.

Datos fijos del caso: el día es **14/11**; la franja crítica es 12:00–13:00; Mariana y Ramón
entran a la sala de archivo a las **12:11** y Ramón sale solo a las **12:19**; la reunión de
Mariana con el directorio es a las **15:00**; el anuncio estaba previsto para las 18:00.

## Biblia visual de continuidad (pegar en los retratos)

- **Mariana Ospina**, 41: cabello castaño corto, traje sastre gris, acreditación de coordinadora colgada al cuello.
- **Ramón Quiroga**, 36: complexión delgada, camisa clara sin corbata, gafas.
- **Diego Montalvo**, 42: complexión media, traje azul oscuro, seguro de sí mismo.
- **Teresa Uribe**, 55: cabello canoso elegante, traje formal burdeos.
- **Claudia Reyes**, 45: cabello negro recogido, traje pantalón azul marino.
- **Javier Peña**, 48: complexión robusta, traje gris claro, algo más informal que los demás.
- **Fernando Acosta**, 50: traje formal oscuro, gafas de lectura.
- **Hugo Bravo**, 58: traje formal gris, aspecto de consultor externo.
- **Paola Jiménez**, 33: ropa de oficina, tablet en mano.
- **Grupo Cordillera**: oficinas corporativas contemporáneas y sobrias, salas de entrevista con
  mesas ovaladas, una sala de archivo con estanterías de expedientes físicos y una puerta
  secundaria con barra antipánico, luz artificial funcional.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio corporativo: una sala de entrevistas ejecutiva vacía al caer la tarde, con una mesa ovalada de madera oscura, cinco sillas ordenadas y una sola silla ligeramente retirada, una carpeta cerrada sobre la mesa y, al fondo, grandes ventanales que dan a una ciudad en penumbra; luz cálida y fría mezcladas, sensación de espera, reputación en juego y algo que no encaja, sin personas visibles. Debe sugerir que lo importante ocurrió fuera de cuadro. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, violencia, iconografía policial, siluetas amenazantes, tecnología futurista, texto o logos.

## 2. Retratos

(`photos/mariana-ospina.png` — víctima: desaparecida)
**PROMPT:** Retrato diegético tipo foto de acreditación corporativa de una mujer de 41 años, cabello castaño corto, traje sastre gris, acreditación de coordinadora al cuello, expresión serena, atenta y algo cansada de profesional meticulosa, fondo neutro de oficina. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, miedo, logos de marcas reales.

(`photos/ramon-quiroga.png` — Ramón Quiroga)
**PROMPT:** Retrato diegético de un hombre de 36 años, complexión delgada, camisa clara sin corbata, gafas, expresión técnica, discreta y reservada de analista metódico, fondo de oficina contemporánea con monitores desenfocados. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, sudor o nerviosismo exagerado.

(`photos/diego-montalvo.png` — Diego Montalvo)
**PROMPT:** Retrato diegético de un hombre de 42 años, complexión media, traje azul oscuro, expresión segura, profesional y competente de directivo, fondo neutro de sala de entrevistas con mesa ovalada. Formato retrato. **EVITAR:** arrogancia exagerada, apariencia de farsante, sonrisa de villano.

(`photos/teresa-uribe.png` — Teresa Uribe)
**PROMPT:** Retrato diegético de una mujer de 55 años, cabello canoso elegante, traje formal burdeos, expresión ejecutiva, controlada y ligeramente distante, fondo de sala de directorio sobria. Formato retrato. **EVITAR:** aspecto de villana, frialdad caricaturesca, joyas ostentosas.

(`photos/claudia-reyes.png` — Claudia Reyes)
**PROMPT:** Retrato diegético de una mujer de 45 años, cabello negro recogido, traje pantalón azul marino, expresión profesional, algo resentida y a la defensiva, fondo neutro de sala de espera de candidatos. Formato retrato. **EVITAR:** sospecha teatral, gesto agresivo, aspecto de derrota.

(`photos/javier-pena.png` — Javier Peña)
**PROMPT:** Retrato diegético de un hombre de 48 años, complexión robusta, traje gris claro algo más informal que el resto, sin corbata ajustada, expresión franca, algo nerviosa y honesta, fondo neutro de sala de espera de candidatos. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, sudor exagerado.

(`photos/fernando-acosta.png` — Lic. Fernando Acosta)
**PROMPT:** Retrato diegético de un hombre de 50 años, traje formal oscuro, gafas de lectura, expresión metódica, cortés y algo seca de evaluador veterano, fondo de sala de entrevistas con mesa ovalada. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/hugo-bravo.png` — Lic. Hugo Bravo)
**PROMPT:** Retrato diegético de un hombre de 58 años, traje formal gris, aspecto sereno de consultor externo de recursos humanos, expresión didáctica y amable, fondo de oficina corporativa sobria. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/paola-jimenez.png` — Paola Jiménez)
**PROMPT:** Retrato diegético de una mujer de 33 años, ropa de oficina, tablet en la mano, expresión eficiente, atenta y algo inquieta, fondo de pasillo de oficinas con una puerta al fondo. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, llanto.

(`photos/sistema-seleccion.png` — sistema de selección)
**PROMPT:** Imagen diegética tipo retrato funcional de la pantalla de una plataforma de gestión de candidaturas, con listas sobrias de candidatos, estados de verificación y un historial de actividad, usada como avatar del sistema de selección. Formato retrato. **EVITAR:** marcas reales, alertas rojas, errores visibles, estética futurista, texto legible crítico.

## 3. Evidencias visuales

(`gallery/v1-organigrama-proceso.png` — V1, organigrama del proceso)(solo esta foto)
**PROMPT:** Documento diegético tipo organigrama corporativo simple, con cajas y líneas jerárquicas claras, mostrando el directorio, el comité de selección, el equipo operativo del proceso y los cinco finalistas, en un diseño funcional y sobrio de documento interno de recursos humanos. Neutro, sin señalar a nadie. Formato horizontal. **EVITAR:** resaltar a algún personaje, iconografía de alerta, colores que destaquen un nombre.
**TEXTO A COMPONER:** título *Proceso de selección — Dirección General, filial principal · Grupo Cordillera*. Cajas: **Directorio:** Teresa Uribe — Patrocinadora del proceso · **Comité de selección:** Fernando Acosta — Evaluador senior; Hugo Bravo — Evaluador (consultor externo de RR. HH.) · **Equipo del proceso:** Mariana Ospina — Coordinadora; Ramón Quiroga — Analista de verificación de referencias; Paola Jiménez — Asistente administrativa · **Finalistas:** Diego Montalvo, Claudia Reyes, Javier Peña, Andrés Lozano, Beatriz Salcedo.

(`gallery/v2-sala-de-archivo.png` — V2, sala de archivo)(solo esta foto)
**PROMPT:** Fotografía diegética funcional de una sala de archivo corporativa, con estanterías de expedientes físicos ordenados, una mesa pequeña con dos sillas y, al fondo, una puerta secundaria de servicio con barra antipánico y sin lector de tarjeta; luz artificial fría y funcional, sin personas. Mismo pasillo y estilo que V11. Formato horizontal. **EVITAR:** personas, indicios de violencia, dramatización, destacar la puerta de servicio.

(`gallery/v12-escritorio-mariana.png` — V12, escritorio de Mariana)(solo esta foto)
**PROMPT:** Fotografía diegética funcional de un escritorio de oficina ordenado, con un computador portátil cerrado, una carpeta de notas abierta (sin texto legible), una taza de café a medio terminar, un cable de carga de teléfono conectado sin teléfono, y una acreditación de coordinadora colgada del respaldo de la silla; sin la persona presente. Sensación de ausencia breve e inesperada. Luz de oficina neutra. Formato 4:5. **EVITAR:** notas de despedida, indicios de violencia, texto legible en la carpeta, teléfono sobre el escritorio.

(`gallery/v3-expediente-diego.png` — V3, carátula del expediente de Diego)(solo esta foto)
**PROMPT:** Documento diegético tipo carátula de expediente de candidatura ejecutiva, con una foto de perfil profesional de estudio de un hombre de unos 42 años de traje azul oscuro (coherente con Diego), nombre, cargo reciente y un breve resumen de trayectoria, en un formato corporativo limpio y sobrio. Dejar vacías las zonas de texto. Formato vertical. **EVITAR:** iconografía de alerta, texto que revele discrepancias.
**TEXTO A COMPONER:** *Expediente de candidatura — Dirección General · Grupo Cordillera.* **Candidato:** Diego Montalvo · 42 años. **Cargo más reciente:** Director de Operaciones Regionales — Comercializadora Andina S.A. (2016–2023). **Situación actual:** Consultor independiente. **Resumen:** Más de quince años liderando operaciones regionales en distribución y logística, con resultados sostenidos en eficiencia operativa y crecimiento. **Versión del expediente:** 2 · 22/10.

(`gallery/v5-carta-referencia.png` — V5, carta de referencia [la fabricada])(solo esta foto)
**PROMPT:** Documento diegético tipo carta de referencia profesional formal, con membrete corporativo genérico, un párrafo breve elogiando el desempeño de un excolaborador y una firma manuscrita al final, aspecto de carta impresa A4 fotografiada sobre un escritorio, luz neutra de oficina. Debe parecer completamente auténtica y convincente. Formato vertical. **EVITAR:** cualquier indicio visual obvio de falsificación, iconografía de alerta. (Las diferencias con el membrete real solo se verán al compararla con V6.)
**TEXTO A COMPONER:** membrete *COMERCIALIZADORA ANDINA S.A. · NIT 900.412.181-3 · Av. Las Américas 540, Piso 9*. Fecha: *6 de noviembre*. «A quien corresponda: Por medio de la presente certifico que Diego Montalvo trabajó en nuestra compañía entre 2016 y 2023, liderando la operación regional con resultados sobresalientes en crecimiento y eficiencia. Lo recomiendo sin reservas para cargos de dirección general.» (La carta NO nombra el cargo ni el título —ni «Gerente» ni «Director»—: así no certifica justo el título que Diego infló, y la alteración del título sigue siendo solo de Diego.) Firma (trazo limpio, vertical y redondeado): **Rodrigo Valdés Arango · Presidente Ejecutivo**.

(`gallery/v7a-matriz-evaluacion.png` — V7 parcial, matriz de evaluación a T+13)(solo esta foto)
**PROMPT:** Documento diegético tipo matriz de evaluación de candidatos ejecutivos, con una tabla simple de cuatro categorías y cinco candidatos con puntuaciones numéricas y un promedio, en un formato de documento corporativo funcional (captura de sistema simple). Neutro, sin resaltados ni columnas de historial. Formato horizontal. **EVITAR:** iconografía de alerta, resaltar a un candidato, columna de historial de cambios (esa es V7).
**TEXTO A COMPONER:** columnas *Candidato · Liderazgo estratégico · Experiencia técnica · Comunicación · Gestión financiera · Promedio*. Filas: **Diego Montalvo** 9,3 · 8,4 · 8,6 · 7,8 · 8,53 — **Claudia Reyes** 8,4 · 8,1 · 8,6 · 7,7 · 8,20 — **Javier Peña** 7,6 · 8,5 · 7,2 · 8,2 · 7,88 — **Andrés Lozano** 7,2 · 7,0 · 7,8 · 7,5 · 7,38 — **Beatriz Salcedo** 6,9 · 7,4 · 8,0 · 7,1 · 7,35.

(`gallery/v4a-version-original.png` — V4 fragmento A, a T+26)(solo esta foto)
**PROMPT:** Documento diegético tipo comparación de texto entre dos versiones de un expediente, en dos columnas paralelas, del que solo se ve la **columna izquierda** ("Versión original"); la derecha está cortada, borrada o fuera de la hoja, como fragmento. Diseño funcional y sobrio, el mismo que V4 y V4b. Formato horizontal. **EVITAR:** iconografía de alerta, mostrar la columna derecha, dramatización.
**TEXTO A COMPONER:** encabezado *Comparación de versiones — Expediente D. Montalvo (v1 ↔ v2)*. Columna **Versión original (v1 · 15/10)**: *Empresa:* Comercializadora Andina S.A. · *Periodo:* 2016–2023 · *Cargo:* **Gerente Regional de Operaciones** · *Descripción:* Dirección de la operación regional zona sur: planificación, equipos y resultados.

(`gallery/v4b-version-presentada.png` — V4 fragmento B, a T+26)(solo esta foto)
**PROMPT:** Mismo documento de comparación que V4a (mismo diseño y misma maqueta), pero del que solo se ve la **columna derecha** ("Versión presentada"); la izquierda está cortada o fuera de la hoja, como fragmento. Formato horizontal. **EVITAR:** iconografía de alerta, mostrar la columna izquierda, cambiar el diseño respecto a V4a.
**TEXTO A COMPONER:** encabezado igual que V4a. Columna **Versión presentada (v2 · 22/10)**: *Empresa:* Comercializadora Andina S.A. · *Periodo:* 2016–2023 · *Cargo:* **Director de Operaciones Regionales** · *Descripción:* Dirección de la operación regional zona sur: planificación, equipos y resultados.

(`gallery/v4-comparacion-titulo.png` — V4 completa, a T+39)(solo esta foto)
**PROMPT:** El mismo documento de comparación de V4a y V4b (mismo diseño), ahora completo, con las dos columnas paralelas visibles y sin cortes. Formato horizontal. **EVITAR:** iconografía de alerta, cambiar el diseño respecto a V4a/V4b, resaltar la diferencia de forma dramática.
**TEXTO A COMPONER:** idéntico a V4a + V4b, con las dos columnas lado a lado, y al pie, en pequeño: *Generado por el sistema de selección el 22/10 a las 18:03 · Campos modificados: 1 (Cargo).*

(`gallery/v6-comparacion-membrete.png` — V6, comparación de membretes)(solo esta foto)
**PROMPT:** Documento diegético tipo comparación visual lado a lado de dos membretes corporativos con una diferencia sutil pero identificable, etiquetados como "muestra de referencia real de la empresa" y "carta presentada en el expediente", con aspecto de documento técnico de verificación. Misma maqueta de membrete que V5. Formato horizontal. **EVITAR:** iconografía de alerta, diferencia demasiado obvia o caricaturesca, flechas o círculos rojos.
**TEXTO A COMPONER:** a la izquierda, **Muestra de referencia real de la empresa (carta archivada de 2021):** membrete *COMERCIALIZADORA ANDINA S.A. · NIT 900.412.118-3 · Av. Las Américas 450, Piso 9*; firma de Rodrigo Valdés Arango con trazo inclinado y larga línea final. A la derecha, **Carta presentada en el expediente:** membrete *COMERCIALIZADORA ANDINA S.A. · NIT 900.412.181-3 · Av. Las Américas 540, Piso 9*; firma de Rodrigo Valdés Arango con trazo vertical y redondeado. Pie: *Comparación elaborada durante la verificación final — M. Ospina.*

(`gallery/v7-matriz-historial-cambios.png` — V7 completa, a T+39)(solo esta foto)
**PROMPT:** La misma matriz de evaluación que V7a (mismo diseño y mismos números), ahora con una sección o columna de "Historial de cambios" que muestra, para Diego en liderazgo estratégico, un valor original y un valor final distintos. Formato horizontal. **EVITAR:** cambiar el diseño respecto a V7a, resaltar el cambio de forma dramática, iconografía de alerta, mostrar el nombre del usuario (eso lo da V8).
**TEXTO A COMPONER:** igual que V7a, más la sección *Historial de cambios*: **Diego Montalvo · Liderazgo estratégico — valor original 7,4 (ronda inicial, 05/11) · valor final 9,3 · modificado el 11/11 a las 21:40**.

(`gallery/v8-registro-sistema.png` — V8, historial de actividad del sistema)(solo esta foto)
**PROMPT:** Captura de pantalla diegética de un sistema simple de gestión de candidaturas, con una tabla de historial de actividad (fecha y hora, usuario, acción, detalle) en una interfaz sobria y funcional, con varias entradas sin resaltar ninguna. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados sobre nombres concretos, interfaz futurista, logos de software real.
**TEXTO A COMPONER:** columnas *Fecha · Hora · Usuario · Acción · Detalle*. Filas: 15/10 09:12 d.montalvo Documento subido — D. Montalvo: expediente v1 · 16/10 11:40 c.reyes Documento subido — C. Reyes: expediente v1 · 17/10 14:05 j.pena Documento subido — J. Peña: expediente v1 · 22/10 18:03 d.montalvo Documento subido — D. Montalvo: expediente v2 (cargo actualizado) · 05/11 17:20 comité Puntuación registrada — Matriz de evaluación, ronda inicial · 08/11 11:20 r.quiroga Referencia marcada como verificada — J. Peña · 08/11 12:15 r.quiroga Verificación de experiencia — C. Reyes, «Expansión Norte»: confirmada parcialmente · 08/11 16:41 r.quiroga Documento subido — D. Montalvo: carta de referencia (Comercializadora Andina S.A.) · 08/11 16:42 r.quiroga Referencia marcada como verificada — D. Montalvo · 11/11 21:40 t.uribe Puntuación modificada — D. Montalvo, Liderazgo estratégico · 14/11 09:05 m.ospina Verificación final iniciada — Expedientes de los finalistas.

(`gallery/v13-mensaje-ramon-teresa.png` — V13, mensajes entre Teresa y Ramón)(solo esta foto)
**PROMPT:** Captura de pantalla diegética de una conversación breve de mensajería profesional entre dos personas, formato de captura de móvil, interfaz simple y genérica, tono cordial pero con presión ambigua. Formato vertical. **EVITAR:** mención explícita de falsificación o daño, iconografía de alarma, logos de apps reales.
**TEXTO A COMPONER:** encabezado *Teresa Uribe · 04/11* (la fecha debe ser anterior al 06/11, la de la carta falsa que este mensaje supuestamente motiva; nunca posterior al 08/11, cuando Ramón la subió). **Teresa (08:12):** «Ramón, buenos días. ¿Cómo van las verificaciones? Quiero que el proceso salga bien y que no haya complicaciones antes del anuncio.» **Ramón (08:20):** «Buenos días, Teresa. Todo bajo control. Voy cerrando las referencias, no habrá complicaciones antes del anuncio.» **Teresa (08:21):** «Perfecto. Confío en ti.»

(`gallery/v9-notas-informe-mariana.png` — V9, notas del informe de Mariana)(solo esta foto)
**PROMPT:** Documento diegético tipo notas de trabajo breves, mecanografiadas o manuscritas, sobre una irregularidad detectada en una carta de referencia, con tono profesional y preocupado, fotografiadas sobre un escritorio de oficina con luz neutra. Formato vertical. **EVITAR:** iconografía de alerta, nombres completos de Ramón o de cualquier responsable, dramatización.
**TEXTO A COMPONER:** *Notas — informe final al directorio (15:00).* • Carta de referencia de Diego M. (Comercializadora Andina): llamé a la empresa a las 09:40. No tienen registro de haber emitido una carta con esa fecha. • Membrete y firma no coinciden con la muestra real (ver comparación). • Ya figura como «verificada» en el sistema desde el 08/11. Averiguar con qué base. • Un candidato me comentó hace unos días que el cargo no encajaba con lo que sabe de esa empresa. Lo comprobé yo por otra vía. • Incluir en el informe. Presentar capturas.

(`gallery/v10-agenda-reunion-directorio.png` — V10, agenda de Mariana)(solo esta foto)
**PROMPT:** Captura de pantalla diegética de un calendario corporativo, mostrando una entrada programada para esa tarde con un título neutro y una lista breve de asistentes genéricos del directorio, en una interfaz de calendario simple y funcional. La reunión aparece como pendiente, no cancelada. Formato vertical. **EVITAR:** iconografía de alarma, marcar la entrada como cancelada, logos de software real.
**TEXTO A COMPONER:** fecha **14/11** (sin día de la semana). Entrada: **15:00–16:00 · Presentación de informe final — Proceso de selección** · Sala del directorio, piso 12 · Organiza: M. Ospina · Asistentes: Directorio (4) · Estado: *Confirmada*.

(`gallery/v11-registro-acceso-archivo.png` — V11, registro de acceso a la sala de archivo)(solo esta foto)
**PROMPT:** Captura de pantalla diegética de un sistema simple de control de accesos de oficina, con una tabla de entradas y salidas por tarjeta de la sala de archivo, en una interfaz sobria y genérica, sin resaltar ninguna fila. Mismo pasillo y estilo que V2. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, interfaz futurista.
**TEXTO A COMPONER:** encabezado *Control de accesos — Sala de archivo, piso 8 — 14/11*. Columnas *Hora · Evento · Tarjeta*. Filas: **08:02 Entrada — Personal de archivo** · **08:47 Salida — Personal de archivo** · **12:11 Entrada — R. Quiroga** · **12:11 Entrada — M. Ospina** · **12:19 Salida — R. Quiroga**. No debe haber ninguna otra fila de salida.

## 4. Audio

El guion está en `case.php`; se exporta listo para estudio con
`php artisan immersion:export-audio-scripts el-candidato-perfecto`. Guardar en `audio/`.

(`audio/a1-entrevista-diego.wav` — A1, fragmento de la entrevista de Diego, 12–15 s)
**GUION:** «Creo que mi experiencia liderando equipos regionales me da una perspectiva muy concreta de los desafíos operativos que enfrenta esta filial. No vengo con teoría, vengo con resultados medibles.» (voz Orus)
**PROMPT:** Fragmento diegético de una entrevista de trabajo ejecutiva: un hombre adulto con voz segura y profesional que describe su experiencia de liderazgo con confianza genuina y sin nerviosismo, tono competente y natural; ambiente de sala de entrevistas silenciosa con un leve eco, sin música. **EVITAR:** tono sospechoso o nervioso, música de tensión, arrogancia exagerada.

(A2 — confesión de Ramón) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Iapetus) para incluir las preguntas que esa mesa le hizo a Ramón. Dirección de voz si se quisiera ensayar: empieza racional y explicativo, se quiebra progresivamente; debe sonar a alguien que dejó que una situación se le fuera de las manos, no a un villano calculador.
