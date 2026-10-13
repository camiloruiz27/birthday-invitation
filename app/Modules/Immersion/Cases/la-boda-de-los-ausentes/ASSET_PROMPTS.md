# Prompts de assets — La Boda de los Ausentes

Total: **1 portada + 10 retratos + 16 evidencias visuales (14 + 2 fragmentos) + 2 audios** (más la
confesión, que se genera por partida y no se graba).

Todo se guarda en `public/immersion/la-boda-de-los-ausentes/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos |
| `gallery/` | las 16 evidencias visuales |
| `audio/` | `a1-ambiente-recepcion.mp3`, `a2-mensaje-voz-lucia.mp3` |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato y de foto.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias V1 → V14** en orden de entrega (ver tabla). V3, V4, V5, V6 y V7 son fotos de la misma
   noche: mismos trajes, misma luz, misma finca. V8 y V9 son el **mismo auto** (antes / después).
   V4 y V3 deben mostrar el mismo traje de Rodrigo, V4 visiblemente más desarreglado.
5. **Audios** A1 y A2.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V8, V9, A1 |
| Expansión | 12 | V2, V3 |
| Pistas privadas | 24 | V10a (mensajes con Lucía, parcial; archivo `v10-conversacion-privada-1.png`), V12a (mensajes con Mateo, parcial; archivo `v12-conversacion-privada-2.png`). Los nombres de archivo llegan al navegador: no llevan «lucia», «mateo», «parcial» ni «a» |
| Complicación | 36 | V4, V5, V6, V7, V13 |
| Giro | 50 | V10, V11, V12, A2 |
| Hallazgo | 62 | V14 |
| Cierre | 62 | (solo texto: coartadas cruzadas) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el dato
exacto ES la prueba (21:05 ≠ 21:00; el nombre del contacto; la cantidad). Para V1, V2, V10a, V10, V11,
V12a, V12 y V13: genera la imagen con la zona de texto **vacía o con texto de relleno ilegible**, y
compón después el texto exacto indicado en "TEXTO A COMPONER" con un editor (Canva, Figma, Photoshop).
Las fotografías (V3, V4, V5, V6, V7, V8, V9, V14) **no llevan texto**: la hora la da el registro V13,
no la propia imagen. Verifica cada hora contra `case.php` antes de guardar.

## Biblia visual de continuidad (pegar en los retratos y fotos)

- **Sebastián Lagos**, 34: cabello oscuro corto, traje de novio azul marino, **gemelos de camisa plateados distintivos** (cuadrados, con un borde grabado).
- **Valeria Paz**, 30 (novia, no se retrata): vestido de novia blanco, cabello recogido con flores.
- **Rodrigo Paz**, 38: complexión media, cabello oscuro, traje gris oscuro de invitado.
- **Andrés Rojas**, 35: traje azul claro de padrino, corbata a juego con el novio.
- **Camila Lagos**, 29: vestido verde oscuro de dama de honor familiar.
- **Mateo Silva**, 33: traje gris de invitado (más claro que el de Rodrigo).
- **Don Rigoberto Paz**, 60: traje formal oscuro, complexión robusta, cabello canoso.
- **Lucía Fonseca**, 31: vestido burdeos discreto de invitada.
- **Sofía Mendoza**, 24: vestido lila de dama de honor.
- **Don Horacio Vega**, 65: ropa de trabajo de jardinería.
- **Finca Los Almendros**: finca privada latinoamericana con jardines amplios, salón de eventos al aire libre bajo una carpa, pista de baile, camino de piedra hacia un cobertizo de jardinería junto al estacionamiento (largo, con autos y setos), luces cálidas de fiesta.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos ni en las
fotografías, sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio social: una finca de
eventos al anochecer, con una carpa de boda cálidamente iluminada y guirnaldas de luces, la pista de
baile vacía en primer plano y, al fondo, un camino de piedra que se aleja hacia un jardín oscuro donde
apenas se distingue la silueta de un cobertizo; sensación elegante y festiva que esconde algo
inquietante, un hueco en medio de la celebración. Sin personas visibles. Debe sugerir una ausencia, no
una tragedia. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre,
cadáver, novios o novia visibles, iconografía policial, estética de terror, texto o logos.

## 2. Retratos

(`photos/sebastian-lagos.png` — víctima: el novio desaparecido)
**PROMPT:** Retrato diegético tipo foto de ceremonia de un hombre de 34 años, cabello oscuro corto, traje
de novio azul marino, gemelos de camisa plateados visibles, expresión serena y algo nerviosa de recién
casado, fondo desenfocado de jardín de finca con luces cálidas. Formato retrato. **EVITAR:** apariencia
de víctima, heridas, dramatización, sangre.

(`photos/rodrigo-paz.png` — Rodrigo Paz)
**PROMPT:** Retrato diegético de un hombre de 38 años, complexión media, cabello oscuro, traje gris
oscuro de invitado, expresión cordial y contenida, sonrisa educada que no llega del todo a los ojos,
fondo de salón de boda con luz cálida. Formato retrato. **EVITAR:** apariencia de culpable, mirada
siniestra, gesto agresivo, iluminación dramática.

(`photos/andres-rojas.png` — Andrés Rojas)
**PROMPT:** Retrato diegético de un hombre de 35 años, traje azul claro de padrino con corbata a juego
(azul marino), expresión afectada, leal y algo preocupada, fondo de jardín de finca con luces cálidas.
Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/camila-lagos.png` — Camila Lagos)
**PROMPT:** Retrato diegético de una mujer de 29 años, vestido verde oscuro de dama de honor familiar,
cabello recogido con sencillez, expresión afectada y protectora, algo evasiva, fondo de salón de boda
cálido. Formato retrato. **EVITAR:** rencor evidente, dramatización, aspecto de sospechosa teatral.

