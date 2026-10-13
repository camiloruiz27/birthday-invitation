# Prompts de assets — El Último Lote

Total: **1 portada + 10 retratos + 18 evidencias visuales + 1 audio** (más la confesión de
Gabriel, que se genera por partida y no se graba).

Todo se guarda en `public/immersion/el-ultimo-lote/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos |
| `gallery/` | las 18 evidencias visuales |
| `audio/` | `a1-ambiente-sala-subasta.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **La obra y su marco**: genera V2 primero y úsala como referencia para V5, V9 y V16 (el marco
   debe ser idéntico en las cuatro).
3. **Portada** (para empezar a vender y a probar el catálogo).
4. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
5. **Evidencias V1 → V16** en orden de entrega (ver tabla). V3 y V14 deben salir de la misma
   "sesión" visual (misma sala de archivo, mismo sujetalibros).
6. **Audio** A1.
7. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V3, V4, A1 |
| Expansión | 13 | V2, V16 |
| Pistas privadas | 26 | V5a (fragmento de foto), V6a (fragmento de reporte) |
| Complicación | 39 | V10, V12 |
| Confrontación de procedencia | 52 | V5 (completa), V6 (completo), V7, V8, V9 |
| Borrador de acuerdo | 52 | V15 |
| Giro | 65 | V11, V13 |
| Cierre | 78 | V14 |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el dato
exacto ES la prueba (entrada 21:47 ≠ llegada del guardia 23:21; "V-62/147" debe leerse igual en
dos imágenes). Para V4, V5, V6, V7, V8, V9, V10, V11, V12, V13, V15 y V16: genera la imagen con
la zona de texto **vacía o con texto de relleno ilegible**, y compón después el texto exacto
indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica cada hora y cada
código contra `case.php` y los `.md` antes de guardar.

**Importante — la obra y el artista son ficticios.** Ningún prompt debe imitar a un artista real,
una escuela real ni una obra real: la pintura es un paisaje clásico genérico y el caso se
resuelve por marcos, etiquetas, fechas y papeles, nunca por el estilo. Si el generador devuelve
algo reconocible como una obra famosa, repite.

## Biblia visual de continuidad (pegar en los retratos)

- **Lucía Montalvo**, 45 (víctima): cabello oscuro recogido, traje sastre gris, gafas de lectura colgadas al cuello.
- **Gabriel Oquendo**, 54: complexión elegante, traje oscuro impecable, cabello canoso peinado hacia atrás.
- **Teresa Andrade**, 41: cabello castaño recogido, blusa formal, carpeta de archivo siempre en mano.
- **Margarita Soler**, 62: cabello plateado elegante, joyería discreta, vestido formal de tonos neutros.
- **Mateo Vega**, 37: cabello oscuro corto, chaqueta casual pero cuidada, sin la elegancia formal del resto.
- **Federico Lara**, 49: traje corporativo gris, maletín de trabajo.
- **Olga Fischer**, 56: bata de restauración sobre ropa práctica, guantes de trabajo colgando del bolsillo.
- **Sofía Reyes**, 28: ropa de oficina informal, tablet en mano.
- **Don Ramiro Castillo**, 60: uniforme de seguridad discreto.
- **Casa Belmonte**: interiores elegantes pero funcionales; sala de subasta con iluminación cálida; sala de archivo con estanterías y documentos protegidos, luz cálida de archivo; taller de restauración con luz fría de trabajo.
- **La obra (Lote 27)**: óleo sobre lienzo de 92 × 73 cm, **paisaje clásico genérico** (un río entre álamos al atardecer, tonos suaves), sin firma legible, sin estilo reconocible. **Marco dorado muy ornamentado, con una pequeña pérdida de moldura (un fragmento de hoja de acanto faltante) en la esquina inferior derecha**: ese defecto debe verse igual en V2, V5 y V16 (de frente); en V9, que muestra el reverso, el mismo defecto queda en la esquina inferior IZQUIERDA vista desde atrás.
- **Los bustos sujetalibros**: pareja de bustos decorativos de bronce patinado de un personaje clásico genérico, de unos 20 cm; en la sala de archivo cierran las filas de carpetas en los estantes. Uno de los dos desaparece del estante en V3 y reaparece en V14.
- **Código de etiqueta antigua**: `V-62/147` (inventario de la familia Vega, 1962, pieza n.º 147). Debe leerse idéntico en V5, V6 y V9.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio de alta sociedad en una casa de subastas: una sala de subasta elegante y vacía de noche, iluminación cálida y tenue, filas de sillas, un atril de subastador y, en el centro, un óleo enmarcado en un marco dorado muy ornamentado sobre un caballete, de espaldas o en penumbra, de modo que no se distinga la pintura. Un único foco cálido sobre el marco. Sensación de lujo contenido, silencio y algo no dicho; sin personas visibles. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror, mostrar una pintura reconocible o imitar a un artista real, texto o logos.

