<?php

namespace App\Modules\Express\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Express\Models\EventSetting;
use App\Modules\Express\Support\ExpressEvents;
use App\Modules\Express\Support\ExpressMetrics;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Manage the promotion shown at the end of Express fair games. */
class AdminExpressController extends Controller
{
    public function index(Request $request, ExpressMetrics $metrics): Response
    {
        $filterData = $request->validate([
            'event' => ['nullable', 'string', 'max:60'],
            'promo_code' => ['nullable', 'string', 'max:60'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $statFilters = [
            'event' => $filterData['event'] ?? null,
            'promo_code' => $filterData['promo_code'] ?? null,
            'from' => $filterData['from'] ?? null,
            'until' => $filterData['until'] ?? null,
        ];
        $overrides = EventSetting::query()->get()->keyBy('event_slug');
        $events = collect(config('express.events', []))
            ->keys()
            ->map(function (string $slug) use ($overrides): array {
                $event = ExpressEvents::find($slug);
                $override = $overrides->get($slug);

                return [
                    'slug' => $slug,
                    'name' => $event['name'],
                    'configured_promo_code' => config("express.events.{$slug}.promo_code"),
                    'promo_code' => $event['promo_code'],
                    'tagline' => $event['tagline'],
                    'landing_title' => $event['landing_title'],
                    'landing_emphasis' => $event['landing_emphasis'],
                    'closed_title' => $event['closed_title'],
                    'closed_reason' => $override?->closed_reason ?? '',
                    'active' => $event['active'],
                    'has_end_date' => $event['ends_at'] !== null,
                    'ends_at' => $event['ends_at']?->format('Y-m-d\\TH:i'),
                    'utm_medium' => $event['utm_medium'],
                    'utm_campaign' => $event['utm_campaign'],
                    'has_override' => $override !== null,
                ];
            })
            ->values()
            ->all();

        $codes = PromoCode::query()
            ->whereNull('assigned_user_id')
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('applies_to')->orWhere('applies_to', PromoCode::APPLIES_CASE);
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_redemptions')->orWhereColumn('redemptions_count', '<', 'max_redemptions');
            })
            ->orderBy('code')
            ->get()
            ->map(fn (PromoCode $code) => [
                'code' => $code->code,
                'description' => $code->describeGrant(),
            ])
            ->all();

        return Inertia::render('Admin/Express', [
            'events' => $events,
            'codes' => $codes,
            'statistics' => fn () => $metrics->dashboard($statFilters),
            'statFilters' => $statFilters,
            'statOptions' => fn () => $metrics->options(),
        ]);
    }

    public function update(Request $request, string $event): RedirectResponse
    {
        abort_unless(ExpressEvents::find($event) !== null, 404);

        $current = ExpressEvents::find($event);
        $setting = EventSetting::query()->firstOrNew(['event_slug' => $event]);
        $promoCode = strtoupper(trim((string) $request->input('promo_code')));
        $request->merge(['promo_code' => $promoCode === '' ? null : $promoCode]);
        $data = $request->validate([
            'promo_code' => ['nullable', 'string', 'max:60', 'regex:/^[A-Z0-9_-]+$/'],
            'display_name' => ['nullable', 'string', 'max:160'],
            'tagline' => ['nullable', 'string', 'max:1000'],
            'landing_title' => ['nullable', 'string', 'max:180'],
            'landing_emphasis' => ['nullable', 'string', 'max:180'],
            'closed_title' => ['nullable', 'string', 'max:160'],
            'closed_reason' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'ends_at_enabled' => ['nullable', 'boolean'],
            'ends_at' => ['nullable', 'date'],
            'utm_medium' => ['sometimes', 'nullable', 'string', 'max:100'],
            'utm_campaign' => ['sometimes', 'nullable', 'string', 'max:100'],
        ], ['promo_code.regex' => 'Selecciona un código promocional válido.']);

        if (($data['promo_code'] ?? null) !== null) {
            $isEligible = PromoCode::query()
                ->where('code', $data['promo_code'])
                ->whereNull('assigned_user_id')
                ->where('active', true)
                ->where(function ($query) {
                    $query->whereNull('applies_to')->orWhere('applies_to', PromoCode::APPLIES_CASE);
                })
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('max_redemptions')->orWhereColumn('redemptions_count', '<', 'max_redemptions');
                })
                ->exists();

            if (! $isEligible) {
                return back()->withErrors(['promo_code' => 'El código debe estar activo, disponible y aplicar a casos.']);
            }
        }

        $hasEndDatePreference = array_key_exists('ends_at_enabled', $data);
        $endsAtEnabled = $hasEndDatePreference
            ? (bool) $data['ends_at_enabled']
            : ((bool) $setting->ends_at_overridden ? $setting->ends_at !== null : $current['ends_at'] !== null);

        if ($hasEndDatePreference && $endsAtEnabled && empty($data['ends_at'])) {
            return back()->withErrors(['ends_at' => 'Indica hasta cuándo estará disponible la feria o desactiva la fecha de cierre.']);
        }

        $setting->fill([
            'promo_code' => $data['promo_code'] ?? $current['promo_code'],
            'display_name' => $data['display_name'] ?? $current['name'],
            'tagline' => $data['tagline'] ?? $current['tagline'],
            'landing_title' => $data['landing_title'] ?? $current['landing_title'],
            'landing_emphasis' => $data['landing_emphasis'] ?? $current['landing_emphasis'],
            'closed_title' => $data['closed_title'] ?? $current['closed_title'],
            'closed_reason' => array_key_exists('closed_reason', $data) ? $data['closed_reason'] : $setting->closed_reason,
            'active' => array_key_exists('active', $data) ? (bool) $data['active'] : $current['active'],
            'ends_at_overridden' => $hasEndDatePreference ? true : (bool) ($setting->ends_at_overridden ?? false),
            'ends_at' => $hasEndDatePreference
                ? ($endsAtEnabled ? ($data['ends_at'] ?? null) : null)
                : $setting->ends_at,
            // Empty strings are deliberate: ExpressEvents::ctaUrl() omits
            // them, so an administrator can remove either parameter.
            'utm_medium' => array_key_exists('utm_medium', $data) ? trim((string) $data['utm_medium']) : $current['utm_medium'],
            'utm_campaign' => array_key_exists('utm_campaign', $data) ? trim((string) $data['utm_campaign']) : $current['utm_campaign'],
            'updated_by' => $request->user()->id,
        ]);
        $setting->save();
        EventSetting::forgetCached($event);

        return redirect()->route('admin.express')->with('status', 'Configuración de Express actualizada.');
    }
}
