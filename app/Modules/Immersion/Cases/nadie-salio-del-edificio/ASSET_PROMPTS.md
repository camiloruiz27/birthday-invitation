# Prompts de assets — Nadie Salió del Edificio

Total: **1 portada + 11 retratos + 21 evidencias visuales + 2 audios** (más la confesión, que
se genera por partida y no se graba).

Todo se guarda en `public/immersion/nadie-salio-del-edificio/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 11 retratos (víctima + 6 sospechosos + 3 testigos + sistema) |
| `gallery/` | las 21 evidencias visuales |
| `audio/` | `a1-fragmento-videollamada.wav`, `a2-anuncio-cierre.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (11): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias en orden de entrega** (ver tabla). V3, V4 y V5 son CCTV y deben salir del mismo
   estilo de cámara (V3 y V5 con el mismo ángulo del lobby). V6, V7, V8, V10, V12 y V13 son
   capturas de sistema con la misma interfaz sobria. V14 y V15 son la misma oficina.
5. **Audios** A1 y A2.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, A2 |
| Expansión | 12 | V2, V3 |
| Pistas privadas | 24 | V2a, V2b, V2c (fragmentos, sin código) |
| Complicación | 38 | V6, V7, V9, V10, V11, V12, V13 |
| Reconstrucción | 52 | V4, V8, A1 |
| Giro | 68 | V14, V15, V16, V17, V18 |
| Cierre | 80 | V5 |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el
dato exacto ES la prueba (15:08 ≠ 15:23; 15:19 y 15:24 son correos distintos). Para V1, V2,
V2a, V2b, V2c, V3, V4, V5, V6, V7, V8, V9, V10, V11, V12, V13, V16, V17 y V18: genera la imagen
con la zona de texto **vacía o con texto de relleno ilegible**, y compón después el texto
exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica cada
hora contra `case.php` y los `.md` antes de guardar. Los retratos, V14 y V15 no llevan texto.

Reglas especiales de este caso:

- **Las capturas de sistema (V6, V7, V8, V10, V12, V13) no resaltan la fila crítica**: nada
  de rojo, flechas ni iconos de alerta. La prueba se ve por lo que falta o por la hora, no por
  el color.
- **V3, V4 y V5 son CCTV de baja resolución en los que nadie es identificable por el rostro**:
  los figurantes se distinguen solo por complexión y ropa, y ningún prompt debe pedir caras
  nítidas.

## Biblia visual de continuidad (pegar en los retratos)

- **Rodolfo Castaño**, 54: cabello canoso corto, traje gris oscuro, gafas de montura fina.
- **Gonzalo Prada**, 48: complexión media, cabello oscuro con entradas, camisa azul clara sin corbata.
- **Valentina Rojas**, 37: cabello castaño recogido, blazer beige.
- **Esteban Navas**, 41: complexión delgada, camiseta de equipo técnico con credencial visible.
- **Patricia Vega**, 45: cabello negro corto, traje pantalón burdeos.
- **Daniela Mercado**, 34: cabello castaño suelto, ropa casual de oficina.
- **Sergio Lemos**, 50: complexión robusta, traje azul marino.
- **Raúl Téllez**, 55: uniforme de seguridad.
- **Carmen Duque**, 29: uniforme discreto de recepción.
- **Hugo Saldaña**, 52: uniforme de mantenimiento con herramientas.
- **Torre Andesol**: edificio corporativo contemporáneo de una capital latinoamericana, pisos
  numerados con oficinas abiertas y privadas, escaleras internas funcionales, lobby con
  recepción y control de acceso, salas de reuniones de cristal.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un thriller corporativo de encierro: la fachada de una torre de oficinas contemporánea al caer la tarde, vista desde la calle, con la entrada principal vacía y las persianas de seguridad parcialmente bajadas, y varios pisos iluminados con luz fría en los que se adivinan siluetas minúsculas tras los cristales; sensación de encierro, vigilancia y calma tensa, sin personas identificables. Debe sugerir que lo importante ocurre dentro y que nadie puede salir. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror, tecnología futurista, texto o logos.

