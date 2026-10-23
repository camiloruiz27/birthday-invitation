<?php

namespace App\Modules\Express\Console;

use App\Modules\Express\Models\Lead;
use App\Modules\Express\Support\ExpressEvents;
use Illuminate\Console\Command;

/**
 * The contacts an event collected, as CSV with the contact decrypted. Personal
 * data: it prints to the terminal or to a file you choose, never anywhere the
 * web can reach.
 */
class ExportLeadsCommand extends Command
{
    protected $signature = 'express:export-leads {event? : Event slug (default: the default event)} {--out= : Write to this file instead of the terminal}';

    protected $description = 'Export the contacts left at an Express event (CSV, decrypted)';

    public function handle(): int
    {
        $event = ExpressEvents::find($this->argument('event'));

        if ($event === null) {
            $this->error('Unknown event.');

            return self::FAILURE;
        }

        $handle = $this->option('out') ? fopen((string) $this->option('out'), 'w') : fopen('php://output', 'w');

        if ($handle === false) {
            $this->error('Cannot open the output file.');

            return self::FAILURE;
        }

        fputcsv($handle, ['nickname', 'type', 'contact', 'consent_at', 'consent_version', 'utm_source', 'utm_medium', 'utm_campaign', 'created_at']);

        Lead::query()->where('event_slug', $event['slug'])->orderBy('id')->each(function (Lead $lead) use ($handle) {
            $touch = (array) ($lead->attribution['first'] ?? []);

            fputcsv($handle, [
                $lead->nickname,
                $lead->contact_type,
                $lead->contact,
                $lead->consent_at?->toIso8601String(),
                $lead->consent_version,
                $touch['utm_source'] ?? '',
                $touch['utm_medium'] ?? '',
                $touch['utm_campaign'] ?? '',
                $lead->created_at?->toIso8601String(),
            ]);
        });

        fclose($handle);

        if ($this->option('out')) {
            $this->info('Exported to '.$this->option('out'));
        }

        return self::SUCCESS;
    }
}
