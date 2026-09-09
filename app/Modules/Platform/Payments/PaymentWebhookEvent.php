<?php

namespace App\Modules\Platform\Payments;

/**
 * One payment provider webhook, normalised.
 *
 * `status` is already translated to the same vocabulary as Order::status
 * (pending|approved|rejected|voided) so BoldWebhookController never has to
 * know what Bold calls its own events ("Venta aprobada", and so on).
 */
final class PaymentWebhookEvent
{
    public function __construct(
        public readonly ?string $reference,
        public readonly string $status,
        public readonly ?string $providerPaymentId,
        public readonly array $raw,
    ) {
    }
}
