# Prompts de assets — El reparto invisible

Total: **1 portada + 13 retratos + 16 evidencias visuales + 1 audio** (la confesión de Walter
se genera por partida y no se graba; el único audio de la línea de tiempo es la nota de voz de
Celina).

Los 13 retratos son: la víctima (Celina), los 7 sospechosos, los 3 testigos y las 2 fuentes de
alcance limitado (Hernán: pantalla del sistema de despacho; Marcela: rostro).

Las 16 evidencias son: V01, V02a, V02, V03, V04, V05, V06, V07, V08, V09, V10, V11 (12) y los 4
fragmentos del plan de turnos PT1–PT4. No tienen imagen: DR (declaración de Rogelio), IA
(informe de la auditora) y DO (declaración de Osvaldo), que son texto dentro de los sobres.

Todo se guarda en `public/immersion/el-reparto-invisible/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 13 retratos |
| `gallery/` | las 16 evidencias visuales |
| `audio/` | `a1-nota-voz-celina.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (13): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera V04 y V08 con la misma estética de
   luz de cada lugar; V01 y los cuadros de texto (PT1–PT4, V02a, V02, V06, V07, V09, V11) se
   componen fuera del generador (ver regla de oro).
5. **Audio** A1.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Apertura | 15 | (solo texto) |
| Pistas privadas | 15 | PT1, PT2, PT3, PT4 (a miembros distintos) y V02a (a otro) |
| Expansión | 30 | V06, V10 |
| Desarrollo | 45 | V02, V03 y DR (texto) |
| Complicación | 60 | V04, V08 |
| Giro | 75 | V05, V11, IA (texto), DO (texto) + audio A1 |
| Cierre | 90 | V07, V09 |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres, cifras ni tablas**, y en este
caso el dato exacto ES la prueba (21:30 ≠ 04:00; 9 pallets ≠ 6; 22:47 ≠ 20:00). Para V01, PT1,
PT2, PT3, PT4, V02a, V02, V03, V05, V06, V07, V09, V10 y V11: genera la imagen con la zona de
texto **vacía o con texto de relleno ilegible**, y compón después el texto exacto indicado en
"TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica cada hora y cada cifra
contra la cronología maestra de abajo y contra `case.php` antes de guardar.

**Estilo común de los documentos de pantalla (PT1–PT4, V02a, V02, V06, V07, V09, V11):** mismo
diseño de interfaz sobria y genérica (sin logos, sin resaltados, sin iconos de alarma), con el
mismo encabezado azul grisáceo y la misma tipografía en todos; los fragmentos PT1–PT4 son
recortes del mismo documento que V06 (misma tabla, distinto tramo).

**Cronología maestra (todas las horas y cifras del caso salen de aquí):**

- Turno de choferes 12:00–20:00. De lunes a jueves, salida de bodega 12:30 y salida del
  camión desde la base 14:00. Viernes: salida de bodega programada sábado 03:30, carga sábado
  04:00, salida del operativo sábado 05:00.
- Camiones: C-1 ruta R2 (Norte), C-2 ruta R3 (Sur), C-3 ruta R4 (Ribera).
- Rutas: R1 Bodega Central → Base de Transportistas; R2 Base → Centro Comunitario Norte; R3
  Base → Centro Comunitario Sur; R4 Base → Centro Ribera.
- Pallets de lunes a jueves: R2 3, R3 3, R4 5. Viernes (plan): R2 4, R3 4, R4 6.
- **Viernes, lo que de verdad salió de bodega:** C-3, ruta R4, **9 pallets**, **21:30**,
  validado por B. Ortiz. Carga en la base 22:00; salida prevista del camión 23:30 (el camión
  nunca salió).
- Acceso a la base (V11): choferes entran 11:52 (Walter), 11:55 (Iván), 11:58 (Teodoro), 12:03
  (Mauricio); Osvaldo entra 18:52; salen 19:58 (Iván), 20:03 (Teodoro), 20:07 (Mauricio);
  **Celina entra 22:05 (puerta principal, sin salida)**; **Walter sale 22:47 (puerta peatonal
  del muelle)**.
- Validaciones de B. Ortiz en consola de bodega, viernes noche: 21:30, 23:10, 00:20.
- Sede central: acto previo viernes 19:00–23:30. Acceso del edificio: Débora 17:40 / 00:15;
  Felisa 08:05 / 23:40.
