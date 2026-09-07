# Immersion - motor de juegos de misterio

Modulo aditivo y autocontenido: es el **motor de juego** (partidas, jugadores,
linea de tiempo, interrogatorios, acusaciones). No conoce ningun caso en
concreto. Para desactivarlo por completo: borra esta carpeta y quita la linea
`App\Modules\Immersion\ImmersionServiceProvider::class` de `config/app.php`.

## 1. Contenido del caso

Cada caso es una carpeta bajo `Cases/` con un `case.php` y su `content/`.
El caso actual es `Cases/steve-jacobs/` ("¿Que le sucedio a Steve Jacobs?").
Ver **[Cases/README.md](Cases/README.md)** para la estructura y para crear un
caso nuevo.

Un archivo `.md` que falte se renderiza vacio a proposito: ese mensaje sale en
blanco en vez de romper el resto de la linea de tiempo.

El caso por defecto de las partidas nuevas se elige con
`IMMERSION_DEFAULT_CASE` en `.env`.

## 2. Variables de entorno

En `.env` (ver `.env.example`):

```
IMMERSION_DEFAULT_CASE=steve-jacobs # caso por defecto de las partidas nuevas
IMMERSION_AI_SERVICE_URL=http://localhost:3001
IMMERSION_AI_PROJECT_ID=mystery-case
IMMERSION_AI_INTERNAL_API_KEY=...   # debe coincidir con MYSTERY_CASE_INTERNAL_API_KEY en el .env del gateway
```

El gateway (`lawxora-ai-service/ai-service`) necesita `MYSTERY_CASE_INTERNAL_API_KEY`
y `MYSTERY_CASE_GEMINI_API_KEY` en su propio `.env`. Si no estan configurados,
los correos de audio salen igual, sin el adjunto (no rompe la linea de tiempo).

## 3. Migrar y sembrar una partida de prueba

```
php artisan migrate
php artisan db:seed --class="App\Modules\Immersion\Database\Seeders\ImmersionDemoSeeder"
```

Esto siembra la cadena completa que una partida necesita: una cuenta de Game
Master (`gm@example.test` / `password`), su acceso al caso, y la partida con 6
jugadores de ejemplo y la linea de tiempo del manifiesto (para steve-jacobs:
8 entradas, minutos 10 a 75).

Siembra la cuenta a proposito: una partida sin dueno es inalcanzable por web
(la niega `GamePolicy`), asi que no serviria de nada. Es idempotente — correrlo
dos veces reutiliza la cuenta.

## 4. Probar el flujo completo en local

1. Recomendado para pruebas: pon `MAIL_MAILER=log` en `.env` para ver los
   correos en `storage/logs/laravel.log` en vez de intentar enviarlos de verdad.
2. Crea tu cuenta en `/registro` y dale acceso al caso:
   ```
   php artisan platform:sync-cases
   php artisan platform:grant-access tu@correo.com steve-jacobs
   ```
   Sin ese acceso el panel no te deja crear partidas (ver
   [Platform/README.md](../Platform/README.md)).
3. En el dashboard, abre la partida sembrada y pulsa "Iniciar caso".
4. Para no esperar los minutos reales, corre manualmente:
   ```
   php artisan immersion:process-timeline
   ```
   (en produccion esto lo dispara solo el scheduler de Laravel, registrado por
   el propio modulo, cada minuto - ver "Cron en produccion (Hostinger)" abajo).
5. Copia el link de bandeja de cualquier jugador desde el dashboard del GM
   (`/jugador/{access_token}`) y revisa sus correos.
6. Cuando llegue el evento de tipo `unlock` (minuto 75, o forzalo manualmente
   con el boton "Forzar siguiente evento"), el jugador puede enviar su
   acusacion en `/jugador/{access_token}/acusacion`.
7. El GM ve el resumen comparativo en "Ver acusaciones".

## 4.2 Modos de partida

Una partida se crea en uno de dos modos, y **ambos corren sobre el mismo
motor**: lo que cambia es quien puede ver y tocar que.

