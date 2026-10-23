# Express — demo de feria

Mini‑casos de 2–4 minutos que juegan varios grupos a la vez, sin registro. Un
visitante (el GM, que también juega) crea una sala, invita de 1 a 4 amigos con
un QR/código y todos reciben pistas distintas (A/B) en su celular.

Todo vive en `app/Modules/Express` y se desactiva quitando el provider de
`config/app.php`. No toca el motor de partidas pagas (`Immersion`).

## Reutilizar en otra feria

1. Añade una entrada en `config/express.php → events` (slug, nombre, promo,
   UTM, casos permitidos).
2. Crea la promo con `php artisan platform:create-promo-code` (la que pongas en
   `promo_code`).
3. Imprime `/express/{slug}/cartel` (QR del stand con los UTM del evento).
4. Después: `php artisan express:report {slug}` y
   `php artisan express:export-leads {slug}`.

## Casos

`Cases/<slug>.json` (esquema `misteriocode.express.author.v1`, recortado a lo
que usa el motor). **Nunca** se serializa tal cual: `Support/PublicPayload`
construye lo que ve el jugador por lista blanca. Los prompts de assets están en
`ASSET_PROMPTS.md`; los archivos van en `public/express-assets/<slug>/`:

```
hero.jpg                      portada (4:5)
portraits/<suspect_id>.jpg    retrato 3:4
e<n>.jpg                      foto/CCTV de la evidencia En (si el caso la tiene)
audio/e<n>.mp3                audio de la evidencia En (si el caso lo tiene)
```

Sin assets el juego funciona: iniciales en vez de retrato y evidencia solo texto.

## Reloj

Segundos desde `started_at`, calculado en cada lectura (sin cron):
E1/E2 0 s · E3 20 s (abre interrogatorios) · E4/E5 70 s · E6 105 s ·
acusaciones 120 s · ventana hasta 150 s (revela antes si todos acusaron) ·
cierre duro 180 s. Se puede unir hasta 75 s después de iniciar.

## Rutas (todas bajo `/express`)

| Método | Ruta | Nombre | Qué hace |
|---|---|---|---|
| GET | `/express` | `express.home` | Landing del evento por defecto (`?e=slug`) |
| GET | `/express/crear` | `express.create` | Formulario del GM (caso + apodo + contacto) |
| POST | `/express/crear` | `express.rooms.store` | Crea sala + jugador GM → redirige a `express.room` |
| GET | `/express/unirse/{code}` | `express.join` | Formulario de unión |
| POST | `/express/unirse/{code}` | `express.join.store` | Crea jugador → redirige a `express.room` |
| GET | `/express/s/{token}` | `express.room` | Página de juego (Inertia `Express/Room`) |
| GET | `/express/s/{token}/estado` | `express.state` | JSON con el estado (poll, **0 consultas**) |
| POST | `/express/s/{token}/iniciar` | `express.start` | GM inicia (≥2 jugadores) |
| POST | `/express/s/{token}/compartir` | `express.share` | `{evidence_id}` comparte una pista con todos |
| POST | `/express/s/{token}/preguntar` | `express.ask` | `{suspect_id, question, topic_id?}` |
| POST | `/express/s/{token}/acusar` | `express.accuse` | `{who, how, why}` (ids opacos) |
| POST | `/express/s/{token}/otra-ronda` | `express.next.create` | `{case_slug}` crea la sala siguiente (el primero gana) |
| POST | `/express/s/{token}/siguiente` | `express.next.join` | Se reincorpora a la sala siguiente → `{redirect}` |
| GET | `/express/{event}/cartel` | `express.poster` | Cartel imprimible con QR del stand |

Las acciones POST responden JSON: `{ok: true, state}` o `{ok: false, code, message}`
(con status 4xx; `code` ∈ `not_started`, `too_early`, `unclear`, `budget`,
`suspect_locked`, `closed`, `not_gm`, `not_enough_players`, `already_accused`,
`room_full`, `join_closed`, `invalid`, `ai_unavailable`, `room_limit`). Los formularios de crear/unirse son POST
Inertia normales (errores de validación en `errors`).

Los formularios `Create` y `Join` envían: `nickname` (máx. 24), `contact_type`
(`none|email|whatsapp`), `contact`, `consent` (bool, requerido si hay contacto),
`adult` (bool, requerido si hay contacto), `website` (honeypot, debe ir vacío) y,
en `Create`, `case_slug`.

## Payload de estado (`GET /express/s/{token}/estado` y prop `state` de `Express/Room`)