- Hallazgo del cuerpo: sábado 04:10.
- Cifras de la campaña (6 semanas): Norte 300 canastas, 298 familias atendidas; Sur 280, 276;
  Ribera 520 canastas, 142 atenciones registradas.

## Biblia visual de continuidad (pegar en los retratos)

- **Celina Rengifo**, 50: cabello corto entrecano, chaqueta funcional con el logo genérico de la red, tablilla de verificación siempre a mano.
- **Walter Benítez**, 39: complexión fuerte, uniforme de transportista, gorra con el logo de la red; sonrisa franca y algo cansada.
- **Rodrigo Palau**, 47: aspecto ejecutivo informal, chaleco de operaciones, radio al cinto.
- **Débora Salinas**, 44: ropa cómoda de oficina, carpeta con el logo de Centro Ribera.
- **Herminia Castro**, 58: elegante, pañuelo navideño, carpeta institucional.
- **Agustina Prado**, 30: ropa creativa, cámara compacta, tablet con materiales de campaña.
- **Benjamín Ortiz**, 42: chaleco de bodega, guantes de trabajo.
- **Felisa Rondón**, 53: aspecto meticuloso, chaqueta formal, calculadora de bolsillo.
- **Osvaldo Mejía**, 60: uniforme sencillo de vigilancia, linterna al cinto.
- **Carolina Ibáñez**, 35: ropa casual de eventos, tablet con el cronograma de La Noche de Reparto.
- **Rogelio Fuentes**, 34: ropa de voluntariado, tablilla de recepción.
- **Marcela Hinojosa**, 45: aspecto profesional sobrio, carpeta de auditoría, gafas.
- **Cadena de Invierno:** bodega central con estanterías y pallets; dos centros comunitarios activos con decoración navideña y familias atendidas; una base de transportistas con camiones y muelle de carga con algunas luces navideñas; y **Centro Ribera**, una oficina pequeña y casi vacía.
- **Camiones:** furgones blancos genéricos con la franja azul de la red, matrículas ilegibles, marcados C-1, C-2, C-3 en la puerta.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales, sin marcas comerciales reales en ningún documento ni camión.
Ambientación navideña presente pero discreta; sin fechas de calendario.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria, en formato vertical de póster de catálogo, para un caso de misterio logístico en una red solidaria navideña: de noche, un muelle de carga de almacén con un camión furgón blanco a medio cargar y a medio cerrar, luz fría de trabajo y un hilo de luces navideñas al fondo; sobre el muelle, un esquema luminoso muy simple de cinco puntos conectados por cuatro líneas, uno de los puntos apagado. Sensación de espera y de ausencia, frío, sin dramatismo. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror, rostros visibles, texto o logos, letras o números legibles en el camión.

## 2. Retratos

(`photos/celina-rengifo.png` — víctima)
**PROMPT:** Retrato diegético tipo foto de credencial interna de una mujer de 50 años, cabello corto entrecano, chaqueta funcional azul grisáceo con logo genérico de una red solidaria, tablilla de verificación sujeta contra el pecho, expresión serena, atenta y algo cansada, fondo neutro de pasillo de oficina con una guirnalda navideña discreta. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales, texto legible.

(`photos/walter-benitez.png` — Walter Benítez)
**PROMPT:** Retrato diegético de un hombre de 39 años, complexión fuerte, uniforme de transportista azul grisáceo, gorra con el logo genérico de la red, sonrisa franca, cercana y algo cansada, fondo del patio de una base de transportistas con un camión blanco desenfocado. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto agresivo, texto legible.

(`photos/rodrigo-palau.png` — Rodrigo Palau)
**PROMPT:** Retrato diegético de un hombre de 47 años, aspecto ejecutivo informal, camisa remangada y chaleco de operaciones, radio al cinto, expresión segura y persuasiva con un leve gesto de cálculo, fondo de oficina de operaciones con un mapa de rutas desenfocado. Formato retrato. **EVITAR:** apariencia de villano, mirada amenazante, iluminación dramática, texto legible.

(`photos/debora-salinas.png` — Débora Salinas)
**PROMPT:** Retrato diegético de una mujer de 44 años, ropa cómoda de oficina, carpeta con el logo genérico de un centro comunitario contra el pecho, expresión cálida en la superficie y algo tensa en los ojos, fondo de oficina pequeña y ordenada con pocas cosas. Formato retrato. **EVITAR:** apariencia de culpable, gesto falso exagerado, texto legible en la carpeta.

