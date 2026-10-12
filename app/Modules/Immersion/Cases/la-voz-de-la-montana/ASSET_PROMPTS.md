# Prompts de assets — La Voz de la Montaña

Total: **1 portada + 10 retratos + 16 evidencias visuales + 1 audio** (más la confesión de
Ignacio, que se genera por partida y no se graba).

Todo se guarda en `public/immersion/la-voz-de-la-montana/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos (víctima, 6 sospechosos, 2 testigos y el sistema) |
| `gallery/` | las 16 evidencias visuales |
| `audio/` | `a1-transmision-adrian.wav` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Audio A1**: es el gancho central del caso. Hazlo antes que las evidencias; su
   tono es la parte más delicada (ver sección 4).
5. **Evidencias V1 → V15** en orden de entrega (ver tabla). V9, V10 y V11 deben salir de la
   misma "sesión" visual (misma repisa, misma luz fría de montaña). V2a y V2 son la misma
   hoja de bitácora en dos versiones.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V3 |
| Expansión | 15 | V2a (bitácora parcial), A1 |
| Pistas privadas (a 2 jugadores al azar, 2 piezas cada una) | 30 | V4 + V5 · V13 + V15 |
| Complicación | 50 | V7, V8, V2 (completa), V6, V14 |
| Giro | 68 | V9, V10, V11, V12 |
| Cierre | 75 | (sin imágenes nuevas) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el
dato exacto ES la prueba (emisión 11:52 ≠ retransmisión 16:48; separación 08:40; "Ignacio sin
tarea asignada"). Para V2a, V2, V4, V5, V6, V7, V8, V10, V12, V13, V14 y V15: genera la imagen
con la zona de texto **vacía o con texto de relleno ilegible**, y compón después el texto
exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica cada
hora contra `case.php` antes de guardar. V1 también lleva nombres de lugares: compónlos.

## Reloj del caso (para verificar cada pieza)

07:00 salida del campamento · 08:40 separación en la Bifurcación de la Canaleta · ~10:20 Adrián
pasa por el Refugio Intermedio · ~10:45 pasa Ignacio · **11:52 parte de radio (emisión)** ·
~12:00 encuentro y caída · 14:20 Valentina llega al Refugio Superior (14:33 aviso por radio) ·
15:00 se activa la búsqueda · **16:48 retransmisión** · 16:53 entrada "contacto confirmado" en la
bitácora · amanecer: hallazgo del cuerpo.

## Biblia visual de continuidad (pegar en los retratos)

- **Adrián Castel**, 42: complexión atlética delgada, barba corta, ropa técnica de alta montaña en tonos oscuros, casco y arnés de escalada.
- **Ignacio Bravo**, 52: complexión robusta, cabello canoso corto, chaleco de coordinador con identificación de la organización, radio en el pecho.
- **Valentina Ruiz**, 34: cabello oscuro recogido bajo un gorro técnico, ropa de escalada técnica, arnés y material de cordada.
- **Marcos Elizalde**, 39: ropa de interior/base, auriculares de operador, varias pantallas de radio frente a él.
- **Renato Salcedo**, 45: complexión media, ropa técnica de guía con logo genérico de su propia empresa.
- **Camila Ibarra**, 38: ropa de campamento práctica, tablet o carpeta de gestión en mano.
- **Tomás Vidal**, 26: chaleco de rescatista, auriculares, aspecto juvenil y entusiasta.
- **Silvia Paredes**, 44: chaleco de logística, radio y lista de equipo en mano.
- **Hernán Quispe**, 58: ropa de refugio, gorro de lana, entorno de un refugio de piedra sencillo.
- **Cerro Centinela**: terreno rocoso de alta montaña, clima frío con nubes bajas intermitentes, refugios de piedra funcionales, campamento base con carpas y antenas, repetidores en collados expuestos.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales. Ignacio debe verse confiable y querido, nunca ominoso.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio de montaña: una cresta rocosa de alta montaña al amanecer, con nubes bajas que se abren y un repetidor de radio pequeño sobre un collado expuesto en primer plano, y a lo lejos un refugio de piedra apenas visible; sensación de aislamiento, frío y espera, con una antena de radio como único elemento humano. Debe sugerir que lo importante viaja por el aire, no por la ruta. Sin personas visibles. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, escaladores en peligro, caída dramática, avalancha, iconografía policial, estética de terror, texto o logos.

## 2. Retratos

(`photos/adrian-castel.png` — víctima)
**PROMPT:** Retrato diegético tipo foto personal de un hombre de 42 años, complexión atlética delgada, barba corta, ropa técnica de alta montaña en tonos oscuros, casco puesto sobre la frente y arnés visible, expresión serena y concentrada de guía experimentado, fondo de roca y cielo frío desenfocados. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales.

(`photos/ignacio-bravo.png` — Ignacio Bravo)
**PROMPT:** Retrato diegético de un hombre de 52 años, complexión robusta, cabello canoso corto, chaleco de coordinador con identificación genérica de la organización de rescate, radio en el pecho, expresión segura, paternal y tranquila de mentor respetado, fondo de carpa de operaciones del campamento base. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, sombras.

(`photos/valentina-ruiz.png` — Valentina Ruiz)
**PROMPT:** Retrato diegético de una mujer de 34 años, cabello oscuro recogido bajo un gorro técnico, ropa de escalada técnica, arnés y material de cordada, expresión afligida, cansada y profesional, fondo de refugio de piedra con luz fría. Formato retrato. **EVITAR:** llanto exagerado, gesto de culpa obvio, dramatización.

(`photos/marcos-elizalde.png` — Marcos Elizalde)
**PROMPT:** Retrato diegético de un hombre de 39 años, ropa de interior/base, auriculares de operador al cuello, varias pantallas de radio y registros desenfocadas frente a él, expresión técnica, meticulosa y levemente a la defensiva, luz de pantalla fría. Formato retrato. **EVITAR:** ciencia ficción, pantallas con alarmas rojas, gesto de sospechoso teatral.

(`photos/renato-salcedo.png` — Renato Salcedo)
**PROMPT:** Retrato diegético de un hombre de 45 años, complexión media, ropa técnica de guía con el logo genérico de una empresa de guías (sin texto legible), expresión cordial y segura con un leve gesto tenso, fondo de cresta rocosa y cielo nublado. Formato retrato. **EVITAR:** villano obvio, sonrisa siniestra, marcas reales.

(`photos/camila-ibarra.png` — Camila Ibarra)
**PROMPT:** Retrato diegético de una mujer de 38 años, ropa de campamento práctica, con una tablet o carpeta de gestión en la mano, expresión contenida, protectora y fatigada, fondo de carpa del campamento base. Formato retrato. **EVITAR:** llanto exagerado, apariencia de sospechosa, dramatización.

(`photos/tomas-vidal.png` — Tomás Vidal)
**PROMPT:** Retrato diegético de un hombre de 26 años, chaleco de rescatista, auriculares puestos, aspecto juvenil y entusiasta pero algo nervioso, fondo del puesto de escucha de radio en una carpa. Formato retrato. **EVITAR:** apariencia de culpable, gesto de engaño, dramatización.

(`photos/silvia-paredes.png` — Silvia Paredes)
**PROMPT:** Retrato diegético de una mujer de 44 años, chaleco de logística, radio al pecho y lista de equipo en la mano, expresión serena, práctica y directa, fondo de zona de logística del campamento base con cajas y carpas. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/hernan-quispe.png` — Hernán Quispe)
**PROMPT:** Retrato diegético de un hombre de 58 años, ropa de refugio, gorro de lana, rostro curtido y parco, expresión tranquila de hombre de montaña, fondo de refugio de piedra sencillo con una ventana pequeña. Formato retrato. **EVITAR:** apariencia de sospechoso, cliché de ermitaño, dramatización.

