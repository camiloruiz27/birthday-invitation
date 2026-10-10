<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\CreatePromoCodes;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Support\PromoCodeMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Creating, switching off and watching promo codes.
 *
 * Codes used to be a console-only job because they were handed out a handful
 * of times a year. Running paid campaigns changed that: discounts of many
 * values, for cases or for credits, created often, by someone who should not
 * need a terminal. The console commands remain (same writer,
 * CreatePromoCodes); this is the same operation behind a form, in an area
 * already limited to administrators (EnsureAdmin).
 */
class AdminPromoCodesController extends Controller
{
    public function index(Request $request, PromoCodeMetrics $metrics): Response
    {
        // Lazily, same reason as AdminDashboardController: an Inertia
        // partial reload should not re-run every aggregate query.
        return Inertia::render('Admin/PromoCodes', [
            'metrics' => fn () => $metrics->all(),
            'cases' => fn () => MysteryCase::ordered()->get(['slug', 'name'])->all(),
            // Codes just created in a batch: shown once, because the whole
            // point of a batch is to copy the list somewhere.
            'created' => fn () => $request->session()->get('created_codes'),
        ]);
    }

    public function store(Request $request, CreatePromoCodes $creator): RedirectResponse
    {
        // Codes are stored and compared upper-cased everywhere else.
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
            'prefix' => strtoupper(trim((string) $request->input('prefix'))),
        ]);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['single', 'batch'])],
            // Same alphabet Attribution accepts from a campaign link, so
            // every code created here can also travel inside one.
            'code' => ['required_if:mode,single', 'nullable', 'string', 'max:60', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('promo_codes', 'code')],
            'prefix' => ['required_if:mode,batch', 'nullable', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/'],
            'count' => ['required_if:mode,batch', 'nullable', 'integer', 'min:1', 'max:'.CreatePromoCodes::MAX_BATCH],
            'kind' => ['required', Rule::in(['discount', 'gift'])],
            'discount_type' => ['required_if:kind,discount', 'nullable', Rule::in([PromoCode::DISCOUNT_PERCENT, PromoCode::DISCOUNT_FIXED])],
            'discount_value' => ['required_if:kind,discount', 'nullable', 'integer', 'min:1'],
            'applies_to' => ['nullable', Rule::in([PromoCode::APPLIES_CASE, PromoCode::APPLIES_CREDITS])],
            'grants_case_slug' => ['nullable', 'string', Rule::exists('mystery_cases', 'slug')],
            'grants_any_case' => ['boolean'],
            'grants_credits' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'max_per_user' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'code.regex' => 'Solo letras, números, guion y guion bajo.',
            'code.unique' => 'Ya existe un código con ese nombre.',
            'prefix.regex' => 'Solo letras, números, guion y guion bajo.',
            'expires_at.after_or_equal' => 'La fecha de vencimiento tiene que ser hoy o futura.',
        ]);

        $validator = validator($data, []);
        $this->validateGrant($validator, $data);

        if ($validator->errors()->isNotEmpty()) {
            return back()->withErrors($validator->errors())->withInput();
        }

        $attributes = [
            'note' => $data['note'] ?? null,
            // A bare date lasts through that whole day.
            'expires_at' => isset($data['expires_at']) ? Carbon::parse($data['expires_at'])->endOfDay() : null,
        ];

        if ($data['kind'] === 'discount') {
            $attributes += [
                'discount_type' => $data['discount_type'],
                'discount_value' => (int) $data['discount_value'],
                'applies_to' => $data['applies_to'] ?? null,
            ];
        } else {
            $attributes += array_filter([
                'grants_case_slug' => $data['grants_case_slug'] ?? null,
                'grants_any_case' => ! empty($data['grants_any_case']) ?: null,
                'grants_credits' => isset($data['grants_credits']) ? (int) $data['grants_credits'] : null,
            ], fn ($value) => $value !== null);
        }

        if ($data['mode'] === 'batch') {
            $codes = $creator->batch($data['prefix'], (int) $data['count'], $attributes);

            return redirect()
                ->route('admin.codes')
                ->with('status', count($codes).' códigos creados.')
                ->with('created_codes', $codes);
        }

        // A lone code: caps are the admin's to choose. 0 per person means no
        // cap, the same convention as the console's --max-per-user=0.
        $perUser = (int) ($data['max_per_user'] ?? 1);

        $creator->single($data['code'], $attributes + [
            'max_redemptions' => isset($data['max_redemptions']) ? (int) $data['max_redemptions'] : null,
            'max_redemptions_per_user' => $perUser === 0 ? null : $perUser,
        ]);

        return redirect()->route('admin.codes')->with('status', "Código {$data['code']} creado.");
    }

    /**
     * Switches a code off (or back on). Nothing is ever deleted: a code that
     * has been redeemed is part of the record of what was given away.
     */
    public function toggle(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->update(['active' => ! $promoCode->active]);

        return back()->with(
            'status',
            $promoCode->active ? "Código {$promoCode->code} activado." : "Código {$promoCode->code} desactivado."
        );
    }

    /**
     * The rules that depend on more than one field — the same ones the
     * console enforces: a code is a gift or a discount, a gift names at least
     * one thing, a percentage stays within 100, and a case is either fixed or
     * left to the redeemer, never both.
     *
     * @param  array<string, mixed>  $data
     */
    private function validateGrant(Validator $validator, array $data): void
    {
        if ($data['kind'] === 'discount') {
            if (($data['discount_type'] ?? null) === PromoCode::DISCOUNT_PERCENT && (int) $data['discount_value'] > 100) {
                $validator->errors()->add('discount_value', 'Un porcentaje no puede pasar de 100.');
            }

            return;
        }

        $anyCase = ! empty($data['grants_any_case']);
        $caseSlug = $data['grants_case_slug'] ?? null;
        $credits = $data['grants_credits'] ?? null;

        if ($anyCase && $caseSlug) {
            $validator->errors()->add('grants_case_slug', 'Elige un caso fijo o "a elección", no los dos.');
        }

        if (! $anyCase && ! $caseSlug && ! $credits) {
            $validator->errors()->add('grants_credits', 'Un regalo tiene que dar un caso, créditos o los dos.');
        }
    }
}
