# Prompts de assets — La Última Postal

Total: **1 portada + 9 retratos + 9 evidencias visuales + 0 audios** (la confesión de Osvaldo
se genera por partida y no se graba; este caso no tiene audio dentro de la línea de tiempo,
por eso no existe la carpeta `audio/`).

Los 9 retratos son 9 archivos: **Hermelinda Casas** es a la vez la víctima del caso («Amenazada»)
y una testigo del roster, y usa **el mismo archivo** `hermelinda-casas.png` en las dos entradas.

Todo se guarda en `public/immersion/la-ultima-postal/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos |
| `gallery/` | las 9 evidencias visuales |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt. En especial la
   **biblia de la maqueta y la plaza**: de ella dependen V1, V2 y V3.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **V2 primero** (la maqueta), después **V1** (la postal) con la misma composición, y por
   último **V3** (la plaza real). Genera V1 y V2 desde la misma imagen base o con la misma
   semilla/referencia; no las generes de forma independiente.
5. El resto de evidencias en orden de entrega (ver tabla).
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1 |
| Expansión | 11 | (sin imagen) |
| Pista privada | 11 | V2 (a un jugador) |
| Desarrollo | 22 | V2 (copia para todos), V3, V5 |
| Complicación | 33 | V4 |
| Giro | 44 | V6, DC (declaración escrita de Casimiro, sin imagen) |
| Cierre | 55 | V7, C1, DA (declaración de Araceli) y RD (registro del depósito), sin imagen |
| Hilos secundarios | 55 | C2, HS (resumen, sin imagen) |
| Coartadas | 63 | (solo texto) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el
dato exacto ES la prueba (17:02 ≠ 16:00; la llave n.º 3). Para V4, V5, V6, C1 y C2: genera la
imagen con la zona de texto **vacía o con texto de relleno ilegible**, y compón después el texto
exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica cada
hora contra `case.php` antes de guardar. No hay fechas de calendario: solo días de la semana.

**Cronología maestra (todas las horas del caso salen de aquí):**
LUNES, sesión en el taller: maqueta montada 15:00; sesión 16:00–17:30; registro del taller:
Rosalinda 14:50–18:40, Casimiro 15:00–18:45, Wenceslao 15:55–17:32, Felisa 15:58–17:36,
Araceli 16:02–17:40, **Osvaldo 17:02–17:31 («Consulta»)**. · MARTES: Osvaldo envía la postal. ·
MIÉRCOLES: Hermelinda la recibe. · SÁBADO, depósito: Benjamín entra 18:05, verifica B-3 a las
19:25 (la estrella está), sale 19:30 (llave maestra al tablero 19:32); **entrada con llave de
montaje n.º 3 20:35, salida 21:05**. · DOMINGO 08:10: Benjamín descubre el hueco. · Llaves de
montaje: n.º 1 Felisa, n.º 2 Wenceslao, n.º 3 Osvaldo. · Valor de la Estrella de Plata:
US$ 15,000.

## Biblia visual de continuidad (pegar en los retratos y en las escenas)

- **Hermelinda Casas**, 50: ropa formal de inspectora municipal, blazer sobrio, credencial sin texto legible, mirada serena y precisa.
- **Osvaldo Reinares**, 55: ropa formal de comité (chaqueta y camisa), aspecto cordial y respetable, cabello entrecano.
- **Nazario Prieto**, 50: ropa de trabajo de proveedor/taller (camisa gruesa, chaleco de taller), manos de oficio.
- **Felisa Orozco**, 58: ropa formal de presidenta de comité, collar discreto, porte ejecutivo.
- **Benjamín Ustariz**, 45: uniforme de encargado de depósito (camisa de trabajo, llavero en el cinturón).
- **Wenceslao Ibarra**, 52: ropa formal de oficina, gafas, carpeta bajo el brazo.
- **Casimiro Peña**, 30: ropa de trabajo de taller, delantal con pintura.
- **Araceli Fuentes**, 44: ropa casual de miembro del comité, cárdigan, tono cercano.
- **Rosalinda Antúnez**, 48: ropa de trabajo de diseñadora de maquetas, delantal, regla o cúter en el bolsillo.
- **El pueblo navideño:** plaza central con decoración navideña cálida y realista, taller de maquetas con el modelo a escala, mismas localizaciones y proporciones en todas las piezas, sin estética de póster ni elementos fantásticos. Nada sobrenatural, nada cifrado.

### Biblia de la maqueta y la plaza (V1, V2, V3) — LÉELA ANTES DE GENERAR

**Esto es lo que hace funcionar el caso.** V1 (postal) y V2 (maqueta) son **la misma toma**:
mismo punto de vista, misma escala, mismas posiciones de cada elemento, mismo fondo. V3 (plaza
real) es una vista **parecida pero no idéntica**: el jugador debe poder verlo al compararlas,
pero solo si mira con atención. Si V1 y V3 se parecen más que V1 y V2, el caso se rompe.

**La toma común de V1 y V2** («la marca de la cámara»): punto de vista fijo en la esquina
sureste de la plaza, a la altura de los ojos de una persona sentada, lente equivalente a 35 mm,
mirando hacia el noroeste. Posiciones en el encuadre (de izquierda a derecha, de arriba abajo):

- **Mástil con la Estrella de Plata**: en el tercio derecho (a un 66 % del ancho). La estrella
  está **levantada unos centímetros por encima de la punta del mástil, inclinada unos 15
  grados**, como si alguien la estuviera sacando. Estrella de ocho puntas, plata, a un 28 % de
  la altura del cuadro.
- **Portal del pesebre** (madera) bajo el mástil, base a un 78 % de la altura.
- **Figura humana genérica, borrosa y sin rostro**, a un paso a la izquierda del mástil (a un
  55 % del ancho), con un brazo alzado hacia la estrella. En la maqueta es una figurita; en
  V1 y V2 debe verse tan desenfocada que no se note a primera vista.
- **Fachada del fondo**: edificio de tres pisos con pórtico de arcos, de borde a borde en el 45 %
  superior. **En la maqueta es un panel liso, pintado, con ventanas impresas y una junta vertical
  apenas visible** (a un 40 % del ancho).
- **Farolas con guirnalda: exactamente CUATRO**, a un 10 %, 30 %, 50 % y 88 % del ancho,
  idénticas entre sí, con la altura reduciéndose hacia la izquierda por la perspectiva.
- **Árbol con luces** a la izquierda (a un 15 % del ancho), de aspecto «de alambre y esponja» en
  la maqueta.
- **Banco** en el primer plano izquierdo.
- **Punta de la torre del campanario** asomando a un 88 % del ancho, por detrás de la fachada.
- **Texturas de maqueta que deben notarse al mirar de cerca** (pero sin resaltar): superficies
  lisas y uniformes, nieve de flocado de grano regular, adoquines como textura repetida, luces
  de las ventanas todas idénticas.

**Diferencias que debe tener V3** (la plaza real), todas sutiles pero reales:

- **Punto de vista desplazado**: la cámara está algo más a la izquierda y girada unos 4 grados,
  de modo que el mástil y la estrella caen hacia el 58 % del ancho (no el 66 %).
- **La estrella está en su sitio**, en la punta del mástil, derecha (no levantada), con el
  soporte real visible: un mástil de madera y hierro con tornillos y placa de base.
- **Fachada de piedra irregular** (sillares desiguales, balcones de hierro forjado, ventanas
  con luces distintas y alguna cortina), sin junta de panel.
- **CINCO farolas**, no cuatro, con separación algo irregular.
- **Personas reales, lejanas y borrosas** en la plaza (siluetas), no una única figura junto a la
  estrella.
- **Cielo y luz reales** (crepúsculo con nubes y reflejos en el suelo mojado), con más
  profundidad de campo y detalle que la maqueta.
- El banco, el árbol, el portal y el campanario están en la misma zona general, pero con las
  texturas y las irregularidades propias de lo real (el árbol es un árbol de verdad).

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio navideño, formato vertical de póster de catálogo (2:3): sobre un escritorio de madera, de noche, una postal fotográfica apoyada contra una lámpara de escritorio, con una estrella plateada de ocho puntas siendo retirada de su soporte en una plaza navideña cálida, ligeramente fuera de foco; al fondo, desenfocada, la silueta de una maqueta de pueblo en un taller, con luces diminutas. Sensación de misterio sereno, calidez navideña con un punto de inquietud, sin dramatismo. Debe sugerir que una imagen no es lo que parece. Estilo realista premium, sin texto integrado. **EVITAR:** texto o logos, rostros visibles, sangre, iconografía policial, elementos sobrenaturales, relojes o fechas legibles, estética de terror.

## 2. Retratos

(`photos/hermelinda-casas.png` — Hermelinda Casas; **víctima «Amenazada» y testigo**, un solo archivo)
**PROMPT:** Retrato diegético de una mujer de 50 años, inspectora de patrimonio municipal, blazer sobrio y credencial sin texto legible, expresión serena, precisa e inquieta sin dramatismo, fondo de oficina municipal con archivadores y un adorno navideño discreto. Formato retrato. **EVITAR:** apariencia de víctima, llanto, dramatización, texto legible.

(`photos/osvaldo-reinares.png` — Osvaldo Reinares)
**PROMPT:** Retrato diegético de un hombre de 55 años, ropa formal de comité (chaqueta y camisa), cabello entrecano, expresión cordial, segura y respetable, con un asomo casi imperceptible de tensión, fondo de la sala de reuniones de un comité de decoración con adornos navideños. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto amenazante, teléfono a la vista.

(`photos/nazario-prieto.png` — Nazario Prieto)
**PROMPT:** Retrato diegético de un hombre de 50 años, ropa de trabajo de proveedor (camisa gruesa y chaleco de taller), manos curtidas, expresión profesional, cordial y algo a la defensiva, fondo de un taller con estructuras de madera y cartón-piedra para decoraciones navideñas. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, texto legible.

(`photos/felisa-orozco.png` — Felisa Orozco)
**PROMPT:** Retrato diegético de una mujer de 58 años, ropa formal de presidenta de comité, collar discreto, porte ejecutivo, expresión segura y cordial con un leve gesto de cautela, fondo de despacho con carpetas y una pequeña corona navideña. Formato retrato. **EVITAR:** villana obvia, gesto de culpa, texto legible, dinero a la vista.

(`photos/benjamin-ustariz.png` — Benjamín Ustariz)
**PROMPT:** Retrato diegético de un hombre de 45 años, uniforme de encargado de depósito (camisa de trabajo), llavero en el cinturón, expresión práctica y algo nerviosa, fondo de estantes de ornamentación navideña guardada en un depósito municipal. Formato retrato. **EVITAR:** apariencia de ladrón, gesto de culpa, texto legible, la estrella a la vista.

(`photos/wenceslao-ibarra.png` — Wenceslao Ibarra)
**PROMPT:** Retrato diegético de un hombre de 52 años, ropa formal de oficina, gafas, carpeta bajo el brazo, expresión meticulosa, cordial y algo defensiva, fondo de una oficina de comité con archivadores y un calendario sin números legibles. Formato retrato. **EVITAR:** villano obvio, gesto de culpa, dinero a la vista, texto legible.

(`photos/casimiro-pena.png` — Casimiro Peña)
**PROMPT:** Retrato diegético de un hombre de 30 años, ropa de trabajo de taller con delantal manchado de pintura, expresión franca, tímida y algo incómoda, fondo de un taller de maquetas con piezas pequeñas pintadas sobre una mesa. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, la maqueta de la plaza a la vista.

(`photos/araceli-fuentes.png` — Araceli Fuentes)
**PROMPT:** Retrato diegético de una mujer de 44 años, ropa casual de miembro de comité (cárdigan), expresión cercana, atenta y algo incómoda, fondo de sala de reuniones con adornos navideños sencillos. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, texto legible.

(`photos/rosalinda-antunez.png` — Rosalinda Antúnez)
**PROMPT:** Retrato diegético de una mujer de 48 años, ropa de trabajo de diseñadora de maquetas (delantal, cúter y regla en el bolsillo), expresión metódica, segura y paciente, fondo del taller con herramientas pequeñas y una mesa de trabajo, sin la maqueta de la plaza a la vista. Formato retrato. **EVITAR:** apariencia de sospechosa, texto legible, la maqueta de la plaza a la vista.

## 3. Evidencias visuales

(`gallery/v1-postal-recibida.png` — V1, postal recibida; entrega pública a T+1)
**PROMPT:** Fotografía diegética de una **postal navideña impresa**: una fotografía de aspecto normal, con borde blanco de postal, algo de grano y una esquina ligeramente gastada, apoyada sobre una superficie neutra. La fotografía muestra la **toma común de la biblia de la maqueta y la plaza** (punto de vista en la esquina sureste, 35 mm): una estrella plateada de ocho puntas siendo retirada de su soporte, levantada unos centímetros sobre el mástil e inclinada unos 15 grados, con una figura genérica, borrosa y sin rostro junto a ella, en una plaza navideña de noche con luces cálidas, una fachada de tres pisos con pórtico al fondo, **cuatro** farolas con guirnalda, un árbol con luces a la izquierda y un banco en primer plano. Iluminación cálida y navideña. Formato horizontal, aspecto de postal 3:2. La pista debe estar visible pero no resaltada: la imagen debe poder confundirse con una foto cualquiera de la plaza real. **EVITAR:** rostros identificables, elementos sobrenaturales, iconografía de alarma, texto, estética de póster, flechas o captions.
**CONTINUIDAD (crítico):** encuadre, escala, posiciones y fondo **idénticos** a V2 (misma imagen base). Solo cambian la luz (cálida, nocturna) y el marco impreso. Debe diferir de forma sutil pero real de V3: fachada de panel liso, cuatro farolas, estrella levantada.
**TEXTO A COMPONER:** ninguno.

(`gallery/v2-maqueta-del-pueblo.png` — V2, maqueta del pueblo; privada a T+11 y copia pública a T+22)
**PROMPT:** Fotografía diegética de archivo de un taller de maquetas: la maqueta detallada a escala de la plaza navideña del pueblo, con la **misma toma, el mismo encuadre, la misma escala y el mismo fondo que V1**: estrella miniatura de ocho puntas levantada unos centímetros sobre la punta del mástil e inclinada unos 15 grados, figurita genérica borrosa a su izquierda, fachada de tres pisos con pórtico como panel liso con ventanas impresas, **cuatro** farolas idénticas con guirnalda, árbol de alambre y esponja con luces a la izquierda, banco en primer plano. Se ven las texturas propias de la maqueta (superficies lisas, nieve de flocado de grano regular, adoquines como textura repetida), sin resaltarlas. Luz artificial de taller (fluorescente neutra, más fría que V1), luces de la maqueta apagadas o muy tenues. En el 6 % inferior del cuadro asoma el borde de la mesa de trabajo con una regla metálica y un lápiz (la única diferencia de composición con V1). Formato horizontal. La pista debe estar visible pero no resaltada. **EVITAR:** iconografía de alarma, personas visibles, cualquier detalle que haga parecer un lugar distinto, captions, flechas, estética de póster.
**CONTINUIDAD (crítico):** el jugador debe poder confirmar que **ángulo y escala coinciden exactamente con V1** y que difieren de V3. Genera V2 antes que V1 y reutiliza la composición.
**TEXTO A COMPONER:** ninguno.

(`gallery/v3-plaza-real.png` — V3, vista real de la plaza; pública a T+22)
**PROMPT:** Fotografía diegética de ambiente de la plaza navideña **real** del pueblo al crepúsculo, desde un punto de vista **parecido pero no idéntico** al de V1/V2: la cámara está algo más a la izquierda y girada unos 4 grados, de modo que el mástil y la estrella caen hacia un 58 % del ancho. La Estrella de Plata está **en su sitio**, recta, en la punta del mástil de madera y hierro con tornillos y placa de base visibles. Fachada de tres pisos con pórtico de **piedra irregular** (sillares desiguales, balcones de hierro forjado, ventanas con luces distintas y alguna cortina). **Cinco** farolas con guirnalda de separación algo irregular. Árbol real con luces a la izquierda, banco en primer plano, torre del campanario al fondo. Siluetas de personas reales, lejanas y borrosas. Cielo con nubes, reflejos en el suelo mojado, más profundidad de campo y detalle que la maqueta. Formato horizontal. Mantener la pista visible pero no resaltada. **EVITAR:** iconografía de alarma, estética de póster, personas protagonistas, captions, flechas, que parezca la misma toma que V1/V2.
**CONTINUIDAD (crítico):** misma disposición general que V2 (el portal, el árbol, el banco, el campanario), con las diferencias de arriba. Debe parecerse menos a V1 que V2.
**TEXTO A COMPONER:** ninguno.

(`gallery/v4-plano-de-montaje.png` — V4, agenda semanal del taller; pública a T+33)
**PROMPT:** Documento diegético tipo agenda técnica de un taller de maquetas, impreso o digital simple, con columnas de día, hora, actividad y personas convocadas, aspecto de documento rutinario, sin resaltados ni iconos. Dejar vacías las celdas de texto. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, estética de póster, captions.
**TEXTO A COMPONER:** título *Taller de maquetas — Agenda de la semana*. Columnas: *Día · Hora · Actividad · Convocados*. Filas: **Lunes · 15:00 · Montaje de la maqueta con la réplica de la estrella levantada de su mástil · R. Antúnez, C. Peña** / **Lunes · 16:00–17:30 · Sesión de planificación: propuesta de cambio de ubicación de la estrella · R. Antúnez, F. Orozco, W. Ibarra, A. Fuentes, C. Peña** / **Lunes · 17:30 · Desmontaje de la posición de extracción · R. Antúnez, C. Peña** / **Martes · 09:00 · Pintura de fachadas de la maqueta · C. Peña** / **Jueves · 10:00 · Revisión semanal de planificación (proveedores y calendario) · F. Orozco, O. Reinares, R. Antúnez**.

(`gallery/v5-inventario-decorativo.png` — V5, inventario; pública a T+22)
**PROMPT:** Documento diegético tipo inventario municipal de ornamentación patrimonial, con columnas de pieza, descripción, categoría, valor estimado y ubicación, sello discreto de la Oficina de Patrimonio, aspecto de registro administrativo rutinario, papel o pantalla simple. Dejar vacías las celdas de texto. Formato vertical. **EVITAR:** iconografía de alarma, resaltados artificiales, fotografías de piezas, estética de póster.
**TEXTO A COMPONER:** título *Oficina de Patrimonio Municipal — Inventario de ornamentación patrimonial*. Columnas: *Pieza · Descripción · Categoría · Valor estimado · Ubicación*. Filas: **Estrella de Plata del pesebre municipal · Estrella de ocho puntas en plata repujada sobre alma de madera; remata el mástil del portal · Patrimonial · US$ 15,000 · Depósito municipal, estantería B-3** / **Portal del pesebre · Portal de madera policromada · Patrimonial · US$ 9,500 · Depósito municipal, estantería A-1** / **Figuras del pesebre · Juego de 11 piezas talladas · Patrimonial · US$ 6,800 · Depósito municipal, estantería A-2** / **Arco de luces de la plaza · Estructura metálica con guirnaldas · Decorativa · US$ 2,400 · Depósito municipal, nave 2**. El nombre «Estrella de Plata del pesebre municipal» debe ser idéntico al de la postal, V2, V3 y V7.

(`gallery/v6-registro-del-taller.png` — V6, registro del taller; pública a T+44)
**PROMPT:** Captura maquetada diegética de un registro de entradas y salidas de un taller de maquetas, sistema interno o documento impreso simple, con columnas de nombre, hora de entrada, hora de salida y motivo, aspecto de trámite rutinario de control. Dejar vacías las celdas. Ninguna fila resaltada. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, estética de póster, captions.
**TEXTO A COMPONER:** título *Taller de maquetas — Registro de entradas y salidas · Lunes*. Columnas: *Nombre · Entrada · Salida · Motivo*. Filas: **Rosalinda Antúnez · 14:50 · 18:40 · Preparación y sesión** / **Casimiro Peña · 15:00 · 18:45 · Preparación y sesión** / **Wenceslao Ibarra · 15:55 · 17:32 · Sesión de planificación** / **Felisa Orozco · 15:58 · 17:36 · Sesión de planificación** / **Araceli Fuentes · 16:02 · 17:40 · Sesión de planificación** / **Osvaldo Reinares · 17:02 · 17:31 · Consulta**.

(`gallery/v7-deposito-estanteria.png` — V7, foto del depósito; pública a T+55)
**PROMPT:** Fotografía diegética documental de la zona de almacenamiento habitual de un depósito municipal, luz interior neutra, con estantes de ornamentación navideña guardada (cajas, figuras envueltas, guirnaldas) y, en la estantería B-3, un **espacio vacío discreto**, limpio, donde debería estar una pieza de forma estrellada; sin ningún detalle dramático añadido. Un rótulo de estantería con «B-3» en una etiqueta pequeña. Formato horizontal. **EVITAR:** señales de forzamiento violento, iconografía de alarma, personas, la estrella a la vista, estética de póster, captions, flechas.
**TEXTO A COMPONER:** etiqueta pequeña de estantería: **B-3**.

(`gallery/c1-mensaje-osvaldo.png` — C1, mensaje de Osvaldo; pública a T+55)
**PROMPT:** Captura de pantalla diegética de un breve intercambio de mensajes entre dos personas, en una interfaz de mensajería simple y genérica, tono sobrio y comercial. Dejar vacíos los textos. Formato vertical, aspecto de captura de móvil. **EVITAR:** logos de apps reales, iconografía de alarma, menciones explícitas al robo o al nombre de la pieza.
**TEXTO A COMPONER:** encabezado de la conversación: *R.*; separador: *Hace tres semanas*. Mensajes: *R. 21:07:* **«¿Seguimos con lo acordado?»** / *Osvaldo Reinares 21:09:* **«Seguimos. La pieza estará lista para el intercambio antes de fin de temporada, como acordamos.»** / *R. 21:10:* **«Bien. Que no haya ruido hasta entonces.»** / *Osvaldo Reinares 21:11:* **«No lo habrá.»**

(`gallery/c2-mensaje-wenceslao.png` — C2, mensaje de Wenceslao; subtrama sin relación con el robo; pública a T+55)
**PROMPT:** Captura de pantalla diegética de un breve intercambio de mensajes entre dos personas, en una interfaz de mensajería simple y genérica, tono discreto. Dejar vacíos los textos. Formato vertical, aspecto de captura de móvil. **EVITAR:** logos de apps reales, iconografía de alarma, dinero a la vista, menciones al robo o a la estrella.
**TEXTO A COMPONER:** encabezado de la conversación: *Distribuciones Lumbre*; separador: *Hace dos semanas*. Mensajes: *Wenceslao Ibarra 18:30:* **«Sobre la licitación de iluminación: sigo atento a los plazos.»** / *Distribuciones Lumbre 18:42:* **«Gracias. Un pequeño reconocimiento si la licitación sale como esperamos.»** / *Wenceslao Ibarra 18:44:* **«Hablamos de eso después, mejor por llamada.»**

## 4. Audio

Este caso **no tiene audio en la línea de tiempo**: el único audio es la confesión de Osvaldo, que
se genera por partida (no se graba), y por eso no existe la carpeta `audio/`.

(A1 — confesión de Osvaldo) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Charon) para incluir las preguntas que esa mesa le hizo a Osvaldo. Dirección de voz: pausado, con un reconocimiento gradual; pasa de la justificación profesional al reconocimiento, sin sobreactuación ni autocompasión excesiva. Duración aproximada: 20 a 25 segundos. Sin música, con un leve ambiente navideño de fondo.
