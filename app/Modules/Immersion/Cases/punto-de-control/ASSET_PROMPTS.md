# Prompts de assets — Punto de Control

Total: **1 portada + 10 retratos + 14 evidencias visuales + 0 audios** (la confesión de Bruno
se genera por partida y no se graba; este caso no tiene audio dentro de la línea de tiempo).

Todo se guarda en `public/immersion/punto-de-control/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `portada.png` (después correr `php artisan platform:build-case-images`) |
| `photos/` | los 10 retratos |
| `gallery/` | las 14 evidencias visuales |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (10): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera V6 con el rostro de Julieta y fija
   ese rostro antes que nada: V6 es la prueba central del caso.
5. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V5 |
| Expansión | 12 | V4a (tabla parcial) |
| Pistas privadas | 25 | V2 y V3 (a un jugador), V13 (a otro) |
| Complicación | 40 | V7, V6, V4 (tabla completa) |
| Giro | 55 | DF (declaración escrita de Fabiola, sin imagen), V8, V9, V10, V11, V12 |
| Cierre | 63 | (solo texto) |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni tablas**, y en este caso el
dato exacto ES la prueba (14:02 ≠ 14:44; 78 min frente a 42). Para V1, V2, V3, V4a, V4, V6,
V7, V8, V10 y V13: genera la imagen con la zona de texto **vacía o con texto de relleno
ilegible**, y compón después el texto exacto indicado en "TEXTO A COMPONER" con un editor
(Canva, Figma, Photoshop). Verifica cada hora contra `case.php` antes de guardar.

**Cronología maestra (todas las horas del caso salen de aquí):** Salida 07:30 · Transición:
114 10:12, 115 10:13, 207 10:05 · Quebrada Seca: 207 13:50, 114 14:02, 115 14:03 · Foto V9
13:58 · Arco de la Garganta: 114 y 115 14:44, 207 15:08 · Puente Viejo: 114 y 115 15:36, 207
16:00 · Meta: 114 17:41, 115 17:42, 207 18:02.

## Biblia visual de continuidad (pegar en los retratos)

- **Renata Campos**, 27: cabello oscuro en trenza deportiva, complexión atlética, chaleco técnico de hidratación azul/gris con dorsal 114.
- **Bruno Salcedo**, 31: complexión atlética robusta, cabello corto oscuro, ropa técnica de Team Altair Endurance con dorsal 207.
- **Julieta Ferreira**, 26: cabello castaño claro en cola alta, chaleco técnico verde/gris de Team Cóndor Trail con dorsal 115.
- **Esteban Nava**, 48: complexión media, gorra y chaleco identificador de "Organización", radio en el pecho.
- **Rocío Salgado**, 44: ropa deportiva de entrenadora, tablet o cronómetro en mano, chaqueta con logo genérico de patrocinador.
- **Darío Pinto**, 29: chaleco de voluntario con identificación, gorra, lector portátil de chips.
- **Manuela Ríos**, 37: chaleco de fotógrafa con múltiples bolsillos, cámara profesional al cuello, credencial de prensa.
- **Nico Fuentes**, 25: ropa técnica de trail running, dorsal de Team Cóndor Trail.
- **Fabiola Reyes**, 52: chaleco de voluntaria de puesto de apoyo, gorro de sol, mesa con agua y alimentos al fondo.
- **Raid Sierra Alta**: terreno rural/montañoso, senderos de tierra y roca, zona de transición con carpas de material (Potrero La Ceiba), camino forestal secundario (Camino de los Pinos), cañón rocoso (Cañón de la Garganta), carpa de cronometraje con monitores sencillos en la meta.
- **El utilitario**: camioneta utilitaria discreta, gris, sin marca reconocible ni matrícula legible (V9).

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales.

---

## 1. Portada

(`cover/portada.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio deportivo: un sendero de montaña de tierra y roca al atardecer, con un arco de control de carrera (estructura sencilla de cronometraje con una antena) en el centro, y un chaleco técnico de corredora azul/gris con un dorsal colgado del arco, sin nadie dentro; al fondo, un cañón rocoso y un bosque oscuro. Sensación de carrera terminada y de ausencia, frío, sin dramatismo. Debe sugerir que algo se registró sin que nadie estuviera. Formato horizontal 16:9, estilo realista premium, sin texto integrado. **EVITAR:** sangre, cadáver, iconografía policial, estética de terror, rostros visibles, números de dorsal legibles, texto o logos.

