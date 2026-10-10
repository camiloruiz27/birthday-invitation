<?php

namespace App\Modules\Platform\Ads;

/**
 * One conversion, in the platform-neutral shape both ad networks are fed
 * from. Built once (AdEvents), sent to each configured network by its own
 * class (TikTokEventsApi, MetaConversionsApi), and plain arrays all the way
 * so it survives being put on a queue.
 *
 * `eventId` is what lets a network recognise that the server-side event and
 * the browser pixel's event describe the SAME conversion and count it once;
 * for a purchase it is the order id, the same value the browser sends.
 */
final class AdEvent
{
    public const PURCHASE = 'Purchase';

    public const COMPLETE_REGISTRATION = 'CompleteRegistration';

    /**
     * @param  array<int, array{id: string, quantity: int}>  $contents
     */
    public function __construct(
        public readonly string $name,
        public readonly string $eventId,
        public readonly int $time,
        public readonly ?string $email = null,
        public readonly ?string $ip = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $url = null,
        public readonly ?string $ttclid = null,
        public readonly ?string $fbclid = null,
        public readonly ?string $fbp = null,
        public readonly ?string $fbc = null,
        public readonly ?string $ttp = null,
        public readonly ?int $value = null,
        public readonly ?string $currency = null,
        public readonly array $contents = [],
    ) {
    }

    /**
     * What identifies a person to an ad network is always a hash of their
     * email, never the address itself: trimmed, lower-cased, SHA-256.
     */
    public function hashedEmail(): ?string
    {
        if ($this->email === null || trim($this->email) === '') {
            return null;
        }

        return hash('sha256', strtolower(trim($this->email)));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
