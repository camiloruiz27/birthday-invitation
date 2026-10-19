# Prompts de assets — El Año que no Cerró

Total: **1 portada + 9 retratos + 8 evidencias visuales + 1 audio** (este caso termina con
epílogos personalizados: NO hay confesión en audio. El único audio es A1, el mensaje de voz de
Ignacio, que va en la línea de tiempo).

Todo se guarda en `public/immersion/el-ano-que-no-cerro/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 9 retratos (8 personas + la imagen de la cooperativa, que es la «afectada») |
| `gallery/` | las 8 evidencias visuales |
| `audio/` | `a1-mensaje-voz-ignacio.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (9): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Primero V02, V03 y V04: comparten cuentas,
   montos y estilo de interfaz; hazlas juntas para que coincidan. V01 y V05 después.
5. **Audio** A1 (`php artisan immersion:export-audio-scripts el-ano-que-no-cerro`).
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Expansión | 12 | (solo texto) |
| Pistas privadas | 12 | V02A (a un jugador), V02B (a otro) |
| La regla completa | 22 | V02 |
| Desarrollo | 26 | V03 |
| Complicación | 40 | V04 |
| Giro | 55 | DJ (declaración escrita de Jorge, sin imagen), V05, V06; audio A1 en correo aparte |
| Cierre | 65 | (solo texto) |

## Regla de oro para los documentos con texto

Este caso es **documental**: no hay fotos ni cámaras, solo capturas contables nativas, documentos
y mensajes. El dato exacto (cuentas, montos, tipo de asiento, usuario) ES la prueba, y los
generadores de imagen **no escriben bien cifras ni tablas**. Para V01 a V06 (y V02A, V02B): genera
la imagen con la zona de texto **vacía o con texto de relleno ilegible**, o monta la tabla en un
editor (Canva, Figma, Excel/Sheets capturado, Photoshop) y compón después el texto exacto indicado
en "TEXTO A COMPONER". Verifica cada cifra contra la **hoja contable maestra** (abajo) antes de
guardar. Tipografía grande y sobria; **nada de iconografía de alerta ni resaltados** en ninguna
pieza.

**Hoja contable maestra (todas las cifras del caso salen de aquí).** Solo hay **dos tipos de
asiento** y **tres cuentas**, y los montos son redondos (dólares estadounidenses, USD):

- Cuentas: **410 Ventas a clientes externos · 520 Notas de crédito pendientes · 610 Pasivos
  reconocidos del nuevo ejercicio.**
- Tipos de asiento: **Compensación directa** (ajuste ordinario contra la 410, sin aprobación
  especial, para montos pequeños) y **Reconocimiento de pasivo** (obligación en la 610; sobre
  **US$ 1,250** requiere la aprobación explícita del director financiero).
- Notas del ejercicio cerrado: **Ferretería Los Álamos US$ 375 · Agrícola El Sur US$ 750 ·
  Inversiones Cumbre US$ 1,875 · Comercial Andes US$ 500 · Distribuidora Marconi US$ 5,000.**
  Total **US$ 8,500** = US$ 6,625 compensado directamente + US$ 1,875 reconocido como pasivo.
- Último día del ejercicio (V04): 09:15 Los Álamos · 09:40 El Sur · 10:20 Cumbre enviada a
  aprobación · 11:05 Octavio aprueba Cumbre · 15:50 Andes · **16:42 Marconi** procesada por R.
  Solórzano, **no enviada** a la cola del director.
- Chat (V06): lunes 16:48 / 16:55 / 17:02. Mensaje de voz (A1): martes por la mañana.

## Biblia visual de continuidad (pegar en los retratos)

