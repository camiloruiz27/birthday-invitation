<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\CaseFileReader;
use App\Modules\Immersion\Support\CaseGallery;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InboxController extends Controller
{
    public function show(Player $player): InertiaResponse
    {
        return Inertia::render('Player/Inbox', [
            'player' => $player,
            'game' => $player->game,
            'items' => fn () => $player->inboxEvents()->map(function ($event) {
                return [
                    'event' => $event,
                    'body_html' => $event->source_file
                        ? CaseFileReader::renderFileExcludingSections(
                            $event->source_file,
                            CaseGallery::excludedHeadings($event->source_file)
                        )
                        : CaseFileReader::renderMarkdown((string) $event->body_markdown),
                    'gallery' => $event->source_file
                        ? CaseGallery::forSourceFile($event->source_file)
                        : [],
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
