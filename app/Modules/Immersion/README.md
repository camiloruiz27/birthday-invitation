# Immersion - "¿Que le sucedio a Steve Jacobs?"

Modulo aditivo y autocontenido (nada del juego base -invitacion ni
"aniversario en Cartagena"- se toca). Para desactivarlo por completo: borra
esta carpeta y quita la linea `App\Modules\Immersion\ImmersionServiceProvider::class`
de `config/app.php`.

## 1. Contenido del caso

Coloca los archivos `.md` del caso en `data/` siguiendo `data/README.md`
(nombres exactos). Sin esos archivos, los correos que citan un "Sobre"
simplemente saldran vacios; el resto de la mecanica funciona igual.

## 2. Variables de entorno

En `.env` (ver `.env.example`):

```
IMMERSION_GM_PASSWORD=...           # contrasena compartida del Game Master
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

Esto crea una partida ("Partida de prueba - Steve Jacobs") con 6 jugadores
de ejemplo y las 8 entradas de linea de tiempo (minutos 10 a 75).

## 4. Probar el flujo completo en local

1. Recomendado para pruebas: pon `MAIL_MAILER=log` en `.env` para ver los
   correos en `storage/logs/laravel.log` en vez de intentar enviarlos de verdad.
2. Entra a `/gm/login` con `IMMERSION_GM_PASSWORD`.
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

## 5. Audio (TTS)

El audio se genera llamando al proyecto `mystery-case` del gateway
`lawxora-ai-service` (ver ese repo, `src/projects/mystery-case/`), que a su
vez llama a Gemini (`gemini-2.5-flash-preview-tts`) y devuelve un `.wav`
(PCM 24kHz/16-bit envuelto en un header WAV). Se cachea en
`storage/app/audio/{event_id}.wav` y no se regenera si ya existe.

## 6. Mecanica 7 - Interrogatorio por IA

Cada jugador puede interrogar a cualquiera de las 9 personas en
`data/suspects/` (8 sospechosos + el testigo Jeremy Burt), maximo 5
preguntas por persona. El modelo (`gemini-3.6-flash` por defecto, ver
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