- **Renata Solórzano**, 36: cabello castaño oscuro recogido, blazer gris, credencial de empleada al cuello, expresión cordial, aplicada y algo tensa.
- **Ignacio Beltrán**, 44: cabello corto con algo de canas, traje azul sin corbata, reloj, sonrisa segura y cercana.
- **Octavio Reyes**, 55: cabello gris, gafas, traje oscuro sobrio, expresión profesional y algo distante.
- **Marcela Ibáñez**, 50: cabello corto, blazer vino, postura institucional y aplomada.
- **Felipe Londoño**, 29: camisa clara con mangas remangadas, relajado, un portátil o libreta a mano.
- **Camila Duarte**, 31: blusa clara, agenda y carpetas, expresión ordenada y discreta.
- **Jorge Patiño**, 46: camisa de manga corta, barba corta, expresión campechana y cansada.
- **Humberto Casas**, 60: cabello blanco, gafas de lectura, saco gris, carpeta de auditoría; sereno y didáctico.
- **Cooperativa Provisión Central**: edificio de oficinas mediano de tres pisos, fachada sobria de ladrillo claro y vidrio, sin logotipo ni rótulo legible.
- **Interfaces contables y documentos**: sobrios, blanco y gris azulado, tipografía sans-serif grande, tablas limpias; sin colores de alarma, sin logos de software reales.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio de oficina, en formato vertical de póster de catálogo: un escritorio de oficina financiera por la noche, con una pila ordenada de informes contables perfectamente alineados y un único folio asomando apenas del montón; una lámpara de escritorio cálida, una ventana con luces de ciudad desenfocadas al fondo y una calculadora apagada. Sensación de calma impecable y de algo fuera de lugar que nadie ha visto. Sin personas. Realista, premium, paleta fría con un acento ámbar. **EVITAR:** texto o cifras legibles, logos, sangre, iconografía policial, estética de terror, manos o rostros, cualquier símbolo de alerta.

## 2. Retratos

(`photos/cooperativa-provision-central.png` — la afectada)
**PROMPT:** Fotografía diegética de la fachada de un edificio de oficinas mediano de tres pisos de una cooperativa, ladrillo claro y vidrio, a primera hora de la mañana, con luz fría y limpia y la entrada vacía; aspecto sobrio y respetable. Formato retrato. **EVITAR:** rótulos o logos legibles, personas protagonistas, dramatización, aspecto de abandono.

(`photos/renata-solorzano.png` — Renata Solórzano)
**PROMPT:** Retrato diegético tipo foto de credencial corporativa de una mujer de 36 años, cabello castaño oscuro recogido, blazer gris, expresión cordial, aplicada y algo tensa, fondo neutro de oficina financiera con una pantalla desenfocada. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, texto legible en la credencial.

(`photos/ignacio-beltran.png` — Ignacio Beltrán)
**PROMPT:** Retrato diegético de un hombre de 44 años, cabello corto con algo de canas, traje azul sin corbata, reloj, sonrisa segura y cercana de comercial veterano, fondo de oficina comercial luminosa. Formato retrato. **EVITAR:** apariencia de villano, gesto de astucia obvio, iluminación dramática.

(`photos/octavio-reyes.png` — Octavio Reyes)
**PROMPT:** Retrato diegético de un hombre de 55 años, cabello gris, gafas, traje oscuro sobrio, expresión profesional, algo distante y reservada, fondo de despacho de dirección financiera con una estantería de carpetas. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, texto legible.

(`photos/marcela-ibanez.png` — Marcela Ibáñez)
**PROMPT:** Retrato diegético de una mujer de 50 años, cabello corto, blazer vino, postura institucional y aplomada, expresión segura con un leve asomo de incomodidad, fondo de sala de juntas con una larga mesa. Formato retrato. **EVITAR:** apariencia de culpable, gesto de culpa obvio, texto legible.

(`photos/felipe-londono.png` — Felipe Londoño)
**PROMPT:** Retrato diegético de un hombre de 29 años, camisa clara con las mangas remangadas, expresión relajada y cercana con un punto de nerviosismo, con una libreta o portátil, fondo de puesto de trabajo de un departamento de contabilidad. Formato retrato. **EVITAR:** apariencia de sospechoso, gesto de culpa obvio, texto legible en pantallas.

(`photos/camila-duarte.png` — Camila Duarte)
**PROMPT:** Retrato diegético de una mujer de 31 años, blusa clara, agenda y carpetas en los brazos, expresión ordenada, precisa y discreta, fondo de recepción de una dirección financiera. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización, texto legible.

(`photos/jorge-patino.png` — Jorge Patiño)
**PROMPT:** Retrato diegético de un hombre de 46 años, camisa de manga corta, barba corta, expresión campechana y algo cansada, con una taza de café, fondo de zona de ventas con escritorios y un pasillo al fondo. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización, texto legible.