(`photos/sistema-red-centinela.png` — Red Centinela)
**PROMPT:** Imagen diegética tipo retrato funcional de una consola de comunicaciones de un centro de radio de montaña, con pantallas sobrias que muestran listas de repetidores y tablas de transmisiones con horas, usada como avatar del sistema de registro. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, fallos visibles, estética futurista, texto legible crítico.

## 3. Evidencias visuales

(`gallery/v1-mapa-cerro-centinela.png` — V1, mapa general)
**PROMPT:** Mapa diegético limpio y funcional de una montaña técnica, estilo mapa de operación de rescate impreso o digital simple, con curvas de nivel suaves. **Geometría obligatoria (las coartadas dependen de ella):** la ruta principal es una sola línea que va, en este orden, desde el Campamento Base hasta la Bifurcación de la Canaleta, luego al Refugio Intermedio, luego al Paso del Águila y por último al Refugio Superior; el Refugio Intermedio queda ANTES del Paso y el Paso entre el refugio intermedio y el superior. En la Bifurcación de la Canaleta (la primera encrucijada, antes del Refugio Intermedio) sale una variante lateral (Variante del Hombro) que rodea la montaña por el norte, pasando por el Hombro Norte y la Cresta del Hombro, **sin pasar por el Refugio Intermedio ni por el Paso del Águila**, y vuelve a llegar al Refugio Superior por otro lado. La Cresta Este es un sector aparte, al este, claramente alejado de la ruta principal y del Paso del Águila, sin conexión con la variante. Tres repetidores de radio marcados con un símbolo de antena: uno en el Campamento Base, uno en un collado al sur del Paso del Águila, fuera de la ruta principal (Collado Sur), y uno en el Refugio Superior. Dejar las etiquetas vacías. Formato vertical o cuadrado. **EVITAR:** iconografía de alerta, paisajes imposibles, alterar el orden de la ruta principal, que la variante pase por el Refugio Intermedio o por el Paso del Águila, marcar el lugar de ningún incidente.
**TEXTO A COMPONER (etiquetas):** *Campamento Base · Bifurcación de la Canaleta · Refugio Intermedio · Paso del Águila · Refugio Superior · Variante del Hombro · Hombro Norte · Cresta del Hombro · Cresta Este · Collado Sur (repetidor RPT-02) · Repetidor Base (RPT-01) · Refugio Superior (repetidor RPT-03).*

