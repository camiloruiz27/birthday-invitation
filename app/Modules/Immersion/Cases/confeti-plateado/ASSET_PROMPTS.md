# Prompts de assets — Confeti plateado

Total: **1 portada + 5 retratos (4 personas + 1 ficha de la parte afectada) + 6 evidencias
visuales + 0 audios** (caso de final clásico: sin confesión; no hay grabaciones en la línea de
tiempo, por eso no existe carpeta `audio/`).

Todo se guarda en `public/immersion/confeti-plateado/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 5 retratos (incluida la ficha de Nébula, que hace de «víctima») |
| `gallery/` | las 6 evidencias visuales |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero. Define los dos logotipos ficticios (Nébula y Aurea)
   ANTES de generar V01, V02 y V03: el acento dorado debe ser idéntico en V01 y en el confeti
   que se ve en cualquier otra pieza.
2. **Portada**.
3. **Retratos** (5): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera V02 primero y V01 después, igualando el
   emblema y el tono plateado.
5. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Pista privada (a un jugador) | 6 | V03a (historial del pedido, vista parcial) |
| Giro | 12 | V02, V03 (historial completo), V04, V05 |
| Cierre | 19 | (solo texto; reutiliza V02 y V01 en la galería) |
| Acusación | 25 | (formulario) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni cuentas**, y aquí el dato exacto ES
la prueba (30/12 · 22:47 · m.lazo). Para V01 (logotipo), V02, V03a, V03, V04 y V05: genera la
imagen con las zonas de texto **vacías o con texto de relleno ilegible**, y compón después el
texto exacto indicado en «TEXTO A COMPONER» con un editor (Canva, Figma, Photoshop). Verifica cada
fecha y hora contra esta hoja antes de guardar.

**Cronología maestra (todas las fechas y horas salen de aquí; dd/mm, sin año):** pedido creado y
muestra aprobada **16/12** (pedido 16:20) · mensaje con Carla: la semana anterior a la fiesta,
sin fecha visible · edición del archivo **30/12 22:47**, cuenta **m.lazo** · cierre de recepción de
archivos **23:00** · envío a producción **23:05** · entrega de cajas a Tomás **31/12 08:30** ·
cuenta regresiva: esa noche.

## Biblia visual de continuidad (pegar en los retratos y en las evidencias)

- **Mariana Lazo**, 34: ropa de oficina elegante, lanyard de Consultora Vértice, cabello castaño recogido, sonrisa profesional.
- **Tomás Ferreira**, 41: ropa cómoda de logística, chaleco con el logo de la consultora, complexión robusta, aire relajado.
- **Joaquín Beltrán**, 38: traje de oficina, móvil en la mano, expresión tensa.
- **Delia Suárez**, 29: ropa de trabajo de imprenta, delantal con manchas de tinta, cabello recogido bajo un pañuelo.
- **Consultora Vértice:** oficinas contemporáneas; salón de eventos decorado para fin de año (guirnaldas plateadas, escenario); almacén de decoración ordenado; imprenta externa pequeña y funcional (**Tinta Norte**, ficticia).
- **Logotipo de Nébula (ficticio):** vector simple, una estrella de cuatro puntas con un pequeño remolino, en gris grafito sobre plata; sin rostro de marca, sin estética de marca real. Es el emblema discreto y repetido en cada pieza de confeti correcta.
- **Logotipo de Aurea (ficticio):** vector simple, un semicírculo dorado con rayos cortos y la palabra «AUREA» en mayúsculas finas, en dorado. Es el acento dorado de las piezas «equivocadas». Debe ser idéntico en V01 y en V03/V05 si aparece.
- **El confeti:** piezas circulares y rectangulares plateadas, lisas, cada una con el micro-emblema de Nébula; alrededor de una de cada cuatro lleva además el acento dorado de Aurea.
- **Sistemas y documentos:** interfaz sobria, sin iconografía de alerta ni resaltados artificiales.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos, sin
logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio empresarial con humor ligero: un puñado de confeti plateado cayendo en cámara lenta sobre un escenario de salón de eventos a medianoche, con desenfoque de luces de fiesta al fondo; entre las piezas plateadas, unas pocas piezas con un pequeño reflejo dorado. Sensación de celebración terminada y de detalle fuera de lugar. Formato vertical de póster de catálogo, estilo realista premium, sin texto integrado. **EVITAR:** texto, logotipos legibles, personas reconocibles, marcas reales, iconografía policial, sangre, estética de terror.

## 2. Retratos

(`photos/nebula-patrocinador.png` — la parte afectada: Nébula, que hace de «víctima»)
**PROMPT:** Ficha corporativa diegética sobria, cuadrada, con el logotipo ficticio de Nébula (estrella de cuatro puntas con un pequeño remolino, gris grafito sobre plata) centrado sobre un fondo plateado liso con un sutil patrón repetido del mismo emblema, como una tarjeta de patrocinador de evento. Sin rostros. Formato retrato. **EVITAR:** marcas reales, texto distinto del logotipo, iconografía de alerta, el acento dorado de Aurea.

(`photos/mariana-lazo.png` — Mariana Lazo)
**PROMPT:** Retrato diegético de una mujer de 34 años, ropa de oficina elegante, lanyard de Consultora Vértice, cabello castaño recogido, expresión profesional y simpática, con un asomo de tensión en la sonrisa, fondo de oficina contemporánea con pantallas apagadas. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, texto legible en el lanyard.

(`photos/tomas-ferreira.png` — Tomás Ferreira)
**PROMPT:** Retrato diegético de un hombre de 41 años, complexión robusta, ropa cómoda de logística, chaleco con el logo genérico de la consultora, expresión relajada y algo distraída, fondo de almacén de decoración con cajas apiladas y guirnaldas. Formato retrato. **EVITAR:** apariencia de sospechoso, gesto de culpa, texto legible, marcas reales.

(`photos/joaquin-beltran.png` — Joaquín Beltrán)
**PROMPT:** Retrato diegético de un hombre de 38 años, traje de oficina, móvil en la mano, expresión profesional pero incómoda y a la defensiva, fondo de sala de reuniones con una pared de cristal. Formato retrato. **EVITAR:** apariencia de culpable, villano obvio, texto legible en la pantalla del móvil, marcas reales.

(`photos/delia-suarez.png` — Delia Suárez)
**PROMPT:** Retrato diegético de una mujer de 29 años, ropa de trabajo de imprenta, delantal con manchas de tinta, cabello recogido bajo un pañuelo, expresión práctica y directa, fondo de una imprenta pequeña y funcional con una máquina de impresión y rollos de papel. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, texto legible, marcas reales.

## 3. Evidencias visuales

(`gallery/v01-confeti-recuperado.png` — V01, confeti recuperado; entrega a T+0)
**PROMPT:** Fotografía diegética de un puñado de confeti plateado recogido del suelo de un escenario tras una fiesta, fotografiado de cerca sobre una superficie neutra y lisa (una mesa de oficina clara). Todas las piezas son plateadas con un diminuto emblema repetido de Nébula (estrella de cuatro puntas con remolino, gris grafito); aproximadamente una de cada cuatro piezas lleva además un pequeño acento dorado con un semicírculo con rayos cortos y la palabra «AUREA». Cámara de móvil, ligero desenfoque de cercanía, luz de oficina cotidiana, sin dramatizar. Formato cuadrado. Dejar las piezas con acento dorado con la zona del logotipo en blanco o ilegible: el logotipo se compone aparte. **EVITAR:** marcas reales, iconografía de alerta, resaltar el acento dorado de forma artificial (círculos, flechas, brillo), manos o personas.
**TEXTO A COMPONER:** el logotipo de Aurea sobre 3 o 4 piezas, pequeño pero legible: semicírculo dorado con rayos cortos y la palabra **AUREA** en mayúsculas finas doradas. El micro-emblema de Nébula (vector) sobre las piezas plateadas. **El acento dorado debe ser idéntico en V01 y, si aparece, en V03a/V03/V05.**

(`gallery/v02-muestra-aprobada.png` — V02, muestra aprobada; entrega a T+12)
**PROMPT:** Documento visual tipo ficha de aprobación de diseño de papelería comercial sobria, fotografiado o escaneado, formato vertical: arriba una muestra física de confeti plateado liso pegada con cinta sobre el papel (sin ningún acento dorado), cada pieza con el diminuto emblema de Nébula; debajo, un recuadro con un sello redondo de «aprobado» con fecha y espacio para firmas. Luz neutra de oficina. Dejar vacías las zonas de texto y el sello. **EVITAR:** iconografía de alerta, marcas reales, cualquier acento dorado, resaltados artificiales. **Continuidad:** el emblema y el tono plateado deben coincidir con las piezas plateadas de V01.
**TEXTO A COMPONER:** título *Consultora Vértice — Ficha de aprobación de diseño*. Campos: *Material:* **Confeti plateado — cuenta regresiva** · *Diseño:* **Plateado liso con emblema Nébula** · *Cantidad:* **40 cajas**. Sello circular: **APROBADO · 16/12**. Líneas de firma: *Vértice — Patrocinios* y *Nébula*.

(`gallery/v03a-historial-pedido-parcial.png` — V03a, vista parcial; entrega PRIVADA a un jugador a T+6)
**PROMPT:** Captura maquetada, horizontal, de un sistema simple de gestión de pedidos de imprenta visto desde una cuenta sin permiso de edición: una ficha de pedido con un historial de movimientos de dos filas (fecha, hora, descripción) y una columna de cuenta cuyo contenido aparece atenuado y restringido. Interfaz sobria y funcional, neutra de pantalla. Mismo diseño que V03, con la columna de cuenta restringida y sin los datos de la cabecera de usuario. Dejar vacío el texto. **EVITAR:** resaltados artificiales, iconografía de alarma, mostrar la cuenta.
**TEXTO A COMPONER:** barra superior *Tinta Norte — Gestión de pedidos*. Cabecera: *Pedido Nº 4471 · Consultora Vértice · Confeti plateado — cuenta regresiva · 40 cajas · Estado: Entregado*. Historial, columnas *Fecha · Hora · Movimiento · Cuenta*: **16/12 · 16:20 · Pedido creado, archivo de diseño v1 · [restringido]** / **30/12 · 22:47 · Archivo de diseño reemplazado por v2 · [restringido]**. Pie en letra pequeña: *Vista sin permiso de edición: algunas columnas no se muestran.*

(`gallery/v03-historial-pedido.png` — V03, historial completo; entrega a T+12)
**PROMPT:** Captura maquetada, horizontal, de un sistema simple de gestión de pedidos de imprenta, mostrando el pedido del confeti con un historial de edición visible de dos entradas: la creación original y una edición posterior, con fecha, hora y nombre de cuenta, y una cabecera con los datos de entrega. Interfaz funcional y sobria, neutra de pantalla; mismo diseño que V03a, ahora sin restricciones. Dejar vacío el texto. **EVITAR:** resaltados artificiales, iconografía de alarma, marcar la fila de la edición con un color distinto, interpretar la edición en el texto. **Continuidad:** coherente con V01 (mismo diseño final) y con V02 (mismo diseño original).
**TEXTO A COMPONER:** barra superior *Tinta Norte — Gestión de pedidos*. Cabecera: *Pedido Nº 4471 · Cliente: Consultora Vértice · Confeti plateado — cuenta regresiva · 40 cajas · Estado: Entregado · Cierre de recepción de archivos: 23:00 · Entrega: 31/12 · 08:30 · Recibe: T. Ferreira (Logística)*. Historial, columnas *Fecha · Hora · Movimiento · Cuenta*: **16/12 · 16:20 · Pedido creado. Archivo de diseño v1 (confeti_plateado_nebula_v1.pdf) · m.lazo (Patrocinios · Vértice)** / **30/12 · 22:47 · Archivo de diseño reemplazado por v2 (confeti_plateado_final_v2.pdf) · m.lazo (Patrocinios · Vértice)**. Línea posterior sin cuenta: **30/12 · 23:05 · Pedido enviado a producción · Sistema**. (Nota para quien compone, NO va en la imagen: las tres filas tienen el mismo estilo; ninguna resaltada.)

(`gallery/v04-clausula-exclusividad.png` — V04, cláusula; entrega a T+12)
**PROMPT:** Documento visual tipo extracto de contrato, formato vertical, papelería legal simplificada y fácil de leer: un párrafo breve en lenguaje sencillo bajo un encabezado de cláusula, y una nota al margen sobre la penalización por incumplimiento. Aspecto de documento impreso o digital simple, luz neutra de oficina. Dejar vacío el texto. **EVITAR:** lenguaje legal complejo o extenso, iconografía de alerta, marcas reales.
**TEXTO A COMPONER:** encabezado *Contrato de patrocinio — Extracto*. **Cláusula 7. Exclusividad de marca.** *«Durante el evento, Nébula es la única marca autorizada a aparecer en la decoración, el escenario y los materiales impresos, incluido el confeti de la cuenta regresiva. Ninguna otra marca puede figurar.»* Nota al margen: **«Incumplimiento: penalización del 30 % del valor del patrocinio y derecho de Nébula a cancelar el contrato.»**

(`gallery/v05-mensaje-carla.png` — V05, mensaje con Carla; entrega a T+12)
**PROMPT:** Captura diegética vertical de un móvil con un intercambio breve de tres mensajes de texto entre dos personas, en una interfaz de mensajería simple y genérica, tono casual y amistoso, con burbujas a izquierda y derecha y un encabezado de contacto. Luz neutra de pantalla. Dejar vacíos los textos. **EVITAR:** mencionar el nombre de ninguna marca, iconografía de alarma, logos de apps reales, fotos de perfil reconocibles.
**TEXTO A COMPONER:** encabezado *Carla*. Mensajes: *Carla:* **«Mil gracias por adelantado por lo de la variante plateada con nuestro toque 🙌»** / *Mariana (derecha):* **«Tranquila. Lo dejo listo la víspera, cuando ya nadie mire el archivo. Que quede entre nosotras.»** / *Carla:* **«Por supuesto. Pasadas las fiestas te cuadro lo tuyo, como quedamos 😉»**. Sin fechas visibles.

## 4. Audio

Este caso **no tiene audio**: ni en la línea de tiempo ni confesión (final clásico). No se crea
la carpeta `audio/`.
