# Prompts de assets — La Cera de los Invitados

Total: **1 portada + 11 retratos + 9 evidencias visuales + 1 audio** (la confesión de Rodolfo
se genera por partida y no se graba; la única grabación de la línea de tiempo es la nota de voz
de Leonor).

Todo se guarda en `public/immersion/la-cera-de-los-invitados/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 11 retratos (10 personas del roster + la víctima) |
| `gallery/` | las 9 evidencias visuales |
| `audio/` | `a1-nota-de-voz-leonor.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (11): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **El altar primero.** Genera V02 (altar original) y fija ese encuadre, el color de las velas y
   la forma de los soportes: V09 y V06 se generan **a partir de V02** (misma cámara, misma luz).
   Son el segundo apoyo de la prueba central del caso (el soporte desplazado).
5. **Evidencias** en orden de entrega (ver tabla). V03 + V04 son el primer apoyo (la costura).
   V05 + V08 son el segundo (la ventana del registro de cierre).
6. **Audio** A1.
7. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V01 |
| Expansión | 13 | V02 |
| Desarrollo | 26 | V04 (a toda la mesa) · V03 (privada, a un jugador) |
| Complicación | 39 | V05 · copia pública de V03 · V07 (privada, a otro jugador) |
| Giro | 52 | V06 · copia pública de V07 |
| Cierre | 65 | V08, V09 |
| Cierre final | 78 | (solo texto: declaraciones ampliadas, antecedentes y coartadas) · A1 (audio) |
| Acusación | 90 | (formulario) |

V03 y V07 son **una sola imagen** cada una: la copia privada y la copia pública usan el mismo
archivo.

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el dato
exacto ES la prueba (22:05 ≠ 21:45; seis soportes frente a cinco; las tres reglas de la ficha).
Para V01, V04, V05, V07 y V08, y para las placas numeradas de V02, V06 y V09: genera la imagen
con la zona de texto **vacía o con texto de relleno ilegible**, y compón después el texto exacto
indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop). Verifica cada hora y cada
número contra `case.php` antes de guardar.

**Cronología maestra (todas las horas del caso salen de aquí):** entrega de la Casa de Insumos
11:20 · moldes: Esteban 14:30, Leonor 15:15, Rodolfo (vela central, molde M-20) 15:50, Esteban
16:40, Leonor 17:15 · nota de voz de Leonor 17:05 · foto del altar (V02) 18:40 · llegada 19:30,
apertura 20:00, encendido de las velas 20:15 · fin del acto de la ceremonia 21:30 · registro de
cierre: Nazario y Esteban 21:45-22:45, Delfina 21:30-23:00, Perpetua 21:30-22:50, **Rodolfo (área
del altar, solo) 22:05-22:45**.

**La cera nunca es el arma.** Ninguna imagen debe sugerir que la cera, el molde o la vela
causaron daño: es solo el rastro del encubrimiento. Nada de sangre ni de violencia en ninguna
pieza.

## Biblia visual de continuidad (pegar en los retratos y en las escenas)

- **Leonor Aguirre**, 50: maestra cerera, delantal artesanal de lino color arena, cabello con
  algunas canas recogido, aspecto cálido y profesional.
- **Rodolfo Casares**, 42: delantal artesanal gris oscuro, camisa remangada, aspecto técnico y
  meticuloso, barba corta bien cuidada.
- **Micaela Aguirre**, 28: ropa elegante de invitada de honor (vestido sobrio de color burdeos).
- **Esteban Riofrío**, 30: delantal de aprendiz de lona, algo manchado de cera.
- **Delfina Casal**, 45: ropa formal de organizadora de eventos (traje oscuro), tablet en mano.
- **Perpetua Salinas**, 50: ropa formal de oficina (blusa y chaqueta), gafas de lectura.
- **Ignacio Davó**, 53: ropa casual elegante (camisa y chaqueta sin corbata), canoso.
- **Araceli Huertas**, 38: ropa casual elegante de invitada.
- **Nazario Bello**, 26: ropa de trabajo de montaje (camiseta oscura, pantalón de trabajo,
  guantes en el bolsillo).