(`gallery/v3-campamento-base.png` — V3, campamento base)
**PROMPT:** Fotografía diegética documental de un campamento base de montaña de tarde, con carpas de operación, equipo de radio, antenas y personas genéricas trabajando en la coordinación de un rescate, siluetas sin rostros reconocibles, tensión contenida, luz fría de exterior y nubes bajas. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, dramatización excesiva, texto legible.

(`gallery/v2a-bitacora-parcial.png` — V2 parcial, entrega a T+15)
**PROMPT:** Documento diegético tipo bitácora de operación de rescate, impreso o digital simple, con una tabla de entradas cronológicas breves (hora, evento, quién registró), en formato funcional y sobrio, **cortado o parcial**: la página termina bruscamente tras la tercera entrada. Dejar vacías las celdas de texto. Formato vertical. **EVITAR:** iconografía de alarma, lenguaje que ya revele el error, dramatizar la entrada del contacto.
**TEXTO A COMPONER:** título *Bitácora del operativo — Cerro Centinela (copia parcial)*; columnas *Hora · Evento · Registró*.
- *15:00 · Activación oficial de la búsqueda. Coordinador de campo: I. Bravo · S. Paredes*
- *15:20 · Salen Equipo A (Refugio Superior, ruta alta) y Equipo B (Collado Sur) · S. Paredes*
- *16:53 · Contacto confirmado con superviviente — transmisión recibida · T. Vidal*

(`gallery/v4-cuaderno-adrian.png` — V4, cuaderno personal)
**PROMPT:** Fotografía diegética de una página de cuaderno personal manuscrito, papel con rayas, letra pequeña y cuidadosa, con anotaciones breves sobre un caso antiguo, hallada entre las pertenencias de Adrián, luz cálida y tenue de carpa. Dejar el cuerpo del texto vacío o ilegible. Formato vertical. **EVITAR:** iconografía de alerta, nombres completos, identificar a ningún sospechoso, subrayados dramáticos.
**TEXTO A COMPONER (manuscrito):** *Expedición de hace dos temporadas (marzo). Revisar archivo. El informe de inspección de anclajes tiene una firma que falta. ¿Quién lo cerró? Preguntar. Si no responden, hablar con la federación. No dejarlo pasar.*

