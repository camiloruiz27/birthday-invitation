<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Platform\Actions\FirstGameOffer;
use App\Modules\Platform\Models\MarketingExport;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Prepares a clean contact CSV for a manual Brevo import. */
class AdminEmailMarketingController extends Controller
{
    public function index(Request $request, FirstGameOffer $offer): InertiaResponse
    {
        $filters = $this->filters($request);
        $users = User::query()->where('is_admin', false)->orderByDesc('created_at')->get();

        return Inertia::render('Admin/EmailMarketing', [
            'filters' => $filters,
            'summary' => fn () => $this->summary($users),
            'contacts' => fn () => $this->applyEmailFilter($this->filteredContacts($filters)->orderByDesc('created_at')->get(), $filters)
                ->map(fn (User $user) => $this->contactPayload($user))->values(),
            'recentCases' => fn () => MysteryCase::query()
                ->whereNotNull('published_at')->where('published_at', '<=', now())->latest('published_at')->limit(8)->get()
                ->map(fn (MysteryCase $case) => [
                    'slug' => $case->slug,
                    'name' => $case->name,
                    'status' => 'Publicado',
                    'published_at' => $case->published_at?->toIso8601String(),
                    'summary' => Str::limit(trim(strip_tags((string) ($case->tagline ?: $case->description))), 180),
                    'url' => route('cases.show', $case, false),
                ])->values(),
            'lastExport' => fn () => $this->lastExport(),
            'autoOffer' => fn () => [
                'enabled' => (bool) config('platform.first_game_offer.enabled'),
                'delay_days' => (int) config('platform.first_game_offer.delay_days'),
                'discount_percent' => (int) config('platform.first_game_offer.discount_percent'),
                'valid_days' => (int) config('platform.first_game_offer.valid_days'),
                'batch_size' => (int) config('platform.first_game_offer.batch_size'),
                'pending' => $offer->pendingCount(),
            ],
        ]);
    }