## 2. Retratos

(`photos/rodolfo-castano.png` — víctima)
**PROMPT:** Retrato diegético tipo foto corporativa de un hombre de 54 años, cabello canoso corto, traje gris oscuro, gafas de montura fina, expresión serena y exigente de director financiero, fondo neutro de oficina contemporánea. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales.

(`photos/gonzalo-prada.png` — Gonzalo Prada)
**PROMPT:** Retrato diegético de un hombre de 48 años, complexión media, cabello oscuro con entradas, camisa azul clara sin corbata, expresión profesional, controlada y algo cansada, fondo neutro de oficina corporativa. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática.

(`photos/valentina-rojas.png` — Valentina Rojas)
**PROMPT:** Retrato diegético de una mujer de 37 años, cabello castaño recogido, blazer beige, expresión eficiente, atenta y algo apenada, fondo neutro de oficina corporativa. Formato retrato. **EVITAR:** gesto de culpa obvio, sospecha teatral.

(`photos/esteban-navas.png` — Esteban Navas)
**PROMPT:** Retrato diegético de un hombre de 41 años, complexión delgada, camiseta de equipo técnico con credencial visible (sin texto legible), expresión técnica y algo nerviosa, fondo de sala de servidores sobria. Formato retrato. **EVITAR:** villano obvio, gesto agresivo, tecnología futurista.

(`photos/patricia-vega.png` — Patricia Vega)
**PROMPT:** Retrato diegético de una mujer de 45 años, cabello negro corto, traje pantalón burdeos, expresión profesional, reservada y protectora de su privacidad, fondo neutro de oficina corporativa. Formato retrato. **EVITAR:** gesto de culpa obvio, dramatización.

(`photos/daniela-mercado.png` — Daniela Mercado)
**PROMPT:** Retrato diegético de una mujer de 34 años, cabello castaño suelto, ropa casual de oficina, expresión profesional y algo evasiva, fondo de oficina abierta con pantallas desenfocadas. Formato retrato. **EVITAR:** gesto de culpa obvio, dramatización.

(`photos/sergio-lemos.png` — Sergio Lemos)
**PROMPT:** Retrato diegético de un hombre de 50 años, complexión robusta, traje azul marino, expresión segura, aplomada y algo evasiva de directivo, fondo de sala de reuniones de cristal. Formato retrato. **EVITAR:** villano obvio, gesto amenazante.

(`photos/raul-tellez.png` — Raúl Téllez)
**PROMPT:** Retrato diegético de un hombre de 55 años, uniforme de guardia de seguridad, expresión formal, veterana y concisa, fondo del puesto de control del lobby de un edificio corporativo. Formato retrato. **EVITAR:** apariencia de sospechoso, armas, dramatización.

(`photos/carmen-duque.png` — Carmen Duque)
**PROMPT:** Retrato diegético de una mujer de 29 años, uniforme discreto de recepción, expresión amable, atenta y precisa, fondo del mostrador de recepción de un lobby corporativo. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/hugo-saldana.png` — Hugo Saldaña)
**PROMPT:** Retrato diegético de un hombre de 52 años, uniforme de mantenimiento con herramientas en el cinturón, expresión tranquila, literal y de pocas palabras, fondo de un descanso de escalera interna de un edificio. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/sistema-control-edificio.png` — sistema de control del edificio)
**PROMPT:** Imagen diegética tipo retrato funcional de una consola de la sala de seguridad de un edificio corporativo, con pantallas sobrias que muestran tablas de accesos, estado de ascensores y registros, usada como avatar del sistema de control. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, fallos visibles, estética futurista, personas.

## 3. Evidencias visuales

