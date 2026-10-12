# Prompts de assets — La Añada Perdida

Total: **1 portada + 9 retratos + 16 evidencias visuales + 1 audio** (más la confesión, que
se genera por partida y no se graba).

Todo se guarda en `public/immersion/la-anada-perdida/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos |
| `gallery/` | las 16 evidencias visuales |
| `audio/` | `a1-ambiente-bodega.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias V1 → V14** en orden de entrega (ver tabla). V3, V4 y V4a deben salir de la
   misma "sesión" visual (misma cava, décadas de diferencia); V11 y V12 también (misma sala de
   barricas, mismo soporte vacío en la pared). V5a es un recorte de V5 y V4a un recorte de V4.
5. **Audio** A1.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V11, A1 |
| Expansión | 12 | V2, V3 |
| Pistas privadas | 24 | V5a (inventario, fragmento), V4a (foto del terremoto, fragmento) |
| Desarrollo | 24 | V9 |
| Complicación | 36 | V4, V5, V6, V7 |
| Giro | 50 | V8, V10, V13 |
| Cierre | 62 | V12, V14 |
| Acusación | 70 | (formulario) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres, cifras ni tablas**, y en este caso
el dato exacto ES la prueba (17:46 ≠ 17:53; 12.400 ≠ 11.900). Para V1, V2, V4, V4a, V5, V5a, V6,
V7, V8, V9, V10, V13 y V14: genera la imagen con la zona de texto **vacía o con texto de relleno
ilegible**, y compón después el texto exacto indicado en "TEXTO A COMPONER" con un editor
(Canva, Figma, Photoshop). Verifica cada hora, cifra y nombre contra `case.php` y los md antes
de guardar. Calendario de ficción: la cena de cierre es el viernes 20 de septiembre a las 20:00;
la carta apareció el lunes 16/09 y el mensaje entre hermanos es del martes 17/09.

## Biblia visual de continuidad (pegar en los retratos)

- **Emilio Larrea** (víctima), 68: cabello blanco, complexión media, chaqueta de lana sobre camisa clara.
- **Ramona Larrea**, 61: cabello canoso corto, vestido sobrio de tonos tierra, collar discreto.
- **Tomás Larrea**, 35: cabello oscuro corto, camisa formal sin corbata.
- **Don Walter Espinoza**, 70: complexión robusta, delantal de cuero de bodega, manos curtidas.
- **Constanza Ibarra**, 44: cabello castaño recogido, bata de trabajo de enóloga.
- **Federico Rojas**, 52: traje formal oscuro, sin vestimenta de bodega (visitante).
- **Sofía Mella**, 28: ropa de oficina informal, carpeta de archivo en mano.
- **Jorge Paillao**, 45: ropa de trabajo de bodega.
- **Bodega Larrea**: construcción histórica de piedra y madera; sala de barricas en penumbra cálida; cava de añadas con estantes numerados; oficina con archivo histórico; sala de cata elegante y sobria; valle vinícola latinoamericano ficticio (Valle de Piedra Alta).

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin marcas ni logotipos reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio familiar y vinícola: el interior en penumbra cálida de una sala de barricas de una bodega histórica de piedra y madera, con hileras de barricas de roble y, en primer plano, una sola botella antigua de vino con etiqueta envejecida apoyada sobre una barrica, iluminada por una única luz cálida y baja; sensación de silencio, legado y algo que lleva mucho tiempo guardado, sin personas visibles. Debe sugerir que lo importante está en el pasado de la bodega. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, herramientas amenazantes, iconografía policial, estética de terror, marcas comerciales reales, texto o logos legibles en la etiqueta.

## 2. Retratos

(`photos/emilio-larrea.png` — víctima)
**PROMPT:** Retrato diegético tipo foto de archivo de un hombre de 68 años, cabello blanco, complexión media, chaqueta de lana sobre camisa clara, expresión serena, amable y algo cansada de anfitrión de una casa vinícola, fondo neutro de sala de cata o barricas desenfocada. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, marcas comerciales reales.

(`photos/ramona-larrea.png` — Ramona Larrea)
**PROMPT:** Retrato diegético de una mujer de 61 años, cabello canoso corto, vestido sobrio de tonos tierra, collar discreto, expresión cálida, protectora y ligeramente contenida, fondo neutro de oficina de bodega o casa familiar. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, sonrisa teatral.

