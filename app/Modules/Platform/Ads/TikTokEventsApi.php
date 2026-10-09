<?php

namespace App\Modules\Platform\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * TikTok Events API: the server-side half of the TikTok pixel.
 *
 * The browser pixel loses events to ad blockers, in-app browsers and Safari's
 * tracking prevention; sending the same conversion from here recovers them,
 * and TikTok merges the two through event_id. Written from TikTok's Events API
 * 2.0 documentation, not a live call — confirm the first events in Events
 * Manager -> Test Events (set PLATFORM_TIKTOK_TEST_EVENT_CODE) before trusting
 * the numbers.
 */
class TikTokEventsApi
{
    private const ENDPOINT = 'https://business-api.tiktok.com/open_api/v1.3/event/track/';

    /**
     * TikTok's own name for each event: its pixel and its API call a
     * purchase CompletePayment.
     */
    private const EVENT_NAMES = [
        AdEvent::PURCHASE => 'CompletePayment',
        AdEvent::COMPLETE_REGISTRATION => 'CompleteRegistration',
    ];

    public function enabled(): bool
    {
        return (string) config('platform.ads.tiktok.pixel_id') !== ''
            && (string) config('platform.ads.tiktok.access_token') !== '';
    }

    public function send(AdEvent $event): void
    {
        $user = array_filter([
            'email' => $event->hashedEmail(),
            'ip' => $event->ip,
            'user_agent' => $event->userAgent,
            'ttclid' => $event->ttclid,
            'ttp' => $event->ttp,
        ]);

        $properties = [];

        if ($event->value !== null) {
            $properties = [
                'currency' => $event->currency ?? 'COP',
                'value' => $event->value,
                'content_type' => 'product',
                'contents' => array_map(fn (array $item) => [
                    'content_id' => $item['id'],
                    'quantity' => $item['quantity'],
                ], $event->contents),
            ];
        }

        $payload = array_filter([
            'event_source' => 'web',
            'event_source_id' => (string) config('platform.ads.tiktok.pixel_id'),
            'test_event_code' => (string) config('platform.ads.tiktok.test_event_code') ?: null,
            'data' => [[
                'event' => self::EVENT_NAMES[$event->name] ?? $event->name,
                'event_time' => $event->time,
                'event_id' => $event->eventId,
                'user' => $user,
                'page' => array_filter(['url' => $event->url]),
                'properties' => $properties ?: new \stdClass(),
            ]],
        ]);

        $response = Http::withHeaders(['Access-Token' => (string) config('platform.ads.tiktok.access_token')])
            ->timeout((int) config('platform.ads.timeout', 5))
            ->post(self::ENDPOINT, $payload);

        // TikTok answers 200 with its own code in the body; code 0 is success.
        if ($response->successful() && (int) $response->json('code', 0) === 0) {
            return;
        }

        Log::warning('platform_ads_tiktok_event_failed', [
            'event' => $event->name,
            'event_id' => $event->eventId,
            'status' => $response->status(),
            'body' => mb_substr($response->body(), 0, 500),
        ]);

        // A 4xx will not improve by retrying; only transient failures should.
        if ($response->serverError()) {
            throw new RuntimeException('TikTok Events API unavailable.');
        }
    }
}