## 2. Retratos

(`photos/renata-campos.png` — víctima)
**PROMPT:** Retrato diegético tipo foto de ficha de inscripción de una corredora de trail de 27 años, cabello oscuro recogido en trenza deportiva, complexión atlética, chaleco técnico de hidratación azul/gris con dorsal 114, expresión serena, decidida y algo reservada, fondo neutro de zona de salida de una carrera. Formato retrato. **EVITAR:** apariencia de víctima, dramatización, sangre, logos de marcas reales.

(`photos/bruno-salcedo.png` — Bruno Salcedo)
**PROMPT:** Retrato diegético de un hombre de 31 años, complexión atlética robusta, cabello corto oscuro, ropa técnica de Team Altair Endurance con dorsal 207, expresión segura y competitiva con un asomo de tensión, fondo de zona de meta de una carrera de montaña. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, iluminación dramática, gesto agresivo.

(`photos/julieta-ferreira.png` — Julieta Ferreira)
**PROMPT:** Retrato diegético de una mujer de 26 años, cabello castaño claro en cola alta, chaleco técnico verde/gris de Team Cóndor Trail con dorsal 115, expresión afectuosa y preocupada, fondo de zona de transición con carpas de material. Formato retrato. **EVITAR:** apariencia de culpable, llanto exagerado, dramatización. (Fija este rostro: debe ser reconocible como la corredora de V6.)

(`photos/esteban-nava.png` — Esteban Nava)
**PROMPT:** Retrato diegético de un hombre de 48 años, complexión media, gorra y chaleco identificador de "Organización", radio en el pecho, expresión institucional, calmada y algo defensiva, fondo de la carpa de cronometraje con monitores sencillos. Formato retrato. **EVITAR:** villano obvio, gesto amenazante, texto legible.

(`photos/rocio-salgado.png` — Rocío Salgado)
**PROMPT:** Retrato diegético de una mujer de 44 años, ropa deportiva de entrenadora, chaqueta con logo genérico de patrocinador, tablet o cronómetro en mano, expresión profesional, protectora y evasiva, fondo de zona de servicio de vehículos de una carrera. Formato retrato. **EVITAR:** apariencia de culpable, mirada siniestra, logos de marcas reales, vehículo identificable.

(`photos/dario-pinto.png` — Darío Pinto)
**PROMPT:** Retrato diegético de un hombre de 29 años, chaleco de voluntario con identificación, gorra, lector portátil de chips en la mano, expresión nerviosa y técnica, fondo del puesto de un control de carrera junto a un sendero. Formato retrato. **EVITAR:** dinero o sobres visibles, villano obvio, texto legible.

(`photos/manuela-rios.png` — Manuela Ríos)
**PROMPT:** Retrato diegético de una mujer de 37 años, chaleco de fotógrafa con múltiples bolsillos, cámara profesional al cuello, credencial de prensa, expresión profesional y algo nerviosa, fondo de sendero de montaña en carrera. Formato retrato. **EVITAR:** gesto de culpa obvio, texto legible en la credencial, marcas reales de cámara.

(`photos/nico-fuentes.png` — Nico Fuentes)
**PROMPT:** Retrato diegético de un hombre de 25 años, ropa técnica de trail running, dorsal de Team Cóndor Trail, expresión franca, preocupada y algo culpable por no haber preguntado más, fondo de zona de meta. Formato retrato. **EVITAR:** apariencia de sospechoso, dramatización.

(`photos/fabiola-reyes.png` — Fabiola Reyes)
**PROMPT:** Retrato diegético de una mujer de 52 años, chaleco de voluntaria de puesto de apoyo, gorro de sol, expresión cálida, observadora y práctica, con una mesa con agua y alimentos al fondo, junto a un sendero. Formato retrato. **EVITAR:** apariencia de sospechosa, dramatización.

(`photos/sistema-chiptrack-sierra.png` — sistema de cronometraje)
**PROMPT:** Imagen diegética tipo retrato funcional de una consola de cronometraje deportivo en una carpa de carrera, con un monitor sobrio que muestra listas de dorsales y horarios, una antena pequeña y un lector junto al teclado, usada como avatar del sistema. Formato retrato. **EVITAR:** marcas reales, alarmas rojas, fallos visibles, estética futurista, texto legible.

## 3. Evidencias visuales

