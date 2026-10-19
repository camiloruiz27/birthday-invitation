# Prompts de assets — El puerto de los calendarios

Total: **1 portada + 13 retratos + 10 evidencias visuales + 1 audio** (la confesión de Leonel se
genera por partida y no se graba; el único audio de la línea de tiempo es A1, la nota de voz de
Aurelio). Los 13 retratos son los 12 del roster más la víctima.

Todo se guarda en `public/immersion/el-puerto-de-los-calendarios/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 13 retratos |
| `gallery/` | las 10 evidencias visuales |
| `audio/` | `a1-nota-de-voz-aurelio.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (13): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). V08 es la prueba más delicada: debe parecer un
   fotograma real de cámara de seguridad, granulado y sin rostros identificables. V04 es la
   pieza central del caso: el texto exacto ES la prueba.
5. **Audio** A1 (`php artisan immersion:export-audio-scripts el-puerto-de-los-calendarios`).
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01, V02 |
| Apertura | 14 | (solo texto: fragmentos privados de la cronología CR1–CR4 y tarjetas de reglas, sin imagen) |
| Expansión | 28 | V03, V07 |
| Desarrollo | 42 | V04, V05 |
| Complicación | 56 | V08 (y CRON, resumen de la cronología, solo texto) |
| Giro | 70 | V06, A1 (audio) |
| Cierre | 85 | V09, V10 (y DW, declaración de Wilson, solo texto) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el dato
exacto ES la prueba (31 de diciembre contra 2 de enero; el usuario `lbravo.patio`; 09:47:20).
Para V01, V02, V03, V04, V06, V07, V08 y V09: genera la imagen con la zona de texto **vacía o
con texto de relleno ilegible**, y compón después el texto exacto indicado en «TEXTO A COMPONER»
con un editor (Canva, Figma, Photoshop). Verifica cada fecha, hora y nombre contra `case.php`
antes de guardar. Ningún control aduanero auténtico, sello oficial real ni norma tributaria real
debe aparecer en ninguna pieza.

**Cronología maestra (todas las fechas y horas del caso salen de aquí):** mediados de octubre,
ingreso a zona franca · mediados de diciembre, conteo de inventario · 30 de diciembre, trámite
pendiente (sin turno en bodega) · **31 de diciembre, fecha de liberación del permiso (V03)** ·
**2 de enero 06:52, movimiento físico del Lote 47 (V04)** · 2 de enero 18:41, última modificación
de `lbravo.patio` (V04) · 3 de enero 07:10 acta (V06) · 3 de enero ~07:35 nota de voz (A1) ·
09:30 inicio de la reunión de oficinas · **09:47:20 fotograma del corredor (V08)** · 10:20 fin de
la reunión.

## Biblia visual de continuidad (pegar en los retratos y en las escenas)

- **Aurelio Nieto** (víctima), 54: uniforme de inspector con chaleco identificador, carpeta de inspección siempre a mano.
- **Leonel Bravo**, 41: chaleco de alta visibilidad de supervisor, casco de trabajo, radio al cinto.
- **Paulina Rengifo**, 46: ropa de oficina práctica, carpeta con el logo genérico de la agencia aduanera.
- **Rogelio Campuzano**, 58: aspecto ejecutivo, chaqueta con el logo genérico de Puerto Altamar.
- **Ismael Duarte**, 50: traje de oficina, siempre con documentos bajo el brazo.
- **Verónica Salas**, 39: chaleco de zona franca, tablet de inventario.
- **Gustavo Henao**, 47: uniforme de seguridad, radio y llavero de accesos.
- **Daniel Osorio**, 35: traje comercial, móvil siempre en mano.
- **Wilson Paredes**, 52: overol de trabajo, casco de grúa.
- **Claudia Reyes**, 33: ropa de oficina, tablet con el cronograma de la reunión de Año Nuevo.
- **Mariana Quiroga**, 34: ropa de oficina práctica, parecida a la de Paulina pero de otro color.
- **Yolanda Prieto**, 49: traje formal, carpeta con el reglamento de regímenes.
- **Camilo Restrepo**, 43: chaleco de logística, tablet de movimientos.
- **Puerto Altamar**: puerto comercial ficticio; zona franca con contenedores apilados, terminal de agentes con oficinas sobrias, bodega general amplia, patio de contenedores con grúas y pasillos de tránsito; amanecer de enero con luz fría. Códigos de zona y de lote (Lote 47) estables en toda la evidencia.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales, ninguna estética ominosa para el culpable.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada de póster de catálogo, **formato vertical**, cinematográfica y sobria, para un thriller portuario: el patio de contenedores de un puerto comercial al amanecer de enero, con luz fría; en primer plano, sobre un palé, una carpeta de inspección abierta con un papel y una hoja de calendario de escritorio arrancada, sin texto legible; a un lado, en el suelo, una cadena de amarre enrollada; al fondo, contenedores apilados y una grúa recortada contra un cielo pálido. Sensación de calendario y de papeles, de algo que no coincide, sin dramatismo. **EVITAR:** cuerpo, sangre, iconografía policial, texto o logos, personas con rostro visible, fechas legibles.

