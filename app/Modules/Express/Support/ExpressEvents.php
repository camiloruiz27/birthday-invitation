<?php

namespace App\Modules\Express\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The fairs Express can run for, read from config/express.php.
 *
 * An event is configuration, not data: reusing the area at another fair means
 * adding an entry there. Rooms and contacts only remember the event's slug.
 */
final class ExpressEvents
{
    private const TIMEZONE = 'America/Bogota';

    public static function defaultSlug(): string
    {
        return (string) config('express.default_event');
    }

    /**
     * @return array{slug: string, name: string, tagline: string, active: bool, ends_at: ?Carbon, promo_code: ?string, promo_note: ?string, utm_campaign: string, case_slugs: ?array<int, string>, cta_path: string}|null
     */
    public static function find(?string $slug): ?array
    {
        $slug = $slug !== null && $slug !== '' ? $slug : self::defaultSlug();
        $event = config("express.events.{$slug}");

        if (! is_array($event)) {
            return null;
        }

        return [
            'slug' => $slug,
            'name' => (string) ($event['name'] ?? $slug),
            'tagline' => (string) ($event['tagline'] ?? ''),
            'active' => (bool) ($event['active'] ?? true),
            'ends_at' => ! empty($event['ends_at']) ? Carbon::parse($event['ends_at'], self::TIMEZONE) : null,
            'promo_code' => ! empty($event['promo_code']) ? (string) $event['promo_code'] : null,
            'promo_note' => ! empty($event['promo_note']) ? (string) $event['promo_note'] : null,
            'utm_campaign' => (string) ($event['utm_campaign'] ?? 'express_'.$slug),
            'case_slugs' => isset($event['case_slugs']) && is_array($event['case_slugs']) ? array_values($event['case_slugs']) : null,
            'cta_path' => (string) ($event['cta_path'] ?? '/jugar'),
        ];
    }

    /**
     * The event a request is about: `?e=slug` (what the printed QR carries),
     * else the one remembered from the page that led here (a form posts
     * without the query string), else the default. An unknown slug falls back
     * to the default rather than 404ing a printed QR.
     *
     * @return array<string, mixed>
     */
    public static function resolve(Request $request, ?string $slug = null): array
    {
        $slug ??= is_string($request->query('e')) ? $request->query('e') : null;
        $slug ??= is_string($request->input('event')) ? $request->input('event') : null;

        if ($slug === null && $request->hasSession()) {
            $slug = $request->session()->get('express.event');
        }

        $event = self::find($slug) ?? self::find(null) ?? abort(404);

        if ($request->hasSession()) {
            $request->session()->put('express.event', $event['slug']);
        }

        return $event;
    }

    /**
     * Whether people can play right now and, if not, what to tell them.
     *
     * @param  array<string, mixed>  $event
     * @return array{open: bool, reason: ?string}
     */
    public static function status(array $event): array
    {
        if (! config('express.enabled') || ! $event['active']) {
            return ['open' => false, 'reason' => 'Esta demostración no está disponible por ahora.'];
        }

        if ($event['ends_at'] !== null && now()->greaterThan($event['ends_at'])) {
            return ['open' => false, 'reason' => 'La feria ya terminó. ¡Gracias por jugar!'];
        }

        return ['open' => true, 'reason' => null];
    }

    /**
     * Cases this event offers, in the registry's order.
     *
     * @param  array<string, mixed>  $event
     * @return array<string, ExpressCase>
     */
    public static function cases(array $event, ExpressCaseRegistry $registry): array
    {
        $all = $registry->all();

        if ($event['case_slugs'] === null) {
            return $all;
        }

        return array_intersect_key($all, array_flip($event['case_slugs']));
    }

    /**
     * Where the end screen sends people: the full catalog, with the fair's
     * promo and the UTMs that let an eventual purchase be credited to it.
     *
     * @param  array<string, mixed>  $event
     */
    public static function ctaUrl(array $event): string
    {
        $query = array_filter([
            'promo_code' => $event['promo_code'],
            'utm_source' => 'express',
            'utm_medium' => $event['slug'],
            'utm_campaign' => $event['utm_campaign'],
        ]);

        return $event['cta_path'].'?'.http_build_query($query);
    }

    /**
     * What the printed QR encodes: the event's landing, tagged as coming from
     * a stand poster.
     *
     * @param  array<string, mixed>  $event
     */
    public static function posterUrl(array $event): string
    {
        return url('/express').'?'.http_build_query([
            'e' => $event['slug'],
            'utm_source' => 'feria',
            'utm_medium' => 'qr',
            'utm_campaign' => $event['utm_campaign'],
        ]);
    }
}