| | `gm_led` | `automatic` |
|---|---|---|
| Quien dirige | Una persona | El sistema |
| El dueno juega | No | **Si** (tiene su propio `Player`, `is_owner = true`) |
| Iniciar / pausar / cerrar | Si | Si |
| Forzar evento, habilitar mecanicas a mano | Si | **No** (`GamePolicy::direct`) |
| Ver interrogatorios y acusaciones | Si | **Solo al cerrar el caso** (`GamePolicy::viewSpoilers`) |
| Ver la linea de tiempo en la consola | Si | Solo el progreso (los titulos son spoilers) |

En modo automatico el interrogatorio se habilita solo: el evento marcado con
`cta_interrogation` en el manifiesto del caso es, por definicion, el momento en
que se apunta a los sospechosos. El autor del caso controla el momento moviendo
esa marca; no hace falta schema nuevo.

Cerrar el caso (`finish`) detiene el reloj, corta el envio de material y es lo
que revela los spoilers al dueno que estuvo jugando.

## 4.2.2 El final del caso

El GM elige el tipo de final **al crear la partida** (`ending_type`), porque los
finales avanzados reservan capacidad de IA. Hoy solo existe `classic`;
`epilogue` y `confession_audio` estan declarados pero deshabilitados.

**Final clasico:** cuando **todos** los jugadores han acusado, el sistema revela
la solucion solo. Cuentan todos los jugadores, incluido el jugador-dueno en modo
automatico. Si alguien no va a acusar, el GM puede forzar la revelacion desde el
panel de la partida.

Al revelar:
- Los jugadores ven la solucion en `/jugador/{token}/solucion`, y en la barra de
  navegacion "Acusacion" se sustituye por "Solucion".
- Las acusaciones se **bloquean** — con la respuesta en pantalla, poder editarla
  seria regalar el marcador.
- El veredicto de cada acusacion se **congela** en la fila (`was_correct`), para
  que editar el manifiesto despues no recalcule una partida ya terminada.

**Revelar no cierra la partida.** Son dos acciones distintas: el final premium
le entrega al GM un audio para reproducir en la mesa *antes* de cerrar el caso.

El interrogatorio tambien se elige al crear y ya no se puede encender a mitad de
partida, por la misma razon: encenderlo despues seria consumo de IA no
reservado.

## 4.2.1 Cupo de partidas

El cupo es **por caso**, no por cuenta: `IMMERSION_MAX_GAMES_PER_CASE` partidas
de cada caso (6 por defecto). Quien tenga 4 casos puede llegar a 24 partidas
—6 de cada uno— y llenarse en uno no afecta a los demas.

**Cuentan todas, en cualquier estado**: una partida terminada sigue ocupando
cupo. La unica forma de liberar uno es **eliminar una partida de ese mismo
caso**; borrar una de otro caso no sirve.

El limite se aplica en el servidor dentro de una transaccion con lock sobre la
fila del dueno: contar y luego insertar es un read-modify-write, y dos
peticiones simultaneas verian ambas cinco partidas y crearian una sexta cada
una.

`Support/GameQuota` es la unica fuente de verdad. `forCases()` resuelve toda la
biblioteca en una sola query agrupada, para que el formulario de creacion y la
biblioteca no hagan una consulta por caso.

Eliminar una partida borra en cascada sus jugadores, eventos, interrogatorios y
acusaciones, y ademas limpia a mano los `.wav` en `storage/app/audio` — el audio
vive en disco, asi que ninguna cascada de base de datos lo alcanza.

Subir el limite es seguro. Bajarlo **no borra nada**: las cuentas por encima del
nuevo tope simplemente no pueden crear hasta volver por debajo.

## 4.3 Cola

`QUEUE_CONNECTION=database` (tabla `jobs`). El trabajo lento — generar el
audio con TTS y enviar los correos — corre en la cola, no dentro del cron ni
de la request del navegador.

No hace falta un daemon: el propio scheduler lanza un worker corto cada
minuto (`queue:work --stop-when-empty --max-time=50`), que vacia la cola y
termina antes del siguiente tick. Ese worker **solo se programa si la cola no
es `sync`**.