(`photos/herminia-castro.png` — Herminia Castro)
**PROMPT:** Retrato diegético de una mujer de 58 años, elegante, pañuelo navideño discreto al cuello, carpeta institucional bajo el brazo, expresión institucional, cálida y algo defensiva, fondo de salón de actos con decoración navideña sencilla. Formato retrato. **EVITAR:** apariencia de villana, dramatización, texto legible.

(`photos/agustina-prado.png` — Agustina Prado)
**PROMPT:** Retrato diegético de una mujer de 30 años, ropa creativa y colorida, cámara compacta al cuello y tablet con materiales de campaña en la mano, expresión entusiasta con un asomo de inquietud, fondo de oficina de comunicación con carteles de campaña desenfocados. Formato retrato. **EVITAR:** apariencia de sospechosa, llanto, texto legible en la tablet.

(`photos/benjamin-ortiz.png` — Benjamín Ortiz)
**PROMPT:** Retrato diegético de un hombre de 42 años, chaleco de bodega, guantes de trabajo en la mano, expresión práctica, algo hosca y reservada, fondo de estanterías con pallets de bodega central. Formato retrato. **EVITAR:** apariencia de ladrón, mercancía en los bolsillos, gesto de culpa obvio, texto legible.

(`photos/felisa-rondon.png` — Felisa Rondón)
**PROMPT:** Retrato diegético de una mujer de 53 años, aspecto meticuloso, chaqueta formal, calculadora de bolsillo en la mano y gafas, expresión orgullosa y algo nerviosa, fondo de oficina contable con archivadores. Formato retrato. **EVITAR:** apariencia de cómplice, gesto de miedo exagerado, texto legible.

(`photos/osvaldo-mejia.png` — Osvaldo Mejía)
**PROMPT:** Retrato diegético de un hombre de 60 años, uniforme sencillo de vigilancia, linterna al cinto, libro de novedades bajo el brazo, expresión serena, formal y algo triste, fondo de una caseta de vigilancia nocturna con luz cálida de lámpara y el patio de una base de transportistas al fondo. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, texto legible.

(`photos/carolina-ibanez.png` — Carolina Ibáñez)
**PROMPT:** Retrato diegético de una mujer de 35 años, ropa casual de eventos, tablet con el cronograma de un acto contra el pecho, expresión ágil, simpática y algo agobiada, fondo de salón de actos en montaje con guirnaldas navideñas. Formato retrato. **EVITAR:** apariencia de sospechosa, texto legible en la tablet.

(`photos/rogelio-fuentes.png` — Rogelio Fuentes)
**PROMPT:** Retrato diegético de un hombre de 34 años, ropa de voluntariado, tablilla de recepción en la mano, expresión cercana, práctica y franca, fondo de un centro comunitario activo con estanterías y canastas, decoración navideña y familias desenfocadas. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, texto legible.

(`photos/hernan-casares.png` — Hernán Casares, operador de despacho)
**PROMPT:** Imagen diegética tipo retrato funcional de la consola de un sistema de despacho de una red logística, en una oficina sencilla: un monitor sobrio con una lista de camiones, rutas y horas, teclado y una taza de café al lado, usada como avatar de la fuente. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, estética futurista, texto legible, rostros.

(`photos/marcela-hinojosa.png` — Marcela Hinojosa, auditora externa)
**PROMPT:** Retrato diegético de una mujer de 45 años, aspecto profesional sobrio, carpeta de auditoría y gafas, expresión escueta, atenta e impasible, fondo de sala de reuniones neutra. Formato retrato. **EVITAR:** apariencia de jueza o acusadora, dramatización, texto legible en la carpeta.

## 3. Evidencias visuales