    /**
     * The first-game offer exactly as it is received, to look at before
     * switching it on. Nothing is created and nothing is sent: the code is an
     * example that never existed and the unsubscribe link goes nowhere.
     */
    public function preview(Request $request, FirstGameOffer $offer): HttpResponse
    {
        $mail = $offer->sampleMail($request->user()->name);
        $html = $mail->render();

        // In a real message the logo is embedded and referenced as cid:...;
        // a browser cannot resolve that, so it points at the same file by URL.
        $html = (string) preg_replace('/src="cid:[^"]*"/', 'src="'.e(url('brand/isotipo-email.png')).'"', $html);

        $bar = '<div style="position:sticky;top:0;z-index:10;padding:10px 16px;background:#16232c;color:#f5efe0;'
            .'font:13px/1.5 Arial,Helvetica,sans-serif;text-align:center;">'
            .'<strong>Vista previa</strong> — Asunto: '.e((string) $mail->subject)
            .' · El código es de ejemplo y no existe; el enlace de baja no hace nada aquí.</div>';

        $html = (string) preg_replace('/(<body[^>]*>)/', '$1'.$bar, $html, 1);

        return new HttpResponse($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function toggleExclusion(User $user): RedirectResponse
    {
        $excluded = $user->marketing_opted_out_at === null;
        $user->forceFill(['marketing_opted_out_at' => $excluded ? now() : null])->save();

        return back()->with('status', $excluded
            ? "{$user->name} fue excluido de las exportaciones de marketing."
            : "{$user->name} puede volver a incluirse en las exportaciones de marketing.");
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $contacts = $this->applyEmailFilter($this->filteredContacts($filters)->get(), $filters)
            ->filter(fn (User $user) => $this->isValidEmail($user->email))
            // Authorised AND not opted out: a person who never said yes is not
            // exported, whatever the admin's filters show.
            ->filter(fn (User $user) => $user->acceptsMarketing())
            ->unique(fn (User $user) => Str::lower(trim($user->email)))->values();

        MarketingExport::create([
            'exported_by' => $request->user()->id,
            'contacts_count' => $contacts->count(),
            'filters' => $filters,
            'exported_at' => now(),
        ]);

        return Response::streamDownload(function () use ($contacts): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['EMAIL', 'FIRSTNAME', 'LASTNAME', 'PHONE', 'USER_ID', 'ACCOUNT_STATUS', 'REGISTERED_AT', 'TAGS', 'MARKETING_STATUS']);
            foreach ($contacts as $user) {
                [$firstName, $lastName] = $this->splitName($user->name);
                fputcsv($output, [$user->email, $firstName, $lastName, '', $user->id, $user->email_verified_at ? 'verified' : 'unverified', optional($user->created_at)->toIso8601String(), 'clientes_activos', 'included']);
            }
            fclose($output);
        }, 'brevo-contactos-'.now()->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{search:string, verification:string, marketing:string, email:string, from:?string, until:?string} */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'verification' => ['nullable', Rule::in(['all', 'verified', 'unverified'])],
            'marketing' => ['nullable', Rule::in(['all', 'included', 'excluded', 'no_consent'])],
            'email' => ['nullable', Rule::in(['all', 'valid', 'invalid'])],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return ['search' => trim((string) ($data['search'] ?? '')), 'verification' => $data['verification'] ?? 'all', 'marketing' => $data['marketing'] ?? 'all', 'email' => $data['email'] ?? 'all', 'from' => $data['from'] ?? null, 'until' => $data['until'] ?? null];
    }

    private function filteredContacts(array $filters): Builder
    {
        return User::query()->where('is_admin', false)
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(fn (Builder $nested) => $nested->where('name', 'like', '%'.$filters['search'].'%')->orWhere('email', 'like', '%'.$filters['search'].'%')))
            ->when($filters['verification'] === 'verified', fn (Builder $query) => $query->whereNotNull('email_verified_at'))
            ->when($filters['verification'] === 'unverified', fn (Builder $query) => $query->whereNull('email_verified_at'))
            ->when($filters['marketing'] === 'included', fn (Builder $query) => $query->acceptingMarketing())
            ->when($filters['marketing'] === 'excluded', fn (Builder $query) => $query->whereNotNull('marketing_opted_out_at'))
            ->when($filters['marketing'] === 'no_consent', fn (Builder $query) => $query->whereNull('marketing_consent_at'))
            ->when($filters['from'], fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['until'], fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date));
    }

    private function applyEmailFilter($users, array $filters)
    {
        if ($filters['email'] === 'valid') {
            return $users->filter(fn (User $user) => $this->isValidEmail($user->email))->values();
        }

        if ($filters['email'] === 'invalid') {
            return $users->reject(fn (User $user) => $this->isValidEmail($user->email))->values();
        }

        return $users;
    }

    private function summary($users): array
    {
        return ['active_clients' => $users->count(), 'valid_emails' => $users->filter(fn (User $user) => $this->isValidEmail($user->email))->count(), 'invalid_or_missing_emails' => $users->reject(fn (User $user) => $this->isValidEmail($user->email))->count(), 'excluded_contacts' => $users->whereNotNull('marketing_opted_out_at')->count(), 'without_consent' => $users->whereNull('marketing_consent_at')->count()];
    }

    private function contactPayload(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'email_valid' => $this->isValidEmail($user->email), 'verified' => $user->email_verified_at !== null, 'excluded' => $user->marketing_opted_out_at !== null, 'consented' => $user->marketing_consent_at !== null, 'registered_at' => $user->created_at?->toIso8601String()];
    }

    private function lastExport(): ?array
    {
        $export = MarketingExport::query()->with('exportedBy:id,name')->latest('exported_at')->first();
        return $export ? ['contacts_count' => $export->contacts_count, 'exported_at' => $export->exported_at?->toIso8601String(), 'exported_by' => $export->exportedBy?->name] : null;
    }

    private function isValidEmail(?string $email): bool
    {
        return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** @return array{0:string, 1:string} */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];
        return [$parts[0] ?? '', $parts[1] ?? ''];
    }
}
