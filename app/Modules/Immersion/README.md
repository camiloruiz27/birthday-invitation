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

El proyecto `mystery-case` del gateway expone tres endpoints:

| Endpoint | Lo usa |
|---|---|
| `POST /tts` | Audios de la linea de tiempo **y** la confesion del final premium |
| `POST /interrogate` | Interrogatorios |
| `POST /epilogue` | Epilogo personalizado (dos prompts, uno por veredicto) |

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
finales avanzados reservan capacidad de IA. Hay tres, y **los dos avanzados se
suman al clasico**, no lo reemplazan:

| Final | Que agrega | Creditos |
|---|---|---|
| `classic` | Nada: la solucion escrita y el marcador | 0 |
| `epilogue` | Un correo por jugador, de la persona que acuso | 10 |
| `confession_audio` | Una grabacion del culpable, solo para el GM | 15 |

**Un final es una capacidad del CONTENIDO, no del build.** El epilogo necesita
una `exoneration` escrita para cada inocente; el audio necesita
`confession_script`. `CaseDefinition::supportedEndings()` es la fuente de
verdad, y la valida tanto el formulario como el servidor: un caso sin guion no
puede vender el final premium.

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

### Epilogo personalizado (`epilogue`)

Al revelar se encola **un job por acusacion** — no por jugador: quien nunca
acuso no tiene a nadie a quien responderle. Cada uno pide al gateway el mensaje
de ESE sospechoso para ESE jugador, lo guarda en la fila de la acusacion
(`epilogue_body`) y lo envia por correo. Guardarlo ademas de enviarlo es lo que
permite releerlo en `/jugador/{token}/solucion`: un correo se pierde facil y en
la mesa no se puede volver a abrir.

Lo que se le manda al gateway **cambia segun el veredicto**, y esa asimetria es
deliberada:

- Acerto → se envian `method`, `motive` y `key_evidence`.
- Fallo → se envia **solo** la `exoneration` de ese sospechoso y el nombre del
  culpable. Un personaje inocente no tiene por que saber como se cometio el
  crimen, y mandarselo dejaria al modelo filtrar la solucion en un mensaje que
  el jugador lee.

`solucion.md` no se envia en ningun caso.

`epilogue_body` esta en `$hidden`: la pagina de acusacion serializa este modelo
y es alcanzable antes de la revelacion. Se opta explicitamente con
`revealEpilogue()`, igual que `Player::revealCredentials()`.

### Confesion en audio (`confession_audio`)

**No usa un endpoint nuevo.** El guion esta autorado en el manifiesto, asi que
es `/tts` normal: el modelo lo lee, no lo escribe. La voz sale de
`solution.confession_voice`.

No se le manda a nadie por correo. Vive en la consola del GM
(`/partidas/{id}/audio-final`, bajo `can:control`) para que lo reproduzca en voz
alta: la gracia es que la mesa lo escuche junta y una sola vez. Un link por
jugador seria otra mecanica, peor.

### Si la IA falla

Los dos extras se disparan **despues** de que la revelacion ya quedo marcada, y
nunca la deshacen. Si el gateway esta caido, si el caso perdio el contenido que
ese final necesita, o si la reserva de creditos se devolvio antes de revelar, se
salta el extra y se registra en el log — la mesa se queda con el cierre clasico,
que es un final completo por si solo. Nunca se cobra un extra que no se entrego.

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

## 4.2.3 Creditos de IA

Todo lo que llama a un modelo de verdad se mide en **creditos**, en un monedero
por cuenta. Los audios de la linea de tiempo y el final clasico **no cuestan**:
son contenido autorado o TTS incluido.

Todo se cobra **por partida, nunca por jugador**: invitar a alguien mas no
puede ser una decision de costo.

| | Cuesta |
|---|---|
| Pregunta a un sospechoso | 1 |
| Final clasico | 0 |
| Audio de confesion | 25 |
| **Epilogo personalizado** | **40** |
| Audios de la linea de tiempo | 0 (ya no usan IA) |