(`gallery/v5-informe-inspeccion.png` — V5, informe de inspección)
**PROMPT:** Documento diegético tipo informe de inspección de equipo de expedición, expediente técnico sobrio de archivo, impreso o escaneado, con una tabla de verificación de anclajes y sistemas de aseguramiento, y un espacio de firma de responsable visiblemente vacío. Dejar vacías las celdas de texto. Formato vertical. **EVITAR:** iconografía de alarma, nombres de empresa que suenen a marca real, nombres de personas.
**TEXTO A COMPONER:** título *Informe de inspección de equipo — expedición comercial*; fecha *14/03 (hace dos temporadas)*. Tabla *Punto de verificación · Estado*: *Anclajes fijos · OK* — *Cuerdas y sistemas de progresión · OK* — *Sistema de aseguramiento del cliente · (casilla sin marcar)* — *Revisión de cierre · (casilla sin marcar)*. Pie: *Firma del responsable:* ________ (**vacía, sin nombre**).

(`gallery/v13-plan-de-ruta.png` — V13, plan de ruta)
**PROMPT:** Fotografía diegética de una hoja de planificación de ruta escrita a mano, con una lista de puntos de la montaña y horarios estimados de paso, papel algo arrugado, luz cálida y tenue de carpa. Dejar el texto vacío o ilegible. Formato vertical. **EVITAR:** iconografía de alerta, tachaduras dramáticas.
**TEXTO A COMPONER (manuscrito):** *Plan — salida 07:00 Campamento Base · 10:15 Refugio Intermedio · **11:45–12:00 Paso del Águila (reporte por radio)** · 13:30 Refugio Superior. Protocolo: cordada unida hasta el Refugio Intermedio.*

(`gallery/v15-mensaje-a-ignacio.png` — V15, mensaje ambiguo)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería breve, interfaz simple y genérica de teléfono móvil, con un único mensaje largo enviado por el usuario y el indicador de leído. Dejar vacío el cuerpo del mensaje. Formato vertical. **EVITAR:** iconografía de alarma, logos de apps reales, detalle explícito del informe.
**TEXTO A COMPONER:** contacto *Ignacio Bravo*; encabezado *lunes · 21:12*; mensaje de Adrián: *«Ignacio, tenemos que hablar de lo de la inspección. No puedo quedarme callado más tiempo.»* — estado *Leído*. (No añadir respuesta.)

(`gallery/v7-registro-red-centinela.png` — V7, registro de repetidores)
**PROMPT:** Captura de pantalla diegética de un sistema técnico de gestión de repetidores de radio, interfaz sobria y funcional, con una tabla de transmisiones del día con columnas de identificador, repetidor, dos horas y modo. El desfase entre las dos horas es un dato más de la tabla, sin resaltarlo. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, flechas o círculos sobre el desfase.
**TEXTO A COMPONER:** título *Red Centinela — informe de transmisiones del día*; columnas *ID radio · Asignada a · Repetidor · Emisión (est.) · Retransmisión · Modo*.
- *R-05 · V. Ruiz · RPT-03 Refugio Superior · 14:33 · 14:33 · Directa*
- ***R-07 · A. Castel · RPT-02 Collado Sur · 11:52 · 16:48 · Retenida (baja señal)***
- *R-14 · Equipo B · RPT-02 Collado Sur · 16:48 · 16:48 · Directa*
(Ninguna otra fila para R-07.)

(`gallery/v8-nota-tecnica-repetidores.png` — V8, nota técnica)
**PROMPT:** Documento diegético tipo extracto de manual técnico de un sistema de radio de montaña, una página impresa o digital simple con un párrafo breve destacado, formato de manual funcional y sobrio. Dejar el texto vacío. Formato vertical. **EVITAR:** iconografía de alarma, jerga técnica excesiva.
**TEXTO A COMPONER:** título *Manual de operación — Red Centinela*; sección *4.3 Modo de almacenamiento por baja señal*: *«En zonas de cobertura débil, el repetidor almacena la transmisión recibida y la reenvía de forma automática cuando detecta mejor cobertura (por ejemplo, cuando otro equipo entra en su zona de alcance). Este comportamiento es normal y está documentado: la hora de emisión y la hora de retransmisión pueden diferir en varias horas. Repetidores en zona de cobertura débil: RPT-02 Collado Sur.»*

