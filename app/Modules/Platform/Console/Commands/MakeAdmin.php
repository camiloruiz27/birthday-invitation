<?php

namespace App\Modules\Platform\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Grants (or removes) platform administrator.
 *
 * Console-only on purpose. There is no screen anywhere that promotes an
 * account, so the privilege cannot be handed out by a bug in a form, a
 * mass-assignment slip, or someone who got hold of a session. Becoming the
 * administrator requires access to the server.
 */
class MakeAdmin extends Command
{
    protected $signature = 'platform:make-admin
                            {email : Account to promote}
                            {--revoke : Remove administrator instead of granting it}';

    protected $description = 'Grant or revoke platform administrator on an account.';

    public function handle(): int
    {
        $user = User::firstWhere('email', $this->argument('email'));

        if (! $user) {
            $this->error("No account found for {$this->argument('email')}.");
            $this->comment('Register at /registro first, then run this again.');

            return self::FAILURE;
        }

        $revoking = (bool) $this->option('revoke');

        if ($revoking && User::where('is_admin', true)->count() === 1 && $user->is_admin) {
            $this->error('This is the only administrator left; promote someone else first.');

            return self::FAILURE;
        }

        // forceFill, not update: is_admin is kept out of $fillable so that no
        // request payload can ever set it. This command is the one place
        // allowed to bypass that, and it does so visibly.
        $user->forceFill(['is_admin' => ! $revoking])->save();

        $this->info($revoking
            ? "{$user->email} is no longer an administrator."
            : "{$user->email} is now a platform administrator.");

        return self::SUCCESS;
    }
}
