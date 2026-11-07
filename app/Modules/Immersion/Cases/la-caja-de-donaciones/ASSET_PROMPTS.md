# Prompts de assets — La Caja de Donaciones

Total: **1 portada + 4 retratos + 5 evidencias visuales + 0 audios** (la confesión de Julián
se genera por partida y no se graba; este caso no tiene audio dentro de la línea de tiempo).

Todo se guarda en `public/immersion/la-caja-de-donaciones/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `cover.png` |
| `photos/` | los 4 retratos (imagen de campaña + 3 personas del caso) |
| `gallery/` | las 5 evidencias visuales |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (4): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera V1 en la misma sesión que V2: misma
   mesa de madera clara, misma caja CD-1, misma luz de tarde. V3 y V4 comparten el mismo
   estilo de documentos administrativos. V5 se genera al final.
5. Fija el objeto ancla CD-1 y el sobre SC-1 en V1 y mantenlos idénticos en V4 y V5.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V2 |
| Reconstrucción | 20 | V3, V4 |
| Cierre | 35 | V5 |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni cifras**, y en este caso el
dato exacto ES la prueba (USD 960 → USD 300 → USD 660; 16:07 entrega a Julián; 16:34 reporte
de «robo»). Para V2, V3, V4 y V5: genera la imagen con la zona de texto **vacía o con texto de
relleno ilegible**, y compón después el texto exacto indicado en «TEXTO A COMPONER» con un editor
(Canva, Figma, Photoshop). Verifica cada cifra y cada hora contra `case.php` antes de guardar.

**Datos exactos de las piezas centrales (prueba del caso):**
- **V2 (hoja de recuento):** USD 960 · código SC-1 · firmas Mara Peña y Julián Soto · 16:00
- **V4 (acta de efectivo restante):** USD 960 / USD 300 / USD 660 · revisión Mara Peña · 16:31
- **V5 (registro de custodia):** 16:07 sobre SC-1 entregado a Julián / 16:34 reporte Julián «robo» — no constan otras entregas de SC-1

## Cronología maestra (todos los datos del caso salen de aquí)

16:00 — Recuento de USD 960; firmas Mara Peña y Julián Soto; efectivo a sobre SC-1 ·
16:07 — Sobre SC-1 entregado a Julián Soto para ingreso ·
16:18 — Recibo de reparación del cierre del depósito de alimentos: USD 300 pagado en efectivo, Almacén del barrio, receptor César Molina, cliente Campaña Solidaria / entrega J. Soto ·
16:31 — Recuento de sobre SC-1: saldo USD 660; diferencia USD 300 con el recuento de 16:00; revisión Mara Peña ·
16:34 — Verificación caja CD-1: precinto intacto número 084 ·
16:34 — Reporte de Julián Soto: «Robo de caja CD-1, USD 300» — no constan otras entregas de SC-1.

## Biblia visual de continuidad (pegar en los retratos)

- **Campaña vecinal de alimentos** (afectada, no es persona individual): mesa de campaña con cajas de alimentos desenfocadas, carteles comunitarios abstractos y la caja de donaciones CD-1 sobre fondo de salón multiuso.
- **Julián Soto**, adulto de mediana edad: ropa cotidiana de organizador, cansancio y preocupación auténticos; nunca aspecto de ladrón caricaturesco.
- **Mara Peña**, adulta: ropa sencilla, carpeta o libreta neutra bajo el brazo, expresión molesta por el desorden pero no agresiva ni acusadora.
- **Olga Rivas**, adulta: vestimenta práctica de voluntaria, atención nerviosa pero cálida, sin exagerar la ausencia.
- **Centro vecinal ficticio**: salón multiuso luminoso, mesas plegables de madera clara, carteles comunitarios abstractos e ilegibles, cajas de alimentos desenfocadas al fondo; sin marcas comerciales.
- **Objetos ancla**: caja de donaciones sencilla CD-1 (precinto crema, número 084 compuesto en posproducción), sobre manila SC-1, mesa de conteo de madera clara. Mantener forma, color y desgaste idénticos en V1, V2, V4 y V5.
- **Paleta**: crema, terracota, verde apagado y luz de tarde natural. Documentos con tinta azul, clips sencillos y sellos genéricos.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales, sin dramatización policial.

---

## 1. Portada

(`cover/cover.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un misterio comunitario: salón vecinal de barrio al final de una tarde, una caja de donaciones sencilla cerrada sobre una mesa plegable de madera clara, un sobre manila apartado a un lado junto a una hoja de conteo doblada, y cajas de alimentos desenfocadas al fondo junto a carteles de campaña abstractos. La composición debe insinuar una decisión humana difícil y una confianza dañada, no un robo violento ni una escena criminal. Sin personas visibles, sin manos, sin texto legible, sin logos ni marcas reales. Formato horizontal 16:9, fotografía realista premium, luz de tarde cálida. **EVITAR:** billetes visibles en abundancia, caja fuerte, cerraduras forzadas, policía, sangre, rótulos acusatorios, dramatización, estética de thriller urbano.

