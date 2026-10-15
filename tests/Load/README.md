# Prueba de carga

Responde **una** pregunta: ¿cuántos jugadores a la vez aguanta este hosting antes de que algo ceda (errores, páginas lentas o el tope de 500 conexiones nuevas por hora a MySQL)?

Se corre **solo contra el sitio de test**. Nunca contra producción: crea partidas falsas y puede tumbar el sitio a propósito.

## Qué necesitas

- [k6](https://k6.io/docs/get-started/installation/) en tu computador (un solo binario).
- Acceso al `.env` de test (hPanel → Administrador de archivos) y a una terminal con acceso a la base de test, o a phpMyAdmin.
- Una hora tranquila: la prueba usa el mismo servidor que el sitio de test.

## 1. Preparar el sitio de test

En el `.env` de **test**:

```
IMMERSION_AI_INTERROGATION=false   # las preguntas usan la respuesta evasiva gratuita: no gasta IA
IMMERSION_SNAPSHOTS=true           # la mejora que se quiere medir (apágala para comparar)
DB_PERSISTENT=false                # se prueba por separado (ver "Qué comparar")
PLATFORM_DB_CONNECTIONS_PER_HOUR=500
```

Crea las partidas y jugadores de prueba (en la terminal del servidor de test, o en tu máquina apuntando a la base de test):

```
php artisan immersion:load-seed --games=50 --players=4
```

Escribe los tokens en `storage/app/load-tokens.json`. Descarga ese archivo a tu computador (`tests/Load/` lo lee de `../../storage/app/load-tokens.json`, o pásalo con `-e TOKENS=ruta`).

## 2. Correrla, por escalones

```
k6 run -e BASE_URL=https://test.misteriocode.com -e LEVEL=25  tests/Load/players.js
k6 run -e BASE_URL=https://test.misteriocode.com -e LEVEL=50  tests/Load/players.js
k6 run -e BASE_URL=https://test.misteriocode.com -e LEVEL=100 tests/Load/players.js
k6 run -e BASE_URL=https://test.misteriocode.com -e LEVEL=200 tests/Load/players.js
```

Sube de escalón solo si el anterior pasó. Cada corrida dura unos 11 minutos (2 de subida, 8 sostenidos, 30 s de bajada); cambia `RAMP` y `HOLD` si hace falta. Con 200 jugadores necesitas al menos 50 partidas de 4 (para que cada token sea distinto).

## 3. Qué mirar

**En la salida de k6** — los umbrales marcan si "aguantó":

| Métrica | Pasa si |
|---|---|
| `http_req_failed` | < 1 % |
| `page_ms` p(95) | < 1,5 s |
| `state_poll_ms` p(95) | < 0,5 s |
| `ask_ok` | > 97 % (cuentan como bien 200, 403, 422 y el 429 "ocupado" del limitador de IA) |
| `http_503` | 0 (un 503 es el sitio sin conexiones a la base) |
| `ask_busy_429` | informativo: preguntas que el limitador de IA devolvió sin gastar |
| `ask_throttled_429` | 0 (un "Too Many Attempts" aquí es un límite de ritmo mal repartido, no el limitador de IA) |

**En el servidor**, antes y después de cada corrida:

1. **Conexiones nuevas reales** (la cifra que importa): en phpMyAdmin / terminal de la base de test,
   `SHOW GLOBAL STATUS LIKE 'Connections';` — anota el valor antes y después; la diferencia dividida entre los minutos de la corrida × 60 es la tasa por hora. (Incluye a otros usuarios del mismo servidor MySQL: es una cota alta.)
2. **`/admin` del sitio de test**: "Conexiones DB, esta hora" y la tabla *Quién abrió las conexiones* (qué rutas gastaron).
3. **hPanel → Uso de recursos**: CPU, RAM, procesos y E/S durante la corrida.
4. `storage/logs/laravel.log`: `db_connection_budget_high`, `db_connection_limit_reached`, `db_persistent_reuse_low`, `immersion_slow_job`.

## 4. Qué comparar

Repite el **mismo nivel** cambiando una sola cosa por vez y apunta cuánto aporta cada una:

| Corrida | `.env` de test |
|---|---|
| A. Base | `IMMERSION_SNAPSHOTS=false` `DB_PERSISTENT=false` |
| B. + instantáneas | `IMMERSION_SNAPSHOTS=true` `DB_PERSISTENT=false` |
| C. + conexión persistente | `IMMERSION_SNAPSHOTS=true` `DB_PERSISTENT=true` (mira "reutilización" en `/admin`) |

Si en C la reutilización queda por debajo del 50 %, LiteSpeed no conserva los procesos PHP y `DB_PERSISTENT` no sirve en este plan.

## 5. Limpiar

```
php artisan immersion:load-seed --purge
```

Borra las partidas "LOAD-TEST n" (y el archivo de tokens). Vuelve a poner el `.env` de test como estaba.

## 6. Anotar el resultado

Copia la tabla en `docs/escalabilidad-y-limites.md` (sección "Techo medido"): jugadores, partidas, conexiones por hora, p95, errores y qué configuración. Ese número, y no una estimación, es el que dice cuándo migrar.

## Qué simula

Cada jugador virtual (VU) es un jugador con su enlace: abre la bandeja; cada ~12 s hace la consulta liviana de estado (`/jugador/{token}/estado`, la que hace la página sola); cada 20–60 s hace algo —ir a la bandeja, al tablero de sospechosos, al chat de uno, a la acusación— y una de cada cinco veces hace una pregunta. Un sospechoso solo puede interrogarlo el primer jugador que le pregunta, por eso cada VU tiene su sospechoso: lo demás devuelve 403, que es una regla del juego y no un fallo.

No simula: el envío de sobres por la línea de tiempo (lo hace el cron), el pago, ni el registro de cuentas.
