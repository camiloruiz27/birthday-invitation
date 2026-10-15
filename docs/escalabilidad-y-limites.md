# Escalabilidad y límites del hosting — manual del administrador

Para quien opera MisterioCode solo. Está escrito para leerse con el sistema caído y poco tiempo: primero qué pasa y qué hacer; después, cómo funciona y cómo crecer.

Última revisión: 2026-10-14. Las cifras marcadas **(estimado)** salen de leer el código, no de medir; las marcadas **(medido)** hay que rellenarlas con la [prueba de carga](../tests/Load/README.md).

---

## 1. Lo esencial en diez líneas

1. El hosting limita al usuario de MySQL a **500 conexiones nuevas por hora**. Pasado eso, **toda** consulta falla hasta que el contador se reinicia (error `1226`, `max_connections_per_hour`). El sitio responde la página "Mucha demanda en este momento" (503).
2. Una **conexión** es una petición PHP que toca la base o un proceso del cron que la toca. No cuentan las consultas, cuentan las conexiones.
3. Lo que más conexiones gasta: el **sondeo** de pantallas abiertas, los **clics** del jugador, las **preguntas** a sospechosos y el **cron**. Todo eso ya está optimizado (ver §4).
4. Lo que queda y no se puede evitar sin cambiar de servidor: **cada pregunta a un sospechoso es una conexión** (escribe datos).
5. Para saber **quién gastó las conexiones**: `/admin` → "Quién abrió las conexiones", y el log (`db_connection_limit_reached` trae las 5 rutas que más gastaron).
6. Interruptores para apagar/encender cosas **sin desplegar**: están en el `.env` de producción (hPanel → Administrador de archivos). Aplican al instante (no se usa `config:cache`). Lista en §3.
7. La palanca que cubre las preguntas es la **conexión persistente** (`DB_PERSISTENT=true`). Solo funciona si LiteSpeed reutiliza los procesos PHP; `/admin` lo mide.
8. Si hay que crecer más allá de lo que el plan da, la salida real es un **VPS** (§8): sin tope por hora, con cola real y Redis.
9. Antes de decidir nada, mide: [`tests/Load/README.md`](../tests/Load/README.md).
10. Escribe a soporte de Hostinger para que suban el tope (texto en §8.1): cuesta nada.

---

## 2. Los límites de tu plan

Tomados de tu hPanel y de la documentación de Hostinger. Los límites son **fijos por plan**: solo un plan superior los sube.

| Recurso | Valor | Qué pasa al pasarlo |
|---|---|---|
| Conexiones nuevas por hora a MySQL (por usuario) | **500** (lo dice el propio error; la tabla pública no lo lista) | Error 1226: todo falla hasta que el contador se reinicia |
| Conexiones MySQL abiertas a la vez, por usuario | 75 | Errores de "demasiadas conexiones" |
| PHP workers | 60 | Las peticiones se encolan; si se agotan, 503/508 |
| Procesos máximos | 120 | Errores de recursos |
| CPU / RAM | 2 núcleos / 3 GB | Páginas lentas, procesos terminados |
| Inodos (archivos) | 600.000 | **Todo deja de poder escribir** (sesiones, caché, logs) |
| Correo desde buzón | 100/día (gratis), 1.000 (Business Starter), 3.000 (Standard/Premium), por buzón, ventana móvil de 24 h | Los correos fallan |
| Tiempo máximo de PHP | 360 s | Se corta la petición |

El contador por hora de MySQL lo reinicia el servidor de MySQL, no tú. Tras un 1226, **espera como máximo una hora**; si hay un problema real, escribe a soporte.

---

## 3. Interruptores de operación (`.env` de producción)

Se editan desde hPanel → Archivos → Administrador de archivos → `public_html/.env`. Aplican en la siguiente petición. Para volver atrás, restaura el valor.

