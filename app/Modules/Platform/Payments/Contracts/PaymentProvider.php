<?php

namespace App\Modules\Platform\Payments\Contracts;

use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\PaymentWebhookEvent;

/**
 * A payment provider hosted checkout.
 *
 * Mirrors the AI provider contracts in App\Modules\Immersion\Ai\Contracts:
 * one interface, one gateway implementation, bound behind a config flag so
 * the rest of the platform never talks to Bold (or whoever comes after it)
 * directly.
 *
 * There is deliberately no "Null" implementation. An unconfigured AI
 * capability degrades to a no-AI fallback because the game stays playable
 * either way; there is no equivalent safe fallback for real money, so when
 * payments are not configured the checkout route is simply not offered
 * (see config('platform.payments.enabled')).
 */
interface PaymentProvider
{
    /**
     * Start a hosted checkout for this order and return the URL to send the
     * buyer to. Card data is entered on the provider's own page and never
     * reaches this application.
     */
    public function createCheckoutLink(Order $order, string $callbackUrl): string;

    /**
     * Verify that a webhook body actually came from the provider, using the
     * raw (unparsed) request body — the signature is computed over exact
     * bytes, so anything that has been through json_decode()/re-encode is the
     * wrong input here.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signatureHeader): bool;

    /**
     * Normalise a decoded webhook payload into the shape the rest of the
     * platform understands, so BoldWebhookController never touches Bold's own
     * field names or event vocabulary directly.
     */
    public function parseWebhookEvent(array $payload): PaymentWebhookEvent;
}
