# Prompts de assets — El pago perfecto

Total: **1 portada + 3 retratos + 1 foto (CCTV) + 0 audios**.

Todo se guarda en `public/express-assets/el-pago-perfecto/`:

| Archivo | Contenido |
|---|---|
| `hero.jpg` | portada (4:5, 1080×1350) |
| `portraits/ivan.jpg` · `mariana.jpg` · `felipe.jpg` | retratos 3:4 (900×1200) |
| `e5.jpg` | dos fotogramas de la cámara de mostrador (E5, lado B) |

No hay audio. E1–E4 y E6 son capturas y documentos que la app dibuja con el texto del caso.

## Orden de producción recomendado

1. **Biblia visual** (abajo): fija la chaqueta azul de Iván ANTES de generar el retrato y el CCTV.
2. **Retrato de Iván** primero; luego **E5** con el mismo hombre y la misma chaqueta.
3. **Portada**.
4. **Retratos** de Mariana y Felipe.

## Regla de oro

**Ninguna imagen lleva texto.** En E5 deja las **esquinas superiores vacías**: las horas (12:07 y 13:08) las pone la app. Lo que prueba el caso es que **el mismo hombre con la misma chaqueta azul** recoge una bolsa en dos momentos distintos.

## Biblia visual de continuidad

- **Lugar:** pequeño stand gastronómico en una feria de emprendimiento, mostrador con un teléfono de pago, bolsas de comida idénticas, recibos al lado de una caja, cocina abierta al fondo.
- **Iván Ortiz**, cliente, 28–35 años: **chaqueta azul** (la misma en el retrato y en E5), pelo oscuro corto; gesto seguro y aparentemente ofendido.
- **Mariana Torres**, cajera, 22–27 años: delantal del puesto sin logo, pelo recogido; gesto rápido y avergonzado.
- **Felipe Mora**, encargado de cocina, 35–45 años: gorro y delantal de cocina; gesto colaborador y apurado.
- Las **dos bolsas de comida son idénticas** (mismo papel, mismo cierre).

Estilo común: fotografía documental realista, luz creíble, sin texto, sin marcas reales, sin rostros de personas reales.

---

## 1. Portada

(`hero.jpg`)
**PROMPT:** Fotografía de portada de una investigación de misterio cotidiano: el mostrador de un pequeño stand gastronómico de feria con un teléfono de pago apoyado en un soporte, **dos bolsas de comida idénticas** una junto a la otra y varios recibos impresos junto a una caja registradora; al fondo, la cocina abierta desenfocada. Luz cálida de feria, sensación de que algo no cuadra. Composición vertical 4:5 para móvil, las dos bolsas idénticas como objeto protagonista, zona superior limpia. Estilo realista documental, sin texto legible. **EVITAR:** texto legible, cifras, logotipos, marcas reales, personas reconocibles, iconografía policial, estética de terror.

## 2. Retratos

(`portraits/`) — plano de busto tipo credencial, expresión neutral humana, fondo discreto sin texto, vertical 3:4.

**`ivan.jpg` — Iván Ortiz**
**PROMPT:** Retrato tipo credencial de un hombre ficticio de 28 a 35 años, cliente de un puesto de comida: **chaqueta azul** de tela lisa (será la misma en otras imágenes), pelo oscuro corto, un teléfono en la mano; gesto seguro y aparentemente ofendido. Plano de busto, fondo de feria desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`mariana.jpg` — Mariana Torres**
**PROMPT:** Retrato tipo credencial de una mujer ficticia de 22 a 27 años, cajera de un puesto de comida: delantal liso del puesto sin logo, pelo recogido en una coleta; gesto rápido y avergonzado. Plano de busto, fondo de mostrador de feria desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`felipe.jpg` — Felipe Mora**
**PROMPT:** Retrato tipo credencial de un hombre ficticio de 35 a 45 años, encargado de cocina: gorro y delantal de cocina lisos, antebrazos algo manchados de salsa; gesto colaborador y apurado. Plano de busto, fondo de cocina de stand desenfocado, luz ambiental natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

## 3. Foto de evidencia

**`e5.jpg` — Registro de retiro de pedidos, CCTV (E5)**
**PROMPT:** Secuencia de **dos fotogramas apilados verticalmente** de una cámara de seguridad fija de mostrador, ángulo alto y ligero grano de video: en ambos se ve **el mismo hombre adulto con chaqueta azul** (la de Iván) retirando una **bolsa de comida** del mostrador, en dos momentos distintos (el segundo fotograma con la bolsa algo diferente en la mano o la luz un poco distinta, mismo lugar). Aspecto de grabación de seguridad, sin pose dramática. **Deja las esquinas superiores de cada fotograma vacías** (sin sello de hora generado). Formato 4:5. **EVITAR:** texto, horas generadas, números, logotipos, rostros nítidos reconocibles, marcas reales.

## Lista de verificación

- [ ] Iván con la **misma chaqueta azul** en `ivan.jpg` y `e5.jpg`
- [ ] `e5.jpg`: dos fotogramas, esquinas superiores vacías, sin texto
- [ ] Las dos bolsas de `hero.jpg` son idénticas