(`photos/humberto-casas.png` — Humberto Casas)
**PROMPT:** Retrato diegético de un hombre de 60 años, cabello blanco, gafas de lectura sobre la nariz, saco gris, una carpeta de auditoría bajo el brazo, expresión serena, didáctica y neutral, fondo de sala de reuniones sobria. Formato retrato. **EVITAR:** apariencia de juez o acusador, gesto de sospecha, texto legible.

## 3. Evidencias visuales

(`gallery/v01-resumen-anual.png` — V01, resumen anual; entrega a T+1)
**PROMPT:** Captura de pantalla diegética de un resumen de cierre de balance anual en una interfaz contable sobria y de tipografía grande, con una tabla simple de tres cuentas y una línea final de totales; aspecto de pantalla de software contable genérico, blanco y gris azulado. Dejar vacías las celdas de texto. Formato horizontal. **EVITAR:** errores de suma, iconografía de alerta, resaltados, colores rojos, logos de software reales.
**TEXTO A COMPONER:** título *Cooperativa Provisión Central — Resumen del ejercicio cerrado* (subtítulo *Montos en dólares estadounidenses (USD)*). Columnas: *Cuenta · Saldo antes del cierre · Movimiento de cierre · Saldo final*. Filas: **410 Ventas a clientes externos · US$ 319,125 · −US$ 6,625 · US$ 312,500** / **520 Notas de crédito pendientes · US$ 8,500 · −US$ 8,500 · US$ 0** / **610 Pasivos reconocidos del nuevo ejercicio · US$ 0 · +US$ 1,875 · US$ 1,875**. Pie: *Total Debe del cierre: US$ 8,500 · Total Haber del cierre: US$ 8,500 · Diferencia: US$ 0 · Estado: ejercicio cerrado, cuadrado.*

(`gallery/v02a-manual-interno.png` — V02A, fragmento privado, manual interno; entrega a T+12 a un jugador)
**PROMPT:** Página única de un manual de procedimientos internos de una cooperativa, documento impreso o digital funcional, con un encabezado de sección y dos párrafos cortos; aspecto sobrio, tipografía grande, luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** lenguaje técnico extenso, iconografía de alerta, resaltados, sellos, cualquier tercer tipo de asiento.
**TEXTO A COMPONER:** encabezado *Manual de procedimientos internos — Sección 4.2 · Notas de crédito pendientes (clientes externos)*. Párrafo 1: **«Compensación directa: ajuste ordinario sobre la cuenta 410 Ventas a clientes externos. No necesita aprobación especial. Aplica a montos pequeños y rutinarios, de hasta US$ 1,250.»** Párrafo 2: **«Reconocimiento de pasivo: obligación formal registrada en la cuenta 610 Pasivos reconocidos del nuevo ejercicio. Si la nota supera US$ 1,250, debe enviarse a la cola de aprobación del director financiero antes de registrarse.»** Pie: *Umbral de aprobación: US$ 1,250.*

(`gallery/v02b-correo-capacitacion.png` — V02B, fragmento privado, correo de capacitación; entrega a T+12 a otro jugador)
**PROMPT:** Captura de pantalla diegética de un correo interno de capacitación en una interfaz de correo simple y genérica, con encabezado de remitente, asunto y un cuerpo breve con dos viñetas; tipografía grande, blanco y gris. Dejar vacío el texto. Formato vertical. **EVITAR:** logos de clientes de correo reales, iconografía de alarma, adjuntos llamativos.
**TEXTO A COMPONER:** *De: Capacitación interna · Para: Equipo financiero · Asunto: Recordatorio de cierre: cómo tratar las notas de crédito pendientes.* Cuerpo: **«Buen día, equipo. Antes del cierre, recordemos las dos formas de tratar una nota de crédito pendiente con un cliente externo:»** Viñeta 1: **«Compensación directa (cuenta 410): para montos pequeños y rutinarios, sin aprobación especial.»** Viñeta 2: **«Reconocimiento de pasivo (cuenta 610): para montos sobre US$ 1,250. Requiere la aprobación explícita del director financiero.»** Cierre: **«Ante cualquier duda, consulten antes de registrar. Gracias.»**