## 2. Retratos

(`photos/aurelio-nieto.png` — víctima)
**PROMPT:** Retrato diegético tipo foto de credencial de un hombre de 54 años, uniforme de inspector con chaleco identificador, carpeta de inspección bajo el brazo, expresión meticulosa, serena y directa, fondo neutro de oficina portuaria. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, texto legible en la credencial, logos reales.

(`photos/leonel-bravo.png` — Leonel Bravo)
**PROMPT:** Retrato diegético de un hombre de 41 años, chaleco de alta visibilidad de supervisor, casco de trabajo, radio al cinto, expresión directa, técnica y algo tensa, fondo del patio de contenedores con grúas. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto agresivo.

(`photos/paulina-rengifo.png` — Paulina Rengifo)
**PROMPT:** Retrato diegético de una mujer de 46 años, ropa de oficina práctica, carpeta con el logo genérico de una agencia aduanera, expresión profesional, segura y algo reservada, fondo de oficina sobria de la terminal de agentes. Formato retrato. **EVITAR:** apariencia de culpable, mirada fría exagerada, logos de marcas reales, texto legible.

(`photos/rogelio-campuzano.png` — Rogelio Campuzano)
**PROMPT:** Retrato diegético de un hombre de 58 años, aspecto ejecutivo, chaqueta con el logo genérico de Puerto Altamar, expresión institucional, cordial y algo defensiva, fondo de despacho de gerencia con una ventana al puerto. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, texto legible.

(`photos/ismael-duarte.png` — Ismael Duarte)
**PROMPT:** Retrato diegético de un hombre de 50 años, traje de oficina, documentos bajo el brazo, expresión correcta y algo evasiva, fondo de oficina de la terminal de agentes con archivadores. Formato retrato. **EVITAR:** apariencia de corrupto, obsequios o dinero visibles, texto legible.

(`photos/veronica-salas.png` — Verónica Salas)
**PROMPT:** Retrato diegético de una mujer de 39 años, chaleco de zona franca, tablet de inventario en la mano, expresión profesional y algo tensa, fondo de zona franca con contenedores apilados. Formato retrato. **EVITAR:** gesto de culpa obvio, texto legible en la tablet.

(`photos/gustavo-henao.png` — Gustavo Henao)
**PROMPT:** Retrato diegético de un hombre de 47 años, uniforme de seguridad, radio y llavero de accesos, expresión segura con un asomo de cansancio, fondo de una sala de monitores de cámaras. Formato retrato. **EVITAR:** apariencia de somnoliento exagerada, villano obvio, texto legible en pantallas.

(`photos/daniel-osorio.png` — Daniel Osorio)
**PROMPT:** Retrato diegético de un hombre de 35 años, traje comercial, móvil en la mano, sonrisa cordial algo forzada, fondo de oficina comercial moderna. Formato retrato. **EVITAR:** apariencia de villano, texto legible en el móvil, marcas reales.

