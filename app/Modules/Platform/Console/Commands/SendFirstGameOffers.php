<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Immersion\Support\MailBudget;
use App\Modules\Platform\Actions\FirstGameOffer;
use Illuminate\Console\Command;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Mails the first-game offer to whoever is due it (see Actions\FirstGameOffer).
 *
 * Scheduled every second day (runsToday) and always registered: whether it
 * actually sends is the switch in Admin → Email marketing (FirstGameOffer::
 * enabled(), with the .env as the default). Each run sends at most
 * `batch_size` and stops early when the mailbox's daily allowance is spent
 * (MailBudget), so the people whose game closed before the feature existed
 * are served over several runs and never crowd out the messages a live table
 * is waiting for.
 *
 * `--dry-run` only lists who would receive it. It works while the feature is
 * switched off, to look before turning it on.
 */
class SendFirstGameOffers extends Command
{
    protected $signature = 'platform:send-first-game-offers
                            {--dry-run : List who would receive it, send nothing}
                            {--limit= : Most people this run (default: first_game_offer.batch_size)}';

    protected $description = 'Send the first-game discount offer to whoever closed their first game';

    /**
     * The scheduler's "every second day" gate: a continuous count of calendar
     * days from a fixed date, so it alternates forever. Cron's `*\/2` on the
     * day of the month would run on the 31st and again on the 1st, and the day
     * of the YEAR would skip a day at New Year (365 is odd). Dates only, in
     * UTC, so a daylight-saving change cannot shift it.
     */
    public static function runsToday(): bool
    {
        return self::runsOn(now());
    }

    /** Is this calendar day one of the run days? */
    public static function runsOn(CarbonInterface $day): bool
    {
        $epoch = Carbon::create(2026, 1, 1, 0, 0, 0, 'UTC');
        $date = Carbon::create($day->year, $day->month, $day->day, 0, 0, 0, 'UTC');

        return $epoch->diffInDays($date) % 2 === 0;
    }

    /** The next moment the scheduler will run this, for the admin panel. */
    public static function nextRunAt(): Carbon
    {
        for ($i = 0; $i <= 3; $i++) {
            $at = now()->startOfDay()->addDays($i)->setTime(10, 40);

            if ($at->isFuture() && self::runsOn($at)) {
                return $at;
            }
        }

        return now()->addDays(2)->setTime(10, 40);
    }

    public function handle(FirstGameOffer $offer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! FirstGameOffer::enabled()) {
            $this->warn('La oferta de primera partida está apagada (se enciende desde Admin → Email marketing). No se envió nada.');

            return self::SUCCESS;
        }

        $limit = $this->option('limit') !== null
            ? (int) $this->option('limit')
            : (int) config('platform.first_game_offer.batch_size');

        $due = $offer->eligible($limit);

        if ($due->isEmpty()) {
            $this->info('Nadie pendiente.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            foreach ($due as $user) {
                $this->line("#{$user->id} {$user->email}");
            }

            $this->info("{$due->count()} recibirían la oferta (máximo {$limit} por corrida).");

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($due as $user) {
            if (! MailBudget::canSend(MailBudget::OPTIONAL)) {
                $this->warn('Se alcanzó el tope diario de correo; el resto sale en la siguiente corrida.');

                break;
            }

            try {
                if ($offer->send($user) !== null) {
                    $sent++;
                }
            } catch (Throwable $e) {
                // Nothing was kept (code and mark roll back together), so this
                // person is simply due again next run. One bad address must not
                // stop everyone behind it.
                report($e);
                $this->error("No se pudo enviar a #{$user->id}: {$e->getMessage()}");
            }
        }

        $this->info("Enviadas: {$sent}.");

        return self::SUCCESS;
    }
}