(`gallery/v1-mapa-oficial-carrera.png` — V1, mapa oficial)
**PROMPT:** Mapa diegético limpio y funcional de una carrera de aventura de montaña, en estilo de mapa de organización deportiva impreso o digital: trazado oficial con seis controles numerados, curvas de nivel simples, leyenda básica y símbolos de puestos de apoyo; terreno de senderos, un cañón y una quebrada. Dejar vacías las etiquetas de los controles. Formato vertical o cuadrado. **EVITAR:** rutas alternativas marcadas, camino forestal, iconografía de alerta, coordenadas.
**TEXTO A COMPONER:** controles numerados en orden: **1 Salida — Plaza de la Estación · 2 Zona de transición — Potrero La Ceiba · 3 Control previo al cañón — Quebrada Seca · 4 Tramo del cañón — Arco de la Garganta (control automático, sin personal) · 5 Control posterior — Puente Viejo · 6 Meta — Polideportivo de Peñablanca.** Símbolos de puesto de apoyo (uno, «Puesto El Roble») entre los controles 3 y 4. Título: *Raid Sierra Alta — Mapa oficial*.

(`gallery/v5-salida-ambiente.png` — V5, salida)
**PROMPT:** Fotografía diegética de ambiente de la salida de una carrera de aventura de montaña a primera hora de la mañana, con siluetas genéricas de corredores calentando y organizando su equipo en una plaza de pueblo, sin rostros protagonistas reconocibles, con tensión competitiva y luz fresca. Formato horizontal. **EVITAR:** rostros protagonistas reconocibles, marcas deportivas reales, texto legible.

(`gallery/v4a-cronometraje-parcial.png` — V4 parcial, entrega a T+12)
**PROMPT:** Captura de pantalla diegética de un sistema simple de cronometraje deportivo, con una tabla funcional de dorsal, equipo y dos columnas de horarios de paso, interfaz sobria. Dejar vacías las celdas de texto. Ninguna fila resaltada. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, más de dos columnas de horarios.
**TEXTO A COMPONER:** título *ChipTrack Sierra — Registro de lecturas (parcial)*. Columnas: *Dorsal · Equipo · Salida · Transición*. Filas: **102 · Team Páramo Sur · 07:30 · 09:58** / **114 · Team Cóndor Trail · 07:30 · 10:12** / **115 · Team Cóndor Trail · 07:30 · 10:13** / **133 · Team Roca Viva · 07:30 · 10:20** / **148 · Team Sendero Norte · 07:30 · 10:27** / **163 · Team Roca Viva · 07:30 · 10:39** / **207 · Team Altair Endurance · 07:30 · 10:05**.

(`gallery/v2-mapa-marcado-renata.png` — V2, mapa de Renata; entrega privada a T+25)
**PROMPT:** Fotografía diegética de un mapa de carrera personal, plegado y algo desgastado, sobre una mesa de carpa, con anotaciones manuscritas en bolígrafo sobre el tramo del cañón y una flecha hacia una zona fuera de la ruta oficial; luz natural cálida. Dejar vacío el texto manuscrito. Formato vertical. **EVITAR:** iconografía de alerta, dramatización, texto impreso legible salvo el manuscrito.
**TEXTO A COMPONER:** a mano, junto a una flecha que sale de la ruta cerca de Quebrada Seca hacia un camino fino sin marcar: **«verificar aquí»**. Junto al cañón: **«cañón»**. Mismo trazado general que V1.

(`gallery/v3-bitacora-renata.png` — V3, bitácora; entrega privada a T+25)
**PROMPT:** Fotografía diegética de una página de cuaderno de entrenamiento deportivo, con anotaciones manuscritas de tiempos y sensaciones de una carrera anterior, una de ellas subrayada, en formato de bitácora funcional; luz natural cálida. Dejar vacío el texto manuscrito. Formato vertical. **EVITAR:** iconografía de alerta, nombres completos legibles.
**TEXTO A COMPONER:** a mano: *Trail Valle Alto — domingo.* Tiempos: *Km 12: 1h 08 · Km 24: 2h 31 · Meta: 4h 52.* Nota subrayada: **«Bruno otra vez. Tiempos de recuperación imposibles en los tramos largos. Anotar y comparar.»** Solo el nombre de pila: nunca el apellido.