(`gallery/v01-mapa-red.png` — V01, mapa de la red; entrega a T+1)
**PROMPT:** Diagrama vectorial simple, tipo mapa esquemático de planificación logística, de una red de distribución: cinco nodos como rectángulos y círculos limpios conectados por cuatro líneas, sobre fondo claro con una cuadrícula muy tenue. Dejar vacías las etiquetas. Formato horizontal. **EVITAR:** nombres de calles o barrios reales, iconografía de alerta, más de cinco nodos o cuatro rutas, un sexto punto fuera del mapa, resaltados.
**TEXTO A COMPONER:** título *Cadena de Invierno — Mapa de la red*. Nodos: **Bodega Central · Base de Transportistas · Centro Comunitario Norte · Centro Comunitario Sur · Centro Ribera**. Rutas (líneas rotuladas): **R1 Bodega Central → Base de Transportistas · R2 Base de Transportistas → Centro Comunitario Norte · R3 Base de Transportistas → Centro Comunitario Sur · R4 Base de Transportistas → Centro Ribera**. Los cinco nodos del mismo tamaño y estilo (ninguno destacado). Sin distancias.

(`gallery/pt1-turnos-lunes-martes.png` — PT1, plan de turnos, lunes y martes; entrega privada a T+15)
**PROMPT:** Captura de pantalla diegética de un tramo de un plan semanal de turnos de transporte, interfaz de calendario simple y sobria con una tabla de dos filas, mismo diseño que V06. Dejar vacías las celdas. Formato horizontal. **EVITAR:** resaltados, iconografía de alarma.
**TEXTO A COMPONER:** título *Plan de turnos de transporte — Semana previa a La Noche de Reparto (tramo: lunes y martes)*. Columnas: *Día · R2 Base → Centro Norte · R3 Base → Centro Sur · R4 Base → Centro Ribera*. Filas: **Lunes · Iván Cardona · Teodoro Lemus · Walter Benítez** / **Martes · Teodoro Lemus · Mauricio Peña · Walter Benítez**. Pie: *Turno de choferes: 12:00–20:00.*

(`gallery/pt2-turnos-miercoles.png` — PT2, plan de turnos, miércoles; entrega privada a T+15)
**PROMPT:** Igual que PT1: un tramo de una fila del mismo plan de turnos.
**TEXTO A COMPONER:** mismo título con *(tramo: miércoles)*, mismas columnas. Fila: **Miércoles · Mauricio Peña · Iván Cardona · Walter Benítez**. Pie: *Turno de choferes: 12:00–20:00.*

(`gallery/pt3-turnos-jueves.png` — PT3, plan de turnos, jueves; entrega privada a T+15)
**PROMPT:** Igual que PT1: un tramo de una fila del mismo plan de turnos.
**TEXTO A COMPONER:** mismo título con *(tramo: jueves)*, mismas columnas. Fila: **Jueves · Walter Benítez · Teodoro Lemus · Iván Cardona**. Pie: *Turno de choferes: 12:00–20:00.*

(`gallery/pt4-turnos-viernes.png` — PT4, plan de turnos, viernes; entrega privada a T+15)
**PROMPT:** Igual que PT1: un tramo de una fila del mismo plan de turnos, con una línea de nota al pie.
**TEXTO A COMPONER:** mismo título con *(tramo: viernes, turnos del operativo)*, mismas columnas. Fila: **Viernes · Iván Cardona · Mauricio Peña · Walter Benítez**. Pie: *Turno de choferes: 12:00–20:00. Carga: sábado 04:00. Salida del operativo: sábado 05:00.*

(`gallery/v02a-orden-carga-parcial.png` — V02a, orden de carga, vista parcial; entrega privada a T+15)
**PROMPT:** Captura de pantalla diegética de un documento de orden de carga de almacén, interfaz de formulario funcional y sobria, con una tabla de doce filas de despachos cerrados. Dejar vacías las celdas. Formato horizontal. **EVITAR:** resaltados artificiales, iconografía de alarma, ninguna fila destacada.
**TEXTO A COMPONER:** título *Bodega Central — Orden de carga · Semana previa a La Noche de Reparto (vista parcial)*. Columnas: *Día · Ruta · Camión · Pallets · Salida de bodega · Estado · Validó*. Filas (para cada uno de lunes, martes, miércoles y jueves, tres filas idénticas salvo el día): **R2 · C-1 · 3 · 12:30 · Despachada · B. Ortiz** / **R3 · C-2 · 3 · 12:30 · Despachada · B. Ortiz** / **R4 · C-3 · 5 · 12:30 · Despachada · B. Ortiz**. Pie: *Registro cerrado hasta: jueves.*