| Variable | Por defecto | Qué hace | Cuándo tocarla |
|---|---|---|---|
| `DB_PERSISTENT` | `false` | Cada proceso PHP conserva su conexión a MySQL entre peticiones | Tras probarlo en test (§6.2) |
| `DB_REUSE_PROBE_EVERY` | `20` | Con persistente, 1 de cada N peticiones mide si se reutiliza | Bájalo a 1–5 mientras pruebas |
| `IMMERSION_SNAPSHOTS` | `false` | Las páginas del jugador se sirven de caché y no de la base (§4.3) | Tras probarlo en test (§6.1) |
| `IMMERSION_TICK_IDLE_SKIP` | `true` | El cron no consulta la base si no hay nada que hacer | Solo `false` para depurar |
| `IMMERSION_TICK_NEXT_RUN_FILE` | `storage/framework/next-run` | Archivo donde el cron lee cuándo despertar PHP | No tocar |
| `IMMERSION_MAIL_TIMELINE` | `unlock` | Qué correos de sobres salen: `unlock`, `all` u `off` | Si quieres volver a mandar todos: `all` |
| `IMMERSION_MAIL_EPILOGUE` | `false` | Si el epílogo también sale por correo | |
| `IMMERSION_MAIL_DAILY_LIMIT` | `100` | Cupo diario del buzón; el sistema espera al llegar | **Ponlo según tu buzón** |
| `IMMERSION_MAIL_PER_MINUTE` | `10` | Ritmo de la cola de correo (solo con `all`) | |
| `IMMERSION_AI_MAX_CONCURRENT` | `20` | Preguntas a la IA esperando a la vez (cada una ocupa un PHP worker) | Sube si ves 429 y la CPU sobra |
| `PLATFORM_DB_CONNECTIONS_PER_HOUR` | `500` | Solo para el aviso al 80 % en el log; no cambia el tope real | Si Hostinger lo sube |
| `OPS_CACHE_STORE` | `ops` | Caché que sobrevive a los despliegues | No tocar |
| `LOG_CHANNEL` | `stack` | Recomendado `daily`: rota el log a 14 días | Ponlo ya |

---

## 4. Cómo gasta (y cómo ahorra) conexiones el sistema

### 4.1 Modelo de capacidad (estimado)

```
conexiones por hora  =  cron  +  peticiones web que tocan la base  +  visitas anónimas no cacheadas
```

| Fuente | Antes de optimizar | Ahora |
|---|---|---|
| Cron, partida en curso | ~73 | ~10–15 (un proceso solo cuando toca un evento) |
| Cron, nadie jugando | ~25 | ~4 |
| Recarga de seguridad de cada pestaña | 30/h (60 en Personas) | 6/h, y de caché con instantáneas |
| Recargas por cambio de cada pestaña | hasta 180/h | tope de una cada 30 s; de caché con instantáneas |
| Clics de navegación del jugador | 60–180/h | ~0 con instantáneas |
| Audio de sobres (rangos) | 50–150 por mesa | 0 (URL estática) |
| Preguntas a sospechosos | ~45–70 por partida | igual: **escriben** |

Una partida de 4 + Game Master gastaba del orden de **900–1.300 conexiones/h** (estimado). Con todo activado queda en **~70–90/h**, casi todas preguntas, más el cron compartido.

Ejemplos con el tope de 500:

| Escenario | Conexiones/h | ¿Cabe? |
|---|---|---|
| 1 partida (4 jugadores) | ~80 | Sí, con holgura |
| 6 partidas a la vez (24 jugadores) | ~500 | En el límite |
| 50 partidas a la vez (200 jugadores) | ~2.500 (solo preguntas) | **No** sin conexión persistente o sin tope |

### 4.2 Qué hace cada pieza

