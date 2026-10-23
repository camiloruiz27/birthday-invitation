# Prompts de assets — El mural de medianoche

Total: **1 portada + 3 retratos + 3 fotos + 0 audios**.

Todo se guarda en `public/express-assets/el-mural-de-medianoche/`:

| Archivo | Contenido |
|---|---|
| `hero.jpg` | portada (4:5, 1080×1350) |
| `portraits/raul.jpg` · `simon.jpg` · `paula.jpg` | retratos 3:4 (900×1200) |
| `e1.jpg` | mural completo con el logotipo, el día anterior (E1, lado A) |
| `e3.jpg` | el mismo mural con el rectángulo negro, 07:00 (E3, para todos) |
| `e6.jpg` | fotograma de cámara de seguridad, 06:18 (E6, para todos) |

No hay audio. E2, E4 y E5 son documentos y capturas que la app dibuja con el texto del caso.

## Orden de producción recomendado

1. **Biblia visual** (abajo).
2. **`e1.jpg` primero** (el mural con logotipo). **`e3.jpg` se deriva de él**: misma pared, misma perspectiva, solo cambian la luz (amanecer) y el rectángulo negro sobre el logotipo.
3. **`e6.jpg`** (cámara) y **retrato de Raúl**: misma chaqueta gris.
4. **Portada** y retratos restantes.

## Regla de oro

**Ninguna imagen lleva texto.** El «logotipo comercial» de E1 es una **forma abstracta ficticia sin letras legibles** (nunca una marca real). En E3 el rectángulo negro debe cubrir **exactamente** donde estaba. En E6 deja una esquina vacía: la hora (06:18) la pone la app.

## Biblia visual de continuidad

- **El mural:** pintura mural contemporánea, colorida y artística, sobre una pared larga de un pasillo abierto de campus. Un **sector derecho** contiene un **logotipo comercial ficticio** (forma geométrica abstracta, un solo color, sin letras) que no parece parte del diseño cultural.
- **Lugar:** pasillo abierto de un campus universitario, suelo de concreto, columnas, luz de tarde (E1) o de amanecer (E3).
- **Raúl Barrera**, coordinador cultural, 38–45 años: **chaqueta gris** (la misma en el retrato y en E6); gesto indignado e institucional.
- **Simón Vega**, artista no seleccionado, 24–30 años: manchas de pintura en la camiseta, mochila; gesto frustrado.
- **Paula Niño**, mantenimiento, 40–50 años: overol de trabajo, guantes; gesto práctico y desconfiado.

Estilo común: fotografía documental realista, luz creíble, sin texto, sin marcas reales, sin rostros de personas reales.

---

## 1. Portada

(`hero.jpg`)
**PROMPT:** Fotografía de portada de una investigación de misterio cultural: un mural colorido sobre la pared de un pasillo de campus universitario con **un rectángulo negro mate reciente** cubriendo una sección del lado derecho, y en el suelo, discretos, un bote de pintura negra y un rodillo. Luz de amanecer, pasillo vacío. Composición vertical 4:5 para móvil, el rectángulo negro sobre el mural como objeto protagonista, zona superior limpia. Estilo realista documental, sin texto integrado. **EVITAR:** texto, logotipos legibles, firmas, personas reconocibles, marcas reales, iconografía policial, estética de terror.

## 2. Retratos

(`portraits/`) — plano de busto tipo credencial, expresión neutral humana, fondo discreto sin texto, vertical 3:4.

**`raul.jpg` — Raúl Barrera**
**PROMPT:** Retrato tipo credencial de un hombre ficticio de 38 a 45 años, coordinador cultural de una universidad: **chaqueta gris** lisa (la misma de la imagen de cámara de seguridad), camisa clara, pelo corto con algo de canas; gesto indignado e institucional. Plano de busto, fondo de pasillo de campus desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`simon.jpg` — Simón Vega**
**PROMPT:** Retrato tipo credencial de un hombre ficticio de 24 a 30 años, artista plástico: camiseta con **manchas de pintura** de varios colores, correa de mochila al hombro, pelo algo largo y despeinado; gesto frustrado, mirada directa. Plano de busto, fondo de taller o pared de campus desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`paula.jpg` — Paula Niño**
**PROMPT:** Retrato tipo credencial de una mujer ficticia de 40 a 50 años, personal de mantenimiento de un campus: overol de trabajo liso, guantes de trabajo en la mano, pelo recogido bajo una gorra sencilla; gesto práctico y desconfiado. Plano de busto, fondo de bodega de mantenimiento desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

## 3. Fotos de evidencia

(E1 y E3: **misma pared, misma perspectiva y mismo encuadre**)

**`e1.jpg` — Mural completo, día anterior (E1)**
**PROMPT:** Fotografía frontal de un mural contemporáneo y colorido sobre una pared larga de un pasillo de campus, luz de tarde, tomada con un celular. En el **sector derecho** aparece un **logotipo comercial ficticio y genérico**: una forma geométrica abstracta de un solo color, **sin letras legibles ni marca real**, que se distingue claramente como ajeno al diseño artístico del mural. Mural completo y recién terminado, pasillo vacío. Formato 4:5. **EVITAR:** texto, letras en el logotipo, marcas reales, firmas legibles, flechas o círculos, personas reconocibles.

**`e3.jpg` — Hallazgo a las 07:00 (E3)**
**PROMPT:** La **misma foto** del mural de la imagen anterior (misma pared, misma perspectiva, mismo encuadre), ahora con **luz de amanecer**. **Único cambio intencional: un rectángulo negro mate reciente cubre exactamente la zona donde estaba el logotipo** del sector derecho; los bordes del rectángulo son algo irregulares, como pintados con rodillo. El resto del mural intacto. Sin realces, sin flechas, sin letras. Formato 4:5. **EVITAR:** texto, flechas o círculos, otros daños en el mural, personas reconocibles, marcas reales.

**`e6.jpg` — Fotograma del pasillo, 06:18 (E6)**
**PROMPT:** Imagen fija de una **cámara de seguridad alta** de servicio en un pasillo de campus, aspecto de video de vigilancia con grano leve y color apagado: un **hombre adulto con chaqueta gris** (la de Raúl) está de pie junto al **sector derecho del mural**, sosteniendo un **rodillo de pintura con pintura negra**; a sus pies, un bote de pintura. Sin pose dramática, luz baja de primeras horas. **Deja una esquina vacía** (sin sello de hora generado). Formato 4:5. **EVITAR:** texto, horas generadas, números, rostro nítido reconocible, marcas reales.

## Lista de verificación

- [ ] `e1.jpg` y `e3.jpg`: misma pared y perspectiva; en E3 el negro cubre justo el logotipo
- [ ] El logotipo de E1 no tiene letras ni es una marca real
- [ ] Raúl con la **misma chaqueta gris** en `raul.jpg` y `e6.jpg`