(`gallery/v02-orden-carga.png` — V02, orden de carga completa; entrega a T+45)
**PROMPT:** Misma captura y mismo diseño que V02a, ahora con las filas del viernes añadidas. Dejar vacías las celdas. Formato horizontal. **EVITAR:** resaltados artificiales, iconografía de alarma, que la fila del viernes se vea distinta del resto.
**TEXTO A COMPONER:** mismo título sin *(vista parcial)*, mismas columnas y las mismas doce filas de lunes a jueves, más: **Viernes · R4 · C-3 · 9 · 21:30 · Despachada · B. Ortiz** / **Viernes · R2 · C-1 · 4 · sáb. 03:30 · Programada · —** / **Viernes · R3 · C-2 · 4 · sáb. 03:30 · Programada · —**. Debajo de la tabla: *Manifiesto nocturno, ruta R4 (viernes): carga en base 22:00 · salida prevista del camión 23:30.* Pie: *Validaciones en consola de bodega, viernes noche: 21:30 · 23:10 · 00:20 — B. Ortiz.*

(`gallery/v03-acta-entrega-ribera.png` — V03, acta de entrega; entrega a T+45)
**PROMPT:** Documento diegético tipo acta de recepción de mercancía, impreso, con un membrete sencillo, una lista breve de mercancía y una firma manuscrita al pie, sin ninguna anomalía visible en el propio documento; luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** iconografía de alerta, sellos de anomalía, tachaduras, resaltados.
**TEXTO A COMPONER:** membrete *Centro Ribera*. Título *Acta de recepción de mercancía*. *Semana de campaña: 5 · Ruta: R4 (Base de Transportistas → Centro Ribera).* Lista: **Canastas alimentarias · 87** / **Bolsas de ropa de abrigo · 43** / **Cajas de juguetes · 33** / **Cajas de kits de aseo · 20**. Texto: *Mercancía recibida en su totalidad, sin novedades.* Firma manuscrita, y debajo en letra impresa: **Débora Salinas — Directora, Centro Ribera**.

(`gallery/v04-oficina-visita.png` — V04, oficina de Centro Ribera; entrega a T+60)
**PROMPT:** Fotografía diegética funcional de una oficina pequeña y modesta, con algunas cajas apiladas en una esquina pero un espacio mayormente vacío y sin actividad visible, un escritorio con un ordenador y una silla, una persiana a medio bajar, sin ningún letrero llamativo, fotografiada en un momento ordinario del día con la cámara de un teléfono; luz natural de oficina, neutra y cotidiana. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, señales explícitas de abandono o ilegalidad, dramatización, texto legible. Como máximo, una silueta genérica sentada al fondo, sin rasgos reconocibles.

(`gallery/v05-contrato-distribucion.png` — V05, contrato; entrega a T+75)
**PROMPT:** Documento diegético tipo contrato de subcontratación de distribución, de una página, con cláusulas breves y genéricas y dos firmas al pie, en un formato legal sobrio, impreso; luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** nombres de empresas reales, iconografía de alerta, sellos de alarma.
**TEXTO A COMPONER:** título *Contrato de distribución y recepción de excedentes · N.º 014*. *Partes: Cadena de Invierno (representada por R. Palau, director de operaciones) y Distribuidora Hinares S.A.S.* **Primera — Objeto:** *El tercero recibirá, para su distribución comercial, lotes de mercancía que la red declare como excedente de sus centros beneficiarios.* **Segunda — Entrega:** *Los lotes se entregarán en las instalaciones del tercero, con transporte a cargo de la red.* **Tercera — Contraprestación:** *Pago por lote, dentro de las 48 horas siguientes a la entrega, por transferencia a la cuenta designada por el responsable de operaciones (terminada en 4471).* **Cuarta — Vigencia:** *Doce meses desde la firma.* Firmas manuscritas, y debajo en letra impresa: **R. Palau — Director de Operaciones, Cadena de Invierno** y **Representante legal — Distribuidora Hinares S.A.S.**

