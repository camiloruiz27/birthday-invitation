<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Models\Order;
use Illuminate\Console\Command;

/**
 * Marks abandoned checkouts as expired.
 *
 * Mirrors immersion:release-stale-holds, but simpler: a `pending` order never
 * granted a case or a credit, so there is nothing to give back — this is data
 * hygiene, not an accounting operation. Without it, an account that started
 * five checkouts and finished none keeps five "pending" rows forever, with no
 * way to tell an abandoned one from one still being confirmed by the webhook.
 */
class ExpireStaleOrders extends Command
{
    protected $signature = 'platform:expire-stale-orders
                            {--hours= : Override the abandonment window}
                            {--dry-run : List what would be expired without touching anything}';

    protected $description = 'Mark payment orders left pending too long as expired';

    public function handle(RedeemPromoCode $promos): int
    {
        $hours = (int) ($this->option('hours') ?: config('platform.payments.stale_order_hours', 24));

        if ($hours < 1) {
            $this->error('El plazo tiene que ser de al menos una hora.');

            return self::FAILURE;
        }

        $stale = Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->where('created_at', '<', now()->subHours($hours))
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No hay ordenes pendientes vencidas.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            foreach ($stale as $order) {
                $this->line("  [simulacion] orden #{$order->id} ({$order->reference}) expiraria");
            }

            $this->info("Se expirarian {$stale->count()} ordenes.");

            return self::SUCCESS;
        }

        // One conditional update per order rather than a mass update: an
        // expired order gives back the use of its discount code, and only
        // the call that really made the transition may do that. Each update
        // still checks for pending, so an order a webhook approved an instant
        // earlier is simply left alone (zero rows) and keeps its redemption.
        $count = 0;

        foreach ($stale as $order) {
            $expired = Order::where('id', $order->id)
                ->where('status', Order::STATUS_PENDING)
                ->update(['status' => Order::STATUS_EXPIRED]);

            if ($expired === 1) {
                $count++;
                $promos->releaseForOrder($order);
            }
        }

        $this->info("Expiradas {$count} ordenes pendientes de mas de {$hours}h.");

        return self::SUCCESS;
    }
}
