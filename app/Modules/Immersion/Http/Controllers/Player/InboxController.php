<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InboxController extends Controller
{
    public function show(Player $player): InertiaResponse
    {
        $case = $player->game->caseDefinition();

        return Inertia::render('Player/Inbox', [
            'player' => $player->revealCredentials(),
            'game' => $player->game,
            'case' => [
                'name' => $case->name(),
                'code' => $case->code(),
            ],
            'items' => fn () => $player->inboxEvents()->map(function ($event) use ($case) {
                return [
                    // The audio script is what the player is meant to HEAR and
                    // the source file is an internal path; neither belongs in
                    // the payload.
                    'event' => $event->only([
                        'id', 'type', 'title', 'sent_at', 'cta_interrogation',
                    ]),
                    'body_html' => $case->renderEventBody($event->source_file, $event->body_markdown),
                    'gallery' => $case->galleryFor($event->source_file),
                ];
            })->values(),
        ]);
    }

    public function audio(Player $player, int $event): Response
    {
        $timelineEvent = $player->inboxEvents()->firstWhere('id', $event);

        abort_if(! $timelineEvent || ! $timelineEvent->audio_path, 404);
        abort_unless(Storage::disk('local')->exists($timelineEvent->audio_path), 404);

        return response(Storage::disk('local')->get($timelineEvent->audio_path), 200, [
            'Content-Type' => 'audio/wav',
        ]);
    }
}