(`gallery/v1-plano-vertical-edificio.png` — V1, plano vertical)
**PROMPT:** Documento diegético tipo plano funcional simple en corte vertical de un edificio corporativo de varios pisos, en líneas esquemáticas claras: planta baja con lobby, recepción, una sala de descanso y una sala de reuniones de cristal; los pisos 2, 3, 4, 5 y 6; la escalera interna conectando todos los pisos y los ascensores a un lado. Los pisos 4 y 6 con sus oficinas relevantes marcadas de forma neutra. Aspecto de plano técnico de orientación. Formato vertical. **EVITAR:** iconografía de alerta, accesos no mencionados, resaltar un recorrido.
**TEXTO A COMPONER:** *Torre Andesol — Plano vertical.* Etiquetas, de abajo arriba: **PB — Lobby, recepción, sala de descanso, sala de reuniones** · **Piso 2 — Sistemas** · **Piso 3 — Sala técnica** · **Piso 4 — Subdirección financiera** · **Piso 5 — Oficinas abiertas y sala de llamadas** · **Piso 6 — Dirección financiera**. Rótulos laterales: **Escalera interna** y **Ascensores**.

(`gallery/v2-matriz-eventos-resumida.png` — V2, matriz resumida)
**PROMPT:** Documento diegético tipo matriz resumen de eventos técnicos generada por el equipo de sistemas, formato horizontal, tabla funcional con columnas Persona, Sistema, Evento y Hora, algunas filas completas y otras con la celda de evento vacía y una etiqueta "pendiente de verificación", en estilo de documento de investigación sobrio. **EVITAR:** iconografía de alerta, resaltados, revelar datos críticos.
**TEXTO A COMPONER:** columnas *Persona · Sistema · Evento · Hora*. Filas: **G. Prada · Tarjeta · Piso 4, archivo financiero · 14:47** — **G. Prada · Videollamada · Conexión · 15:05** — **V. Rojas · Tarjeta · Piso 5, asistencia ejecutiva · 14:58** — **P. Vega · Sesión · Inicio de sesión · 08:03** — **D. Mercado · Sesión · Inicio de sesión · 08:34** — **S. Lemos · Videollamada · Conexión · 15:02** — **E. Navas · Sesión · Inicio de sesión · 08:20** — y, en el resto de la ventana 15:00–15:40, la etiqueta **pendiente de verificación**.

(`gallery/v3-cctv-lobby-manana.png` — V3, CCTV1 lobby a primera hora)
**PROMPT:** Imagen diegética de cámara de seguridad fija, ángulo elevado, del lobby de un edificio corporativo a primera hora de la mañana, con varios empleados pasando por el control de acceso con sus credenciales en actitud rutinaria, calidad de vídeo de seguridad plausible y baja resolución. Los empleados son figuras de complexión y ropa variadas, sin rostros nítidos: entre ellas una figura de complexión media con cabello oscuro y camisa azul clara sin corbata, una de traje gris oscuro, una de blazer beige y una de traje azul marino, sin que ninguna sea el centro de la imagen. Deja libre una esquina para la marca de hora. Formato horizontal. **EVITAR:** rostros identificables, dramatización, resaltar a algún personaje, cambiar el ángulo respecto a V5.
**TEXTO A COMPONER:** marca de hora discreta en una esquina: *CAM LOBBY 01 — 08:46:12*.

(`gallery/v2a-fragmento-gonzalo-valentina.png` — V2 fragmento privado, T+24)
**PROMPT:** Fragmento recortado de la misma matriz de V2 (mismo diseño, cortado por los bordes) con solo las filas de dos personas. Formato horizontal. **EVITAR:** iconografía de alerta, resaltados, revelar datos de la ventana del cierre.
**TEXTO A COMPONER:** *Persona · Sistema · Evento · Hora* — **G. Prada · Tarjeta · Lobby, torniquete · 08:46** — **G. Prada · Sesión · Inicio de sesión · 08:52** — **G. Prada · Tarjeta · Piso 4, archivo financiero · 14:47** — **G. Prada · Videollamada · Conexión · 15:05** — **G. Prada · Videollamada · 15:08–15:30 · pendiente de verificación** — **V. Rojas · Tarjeta · Piso 5, asistencia ejecutiva · 08:20** — **V. Rojas · Sesión · Inicio de sesión · 08:24** — **V. Rojas · Tarjeta · Piso 5, asistencia ejecutiva · 14:58** — **V. Rojas · Correo · 15:15–15:25 · pendiente de verificación**.

