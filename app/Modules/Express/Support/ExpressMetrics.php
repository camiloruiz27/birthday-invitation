<?php

namespace App\Modules\Express\Support;

use App\Modules\Express\Models\Accusation;
use App\Modules\Express\Models\CtaClick;
use App\Modules\Express\Models\Player;
use App\Modules\Express\Models\Question;
use App\Modules\Express\Models\Room;
use Illuminate\Support\Collection;

/** Anonymous operational and engagement statistics for Express fairs. */
final class ExpressMetrics
{
    /**
     * @param  array{event: ?string, promo_code: ?string, from: ?string, until: ?string}  $filters
     * @return array<string, mixed>
     */
    public function dashboard(array $filters): array
    {
        $query = Room::query()
            ->when($filters['event'], fn ($rooms, $event) => $rooms->where('event_slug', $event))
            ->when($filters['promo_code'], function ($rooms, $code) {
                return $code === '__none__' ? $rooms->whereNull('promo_code') : $rooms->where('promo_code', $code);
            })
            ->when($filters['from'], fn ($rooms, $date) => $rooms->where('created_at', '>=', $date.' 00:00:00'))
            ->when($filters['until'], fn ($rooms, $date) => $rooms->where('created_at', '<=', $date.' 23:59:59'));

        $rooms = $query->get([
            'id', 'event_slug', 'case_slug', 'status', 'started_at_ms', 'promo_code',
            'utm_campaign', 'campaign_snapshotted', 'created_at',
        ]);
        $roomIds = $rooms->pluck('id');

        $players = $roomIds->isEmpty()
            ? collect()
            : Player::query()->whereIn('room_id', $roomIds)->get(['id', 'room_id', 'lead_id']);
        $questions = $roomIds->isEmpty()
            ? collect()
            : Question::query()->whereIn('room_id', $roomIds)->get(['id', 'room_id']);
        $accusations = $roomIds->isEmpty()
            ? collect()
            : Accusation::query()->whereIn('room_id', $roomIds)->get(['id', 'room_id', 'score_total']);
        $clicks = $roomIds->isEmpty()
            ? collect()
            : CtaClick::query()->whereIn('room_id', $roomIds)->get(['id', 'room_id']);

        $started = $rooms->whereNotNull('started_at_ms')->count();
        $finished = $rooms->where('status', Room::FINISHED)->count();

        return [
            'summary' => [
                'rooms' => $rooms->count(),
                'started' => $started,
                'finished' => $finished,
                'completion_rate' => $started > 0 ? round($finished / $started * 100, 1) : null,
                'players' => $players->count(),
                'questions' => $questions->count(),
                'accusations' => $accusations->count(),
                'average_score' => $accusations->whereNotNull('score_total')->isEmpty()
                    ? null
                    : round((float) $accusations->whereNotNull('score_total')->avg('score_total'), 1),
                'perfect_scores' => $accusations->where('score_total', 100)->count(),
                'leads' => $players->pluck('lead_id')->filter()->unique()->count(),
                'cta_clicks' => $clicks->count(),
                'cta_rate' => $players->count() > 0 ? round($clicks->count() / $players->count() * 100, 1) : null,
            ],
            'campaigns' => $this->campaignRows($rooms, $players, $clicks),
            'cases' => $this->caseRows($rooms, $players, $accusations),
        ];
    }

    /** @return array{events: array<int, array{value: string, label: string}>, promo_codes: array<int, string>} */
    public function options(): array
    {
        $eventSlugs = Room::query()->distinct()->orderBy('event_slug')->pluck('event_slug')
            ->merge(array_keys((array) config('express.events', [])))->unique()->sort()->values();

        return [
            'events' => $eventSlugs->map(function (string $slug): array {
                $event = ExpressEvents::find($slug);

                return ['value' => $slug, 'label' => $event['name'] ?? $slug];
            })->all(),
            'promo_codes' => Room::query()->whereNotNull('promo_code')->distinct()->orderBy('promo_code')->pluck('promo_code')->all(),
        ];
    }

    private function campaignRows(Collection $rooms, Collection $players, Collection $clicks): array
    {
        return $rooms
            ->groupBy(fn (Room $room) => implode('|', [
                $room->event_slug,
                $room->promo_code ?? '',
                $room->campaign_snapshotted ? '1' : '0',
            ]))
            ->map(function (Collection $group) use ($players, $clicks): array {
                /** @var Room $first */
                $first = $group->first();
                $ids = $group->pluck('id');
                $event = ExpressEvents::find($first->event_slug);

                return [
                    'event_slug' => $first->event_slug,
                    'event_name' => $event['name'] ?? $first->event_slug,
                    'promo_code' => $first->promo_code,
                    'legacy' => ! $first->campaign_snapshotted,
                    'utm_campaign' => $first->utm_campaign,
                    'rooms' => $group->count(),
                    'finished' => $group->where('status', Room::FINISHED)->count(),
                    'players' => $players->whereIn('room_id', $ids)->count(),
                    'cta_clicks' => $clicks->whereIn('room_id', $ids)->count(),
                ];
            })
            ->sortByDesc('rooms')
            ->values()
            ->all();
    }

    private function caseRows(Collection $rooms, Collection $players, Collection $accusations): array
    {
        return $rooms->groupBy('case_slug')->map(function (Collection $group, string $slug) use ($players, $accusations): array {
            $ids = $group->pluck('id');
            $scores = $accusations->whereIn('room_id', $ids)->whereNotNull('score_total');
            $case = app(ExpressCaseRegistry::class)->find($slug);

            return [
                'slug' => $slug,
                'name' => $case?->title() ?? $slug,
                'rooms' => $group->count(),
                'finished' => $group->where('status', Room::FINISHED)->count(),
                'players' => $players->whereIn('room_id', $ids)->count(),
                'average_score' => $scores->isEmpty() ? null : round((float) $scores->avg('score_total'), 1),
            ];
        })->sortByDesc('rooms')->values()->all();
    }
}