## 2. Retratos

(`photos/lucia-montalvo.png` — Lucía Montalvo, víctima)
**PROMPT:** Retrato diegético tipo foto profesional de una mujer de 45 años, cabello oscuro recogido, traje sastre gris, gafas de lectura colgadas al cuello, expresión serena, atenta y rigurosa de especialista en autenticación, fondo neutro de un despacho de casa de subastas. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos reales.

(`photos/gabriel-oquendo.png` — Gabriel Oquendo)
**PROMPT:** Retrato diegético de un hombre de 54 años, complexión elegante, traje oscuro impecable, cabello canoso peinado hacia atrás, expresión calmada, segura y encantadora de director de una casa de subastas, fondo de sala de subasta con iluminación cálida. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, sonrisa de villano.

(`photos/teresa-andrade.png` — Teresa Andrade)
**PROMPT:** Retrato diegético de una mujer de 41 años, cabello castaño recogido, blusa formal, con una carpeta de archivo apretada contra el pecho, expresión meticulosa y levemente tensa, fondo de oficina de departamento de procedencia con estanterías. Formato retrato. **EVITAR:** gesto de culpa obvio, sospecha teatral, iluminación dramática.

(`photos/margarita-soler.png` — Margarita Soler)
**PROMPT:** Retrato diegético de una mujer de 62 años, cabello plateado elegante, joyería discreta, vestido formal de tonos neutros, expresión digna, reservada y algo evasiva, fondo de salón elegante de una casa de subastas. Formato retrato. **EVITAR:** apariencia de villana, dramatización, ostentación exagerada.

(`photos/mateo-vega.png` — Mateo Vega)
**PROMPT:** Retrato diegético de un hombre de 37 años, cabello oscuro corto, chaqueta casual pero cuidada, sin corbata, expresión decidida y algo desconfiada, fondo neutro de pasillo de una casa de subastas. Formato retrato. **EVITAR:** gesto amenazante, apariencia de reclamante agresivo, dramatización.

(`photos/federico-lara.png` — Federico Lara)
**PROMPT:** Retrato diegético de un hombre de 49 años, traje corporativo gris, maletín de trabajo en la mano, expresión profesional, calculadora y cauta, fondo de sala de reuniones de oficina. Formato retrato. **EVITAR:** cliché de ejecutivo corrupto, sonrisa falsa, dramatización.

(`photos/olga-fischer.png` — Olga Fischer)
**PROMPT:** Retrato diegético de una mujer de 56 años, bata de restauración sobre ropa práctica, guantes de trabajo colgando del bolsillo, expresión técnica y algo reservada, fondo de taller de restauración con luz fría y un caballete difuso. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, herramientas en actitud amenazante.

