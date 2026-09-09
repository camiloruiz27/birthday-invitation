<?php

namespace App\Modules\Platform\Payments;

/**
 * What creating a hosted checkout gives back: where to send the buyer, and
 * the provider's own id for the link — needed later to actively ask "what
 * happened to this?" instead of only ever waiting on a webhook.
 */
final class PaymentCheckoutLink
{
    public function __construct(
        public readonly string $url,
        public readonly ?string $providerLinkId,
    ) {
    }
}