- **Fermina Ubilla**, 55: ropa casual elegante de invitada, un chal.
- **Walter Niembro**, 48: uniforme de reparto de la Casa de Insumos Cerámicos y Cereros (logo
  genérico), furgoneta al fondo.
- **El Atelier de Cera:** taller boutique con salón de ceremonia de luz de velas plausible y
  cálida, área de preparación de moldes con mesas de trabajo de madera, mismos moldes, colores y
  recipientes en todas las piezas. Sin estética satánica ni ocultismo real.
- **El altar:** una **vela central grande** (pilar de cera color marfil, unos 35 cm de alto y 15
  de ancho), rodeada de velas menores terracota; **seis soportes de madera tallada** dispuestos en
  círculo alrededor de la vela central, cada uno con una **placa de latón numerada del 1 al 6**
  clavada en el frente de la base. Telas otoñales sobrias, hojas secas y calabazas pequeñas.
  Posiciones (vista desde arriba, el 6 es el más cercano a quien mira): soporte 1 a las 12,
  soporte 2 a las 2, soporte 3 a las 4, soporte 4 a las 6, soporte 5 a las 8, soporte 6 a las 10
  (como las agujas de un reloj). La cámara de las fotos del altar mira desde el frente, desde el
  lado del soporte 4.
- **Los moldes:** M-03 (pequeño, obsequios), M-08 (pilar menor), M-12 (cilíndrico), M-20 (grande,
  vela central).

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos, sin
logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria, en **formato vertical de póster de catálogo
(2:3)**, para un caso de misterio artesanal: el altar de una ceremonia de velas de Halloween en
un taller boutique, visto desde el frente en penumbra cálida; una vela central grande de color
marfil rodeada de velas menores terracota y de soportes de madera tallada dispuestos en círculo,
con un pequeño hueco en el círculo que apenas se nota; hojas secas y calabazas pequeñas; la llama
de la vela central refleja una línea sutil, casi invisible, de unión entre dos capas de cera.
Sensación de ceremonia que parece intacta y de algo que no lo está, íntima y elegante, sin
dramatismo. Sin personas. Estilo realista premium, **sin texto integrado**. **EVITAR:** sangre,
cadáver, rostros, iconografía policial, estética satánica u ocultista, calaveras, texto o logos.

## 2. Retratos

(`photos/leonor-aguirre.png` — víctima)
**PROMPT:** Retrato diegético tipo foto de presentación del taller de una maestra cerera de 50
años, delantal artesanal de lino color arena, cabello con algunas canas recogido, expresión cálida,
serena y profesional, mirada atenta, fondo de un taller de velas con mesas de madera y moldes
ordenados. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, heridas,
logos de marcas reales.

(`photos/rodolfo-casares.png` — Rodolfo Casares)
**PROMPT:** Retrato diegético de un hombre de 42 años, delantal artesanal gris oscuro, camisa
remangada, barba corta bien cuidada, expresión cordial, técnica y meticulosa con un asomo de
tensión, fondo de un área de preparación de moldes de un taller de velas. Formato retrato.
**EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto agresivo.

(`photos/micaela-aguirre.png` — Micaela Aguirre)
**PROMPT:** Retrato diegético de una mujer de 28 años, vestido sobrio de color burdeos de
invitada de honor, cabello oscuro suelto, expresión afectuosa y a la vez defensiva, ojos algo
enrojecidos sin llanto exagerado, fondo del salón de una ceremonia con luz de velas. Formato
retrato. **EVITAR:** apariencia de culpable, llanto exagerado, dramatización.

(`photos/esteban-riofrio.png` — Esteban Riofrío)
**PROMPT:** Retrato diegético de un hombre de 30 años, delantal de aprendiz de lona algo manchado
de cera, cabello corto, expresión profesional, algo resentida y a la defensiva, fondo de una
mesa de trabajo de un taller de velas con moldes. Formato retrato. **EVITAR:** villano obvio,
gesto amenazante, texto legible.

