# Prompts de assets — La última función

Total: **1 portada + 3 retratos + 0 fotos + 1 audio**.

Todo se guarda en `public/express-assets/la-ultima-funcion/`:

| Archivo | Contenido |
|---|---|
| `hero.jpg` | portada (4:5, 1080×1350) |
| `portraits/nuria.jpg` · `tomas.jpg` · `elena.jpg` | retratos 3:4 (900×1200) |
| `audio/e3.mp3` | aviso de evacuación por megafonía (E3, lo oyen todos) |

No hay fotos de evidencia: E1, E2, E4, E5 y E6 son documentos y capturas que la app dibuja con el texto del caso.

## Orden de producción recomendado

1. **Biblia visual** (abajo).
2. **Portada**.
3. **Retratos** (3).
4. **Audio** E3: es la pista que marca la hora del incidente; oye el resultado en un celular.

## Regla de oro

**Ninguna imagen lleva texto.** El audio dice únicamente la frase del aviso: **no menciones la hora** (20:17); esa la pone la app en la transcripción.

## Biblia visual de continuidad

- **Lugar:** el área técnica de un teatro universitario durante un estreno: mesa de regiduría con hojas de indicaciones, una máquina de niebla, un altavoz de aviso en la pared, luces de servicio tenues.
- **Nuria Beltrán**, directora de escena, 38–45 años: ropa negra, auricular de regiduría; gesto contenido y convencido.
- **Tomás Cárdenas**, técnico de efectos, 25–32 años: camiseta negra de equipo técnico, linterna frontal en la frente; gesto directo y frustrado.
- **Elena Duarte**, productora, 45–55 años: blazer claro, credencial de organizadora sin texto legible; gesto institucional y defensivo.

Estilo común: fotografía documental realista, luz creíble, sin texto, sin marcas reales, sin rostros de personas reales.

---

## 1. Portada

(`hero.jpg`)
**PROMPT:** Fotografía de portada de una investigación de misterio teatral: la parte técnica de un teatro universitario con una máquina de niebla en primer plano soltando un hilo tenue de bruma, hojas de indicaciones esparcidas sobre una mesa de regiduría y, en la pared del fondo, un altavoz de aviso con una pequeña luz. Luz de servicio azulada, ambiente de última función. Composición vertical 4:5 para móvil, la máquina de niebla junto a las hojas como objeto protagonista, zona superior limpia. Estilo realista documental, sin texto integrado. **EVITAR:** texto legible, logotipos, personas reconocibles, marcas reales, iconografía policial, pánico o multitudes, estética de terror.

## 2. Retratos

(`portraits/`) — plano de busto tipo credencial, expresión neutral humana, fondo discreto sin texto, vertical 3:4.

**`nuria.jpg` — Nuria Beltrán**
**PROMPT:** Retrato tipo credencial de una mujer ficticia de 38 a 45 años, directora de escena de un teatro: ropa negra sencilla, un pequeño auricular de regiduría en una oreja, pelo recogido; gesto contenido y convencido de su causa. Plano de busto, fondo de bambalinas desenfocado, luz tenue pero natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`tomas.jpg` — Tomás Cárdenas**
**PROMPT:** Retrato tipo credencial de un hombre ficticio de 25 a 32 años, técnico de efectos de un teatro: camiseta negra lisa de equipo técnico, una linterna frontal apagada sobre la frente, un walkie sin marcas colgando del cinturón; gesto directo y algo frustrado. Plano de busto, fondo de cabina técnica desenfocado, luz ambiental natural, cámara realista, vertical 3:4. **EVITAR:** texto, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

**`elena.jpg` — Elena Duarte**
**PROMPT:** Retrato tipo credencial de una mujer ficticia de 45 a 55 años, productora de un estreno teatral: blazer claro, una credencial de organizadora colgada del cuello **sin texto legible**, pelo corto cuidado; gesto institucional y defensivo. Plano de busto, fondo de foyer de teatro desenfocado, luz natural, cámara realista, vertical 3:4. **EVITAR:** texto legible, logotipos, uniforme policial, armas, estética de villano, parecido con personas reales.

## 3. Audio

**`audio/e3.mp3` — Aviso de evacuación (E3)**
Dura unos 7 s, mono, MP3. Debe entenderse en una sala ruidosa y con el celular sin auriculares.

**GUION LITERAL:** «[timbre breve] Atención. Evacúen con calma por las salidas señalizadas.»

**PROMPT / DIRECCIÓN:** Aviso de megafonía de teatro en español neutro, voz profesional y **tranquila** (sin pánico, sin gritos), con un **timbre corto al inicio** y una reverberación leve de sala. Sin música ni efectos adicionales. **No decir la hora** ni ninguna otra palabra fuera del guion. **EVITAR:** música, sirenas, tono alarmado, decir «20:17».

La app muestra además la transcripción del aviso.

## Lista de verificación

- [ ] `hero.jpg` sin texto
- [ ] 3 retratos, tres personas distintas
- [ ] `audio/e3.mp3` con timbre al inicio, voz tranquila y sin decir la hora
