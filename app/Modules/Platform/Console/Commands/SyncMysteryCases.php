<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Console\Command;

/**
 * Publishes the installed case manifests into the catalog table.
 *
 * Idempotent by design — safe to run on every deploy. The split matters:
 *
 *  - Content fields (name, tagline, description, difficulty, duration,
 *    players, mechanics, version) are re-synced every run. The manifest in
 *    git is their source of truth.
 *  - Commercial fields (price, currency, publication) are written only when
 *    the row is created. After that the database owns them, so changing a
 *    price does not require a deploy and is not silently reverted by one.
 *
 * Use --prices to deliberately re-apply the manifest's commercial values.
 */
class SyncMysteryCases extends Command
{
    protected $signature = 'platform:sync-cases
                            {--prices : Also overwrite price, currency and publication from the manifest}
                            {--dry-run : Report what would change without writing}';

    protected $description = 'Sync the installed mystery case manifests into the catalog.';

    public function handle(CaseRegistry $registry): int
    {
        $cases = $registry->all();

        if ($cases === []) {
            $this->warn('No case manifests found. Nothing to sync.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $syncPrices = (bool) $this->option('prices');

        $created = 0;
        $updated = 0;

        foreach ($cases as $case) {
            $existing = MysteryCase::firstWhere('slug', $case->slug);

            $attributes = $this->contentAttributes($case);

            if (! $existing || $syncPrices) {
                $attributes += $this->commercialAttributes($case);
            }

            if ($dryRun) {
                $this->line(sprintf(
                    '  %s %s',
                    $existing ? '~ would update' : '+ would create',
                    $case->slug
                ));

                continue;
            }

            MysteryCase::updateOrCreate(['slug' => $case->slug], $attributes);

            $existing ? $updated++ : $created++;
        }

        if ($dryRun) {
            $this->info(count($cases).' case(s) inspected. Nothing written.');

            return self::SUCCESS;
        }

        $this->info("Catalog synced: {$created} created, {$updated} updated.");

        if (! $syncPrices && $updated > 0) {
            $this->comment('Prices and publication were left as-is. Use --prices to overwrite them.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function contentAttributes(CaseDefinition $case): array
    {
        $catalog = $case->catalog();

        return [
            'name' => $case->name(),
            'tagline' => $catalog['tagline'],
            'description' => $catalog['description'],
            'cover_path' => $catalog['cover_path'],
            'difficulty' => $catalog['difficulty'],
            'duration_minutes' => $catalog['duration_minutes'],
            'min_players' => $catalog['min_players'],
            'max_players' => $catalog['max_players'],
            'mechanics' => $case->mechanics(),
            'content_version' => $case->version(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function commercialAttributes(CaseDefinition $case): array
    {
        $catalog = $case->catalog();

        return [
            'price_amount' => $catalog['price_amount'],
            'currency' => $catalog['currency'],
            'sort_order' => $catalog['sort_order'],
            'published_at' => $catalog['published'] ? now() : null,
        ];
    }
}
