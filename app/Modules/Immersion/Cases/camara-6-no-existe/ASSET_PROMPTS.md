# Prompts de assets — Cámara 6 no existe

Total: **1 portada + 10 retratos + 18 evidencias visuales + 1 audio** (más la confesión de Gerardo,
que se genera por partida y no se graba).

Todo se guarda en `public/immersion/camara-6-no-existe/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos (9 roster + la persona desaparecida) |
| `gallery/` | las 18 evidencias visuales |
| `audio/` | `a1-radio-patrulla.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato y de cámara trampa.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias en orden de entrega** (ver tabla). V1, V4, V5, V6, V7 y V8 deben salir de la misma
   "sesión" visual (mismo bosque, mismos senderos, misma luz de cámara trampa).
5. **Audio** A1.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V4, A1 |
| Expansión | 13 | V2, V5, V6, V7, V15 |
| Pistas privadas | 26 | V3a (ficha parcial), V9a (nota parcial), V10a (notas de Diego parciales) |
| Complicación | 39 | V3 (completa), V9 (completa), V13 |
| Giro | 52 | V8, V10 (completas), V12, V14 |
| Cierre | 65 | V11 |
| Acusación | 75 | (formulario; sin imágenes nuevas) |

**Qué se ha cambiado del diseño original en las imágenes:** el prompt único «V5 a V7 — imágenes de las
cámaras oficiales 1–5» se convirtió en **tres imágenes distintas** (V5 = CAM-02, V6 = CAM-04, V7 =
CAM-05). CAM-01 y CAM-03 solo aparecen como filas en V1 y V2. V3, V9 y V10 tienen una versión parcial
(entrega privada, archivo `…a-…` / `…-parcial`) y una completa posterior, que son **archivos
distintos** con el mismo diseño.

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el dato exacto
ES la prueba (08:52, 14/05/2026, «CAM-06», 11/02/2026). Para V1, V2, V3a, V3, V4, V5, V6, V7, V9a, V9,
V10a, V10, V12, V13 y V14: genera la imagen con la zona de texto **vacía o con texto de relleno
ilegible**, y compón después el texto exacto indicado en «TEXTO A COMPONER» con un editor (Canva,
Figma, Photoshop). Verifica cada fecha y hora contra `case.php` antes de guardar. La imagen de
cámara trampa lleva SIEMPRE el mismo formato de sello: **«CAM-0X  DD/MM/AAAA  HH:MM:SS»**, en
tipografía monoespaciada blanca sobre una franja negra al pie.

## Biblia visual de continuidad (pegar en los retratos y en las cámaras trampa)

- **Diego Valderrama**, 38: complexión media, cabello oscuro corto, uniforme de guardaparques verde oscuro, mochila de campo.
- **Gerardo Oyarzo**, 55: complexión robusta, cabello canoso, chaqueta de director con el logo genérico de la reserva.
- **Lucas Figueroa**, 42: complexión delgada, uniforme de guardaparques con parche de «antifurtivismo».
- **Camila Reyes**, 33: cabello castaño recogido, chaleco de campo con bolsillos.
- **Tomás Bravo**, 29: uniforme de guardaparques, claramente más joven que el resto.
- **Patricia Núñez**, 46: ropa de trabajo técnico, credencial de mantenimiento al cuello.
- **Raúl Mena**, 50: ropa casual elegante, sin uniforme de la reserva.
- **Sofía Ibarra**, 35: chaleco de voluntaria de campo.
- **Don Armando Paillao**, 60: uniforme de guardaparques veterano, radio al cinto.
- **La reserva Quebrada Honda**: bosque templado denso y húmedo (coihues, robles, helechos), senderos de tierra, quebradas con pequeños **puentes de madera** sencillos, cabañas de mantenimiento de madera, oficina central funcional («Puesto Central»). Las cámaras trampa van atadas a un tronco, a baja altura.
- **Geografía de referencia** (mismo plano en V1): el *Sendero de la Quebrada* sale del Puesto Central hacia el norte, cruza dos puentes de madera (el segundo es donde está CAM-06), llega al *Cruce de la Quebrada* y sigue al *Mirador del Coihue* (punto de control 3). La *Pista del Alto* sale del Puesto Central por el este, se une al sendero en el Cruce de la Quebrada y termina en la *Cabaña del Alto* (mantenimiento), al nordeste. Las cámaras oficiales: CAM-01 Portería Sur, CAM-02 Bebedero del Arroyo, CAM-03 Paso del Roble, CAM-04 Cruce Los Maitenes, CAM-05 Laguna Chica.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos, sin
logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio ambiental: un sendero de tierra que se adentra en un bosque templado denso y húmedo al amanecer, con neblina baja entre los árboles; en primer plano y ligeramente desenfocada, una cámara trampa de conservación atada a un tronco a baja altura, con un pequeño piloto apagado; al fondo, el sendero se pierde entre la vegetación. Sensación de territorio amplio, observado solo a fragmentos, aislado y silencioso. Sin personas visibles. Formato horizontal 16:9, estilo realista premium, luz natural fría, sin texto integrado. **EVITAR:** personas, sangre, iconografía policial, estética de terror, tecnología futurista, texto o logos, cámara con número o etiqueta legible.