(`gallery/v02-regla-compensacion.png` — V02, regla completa; entrega pública a T+22)
**PROMPT:** Documento visual tipo página única de reglamento interno, con un título, una frase de introducción y dos líneas numeradas claras; tipografía grande, aspecto de documento impreso o digital funcional, luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** lenguaje técnico extenso, iconografía de alerta, resaltados, más de dos tipos de asiento.
**TEXTO A COMPONER:** título *Reglamento interno — Artículo 14. Notas de crédito pendientes con clientes externos.* Introducción: *Toda nota de crédito pendiente con un cliente externo se trata de una de estas dos formas:* Línea 1: **«1. Compensación directa. Ajuste ordinario contra la cuenta 410 Ventas a clientes externos. No requiere aprobación especial. Se usa para montos pequeños y rutinarios, de hasta US$ 1,250.»** Línea 2: **«2. Reconocimiento de pasivo. Obligación formal registrada en la cuenta 610 Pasivos reconocidos del nuevo ejercicio. Toda nota que supere US$ 1,250 debe tratarse así y requiere la aprobación explícita del director financiero.»** Pie: *Umbral de aprobación: US$ 1,250.*

(`gallery/v03-notas-por-compensar.png` — V03, listado de notas; entrega a T+26)
**PROMPT:** Captura maquetada de un listado simple de notas de crédito de un sistema contable, en una interfaz sobria de tipografía grande, con cuatro columnas y cinco filas; blanco y gris azulado, ninguna fila resaltada. Dejar vacías las celdas de texto. Formato horizontal. **EVITAR:** iconografía de alerta, resaltados artificiales, filas de otro color, cualquier fila marcada.
**TEXTO A COMPONER:** título *Notas de crédito del ejercicio — Listado de cierre* (subtítulo *Montos en dólares estadounidenses (USD)*). Columnas: *Cliente · Monto · Tipo de asiento aplicado · Cuenta*. Filas en este orden: **Ferretería Los Álamos · US$ 375 · Compensación directa · 410** / **Agrícola El Sur · US$ 750 · Compensación directa · 410** / **Inversiones Cumbre · US$ 1,875 · Reconocimiento de pasivo · 610** / **Comercial Andes · US$ 500 · Compensación directa · 410** / **Distribuidora Marconi · US$ 5,000 · Compensación directa · 410**. Pie: *Total: US$ 8,500.*

(`gallery/v04-registro-aprobacion.png` — V04, registro de aprobación; entrega a T+40)
**PROMPT:** Captura maquetada de un historial de acciones administrativas de un sistema contable, interfaz sobria de tipografía grande, con una tabla de seis filas y siete columnas y una línea de pie; blanco y gris azulado, sin ninguna fila resaltada. Dejar vacías las celdas de texto. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, rojo, candados o escudos, cualquier fila marcada.
**TEXTO A COMPONER:** título *Sistema contable — Historial de acciones · Último día del ejercicio*. Columnas: *Hora · Usuario · Acción · Cliente · Monto · Tipo de asiento · Cola del director financiero*. Filas: **09:15 · R. Solórzano · Procesó nota · Ferretería Los Álamos · US$ 375 · Compensación directa · No aplica** / **09:40 · R. Solórzano · Procesó nota · Agrícola El Sur · US$ 750 · Compensación directa · No aplica** / **10:20 · R. Solórzano · Envió a aprobación · Inversiones Cumbre · US$ 1,875 · Reconocimiento de pasivo · Enviada** / **11:05 · O. Reyes · Aprobó · Inversiones Cumbre · US$ 1,875 · Reconocimiento de pasivo · Aprobada** / **15:50 · R. Solórzano · Procesó nota · Comercial Andes · US$ 500 · Compensación directa · No aplica** / **16:42 · R. Solórzano · Procesó nota · Distribuidora Marconi · US$ 5,000 · Compensación directa · No enviada**. Pie en letra pequeña: *Permiso de proceso sobre notas de clientes externos: R. Solórzano (Analista financiera).*

