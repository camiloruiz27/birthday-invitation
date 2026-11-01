<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Commercial availability of the cases installed on this server.
 *
 * A manifest makes the game content deployable; it does not put a product on
 * sale. Only an administrator may do that here, which keeps a finished case
 * private until its launch is intentional.
 */
class AdminCasesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Cases', [
            'cases' => fn () => MysteryCase::ordered()->get()->map(fn (MysteryCase $case) => [
                'slug' => $case->slug,
                'name' => $case->name,
                'tagline' => $case->tagline,
                'cover_url' => $case->coverUrl(),
                'price_amount' => $case->price_amount,
                'currency' => $case->currency,
                'sort_order' => $case->sort_order,
                'published' => $case->isPublished(),
                'published_at' => $case->published_at?->toIso8601String(),
                'playable' => $case->isPlayable(),
            ])->values(),
        ]);
    }

    /**
     * Publish a draft or take a published case off sale.
     *
     * Taking a case down is always allowed, even after its manifest was
     * removed. Publishing, however, requires playable content on this server.
     */
    public function toggle(MysteryCase $mysteryCase): RedirectResponse
    {
        if ($mysteryCase->isPublished()) {
            $mysteryCase->update(['published_at' => null]);

            return redirect()
                ->route('admin.cases')
                ->with('status', "{$mysteryCase->name} fue retirado de venta.");
        }

        if (! $mysteryCase->isPlayable()) {
            return redirect()
                ->route('admin.cases')
                ->withErrors(['case' => "No puedes publicar {$mysteryCase->name} porque su contenido no está instalado en este servidor."]);
        }

        $mysteryCase->update(['published_at' => now()]);

        return redirect()
            ->route('admin.cases')
            ->with('status', "{$mysteryCase->name} ya está disponible para compra.");
    }
}