- **Sondeo sin base de datos** (`/jugador/{token}/estado`, `/partidas/{id}/estado`): leen un sello de la caché. La pantalla solo recarga si un sello que le interesa cambió (canales: `timeline`, `interrogation`, `accusation`). Un solo sondeador por pantalla.
- **Cron inteligente** (`cron-scheduler.sh`): lee `storage/framework/next-run` y **no arranca PHP** si no toca. Cada 5 minutos corre `schedule:run` completo. La hora de cada evento se conoce (inicio + pausas + minuto): `TickPlanner`. Los eventos corren dentro del propio tick, sin cola.
- **Correos**: por defecto solo el enlace de acceso (clic del Game Master) y el sobre "Fase de acusaciones habilitada". Salen en el mismo proceso, sin cola. Con `IMMERSION_MAIL_TIMELINE=all` vuelven los de cada sobre, por cola y con cupo diario.
- **Almacén `ops`** (`storage/app/ops-cache`): `php artisan optimize:clear` (cada despliegue) **no lo borra**. Ahí viven los sellos, los contadores de conexiones y de correo, y el plan del tick.

### 4.3 Instantáneas de partida (`IMMERSION_SNAPSHOTS`)

Lo que todos los jugadores de una mesa leen (sobres enviados, quién reclamó qué sospechoso, qué abrió el caso) se guarda en caché y lo reconstruye **quien escribe**: la petición que hizo la pregunta, o el tick que envió el evento, ya tiene conexión abierta. Los lectores casi nunca tocan la base. Solo lecturas (GET); las escrituras siempre van a la base.

Seguridad de la caché: la clave de cada jugador lleva su token hasheado; los sobres privados se filtran por jugador en PHP; el test `GameSnapshotTest` compara cada página con la que da la base, para cada jugador y tras cada tipo de cambio.

Una instantánea nunca se sirve pasados 5 minutos (`GameSnapshot::TTL_SECONDS`), aunque nadie haya movido su sello: así un error de este mecanismo vive como mucho 5 minutos.

---

## 5. Si el sistema se cae (runbook)

### 5.1 Síntomas

- Los jugadores ven "Mucha demanda en este momento" (503) o el sitio da errores.
- `storage/logs/laravel.log` tiene `db_connection_limit_reached` (una línea cada 10 minutos, con las 5 rutas que más gastaron en `heaviest`).
- `cron-scheduler.log` muestra comandos con `FAIL`.

### 5.2 Qué hacer, en orden

1. **Confirma que es el límite**: el log dice `1226` / `max_connections_per_hour`. Si dice otra cosa (`No space left`, `Maximum execution time`, `Class not found`) es otro problema: §5.4.
2. **Mira quién gastó**: `/admin` → "Quién abrió las conexiones" (esta hora y la anterior), o `heaviest` en el log. La ruta que más gastó es el sospechoso.
3. **Espera**: el contador se reinicia en menos de una hora. Mientras, el juego no pierde estado: los eventos vencidos se envían en cuanto el cron vuelve a tener conexión.
4. **Si se repite**: apaga lo que más gaste (§5.3) y escribe a soporte (§8.1).
5. **Después**: apunta qué ocurrió y cuántos jugadores había; el dato alimenta §7.

### 5.3 Qué apagar primero

| Si la ruta que gasta es… | Haz |
|---|---|
| `immersion.player.*` (páginas del jugador) | Activa `IMMERSION_SNAPSHOTS=true`; si ya está, activa `DB_PERSISTENT=true` |
| `cmd:immersion:tick` | Comprueba `IMMERSION_TICK_IDLE_SKIP=true` y que `storage/framework/next-run` se actualiza |
| `immersion.player.interrogation.ask` | Es el gasto inevitable: `DB_PERSISTENT=true`, o limita con `IMMERSION_AI_MAX_CONCURRENT` |
| `ads.*`, `home`, `cases.*`, `register`, `login` | Es tráfico de anuncios/visitas: las páginas públicas ya se cachean 1 min; revisa la campaña |
| `payments.*` | Revisa Bold y la página "Confirmando" |

### 5.4 Otros fallos que parecen este

| En el log | Es |
|---|---|
| `No space left` / `Disk quota exceeded` | Inodos o disco: `php artisan ops:prune-cache` (borra caché y sesiones vencidas); revisa hPanel → Uso de disco |
| `Maximum execution time` (en `error_log`) | Una petición tardó demasiado (IA lenta); mira `immersion_slow_job` |
| `Class ... not found` / `Unknown mystery case` | Despliegue incompleto: vuelve a correr el workflow |
| 508 / "Resource Limit Reached" | Se agotaron PHP workers/CPU, no la base: mira hPanel → Uso de recursos |