(`gallery/v2b-fragmento-esteban-patricia.png` — V2 fragmento privado, T+24)
**PROMPT:** Fragmento recortado de la misma matriz de V2 (mismo diseño, cortado por los bordes) con solo las filas de dos personas. Formato horizontal. **EVITAR:** iconografía de alerta, resaltados, revelar datos de la ventana del cierre.
**TEXTO A COMPONER:** *Persona · Sistema · Evento · Hora* — **E. Navas · Tarjeta · Lobby, torniquete · 08:15** — **E. Navas · Sesión · Inicio de sesión · 08:20** — **E. Navas · Tarjeta / impresora · 15:00–15:40 · pendiente de verificación** — **P. Vega · Sesión · Inicio de sesión · 08:03** — **P. Vega · Sesión · 08:03 → 15:50 · activa, sin interrupciones** — **P. Vega · otros eventos · 15:00–15:40 · pendiente de verificación**.

(`gallery/v2c-fragmento-daniela-sergio.png` — V2 fragmento privado, T+24)
**PROMPT:** Fragmento recortado de la misma matriz de V2 (mismo diseño, cortado por los bordes) con solo las filas de dos personas. Formato horizontal. **EVITAR:** iconografía de alerta, resaltados, revelar datos de la ventana del cierre.
**TEXTO A COMPONER:** *Persona · Sistema · Evento · Hora* — **D. Mercado · Tarjeta · Lobby, torniquete · 08:30** — **D. Mercado · Sesión · Inicio de sesión · 08:34** — **D. Mercado · Sesión · 15:00–15:40 · pendiente de verificación** — **S. Lemos · Tarjeta · Lobby, torniquete · 09:05** — **S. Lemos · Videollamada · Conexión a reunión programada · 15:02** — **S. Lemos · Videollamada · 15:02–15:35 · detalle pendiente de verificación**.

(`gallery/v6-registro-tarjetas.png` — V6, registro de tarjetas)
**PROMPT:** Captura de pantalla diegética de un sistema simple de control de accesos, con una tabla funcional de credenciales, puertas y horas durante el periodo del cierre, interfaz sobria, con filas de varias credenciales y sin ningún resaltado; el vacío de una credencial entre dos de sus filas debe notarse por ausencia, no por color. Formato horizontal. **EVITAR:** iconografía de alarma, resaltar el vacío, rojo.
**TEXTO A COMPONER:** columnas *Hora · Credencial · Puerta*. Filas en orden cronológico: **14:47 G. Prada · Piso 4, archivo financiero** — **14:56 R. Téllez · PB, torniquete (modo cierre)** — **14:58 V. Rojas · Piso 5, asistencia ejecutiva** — **15:01 C. Duque · PB, recepción** — **15:03 E. Navas · Piso 3, sala técnica** — **15:08 H. Saldaña · Piso 5, cuarto de mantenimiento** — **15:34 G. Prada · Piso 4, archivo financiero** — **15:40 R. Téllez · PB, torniquete (fin del cierre)**. No añadir ninguna fila de G. Prada entre 14:47 y 15:34.

(`gallery/v7-registro-ascensores.png` — V7, registro de ascensores)
**PROMPT:** Captura de pantalla diegética de un sistema simple de registro de uso de ascensores, con una tabla funcional de piso de origen, piso de destino y hora, interfaz sobria. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados.
**TEXTO A COMPONER:** columnas *Hora · Origen · Destino · Nota*. Filas: **14:49 PB → Piso 5** — **14:53 PB → Piso 3** — **15:00 — Ascensores restringidos a uso esencial (cierre de seguridad)** — **15:08 PB → Piso 2 · Uso esencial autorizado** — **15:26 Piso 2 → PB** — **15:40 — Fin del cierre de seguridad** — **15:43 Piso 5 → Piso 6**. Ninguna fila con origen o destino en el piso 6 entre las 15:00 y las 15:40.

