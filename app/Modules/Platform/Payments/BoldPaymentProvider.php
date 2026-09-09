<?php

namespace App\Modules\Platform\Payments;

use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use App\Modules\Platform\Payments\Exceptions\PaymentProviderException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Bold's Payment Link API (https://developers.bold.co/pagos-en-linea/api-link-de-pagos).
 *
 * The buyer is redirected to a page Bold hosts for the card form; this class
 * never sees a card number. It only creates the link and, later, verifies and
 * decodes what Bold's webhook reports about it.
 *
 * card-only by product decision (no PSE, Nequi or cash yet — see the payment
 * gateway plan), so `payment_methods` is pinned to CREDIT_CARD. Confirm that
 * literal against a real sandbox response before going live: Bold's dashboard
 * is the source of truth for its own enum values, and this was written from
 * documentation, not a live call.
 */
class BoldPaymentProvider implements PaymentProvider
{
    public function createCheckoutLink(Order $order, string $callbackUrl): string
    {
        $response = Http::baseUrl($this->baseUrl())
            ->withHeaders(['Authorization' => "x-api-key {$this->identityKey()}"])
            ->timeout($this->timeout())
            ->post('/online/link/v1', array_filter([
                'amount_type' => 'CLOSE',
                'amount' => [
                    'currency' => $order->currency,
                    'tip_amount' => 0,
                    'total_amount' => $order->amount,
                ],
                'reference' => $order->reference,
                'description' => $this->descriptionFor($order),
                'payment_methods' => ['CREDIT_CARD'],
                // Bold requires https://; a local http:// callback would be
                // rejected, so it is only sent when it actually qualifies.
                // Bold still works without one — the buyer just has nothing
                // to click after paying, so PaymentCallbackController's
                // polling page is the only way to know it worked.
                'callback_url' => str_starts_with($callbackUrl, 'https://') ? $callbackUrl : null,
            ]));

        $url = $response->json('payload.url');

        if (! $response->successful() || ! is_string($url) || $url === '') {
            Log::error('platform_payment_link_failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PaymentProviderException(
                "Bold no devolvio un link de pago para la orden #{$order->id}."
            );
        }

        return $url;
    }

    /**
     * Per Bold's docs: Base64-encode the raw request body, then HMAC-SHA256
     * it with the secret key, and compare against x-bold-signature. Must run
     * against the exact bytes Bold sent — never a re-encoded/parsed body.
     *
     * Sandbox note: Bold signs test-mode webhooks with an empty secret key,
     * so this same code path verifies real and sandbox webhooks alike.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (! $signatureHeader) {
            return false;
        }

        $expected = hash_hmac('sha256', base64_encode($rawBody), $this->secretKey());

        return hash_equals($expected, $signatureHeader);
    }

    public function parseWebhookEvent(array $payload): PaymentWebhookEvent
    {
        $status = match ($payload['type'] ?? null) {
            'SALE_APPROVED' => Order::STATUS_APPROVED,
            'SALE_REJECTED' => Order::STATUS_REJECTED,
            'VOID_APPROVED' => Order::STATUS_VOIDED,
            // A rejected void changes nothing about the sale itself, so it
            // maps to no state transition the rest of the platform reacts to.
            default => 'unknown',
        };

        $data = (array) ($payload['data'] ?? []);

        // Bold echoes back whatever we sent as the link's `reference` nested
        // under data.metadata.reference, not as a sibling of payment_id — see
        // the worked example in Bold's webhook docs.
        $reference = $data['metadata']['reference'] ?? $data['reference'] ?? null;

        return new PaymentWebhookEvent(
            reference: $reference !== null ? (string) $reference : null,
            status: $status,
            providerPaymentId: isset($data['payment_id']) ? (string) $data['payment_id'] : null,
            raw: $payload,
        );
    }

    private function descriptionFor(Order $order): string
    {
        return $order->type === Order::TYPE_CASE
            ? "Caso: {$order->mysteryCase?->name}"
            : "Recarga de creditos ({$order->credits_granted} creditos)";
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('platform.payments.base_url'), '/');
    }

    private function identityKey(): string
    {
        return (string) config('platform.payments.identity_key');
    }

    private function secretKey(): string
    {
        return (string) config('platform.payments.secret_key');
    }

    private function timeout(): int
    {
        return (int) config('platform.payments.timeout');
    }
}