## 2. Retratos

(`photos/diego-valderrama.png` — la persona desaparecida)
**PROMPT:** Retrato diegético tipo foto de ficha de personal de un hombre de 38 años, complexión media, cabello oscuro corto, uniforme de guardaparques verde oscuro con el logo genérico de una reserva natural y mochila de campo asomando al hombro, expresión serena, abierta y decidida de quien conoce bien su territorio, fondo neutro de puesto de guardaparques. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales.

(`photos/gerardo-oyarzo.png` — Gerardo Oyarzo)
**PROMPT:** Retrato diegético de un hombre de 55 años, complexión robusta, cabello canoso, chaqueta de director con el logo genérico de una reserva natural, expresión autoritaria, calmada y segura, fondo de una oficina central funcional con un mapa de senderos en la pared. Formato retrato. **EVITAR:** apariencia de villano, mirada siniestra, iluminación dramática, traje de ejecutivo.

(`photos/lucas-figueroa.png` — Lucas Figueroa)
**PROMPT:** Retrato diegético de un hombre de 42 años, complexión delgada, uniforme de guardaparques con un parche de «antifurtivismo», expresión técnica, reservada y atenta, fondo de bosque denso con un sendero de tierra. Formato retrato. **EVITAR:** apariencia de culpable, equipo de espionaje visible, gesto amenazante.

(`photos/camila-reyes.png` — Camila Reyes)
**PROMPT:** Retrato diegético de una mujer de 33 años, cabello castaño recogido, chaleco de campo con bolsillos sobre ropa de trabajo, expresión profesional y algo evasiva, fondo de un laboratorio de campo sencillo con muestras y microscopio. Formato retrato. **EVITAR:** bata de laboratorio de ciencia ficción, apariencia de sospechosa, dramatización.

(`photos/tomas-bravo.png` — Tomás Bravo)
**PROMPT:** Retrato diegético de un hombre de 29 años, claramente más joven que el resto del equipo, uniforme de guardaparques, expresión nerviosa y ansiosa por no meterse en problemas, fondo del exterior de un puesto de guardaparques entre árboles. Formato retrato. **EVITAR:** apariencia de culpable, gesto de pánico, dramatización.

(`photos/patricia-nunez.png` — Patricia Núñez)
**PROMPT:** Retrato diegético de una mujer de 46 años, ropa de trabajo técnico, credencial de mantenimiento colgada al cuello, expresión meticulosa y levemente incómoda, fondo de una oficina de mantenimiento con estanterías de equipos y un ordenador. Formato retrato. **EVITAR:** apariencia de sospechosa, sospecha teatral, tecnología futurista.

(`photos/raul-mena.png` — Raúl Mena)
**PROMPT:** Retrato diegético de un hombre de 50 años, ropa casual elegante (camisa de lino, chaqueta ligera), sin uniforme de la reserva, expresión cordial y afable con un punto de cálculo, fondo de una terraza o camino rural con campo abierto detrás. Formato retrato. **EVITAR:** apariencia de villano, traje de mafioso, joyas ostentosas, gesto amenazante.

