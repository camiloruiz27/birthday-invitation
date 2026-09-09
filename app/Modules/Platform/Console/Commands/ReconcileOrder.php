<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Actions\SettleOrder;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use Illuminate\Console\Command;

/**
 * Manually ask Bold what happened to an order and settle it — the same
 * active check PaymentCallbackController runs on every load, available as an
 * operator escape hatch for an order stuck pending because its webhook never
 * arrived (and the buyer already left the confirmation page).
 */
class ReconcileOrder extends Command
{
    protected $signature = 'platform:reconcile-order {order : Order id}';

    protected $description = 'Ask Bold directly what happened to a pending order and settle it';

    public function handle(PaymentProvider $payments, SettleOrder $settle): int
    {
        $order = Order::find((int) $this->argument('order'));

        if (! $order) {
            $this->error('No existe esa orden.');

            return self::FAILURE;
        }

        $this->line("Orden #{$order->id}: estado actual '{$order->status}'.");

        if (! $order->isPending()) {
            $this->info('Ya no esta pendiente, nada que hacer.');

            return self::SUCCESS;
        }

        $event = $payments->checkStatus($order);
        $this->line('Bold reporta: '.($event->status === 'unknown' ? 'sin novedad todavia' : $event->status));

        $settle->apply($order, $event);

        $order->refresh();
        $this->info("Estado final: {$order->status}.");

        return self::SUCCESS;
    }
}