(`gallery/v9-correo-rodolfo.png` — V9, correo de Rodolfo)
**PROMPT:** Captura de pantalla diegética de una bandeja de correo corporativo genérica en formato vertical, con la lista de mensajes de la tarde y dos respuestas breves y operativas enviadas desde la cuenta de "Rodolfo Castaño", en una interfaz simple. Formato vertical. **EVITAR:** iconografía de alarma, mostrar "en nombre de" o el nombre de Valentina en la interfaz.
**TEXTO A COMPONER:** carpeta *Enviados — Rodolfo Castaño*. **15:19 · Para: Contabilidad General · Asunto: RE: Cierre de mes — aprobación pendiente** — «Recibido, lo reviso apenas termine el cierre y les confirmo. Quedo atenta a cualquier novedad.» **15:24 · Para: Secretaría de la Junta Directiva · Asunto: RE: Reunión de hoy** — «Confirmo mi asistencia. Por favor dejen lista la sala.» Mensajes recibidos de relleno antes de las 15:00.

(`gallery/v10-registro-impresora.png` — V10, registro de impresora)
**PROMPT:** Captura de pantalla diegética de un sistema simple de registro de trabajos de impresión, con una tabla funcional de credencial, piso de la impresora, hora y documento, interfaz sobria. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados.
**TEXTO A COMPONER:** columnas *Hora · Credencial · Impresora · Documento*. Filas: **14:20 R. Castaño · Piso 6 · Agenda_Junta.pdf (1 pág.)** — **14:36 V. Rojas · Piso 5 · Actas_Comite.pdf (6 págs.)** — **14:58 D. Mercado · Piso 5 · Proy_Ventas_Q3.pdf (3 págs.)** — **15:12 E. Navas · Piso 3 · Plano_rack_sala_tecnica.pdf (2 págs.)**.

(`gallery/v11-reporte-automatico.png` — V11, reporte automático)
**PROMPT:** Captura de pantalla diegética de un correo o documento de reporte corporativo, formato vertical, con un encabezado técnico simple que indica que fue generado automáticamente y la hora fija de envío diario, y un cuerpo con una pequeña tabla de cifras de relleno. Formato vertical. **EVITAR:** iconografía de alarma, destacar el encabezado.
**TEXTO A COMPONER:** *De: Sistema de reportes de RR. HH. (no responder) · Para: P. Vega · Asunto: Reporte diario de novedades de personal.* Encabezado técnico: **Generado automáticamente — envío programado: todos los días a las 15:15.** Hora de este envío: **15:15**. Cuerpo de relleno: *Altas: 0 · Bajas: 0 · Ausentismo: 2 %*.

(`gallery/v12-sesion-daniela.png` — V12, sesión de Daniela)
**PROMPT:** Captura de pantalla diegética de un registro simple de actividad de sesión de computador, con inicio de sesión y, más adelante, una breve actividad de apertura de un archivo compartido, interfaz sobria. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados, indicar quién estaba frente al equipo.
**TEXTO A COMPONER:** *Usuario: D. Mercado.* Filas: **08:34 Inicio de sesión** — **15:14 Apertura de archivo compartido «Proyecciones_Q3_Comercial.xlsx»** — **15:16 Cierre del archivo** — y al pie: **Estado de la sesión: activa, sin bloqueo de pantalla (08:34 → 15:50)**.

(`gallery/v13-videollamada-sergio.png` — V13, videollamada de Sergio)
**PROMPT:** Captura de pantalla diegética de un panel simple de registro de una videollamada corporativa, con un participante conectado con la cámara apagada y sin ninguna actividad de audio durante toda la sesión, en el mismo estilo de interfaz que V8. Formato horizontal. **EVITAR:** iconografía de alarma, interpretación escrita.
**TEXTO A COMPONER:** *Reunión: Revisión comercial semanal.* Participante: **S. Lemos**. **Conexión: 15:02 → 15:35 (continua). Cámara: apagada. Actividad de audio: ninguna.**