(`photos/delfina-casal.png` — Delfina Casal)
**PROMPT:** Retrato diegético de una mujer de 45 años, traje oscuro formal de organizadora de
eventos, tablet en mano, cabello impecable, expresión pulida, mediática y algo defensiva, fondo
del salón de una velada con luz cálida y servicio al fondo. Formato retrato. **EVITAR:** sobres o
dinero visibles, villana obvia, texto legible en la tablet.

(`photos/perpetua-salinas.png` — Perpetua Salinas)
**PROMPT:** Retrato diegético de una mujer de 50 años, blusa y chaqueta de oficina, gafas de
lectura, expresión meticulosa y reservada, fondo de una oficina administrativa con carpetas y un
monitor sobrio. Formato retrato. **EVITAR:** gesto de culpa obvio, documentos legibles, dinero
visible.

(`photos/ignacio-davo.png` — Ignacio Davó)
**PROMPT:** Retrato diegético de un hombre de 53 años, camisa y chaqueta sin corbata (casual
elegante), canoso, expresión cordial y algo evasiva con una sonrisa que no llega a los ojos,
fondo del salón de una velada con luz de velas. Formato retrato. **EVITAR:** apariencia de
culpable, mirada siniestra, gesto dramático.

(`photos/araceli-huertas.png` — Araceli Huertas)
**PROMPT:** Retrato diegético de una mujer de 38 años, ropa casual elegante de invitada, cabello
recogido, expresión amable, observadora y algo nerviosa, fondo del salón de una ceremonia con
luz cálida. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/nazario-bello.png` — Nazario Bello)
**PROMPT:** Retrato diegético de un hombre de 26 años, camiseta oscura y pantalón de trabajo de
montaje, guantes en el bolsillo, expresión joven, práctica y algo conmovida, fondo de un salón
con mesas apiladas y utilería de montaje. Formato retrato. **EVITAR:** apariencia de sospechoso,
dramatización, sangre.

(`photos/fermina-ubilla.png` — Fermina Ubilla)
**PROMPT:** Retrato diegético de una mujer de 55 años, ropa casual elegante de invitada con un
chal, cabello canoso corto, expresión cálida, afectada por la pérdida y cuidadosa, fondo de una
terraza con luz tenue de velas. Formato retrato. **EVITAR:** apariencia de sospechosa, llanto
exagerado, dramatización.

(`photos/walter-niembro.png` — Walter Niembro)
**PROMPT:** Retrato diegético de un hombre de 48 años, uniforme de reparto de una casa de
insumos cerámicos (logo genérico sin texto legible), expresión práctica y neutral, una
furgoneta de reparto y cajas de cera en bloque al fondo, luz diurna. Formato retrato. **EVITAR:**
logos de marcas reales, texto legible, apariencia de sospechoso.

## 3. Evidencias visuales

(`gallery/v01-plano-de-mesas.png` — V01, plano de mesas)
**PROMPT:** Diagrama vectorial diegético para un juego de investigación: plano simple y limpio de
la distribución de una ceremonia privada en un taller de velas, visto desde arriba, con el
salón principal con seis mesas de invitados, el altar artesanal circular en el centro con seis
pequeños puntos a su alrededor, una mesa lateral cercana al altar, un pasillo del fondo que lleva
al área de preparación de moldes (con su mesa de trabajo), la barra y servicio, una terraza, la
oficina administrativa y la entrada. Estilo de documento técnico institucional, líneas finas,
colores neutros. Dejar vacías las etiquetas. Formato horizontal. **EVITAR:** iconografía de
alarma, flechas, resaltados, marcas de recorrido, estética de póster, estética satánica u
ocultista. Mantener la pista visible pero no resaltada.
**TEXTO A COMPONER:** título *Atelier de Cera — Plano de distribución — Ceremonia privada*.
Zonas: *Entrada · Salón principal · Barra y servicio · Terraza · Oficina administrativa · Pasillo
del fondo · Área de preparación de moldes (con «Mesa de trabajo» dentro) · Mesa lateral —
utilería sobrante · Altar artesanal — vela central de encendido continuo (la zona del altar se
cierra al público al terminar el acto)*. Seis puntos alrededor del altar, rotulados **1 a 6**
según las posiciones de la biblia visual (1 a las 12, 2 a las 2, 3 a las 4, 4 a las 6, 5 a las
8, 6 a las 10), con la leyenda *Soportes de madera (posición de montaje)*. Mesas de invitados
numeradas **1 a 6**; asientos de protocolo: **Mesa 1 — Leonor Aguirre (anfitriona), Micaela
Aguirre, Fermina Ubilla · Mesa 2 — Rodolfo Casares, Ignacio Davó, Araceli Huertas · Mesas 3 a 6 —
invitados**. Coordinación del servicio: *Delfina Casal*. Montaje: *Nazario Bello, Esteban
Riofrío*. Sin hora específica. Misma disposición de zonas en V02, V06 y V09.

(`gallery/v02-altar-original.png` — V02, el altar antes de la ceremonia)
**PROMPT:** Fotografía diegética documental del altar artesanal de una ceremonia de velas de
Halloween, tomada por el equipo de montaje **antes de que comience la ceremonia**, con las velas
**apagadas**: una vela central grande de color marfil, de superficie lisa y pareja, rodeada de
velas menores terracota y de **seis soportes de madera tallada dispuestos en círculo regular a su
alrededor, cada uno con una placa de latón numerada del 1 al 6 clavada en el frente de la base**;
telas otoñales sobrias, hojas secas, calabazas pequeñas. Escenario elegante y cálido, luz
ambarina de salón, cámara frontal a la altura de la mesa del altar, desde el lado del soporte 4.
Todos los soportes y sus placas deben verse con claridad y en posiciones exactamente
reproducibles. Sin personas. Formato horizontal. **EVITAR:** sangre, señales de violencia,
iconografía satánica u ocultista, velas encendidas, resaltados, texto o carteles. Mantener la
pista visible pero no resaltada. (**Fija este encuadre y esta luz**: V06 y V09 deben usar la
misma cámara, los mismos moldes, colores y soportes.)
**TEXTO A COMPONER:** si el generador no resuelve bien las placas, componer los números **1, 2,
3, 4, 5, 6** sobre las placas de latón de los seis soportes, en las posiciones del plano V01.

(`gallery/v03-vela-central-detalle.png` — V03, vela central de cerca; copia privada y pública)
**PROMPT:** Macrofotografía documental diegética de la superficie de la **vela central** del
altar (pilar de cera color marfil) **después del incidente**, ya con la llama pequeña, que
muestra una **línea de unión visible pero sutil entre dos capas de cera de textura ligeramente
distinta** (la capa superior algo más lisa y la inferior algo más rugosa), alrededor del borde de
la superficie, junto a un pequeño charco de cera derretida alrededor de la mecha. Luz cálida de
vela, detalle artesanal íntimo, sin ningún elemento dramático. Formato cuadrado o vertical.
**EVITAR:** resaltados artificiales de la costura, círculos o flechas, iconografía de alarma,
elementos ocultistas, sangre, texto. Mantener la pista visible pero no resaltada: debe notarse al
mirar de cerca. Mismo color y tipo de vela que V02, V04 y V06.

(`gallery/v04-ficha-de-trabajo.png` — V04, ficha de trabajo)
**PROMPT:** Documento visual diegético de una ficha de trabajo artesanal de **una sola página**,
con aspecto de ficha impresa simple de taller, con **tres notas de fabricación** sobre el
comportamiento de la cera y, junto a cada una, un pequeño dibujo esquemático (una vela recién
vertida lisa y brillante; una vela que ardió de corrido con un charco parejo alrededor de la
mecha; una vela con dos capas y una línea de unión visible). Papel color crema, tipografía
sencilla. Dejar vacía la zona de texto. Formato vertical. **EVITAR:** química real, fórmulas
técnicas, iconografía de alarma, resaltados, estética de póster. Coherente con V03.
**TEXTO A COMPONER** (copiar **literal**, sin cambiar una palabra; título: *Atelier de Cera —
Ficha de trabajo — Cómo leer una vela*):

> Regla 1: una vela recién vertida mantiene una superficie lisa y brillante durante los primeros minutos, antes de endurecerse desde los bordes hacia el centro. Regla 2: una vela que ardió de corrido, sin interrupción, muestra un charco de cera derretida parejo alrededor de la mecha, cada vez más amplio con el tiempo. Regla 3: una vela que se apagó y se volvió a encender, o que fue vuelta a verter sobre una base ya endurecida, deja una línea visible de unión entre dos capas de cera de textura distinta — una costura que no aparece en una vela que ardió sin interrupción.

(Maquetación sugerida: cada regla en su propio bloque numerado con su dibujo al lado. El texto de
las tres reglas es la clave interpretativa completa; no añadir nada más.)

(`gallery/v05-registro-de-moldes.png` — V05, registro de moldes)
**PROMPT:** Documento visual diegético de un registro de control de existencias de moldes y
materiales de un taller de velas, en una hoja o pantalla simple con columnas de responsable,
molde, uso y hora, mostrando varias entradas rutinarias. Neutro, de documento de taller.
Dejar vacías las celdas de texto. Ninguna fila resaltada. Formato horizontal. **EVITAR:**
iconografía de alarma, resaltados artificiales, estética de póster. Coherente con V08.
**TEXTO A COMPONER:** título *Atelier de Cera — Registro de moldes y materiales — Día de la
ceremonia*. Columnas *Responsable · Molde · Uso · Hora*. Filas: **R. Casares · Recepción de
entrega (Casa de Insumos): M-03, M-08, M-12, M-20 y 40 kg de cera en bloque · 11:20** / **E.
Riofrío · M-12 · Velas personalizadas de los invitados, lote 1 · 14:30** / **L. Aguirre · M-08 ·
Velas menores del altar · 15:15** / **R. Casares · M-20 · Vela central del altar · 15:50** / **E.
Riofrío · M-12 · Velas personalizadas de los invitados, lote 2 · 16:40** / **L. Aguirre · M-03 ·
Obsequios de cierre para los invitados · 17:15**.

(`gallery/v06-mesa-lateral.png` — V06, mesa lateral)
**PROMPT:** Fotografía diegética documental de una **mesa lateral** cercana a un altar de velas,
cubierta de utilería sobrante del montaje (telas dobladas, calabazas pequeñas, hojas secas, una
cesta) y, entre esa utilería, **un soporte de madera tallada con una placa de latón numerada 5**
clavada en el frente de la base, idéntico al diseño de los soportes del altar de V02 y fuera de
su lugar original; al fondo, desenfocado, se intuye el borde del altar. Luz cálida ambarina de
velas, misma paleta que V02. Sin personas. Formato horizontal. **EVITAR:** sangre, señales de
violencia, iconografía de alarma, resaltados, círculos o flechas sobre el soporte, texto. Mantener
la pista visible pero no resaltada.
**TEXTO A COMPONER:** el número **5** sobre la placa de latón del soporte (legible al mirar de
cerca).

(`gallery/v07-invitaciones.png` — V07, invitaciones; copia privada y pública)
**PROMPT:** Captura de pantalla diegética maquetada de una interfaz de mensajería simple y
genérica, formato de captura de móvil, vertical, con **dos mensajes de invitación** a una
ceremonia, uno protocolario y otro enviado a otro contacto; tono neutral, interfaz sin logos de
apps reales. Dejar vacíos los textos. **EVITAR:** iconografía de alarma, nombres de marcas
reales, nombres de empresas, resaltados, estética de póster.
**TEXTO A COMPONER:** dos paneles uno bajo el otro.
Panel 1, encabezado *Copia de la invitación enviada a los invitados — Atelier de Cera — Lunes
09:40 — Para: Araceli Huertas*: **«Hola, Araceli: te esperamos en la ceremonia privada de
Halloween del Atelier de Cera. Llegada: 19:30. Apertura: 20:00. Cada invitado recibe su vela
personalizada. Un abrazo. Atelier de Cera»**.
Panel 2, encabezado *Chat — Rodolfo C. — Lunes 19:12 (conversación en el teléfono de Leonor
Aguirre)*, mensaje recibido de *Rodolfo C.*: **«Hola. Te confirmo tu lugar en la ceremonia
privada del Atelier de Cera: llegada 19:30, apertura 20:00. Y sobre los detalles del acuerdo con
la otra marca, como hablamos: mejor lo cerramos después de la velada.»** Debajo, estado
*Leído*; sin respuesta de Leonor. No debe aparecer ningún nombre de empresa.

(`gallery/v08-registro-de-cierre.png` — V08, registro de limpieza y cierre)
**PROMPT:** Documento visual diegético de un registro de limpieza y cierre de un taller, con
columnas de responsable, área, hora de inicio, hora de fin y personal asignado, mostrando el
cierre rutinario de varias áreas al final de una velada, impreso o en pantalla simple, de la
oficina administrativa. Neutro. Dejar vacías las celdas de texto. Ninguna fila resaltada. Formato
horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, estética de póster.
Coherente con V05.
**TEXTO A COMPONER:** título *Atelier de Cera — Registro de limpieza y cierre — Noche de la
ceremonia*. Columnas *Responsable · Área · Inicio · Fin · Personal asignado*. Filas: **N.
Bello / E. Riofrío · Salón principal (mesas, manteles y utilería) · 21:45 · 22:45 · 2** / **D.
Casal · Servicio de la velada (barra y cocina) · 21:30 · 23:00 · 1 + catering** / **P. Salinas ·
Oficina administrativa (cierre de caja del evento) · 21:30 · 22:50 · 1** / **R. Casares · Altar
artesanal (cierre del área) · 22:05 · 22:45 · 1**. Nota al pie: *El área del altar se cierra al
público al terminar el acto de la ceremonia.*

(`gallery/v09-soportes-altar.png` — V09, soportes después)
**PROMPT:** Fotografía diegética documental de detalle de los **soportes de madera tallada del
altar después del incidente**, misma cámara, misma luz y misma paleta que V02 (frontal, desde el
lado del soporte 4): ahora se ven **cinco soportes** con placa de latón (números **1, 2, 3, 4 y
6**) y el **soporte 4 está ligeramente corrido hacia la izquierda**, hacia el sitio donde estaba
el 5, de modo que el círculo ya no es regular (huecos de unos 90° a ambos lados del 4, en lugar de
60°); la vela central y las velas menores ya están encendidas, con llama pequeña y charcos
parejos de cera. Los soportes se ven como objetos artesanales similares entre sí. Sin personas.
Formato horizontal. **EVITAR:** sangre, señales de violencia, iconografía de alarma, círculos,
flechas o resaltados sobre el desplazamiento, texto. Mantener la pista visible pero no resaltada.
**TEXTO A COMPONER:** los números **1, 2, 3, 4, 6** sobre las placas de latón (el 5 no aparece
en esta imagen). Verifica la comparación con V02 y con el plano V01 antes de guardar.

## 4. Audio

(`audio/a1-nota-de-voz-leonor.mp3` — A1, nota de voz de Leonor; evento del minuto 78)
- **Scene:** nota de voz de móvil grabada en el área de trabajo de un taller artesanal de velas,
  con un leve ambiente de taller de fondo (cacharros, madera); sin música.
- **Sample context:** una mujer adulta de 50 años, tono calmado y decidido, como quien ha tomado
  una decisión difícil sobre un asunto profesional y está resuelta a afrontarlo esa misma noche.
  Sin ninguna señal de miedo ni de presagio: suena como alguien que procesa una decisión, no como
  alguien en peligro.
- **Speaker:** Speaker 1 - Leonor Aguirre. **Voz (Gemini):** `Vindemiatrix`.
- **Script:** «Ya até los cabos. Esta noche, cuando termine la ceremonia, voy a hablar con la
  persona involucrada, cara a cara, antes de decidir qué hacer con el taller.»
- **Duración:** 15-20 segundos.
- **EVITAR:** tono de miedo o presagio explícito, música, cualquier nombre propio mencionado
  directamente.

(A2 — confesión de Rodolfo) **No se graba.** Es `solution.confession_script` en `case.php`: se
genera por partida (con voz `Charon`) para incluir las preguntas que esa mesa le hizo a Rodolfo.
Dirección de voz: grave, pausada, con un quiebre de voz contenido; el reconocimiento del fraude
llega primero, el del homicidio y el encubrimiento después. Sin música, sin sobreactuar, sin
ningún detalle gráfico de la muerte.
