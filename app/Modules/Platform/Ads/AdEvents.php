<?php

namespace App\Modules\Platform\Ads;

use App\Models\User;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Support\Attribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The single door conversions leave the platform through towards TikTok and
 * Meta — same principle as StartCheckout and GrantCaseAccess: one place, so
 * there is one place to look when asking "why did (or did not) this sale
 * reach the ad networks?".
 *
 * Two rules hold everywhere in here:
 *
 *  - Nothing is sent without the visitor's marketing consent. It is recorded
 *    at the moment of the action (a cookie only exists in the browser), as
 *    Orders.marketing_consent for a purchase, because the webhook that later
 *    settles the order has no browser to ask.
 *  - Nothing here may break what called it. A payment settling or an account
 *    being created matters more than a conversion report, so every failure is
 *    logged and swallowed.
 */
class AdEvents
{
    public const TIKTOK = 'tiktok';

    public const META = 'meta';

    public function __construct(private TikTokEventsApi $tiktok, private MetaConversionsApi $meta)
    {
    }

    /**
     * Whether the browser's consent cookie says the visitor accepted the
     * marketing category. Anything unreadable counts as "no".
     */
    public static function marketingConsent(Request $request): bool
    {
        $raw = $request->cookie('mc_consent');

        if (! is_string($raw) || $raw === '') {
            return false;
        }

        $parsed = json_decode($raw, true);

        return is_array($parsed)
            && (int) ($parsed['v'] ?? 0) >= 3
            && ($parsed['marketing'] ?? false) === true;
    }

    /**
     * What an order records about the visitor at the moment it is created —
     * the context a server-side conversion needs later, once the browser is
     * gone. Plain Order attributes, ready to merge into Order::create().
     *
     * The ad networks' own cookies (_fbp, _fbc, _ttp) are only read, and only
     * kept, with marketing consent; the campaign touches are kept regardless
     * because they are our own first-party measurement of where a sale came
     * from, not data shared with anyone.
     *
     * @return array<string, mixed>
     */
    public static function orderContext(Request $request, ?User $user = null): array
    {
        $consent = self::marketingConsent($request);
        $attribution = Attribution::touches($request, $user);

        if ($consent) {
            $cookies = array_filter([
                'fbp' => self::cookie($request, '_fbp'),
                'fbc' => self::cookie($request, '_fbc'),
                'ttp' => self::cookie($request, '_ttp'),
            ]);

            if ($cookies !== []) {
                $attribution['cookies'] = $cookies;
            }
        }

        return [
            'attribution' => $attribution ?: null,
            'client_ip' => $request->ip(),
            'client_user_agent' => $request->userAgent() ? mb_substr($request->userAgent(), 0, 255) : null,
            'marketing_consent' => $consent,
        ];
    }

    /**
     * A sale. Called once per order, from the single place an order becomes
     * approved (SettleOrder, or StartCheckout for a free one).
     */
    public function purchase(Order $order): void
    {
        if (! $order->marketing_consent) {
            return;
        }

        try {
            $order->loadMissing(['user', 'mysteryCase']);

            $attribution = (array) $order->attribution;
            $last = (array) ($attribution['last'] ?? []);
            $cookies = (array) ($attribution['cookies'] ?? []);

            $contentId = $order->type === Order::TYPE_CASE
                ? ($order->mysteryCase?->slug ?? 'case')
                : (string) $order->credit_package_id;

            $this->dispatch(new AdEvent(
                name: AdEvent::PURCHASE,
                // The same value the browser pixel sends on the confirmation
                // page (Payments/Confirming.jsx), so each network counts the
                // sale once.
                eventId: (string) $order->id,
                time: ($order->paid_at ?? now())->getTimestamp(),
                email: $order->user?->email,
                ip: $order->client_ip,
                userAgent: $order->client_user_agent,
                url: url($last['landing_path'] ?? '/'),
                ttclid: $last['ttclid'] ?? null,
                fbclid: $last['fbclid'] ?? null,
                fbp: $cookies['fbp'] ?? null,
                fbc: $cookies['fbc'] ?? null,
                ttp: $cookies['ttp'] ?? null,
                value: (int) $order->amount,
                currency: $order->currency,
                contents: [['id' => $contentId, 'quantity' => 1]],
            ));
        } catch (Throwable $exception) {
            Log::warning('platform_ads_purchase_event_failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * An account was created.
     */
    public function completeRegistration(User $user, Request $request): void
    {
        if (! self::marketingConsent($request)) {
            return;
        }

        try {
            $last = (array) (Attribution::touches($request)['last'] ?? []);

            $this->dispatch(new AdEvent(
                name: AdEvent::COMPLETE_REGISTRATION,
                // Matches the id the browser sends from the registration form.
                eventId: 'reg-'.$user->id,
                time: now()->getTimestamp(),
                email: $user->email,
                ip: $request->ip(),
                userAgent: $request->userAgent() ? mb_substr($request->userAgent(), 0, 255) : null,
                url: url($last['landing_path'] ?? '/'),
                ttclid: $last['ttclid'] ?? null,
                fbclid: $last['fbclid'] ?? null,
                fbp: self::cookie($request, '_fbp'),
                fbc: self::cookie($request, '_fbc'),
                ttp: self::cookie($request, '_ttp'),
            ));
        } catch (Throwable $exception) {
            Log::warning('platform_ads_registration_event_failed', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * One job per network that is actually configured, so one being down
     * never costs the other its event.
     */
    private function dispatch(AdEvent $event): void
    {
        foreach ([self::TIKTOK => $this->tiktok, self::META => $this->meta] as $network => $api) {
            if (! $api->enabled()) {
                continue;
            }

            try {
                SendAdEvent::dispatch($network, $event->toArray())->afterCommit();
            } catch (Throwable $exception) {
                // QUEUE_CONNECTION=sync runs the job right here, so a network
                // failure surfaces here — it must stop at the log.
                Log::warning('platform_ads_event_failed', [
                    'network' => $network,
                    'event' => $event->name,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private static function cookie(Request $request, string $name): ?string
    {
        $value = $request->cookie($name);

        return is_string($value) && $value !== '' ? mb_substr($value, 0, 255) : null;
    }
}
