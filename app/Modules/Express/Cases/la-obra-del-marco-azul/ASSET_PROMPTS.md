# Prompts de assets — La obra del marco azul

Total: **1 portada + 3 retratos + 2 fotos + 0 audios**.

Todo se guarda en `public/express-assets/la-obra-del-marco-azul/`:

| Archivo | Contenido |
|---|---|
| `hero.jpg` | portada (4:5, 1080×1350) |
| `portraits/ines.jpg` · `mateo.jpg` · `julia.jpg` | retratos 3:4 (900×1200) |
| `e1.jpg` | foto del montaje con la **muesca** en el marco (E1, lado A) |
| `e4.jpg` | foto del cuadro expuesto **sin muesca** + reverso sin sello (E4, lado A) |

No hay audio. E2, E3, E5 y E6 son documentos que la app dibuja con el texto del caso.

## Orden de producción recomendado

1. **Biblia visual** (abajo): fija la pintura y el marco ANTES de generar nada.
2. **E1 primero**; luego **E4** derivada de E1 (misma pared, mismo ángulo, misma luz). Es el corazón del caso: la diferencia entre ambas debe ser la muesca y el sello.
3. **Portada**.
4. **Retratos** (3).

## Regla de oro

**Ninguna imagen lleva texto.** Las dos fotos se comparan entre sí, así que deben ser **la misma pintura, el mismo encuadre y la misma luz** (solo cambian la muesca del marco y el reverso). El texto de las horas y la descripción la pone la app.

## Biblia visual de continuidad

- **La pintura:** un paisaje contemporáneo (colinas y cielo, colores serenos) en un **marco de madera azul**. **Original:** tiene una **pequeña muesca de madera expuesta en la esquina inferior izquierda** del marco. **Copia:** el marco es parecido pero **sin muesca**.
- **Reverso del bastidor:** el original lleva un **sello triangular** estampado; la copia no lleva ninguno (bastidor liso).
- **Lugar:** galería pequeña de arte contemporáneo, paredes blancas, luz de montaje, una funda rígida para transportar cuadros en un rincón.
- **Inés Valdés**, representante de la artista, 40–45 años: elegante, abrigo largo y pañuelo; gesto cortante.
- **Mateo Sarmiento**, curador, 35–45 años: camisa abotonada, gafas, llavero de sala; gesto meticuloso y algo avergonzado.
- **Julia Márquez**, encargada de limpieza, 40–50 años: delantal de trabajo, guantes en el bolsillo; expresión reservada.

Estilo común: fotografía documental realista, luz creíble, sin texto, sin marcas reales, sin rostros de personas reales.

---

## 1. Portada

(`hero.jpg`)
**PROMPT:** Fotografía de portada de una investigación de misterio de arte: una galería contemporánea blanca con una pintura de paisaje en un marco de madera azul colgada en la pared, con luz de montaje cálida; en un rincón, una funda rígida alargada para transportar cuadros apoyada contra la pared. Sensación de calma y de algo que no encaja. Composición vertical 4:5 para móvil, el cuadro como objeto protagonista, zona superior limpia. Estilo realista documental, sin texto integrado. **EVITAR:** texto, placas legibles, firmas, personas reconocibles, marcas reales, iconografía policial, estética de terror.

## 2. Retratos

(`portraits/`) — plano de busto tipo credencial, expresión neutral humana, fondo discreto sin texto, vertical 3:4.

**`ines.jpg` — Inés Valdés**
**PROMPT:** Retrato tipo credencial de una mujer ficticia de 40 a 45 años, representante de una artista: abrigo largo oscuro y un pañuelo de seda, pelo recogido con pulcritud, mirada elegante y cortante. Plano de busto, fondo de galería blanca desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`mateo.jpg` — Mateo Sarmiento**
**PROMPT:** Retrato tipo credencial de un hombre ficticio de 35 a 45 años, curador de una galería: camisa abotonada, gafas de pasta, un llavero de sala colgando del cinturón o del cuello; gesto meticuloso y algo avergonzado. Plano de busto, fondo de sala de exposición desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`julia.jpg` — Julia Márquez**
**PROMPT:** Retrato tipo credencial de una mujer ficticia de 40 a 50 años, encargada de limpieza de una galería: delantal de trabajo liso, guantes de goma asomando de un bolsillo, pelo recogido; expresión reservada y factual. Plano de busto, fondo de pasillo de galería desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

## 3. Fotos de evidencia

(misma pared, mismo ángulo, misma luz en ambas)

**`e1.jpg` — Foto del montaje (E1, original)**
**PROMPT:** Fotografía hecha con un celular durante el montaje de una exposición: una pintura de paisaje en un marco de madera azul, vista de frente. **En la esquina inferior izquierda del marco hay una pequeña muesca donde se ve la madera clara expuesta** (debe verse comprobable pero sin destacarla). Luz de montaje, pared blanca, ligero desorden de montaje alrededor (escalera, cinta). Formato 4:5. **EVITAR:** texto, firmas legibles, flechas o círculos señalando la muesca, personas reconocibles, marcas reales.

**`e4.jpg` — Foto del cuadro expuesto (E4, copia)**
**PROMPT:** Composición de conservación en dos tomas neutrales apiladas: arriba, la **misma pintura de paisaje con el mismo marco azul, en el mismo lugar y con la misma luz que la foto del montaje, pero el marco está intacto, sin muesca en la esquina inferior izquierda**; abajo, el **reverso del bastidor de madera, completamente liso, sin ningún sello ni marca**. Aspecto de foto técnica de inspección, luz pareja. Formato 4:5. **EVITAR:** texto, flechas o círculos, sellos o marcas en el reverso, cambios en la pintura, personas reconocibles.

## Lista de verificación

- [ ] `e1.jpg`: muesca visible en la esquina inferior izquierda
- [ ] `e4.jpg`: marco sin muesca y reverso liso; misma pintura que `e1.jpg`
- [ ] Ninguna foto con flechas, círculos ni texto
