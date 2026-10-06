<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\GameQuota;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * The three legal documents: data policy, terms and cookie policy.
 *
 * The texts live in the React pages; what comes from here is only the part
 * that changes per deployment — who the controller is and which version of
 * each document is in force — so none of it is hard-coded into prose.
 */
class LegalController extends Controller
{
    public function privacy(): HttpResponse
    {
        return $this->withoutIndexing(
            Inertia::render('Public/Legal/Privacy', $this->props('privacy_version'))
        );
    }

    public function terms(): HttpResponse
    {
        return $this->withoutIndexing(
            Inertia::render('Public/Legal/Terms', $this->props('terms_version') + [
                // The quota is stated in the terms, so it comes from the same
                // place the quota is enforced.
                'gamesPerCase' => app(GameQuota::class)->limit(),
            ])
        );
    }

    public function cookies(): Response
    {
        return Inertia::render('Public/Legal/Cookies', $this->props('cookies_version'));
    }

    /**
     * Keeps a page out of search results and cached copies.
     *
     * The privacy policy and the terms are where the responsible person's
     * identity has to appear (Ley 1581, Ley 1480 art. 50). They stay public
     * for anyone who follows a link to them, but a search for that person's
     * name should not lead to them. Sent as a header, which also covers
     * crawlers that never read the markup. The cookie policy carries no
     * personal data and is left alone.
     */
    private function withoutIndexing(Response $page): HttpResponse
    {
        return $page->toResponse(request())->header('X-Robots-Tag', 'noindex, noarchive');
    }

    /**
     * @return array<string, mixed>
     */
    private function props(string $versionKey): array
    {
        return [
            'legal' => [
                'entity_name' => config('legal.entity_name'),
                'entity_id' => config('legal.entity_id'),
                'address' => config('legal.address'),
                'city' => config('legal.city'),
                'email' => config('legal.email'),
                'phone' => config('legal.phone'),
                'version' => config("legal.{$versionKey}"),
                'updated_at' => config('legal.updated_at'),
            ],
        ];
    }
}