(`photos/tomas-larrea.png` — Tomás Larrea)
**PROMPT:** Retrato diegético de un hombre de 35 años, cabello oscuro corto, camisa formal sin corbata, expresión profesional y algo ansiosa de ejecutivo joven, fondo neutro de sala de cata moderna. Formato retrato. **EVITAR:** villano obvio, gesto agresivo, aspecto de empresario de película.

(`photos/walter-espinoza.png` — Don Walter Espinoza)
**PROMPT:** Retrato diegético de un hombre de 70 años, complexión robusta, delantal de cuero de bodega, manos curtidas visibles, expresión reservada, orgullosa y algo avergonzada de maestro de oficio, fondo de cava de añadas con estantes numerados. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, herramientas en actitud agresiva, cliché de viejo gruñón.

(`photos/constanza-ibarra.png` — Constanza Ibarra)
**PROMPT:** Retrato diegético de una mujer de 44 años, cabello castaño recogido, bata de trabajo de enóloga, expresión profesional, precisa y levemente evasiva, fondo de sala de cata sobria con copas y botellas. Formato retrato. **EVITAR:** gesto de culpa obvio, sospecha teatral, pose romántica.

(`photos/federico-rojas.png` — Federico Rojas)
**PROMPT:** Retrato diegético de un hombre de 52 años, traje formal oscuro, sin vestimenta de bodega, expresión calculadora, contenida y cortés de ejecutivo de un fondo de inversión, fondo neutro de oficina con carpetas de archivo. Formato retrato. **EVITAR:** villano corporativo obvio, gesto de desprecio, estética de película de Wall Street.

(`photos/sofia-mella.png` — Sofía Mella)
**PROMPT:** Retrato diegético de una mujer de 28 años, ropa de oficina informal, carpeta de archivo en la mano, expresión atenta, ordenada y afectada, fondo de oficina de archivo con estanterías de documentos. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, lágrimas.

(`photos/jorge-paillao.png` — Jorge Paillao)
**PROMPT:** Retrato diegético de un hombre de 45 años, ropa de trabajo de bodega, expresión franca, sencilla y servicial, fondo del pasillo de una cava con cajas de botellas. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, herramientas amenazantes.

(`photos/registro-notarial-inventario.png` — registro notarial de inventario)
**PROMPT:** Imagen diegética tipo retrato funcional de un libro de registro notarial de inventario abierto sobre una mesa de madera, con tablas manuscritas y sellos discretos, luz cálida de escritorio, usado como avatar de la fuente documental. Formato retrato. **EVITAR:** texto legible crítico, sellos con nombres reales, alarmas, estética futurista.

## 3. Evidencias visuales

(`gallery/v1-plano-bodega.png` — V1, plano de la bodega)
**PROMPT:** Documento diegético tipo plano funcional simple de una bodega vinícola histórica, mostrando la sala de barricas (nivel inferior, a la que se baja por una escalera), la cava de añadas, la oficina de archivo, la sala de cata y el pasillo principal que conecta la oficina con la escalera de la sala de barricas, además de un paso interno entre la cava y la sala de cata y una escalera hacia la planta superior (comedor). Líneas esquemáticas claras, tono sobrio de documento de orientación de la propiedad. Dejar las etiquetas vacías para componerlas. Formato horizontal. **EVITAR:** accesos no mencionados, iconografía de alerta, resaltar ninguna sala.
**TEXTO A COMPONER:** etiquetas *Oficina de archivo · Pasillo principal · Escalera a sala de barricas · Sala de barricas (nivel inferior) · Cava de añadas · Paso interno · Sala de cata · Escalera a planta superior (comedor)*.

(`gallery/v11-sala-de-barricas.png` — V11, escena en la sala de barricas)
**PROMPT:** Fotografía diegética funcional de una sala de barricas de bodega, con hileras de barricas de roble en penumbra cálida y silenciosa; el foco en una zona despejada entre las barricas, con un leve desorden sugerido, un pequeño objeto caído en el suelo y, en la pared del fondo, un soporte de herramienta vacío (mismo estilo que V12). Sin cuerpo visible (puede sugerirse fuera de cuadro). Formato horizontal. **EVITAR:** sangre, gore, cuerpo en primer plano, dramatización.

