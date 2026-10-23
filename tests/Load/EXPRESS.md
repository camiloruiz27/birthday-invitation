# Carga de Express (la demo de feria)

`express.mjs` juega rondas **completas** con el comportamiento de un teléfono (landing, formularios, un poll cada ~3 s durante los 3 minutos, preguntas, compartir, acusar, pantalla final) y mide tres cosas: latencia y errores, **conexiones nuevas a MySQL por grupo** y si todos los teléfonos de una sala cuentan la misma historia. Solo necesita Node 20+ (no k6).

Se corre **solo contra un servidor de pruebas**: crea salas y jugadores falsos. Contra test, gasta el presupuesto de conexiones de ese sitio.

## Local, sin tocar ninguna base real

Se necesita una base MySQL/MariaDB **aparte** y varios procesos PHP (uno solo atiende una petición a la vez; con 8 se parece a un hosting con varios workers). Las variables de entorno del proceso ganan sobre `.env`, así que no hay que editar nada:

```powershell
# 1. Una base vacía (ejemplo: MariaDB de XAMPP en otro puerto y otra carpeta de datos)
mysql_install_db.exe --datadir=C:\tmp\loaddb
mysqld.exe --defaults-file=C:\tmp\loaddb\my.ini --port=3307 --max_connections=300
# (ponle contraseña a root: PowerShell no deja "vacía" una variable de entorno)

# 2. Esquema
$env:DB_HOST="127.0.0.1"; $env:DB_PORT="3307"; $env:DB_DATABASE="express_load"
$env:DB_USERNAME="root"; $env:DB_PASSWORD="loadtest"
php artisan migrate --force

# 3. Ocho "workers" (con DB_PERSISTENT=true para medir con conexiones persistentes)
$env:EXPRESS_AI_ENABLED="false"; $env:APP_DEBUG="false"; $env:EXPRESS_USB_ENDS_AT="2030-01-01 00:00:00"
foreach ($p in 8101..8108) { Start-Process php -ArgumentList "-d","zend_extension=opcache","-d","opcache.enable_cli=1","-S","127.0.0.1:$p","-t","public","public/index.php" -WindowStyle Hidden }
```

```bash
B="http://127.0.0.1:8101,http://127.0.0.1:8102,http://127.0.0.1:8103,http://127.0.0.1:8104,http://127.0.0.1:8105,http://127.0.0.1:8106,http://127.0.0.1:8107,http://127.0.0.1:8108"
MY="--mysql-bin mysql.exe --mysql-args \"-uroot -ploadtest --host=127.0.0.1 --port=3307\""

# 20 jugadores a la vez en 4 salas separadas (el objetivo), dos oleadas
node tests/Load/express.mjs --bases "$B" --scenario burst --groups 4 --size 5 --waves 2 $MY
# 100 jugadores a la vez (5 veces el objetivo)
node tests/Load/express.mjs --bases "$B" --scenario burst --groups 20 --size 5 $MY
# llegadas sostenidas, ~300 jugadores/hora durante 8 minutos
node tests/Load/express.mjs --bases "$B" --scenario sustained --arrivals-per-min 1 --minutes 8 --size 5 $MY
```

Después: parar los procesos y borrar los archivos de `storage/framework/sessions`, `storage/app/ops-cache` y `storage/framework/cache/data` creados durante la prueba.

## Contra el sitio de test

```bash
node tests/Load/express.mjs --bases https://TEST --scenario burst --groups 4 --size 5
```

Sin `--mysql-bin` no cuenta conexiones; para eso, en la base de test: `SHOW GLOBAL STATUS LIKE 'Connections';` antes y después (la diferencia, menos tus propias consultas). Corre el evento **cerrado al público** y avisa: son salas reales en esa base.

## Resultados (2026-10-22, portátil de 16 núcleos, MariaDB local sin latencia de red)

| Escenario | Jugadores | Peticiones | Fallos | poll p95 | Peor página p95 | Rondas | Anomalías |
|---|---|---|---|---|---|---|---|
| 4 salas × 5, 2 oleadas | 20 a la vez | 2.441 (pico 22/s) | 0 | 122 ms | 336 ms | 8/8 | 0 |
| 20 salas × 5 | 100 a la vez | 6.263 (pico 95/s) | 0 | 56 ms | 355 ms | 20/20 | 0 |
| Llegadas ~300 jugadores/h, 8 min | ~17 a la vez | 1.826 | 0 | 133 ms | 196 ms | 6/6 | 0 |

**Conexiones nuevas a MySQL** (lo que topa el hosting: 500 por hora para todo el sitio):

| | Por grupo de 5 | A 100 jugadores/hora (20 grupos) |
|---|---|---|
| Sin conexiones persistentes | **20** (4 por jugador) | **~400 de las 500** |
| Con `DB_PERSISTENT=true` | ~1 (más ≈ una por worker, 9 vistas) | ~25 |

Conclusión: la aplicación aguanta la carga (sin errores ni inconsistencias hasta 100 jugadores a la vez en esta máquina). **El cuello de botella es el tope de conexiones por hora, no el rendimiento**: sin conexiones persistentes, 100 jugadores/hora gastan ~80 % del presupuesto de todo el sitio.

## Qué NO prueba

- La CPU y los límites de procesos del hosting real (esta máquina es mucho más potente).
- La latencia de red hacia la base de Hostinger.
- La IA (se corre con `EXPRESS_AI_ENABLED=false`): su carga es aparte (≈45 llamadas por 3 minutos con 15 grupos).
- Descarga de los archivos estáticos (JS/CSS/imágenes), que sirve el servidor web, no PHP.