El epilogo es el mas caro por lo que **entrega** — cada jugador recibe algo
escrito para el —, no por lo que cuesta producirlo. Medido contra los precios
de Google, una confesion sale ~4.6x mas cara que un epilogo de seis jugadores:
el texto es barato y dos minutos de voz sintetizada no. Con la partida mas cara
posible costando unos 220 COP en IA, estos numeros no son para recuperar costos
sino para ordenar la experiencia.

### La reserva

Al **iniciar** la partida se congela de una vez **todo el techo** que esa
partida podria llegar a consumir. Si no alcanza, la partida no arranca y el GM
ve cuanto le falta — con la mesa todavia sin sentarse, que es el unico momento
en que eso se puede arreglar.

El techo **no depende de cuantos jugadores haya**: un sospechoso pertenece al
primer jugador que lo interroga de verdad, asi que el presupuesto es
`sospechosos x preguntas` (45 en steve-jacobs) jueguen tres personas u ocho.

Al **cerrar** el caso se devuelve lo que no se uso. Una mesa que solo interroga
a tres personas paga tres, no nueve. Eliminar la partida tambien devuelve, y lo
hace **antes** del delete: la fila del hold se va en cascada con la partida.

`Support/AiCredits` es lo unico que mueve creditos. Cada movimiento cambia el
monedero y escribe en el ledger dentro de la misma transaccion, con lock sobre
la fila: son dos escrituras que no pueden separarse nunca.

- `ai_credit_wallets` — `balance` (libre) y `reserved` (congelado). Columnas
  unsigned a proposito: un sobregiro falla en la base de datos en vez de
  regalar llamadas al modelo.
- `ai_credit_ledger` — append-only. Una correccion es una entrada nueva con el
  signo opuesto, nunca un update.
- `ai_credit_holds` — una reserva por partida (`unique(game_id)`, que es lo que
  hace que el doble clic en "Iniciar caso" congele una sola vez).

### Partidas de varias sesiones

Jugar un caso en dos fines de semana es normal, y la reserva no puede quedarse
congelada la semana entera.

**Pausar devuelve, reanudar vuelve a congelar.** Al pausar, la capacidad
restante vuelve al saldo de inmediato. Al reanudar se congela otra vez, pero
**solo lo que le queda**: una partida que ya hizo 12 preguntas vuelve a reservar
33, no 45. Lo ya usado no se cobra dos veces.

Si al reanudar el saldo ya no alcanza (se fue en otra mesa), la partida **se
queda en pausa** y dice por que. Una partida corriendo cuyos sospechosos no
responden es peor que una pausada: solo la pausada explica el problema.

### Partidas abandonadas

Una partida que nadie cierra **ni pausa** congelaria su reserva para siempre. El
scheduler corre cada hora `immersion:release-stale-holds`, que devuelve la
reserva de las partidas sin actividad en `IMMERSION_STALE_HOLD_HOURS` (una
semana por defecto) y **deja la partida intacta** — cerrarla desde un cron le
ocultaria el final a una mesa que todavia podria volver.

Esa partida deja de poder interrogar, que es lo honesto una vez devuelta la
capacidad. Su consola muestra el estado y ofrece **"Reactivar IA"**, que vuelve
a congelar lo que le queda. Es un boton y no algo automatico dentro de la
pregunta de un jugador: reactivar debita el monedero del GM, y eso no puede
pasar sin que el GM lo decida.

### Casos borde deliberados

- Una partida **sin hold** corre gratis. Es lo que mantiene jugables las
  partidas que ya estaban en curso cuando se desplego esto.
- Una partida cuyo hold fue **liberado** ya no puede gastar: sus creditos
  volvieron al monedero y dejarla seguir seria gastarlos dos veces.
- Si cobrar falla despues de tomar el turno, el turno se **devuelve**: el
  jugador no pierde una de sus cinco preguntas por algo que no ocurrio.

### Comandos

```
php artisan immersion:grant-credits correo@ejemplo.com 100 --note="Compensacion"
php artisan immersion:release-stale-holds --dry-run
```

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
