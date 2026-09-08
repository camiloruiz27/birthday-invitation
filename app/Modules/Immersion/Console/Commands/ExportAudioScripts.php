<?php

namespace App\Modules\Immersion\Console\Commands;

use App\Modules\Immersion\Cases\CaseRegistry;
use Illuminate\Console\Command;

/**
 * Prints a case's audio scripts in the shape the recording studio asks for.
 *
 * Case audio is authored, not generated: it is the same recording for every
 * table, so it is produced once outside the platform and deployed with the
 * case. This is the hand-off — the scripts, laid out as Scene / Sample Context
 * / Speaker / speech, ready to paste in one at a time.
 *
 * The recorded files go in public/immersion/<slug>/audio/, and the manifest
 * points at each with `audio_file`.
 */
class ExportAudioScripts extends Command
{
    protected $signature = 'immersion:export-audio-scripts
                            {case? : Case slug (default: every installed case)}
                            {--missing : Only the ones with no recording yet}';

    protected $description = 'Print a case audio scripts in the recording studio format';

    public function handle(CaseRegistry $cases): int
    {
        $slugs = $this->argument('case') ? [$this->argument('case')] : $cases->slugs();

        foreach ($slugs as $slug) {
            $case = $cases->find($slug);

            if (! $case) {
                $this->error("No existe el caso \"{$slug}\".");

                return self::FAILURE;
            }

            $this->exportCase($case, $slug);
        }

        return self::SUCCESS;
    }

    private function exportCase($case, string $slug): void
    {
        $scripts = $case->audioScripts();

        if ($this->option('missing')) {
            $scripts = array_values(array_filter($scripts, fn (array $s) => $s['file'] === ''));
        }

        $this->newLine();
        $this->line(str_repeat('=', 72));
        $this->line('  CASO: '.$case->name().'  ('.$slug.')');
        $this->line('  Sube los .wav a: public/immersion/'.$slug.'/audio/');
        $this->line(str_repeat('=', 72));

        if ($scripts === []) {
            $this->newLine();
            $this->line($this->option('missing')
                ? '  Todos los audios de este caso ya estan grabados.'
                : '  Este caso no tiene audios en su linea de tiempo.');

            return;
        }

        foreach ($scripts as $script) {
            $this->exportScript($script, $slug);
        }

        $this->newLine();
        $this->line('  '.count($scripts).' audio(s). Al terminar, apunta cada archivo en case.php:');
        $this->line("      'audio_file' => 'nombre-del-archivo.wav',");
        $this->newLine();
    }

    private function exportScript(array $script, string $slug): void
    {
        $this->newLine();
        $this->line(str_repeat('-', 72));
        $this->line("  #{$script['index']}  {$script['title']}   (minuto {$script['minute']})");

        $this->line($script['file'] !== ''
            ? "  Grabado: public/immersion/{$slug}/audio/{$script['file']}"
            : '  SIN GRABAR');

        $this->line(str_repeat('-', 72));

        $this->newLine();
        $this->line('  Scene');
        $this->line('    '.($script['scene'] ?: '(sin definir en el manifiesto: audio_scene)'));

        $this->newLine();
        $this->line('  Sample Context');
        $this->line('    '.($script['context'] ?: '(sin definir en el manifiesto: audio_context)'));

        $this->newLine();
        $speaker = $script['speaker'];
        $this->line('  '.($script['voice'] !== '' ? "{$speaker} - {$script['voice']}" : $speaker));
        $this->newLine();

        // Wrapped, but the text itself is untouched: what is pasted into the
        // studio has to be exactly what the case says.
        foreach (preg_split('/\R/', $script['script']) as $line) {
            $this->line('    '.$line);
        }

        $this->newLine();
    }
}