## 2. Retratos

(`photos/campaña-vecinal.png` — afectada: imagen documental de campaña)
**PROMPT:** Imagen diegética documental vertical de una mesa de campaña vecinal de donación de alimentos: sobre la mesa de madera clara, la caja de donaciones sencilla CD-1 cerrada, una hoja de registro doblada y algunas cajas de alimentos al fondo suavemente desenfocadas, con carteles comunitarios abstractos en la pared; luz de tarde cálida, sin personas ni efectivo visible. Debe representar la confianza de la comunidad, no una víctima individual. Formato retrato 4:5. **EVITAR:** efectivo visible, caja fuerte, personas, texto legible, iconografía de alarma.

(`photos/julian-soto.png` — Julián Soto, organizador de la campaña)
**PROMPT:** Retrato diegético de un hombre adulto de mediana edad, ropa cotidiana de organizador comunitario (camisa o polo informal), expresión de cansancio y preocupación auténticos, mirada algo evasiva pero no amenazante, fondo de un salón comunitario con carteles ilegibles desenfocados. Formato retrato 4:5. **EVITAR:** apariencia de ladrón caricaturesco, dinero o sobre en la mano, gesto agresivo, iluminación dramática.

(`photos/mara-pena.png` — Mara Peña, tesorera y responsable del recuento)
**PROMPT:** Retrato diegético de una mujer adulta, ropa sencilla, carpeta o libreta neutra bajo el brazo, expresión molesta y meticulosa por el manejo informal, mirada directa y profesional, fondo de mesa de conteo del salón comunitario. Formato retrato 4:5. **EVITAR:** apariencia de acusadora teatral, dramatización, dinero o sobre visible, texto legible.

(`photos/olga-rivas.png` — Olga Rivas, voluntaria en recepción de donaciones)
**PROMPT:** Retrato diegético de una mujer adulta, vestimenta práctica de voluntaria, expresión nerviosa pero cálida, postura atenta, fondo de la mesa de recepción del salón comunitario con la caja CD-1 desenfocada al fondo. Formato retrato 4:5. **EVITAR:** apariencia de sospechosa, dramatización, texto legible, caja o sobre en primer plano.

## 3. Evidencias visuales

(`gallery/v1-caja-precintada.png` — V1, caja CD-1 cerrada con precinto intacto)
**PROMPT:** Fotografía diegética documental ligeramente cenital de la caja de donaciones sencilla CD-1 sobre la mesa de madera clara del salón comunitario: caja rectangular de cartón o plástico ligero, cerrada, con un precinto de plástico color crema pasado por la ranura y visiblemente intacto, sin señales de apertura forzada. La ranura de depósito está cubierta. Luz de tarde cálida, profundidad de campo suave con el fondo del salón desenfocado. El número del precinto se deja en blanco para composición posterior. Formato horizontal. **EVITAR:** caja fuerte, cerradura sofisticada, señales de fuerza o daño, dinero visible, texto generado automáticamente en el precinto.
**TEXTO A COMPONER:** etiqueta pequeña en la caja: *Caja de donaciones **CD-1***. En el cuerpo del precinto: ***084***. Pie de foto compuesto en interfaz: *16:34 · Precinto intacto número **084** · Sin señal de apertura forzada.*