(`photos/sofia-reyes.png` — Sofía Reyes)
**PROMPT:** Retrato diegético de una mujer de 28 años, ropa de oficina informal, tablet en la mano, expresión cercana, afectada y atenta, fondo de corredor de oficinas de una casa de subastas. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/ramiro-castillo.png` — Don Ramiro Castillo)
**PROMPT:** Retrato diegético de un hombre de 60 años, uniforme de seguridad discreto, expresión serena, formal y metódica, fondo de pasillo de una casa de subastas con luz nocturna tenue. Formato retrato. **EVITAR:** apariencia de sospechoso, arma visible, dramatización.

(`photos/sistema-laboratorio-y-aseguramiento.png` — laboratorio y registro de aseguramiento)
**PROMPT:** Imagen diegética tipo retrato funcional de un terminal de consulta en un laboratorio de análisis de arte, con una pantalla sobria que muestra listas de informes y registros y, al lado, una mesa de análisis con equipo de medición discreto; se usa como avatar de una fuente técnica. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, estética futurista, texto legible.

## 3. Evidencias visuales

(`gallery/v1-plano-casa-belmonte.png` — V1, plano de la casa de subastas)
**PROMPT:** Documento diegético tipo plano funcional simple de una casa de subastas de lujo, en planta esquemática, mostrando cuatro estancias conectadas por un pasillo: la sala de subasta principal, la sala de archivo, las oficinas del departamento de autenticación y procedencia y el taller de restauración, en líneas técnicas claras y elegantes. Formato horizontal. **EVITAR:** iconografía de alerta, accesos o salas no mencionados, colores arbitrarios.
**TEXTO A COMPONER:** etiquetas simples y legibles: *Sala de subasta principal · Sala de archivo · Oficinas de autenticación y procedencia · Taller de restauración · Pasillo*.

(`gallery/v2-obra-catalogo.png` — V2, foto oficial de la obra)
**PROMPT:** Fotografía diegética de catálogo de subasta, foto profesional controlada, de un óleo enmarcado de estilo clásico genérico (paisaje con un río entre álamos al atardecer, no identificable como obra real ni asociable a ningún artista real), con un marco dorado muy ornamentado que tiene una pequeña pérdida de moldura en la esquina inferior derecha, fotografiado de frente contra un fondo neutro. Iluminación de estudio uniforme. Formato vertical. **EVITAR:** firma legible, estilo pictórico distintivo o reconocible, referencias a artistas o movimientos reales, texto.

(`gallery/v3-sala-de-archivo.png` — V3, la sala de archivo tal como se encontró)
**PROMPT:** Fotografía diegética funcional de una sala de archivo elegante, con estanterías de documentos protegidos y cajas de conservación, luz cálida de archivo. En primer plano, un estante con una fila de carpetas que se inclina porque falta uno de los dos bustos decorativos de bronce que la sostenían (queda solo el gemelo en el otro extremo) y una silla desplazada de la mesa de lectura. Sin cuerpo visible (como mucho, algo fuera de cuadro). Desorden mínimo. Formato horizontal. **EVITAR:** sangre, gore, cuerpo en primer plano, dramatización, mostrar el busto desaparecido.

(`gallery/v4-nota-hallada.png` — V4, la nota hallada)
**PROMPT:** Fotografía diegética funcional de una nota manuscrita breve en papel de oficina simple, con letra clara y sobria, sin firma ni destinatario, sobre una superficie neutra, luz neutra. Dejar vacía la zona de la frase. Formato vertical. **EVITAR:** iconografía de alerta, manchas, daños, dramatización.
**TEXTO A COMPONER:** *«El problema no es que la obra sea falsa.»* (a mano, una sola línea, sin firma).

(`gallery/archivo-foto-salon.png` — V5 fragmento, entrega privada a T+26)
**PROMPT:** Fotografía de archivo familiar de los años sesenta, en sepia suave y con grano de época, mostrando un interior doméstico elegante, **recortada o parcial como fragmento**: se ve parte de una pared y de un marco dorado ornamentado, pero no la pintura completa ni a nadie con claridad. Sin ninguna anotación ni leyenda. Formato horizontal. **EVITAR:** mostrar la esquina inferior derecha del marco con claridad, anotaciones, anacronismos, marcas comerciales.

(`gallery/v5-inventario-familiar-1962.png` — V5 completa, entrega a T+52)
**PROMPT:** La misma fotografía de archivo familiar que V5 fragmento (mismo salón, misma pared, mismo encuadre base), ahora completa y sin cortes: se ve el óleo enmarcado colgado en la pared (el mismo marco dorado ornamentado que V2, con la pérdida de moldura en la esquina inferior derecha), entre muebles y objetos de época, y una familia genérica posando de forma casual cerca, sin protagonismo ni rostros reconocibles. Tono sepia, grano de época. Formato horizontal. Dejar vacía una franja inferior para una anotación a mano. **EVITAR:** anacronismos, marcas comerciales, rostros protagonistas, cambiar el salón respecto al fragmento.
**TEXTO A COMPONER:** anotación a mano en el margen inferior: *Inv. V-62 · n.º 147 · Salón · 1962*.

(`gallery/archivo-documento-antiguo.png` — V6 fragmento, entrega privada a T+26)
**PROMPT:** Documento diegético tipo reporte de reclamo de seguro antiguo, con membrete genérico de una aseguradora, papel envejecido, **cortado o rasgado como fragmento** que deja solo la parte superior: encabezado, número de siniestro, fecha y naturaleza del siniestro. El resto, incluida la descripción del bien y el estado del reclamo, no aparece. Formato vertical. **EVITAR:** iconografía de alerta, logos reales, mostrar la descripción del bien o el pago.
**TEXTO A COMPONER:** membrete genérico *Compañía Aseguradora Nacional* (inventado, sin logo); *Reporte de reclamo — Siniestro n.º 78/0412 — Fecha: 14 de noviembre de 1978 — Naturaleza: sustracción de bien de residencia privada.* El resto, cortado.

(`gallery/v6-reporte-robo-seguro-1978.png` — V6 completo, entrega a T+52)
**PROMPT:** El mismo documento que V6 fragmento (mismo membrete, mismo papel envejecido), ahora completo y sin cortes, fotografiado sobre una superficie neutra con luz neutra. Formato vertical. **EVITAR:** iconografía de alerta, detalles de estilo artístico, cambiar el diseño respecto al fragmento.
**TEXTO A COMPONER:** la parte superior idéntica a V6 fragmento, más: *Asegurado: Familia Vega. Bien: óleo sobre lienzo, paisaje, 92 × 73 cm, en marco dorado ornamentado con pérdida de moldura en la esquina inferior derecha. Ref. de inventario del asegurado: V-62/147. Estado del reclamo: procedente. Pago efectuado el 20 de diciembre de 1978.*

(`gallery/v7-compra-2006.png` — V7, documento de compra de 2006)
**PROMPT:** Documento diegético tipo contrato de compraventa privado, impreso, de lenguaje formal breve, fotografiado sobre un escritorio con luz neutra de oficina, con dos firmas genéricas ilegibles al pie. Dejar vacío el cuerpo del texto. Formato vertical. **EVITAR:** iconografía de alerta, sellos notariales, números de expediente, cualquier detalle que delate la falsedad por sí solo.
**TEXTO A COMPONER:** *Contrato de compraventa privado. En la ciudad, a 12 de mayo de 2006. Vendedor: la sucesión privada del Sr. Aurelio Ibarra Cantú. Comprador: D. Ernesto Roldán. Objeto: un óleo sobre lienzo, paisaje, atribuido a Augusto Ferrán, de 92 × 73 cm, procedente de la sucesión privada del Sr. Aurelio Ibarra Cantú. Precio: el acordado entre las partes.* Dos firmas genéricas ilegibles.

(`gallery/v8-busqueda-sucesion.png` — V8, verificación de la sucesión inexistente)
**PROMPT:** Captura de pantalla diegética de un resultado de búsqueda en un registro público y notarial simple, interfaz administrativa sobria, con un campo de criterio de búsqueda y una zona de resultados vacía. Formato vertical. **EVITAR:** iconografía de alerta, texto interpretativo ("falso", "fraude"), logos reales.
**TEXTO A COMPONER:** *Registro de sucesiones y actos notariales — Búsqueda. Criterio: sucesión de Aurelio Ibarra Cantú. Período: 1995–2006. Ámbito: nacional. Resultados: 0 (cero) registros. Consulta realizada por: L. Montalvo.*

(`gallery/v9-etiqueta-reverso.png` — V9, etiqueta antigua en el reverso)
**PROMPT:** Fotografía diegética funcional de trabajo de restauración: el reverso de un lienzo enmarcado, con el bastidor de madera y la tela por detrás, y en la esquina del bastidor una pequeña etiqueta de inventario antigua, parcialmente despegada y desgastada, de papel amarillento, con un código escrito a mano. Se ve parte del mismo marco dorado ornamentado de V2, con la pérdida de moldura en la esquina inferior IZQUIERDA vista desde el reverso (es la misma esquina que la inferior derecha de V2 vista de frente; no la pongas en la esquina inferior derecha del reverso). Luz fría de taller. Formato 4:5. Dejar el código para composición. **EVITAR:** iconografía de alerta, etiqueta nueva o perfectamente legible, números inventados por el generador.
**TEXTO A COMPONER:** en la etiqueta, a mano con tinta desvaída: *V-62/147*.

(`gallery/v10-informe-laboratorio.png` — V10, informe del laboratorio de análisis material)
**PROMPT:** Documento diegético tipo informe técnico de laboratorio, impreso o digital, lenguaje sobrio, con encabezado, un breve cuerpo de texto y una conclusión destacada de forma discreta, sin gráficos complejos. Dejar vacía la zona de texto. Formato vertical. **EVITAR:** jerga científica excesiva, iconografía de alerta, logos reales.
**TEXTO A COMPONER:** *Laboratorio de análisis material — Informe — Lote 27. Objeto: óleo sobre lienzo, atribuido a Augusto Ferrán, 92 × 73 cm. Métodos: examen del soporte, análisis de pigmentos y examen estratigráfico. Conclusión: los materiales, el soporte y la técnica son consistentes con la época y la atribución declaradas. No se detectan elementos anacrónicos. Este informe no se pronuncia sobre titularidad ni procedencia.*

(`gallery/v11-chat-gabriel-teresa.png` — V11, chat entre Gabriel y Teresa)
**PROMPT:** Captura de pantalla diegética de una conversación breve en una aplicación de mensajería corporativa genérica, formato de captura de móvil, interfaz simple y neutra, burbujas alternadas entre dos remitentes. Dejar vacío el texto de los mensajes. Formato vertical. **EVITAR:** iconografía de alarma, mención explícita de fabricación o falsificación, logos de apps reales.
**TEXTO A COMPONER:** *Belmonte Chat — Gabriel O. / Teresa A.* — *Martes 14:12 — Gabriel O.: «Teresa, la ficha de procedencia del Lote 27 sigue con vacíos. Necesitamos que esté completa antes de la venta.» — Teresa A.: «Hay un tramo de 2006 que no puedo respaldar con lo que tenemos. Preferiría consultarlo con la consignataria.» — Gabriel O.: «No hay tiempo para eso. Encuentra la forma de cerrar esos vacíos. No quiero preguntas, quiero la ficha cerrada.» — Teresa A.: «Lo intentaré.» — Gabriel O.: «No lo intentes. Hazlo. Esta venta no puede caerse.»*

(`gallery/v12-notas-lucia.png` — V12, notas de Lucía)
**PROMPT:** Documento diegético tipo notas de trabajo breves, manuscritas, en una hoja de bloc sobre un escritorio, con letra cuidada y tono profesional y preocupado, luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** iconografía de alerta, texto acusatorio dramatizado, nombres completos.
**TEXTO A COMPONER:** *Lote 27 — revisión final. · Informe del laboratorio: consistente. La obra es buena. · PROCEDENCIA: discrepancia en el eslabón de 2006; no logro respaldarlo con los registros que he consultado. · Pendiente: confirmar con archivos históricos y con la aseguradora; revisar las fotos de restauración. · Si no se aclara: detener la venta. No firmo el certificado.* Firmado solo *L. M.*

(`gallery/v13-registro-acceso-archivo.png` — V13, registro de acceso a la sala de archivo)
**PROMPT:** Captura de pantalla diegética de un sistema simple de control de accesos, con una tabla funcional de entradas y salidas por tarjeta a una sala, interfaz sobria. Ninguna fila resaltada. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, logos reales.
**TEXTO A COMPONER:** título *Control de accesos — Sala de archivo — Registro del día.* Columnas *Hora · Tarjeta · Evento.* Filas: *09:05 Reyes, S. Entrada · 09:32 Reyes, S. Salida · 11:10 Andrade, T. Entrada · 11:48 Andrade, T. Salida · 18:12 Montalvo, L. Entrada · 20:05 Castillo, R. (ronda) Entrada · 20:08 Castillo, R. (ronda) Salida · 21:47 Oquendo, G. Entrada · 22:34 Oquendo, G. Salida · 23:21 Castillo, R. (ronda) Entrada.* (Ninguna salida registrada para Montalvo, L.)

(`gallery/v14-busto-sujetalibros.png` — V14, el objeto fuera de lugar)
**PROMPT:** Fotografía diegética funcional de un busto decorativo pequeño de bronce patinado (uno de la pareja de sujetalibros de V3), apoyado sobre un estante distinto de la sala de archivo, fuera de su posición habitual, con una superficie inusualmente limpia y brillante comparada con el polvo leve visible en los objetos cercanos. Luz cálida de archivo, misma sala que V3. Sin sangre ni indicio de violencia. Formato 4:5. **EVITAR:** sangre, daño, marcas de violencia, resaltados artificiales sobre la limpieza.

(`gallery/v15-borrador-acuerdo.png` — V15, borrador de acuerdo privado)
**PROMPT:** Documento diegético tipo borrador de acuerdo privado, impreso, de lenguaje formal y discreto, con la marca "borrador" y sin firmar, sobre una superficie neutra con luz de oficina. Dejar vacío el cuerpo del texto. Formato vertical. **EVITAR:** iconografía de alerta, montos dramatizados, logos.
**TEXTO A COMPONER:** *Borrador v.3 — Acuerdo privado y confidencial. Entre D.ª Margarita Soler y D. Mateo Vega, respecto al Lote 27. 1. El Sr. Vega se abstendrá de formular reclamación pública sobre la obra. 2. La Sra. Soler abonará una compensación económica, cuyo importe consta en anexo reservado. 3. Las partes mantendrán la confidencialidad de este acuerdo.* Sin firmas.

(`gallery/v16-catalogo-subasta.png` — V16, catálogo de la subasta)
**PROMPT:** Fotografía diegética de un catálogo impreso de subasta de lujo, abierto en la página del lote estrella, con la misma obra de V2 (mismo marco ornamentado con la pérdida de moldura en la esquina inferior derecha) en fotografía de página y un texto de presentación breve y elegante. Luz cálida de exhibición. Formato horizontal. **EVITAR:** iconografía de alerta, detalles de procedencia, referencias a artistas reales.
**TEXTO A COMPONER:** *Lote 27 — Venta privada. Atribuido a Augusto Ferrán. «Paisaje con álamos». Óleo sobre lienzo, 92 × 73 cm. Estimación: bajo petición.* El resto, texto de relleno ilegible.

## 4. Audio

El guion está en `case.php`; se exporta listo para estudio con
`php artisan immersion:export-audio-scripts el-ultimo-lote`. Guardar en `audio/`.

(`audio/a1-ambiente-sala-subasta.wav` — A1, ambiente de la sala de subasta, 10–12 s)
**GUION:** *«Lote final: atribuido a Augusto Ferrán. Comenzamos la puja en...»*
**PROMPT:** Ambiente sonoro diegético de una sala de subasta elegante durante un ensayo previo al evento: murmullo distante, un golpe suave de martillo de subasta a lo lejos y una voz profesional (voz masculina adulta, tono neutro y elegante) que ensaya la presentación de un lote final, ajena a lo que ocurrirá esa noche. Sin música. **EVITAR:** tono de tensión, música dramática, sonidos de catástrofe, pista de ambiente que anticipe lo que ocurre después.

(A2 — confesión de Gabriel) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Orus) para incluir las preguntas que esa mesa le hizo a Gabriel.