(`gallery/v2-arbol-familiar.png` — V2, estructura familiar y de propiedad)
**PROMPT:** Documento diegético tipo árbol genealógico y de propiedad simple, con cajas y líneas, en un diseño funcional y sobrio de documento societario de una bodega. Sin resaltar ningún nombre. Dejar las cajas vacías para componer el texto. Formato horizontal. **EVITAR:** resaltar a algún personaje, iconografía de alerta.
**TEXTO A COMPONER:** caja superior *Horacio Larrea (†) — Fundador*; debajo, dos cajas: *Emilio Larrea — 70 %* y *Ramona Larrea — 30 %*; de la caja de Emilio cuelga *Tomás Larrea — Área comercial*.

(`gallery/v3-vendimia-1979.png` — V3, vendimia de 1979)
**PROMPT:** Fotografía diegética de archivo en sepia suave, de trabajadores de bodega en plena vendimia junto a barricas, en un viñedo histórico con ropa y herramientas de finales de los años setenta; aire de orgullo y escala. Personajes genéricos de época sin rostros protagonistas. Grano de época, luz natural de exterior. Formato horizontal. **EVITAR:** anacronismos, marcas comerciales reales, dramatización, texto legible.

(`gallery/archivo-hoja-inventario.png` — V5 parcial, entrega privada a T+24)
**PROMPT:** Fragmento de una hoja de inventario de bodega, ligeramente envejecida, cortada a la altura de la tabla, con solo las filas inferiores visibles y el borde roto por arriba; aspecto de archivo impreso. La zona de la tabla queda con texto de relleno ilegible. Formato vertical. **EVITAR:** iconografía de alerta, resaltar ninguna fila, mostrar la fila de enero.
**TEXTO A COMPONER:** columnas *Fecha · Cosecha · Existencias · Observaciones*. Solo estas filas: *20/11/1986 · 1978 · 8.100 · Conteo anual* y *20/11/1986 · 1979 · 11.900 · Conteo anual — cava dañada por sismo (marzo)*. Sin las filas de enero.

(`gallery/archivo-foto-cava.png` — V4 parcial, entrega privada a T+24)
**PROMPT:** La mitad izquierda de la fotografía de V4 (misma toma), como recorte de archivo con el borde inferior cortado: interior de una cava con barricas caídas y vidrio en el suelo, tonos desaturados de los años ochenta. Sin anotación ni fecha visible. Formato horizontal. **EVITAR:** personas heridas, contenido gráfico, texto legible.

(`gallery/v9-informe-tasacion.png` — V9, informe de tasación preliminar)
**PROMPT:** Documento diegético tipo informe de tasación preliminar de una operación de compra, aspecto de documento corporativo sobrio, impreso o digital simple, con membrete genérico de un fondo de inversión. Dejar el cuerpo vacío para componerlo. Formato vertical. **EVITAR:** iconografía de alerta, sellos de "urgente", acusaciones dramatizadas, logos reales.
**TEXTO A COMPONER:** encabezado *Fondo Horizonte Capital — Revisión documental · Informe de tasación preliminar · Operación: Bodega Larrea · Fecha: 07/08 · Preparado por: F. Rojas*. Cuerpo: *1. Procedencia — Observaciones menores sobre la documentación de procedencia de la Cosecha 1979 que requieren aclaración antes del cierre. 2. Situación financiera — Compromisos de crédito de la sociedad por conciliar con los estados presentados. 3. Resto de la colección — Sin observaciones.*

(`gallery/v4-terremoto-cava-1986.png` — V4, la cava tras el sismo)
**PROMPT:** Fotografía diegética de archivo tomada tras un sismo: el interior de una cava subterránea de bodega con barricas caídas y botellas rotas en el suelo, tonos ligeramente desaturados de fotografía de los años ochenta, luz tenue de emergencia, grano de época. Debajo, una franja o dorso de foto con una anotación manuscrita. Mismo plano que V4a pero completo. Formato horizontal. **EVITAR:** personas heridas, contenido gráfico, dramatización excesiva.
**TEXTO A COMPONER:** anotación manuscrita al pie: *Cava de añadas tras el sismo — marzo de 1986 — Archivo Bodega Larrea.*