(`gallery/v13-mensaje-renata-nico.png` — V13, mensaje; entrega privada a T+25)
**PROMPT:** Captura de pantalla diegética de una conversación de mensajería breve entre dos personas, formato de captura de móvil, interfaz simple y genérica, tono informal. Dejar vacíos los textos. Formato vertical. **EVITAR:** iconografía de alarma, mención de Bruno, logos de apps reales.
**TEXTO A COMPONER:** *Renata Campos — martes 21:14.* **«Este sábado voy a comprobar algo en el tramo del cañón. Cúbreme si preguntan, ¿vale? Después te cuento.»** *Nico:* «¿Algo de la carrera?» *Renata:* «Más o menos. Luego te explico.»

(`gallery/v7-informe-chiptrack.png` — V7, informe técnico)
**PROMPT:** Documento diegético tipo informe técnico de un sistema de cronometraje deportivo, con una tabla resumen por dispositivo y una nota final en letra pequeña pero legible, formato de informe sobrio, impreso o digital. Dejar vacías la tabla y la nota. Formato vertical. **EVITAR:** iconografía de alerta, lenguaje alarmista, dramatizar la nota.
**TEXTO A COMPONER:** título *ChipTrack Sierra — Informe de lecturas*. Tabla *Dorsal · Equipo · Lecturas · Tiempo total*: **114 · Team Cóndor Trail · 6/6 · 10 h 11** / **115 · Team Cóndor Trail · 6/6 · 10 h 12** / **207 · Team Altair Endurance · 6/6 · 10 h 32**. *Cámaras fijas: Arco de la Garganta, Puente Viejo, Meta.* Nota al pie: **«ChipTrack Sierra registra dispositivos (chips integrados en el chaleco o el dorsal) por proximidad a las antenas de cada control. El sistema no verifica ni almacena la identidad de la persona que porta el dispositivo, ni puede determinar si un dispositivo cambia de portador entre controles.»**

(`gallery/v6-arco-garganta.png` — V6, arco automático del cañón)
**PROMPT:** Fotografía diegética de una cámara fija automática de control deportivo, ángulo elevado y calidad funcional, que captura a una corredora de 26 años, cabello castaño claro en cola alta, con un chaleco técnico de hidratación azul/gris y el dorsal 114 claramente visible, cruzando un arco de control sin personal en un tramo de cañón rocoso; luz natural de exterior. Dejar vacío el rótulo de la cámara. Formato horizontal. **EVITAR:** iconografía de alerta, resaltados sobre el rostro, un rostro que coincida con la trenza oscura de Renata (debe ser reconociblemente Julieta), otros corredores en cuadro. (Fija el rostro de `photos/julieta-ferreira.png`.)
**TEXTO A COMPONER:** dorsal **114** legible en el pecho. Rótulo de cámara: *ARCO DE LA GARGANTA · CAM 1 · 14:44:12*.

(`gallery/v4-cronometraje-completo.png` — V4 completo, entrega a T+40)
**PROMPT:** Captura de pantalla diegética de un sistema simple de cronometraje deportivo con una tabla funcional de dorsal, equipo y seis horarios de paso, interfaz sobria; mismo diseño que V4a, ahora con los seis controles. Dejar vacías las celdas. Ninguna fila resaltada. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, cambiar el diseño respecto a V4a.
**TEXTO A COMPONER:** título *ChipTrack Sierra — Registro de lecturas*. Columnas: *Dorsal · Equipo · Salida · Transición · Quebrada Seca · Arco Garganta · Puente Viejo · Meta*. Filas: **102 · Team Páramo Sur · 07:30 · 09:58 · 13:31 · 14:12 · 15:01 · 17:10** / **114 · Team Cóndor Trail · 07:30 · 10:12 · 14:02 · 14:44 · 15:36 · 17:41** / **115 · Team Cóndor Trail · 07:30 · 10:13 · 14:03 · 14:44 · 15:36 · 17:42** / **133 · Team Roca Viva · 07:30 · 10:20 · 14:11 · 14:53 · 15:44 · 17:55** / **148 · Team Sendero Norte · 07:30 · 10:27 · 14:19 · 15:02 · 15:55 · 18:07** / **163 · Team Roca Viva · 07:30 · 10:39 · 14:41 · 15:24 · 16:20 · 18:30** / **207 · Team Altair Endurance · 07:30 · 10:05 · 13:50 · 15:08 · 16:00 · 18:02**. Pie en letra pequeña: *Corte horario en Quebrada Seca: 14:30.*