(`photos/mateo-silva.png` — Mateo Silva)
**PROMPT:** Retrato diegético de un hombre de 33 años, traje gris de invitado en un tono medio (más
claro que el de Rodrigo), expresión leal y nerviosa, mirada que se desvía un poco, fondo de salón de boda desenfocado.
Formato retrato. **EVITAR:** gesto culpable obvio, sudor exagerado, dramatización.

(`photos/rigoberto-paz.png` — Don Rigoberto Paz)
**PROMPT:** Retrato diegético de un hombre de 60 años, complexión robusta, cabello canoso, traje formal
oscuro, expresión formal, protectora y algo distante de empresario y padre de la novia, fondo de mesa
principal de una recepción. Formato retrato. **EVITAR:** villano, gesto amenazante, caricatura de
patriarca.

(`photos/lucia-fonseca.png` — Lucía Fonseca)
**PROMPT:** Retrato diegético de una mujer de 31 años, vestido burdeos discreto de invitada, cabello
recogido con sobriedad, expresión profesional, algo incómoda y cuidadosa, fondo de jardín de finca con
luz cálida tenue. Formato retrato. **EVITAR:** aspecto de femme fatale, mirada siniestra, dramatización.

(`photos/sofia-mendoza.png` — Sofía Mendoza)
**PROMPT:** Retrato diegético de una mujer de 24 años, vestido lila de dama de honor, expresión amable,
atenta y algo nerviosa, fondo de pista de baile con luces de fiesta desenfocadas. Formato retrato.
**EVITAR:** apariencia de sospechosa, dramatización.

