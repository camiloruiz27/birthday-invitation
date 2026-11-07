# Prompts de assets — La Final Sin Tablero

Total: **1 portada + 4 retratos + 5 evidencias visuales + 0 audios** (la confesión de Hugo
se genera por partida y no se graba; este caso no tiene audio dentro de la línea de tiempo).

Todo se guarda en `public/immersion/la-final-sin-tablero/`:

| Carpeta | Contenido |
|---|---|
| `cover/` | `cover.png` |
| `photos/` | los 4 retratos (afectada + 3 personas del caso) |
| `gallery/` | las 5 evidencias visuales |

## Orden de producción recomendado

1. **Biblia visual** (abajo): fíjala primero y pégala al inicio de cada prompt de retrato.
2. **Portada** (para empezar a vender y a probar el catálogo).
3. **Retratos** (4): son lo primero que ve el jugador y lo que exige el test de integridad.
4. **Evidencias** en orden de entrega (ver tabla). Genera V1 y V2 en la misma sesión visual
   (misma mesa, misma luz del café); V3 y V4 con la misma identidad gráfica del torneo.
5. **V5** al final: es el motivo y no debe revelar nada antes de su momento de entrega.
6. `php artisan platform:build-case-images`, luego `platform:sync-cases` y los tests.

| Entrega | Minuto | Evidencias |
|---|---|---|
| Gancho | 1 | V1, V2 |
| Reconstrucción | 20 | V3, V4 |
| Motivo | 35 | V5 |

## Regla de oro para los documentos con texto

Los generadores de imagen **no escriben bien horas, nombres ni cifras**, y en este caso el
dato exacto ES la prueba (Alba 47 ≠ Alba 43; Bruno 43 ≠ Bruno 47; 19:03 ≠ 19:12). Para V2,
V3, V4 y V5: genera la imagen con la zona de texto **vacía o con texto de relleno ilegible**,
y compón después el texto exacto indicado en «TEXTO A COMPONER» con un editor (Canva, Figma,
Photoshop). Verifica cada puntuación y cada hora contra `case.php` antes de guardar.

**Datos exactos de las dos piezas centrales (prueba del caso):**
- **V2 (hoja original):** Alba Reyes 47 pts · Bruno Costa 43 pts · hora 19:03 · foto de Luisa Prado
- **V3 (acta oficial):** Alba Reyes 43 pts · Bruno Costa 47 pts · ganador Bruno Costa · premio USD 200 · firmado Hugo Serrano · 19:12

## Cronología maestra (todos los datos del caso salen de aquí)

19:03 — Fotografía de hoja de puntuación (Luisa Prado): Alba 47 / Bruno 43 ·
19:04 — Mireya Campos firma la hoja original ·
19:07 — Mireya entrega la hoja a Hugo Serrano ·
19:12 — Hugo publica el acta oficial con datos invertidos: Alba 43 / Bruno 47, ganador Bruno Costa ·
Premio al ganador: USD 200 ·
Acuerdo de patrocinio vigente: USD 1,000 al café «Tablero Abierto» si Bruno resulta ganador oficial.

## Biblia visual de continuidad (pegar en los retratos)

- **Alba Reyes**, adulta joven: cabello medio, ropa informal elegante en tonos crema o azul petróleo, expresión serena y decepcionada pero no abatida, postura erguida.
- **Hugo Serrano**, adulto de mediana edad: camisa sobria y credencial genérica de organización, aspecto pulcro y competente; nunca apariencia de villano evidente.
- **Mireya Campos**, adulta: ropa práctica y funcional de árbitra sin uniforme deportivo reconocible, expresión meticulosa y profesional, portapapeles o bolígrafo en mano.
- **Bruno Costa**, adulto joven: chaqueta casual bien cuidada, actitud competitiva y contenida, mirada directa sin gestos triunfalistas ni maliciosos.
- **Café «Tablero Abierto»**: local de juegos de barrio al anochecer; madera oscura, lámparas cálidas suspendidas, estanterías con cajas de juegos desenfocadas al fondo; sin marcas comerciales reales.
- **El juego**: totalmente inventado y abstracto; fichas geométricas neutras y cuadrícula sin marca ni referencia a ningún juego comercial existente.
- **Documentos del torneo**: papel marfil, tinta azul oscuro y verde apagado, sellos genéricos, diseño de torneo local sobrio y coherente en V3 y V4.

Estilo común: realista, fotografía/documento diegético, sin texto integrado en los retratos,
sin logos de marcas reales, sin dramatización policial.

---

## 1. Portada