```jsonc
{
  "rev": 17,                         // sube con cada escritura
  "server_now_ms": 1790000000000,
  "room": {
    "code": "K7M2Q",
    "status": "lobby|playing|accusing|reveal",
    "started_at_ms": null,           // epoch ms; null en lobby
    "elapsed_s": null,               // segundos desde el inicio (servidor)
    "join_url": "https://…/express/unirse/K7M2Q",
    "joinable": true,
    "min_players": 2, "max_players": 5,
    "case": { "slug": "…", "title": "…", "tagline": "…", "genre": "…",
              "scenario": "…", "incident": "…", "hook": "…", "hero": "/express-assets/x/hero.jpg|null" },
    "schedule": { "interrogation_opens_s": 20, "accusations_open_s": 120,
                  "window_end_s": 150, "hard_close_s": 180, "join_cutoff_s": 75 },
    "event": { "slug": "usb-cali-2026", "name": "…" }
  },
  "me": { "id": 12, "nickname": "Ana", "audience": "A", "is_gm": true,
          "can_start": false, "accused": false },
  "players": [ { "id": 12, "nickname": "Ana", "is_gm": true, "accused": false, "is_me": true } ],
  "evidence": [
    { "id": "E1", "title": "…", "type": "audio|document|screenshot|photo|cctv",
      "lines": ["10:58 | Alma P. entra a Sala B.", "11:03 | aviso por altavoz."],
      "audience": "A|B|global", "unlocked_at_s": 0,
      "mine": true,                  // privada de mi audiencia
      "shared": false, "shared_by": null,   // nickname
      "can_share": true,
      "image": "/express-assets/<slug>/e1.jpg|null", "audio": "/express-assets/<slug>/audio/e1.mp3|null" }
  ],
  "next_unlock": { "at_s": 20, "in_s": 12 } | null,
  "suspects": [
    { "id": "nora", "name": "Nora Leal", "role": "productora", "relation": "…",
      "portrait": "/express-assets/<slug>/portraits/nora.jpg|null",
      "claimed_by": null | { "id": 3, "nickname": "Leo" },
      "locked_for_me": false,
      "chips": [ { "topic_id": "reproduccion", "text": "¿Cómo recibieron el audio?" } ],
      "answered_topics": ["reproduccion"] }
  ],
  "interrogation": {
    "open": false, "budget_total": 3, "budget_used": 0, "budget_left": 3,
    "log": [ { "id": 1, "suspect_id": "nora", "nickname": "Leo", "question": "…",
               "answer": "…", "topic_id": "reproduccion" } ]
  },
  "accusation": {
    "open": false, "submitted": false, "closes_in_s": null,
    "options": null | { "who": [{"id":"a1b2c3d4","label":"Nora Leal"}], "how": [...], "why": [...] },
    "mine": null | { "who": "a1b2c3d4", "how": "…", "why": "…" },
    "waiting_for": ["Leo"]
  },
  "reveal": null | {
    "my": { "who": {"label":"…","correct":true,"points":40}, "how": {…}, "why": {…}, "total": 70 },
    "ranking": [ { "nickname": "Ana", "total": 100, "is_me": true } ],
    "solution": { "headline": "…", "truth": "…", "reconstruction": "…",
                  "correct": { "who": "…", "how": "…", "why": "…" }, "nodes": ["C1: …"] },
    "next_round": { "code": null | "K7M2Q", "join_url": null | "https://…" },
    "cta": { "label": "Ver los casos completos", "url": "https://misteriocode.com/jugar?promo_code=…&utm_…",
             "promo_code": "FERIA…|null", "promo_note": "…|null" }
  }
}
```

`accusation.open` es `true` solo mientras la ventana está abierta **y el jugador
aún no ha acusado**; tras acusar pasa a `false` y `accusation.submitted` a `true`.
La ventana dura hasta que todos acusan (se revela de inmediato) o hasta el
cierre duro de 180 s; `window_end_s` (150 s) solo la marca la interfaz.

`status`: `lobby` (sin iniciar) → `playing` (0–120 s) → `accusing` (≥120 s,
acusaciones abiertas) → `reveal` (o `expired` si nadie terminó la sala). `accusation.options` solo llega con
`accusation.open`; no hay puntajes ni aciertos hasta `reveal`. `evidence` solo
incluye lo ya desbloqueado y visible para el jugador (privadas de su audiencia
+ globales + compartidas).

## Datos y rendimiento

BD = verdad en escrituras; lectura y poll salen de `Ops::store()` (instantánea
por sala, reconstruida tras cada escritura). El endpoint de estado no abre
conexión a la BD si la instantánea existe: Hostinger limita a 500 conexiones por
hora para todo el sitio.

## Interrogatorios con IA

El sospechoso lo interpreta la IA del gateway `lawxora-ai-service` (proyecto
`mystery-case`, ruta nueva `POST /api/projects/mystery-case/interrogate-express`;
URL y llaves son las del juego pago, `immersion.ai.*`). Express **no usa créditos**.

- Se envía **solo la ficha de ESE sospechoso** (tono, lo que sabe/cree/ignora, la
  versión que sostiene, lo que oculta), lo que ya declaró (`response_exact`), las
  pistas que **toda la mesa** ya vio y los últimos turnos con él. **Nunca** la
  solución del caso ni la ficha de otro (`ExpressCase::persona()`; hay una prueba
  que lo fija).
- Cada pregunta gasta 1 de las 3 del equipo (3 por sospechoso). El lugar se toma y
  se confirma **antes** de llamar al gateway (fila sin `answer`), así dos celulares
  no gastan la misma pregunta y el bloqueo de la sala no se mantiene mientras se espera.
- Si la IA no responde (caído, lento > `EXPRESS_AI_TIMEOUT`, respuesta vacía o
  descartada por el filtro de fugas): una pregunta que coincide con un tema del autor
  se contesta con el texto del autor (`source: authored`, igual cuenta); una que no
  coincide con ninguno se devuelve **gratis** con `ai_unavailable`.
- `EXPRESS_AI_ENABLED=false` (o gateway sin configurar) = vuelve el modo sin IA: contesta
  el texto del autor, repetir un tema es gratis y «no entendí» tampoco gasta.
- El gateway descarta respuestas que «razonan en voz alta» (inglés, citas de la ficha,
  cortadas por tokens) y pide razonamiento mínimo a los modelos que lo aceptan.
  Requiere desplegar la versión nueva de `lawxora-ai-service`.

Variables: `EXPRESS_AI_ENABLED` (true), `EXPRESS_AI_TIMEOUT` (8 s). En el gateway:
`MYSTERY_CASE_EXPRESS_ATTEMPT_TIMEOUT_MS`, `MYSTERY_CASE_EXPRESS_DEADLINE_MS`,
`MYSTERY_CASE_EXPRESS_MINIMAL_THINKING_MODELS`.