(`photos/horacio-vega.png` — Don Horacio Vega)
**PROMPT:** Retrato diegético de un hombre de 65 años, ropa de trabajo de jardinería (camisa de
trabajo, chaleco, gorra), manos curtidas, expresión sencilla, respetuosa y reservada, fondo de camino
de piedra de un jardín al atardecer. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/registro-fotografo-evento.png` — fotógrafo del evento / registro)
**PROMPT:** Imagen diegética tipo retrato funcional de una cámara réflex profesional sobre una mesa
junto a un portátil que muestra una hoja de contactos con miniaturas pequeñas e ilegibles de fotos de
una boda, usada como avatar del registro del fotógrafo. Luz neutra de mesa de trabajo, ambiente de
cobertura de evento. Formato retrato. **EVITAR:** rostros nítidos en las miniaturas, texto legible,
marcas reales, dramatización.

## 3. Evidencias visuales

(`gallery/v1-plano-finca.png` — V1, plano de la finca)
**PROMPT:** Documento diegético tipo plano funcional simple de una finca privada para eventos, en
líneas esquemáticas claras vistas desde arriba, mostrando el salón/carpa de la recepción con su pista
de baile, un camino de piedra de jardín que parte del salón hacia un cobertizo de herramientas, y una
zona de estacionamiento larga y estrecha, con su entrada del lado del salón y su extremo opuesto junto
al cobertizo, y un único auto señalado en el **tramo central** del estacionamiento (lejos del
cobertizo y de la entrada, entre autos y setos). Papel de planificación de evento. Formato horizontal.
Zonas de etiqueta vacías. **EVITAR:** iconografía de alerta, accesos inventados (solo existen esos
cinco espacios), flechas o marcas de recorrido, el auto junto al cobertizo.
**TEXTO A COMPONER:** etiquetas simples *Salón / carpa de recepción · Pista de baile · Camino de jardín
· Cobertizo de herramientas · Estacionamiento (entrada junto al salón / tramo central: auto de
Sebastián / extremo junto al cobertizo)*.

(`gallery/v2-plano-de-mesas.png` — V2, plano de mesas)
**PROMPT:** Documento diegético tipo plano de mesas de boda, con círculos que representan mesas
numeradas y nombres de invitados asignados a cada una, papelería elegante pero funcional (marco
decorado, tipografía clásica), fotografiado o impreso sobre superficie neutra, luz cálida. Formato
vertical. Dejar los nombres vacíos. **EVITAR:** iconografía de alerta, resaltar ningún nombre.
**TEXTO A COMPONER:** *Mesa 1 (principal): Valeria Paz · Sebastián Lagos · Don Rigoberto Paz · Camila
Lagos. — Mesa 2: Rodrigo Paz · Sofía Mendoza. — Mesa 3: Andrés Rojas · Mateo Silva. — Mesa 4: Lucía
Fonseca.* Más nombres de relleno coherentes en otras mesas.

(`gallery/v3-foto-grupal-2040.png` — V3, foto grupal)
**PROMPT:** Fotografía diegética de evento social tipo foto de fotógrafo profesional: un grupo de
invitados en la pista de baile de una boda al aire libre bajo una carpa, luces cálidas de fiesta,
entre ellos un hombre de complexión media, cabello oscuro y traje gris oscuro (Rodrigo) en actitud
relajada y normal, bailando o conversando, reconocible pero sin protagonismo. Buena calidad, luz cálida
festiva. Formato horizontal. **EVITAR:** marcas de tiempo incrustadas, dramatización, resaltar a Rodrigo.

(`gallery/v4-foto-candid-jardin.png` — V4, foto candid de las 21:05)
**PROMPT:** Fotografía diegética informal tipo foto de móvil de un invitado, tomada de pasada: en
primer plano, un grupo de invitados sin relación posando para la foto (ese grupo es el sujeto de la
foto; Rodrigo NO lo es); en el fondo, un camino de jardín tenuemente iluminado por el que camina un
hombre de complexión media y cabello oscuro: **debe ser reconocible por su rostro (pequeño pero nítido
y de perfil o tres cuartos) y por su traje gris oscuro** (Rodrigo, mismo traje y mismo rostro que en
V3; más oscuro que el gris medio de Mateo, con quien no debe poder confundirse), con la chaqueta
desabrochada y algo despeinado, ropa visiblemente desarreglada respecto a V3. Solo un ligero
desenfoque de movimiento en el resto del fondo, nunca en su cara; luz nocturna de jardín cálida pero
más tenue que la del salón. Formato 4:5. **EVITAR:** rostro borroso o de espaldas, que parezca otra
persona o Mateo, resaltar a Rodrigo con encuadre o luz artificial, marcas de tiempo, sangre, gesto de
violencia.

(`gallery/v5-mesa-postres-silla-vacia.png` — V5, mesa de postres a las 21:00)
**PROMPT:** Fotografía diegética de evento social tipo foto de fotógrafo profesional: una mesa de
postres de boda bien iluminada con varios invitados alrededor y, en una silla cercana, un bolso de
mujer burdeos colgado del respaldo, la silla visiblemente vacía (Lucía), sin que ella esté en el
encuadre. Luz cálida festiva, ausencia sin dramatizar. Formato horizontal. **EVITAR:** resaltar la silla
vacía, marcas de tiempo, personas mirando hacia la silla.

(`gallery/v6-invitado-estacionamiento.png` — V6, Andrés en el estacionamiento a las 20:52)
**PROMPT:** Fotografía diegética informal tipo foto de móvil: un hombre con traje azul claro (Andrés) de
pie en el extremo de entrada de una zona de estacionamiento de finca, del lado del salón, revisando su
teléfono con actitud relajada y sin urgencia; autos parcialmente visibles al fondo bajo farolas tenues y
setos que tapan la vista del extremo opuesto. Luz nocturna fría y tenue. Formato 4:5. **EVITAR:**
dramatización, marcas de tiempo, que se vea el cobertizo o el camino del jardín.

(`gallery/v7-rincon-salon.png` — V7, conversación tensa a las 20:00)
**PROMPT:** Fotografía diegética de evento social tipo foto profesional de cobertura: una mujer con
vestido verde oscuro (Camila) y un hombre con traje de novio azul marino y gemelos plateados
(Sebastián) conversando en un rincón apartado del salón, brazos cruzados y expresiones serias, sin gente
alrededor; tensión familiar contenida, sin violencia. Luz cálida de salón. Formato horizontal.
**EVITAR:** gestos agresivos, lágrimas, dramatización, marcas de tiempo.

(`gallery/v8-auto-antes.png` — V8, interior del auto antes de la ceremonia)
**PROMPT:** Fotografía diegética de "preparativos" de boda tipo foto profesional de detalles: el interior
limpio y ordenado de un auto de gama media (mismo auto que V9), asiento del conductor y trasero sin
manchas ni objetos fuera de lugar, luz natural cálida de día. Parece una foto decorativa de detalle.
Formato 4:5. **EVITAR:** manchas, daños, suciedad, marcas de tiempo.

(`gallery/v9-auto-abandonado.png` — V9, el auto abandonado)
**PROMPT:** Fotografía diegética funcional tomada por quien lo encontró: el exterior del mismo auto de
V8, de gama media, estacionado de forma irregular y apresurada en un tramo apartado de un camino
secundario, de noche, con el interior parcialmente visible a través de la ventana mostrando una mancha
oscura discreta en el asiento del conductor, sin mostrarla de forma gráfica. Foto de móvil nocturna con
flash o linterna parcial, algo de grano, un punto de luz artificial. Formato horizontal. **EVITAR:**
sangre abundante, gore, dramatización, personas.

(`gallery/v10-conversacion-privada-1.png` — V10 parcial, entrega privada a T+24)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería, interfaz simple y
genérica, tono profesional y discreto; cortada por arriba, mostrando solo el final del intercambio,
**sin el nombre del contacto ni ninguna inicial** (cabecera genérica «Contacto», sin foto). Fondo neutro,
burbujas sencillas, sin cifras. Formato vertical, aspecto de captura de móvil. Dejar vacías las
burbujas. **EVITAR:** iconografía de alarma, el nombre del contacto o su inicial, cifras de dinero, logos
de apps reales. El texto de las burbujas son las TRES ÚLTIMAS burbujas, contiguas, de V10 completo.
**TEXTO A COMPONER:** cabecera *Contacto* — burbuja del contacto: *Voy a estar a esa hora. Sin
escándalos.* — Sebastián: *Llevo una parte en efectivo y el resto lo acordamos esa misma noche.* —
contacto: *Está bien. Gracias por no esquivarlo.* (Sin fecha, sin cobertizo: el correo del fragmento
no menciona ningún lugar.)

(`gallery/v10-mensajes-lucia.png` — V10 completo, entrega a T+50)
**PROMPT:** La misma interfaz de mensajería que V10 parcial (mismo diseño de burbujas y mismo tono
profesional), ahora la conversación completa, con el nombre completo del contacto visible. Formato
vertical. **EVITAR:** cambiar el diseño respecto a V10 parcial, cifras exactas de dinero, iconografía de
alarma, resaltados.
**TEXTO A COMPONER:** cabecera *Lucía Fonseca*. *Mar 17/06, 18:42* — Lucía: *Sebas, ya van meses.
Necesito que me digas cuándo vamos a cerrar lo pendiente.* — Sebastián: *Lo sé. Te debo una explicación y
te debo el resto. No quiero que esto salga a la luz antes de la boda.* — Lucía: *Yo tampoco. Dime cómo.*
— Sebastián: *El sábado en la finca. Hay un cobertizo de jardinería junto al estacionamiento. Hacia las
nueve, cuando empiece el baile, nadie va a notar que salgo un rato.* — Lucía: *Voy a estar a esa hora.
Sin escándalos.* — Sebastián: *Llevo una parte en efectivo y el resto lo acordamos esa misma noche.* —
Lucía: *Está bien. Gracias por no esquivarlo.*

(`gallery/v11-recibo-retiro.png` — V11, comprobante de retiro de efectivo)
**PROMPT:** Documento diegético tipo recibo impreso de cajero automático, pequeño y vertical, de un
retiro de efectivo, fotografiado sobre una superficie neutra (una mesa de madera clara), luz neutra.
Formato vertical. Dejar vacías las líneas de fecha, hora y monto. **EVITAR:** iconografía de alarma,
logos de bancos reales, otros textos.
**TEXTO A COMPONER:** *CAJERO AUTOMÁTICO — RETIRO DE EFECTIVO — Jue 19/06 16:22 — Monto: $3.000.000 —
Operación aprobada.*

(`gallery/v12-conversacion-privada-2.png` — V12 parcial, entrega privada a T+24)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería entre dos amigos cercanos,
interfaz simple y genérica, tono informal y de confianza; cortada por arriba y por abajo, mostrando solo
un tramo del intercambio, **sin el nombre del contacto ni ninguna inicial** (cabecera genérica
«Contacto», sin foto). Formato vertical, aspecto de captura de móvil. Dejar vacías las burbujas.
**EVITAR:** iconografía de alarma, el nombre del contacto o su inicial, el motivo, logos de apps
reales. El texto son DOS burbujas contiguas de V12 completo (la 2.ª y la 3.ª), no un resumen.
**TEXTO A COMPONER:** cabecera *Contacto* — contacto: *¿Todo bien?* — Sebastián: *Todo bien. Si alguien
pregunta por mí, cúbreme. Dices que ando saludando por las mesas.* (Sin fecha ni hora.)

(`gallery/v12-mensajes-mateo.png` — V12 completo, entrega a T+50)
**PROMPT:** La misma interfaz de mensajería que V12 parcial (mismo diseño, mismo tono informal de
amigos), ahora la conversación completa, con el nombre del contacto visible. Formato vertical.
**EVITAR:** cambiar el diseño respecto a V12 parcial, iconografía de alarma, resaltados, que sea
idéntico en estilo a V10 hasta confundirse (debe parecerse pero ser otra conversación).
**TEXTO A COMPONER:** cabecera *Mateo Silva*. *Sáb 21/06, 20:31* — Sebastián: *Hermano, necesito pedirte
un favor. Esta noche me ausento un rato, nada grave.* — Mateo: *¿Todo bien?* — Sebastián: *Todo bien. Si
alguien pregunta por mí, cúbreme. Dices que ando saludando por las mesas.* — Mateo: *¿Es lo de ya sabes
quién?* — Sebastián: *Sí. Lo cierro esta noche y se acaba. No le digas a nadie.* — Mateo: *Tranquilo.
Aquí te cubro.* — Sebastián: *Vuelvo antes del primer baile.*

(`gallery/v13-registro-fotografo.png` — V13, registro de horarios del fotógrafo)
**PROMPT:** Documento diegético tipo hoja de registro simple de un fotógrafo profesional, con una tabla
funcional de nombres de archivo, horas y origen de las fotos de una boda, formato de documento de
trabajo sobrio, impreso o digital, luz neutra. Formato vertical. Dejar vacías las celdas de la tabla.
**EVITAR:** iconografía de alerta, resaltar ninguna fila, interpretaciones escritas.
**TEXTO A COMPONER:** título *Registro de fotografías — Boda S. Lagos & V. Paz — Finca Los Almendros*.
Columnas *Archivo · Hora · Origen · Descripción*: *IMG_0412 · 16:40 · Cámara principal · Detalle,
interior de auto* — *IMG_0877 · 20:00 · Cámara principal · Dos personas conversando, rincón del salón* —
*IMG_0951 · 20:40 · Cámara principal · Grupo en la pista de baile* — *ALB_031 · 20:52 · Álbum compartido
(móvil de invitado) · Invitado en el estacionamiento* — *IMG_1004 · 21:00 · Cámara principal · Mesa de
postres* — *ALB_044 · 21:05 · Álbum compartido (móvil de invitado) · Invitados posando, camino del jardín
al fondo*. Pie: *Relojes de las cámaras y de los móviles del álbum verificados contra la hora de
referencia: sincronizados.*

(`gallery/v14-gemelo-camino.png` — V14, gemelo junto al camino)
**PROMPT:** Fotografía diegética funcional de un gemelo de camisa plateado distintivo (cuadrado, con un
borde grabado, mismo que el de Sebastián en la biblia visual), tirado en el suelo junto al borde de
piedra de un camino de jardín, cerca de la rueda de una carretilla de jardinería, fotografiado durante
la investigación a la luz del día. El objeto es pequeño y no se resalta. Luz natural neutra. Formato 4:5.
**EVITAR:** sangre, indicios de violencia, resaltados artificiales, círculos o flechas.

## 4. Audio

Los guiones están en `case.php`; se exportan listos para estudio con
`php artisan immersion:export-audio-scripts la-boda-de-los-ausentes`. Guardar en `audio/`.

(`audio/a1-ambiente-recepcion.mp3` — A1, ambiente de la recepción, 10–12 s)
**Guion:** *«¡Y ahora, en unos minutos, el primer baile de los novios!»* — voz de maestro de ceremonias
lejana, alegre y cálida.
**PROMPT:** Ambiente sonoro diegético de una recepción de boda al aire libre: música festiva de fondo,
risas y conversación distantes, tintineo de copas, un brindis breve a lo lejos y, sobre ello, el anuncio
alegre de un maestro de ceremonias del próximo primer baile de los novios. Sin música dramática.
**EVITAR:** tono siniestro o premonitorio, música de tensión.

(`audio/a2-mensaje-voz-lucia.mp3` — A2, mensaje de voz de Lucía, 12–15 s)
**Guion:** *«Sebastián, ya llevo un rato aquí. Entiendo si te demoraste con algo de la fiesta, pero no
puedo quedarme mucho más sin que alguien note que no estoy en mi mesa. Avísame.»*
**PROMPT:** Mensaje de voz diegético de una mujer adulta, tono bajo y discreto con impaciencia contenida
pero sin alarma, dejado a alguien que no llegó a una cita acordada durante una fiesta, pidiendo que le
avise pronto porque no puede ausentarse mucho más tiempo sin que lo noten; ambiente nocturno de exterior
de fondo (grillos, un rumor lejano de fiesta), sin música. **EVITAR:** tono de pánico o alarma explícita,
música de tensión.

(A3 — confesión de Rodrigo) **No se graba.** Es `solution.confession_script` en `case.php`: se genera
por partida (con voz Orus) para incluir las preguntas que esa mesa le hizo a Rodrigo.
