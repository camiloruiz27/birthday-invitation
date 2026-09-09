<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Actions\SettleOrder;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use Illuminate\Console\Command;

/**
 * The safety net for a buyer who closes the tab before the confirmation page
 * resolves: without this, an order whose webhook never arrives sits
 * `pending` forever until platform:expire-stale-orders writes it off a full
 * day later, with the case or credits never delivered even though Bold may
 * have approved the charge.
 *
 * Skips anything younger than a couple of minutes — that window belongs to
 * the confirmation page's own active check (PaymentCallbackController), not
 * this sweep.
 */
class ReconcilePendingOrders extends Command
{
    protected $signature = 'platform:reconcile-pending-orders {--minutes=2 : Skip orders younger than this}';

    protected $description = 'Actively re-check every still-pending order against Bold, in case a webhook never arrived';

    public function handle(PaymentProvider $payments, SettleOrder $settle): int
    {
        $minutes = (int) $this->option('minutes');

        $pending = Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->whereNotNull('provider_link_id')
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No hay ordenes pendientes que revisar.');

            return self::SUCCESS;
        }

        $settled = 0;

        foreach ($pending as $order) {
            $event = $payments->checkStatus($order);
            $settle->apply($order, $event);

            if ($event->status !== 'unknown') {
                $settled++;
                $this->line("  orden #{$order->id}: {$event->status}");
            }
        }

        $this->info("Revisadas {$pending->count()}, resueltas {$settled}.");

        return self::SUCCESS;
    }
}
