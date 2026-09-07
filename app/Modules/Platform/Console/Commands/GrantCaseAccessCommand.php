<?php

namespace App\Modules\Platform\Console\Commands;

use App\Models\User;
use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Console\Command;

/**
 * Grants case access by hand.
 *
 * This is how access is created until real checkout exists, and it stays
 * useful afterwards for support and comps. It writes an ordinary entitlement
 * through the same action a purchase uses, so nothing downstream can tell the
 * difference — only `source` records that it was a manual grant.
 */
class GrantCaseAccessCommand extends Command
{
    protected $signature = 'platform:grant-access
                            {email : Account that should get access}
                            {case : Mystery case slug}
                            {--revoke : Remove the access instead of granting it}';

    protected $description = 'Grant (or revoke) a user access to a mystery case.';

    public function handle(GrantCaseAccess $access): int
    {
        $user = User::firstWhere('email', $this->argument('email'));

        if (! $user) {
            $this->error("No account found for {$this->argument('email')}.");

            return self::FAILURE;
        }

        $case = MysteryCase::firstWhere('slug', $this->argument('case'));

        if (! $case) {
            $this->error("No case found for slug \"{$this->argument('case')}\".");
            $this->line('Available: '.MysteryCase::pluck('slug')->implode(', '));
            $this->comment('If the catalog is empty, run: php artisan platform:sync-cases');

            return self::FAILURE;
        }

        if ($this->option('revoke')) {
            $access->revoke($user, $case);
            $this->info("Revoked {$case->slug} from {$user->email}.");

            return self::SUCCESS;
        }

        $access->grant($user, $case, Entitlement::SOURCE_GRANT);

        $this->info("Granted {$case->slug} to {$user->email}.");

        return self::SUCCESS;
    }
}
