<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Console\Command;

/**
 * Syncs the installed case manifests into the catalog table.
 *
 * Idempotent by design — safe to run on every deploy. The split matters:
 *
 *  - Content fields (name, tagline, description, difficulty, duration,
 *    players, mechanics, version) are re-synced every run. The manifest in
 *    git is their source of truth.
 *  - A new row begins as a draft. Publication belongs exclusively to the
 *    administrator, never to a deploy or a manifest flag.
 *  - Price, currency and sort order are seeded on creation and may be
 *    deliberately reapplied with --prices. Publication is never overwritten.
 *
 * Use --prices to deliberately re-apply the manifest's price and sort order.
 */
class SyncMysteryCases extends Command
{
    protected $signature = 'platform:sync-cases
                            {--prices : Also overwrite price, currency and sort order from the manifest}
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

            if (! $existing) {
                $attributes += $this->initialCommercialAttributes($case);
            } elseif ($syncPrices) {
                $attributes += $this->pricingAttributes($case);
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
            $this->comment('Existing prices and publication were left as-is. Use --prices to update prices only.');
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
            'ad' => $catalog['ad'],
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
    private function initialCommercialAttributes(CaseDefinition $case): array
    {
        return $this->pricingAttributes($case) + [
            // A deploy only makes the product available to the administrator.
            // Going on sale is an explicit action in /admin/casos.
            'published_at' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pricingAttributes(CaseDefinition $case): array
    {
        $catalog = $case->catalog();

        return [
            'price_amount' => $catalog['price_amount'],
            'currency' => $catalog['currency'],
            'sort_order' => $catalog['sort_order'],
        ];
    }
}
