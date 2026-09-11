<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Support\CronDiagnostics;
use Illuminate\Console\Command;

/**
 * Answers "is the cron actually working?" without needing SSH.
 *
 * This exists because the honest answer used to be unobtainable. Laravel
 * records nothing about having run the scheduler, so a cron that silently
 * fails — a wrong path to artisan, a killed process leaving a lock behind,
 * the whole entry deleted — looks exactly like a quiet afternoon. The first
 * symptom is a table of players waiting for an envelope that is never coming,
 * hours later, with no way to tell what went wrong.
 *
 * It reports the cause (is the scheduler alive?) next to the symptom (is any
 * game overdue?), because either one alone is misleading: a healthy heartbeat
 * with overdue events means the queue is stuck, not the cron.
 *
 * Exit code is 1 when something is actually wrong, so this is also usable as
 * a scheduled check that only writes to a log when it matters.
 */
class CronStatus extends Command
{
    protected $signature = 'platform:cron-status
                            {--tolerance=3 : Minutes without a heartbeat before the cron counts as down}';

    protected $description = 'Report whether the scheduler is running and whether anything is stuck';

    /** Collected as we go, so the command always prints the full picture. */
    private array $problems = [];

    public function handle(): int
    {
        $diagnostics = CronDiagnostics::gather();

        $this->line('');
        $this->line('  <options=bold>Estado del cron y de las tareas programadas</>');
        $this->line('');

        $this->reportHeartbeat($diagnostics);
        $this->reportOverdueTimeline($diagnostics);
        $this->reportQueue($diagnostics);
        $this->reportEnvironment($diagnostics);

        $this->line('');

        if ($this->problems === []) {
            $this->info('  Todo en orden.');
            $this->line('');

            return self::SUCCESS;
        }

        $this->error('  Hay '.count($this->problems).' cosa(s) que revisar:');

        foreach ($this->problems as $problem) {
            $this->line('   - '.$problem);
        }

        $this->line('');

        return self::FAILURE;
    }

    /**
     * The cause. Written by a one-line scheduled closure every minute, so if
     * this is stale the cron itself is not running the scheduler at all.
     */
    private function reportHeartbeat(array $diagnostics): void
    {
        $tolerance = max(2, (int) $this->option('tolerance'));

        if ($diagnostics['heartbeat_at'] === null) {
            $this->row('Último latido', 'nunca', false);
            $this->problems[] = 'El scheduler no ha corrido ni una vez desde que se instaló esta '
                .'comprobación. Revisa la tarea cron en el panel del hosting: la ruta a `artisan` '
                .'es lo que más se equivoca.';

            return;
        }

        $ago = $diagnostics['heartbeat_ago_minutes'];
        $alive = $ago <= $tolerance;

        $this->row(
            'Último latido',
            $diagnostics['heartbeat_at']->timezone(config('app.timezone'))->format('d/m/Y H:i:s')." ({$ago} min)",
            $alive,
        );

        if (! $alive) {
            $this->problems[] = "El scheduler lleva {$ago} minutos sin correr. Debería hacerlo cada "
                .'minuto. O la tarea cron se detuvo, o está fallando en silencio: si la mandaste a '
                .'/dev/null, cámbiala para que escriba en un log y vuelve a mirar.';
        }
    }

    /**
     * The symptom, and the reason a heartbeat alone is not enough: the
     * scheduler can be perfectly alive while events pile up because the queue
     * behind it is not being drained.
     */
    private function reportOverdueTimeline(array $diagnostics): void
    {
        $overdue = $diagnostics['overdue_events'];
        $games = $diagnostics['overdue_games'];

        $this->row(
            'Eventos vencidos sin enviar',
            $overdue === 0 ? 'ninguno' : "{$overdue} en {$games} partida(s)",
            $overdue === 0,
        );

        if ($overdue > 0) {
            $this->problems[] = "Hay {$overdue} evento(s) que ya deberían haber salido. Si el latido "
                .'de arriba está al día, el problema no es el cron sino la cola: mira si hay trabajos '
                .'atascados o fallidos.';
        }
    }

    private function reportQueue(array $diagnostics): void
    {
        $driver = $diagnostics['queue_driver'];

        $this->row('Driver de cola', $driver, $driver !== 'sync');

        if ($driver === 'sync') {
            $this->problems[] = 'La cola está en `sync`, así que el audio y los correos se generan '
                .'dentro de la propia petición. Revelar la solución de una partida puede tardar más '
                .'de lo que aguanta un navegador. Cámbialo a `database` y corre `php artisan migrate`.';

            return;
        }

        if ($diagnostics['queue_waiting'] !== null) {
            $waiting = $diagnostics['queue_waiting'];
            $this->row('Trabajos en cola', (string) $waiting, $waiting < 25);

            if ($waiting >= 25) {
                $this->problems[] = "Hay {$waiting} trabajos esperando. La cola no se está vaciando.";
            }
        }

        if ($diagnostics['queue_failed'] !== null) {
            $failed = $diagnostics['queue_failed'];
            $this->row('Trabajos fallidos', (string) $failed, $failed === 0);

            if ($failed > 0) {
                $this->problems[] = "Hay {$failed} trabajo(s) fallidos. Míralos con "
                    .'`php artisan queue:failed`; suelen ser correos o audio que no salieron.';
            }
        }
    }

    /**
     * Not about the cron, but this is the command someone runs when the
     * server is misbehaving, and a production site left in debug mode is
     * worth saying out loud while somebody is looking.
     */
    private function reportEnvironment(array $diagnostics): void
    {
        // Informational, not a verdict: this command runs on a laptop as
        // often as on the server, and a red mark against "local" there would
        // be noise with nothing to fix. APP_DEBUG is what is dangerous
        // wherever it is true, so it is the one that carries the alarm.
        $this->row('Entorno', $diagnostics['environment'], null);
        $this->row('Modo depuración', $diagnostics['debug'] ? 'activado' : 'desactivado', ! $diagnostics['debug']);

        if ($diagnostics['debug']) {
            $this->problems[] = 'APP_DEBUG está activado. Cualquier error muestra una traza con el '
                .'contenido del .env, incluidas las contraseñas de la base de datos y del correo.';
        }
    }

    /** `$ok` of null is an informational row: reported, not judged. */
    private function row(string $label, string $value, ?bool $ok): void
    {
        $mark = match ($ok) {
            true => '<fg=green>OK </>',
            false => '<fg=red>!! </>',
            null => '   ',
        };

        $this->line('  '.$mark.str_pad($label, 30).$value);
    }
}
