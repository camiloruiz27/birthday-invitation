<?php

namespace App\Modules\Platform\Ads;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends one conversion to one ad network.
 *
 * A job rather than a call made where the sale is recorded, so a slow or
 * failing ad network can never hold up (or undo) a payment settling. One job
 * per network, so TikTok being down does not cost the Meta event.
 */
class SendAdEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  array<string, mixed>  $event  AdEvent::toArray()
     */
    public function __construct(public string $network, public array $event)
    {
    }

    public function handle(TikTokEventsApi $tiktok, MetaConversionsApi $meta): void
    {
        $event = AdEvent::fromArray($this->event);

        $sender = match ($this->network) {
            AdEvents::TIKTOK => $tiktok,
            AdEvents::META => $meta,
            default => null,
        };

        if ($sender && $sender->enabled()) {
            $sender->send($event);
        }
    }
}
