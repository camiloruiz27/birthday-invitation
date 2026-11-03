<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\CaseRating;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Anonymous satisfaction metrics; individual player identities never leave storage. */
class AdminRatingsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'case' => ['nullable', 'string', Rule::exists('mystery_cases', 'slug')],
            'sort' => ['nullable', Rule::in(['average', 'count', 'name'])],
        ]);

        $caseSlug = $data['case'] ?? null;
        $sort = $data['sort'] ?? 'count';
        $cases = MysteryCase::ordered()->get(['slug', 'name']);
        $ratings = CaseRating::query()
            ->when($caseSlug, fn ($query, $slug) => $query->where('case_slug', $slug))
            ->get(['case_slug', 'rating_half_steps', 'tags']);
        $byCase = $ratings->groupBy('case_slug');

        $rows = $cases
            ->when($caseSlug, fn ($collection) => $collection->where('slug', $caseSlug))
            ->map(function (MysteryCase $case) use ($byCase) {
                $group = $byCase->get($case->slug, collect());
                $tagCounts = $group->flatMap(fn (CaseRating $rating) => $rating->tags ?? [])
                    ->countBy()
                    ->mapWithKeys(fn ($count, $tag) => [
                        $tag => ['label' => CaseRating::TAGS[$tag] ?? $tag, 'count' => $count],
                    ])->values();

                return [
                    'slug' => $case->slug,
                    'name' => $case->name,
                    'average' => $group->isEmpty() ? null : round($group->avg('rating_half_steps') / 2, 1),
                    'count' => $group->count(),
                    'distribution' => collect(range(2, 10))->map(fn (int $steps) => [
                        'half_steps' => $steps,
                        'label' => rtrim(rtrim(number_format($steps / 2, 1, '.', ''), '0'), '.'),
                        'count' => $group->where('rating_half_steps', $steps)->count(),
                    ])->values(),
                    'tags' => $tagCounts,
                ];
            });

        $rows = match ($sort) {
            'average' => $rows->sortByDesc(fn (array $row) => $row['average'] ?? -1)->values(),
            'name' => $rows->sortBy('name')->values(),
            default => $rows->sortByDesc('count')->values(),
        };

        return Inertia::render('Admin/Ratings', [
            'cases' => fn () => $cases->map(fn (MysteryCase $case) => $case->only(['slug', 'name']))->values(),
            'ratings' => fn () => $rows,
            'filters' => ['case' => $caseSlug, 'sort' => $sort],
        ]);
    }
}