(`gallery/v4-cctv-escalera.png` — V4, CCTV2 escalera)
**PROMPT:** Imagen diegética de cámara de seguridad fija de bajo perfil de una escalera interna de emergencia, ángulo elevado y algo lateral, con una persona subiendo entre dos pisos, vista de espaldas o de forma parcial sin rostro identificable, de complexión media, cabello oscuro y camisa azul clara sin corbata; calidad de vídeo de seguridad granulada, más oscura y de menor resolución que la del lobby. Deja libre una esquina para la marca de hora. Formato horizontal. **EVITAR:** rostro identificable, dramatización, cambiar la ropa respecto a V3.
**TEXTO A COMPONER:** marca de hora discreta: *CAM ESC-INT P4-P5 — 15:11:38*.

(`gallery/v8-videollamada-gonzalo.png` — V8, videollamada de Gonzalo)
**PROMPT:** Captura de pantalla diegética de un panel simple de registro de una videollamada corporativa, con una línea de tiempo por participante que separa visualmente la conexión (continua), el estado de la cámara y la actividad de audio a lo largo del tiempo, mostrando un tramo prolongado sin actividad de audio ni cámara, interfaz sobria. Formato horizontal. **EVITAR:** iconografía de alarma, interpretación escrita, resaltar el tramo.
**TEXTO A COMPONER:** *Videollamada: Coordinación de equipo — Finanzas. Participante: G. Prada.* **Conexión: 15:05 → 15:30 (continua).** **Cámara: encendida 15:05–15:08 · apagada 15:08–15:23 · encendida 15:23–15:30.** **Actividad de audio: sí 15:05–15:08 · ninguna 15:08–15:23 · sí 15:23–15:30.**

(`gallery/v14-escena-oficina.png` — V14, escena)
**PROMPT:** Fotografía diegética funcional de una oficina ejecutiva ordenada, con un escritorio, una estantería con reconocimientos y una silla, sin mostrar el cuerpo (puede sugerirse fuera de cuadro). El foco es el escritorio, con apenas un detalle fuera de lugar: en una esquina hay un círculo de superficie más limpio, sin la fina capa de polvo del resto, donde antes estaba un objeto. Luz de oficina neutra. Formato 4:5. **EVITAR:** sangre, gore, cuerpo en primer plano, dramatización, trofeo en el escritorio.

(`gallery/v15-trofeo.png` — V15, trofeo)
**PROMPT:** Fotografía diegética funcional de un trofeo de reconocimiento corporativo de escritorio sobre una superficie neutra, tomada durante la inspección, con la superficie del trofeo inusualmente limpia en comparación con otros objetos de la misma estantería que se ven al fondo con una fina capa de polvo. Sin sangre ni ningún indicio de violencia. Luz neutra de interior. Formato 4:5. **EVITAR:** sangre, daño, marcas de violencia, resaltados artificiales sobre la limpieza, inscripción legible.

(`gallery/v16-borrador-auditoria.png` — V16, borrador de auditoría)
**PROMPT:** Documento diegético tipo página de informe de auditoría interna impreso, fotografiado sobre un escritorio, con lenguaje profesional y sobrio, en formato A4 vertical, con la zona de texto vacía para componer. Formato vertical. **EVITAR:** texto acusatorio dramatizado, iconografía de alerta, tachaduras.
**TEXTO A COMPONER:** *INFORME DE AUDITORÍA INTERNA — BORRADOR v3. Dirección Financiera, Andesol Technologies. Para presentación a la Junta Directiva.* **Hallazgo 1:** pagos recurrentes al proveedor «Meridian Soluciones Operativas S.A.S.» por «consultoría operativa»: 21 pagos en 22 meses, por un total de USD 184.300. **Hallazgo 2:** sin contrato marco, sin orden de servicio ni entregables verificables. **Hallazgo 3:** el proveedor fue dado de alta por la Subdirección Financiera. **Aprobación de los pagos: Gonzalo Prada, Subdirector Financiero.** *Recomendación: suspender los pagos y presentar el caso a la Junta Directiva.* Elaborado por: R. Castaño.

