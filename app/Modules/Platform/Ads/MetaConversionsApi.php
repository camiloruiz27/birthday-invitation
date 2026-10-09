<?php

namespace App\Modules\Platform\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Meta Conversions API: the server-side half of the Meta pixel.
 *
 * Same purpose as TikTokEventsApi — recover what the browser pixel loses and
 * let Meta merge the two through event_id. Written from Meta's documentation,
 * not a live call: confirm the first events in Events Manager -> Test Events
 * (set PLATFORM_META_TEST_EVENT_CODE) before trusting the numbers.
 */
class MetaConversionsApi
{
    public function enabled(): bool
    {
        return (string) config('platform.ads.meta.pixel_id') !== ''
            && (string) config('platform.ads.meta.access_token') !== '';
    }

    public function send(AdEvent $event): void
    {
        $pixelId = (string) config('platform.ads.meta.pixel_id');
        $version = (string) config('platform.ads.meta.api_version', 'v21.0');

        $userData = array_filter([
            'em' => $event->hashedEmail() ? [$event->hashedEmail()] : null,
            'client_ip_address' => $event->ip,
            'client_user_agent' => $event->userAgent,
            'fbp' => $event->fbp,
            'fbc' => $event->fbc ?? $this->fbcFromClickId($event),
        ]);

        $customData = [];

        if ($event->value !== null) {
            $customData = [
                'currency' => $event->currency ?? 'COP',
                'value' => $event->value,
                'content_type' => 'product',
                'content_ids' => array_column($event->contents, 'id'),
                'contents' => array_map(fn (array $item) => [
                    'id' => $item['id'],
                    'quantity' => $item['quantity'],
                ], $event->contents),
            ];
        }

        $payload = array_filter([
            'data' => [array_filter([
                'event_name' => $event->name,
                'event_time' => $event->time,
                'event_id' => $event->eventId,
                'action_source' => 'website',
                'event_source_url' => $event->url,
                'user_data' => $userData,
                'custom_data' => $customData ?: null,
            ])],
            'test_event_code' => (string) config('platform.ads.meta.test_event_code') ?: null,
        ]);

        $response = Http::timeout((int) config('platform.ads.timeout', 5))
            ->post(
                "https://graph.facebook.com/{$version}/{$pixelId}/events?access_token="
                    .urlencode((string) config('platform.ads.meta.access_token')),
                $payload
            );

        if ($response->successful()) {
            return;
        }

        // Never log the URL: it carries the access token.
        Log::warning('platform_ads_meta_event_failed', [
            'event' => $event->name,
            'event_id' => $event->eventId,
            'status' => $response->status(),
            'body' => mb_substr($response->body(), 0, 500),
        ]);

        if ($response->serverError()) {
            throw new RuntimeException('Meta Conversions API unavailable.');
        }
    }

    /**
     * When the visitor clicked a Meta ad but the _fbc cookie never got set
     * (the pixel was blocked or not yet loaded), Meta documents rebuilding it
     * from the click id: fb.1.<click time ms>.<fbclid>.
     */
    private function fbcFromClickId(AdEvent $event): ?string
    {
        return $event->fbclid ? 'fb.1.'.($event->time * 1000).'.'.$event->fbclid : null;
    }
}