(`cover/cover.png`)
**PROMPT:** Imagen de portada cinematográfica sobria para un caso de misterio de torneo local: interior de un café de juegos de barrio al anochecer, mesa de madera oscura recién despejada con el rectángulo limpio que dejó el tablero retirado, dos sillas ligeramente separadas, una hoja de anotaciones boca abajo en un extremo y lámparas cálidas suspendidas sobre estanterías desenfocadas con cajas de juegos apiladas. Debe sugerir que el resultado ya no puede verificarse, con una atmósfera lúdica y elegante cruzada por una tensión contenida. Sin personas visibles, sin manos, sin siluetas, sin tablero activo, sin texto integrado, sin marcas ni logotipos. Formato horizontal 16:9, fotografía realista premium. **EVITAR:** piezas de juegos comerciales reconocibles, números o puntajes legibles, trofeos ostentosos, estética policial o de crimen, terror, sangre, dramatización.

## 2. Retratos

(`photos/alba-reyes.png` — afectada/víctima)
**PROMPT:** Retrato diegético tipo foto de ficha de torneo de una mujer adulta joven, cabello medio, ropa informal elegante en tonos crema o azul petróleo, expresión serena y decepcionada pero no abatida, mirada directa, fondo neutro del interior de un café de juegos con madera oscura desenfocada. Formato retrato 4:5. **EVITAR:** apariencia de víctima dramática, llanto, dramatización, texto legible, tablero o hoja de puntuaciones en la imagen.

(`photos/hugo-serrano.png` — Hugo Serrano, organizador y custodio del acta)
**PROMPT:** Retrato diegético de un hombre adulto de mediana edad, camisa sobria y credencial genérica de organización colgada al cuello, aspecto pulcro y competente, expresión institucional y algo a la defensiva, fondo neutro de una zona administrativa del café con un tablón de avisos desenfocado. Formato retrato 4:5. **EVITAR:** apariencia de culpable evidente, mirada siniestra o amenazante, iluminación dramática de interrogatorio, texto legible en la credencial.

(`photos/mireya-campos.png` — Mireya Campos, árbitra de la final)
**PROMPT:** Retrato diegético de una mujer adulta, ropa práctica y funcional de árbitra sin uniforme deportivo de marca reconocible, bolígrafo o portapapeles en la mano, expresión meticulosa y profesional con un asomo de incomodidad, fondo neutro de mesa de árbitro vacía en el café. Formato retrato 4:5. **EVITAR:** uniforme deportivo con marca real, gesto de culpa obvio, dramatización, texto legible.

(`photos/bruno-costa.png` — Bruno Costa, finalista declarado ganador)
**PROMPT:** Retrato diegético de un hombre adulto joven, chaqueta casual bien cuidada, actitud competitiva y contenida, mirada directa y segura sin triunfalismo excesivo, fondo neutro de estanterías desenfocadas de café de juegos. Formato retrato 4:5. **EVITAR:** gesto de victoria teatral, mirada maliciosa, apariencia de culpable evidente, text legible.

## 3. Evidencias visuales

(`gallery/v1-mesa-de-final.png` — V1, mesa recién despejada)
**PROMPT:** Fotografía diegética documental del interior de un café de juegos de barrio, tomada desde la altura de los ojos de alguien sentado en una silla vecina, con luz cálida de lámparas suspendidas: en el centro, una mesa de madera oscura con un rectángulo ligeramente más limpio donde estuvo el tablero retirado hace pocos minutos, dos sillas con los respaldos ligeramente separados como si los jugadores acabaran de levantarse, y un vaso de agua abandonado en un extremo. Sin tablero, sin fichas, sin hojas de puntuación visibles, sin personas en primer plano. Profundidad de campo suave con estanterías desenfocadas al fondo. Formato horizontal. **EVITAR:** tablero de juego activo, fichas en la mesa, hoja de puntajes, personas reconocibles, texto legible, dramatización de escena del crimen.
**TEXTO A COMPONER:** ninguno. Es una fotografía de ambiente sin texto crítico.

(`gallery/v2-hoja-de-puntuacion-original.png` — V2, hoja de puntuación fotografiada)
**PROMPT:** Fotografía diegética tomada desde arriba (picado suave) de una hoja de puntuación de un torneo amateur sobre la mesa de madera oscura del café, con luz cálida lateral: papel marfil ligeramente usado, encabezado con el nombre del torneo, dos secciones claramente diferenciadas para los dos finalistas con sus nombres y puntuaciones escritas a mano en tinta azul, una línea de hora en la parte inferior y una esquina desenfocada del café al fondo. La hoja tiene espacio amplio y legible para las cifras compuestas. Formato 4:5 vertical. Dejar los campos de puntuación, nombres y hora vacíos para composición posterior. **EVITAR:** cifras de puntuación ilegibles o inventadas por la IA, texto generado automáticamente, firma visible, iconografía de alerta.
**TEXTO A COMPONER:** encabezado *Torneo Tablero Abierto — Final*. Dos secciones: **Alba Reyes: 47 puntos** / **Bruno Costa: 43 puntos**. Pie: *Hora: 19:03 · Foto: Luisa Prado*. Nota breve: *Mayor puntaje gana.*