(`photos/sofia-ibarra.png` — Sofía Ibarra)
**PROMPT:** Retrato diegético de una mujer de 35 años, chaleco de voluntaria de campo, expresión amable, atenta y concreta, fondo de exterior de un puesto de guardaparques con material de campo. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/armando-paillao.png` — Don Armando Paillao)
**PROMPT:** Retrato diegético de un hombre de 60 años, uniforme de guardaparques veterano algo desgastado, radio de patrulla al cinto, expresión parca, veterana y observadora, fondo de la sala de radio de un puesto de patrullas. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, cliché de guardabosques de película.

(`photos/sistema-sensores-mantenimiento.png` — base de datos de sensores y mantenimiento)
**PROMPT:** Imagen diegética tipo retrato funcional de una pantalla de gestión de sensores de conservación, con una lista sobria de equipos con identificadores, estados y fechas, usada como avatar de la base de datos. Formato retrato. **EVITAR:** marcas reales, alertas rojas, estética futurista, texto legible crítico.

## 3. Evidencias visuales

(`gallery/v1-mapa-reserva.png` — V1, mapa de la reserva)
**PROMPT:** Documento diegético tipo mapa funcional simple de una reserva natural extensa, en líneas esquemáticas claras sobre un fondo de terreno simplificado, mostrando senderos principales, el Puesto Central, la Pista del Alto, la Cabaña del Alto (mantenimiento, remota, al nordeste), el Mirador del Coihue, el Sendero de la Quebrada con **dos pequeños puentes** dibujados y la ubicación oficial de **cinco** cámaras marcadas con círculos numerados del 1 al 5. Aspecto de mapa de orientación de la reserva. Dejar las etiquetas vacías o con texto de relleno ilegible. Formato horizontal. **EVITAR:** una sexta ubicación de cámara, ningún círculo en el Sendero de la Quebrada, iconografía de alerta.
**TEXTO A COMPONER:** *Puesto Central · Portería Sur (1) · Bebedero del Arroyo (2) · Paso del Roble (3) · Cruce Los Maitenes (4) · Laguna Chica (5) · Sendero de la Quebrada (con «1.er puente» y «2.º puente») · Cruce de la Quebrada · Pista del Alto · Mirador del Coihue — punto de control 3 · Cabaña del Alto (mantenimiento) · borde este: terrenos colindantes.* Leyenda: *● Cámara trampa oficial (1–5)*.

(`gallery/v2-lista-camaras-activas.png` — V2, lista de cámaras activas)
**PROMPT:** Captura de pantalla diegética de un sistema simple de gestión de sensores de conservación, con una tabla funcional de **cinco** cámaras activas con número, ubicación y estado «operativa», interfaz sobria. Dejar vacías las celdas de texto. Formato horizontal. **EVITAR:** una sexta fila, iconografía de alerta, marcas reales.
**TEXTO A COMPONER:** columnas *ID · Ubicación · Estado* (sin columna de última transmisión) — **CAM-01 · Portería Sur (acceso) · Operativa** — **CAM-02 · Bebedero del Arroyo · Operativa** — **CAM-03 · Paso del Roble · Operativa** — **CAM-04 · Cruce Los Maitenes · Operativa** — **CAM-05 · Laguna Chica · Operativa**. Pie: *Cámaras activas: 5.*

(`gallery/v3a-ficha-parcial.png` — V3 parcial, entrega privada a T+26)
**PROMPT:** Documento diegético tipo ficha de inventario técnico de equipo de conservación, aspecto de impreso o ficha digital sencilla, **cortado o parcial** (solo la parte superior de la ficha, rasgado o recortado a mitad del historial). Formato vertical. **EVITAR:** iconografía de alerta, mostrar los eventos de daño o retiro, lenguaje técnico complejo.
**TEXTO A COMPONER:** *Reserva Natural Quebrada Honda — Unidad de Mantenimiento de Equipos. Ficha de equipo. Identificador: **CAM-06** («Cámara 6») · Tipo: cámara trampa · Modelo: TX-200 · N.º de serie: 06-0412. Historial: **18/03/2019** — Instalación original. Ubicación: Mirador del Coihue (sector alto). **09/08/2021** — Mantenimiento preventivo, sin novedades.* (corte aquí).

(`gallery/v3-ficha-mantenimiento.png` — V3 completa, entrega a T+39)
**PROMPT:** La misma ficha que V3 parcial (mismo diseño, mismas primeras líneas), ahora completa y sin cortes, con el historial entero del equipo en filas simples. Formato vertical. **EVITAR:** cambiar el diseño respecto a V3 parcial, iconografía de alerta.
**TEXTO A COMPONER:** igual que V3 parcial, más: **22/06/2022** — Daño por temporal (carcasa y sensor). Retirada del servicio activo. **05/07/2022** — Ingreso a almacén. Estado: **REPUESTO — sin baja definitiva.** Pie: *Reasignación de ubicación: sin registro. Red activa: no incluida.*

(`gallery/v4-camara-6-ultima-imagen.png` — V4, la última imagen de Diego)
**PROMPT:** Imagen diegética de cámara trampa de conservación, ángulo fijo a baja altura, mostrando a un hombre de unos 38 años, complexión media, cabello oscuro corto, uniforme de guardaparques verde oscuro y mochila de campo, caminando **solo y con normalidad** por un sendero de tierra, a punto de cruzar un **pequeño puente de madera sobre un arroyo**, rodeado de vegetación densa y húmeda de bosque templado. Luz natural de día, algo nublada. Calidad plausible de cámara trampa (algo de grano, color ligeramente apagado). Franja negra al pie con el sello de la cámara vacía. Formato horizontal. **EVITAR:** gestos de alarma, otras personas en el encuadre, estética cinematográfica, iconografía de alerta.
**TEXTO A COMPONER:** *CAM-06  14/05/2026  08:52:07*

(`gallery/v5-cam02-bebedero.png` — V5, CAM-02 Bebedero del Arroyo)
**PROMPT:** Imagen diegética de cámara trampa de conservación, ángulo fijo a baja altura, mostrando un mamífero pequeño y común (un zorro pequeño o un roedor grande, sin especie amenazada identificable) bebiendo en la orilla de un arroyo de aguas claras, con vegetación de ribera sin personas. Luz natural de día. Mismo estilo de cámara que V4. Franja inferior vacía para el sello. Formato horizontal. **EVITAR:** personas, animales heridos, especies amenazadas explícitas, iconografía de alerta.
**TEXTO A COMPONER:** *CAM-02  09/05/2026  16:41:22*

(`gallery/v6-cam04-cruce.png` — V6, CAM-04 Cruce Los Maitenes, nocturna)
**PROMPT:** Imagen diegética de cámara trampa de conservación en **visión nocturna infrarroja** (blanco y negro verdoso, algo de grano), ángulo fijo a baja altura, mostrando un cruce de dos senderos de tierra con maitenes y vegetación, y un mamífero pequeño cruzando (por ejemplo un zorro), sin personas. Mismo estilo de cámara que V4. Franja inferior vacía para el sello. Formato horizontal. **EVITAR:** personas, animales heridos, iconografía de alerta, estética cinematográfica.
**TEXTO A COMPONER:** *CAM-04  11/05/2026  23:17:48*

(`gallery/v7-cam05-laguna.png` — V7, CAM-05 Laguna Chica)
**PROMPT:** Imagen diegética de cámara trampa de conservación, ángulo fijo a baja altura, mostrando la orilla de una laguna pequeña con juncos y un par de aves acuáticas comunes (patos, sin especie amenazada identificable), luz natural de mañana, sin personas. Mismo estilo de cámara que V4. Franja inferior vacía para el sello. Formato horizontal. **EVITAR:** personas, aves heridas, iconografía de alerta.
**TEXTO A COMPONER:** *CAM-05  12/05/2026  07:05:31*

(`gallery/v8-trampa-sendero.png` — V8, trampa vacía)
**PROMPT:** Fotografía diegética funcional de un sendero de reserva natural en bosque templado, mostrando una trampa o jaula vacía de aspecto rudimentario junto a la vegetación, sin ningún animal presente ni herido, fotografiada durante una inspección de rutina. Luz natural de día, funcional, sin dramatizar. Debe ser coherente con el bosque y el sendero de V4 (mismo tipo de vegetación y tierra). Formato 4:5. **EVITAR:** animales heridos o muertos, contenido gráfico, dramatización, texto.

(`gallery/v9a-notas-campo-parcial.png` — V9 parcial, entrega privada a T+26)
**PROMPT:** Documento diegético tipo nota de campo mecanografiada o manuscrita breve, en un cuaderno de campo funcional fotografiado sobre una mesa de trabajo, **solo un fragmento** (una entrada corta, sin firma, sin lugar y sin número de equipo). Tono profesional pero cauteloso. Formato vertical. **EVITAR:** iconografía de alerta, la mención «CAM-06» o «Cámara 6», la firma.
**TEXTO A COMPONER:** *10/02 — Sigue el movimiento raro en el tramo alto: huellas de camioneta fuera de la pista y gente entrando de noche. No voy a pedir autorización: si la solicitud pasa por la oficina, se sabe en toda la oficina. Voy a **usar el equipo viejo del almacén**.* (corte aquí).

(`gallery/v9-notas-campo-lucas.png` — V9 completa, entrega a T+39)
**PROMPT:** El mismo cuaderno de campo que V9 parcial (mismo diseño, misma letra, misma primera entrada), ahora completo y sin cortes, con tres entradas y la firma. Formato vertical. **EVITAR:** cambiar el diseño respecto a V9 parcial, iconografía de alerta, resaltados.
**TEXTO A COMPONER:** igual que V9 parcial, más: *11/02 — Retirada del almacén. Cambié carcasa y sensor; la unidad **CAM-06** sirve con repuestos.* *12/02 — Instalada junto al **segundo puente** del Sendero de la Quebrada. Sin avisar al sistema por ahora: la unidad transmite sola, con su propio módem y su ID de siempre; no la di de alta en la red activa. Revisar en dos semanas.* Firma: *L. Figueroa.*

(`gallery/v10a-notas-diego-parcial.png` — V10 parcial, entrega privada a T+26)
**PROMPT:** Documento diegético tipo notas de campo o borrador de informe sobre un escritorio sencillo, letra apretada y cuidadosa, tono profesional y preocupado, **cortado o parcial** (la parte superior de la hoja; el borde inferior rasgado), con el campo del responsable ilegible o en la parte rota. Formato vertical. **EVITAR:** el nombre del responsable, texto acusatorio dramatizado, iconografía de alerta.
**TEXTO A COMPONER:** *Cuaderno de campo — D. Valderrama. Hoja 14. 12/05 — Revisión de permisos de acceso a zonas restringidas (sector alto y norte): tres ingresos de vehículos sin registro de visita, ninguno autorizado en la planilla oficial. Pagos irregulares vinculados a esos permisos. Decidido: reportarlo a la Autoridad Nacional de Parques.* Debajo, la línea *Responsable:* **cortada por el borde rasgado** (no legible).

(`gallery/v10-notas-diego.png` — V10 completa, entrega a T+52)
**PROMPT:** La misma hoja que V10 parcial (mismo diseño, misma letra, mismas líneas), ahora completa y sin cortes. Formato vertical. **EVITAR:** cambiar el diseño respecto a V10 parcial, texto acusatorio dramatizado, resaltados.
**TEXTO A COMPONER:** igual que V10 parcial, más: *Responsable: **G. Oyarzo (dirección)**. Los permisos los firma una sola persona. Pagos mensuales: ver copias de transferencias adjuntas. Pendiente: no comentar nada en la oficina hasta tener el informe listo.*

(`gallery/v11-cabana-del-alto.png` — V11, cabaña de mantenimiento)
**PROMPT:** Fotografía diegética funcional, tomada por un dron a baja altura en ángulo oblicuo (se ven el techo, el frente y la puerta con el candado), del exterior de una cabaña de mantenimiento sencilla de madera, en un sector poco transitado de una reserva natural, al final de una pista de tierra, rodeada de vegetación densa, con la puerta cerrada y un candado simple visible, sin ningún elemento amenazante. Luz natural de día nublado, algo apagada. Debe corresponder con la «Cabaña del Alto» marcada en V1. Formato horizontal. **EVITAR:** rejas, aspecto de cautiverio, elementos amenazantes, iluminación dramática, toma a pie de puerta, texto.

(`gallery/v12-radio-diego.png` — V12, radio de Diego)
**PROMPT:** Fotografía diegética funcional de una radio portátil de patrulla de guardaparques, tirada en el suelo entre la maleza en el borde de una pista de tierra, fotografiada durante la búsqueda, con una etiqueta de identificación adhesiva en el cuerpo del aparato. Sin sangre ni indicio de violencia. Luz natural de día. Formato 4:5. **EVITAR:** sangre, indicios de violencia, resaltados artificiales, otros objetos.
**TEXTO A COMPONER:** etiqueta del aparato: *GP-07 · D. VALDERRAMA*.

(`gallery/v13-registro-movimiento.png` — V13, registro de movimiento de equipo)
**PROMPT:** Captura de pantalla diegética de un sistema simple de control de inventario técnico, con una entrada de «equipo retirado de almacén», interfaz sobria. Dejar vacías las celdas de texto. Formato horizontal. **EVITAR:** iconografía de alarma, nombre de quién lo retiró, marcas reales.
**TEXTO A COMPONER:** *Inventario técnico — Movimientos de equipo. Equipo: **CAM-06** («Cámara 6») · Movimiento: **RETIRO DE ALMACÉN** · Fecha: **11/02/2026 09:14** · Estado previo: Repuesto · Operador: sin registro · Destino: no especificado.*

(`gallery/v14-registro-transferencias.png` — V14, registro de transferencias)
**PROMPT:** Documento diegético tipo registro contable simple de transferencias, repetidas a lo largo de varios meses, con un concepto vago, en un formato financiero funcional y sobrio, sin membretes de instituciones reales. Dejar vacías las celdas. Formato vertical. **EVITAR:** iconografía de alerta, bancos reales, montos dramatizados, acusación explícita.
**TEXTO A COMPONER:** *Registro de transferencias. Ordenante: **R. Mena (Inversiones Mena)**. Beneficiario: **G. Oyarzo**. Concepto: «Consultoría». Monto: $4.800.000 cada una.* Fechas: **15/12/2025 · 15/01/2026 · 16/02/2026 · 16/03/2026 · 15/04/2026**.

(`gallery/v15-escritorio-diego.png` — V15, escritorio de Diego)
**PROMPT:** Fotografía diegética funcional de un pequeño escritorio de campo en el puesto central de guardaparques, con un cuaderno abierto (texto ilegible), una radio de repuesto con una etiqueta «REPUESTO» y un mapa de senderos marcado a mano con una línea de bolígrafo que sube por un sendero; sin la persona presente, con la sensación de una ausencia breve e inesperada. Luz cálida natural de ventana. Formato 4:5. **EVITAR:** notas de despedida, indicios de violencia, texto legible crítico, la misma radio de V12.

## 4. Audio

El guion está en `case.php`; se exporta listo para estudio con
`php artisan immersion:export-audio-scripts camara-6-no-existe`. Guardar en `audio/`.

(`audio/a1-radio-patrulla.wav` — A1, llamada de radio a Diego, 15–18 s)
**GUION:** *«Diego, ¿copiado? Punto de control tres, confirma posición.»* (estática, silencio prolongado) *«Diego, ¿me copia? Repito, confirma posición.»* (silencio).
**PROMPT:** Intercambio de radio de patrulla diegético en una reserva natural: un hombre adulto de unos 60 años, voz profesional y veterana, llama a un compañero para confirmar su posición; estática de radio y ambiente de campo (viento, aves distantes) de fondo. No hay respuesta. Repite la llamada con una seriedad creciente pero sin pánico. Sin música. **EVITAR:** música de tensión, tono de pánico explícito, respuesta del otro lado.

(A2 — confesión de Gerardo) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Orus) para incluir las preguntas que esa mesa le hizo a Gerardo. Tono: empieza racional y explicativo, se quiebra hacia el reconocimiento genuino; alguien que dejó que una situación se le fuera de las manos, no un villano calculador. Sin música ni sobreactuación.