(`gallery/v05-acta-auditoria.png` — V05, acta de revisión; entrega a T+55)
**PROMPT:** Documento visual tipo acta breve de revisión de cierre, tono neutro y técnico, formato institucional sobrio: encabezado, tres secciones cortas (alcance, hallazgo, recomendación) y un espacio de firma; tipografía grande, luz neutra de oficina. Dejar vacío el texto. Formato vertical. **EVITAR:** iconografía de alerta, nombres de personas, lenguaje acusatorio, sellos de «fraude» o «alerta», resaltados.
**TEXTO A COMPONER:** encabezado *Cooperativa Provisión Central — Acta de revisión de cierre · Revisor de cuentas externo.* *Alcance:* **«Revisión rutinaria del proceso de cierre del ejercicio.»** *Hallazgo 1:* **«En la cuenta 520 Notas de crédito pendientes se identificó un movimiento de US$ 5,000 compensado directamente contra la cuenta 410 Ventas a clientes externos. El monto supera el umbral habitual de US$ 1,250 para este tipo de asiento. No consta aprobación del director financiero para este movimiento.»** *Observación:* **«El balance del ejercicio cerrado cuadra; el hallazgo no corresponde a una diferencia de sumas.»** *Recomendación:* **«Revisar el tratamiento de la nota.»** Firma: *Revisor de cuentas externo* (sin nombre propio ni nombre de cliente).

(`gallery/v06-correspondencia-comercial.png` — V06, chat interno; entrega a T+55)
**PROMPT:** Captura de pantalla diegética de una conversación breve de chat interno de empresa entre dos personas, interfaz simple y genérica, tipografía grande, tono informal y profesional, burbujas con hora. Dejar vacíos los textos. Formato vertical. **EVITAR:** iconografía de alarma, logos de apps reales, resaltados, emojis, la palabra «fraude» o similares.
**TEXTO A COMPONER:** encabezado *Chat interno — Ignacio Beltrán · Renata Solórzano.* **Ignacio Beltrán · lun 16:48:** «Renata, lo de Marconi tiene que quedar resuelto antes de la junta. ¿Cómo vas?» **Renata Solórzano · lun 16:55:** «Ya la tengo cargada, son US$ 5,000. Solo me falta saber si ya pasó por Octavio.» **Ignacio Beltrán · lun 17:02:** «Eso ya lo hablé yo, tú tranquila. Resuélvela rápido y no la compliques.» (Nunca debe aparecer la palabra «aprobada».)

## 4. Audio

Los guiones están en `case.php`; se exportan listos para estudio con
`php artisan immersion:export-audio-scripts el-ano-que-no-cerro`. Guardar en `audio/`.

(`audio/a1-mensaje-voz-ignacio.mp3` — A1, mensaje de voz de Ignacio a Renata, 10–15 s; entrega a T+55)
**VOZ (Gemini):** `Orus` (hombre adulto, cálido).
**SCENE:** Mensaje de voz de móvil, dejado desde un pasillo de oficina, con ambiente de oficina de fondo (teclas, conversación lejana). Sin música ni dramatismo.
**CONTEXT:** Ignacio, gerente comercial de 44 años, deja un mensaje breve a una colega pidiéndole que resuelva un trámite rápido antes de una reunión importante. Tono cordial pero apremiante, como un favor entre compañeros de trabajo; no debe sonar como una amenaza.
**GUION (solo lo que se dice):** *Ignacio:* «Renata, lo de Marconi: resuélvelo rápido antes de la junta, ¿sí? Ya está hablado por mi lado, no te compliques más de la cuenta. Gracias, en serio.»
**PROMPT:** Mensaje de voz diegético breve de un hombre adulto, tono cordial pero apremiante, pidiendo a una colega que resuelva rápido un trámite administrativo antes de una reunión importante, insinuando sin decirlo explícitamente que ya cuenta con una aprobación superior, en un tono de favor entre compañeros de trabajo; sin música ni dramatismo, ambiente de oficina de fondo. **EVITAR:** música dramática, tono amenazante, cualquier detalle que no sea deducible por la evidencia.

(Confesión de Renata) **No existe.** El caso termina con epílogos personalizados: no hay `confession_script` y no se graba nada más.