(`gallery/v3-acta-oficial.png` — V3, acta oficial con datos invertidos)
**PROMPT:** Documento diegético tipo acta formal de torneo, formato vertical A4, fotografiado sobre la madera oscura del café con luz cálida: papel marfil de mayor gramaje que la hoja de puntuación, encabezado más prominente con el nombre del torneo, una tabla central con los dos competidores y sus puntuaciones, una línea de ganador oficial, una línea de premio y una firma al pie con sello genérico tenue. Mismo diseño general que V4 (mismo encabezado, misma tipografía, mismo papel). Dejar todos los campos vacíos para composición posterior. Formato vertical 4:5. **EVITAR:** sello o escudo de institución real, firma legible generada por la IA, texto generado automáticamente, resaltados o tachones visibles.
**TEXTO A COMPONER:** encabezado *Torneo Tablero Abierto — Acta de la Final*. Tabla: **Alba Reyes: 43 puntos** / **Bruno Costa: 47 puntos**. Línea: *Ganador oficial: **Bruno Costa***. Línea: *Premio: **USD 200***. Pie: *Emitida por: Hugo Serrano · Hora: **19:12***. Sello genérico sin texto legible.

(`gallery/v4-cadena-de-custodia.png` — V4, registro de cadena de custodia)
**PROMPT:** Documento diegético tipo registro de custodia interno de un torneo local, formato vertical, fotografiado sobre la mesa del café: papel marfil, misma identidad gráfica que V3, una tabla sencilla de tres filas con columnas de hora, evento y responsable, sin flechas, tachones ni resaltados. Las filas son el único contenido; los márgenes son amplios y los campos están vacíos para composición posterior. Dos espacios de firma discreta en la parte inferior. Formato vertical 4:5. **EVITAR:** diseño diferente al de V3, flechas o círculos sobre alguna fila, lenguaje acusatorio, iconografía de alarma.
**TEXTO A COMPONER:** encabezado *Registro de custodia — Final del torneo*. Columnas: *Hora · Evento · Responsable*. Filas: **19:04 · Mireya Campos firma la hoja de puntuación (Alba 47 / Bruno 43) · M. Campos** / **19:07 · Mireya entrega la hoja a Hugo Serrano · M. Campos** / **19:12 · Hugo Serrano publica el acta oficial · H. Serrano**. Nota al pie: *Sin modificaciones documentadas en el marcador entre 19:04 y 19:12.*

(`gallery/v5-acuerdo-de-patrocinio.png` — V5, extracto del acuerdo de patrocinio)
**PROMPT:** Documento diegético tipo extracto de acuerdo de patrocinio comercial, formato vertical, fotografiado en picado suave sobre la mesa del café con un clip metálico en la esquina superior izquierda: papel blanco con membrete ficticio sobrio, párrafo de condiciones con espacios amplios para cifras y nombres, dos líneas de fecha y vigencia, firma ilegible al pie. Aspecto de documento corporativo sencillo pero formal. Dejar todos los campos vacíos para composición posterior. Formato vertical 4:5. **EVITAR:** logos o membrete de empresa real, dinero físico visible, contrato jurídico extenso, texto generado automáticamente, iconografía de alarma.
**TEXTO A COMPONER:** encabezado *Acuerdo de patrocinio — Café Tablero Abierto*. Cuerpo: *Aporte condicionado: **USD 1,000** al café «Tablero Abierto» si **Bruno Costa**, embajador del evento, resulta ganador oficial de la final.* Línea: *Premio al finalista ganador: **USD 200**.* Línea: *Vigencia: **final de hoy**.* Firma ilegible al pie, sin nombre.

## 4. Audio

Este caso **no tiene audio en la línea de tiempo**: el único audio es la confesión de Hugo, que
se genera por partida (no se graba).

(A1 — confesión de Hugo Serrano) **No se graba.** Es `solution.confession_script` en `case.php`:
se genera por partida (con voz Charon) para incluir las preguntas que esa mesa le hizo a Hugo.
Dirección de voz: comienza racional, justificándose en la presión del acuerdo de patrocinio y en
que «los puntos eran casi iguales»; va cediendo a medida que las evidencias se apilan, hasta un
reconocimiento contenido y sin violencia. Sin música, sin sobreactuación, sin contenido violento.