(`photos/wilson-paredes.png` — Wilson Paredes)
**PROMPT:** Retrato diegético de un hombre de 52 años, overol de trabajo y casco de grúa, expresión sencilla, seria e impresionada, fondo de la base de una grúa del patio de contenedores. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/claudia-reyes.png` — Claudia Reyes)
**PROMPT:** Retrato diegético de una mujer de 33 años, ropa de oficina, tablet con un cronograma en la mano, expresión ordenada, precisa y amable, fondo de sala de reuniones sobria. Formato retrato. **EVITAR:** apariencia de sospechosa, texto legible en la tablet.

(`photos/mariana-quiroga.png` — Mariana Quiroga)
**PROMPT:** Retrato diegético de una mujer de 34 años, ropa de oficina práctica, expresión amable y práctica, fondo de la terminal de agentes con un mostrador de trámites. Formato retrato. **EVITAR:** apariencia de sospechosa, texto legible.

(`photos/yolanda-prieto.png` — Yolanda Prieto)
**PROMPT:** Retrato diegético de una mujer de 49 años, traje formal, carpeta con un reglamento de regímenes bajo el brazo, expresión neutral, formal y didáctica, fondo de una sala de oficina sobria con una pizarra sin texto legible. Formato retrato. **EVITAR:** apariencia de sospechosa o de juez, texto legible, sellos oficiales reales.

(`photos/camilo-restrepo.png` — Camilo Restrepo)
**PROMPT:** Retrato diegético de un hombre de 43 años, chaleco de logística, tablet de movimientos en la mano, expresión técnica y ordenada, fondo de la bodega general con contenedores apilados. Formato retrato. **EVITAR:** apariencia de sospechoso, texto legible en la tablet.

## 3. Evidencias visuales

(`gallery/v01-mapa-zonas.png` — V01, mapa de las cuatro zonas; entrega T+1)
**PROMPT:** Diagrama vectorial simple tipo mapa esquemático de un puerto comercial, con exactamente cuatro zonas como bloques claramente distintos conectados por flechas simples de flujo de mercancía, diseño limpio y funcional, colores sobrios. Dejar vacías las etiquetas. Formato horizontal. **EVITAR:** controles aduaneros auténticos, iconografía de alerta, más de cuatro zonas, coordenadas.
**TEXTO A COMPONER:** título *Puerto Altamar — Plano de zonas*. Cuatro bloques con exactamente estos nombres: **Zona franca · Terminal de agentes · Bodega general · Patio de contenedores**. Flechas: *Zona franca → Bodega general* (mercancía liberada); *Bodega general ↔ Patio de contenedores* (tránsito); línea de puntos *Terminal de agentes* hacia *Zona franca* y *Bodega general* (trámites). Sin más textos.

(`gallery/v02-tabla-regimenes.png` — V02, tabla de las tres reglas; entrega T+1)
**PROMPT:** Documento tipo tabla de reglamento técnico de una sola página, con tres filas simples, formato institucional sobrio, tipografía grande, aspecto de documento impreso o digital funcional, iluminación neutra de oficina. Dejar vacías las celdas. Formato vertical. **EVITAR:** lenguaje técnico real de aduanas, normativa tributaria real, iconografía de alerta, más de tres filas.
**TEXTO A COMPONER:** título *Puerto Altamar — Regímenes aplicables*. Fila **1 · Depósito temporal:** «No genera obligación fiscal mientras la mercancía permanezca en la zona franca, sin límite de calendario.» Fila **2 · Tránsito preferencial:** «Válido únicamente hasta el 31 de diciembre del año en curso. Su beneficio no puede aplicarse a liberaciones posteriores, sin importar qué fecha figure en el papel.» Fila **3 · Régimen ordinario:** «Toda mercancía liberada después del vencimiento del régimen preferencial cae automáticamente bajo el régimen ordinario de importación, con obligaciones fiscales completas.»

(`gallery/v03-permiso-lote.png` — V03, permiso del lote; T+28)
**PROMPT:** Documento tipo permiso de liberación de mercancía, formato administrativo limpio y funcional, correctamente diligenciado en apariencia, sin ninguna anomalía visible, aspecto de documento impreso o digital, iluminación neutra de oficina. Dejar vacíos los campos de texto. Formato vertical. **EVITAR:** iconografía de alerta, sellos o validaciones aduaneras reales, tachaduras.
**TEXTO A COMPONER:** encabezado *Puerto Altamar — Terminal de agentes · Permiso de liberación de mercancía*. Campos: **Lote: 47 · Mercancía: Maquinaria industrial · Cliente: Importadora Rumbo Norte · Zona de origen: Zona franca · Destino: Bodega general · Régimen aplicado: Tránsito preferencial · Fecha de liberación: 31 de diciembre · Agente responsable: P. Rengifo · Estado: Liberación autorizada.**

(`gallery/v04-movimiento-carga.png` — V04, registro de movimiento de carga; T+42)
**PROMPT:** Captura maquetada de un sistema simple de registro de movimiento de contenedores, tabla con seis columnas y cinco filas en una interfaz logística sobria, sin resaltados artificiales; la fila del Lote 47 se distingue solo por su contenido, no por su color. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alarma, GPS o rastreo satelital, resaltados, cambiar el diseño entre filas.
**TEXTO A COMPONER:** título *Puerto Altamar — Sistema de movimiento de carga · Extracto*. Columnas: *Lote · Origen → Destino · Salida declarada · Movimiento físico (lector de puerta) · Última modificación*. Filas:
**44 · Zona franca → Bodega general · 29-dic · 29-dic 08:14 · Sistema (automática)** /
**45 · Patio de contenedores → Bodega general · 30-dic · 30-dic 11:02 · Sistema (automática)** /
**46 · Zona franca → Bodega general · 30-dic · 30-dic 15:47 · Sistema (automática)** /
**47 · Zona franca → Bodega general · 31-dic · 02-ene 06:52 · lbravo.patio — 02-ene 18:41** /
**48 · Bodega general → Patio de contenedores · 02-ene · 02-ene 09:20 · Sistema (automática)**.
Pie en letra pequeña: *La salida declarada puede ajustarse con permiso de sistema; el registro conserva el usuario y la hora de la última modificación.* (Verificar: en el Lote 47 la salida declarada dice 31-dic y el movimiento físico 02-ene; es el único con un usuario en «Última modificación».)

(`gallery/v05-foto-almacen.png` — V05, bodega general; T+42)
**PROMPT:** Fotografía diegética documental de una bodega general portuaria, con contenedores y palés de maquinaria industrial genérica apilados de forma ordinaria, siluetas genéricas de trabajadores portuarios sin rasgos reconocibles, luz fría de amanecer de enero entrando por los portones. Etiqueta de contenedor en primer plano con el código de lote en zona reservada. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, marcas reales, cualquier indicio de mercancía ilícita.
**TEXTO A COMPONER:** en la etiqueta de un contenedor, legible: **LOTE 47**; en un letrero alto al fondo: **BODEGA GENERAL**.

(`gallery/v06-acta-inspeccion.png` — V06, acta de inspección; T+70)
**PROMPT:** Documento tipo acta breve de inspección rutinaria de cierre de año, redactada en tono neutro y técnico, con un solo hallazgo, formato institucional sobrio, aspecto de documento impreso o digital, iluminación neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** iconografía de alerta, nombres propios de sospechosos, lenguaje acusatorio, tachaduras.
**TEXTO A COMPONER:** encabezado *Autoridad Aduanera Portuaria — Acta de inspección · Revisión rutinaria de cierre de año*. *Fecha: 3 de enero · 07:10. Inspector: A. Nieto.* **Hallazgo 1 — Lote 47 (maquinaria industrial):** «El permiso de liberación declara liberación el 31 de diciembre bajo régimen de tránsito preferencial. El registro de movimiento de carga indica movimiento físico el 2 de enero. La salida declarada del lote fue modificada con posterioridad al movimiento. Conforme a las reglas 2 y 3 del reglamento de regímenes, una liberación posterior al 31 de diciembre corresponde al régimen ordinario.» **Pendiente:** «Contrastar con quien tiene permiso de ajuste sobre el registro físico, antes del reporte formal.» Sin firmas legibles ni nombres de otras personas.

(`gallery/v07-lista-autorizantes.png` — V07, lista de autorizantes; T+28)
**PROMPT:** Documento tipo organigrama simple de responsabilidades portuarias, dividido en dos grupos claramente separados («con permiso de ajuste» y «solo consulta»), formato institucional claro y funcional, aspecto de documento impreso o digital, iluminación neutra de oficina. Dejar vacías las etiquetas. Formato vertical. **EVITAR:** iconografía de alerta, nombres propios de personas.
**TEXTO A COMPONER:** título *Puerto Altamar — Permisos de sistema sobre el registro de movimiento de carga*. Grupo **Con permiso de ajuste:** *Supervisor del patio de contenedores* · *Gerencia general (permiso de administrador del sistema; no ejecuta ajustes de rutina)*. Grupo **Solo consulta:** *Jefatura de agentes aduaneros · Agentes de aduanas · Administración de la zona franca · Seguridad portuaria · Cuentas comerciales · Asistencia de gerencia · Agente logístico · Operación de grúas.* Sin nombres propios.

(`gallery/v08-cctv-corredor.png` — V08, fotograma de CCTV; T+56)
**PROMPT:** Fotograma estático diegético de una cámara de seguridad de ángulo fijo y elevado en un corredor del patio de contenedores, con dos figuras genéricas en uniforme de trabajo portuario conversando de pie a cierta distancia de la cámara, calidad de vigilancia plausible, granulada y de baja resolución, contraste frío de amanecer, sin movimiento. Zona reservada en una esquina para la marca de fecha y hora. Formato horizontal, aspecto de fotograma de CCTV. **EVITAR:** dramatización, señales de forcejeo, iconografía de alarma, rostros identificables (la calidad debe ser la de una cámara real: las dos figuras no deben poder reconocerse), cadena de amarre visible en primer plano.
**TEXTO A COMPONER:** en la esquina superior: **PATIO-C2 · CORREDOR · 03-ENE · 09:47:20**. Sin más textos.

(`gallery/v09-contrato-comercial.png` — V09, contrato comercial; T+85)
**PROMPT:** Documento tipo contrato de servicios de agenciamiento aduanero entre una agencia del puerto y un cliente importador, con una cláusula de plazos y una penalización económica, formato comercial limpio y funcional, aspecto de documento impreso o digital, iluminación neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** normativa tributaria real, iconografía de alerta, firmas legibles de personas reales.
**TEXTO A COMPONER:** título *Contrato de servicios de agenciamiento aduanero — Agencia de Aduanas de Puerto Altamar e Importadora Rumbo Norte*. **Cláusula séptima — Plazo de liberación:** «La agencia gestionará la liberación de la mercancía en depósito bajo régimen de tránsito preferencial a más tardar el 31 de diciembre. Si la liberación no se completa a más tardar en esa fecha, el cliente podrá dar por terminado este contrato y exigir una penalización equivalente al 10 % de los honorarios anuales.» Espacio de firmas sin nombres legibles.

(`gallery/v10-acceso-exterior.png` — V10, acceso exterior; T+85)
**PROMPT:** Fotografía diegética documental de un punto de acceso exterior de un puerto comercial, con una caseta de control y una vía de entrada tranquila, sin actividad inusual, luz fría de amanecer de enero; como máximo una silueta genérica de un guardia sin rasgos reconocibles. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, marcas reales, dramatización, vehículos sospechosos.
**TEXTO A COMPONER:** ninguno crítico (opcional: letrero genérico *ACCESO NORTE* sobre la caseta).

## 4. Audio

El guion está en `case.php`; se exporta listo para estudio con
`php artisan immersion:export-audio-scripts el-puerto-de-los-calendarios`. Guardar en `audio/`.

(`audio/a1-nota-de-voz-aurelio.mp3` — A1, nota de voz de Aurelio, hallada en el borrador de su teléfono, 20–25 s, minuto 70)
**Voz:** `Charon` (hombre adulto de 54 años). **Scene:** nota de voz grabada con el teléfono, al aire libre, junto al patio de contenedores a primera hora; ruido de fondo de un patio portuario (motores lejanos, metal), sin música; una pausa breve antes de la última frase.
**Context:** Aurelio graba la nota para un colega de confianza: tiene claro lo del Lote 47 y piensa hablar primero con quien pudo ajustar el registro físico, antes de reportarlo.
**GUION (solo lo que se dice):** «Ya tengo claro lo del Lote 47. El permiso dice 31 de diciembre, pero el registro de movimiento es de otro día. Esto no lo decidió solo quien hizo el papeleo: alguien tuvo que ajustar el registro físico también. Voy a ir al patio esta mañana a hablar directamente con quien pudo haber hecho ese ajuste, antes de reportarlo formalmente. (pausa) Si hay una explicación razonable, prefiero escucharla primero.»
**PROMPT:** Nota de voz diegética de un hombre adulto de mediana edad, tono profesional y tranquilo, con una determinación serena, grabada para un colega; ruido ambiental de patio industrial de fondo, sin música. **EVITAR:** música dramática, tono acusatorio hacia una persona concreta, cualquier detalle que no se deduzca de la evidencia.

(A2 — confesión de Leonel) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz `Fenrir`) para incluir las preguntas que esa mesa le hizo a Leonel. Dirección de voz: empieza defensivo y tenso, se estabiliza hacia una aceptación genuina y dolorosa; alguien que reaccionó por miedo a una prueba técnica innegable, no con frialdad. Sin música, sin sobreactuar, sin contenido violento explícito.