(`gallery/v2-bitacora-completa.png` — V2 completa, entrega a T+50)
**PROMPT:** La misma hoja de bitácora que V2a (mismo diseño, mismo encabezado y mismas tres entradas originales en el mismo orden relativo), ahora completa y sin cortes, con entradas adicionales antes y después. Dejar vacías las celdas de texto. Formato vertical. **EVITAR:** cambiar el diseño respecto a V2a, iconografía de alarma, resaltar la entrada del contacto.
**TEXTO A COMPONER:** título *Bitácora del operativo — Cerro Centinela*; columnas *Hora · Evento · Registró*.
- *14:35 · Refugio Superior informa: A. Castel no ha llegado; su compañera V. Ruiz lo espera desde las 14:20 · S. Paredes*
- *15:00 · Activación oficial de la búsqueda. Coordinador de campo: I. Bravo · S. Paredes*
- *15:20 · Salen Equipo A (Refugio Superior, ruta alta) y Equipo B (Collado Sur) · S. Paredes*
- *16:53 · Contacto confirmado con superviviente — transmisión recibida · T. Vidal*
- *17:10 · Se concentra la búsqueda en el sector de Collado Sur · S. Paredes*
- *17:35 · Consulta de familiar (C. Ibarra) sobre la cobertura del seguro de rescate de la empresa · S. Paredes*
- *21:00 · Persisten dudas sobre la ubicación; se refuerza el sector de Collado Sur · S. Paredes*
- *21:40 · Se solicita a Red Centinela el informe de transmisiones del día · S. Paredes*

(`gallery/v6-checkpoints-valentina.png` — V6, registro de ruta de Valentina)
**PROMPT:** Captura de pantalla diegética de una aplicación deportiva de seguimiento de ruta, interfaz simple y funcional, con un trazado de la ruta de una persona sobre un mapa topográfico sencillo, un trazado principal punteado y gris más cercano al Paso del Águila, y marcas de hora en distintos puntos. El trazado de ella se separa claramente del principal en un punto. Dejar vacías las etiquetas. Formato vertical. **EVITAR:** iconografía de alarma, resaltados artificiales.
**TEXTO A COMPONER:** título *Registro de ruta — V. Ruiz*. Puntos: *07:02 Campamento Base* · ***08:40 Bifurcación de la Canaleta — sale del trazado principal (Variante del Hombro)*** · *10:30 Hombro Norte* · *12:05 Cresta del Hombro* · *14:20 Refugio Superior — llegada*.

(`gallery/v14-asignacion-tareas.png` — V14, asignación de tareas)
**PROMPT:** Documento diegético tipo hoja de asignación de tareas de un operativo de montaña, formato operativo sobrio, impreso o digital simple, con dos bloques de tabla (mañana y tarde). Ninguna fila resaltada. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alarma, resaltar la fila de Ignacio, resaltados artificiales.
**TEXTO A COMPONER:** título *Asignación de tareas — jornada Cerro Centinela*. Columnas *Sector / función · Responsable · Franja*.
- Bloque MAÑANA (06:00–14:59): *Campamento Base — logística · S. Paredes · desde 06:00* — *Campamento Base — comunicaciones · M. Elizalde · desde 06:00* — *Puesto de escucha · T. Vidal · desde 07:00* — *Refugio Intermedio · H. Quispe · desde 06:00* — *Paso del Águila / Refugio Superior (ascenso programado) · A. Castel, V. Ruiz · desde 07:00* — ***I. Bravo · Sin tarea asignada** (en blanco)* — *Grupos externos registrados: Guías Salcedo Montaña (2) — Cresta Este — acreditación: por verificar*.
- Bloque TARDE (desde 15:00): *Coordinación de campo · I. Bravo · desde 15:00* — *Equipo A · Refugio Superior (ruta alta)* — *Equipo B · Collado Sur* — *Paso del Águila · sin asignación inicial*.