(`gallery/v17-facturas-proveedor.png` — V17, facturas)
**PROMPT:** Documento diegético tipo factura comercial genérica con membrete de una empresa proveedora ficticia, concepto vago y monto elevado, repetida en varias fechas a lo largo de casi dos años, en un documento contable simple impreso y fotografiado sobre un escritorio (varias hojas apiladas, la superior legible). Formato vertical A4. **EVITAR:** iconografía de alerta, montos dramatizados, logos reales.
**TEXTO A COMPONER:** membrete **Meridian Soluciones Operativas S.A.S.** Tabla *Factura · Fecha · Concepto · Monto*: **0007 · 15/11/2024 · Consultoría operativa · USD 8.200** — **0019 · 14/04/2025 · Consultoría operativa · USD 9.100** — **0028 · 15/09/2025 · Consultoría operativa · USD 8.700** — **0041 · 14/08/2026 · Consultoría operativa · USD 9.600**. Sello al pie: **Aprobado para pago — Subdirección Financiera (G.P.)**. (Si cambia el año del caso, desplazar las fechas manteniendo los 22 meses de rango.)

(`gallery/v18-mensaje-gonzalo.png` — V18, mensaje posterior)
**PROMPT:** Captura de pantalla diegética de un mensaje breve de mensajería profesional, enviado esa misma tarde por una persona a un colega, en una interfaz simple y genérica con formato de captura de móvil, tono ligero. Formato vertical. **EVITAR:** iconografía de alarma, tono evidentemente sospechoso, logos de apps reales.
**TEXTO A COMPONER:** contacto *Colega — equipo Finanzas*. Mensaje de **Gonzalo Prada, 16:12**: «¿Todo bien con la llamada de hoy? ¿No me viste raro, no?»

(`gallery/v5-cctv-lobby-tras-cierre.png` — V5, CCTV3 lobby tras el cierre)
**PROMPT:** Imagen diegética de cámara de seguridad fija con el mismo ángulo, encuadre y estilo que V3, del lobby justo después de levantarse el cierre de seguridad, con el flujo normal de personas reanudándose y sin ninguna entrada o salida fuera de lo común, figuras sin rostros nítidos. Formato horizontal. **EVITAR:** rostros identificables, dramatización, cambiar el ángulo respecto a V3.
**TEXTO A COMPONER:** marca de hora discreta: *CAM LOBBY 01 — 15:42:10*.

## 4. Audio

Los guiones están en `case.php`; se exportan listos para estudio con
`php artisan immersion:export-audio-scripts nadie-salio-del-edificio`. Guardar en `audio/`.
**A1 es multivoz** (tres hablantes con etiquetas `Speaker 1/2/3` en el guion): grábala con
tres voces o con voz múltiple, y quita las etiquetas del audio final. Los silencios están
descritos en `audio_scene`, no en el guion.

(`audio/a2-anuncio-cierre.wav` — A2, anuncio del cierre, 12–15 s)
**PROMPT:** Anuncio de megafonía diegético en un edificio corporativo, voz adulta profesional y calmada, que informa de la activación de un protocolo de cierre de seguridad por un objeto no identificado en recepción y pide a los empleados que permanezcan en sus puestos; tono de protocolo estándar sin alarma excesiva; ambiente de oficina de fondo y un breve tono de aviso antes de la voz. Sin música. **EVITAR:** tono de pánico, música de tensión, sirenas.

(`audio/a1-fragmento-videollamada.wav` — A1, fragmento de la videollamada de Gonzalo, 18–22 s)
**PROMPT:** Fragmento de audio diegético de una videollamada corporativa con varias voces: una colega pregunta algo directamente a un compañero llamado Gonzalo; sigue un silencio prolongado y extraño de unos seis segundos, sin nada del lado de Gonzalo y solo con el leve ruido de fondo de otra sala; otro colega comenta, algo extrañado, que Gonzalo sigue conectado; tras otro silencio breve, responde Gonzalo con voz apresurada y algo fuera de sincronía con la conversación. Sin música. **EVITAR:** música de tensión, confirmación explícita de culpabilidad, tono dramático.

(A3 — confesión de Gonzalo) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Charon) para incluir las preguntas que esa mesa le hizo a Gonzalo.