(`gallery/v8-detalle-cronometraje.png` — V8, detalle de cronometraje del dorsal 207)
**PROMPT:** Captura de pantalla diegética de un detalle de cronometraje individual, con una tabla de intervalos por tramo del dorsal 207 comparada con la media del campo, interfaz sobria; el hueco como un dato más, sin resaltarlo. Dejar vacías las celdas. Formato horizontal. **EVITAR:** iconografía de alarma, resaltados artificiales, interpretación escrita del hueco.
**TEXTO A COMPONER:** título *ChipTrack Sierra — Detalle dorsal 207 (Team Altair Endurance)*. Columnas *Tramo · Dorsal 207 · Media del campo*: **Salida → Transición · 2 h 35 · 2 h 48** / **Transición → Quebrada Seca · 3 h 45 · 3 h 50** / **Quebrada Seca → Arco Garganta · 1 h 18 · 0 h 42** / **Arco Garganta → Puente Viejo · 0 h 52 · 0 h 52** / **Puente Viejo → Meta · 2 h 02 · 2 h 09**. (Nota para quien compone, NO va en la imagen: la media del campo es el promedio de los otros seis dorsales de la tabla V4; el único tramo claramente atípico del 207 es Quebrada Seca → Arco.)

(`gallery/v9-foto-vehiculo-camino.png` — V9, vehículo en el camino forestal)
**PROMPT:** Fotografía diegética de un tramo de sendero junto a un camino forestal, con calidad de cámara profesional de prensa deportiva: el sujeto principal es un corredor genérico en plano medio y, en segundo plano parcialmente desenfocado, un utilitario gris discreto estacionado junto al camino; luz natural de exterior, profundidad de campo media. Formato horizontal. **EVITAR:** matrículas legibles, marcas de vehículos reales, rostro protagonista del corredor, que el vehículo sea lo más llamativo del cuadro.
**TEXTO A COMPONER:** (opcional) línea de crédito pequeña en una esquina: *Foto: M. Ríos*.

(`gallery/v10-calzado-control-posterior.png` — V10, calzado en Puente Viejo)
**PROMPT:** Comparación lado a lado, formato horizontal, de dos fotografías de cámara fija del control posterior al cañón: a la izquierda, un corredor genérico con zapatillas y calcetas notablemente limpias; a la derecha, otro corredor con zapatillas visiblemente embarradas, en el mismo punto y con la misma luz natural. Dejar vacías las etiquetas. **EVITAR:** iconografía de alerta, diferencia demasiado sutil para notarse, rostros protagonistas reconocibles.
**TEXTO A COMPONER:** encabezado *Control posterior al cañón — Puente Viejo*. Izquierda: *dorsal 207 · 16:00*. Derecha: *dorsal 133 · 15:44*.

(`gallery/v11-refugio-forestal.png` — V11, refugio)
**PROMPT:** Fotografía diegética funcional de un refugio de montaña pequeño y sencillo, de madera, con una puerta cerrada, ventanas simples y un sendero de tierra que llega hasta él, en un entorno boscoso; luz natural cálida de tarde. Aspecto de fotografía de archivo de una brigada forestal. Formato horizontal. **EVITAR:** elementos amenazantes, señales de forcejeo, personas, dramatización.

(`gallery/v12-telefono-baliza.png` — V12, teléfono y baliza)
**PROMPT:** Fotografía diegética funcional de un teléfono móvil y un pequeño dispositivo de baliza de emergencia, ambos apagados, apoyados sobre una roca junto a un sendero forestal, sin ninguna persona presente; luz natural de exterior, mismo entorno de pinos y tierra que V11. Formato 4:5. **EVITAR:** indicios de violencia, resaltados artificiales, marcas reales.

## 4. Audio

Este caso **no tiene audio en la línea de tiempo**: el único audio es la confesión de Bruno, que
se genera por partida (no se graba).

(A1 — confesión de Bruno) **No se graba.** Es `solution.confession_script` en `case.php`: se genera por partida (con voz Fenrir) para incluir las preguntas que esa mesa le hizo a Bruno. Dirección de voz: empieza racional y justificándose, se quiebra hacia el reconocimiento genuino; no es un plan calculado sino alguien que dejó que la presión y el pánico lo llevaran a una decisión que no supo revertir. Sin música, sin sobreactuar, sin contenido violento.