---

## 6. Activar las mejoras, paso a paso

Siempre **primero en test, desde casa**, con alguien jugando de verdad o con la prueba de carga.

### 6.1 Instantáneas

1. En el `.env` de test: `IMMERSION_SNAPSHOTS=true`.
2. Juega una mesa: bandeja, sospechosos, preguntas, acusación. Compara con producción: tiene que verse **igual**.
3. En `/admin` de test, "Conexiones DB, esta hora": debe subir mucho menos que sin instantáneas.
4. Producción, en hora tranquila: `IMMERSION_SNAPSHOTS=true`. Vigila el log.
5. **Reversión**: `IMMERSION_SNAPSHOTS=false` (la caché queda sin usar).

### 6.2 Conexión persistente

1. En el `.env` de test: `DB_PERSISTENT=true` y `DB_REUSE_PROBE_EVERY=1`.
2. Navega unas decenas de páginas y mira `/admin` de test → "Conexiones persistentes: X % reutilizó".
3. **≥ 80 %**: funciona. **< 50 %**: LiteSpeed no conserva los procesos; no sirve en este plan (apágala).
4. Mide con la prueba de carga comparando conexiones/hora con y sin ella.
5. Producción: `DB_PERSISTENT=true`, `DB_REUSE_PROBE_EVERY=20`. Vigila `/admin`.
6. Cuidado: 60 workers + procesos del cron ≈ 62 < 75 conexiones simultáneas. Si MySQL empieza a rechazar por "demasiadas conexiones", apágala.
7. **Reversión**: `DB_PERSISTENT=false`. Un middleware (`ResetDatabaseConnection`) ya deshace transacciones colgadas, que es el riesgo conocido de las conexiones persistentes.

### 6.3 Despliegues

El workflow `deploy.yml` corre `git reset`, `composer install`, `migrate`, `platform:sync-cases`, `optimize:clear`, `queue:restart`, `view:cache`. **No** corre `config:cache` a propósito: así los cambios en el `.env` aplican al instante (no hay SSH desde todas partes). El almacén `ops` sobrevive, así que un despliegue ya no hace recargar a todas las pestañas ni reinicia los contadores.

---

## 7. Techo medido

> Rellena esto con la prueba de carga ([`tests/Load/README.md`](../tests/Load/README.md)). Hasta entonces, no hay cifra: solo las estimaciones de §4.1.

| Fecha | Configuración (`SNAPSHOTS` / `PERSISTENT`) | Jugadores | Partidas | Conexiones nuevas/h | p95 página | Errores | Notas |
|---|---|---|---|---|---|---|---|
| | | | | | | | |

---

## 8. Escalera de salidas (de menor a mayor esfuerzo)

### 8.1 Pedir a Hostinger que suba el tope (gratis, hoy)

Abre un ticket (chat de hPanel). Texto sugerido:

> Hola. Mi sitio misteriocode.com (usuario MySQL `u206029413_viaje_ctg`) recibe el error `1226: User has exceeded the 'max_connections_per_hour' resource (current value: 500)`. Es una plataforma de juego multijugador: las partidas generan tráfico real y legítimo, y el tope de 500 conexiones nuevas por hora se agota con unas pocas partidas simultáneas.
> Ya redujimos el consumo en el código (caché, sondeo sin base de datos, cron predictivo) y estamos probando conexiones persistentes, pero necesito margen.
> ¿Pueden **subir `max_connections_per_hour`** de ese usuario (por ejemplo a 5.000), o indicarme qué plan lo incluye? También quiero confirmar si en mi plan los PHP workers (60) y las 75 conexiones simultáneas son fijos.
> Gracias.

El resultado es incierto: a veces suben el tope, a veces solo reinician el contador. Guarda su respuesta en este manual.

### 8.2 Plan superior de Hostinger