(`gallery/v06-plan-entregas.png` — V06, plan de entregas completo; entrega a T+30)
**PROMPT:** Captura de pantalla diegética de un calendario simple de campaña, interfaz sobria y funcional con una tabla de cinco filas por cuatro columnas más una nota al pie; mismo diseño que PT1–PT4 (son recortes de este documento). Dejar vacías las celdas. Formato horizontal. **EVITAR:** resaltados artificiales, iconografía de alarma, ninguna celda destacada.
**TEXTO A COMPONER:** título *Cadena de Invierno — Plan de entregas · Semana previa a La Noche de Reparto*. Columnas: *Día · R2 Base → Centro Norte · R3 Base → Centro Sur · R4 Base → Centro Ribera*. Cada celda con *chofer · pallets · salida*. Filas: **Lunes · Iván Cardona · 3 · 14:00 | Teodoro Lemus · 3 · 14:00 | Walter Benítez · 5 · 14:00** / **Martes · Teodoro Lemus · 3 · 14:00 | Mauricio Peña · 3 · 14:00 | Walter Benítez · 5 · 14:00** / **Miércoles · Mauricio Peña · 3 · 14:00 | Iván Cardona · 3 · 14:00 | Walter Benítez · 5 · 14:00** / **Jueves · Walter Benítez · 3 · 14:00 | Teodoro Lemus · 3 · 14:00 | Iván Cardona · 5 · 14:00** / **Viernes · Iván Cardona · 4 · sáb. 05:00 | Mauricio Peña · 4 · sáb. 05:00 | Walter Benítez · 6 · sáb. 05:00**. Pie: *Turno de choferes: 12:00–20:00. Viernes: carga sábado 04:00, salida del operativo sábado 05:00. Viernes 19:00–23:30: acto previo a La Noche de Reparto, sede central.*

(`gallery/v07-registro-recepcion-norte.png` — V07, registro de un centro que opera; entrega a T+90)
**PROMPT:** Captura de pantalla diegética de un formulario interno de recepción de un centro comunitario, interfaz funcional y sobria, con una tabla de seis filas y una fila de totales, con cifras consistentes y detalladas. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, ninguna fila destacada.
**TEXTO A COMPONER:** título *Centro Comunitario Norte — Registro de recepción · Campaña de invierno*. Columnas: *Semana · Canastas recibidas · Familias atendidas · Canastas entregadas · Saldo*. Filas: **1 · 50 · 50 · 50 · 0** / **2 · 50 · 49 · 49 · 1** / **3 · 50 · 51 · 51 · 0** / **4 · 50 · 48 · 48 · 2** / **5 · 50 · 50 · 50 · 2** / **6 · 50 · 50 · 50 · 2**. Totales: **300 · 298 · 298 · 2**.

(`gallery/v08-muelle-carga-noche.png` — V08, el muelle; entrega a T+60)
**PROMPT:** Fotografía diegética funcional de un muelle de carga de una base de transportistas, de noche, con un camión furgón blanco parcialmente cargado y con la compuerta sin cerrar del todo, una plataforma elevada y una tarima de carga cerca del borde, sin ningún elemento amenazante ni señal de violencia; iluminación artificial fría de trabajo, con algunas luces navideñas de fondo en el recinto; calidad de cámara de seguridad o de teléfono. Formato horizontal. **EVITAR:** señales de forcejeo evidentes, sangre, cuerpos, siluetas, elementos amenazantes, texto legible, matrícula legible.

(`gallery/v09-inventario-existencias.png` — V09, inventario; entrega a T+90)
**PROMPT:** Captura de pantalla diegética de un sencillo inventario de existencias de bodega, interfaz de hoja de cálculo simple, con una tabla de cuatro filas de categoría más una fila final. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alerta, resaltados, cálculos complejos, ninguna celda destacada.
**TEXTO A COMPONER:** título *Cierre de inventario — Campaña de invierno*. Columnas: *Categoría · Total donado · Norte · Sur · Ribera · En bodega (libros) · En bodega (conteo)*. Filas: **Alimentos (canastas) · 1.200 · 300 · 280 · 520 · 100 · 94** / **Ropa de abrigo (bolsas) · 600 · 150 · 140 · 260 · 50 · 50** / **Juguetes (cajas) · 450 · 110 · 100 · 200 · 40 · 37** / **Kits de aseo (cajas) · 300 · 80 · 70 · 120 · 30 · 29**. Fila final: **Atenciones a familias registradas (campaña) · — · 298 · 276 · 142 · — · —**.