> El cron del scheduler (ver abajo) es ahora todavia mas importante: sin el no
> corre ni la linea de tiempo ni el worker, y los correos no salen.

Con `QUEUE_CONNECTION=sync` todo vuelve a ejecutarse inline. Sigue
funcionando, pero el cron de cada minuto puede tardar mas de un minuto.

## 4.1 Cron en produccion (Hostinger)

Sin esto, los correos SOLO salen al forzar el evento manualmente desde el
dashboard del GM; nunca salen solos al pasar los minutos. El pipeline de
deploy (`.github/workflows/deploy.yml`) no puede configurar esto por SSH
porque el comando `crontab` no esta disponible para el usuario de este
hosting compartido, asi que hay que agregarlo **una sola vez** a mano:

1. Entra a hPanel (Hostinger) > Avanzado > Cron Jobs.
2. Crea un cron job nuevo con frecuencia "Cada minuto" (`* * * * *`).
3. Comando:
   ```
   cd /home/u206029413/domains/cumplemiamor.cramultimedia.com/public_html && /opt/alt/php82/usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```
   (ajusta la ruta del binario de PHP si hPanel te ofrece un selector de
   version en vez de la ruta completa; usar PHP 8.2).

## 4.4 IA: se puede apagar

Las dos capacidades de IA estan detras de contratos
(`Ai/Contracts/InterrogationProvider`, `Ai/Contracts/SpeechProvider`) con dos
implementaciones reales cada una: el gateway y una **null**.

```
IMMERSION_AI_INTERROGATION=false   # los sospechosos responden en personaje, sin agregar nada
IMMERSION_AI_SPEECH=false          # los correos de voz salen sin grabacion
```

Si el gateway no esta configurado (sin URL, sin key, o con la key de ejemplo
`CHANGE_ME...`), se usan los proveedores null aunque las banderas esten
encendidas. El caso sigue siendo jugable: los sobres, la evidencia y los
testimonios escritos no dependen de ningun modelo.

## 5. Audio (TTS)

El audio se genera llamando al proyecto `mystery-case` del gateway
`lawxora-ai-service` (ver ese repo, `src/projects/mystery-case/`), que a su
vez llama a Gemini (`gemini-2.5-flash-preview-tts`) y devuelve un `.wav`
(PCM 24kHz/16-bit envuelto en un header WAV). Se cachea en
`storage/app/audio/{event_id}.wav` y no se regenera si ya existe.

## 6. Mecanica 7 - Interrogatorio por IA

Cada jugador puede interrogar a las personas que declare el manifiesto del
caso (para steve-jacobs: 9 en `Cases/steve-jacobs/content/suspects/`, 8
sospechosos + el testigo Jeremy Burt). El presupuesto de preguntas por persona
lo fija el caso (`limits.interrogation_questions`, 5 en steve-jacobs) y queda
grabado en cada sesion al crearse, asi que editarlo no altera partidas en
curso. El modelo (`gemini-3.6-flash` por defecto, ver
`MYSTERY_CASE_CHAT_MODEL` en el `.env` del gateway) solo recibe el
testimonio verbatim de ESA persona (nunca la solucion del caso ni el
testimonio de otros), y tiene instrucciones estrictas de no inventar
hechos y de no romper personaje ante intentos de manipulacion. Al llegar
a la quinta pregunta la sesion se cierra sola y se revela debajo del chat
la declaracion oficial completa de esa persona.

- El GM habilita/deshabilita esta mecanica por partida con el boton
  "Habilitar interrogatorio (Mec. 7)" en el dashboard de la partida
  (aparece "Interrogatorio" en la bandeja del jugador solo si esta
  habilitada).
- El GM puede revisar todas las conversaciones completas en
  "Ver interrogatorios" dentro de esa misma partida.
- Usa el mismo Gemini API key que el TTS (`MYSTERY_CASE_GEMINI_API_KEY`
  en el `.env` del gateway) — no hace falta configurar nada adicional.