(`gallery/v5-inventario-historico.png` — V5, inventario histórico completo)
**PROMPT:** La misma hoja de inventario que V5a (mismo diseño), completa y sin cortes, con la tabla de añadas y conteos en dos fechas de 1986, aspecto de documento de archivo impreso, ligeramente envejecido. Formato vertical. **EVITAR:** iconografía de alerta, resaltar la discrepancia, cambiar el diseño respecto a V5a.
**TEXTO A COMPONER:** columnas *Fecha · Cosecha · Existencias · Observaciones*. Filas: *15/01/1986 · 1978 · 8.200 · Conteo anual* · *15/01/1986 · 1979 · 12.400 · Conteo anual* · *20/11/1986 · 1978 · 8.100 · Conteo anual* · *20/11/1986 · 1979 · 11.900 · Conteo anual — cava dañada por sismo (marzo)*.

(`gallery/v6-comparacion-etiquetas.png` — V6, comparación de etiquetas)
**PROMPT:** Fotografía diegética de comparación simple de dos etiquetas de vino de la misma cosecha declarada, una junto a la otra sobre una superficie neutra: la de la izquierda con impresión tipográfica de época, papel crema con textura y ligero sangrado de tinta; la de la derecha con impresión offset limpia y uniforme, papel más blanco y liso con un leve brillo. La diferencia debe ser sutil pero identificable. Mismo diseño y texto en ambas; sin marcas comerciales reales. Formato horizontal. **EVITAR:** diferencia caricaturesca, marcas reales, flechas o resaltados.
**TEXTO A COMPONER:** en ambas etiquetas: *BODEGA LARREA · Reserva de la Casa · COSECHA 1979 · Valle de Piedra Alta*. Rótulo bajo la izquierda: *Muestra original (archivo, 1979)*; bajo la derecha: *Muestra de la colección actual*.

(`gallery/v7-carta-horacio.png` — V7, carta de Horacio Larrea)
**PROMPT:** Carta manuscrita en papel envejecido, caligrafía de época, fotografiada sobre una superficie de madera con luz cálida e íntima, doblada parcialmente y con un paño de tela junto a ella. Tono de confesión privada y arrepentida de alguien mayor escribiendo para la posteridad. Dejar el cuerpo ilegible para componerlo. Formato vertical. **EVITAR:** iconografía de alerta, tono acusatorio dramatizado, letras góticas exageradas.
**TEXTO A COMPONER:** *Valle de Piedra Alta, abril de 1986. — A quien herede esta casa: — El sismo de marzo nos quitó casi toda la Cosecha 1979. Lo que prometimos a nuestros clientes ya no existe, y sin ella perdemos el nombre y la casa. He decidido sustituirla: botellas de una cosecha posterior llevarán la etiqueta de 1979 y nadie lo sabrá. Lo hago por la familia y sé que no es excusa. Guardo esta carta para que quien venga después decida qué hacer con esta mentira, porque yo no tuve la fuerza de deshacerla. Perdóname, quien seas. — Horacio Larrea.*

(`gallery/v8-registro-ventas-antiguas.png` — V8, registro de ventas antiguas)
**PROMPT:** Documento diegético tipo registro de ventas históricas de bodega, con una tabla simple de fecha, comprador, cantidad y responsable de la gestión, aspecto comercial sobrio, impreso o digital, algo envejecido. Ninguna fila resaltada. Dejar las celdas vacías para componerlas. Formato vertical. **EVITAR:** iconografía de alerta, resaltar el nombre de Ramona, marcas reales.
**TEXTO A COMPONER:** título *Registro de ventas — Cosecha 1979 (extracto)*; columnas *Fecha · Comprador · Botellas · Gestión*. Filas: *12/05/1991 · Colección Montenegro · 12 · H. Larrea* · *03/10/1996 · A. Ferrer · 6 · E. Larrea* · *17/04/2001 · Dr. M. Salgado · 12 · E. Larrea* · *12/06/2007 · Colección Montenegro · 18 · R. Larrea* · *14/02/2010 · P. Olmedo · 6 · E. Larrea* · *21/03/2012 · Cavas Privadas del Sur · 24 · R. Larrea* · *05/11/2016 · Club Enófilo del Valle · 36 · R. Larrea* · *18/09/2019 · Subastas Aurelia · 24 · R. Larrea*.