(`gallery/v10-tablero-voluntarios.png` — V10, tablero; entrega a T+30)
**PROMPT:** Fotografía diegética de un tablero físico de corcho de comunicación interna de una oficina, con notas escritas a mano, listas de turnos de voluntariado y mensajes navideños de agradecimiento, en un ambiente cálido y comunitario propio de la temporada; luz cálida, acogedora. Dejar vacío el texto de las notas. Formato vertical. **EVITAR:** iconografía de alerta, texto generado al azar, personas.
**TEXTO A COMPONER:** dos columnas de notas bajo los rótulos **«Equipo de campo»** y **«Equipo de oficina»**. Campo: *Walter Benítez — Transporte · Iván Cardona — Transporte · Teodoro Lemus — Transporte · Mauricio Peña — Transporte · Osvaldo Mejía — Vigilancia nocturna, base · Benjamín Ortiz — Bodega central · Rogelio Fuentes — Centro Comunitario Norte · Celina Rengifo — Verificación de entregas.* Oficina: *Rodrigo Palau — Operaciones · Débora Salinas — Alianzas y Centro Ribera · Felisa Rondón — Contabilidad · Agustina Prado — Comunicación y voluntariado · Carolina Ibáñez — Eventos · Herminia Castro — Presidencia.* Nota suelta: *Acto previo a La Noche de Reparto — viernes 19:00, sede central. ¡Gracias, voluntarios!*

(`gallery/v11-registro-acceso-base.png` — V11, registro de acceso; entrega a T+75)
**PROMPT:** Captura de pantalla diegética de un sistema simple de control de accesos por tarjeta, con una lista vertical de entradas y salidas con hora, nombre, puerta y movimiento, en una interfaz funcional y sobria. Dejar vacías las celdas. Formato vertical. **EVITAR:** iconografía de alarma, imágenes de cámaras de video (el sistema es de tarjeta, no de video), resaltados, ninguna fila destacada.
**TEXTO A COMPONER:** título *Base de Transportistas — Control de acceso · Viernes*. Columnas: *Hora · Nombre · Puerta · Movimiento*. Filas, en este orden: **11:52 · W. Benítez · Puerta principal · Entrada** / **11:55 · I. Cardona · Puerta principal · Entrada** / **11:58 · T. Lemus · Puerta principal · Entrada** / **12:03 · M. Peña · Puerta principal · Entrada** / **18:52 · O. Mejía · Puerta principal · Entrada** / **19:58 · I. Cardona · Puerta principal · Salida** / **20:03 · T. Lemus · Puerta principal · Salida** / **20:07 · M. Peña · Puerta principal · Salida** / **22:05 · C. Rengifo · Puerta principal · Entrada** / **22:47 · W. Benítez · Puerta peatonal del muelle · Salida**. Pie: *Fin de la consulta: sábado 04:30.* (Verifica que entre 22:05 y 22:47 no haya ninguna otra fila y que C. Rengifo no tenga salida.)

## 4. Audio

(`audio/a1-nota-voz-celina.mp3` — A1, nota de voz de Celina; entrega a T+75)
- **Scene:** Nota de voz de móvil grabada dentro de un vehículo parado: ruido de motor y de patio de carga de fondo, sin música. Una pausa breve a mitad de la nota, con el ruido del vehículo.
- **Context:** Celina, mujer de 50 años, profesional y contenida, graba un mensaje para una colega de confianza poco antes de ir a la base de transportistas. Un leve nerviosismo al final. No acusa a nadie en concreto.
- **Speaker:** Speaker 1 - Celina Rengifo · **Voz:** Gacrux
- **Duración:** 25–30 segundos.
- **Script (solo lo que se dice):**
  > Ya tengo claro el patrón. Ribera recibe camiones completos cada semana, en el papel, pero esa oficina apenas tiene mercancía. Voy a ir esta noche a la base de transportistas, a ver quién hace esa ruta, antes de que arranque todo mañana. Si hay alguien ahí que sepa algo, prefiero hablarlo esta noche, en privado, antes de llevarlo a la junta.
  >
  > Espero que sea solo un malentendido administrativo. Pero no lo creo.
- **Dirección de voz:** profesional, contenida, con una nota de determinación tranquila; debe sonar como alguien a punto de confirmar algo que preferiría no confirmar. **EVITAR:** música dramática, tono acusatorio hacia una persona concreta.

(A2 — confesión de Walter) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Charon) para incluir las preguntas que esa mesa le hizo a Walter. Dirección de voz: empieza con pánico contenido y se estabiliza hacia un reconocimiento genuino y doloroso; alguien que reaccionó por miedo a quedar expuesto, no con frialdad. Sin música, sin sobreactuar, sin contenido violento explícito; ambiente de patio de carga nocturno de fondo.