Pregunta **por escrito** si el tope por hora cambia con un plan Cloud superior: no está en la tabla pública de límites. No cambies de plan sin esa respuesta.

### 8.3 VPS: la salida real para 200+ jugadores

Un VPS no tiene el tope por hora (la base es tuya), permite un **worker de cola permanente**, **Redis** y un **cron propio**. Hostinger ofrece VPS KVM (de 1 a 8 vCPU, 4–32 GB de RAM) y datacenters en EE. UU. y Brasil, entre otros; precios del orden de 5–36 USD/mes según plan **(verifica en su sitio: cambian y hay precio promocional que sube al renovar)**. Un KVM 2 (2 vCPU / 8 GB) es un punto de partida razonable para empezar.

Qué se gana:

- Sin `max_connections_per_hour`; `DB_PERSISTENT` deja de hacer falta.
- Cola con **worker permanente** (Supervisor): las preguntas a la IA pueden ser asíncronas y los correos/TTS no dependen del cron de 1 minuto.
- Redis para caché, sesiones y colas: más rápido y sin archivos que llenen inodos.
- `pm.max_children` de PHP-FPM a tu medida.

Pasos (Ubuntu LTS; el mismo PHP 8.2 que hoy):

1. **Servidor**: crea el VPS (región São Paulo o EE. UU. este para Latinoamérica); usuario no-root con `sudo`, llave SSH, `ufw allow OpenSSH,80,443`, actualizaciones automáticas.
2. **Pila**: `nginx`, `php8.2-fpm` (+ `mysql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`, `gd`, `redis`), `mariadb-server` o `mysql-server`, `redis-server`, `supervisor`, `certbot`, `git`, `composer`, Node 20 (para compilar; o compila en GitHub Actions como hoy).
3. **Base**: crea usuario y base; `max_connections=300` en MariaDB. Importa el volcado de producción (`mysqldump --single-transaction`) en una **ventana de mantenimiento corta**.
4. **Código**: clona en `/var/www/misteriocode`; `.env` de producción (copia el actual, cambia `DB_HOST=127.0.0.1`, `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `DB_PERSISTENT=false`); `composer install --no-dev -o`; `php artisan migrate --force`; `php artisan storage:link`; permisos de `storage` y `bootstrap/cache`.
5. **Nginx**: raíz `public/`, `try_files $uri /index.php?$query_string`, `fastcgi_pass` al socket de PHP-FPM; sirve `/build/` e `/immersion/` con `expires 30d` (estáticos, sin PHP).
6. **Cron**: `* * * * * cd /var/www/misteriocode && php artisan schedule:run >> /dev/null 2>&1` (el script `cron-scheduler.sh` ya no hace falta; puedes quedarte con él).
7. **Cola**: Supervisor con `php artisan queue:work redis --queue=emails,default,slow --tries=2 --max-time=3600`; reinicia con `php artisan queue:restart` en cada despliegue.
8. **HTTPS**: `certbot --nginx`.
9. **Despliegue**: duplica `.github/workflows/deploy.yml` apuntando al VPS (ya usas SSH; cambia host/puerto/ruta y quita el copiado de `public/build` si compilas en el servidor).
10. **Cambio**: baja el TTL del DNS a 300 s un día antes; en la ventana de mantenimiento pon el sitio en modo mantenimiento, vuelca la base, restaura, prueba con `/etc/hosts`, cambia el DNS. Mantén Hostinger compartido encendido unos días como respaldo.
11. **Copias de seguridad**: `mysqldump` diario a almacenamiento externo + snapshots del VPS.

### 8.4 VPS administrado con panel (Forge / Ploi / similares)

Hacen los pasos 2–8 por ti (Nginx, PHP, Supervisor, certificados, despliegue desde GitHub) por unos 10–20 USD/mes más el VPS **(verifica)**. Si no quieres administrar un servidor, es la opción más cómoda.

### 8.5 PaaS + MySQL gestionado

Laravel Cloud, Render, Fly.io o DigitalOcean App Platform con base de datos gestionada. Escalan solos y no hay servidor que cuidar, pero: cuestan más, el disco de la aplicación **no es persistente** (hay que mover audios generados, imágenes y sesiones a almacenamiento de objetos y Redis) y la base gestionada también tiene límites de conexiones (se resuelven con un pooler). Úsalo si prefieres pagar para no administrar.

### 8.6 Cloudflare delante (sirve con cualquier opción)

- CDN para `public/immersion` (~600 MB de imágenes y audio) y `public/build`: los jugadores de **otros países** reciben los archivos del nodo más cercano, y el servidor deja de servirlos.
- Elige **una sola región** para la aplicación (la más cercana a la mayoría) y deja que el CDN haga el resto; multirregión con base de datos distribuida no se justifica a esta escala.
- Regla de caché: `/immersion/*` y `/build/*` 30 días; las páginas HTML **no** (llevan token de jugador).

### 8.7 Para 200+ jugadores en paralelo

Con VPS o PaaS:

1. **Preguntas asíncronas**: hoy la petición espera a la IA con un PHP worker ocupado hasta 35 s. Con worker permanente, `ask` encola un trabajo y responde al instante; el chat se actualiza con el sondeo (el canal `interrogation` ya existe) o con WebSockets.
2. **WebSockets** (Laravel Reverb o Pusher) en lugar de sondeo: la pantalla recibe el cambio al instante, sin peticiones periódicas.
3. **Réplica de lectura** o base gestionada: solo si el volumen lo pide; las instantáneas ya evitan casi toda la lectura.
4. **Separar el servicio de IA** en su propio servidor si sus tiempos de respuesta empiezan a limitar.

Qué **no** conviene en esta etapa: una base SQLite por partida (sin tope de conexiones, pero rompe créditos, cuentas y compras, y complica copias de seguridad) ni bases por país (la latencia de un chat de texto no lo justifica).

### 8.8 Plan B si LiteSpeed no reutiliza conexiones y no hay migración aún

**Volcado diferido de preguntas** (no implementado; solo diseño): `ask` escribe la pregunta y la respuesta en un archivo por partida (con `flock`) y el cron las pasa a la base en bloque cada minuto. Quita la conexión de cada pregunta, pero toca el cobro de créditos y la exclusividad del sospechoso (hoy se garantizan con una actualización atómica en MySQL). Solo vale la pena si tras medir `DB_PERSISTENT` no sirve **y** migrar a VPS no es posible.

---

## 9. Cuándo migrar (disparadores)

Migra a un VPS cuando se cumpla **cualquiera** de estos durante una semana normal:

- Conexiones por hora **sostenidas por encima del 60 %** del tope (300) en horas de juego.
- Más de **40–50 jugadores simultáneos** de forma habitual.
- **p95 de página > 1 s** en la prueba de carga o en hPanel.
- **429 frecuentes** del limitador de IA (`ask_busy_429`).
- `/admin` muestra una **reutilización < 50 %** con `DB_PERSISTENT` y el tope sigue cayéndose.
- Un solo incidente 1226 en una hora con una campaña de anuncios activa.

---

## 10. Checklist

**Antes de desplegar**: `vendor/bin/phpunit` verde (salvo los 5 fallos conocidos de imágenes/audios por generar); `npm run build`; revisar que el `.env` de producción tenga `LOG_CHANNEL=daily` y `IMMERSION_MAIL_DAILY_LIMIT` según el buzón.

**Después de desplegar**: abrir `/admin` (conexiones y, si está activa, reutilización); `storage/logs/laravel.log` sin `db_connection_*`; `storage/logs/cron-scheduler.log` con `exit code 0`; una partida de prueba (bandeja, un sobre, una pregunta).

**Reversión rápida** (desde hPanel, sin desplegar): `DB_PERSISTENT=false`, `IMMERSION_SNAPSHOTS=false`, `IMMERSION_TICK_IDLE_SKIP=false` (el cron vuelve a correr el tick cada minuto). Para volver al código anterior: revierte el merge en `main` y deja que el workflow despliegue.