(`gallery/v9-camara-accion.png` — V9, metraje de la cámara de acción)
**PROMPT:** Tira de cuatro fotogramas diegéticos de una cámara de acción personal montada en el pecho (punto de vista), horizontal, con calidad de compresión y movimiento realistas: terreno rocoso de alta montaña con nubes bajas, un tramo técnico del Paso del Águila; en el segundo fotograma una mano enguantada sostiene un micrófono de radio contra el pecho; en el tercero se ve un tramo de roca sin cuerda fija; en el último, borroso, una silueta parcial y genérica de otra persona acercándose desde un lado, sin rasgos identificables. Luz fría y nublada. Dejar vacías las marcas de hora. **EVITAR:** rostros identificables, ropa o chaleco reconocibles, violencia explícita, cualquier elemento que resuelva el caso por sí solo.
**TEXTO A COMPONER (sello de hora de cada fotograma):** *11:47* · *11:52* · *11:55* · *11:58 (último fotograma antes de que se corte el metraje)*.

(`gallery/v10-aseguramiento-anomalo.png` — V10, sistema de aseguramiento)
**PROMPT:** Fotografía diegética funcional de un arnés y una longe con un mosquetón de seguridad abierto, hallados sobre roca, con una pequeña nota técnica escrita a mano por el equipo de rescate sobre un papel junto al equipo. Formato de evidencia de campo, luz fría natural. Dejar vacía la nota. Formato horizontal. **EVITAR:** sangre, elementos gráficos, iconografía de alarma, dramatizar.
**TEXTO A COMPONER (nota manuscrita):** *Mosquetón de la longe hallado abierto (gatillo y seguro liberados), sin deformación ni marcas de carga de caída. Según el protocolo de la federación, este estado no es compatible con una caída accidental simple. — Equipo de rescate, 06:40.*

(`gallery/v11-lugar-del-hallazgo.png` — V11, lugar del hallazgo)
**PROMPT:** Fotografía diegética funcional de una repisa de roca fuera de la ruta habitual de descenso, a un lado y por debajo de un paso técnico, con equipo personal disperso de forma realista (una mochila, un casco) y sin ningún elemento gráfico; fotografiada al amanecer durante la investigación, luz fría natural. Sin cuerpo visible. Formato horizontal. **EVITAR:** restos humanos visibles, sangre, dramatización.

(`gallery/v12-parte-meteorologico.png` — V12, parte meteorológico)
**PROMPT:** Documento diegético tipo parte meteorológico interno de una operación de montaña, formato de registro funcional y sobrio, con una tabla simple de condiciones por hora. Dejar vacías las celdas. Formato vertical. **EVITAR:** iconografía de alarma, sugerir avalancha u otro evento climático masivo como causa.
**TEXTO A COMPONER:** título *Parte meteorológico interno — jornada*; columnas *Hora · Temp. · Viento · Visibilidad*: *06:00 · −2 °C · 10 km/h · >2 km* — *09:00 · 1 °C · 15 km/h · 1,5 km* — *12:00 · 3 °C · 20 km/h · 400 m (nubes bajas)* — *15:00 · 2 °C · 25 km/h · 120 m (niebla)* — *18:00 · −1 °C · 20 km/h · 60 m (niebla)*.

## 4. Audio

El guion está en `case.php`; se exporta listo para estudio con
`php artisan immersion:export-audio-scripts la-voz-de-la-montana`. Guardar en `audio/`.

(`audio/a1-transmision-adrian.wav` — A1, transmisión de radio de Adrián, 10–15 s — **EL GANCHO CENTRAL**)
**Guion:** *«Aquí Adrián. Todo bien, avanzando según lo previsto, en el Paso del Águila. Cambio y corto.»* (voz: Sadaltager)
**PROMPT:** Transmisión de radio diegética de un hombre adulto, tono tranquilo y rutinario, dando un parte breve de posición y estado durante un ascenso de montaña, con viento moderado y ligera estática de radio de fondo. Sin música. Debe sonar **exactamente como un parte de rutina cualquiera**: ni cansado, ni apurado, ni entrecortado, ni con ninguna señal vocal de tensión o peligro. **EVITAR:** música, cualquier indicio vocal de peligro o de despedida, efectos que sugieran manipulación o distorsión artificial de la voz, silencios dramáticos, eco.

(A2 — confesión de Ignacio) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Charon) para incluir las preguntas que esa mesa le hizo a Ignacio.