(`gallery/v2-hoja-de-recuento.png` — V2, hoja de conteo inicial)
**PROMPT:** Fotografía diegética de una hoja de conteo administrativa sobre la mesa de madera clara del salón comunitario: papel ligeramente texturado con líneas, encabezado sobrio de campaña, columna de conceptos y columna de cifras con espacio amplio y legible, dos líneas de firma al pie con un espacio para código de sobre. Luz de tarde cálida. Dejar todos los campos de texto, cifras y nombres vacíos para composición posterior. Formato vertical 4:5. **EVITAR:** cifras inventadas por la IA, nombre de campaña real, dinero físico visible, iconografía de alarma.
**TEXTO A COMPONER:** encabezado *Campaña Solidaria — Hoja de recuento*. Fecha/hora: *16:00*. Concepto: *Total contado en efectivo.* Cifra: ***USD 960***. Línea: *Efectivo trasladado a sobre código: **SC-1** para ingreso posterior.* Firmas: ***Mara Peña*** · ***Julián Soto***.

(`gallery/v3-recibo-reparacion.png` — V3, recibo de reparación del taller del barrio)
**PROMPT:** Fotografía diegética de un recibo de taller de barrio, fotografiado sobre una superficie neutra clara: papel térmico o de talonario sencillo, membrete ficticio sin logo reconocible, dos o tres líneas de concepto, una línea de importe, una línea de receptor y una de cliente, con espacio para hora. Aspecto de comercio local informal, ligeramente arrugado en un extremo. Dejar todos los campos vacíos para composición posterior. Formato vertical 4:5. **EVITAR:** logos o nombres de establecimientos reales, diseño corporativo sofisticado, dinero en efectivo visible, iconografía de alarma.
**TEXTO A COMPONER:** membrete: *Almacén del barrio*. Concepto: *Reparación del cierre del depósito de alimentos.* Importe: ***USD 300** — pagado en efectivo.* Hora: *16:18.* Recibí: ***César Molina***. Cliente: *Campaña Solidaria / entrega **J. Soto***.

(`gallery/v4-acta-efectivo-restante.png` — V4, acta de efectivo restante)
**PROMPT:** Documento diegético tipo acta administrativa breve, fotografiado en picado suave sobre la mesa de madera clara: papel crema, misma identidad gráfica sobria que V2, una operación de tres filas con espacio amplio para tres cifras y una línea de revisora y hora al pie. Sin calculadora, sin billetes, sin resaltadores ni anotaciones acusatorias. Dejar todos los campos vacíos para composición posterior. Formato vertical 4:5. **EVITAR:** cifras inventadas por la IA, diseño diferente al de V2, iconografía de alarma, flechas o subrayados.
**TEXTO A COMPONER:** encabezado *Campaña Solidaria — Acta de recuento posterior*. Tabla: *Total contado a las 16:00: **USD 960*** / *Salida documentada: **USD 300*** / *Saldo en sobre SC-1: **USD 660***. Pie: *Diferencia con recuento de 16:00: **USD 300**. Revisión: **Mara Peña** · 16:31.*

(`gallery/v5-registro-custodia.png` — V5, registro de custodia y reporte de Julián)
**PROMPT:** Documento diegético tipo hoja de control de custodia administrativa, fotografiado sobre la mesa del salón: papel crema, mismo estilo sobrio que V2 y V4, una tabla sencilla de dos filas con columnas de hora, evento y responsable, espacios amplios y limpios para los textos. Sin flechas, tachones, resaltados ni terceras entradas. Dejar todos los campos vacíos para composición posterior. Formato vertical 4:5. **EVITAR:** diseño diferente al de V2 y V4, iconografía de alarma, flechas o círculos sobre alguna fila, lenguaje acusatorio visible.
**TEXTO A COMPONER:** encabezado *Registro de custodia — Campaña Solidaria*. Columnas: *Hora · Evento · Responsable*. Filas: ***16:07** · Sobre **SC-1** entregado a **Julián Soto** para ingreso · M. Peña* / ***16:34** · Reporte de Julián Soto: «Robo de caja **CD-1**, **USD 300**» · J. Soto*. Nota al pie: *No constan otras entregas de SC-1.*

## 4. Audio

Este caso **no tiene audio en la línea de tiempo**: el único audio es la confesión de Julián, que
se genera por partida (no se graba).

(A1 — confesión de Julián Soto) **No se graba.** Es `solution.confession_script` en `case.php`:
se genera por partida (con voz Charon) para incluir las preguntas que esa mesa le hizo a Julián.
Dirección de voz: comienza con una narrativa de urgencia y buena intención («la reparación era
necesaria para la propia campaña»), luego se derrumba cuando se le presenta V5; termina con
vergüenza genuina más que con cinismo. Sin música, sin sobreactuación, sin contenido violento.