(`gallery/v10-mensaje-emilio-ramona.png` — V10, mensajes tensos)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería breve entre dos personas, tono tenso pero contenido, interfaz simple y genérica de móvil, sin fotos de perfil identificables. Dejar los globos de texto vacíos para componerlos. Formato vertical. **EVITAR:** iconografía de alarma, logos de apps reales, contenido explícito del fraude.
**TEXTO A COMPONER:** encabezado *Ramona* · *Martes 17/09*. *Emilio (21:14):* «Ya lo pensé bien. El viernes, en la cena de cierre, hay que decirlo todo. No hay otra forma.» *Ramona (21:16):* «Dame un poco más de tiempo, por favor.» *Emilio (21:18):* «No hay más tiempo, Ramona. Se dice todo en la cena.» *Ramona (21:19):* «Emilio, por favor.»

(`gallery/v13-registro-acceso-barricas.png` — V13, libro de acceso a la sala de barricas)
**PROMPT:** Documento diegético tipo cuaderno de bitácora digitalizado o registro simple de acceso a una zona de la bodega, tabla funcional sobria con varias entradas del día, ninguna resaltada. Dejar las celdas vacías para componerlas. Formato vertical. **EVITAR:** iconografía de alarma, resaltados artificiales, firmas legibles de personas reales.
**TEXTO A COMPONER:** título *Sala de barricas — Libro de acceso — Viernes 20/09 (solo entradas)*; columnas *Hora de entrada · Nombre*. Filas: *08:05 · J. Paillao* · *09:40 · W. Espinoza* · *11:20 · C. Ibarra* · **17:46 · E. Larrea** · **17:53 · R. Larrea** · *19:52 · J. Paillao* (sin resaltar en la imagen; la última entrada es la de quien halló el cuerpo, y nadie más entró entre las 17:53 y las 19:52).

(`gallery/v12-mazo-embutir.png` — V12, mazo de embutir barricas)
**PROMPT:** Fotografía diegética funcional de un mazo de madera de embutir barricas (herramienta estándar de bodega), fuera de su soporte de pared, apoyado en el suelo cerca de las barricas; la superficie se ve inusualmente limpia comparada con el desgaste y el polvo leve de otras herramientas colgadas cerca. Mismo soporte vacío de pared que en V11. Luz cálida tenue de bodega. Formato 4:5. **EVITAR:** sangre, daño, marcas de violencia, resaltados artificiales sobre la limpieza.

(`gallery/v14-requerimiento-financiero.png` — V14, requerimiento financiero)
**PROMPT:** Documento diegético tipo notificación financiera personal breve, con membrete genérico de una entidad financiera, formato sobrio e impreso, sin cifras dramatizadas. Dejar el cuerpo vacío para componerlo. Formato vertical. **EVITAR:** iconografía de alerta, sellos rojos, logos de bancos reales.
**TEXTO A COMPONER:** *Banco Regional del Valle — Cobranza de Banca Personal · Notificación de requerimiento de pago · Fecha: 30/08 · Titular: Ramona Larrea · Crédito personal con garantía · Cuotas vencidas: 4 · Monto vencido: $ 38.400.000 · Plazo para regularizar: hasta el 15/10.*

## 4. Audio

Los guiones están en `case.php`; se exportan listos para estudio con
`php artisan immersion:export-audio-scripts la-anada-perdida`. Guardar en `audio/`.

(`audio/a1-ambiente-bodega.wav` — A1, la sala de barricas poco antes de la cena, 12–15 s)
**GUION (voz distante, voz masculina, tono normal):** «¿Don Emilio? La cena está casi lista, lo esperamos arriba.» Silencio sin respuesta.
**PROMPT:** Ambiente sonoro diegético de una sala de barricas de bodega: silencio cálido de interior, un leve goteo distante y crujidos suaves de madera; después, a lo lejos, una voz masculina cotidiana llamando a alguien para la cena; silencio sin respuesta. Sin música. **EVITAR:** música de tensión, tono de alarma, énfasis dramático en la voz.

(A2 — confesión de Ramona) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Kore) para incluir las preguntas que esa mesa le hizo a Ramona.
