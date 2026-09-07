<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    /**
     * Streams an event's recording.
     *
     * Served as a file rather than read into a string: a WAV is several
     * megabytes, and BinaryFileResponse handles Range requests, which is what
     * lets a phone seek within the audio instead of downloading all of it
     * before playing.
     */
    public function audio(Player $player, int $event): BinaryFileResponse
    {
        $timelineEvent = $player->inboxEvents()->firstWhere('id', $event);

        abort_if(! $timelineEvent || ! $timelineEvent->audio_path, 404);
        abort_unless(Storage::disk('local')->exists($timelineEvent->audio_path), 404);

        return response()
            ->file(Storage::disk('local')->path($timelineEvent->audio_path), [
                'Content-Type' => 'audio/wav',
                // The recording for an event never changes, and it is only
                // reachable with the player's own token.
                'Cache-Control' => 'private, max-age=86400',
            ])
            ->setAutoLastModified();
    }
}
