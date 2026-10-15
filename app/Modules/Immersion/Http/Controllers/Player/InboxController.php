<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\GameSnapshot;
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
            'game' => $player->game->forPlayerView(),
            'case' => [
                'name' => $case->name(),
                'code' => $case->code(),
            ],
            'items' => fn () => GameSnapshot::enabled()
                ? $this->itemsFromSnapshot($player)
                : $player->inboxEvents()->map(function ($event) use ($case) {
                    return [
                        // The audio script is what the player is meant to HEAR and
                        // the source file is an internal path; neither belongs in
                        // the payload.
                        'event' => $event->only([
                            'id', 'type', 'title', 'sent_at', 'cta_interrogation',
                        ]),
                        'body_html' => $case->renderEventBody($event->source_file, $event->body_markdown, (int) $event->trigger_offset_minutes),
                        'gallery' => $case->galleryFor($event->source_file),
                        'audio_url' => $this->staticAudioUrl($event),
                    ];
                })->values(),
        ]);
    }

    /**
     * The same items, from what the whole table shares: the envelopes already
     * sent, with their body rendered once for everybody.
     */
    private function itemsFromSnapshot(Player $player)
    {
        $rendered = GameSnapshot::inbox($player->game);

        return collect(GameSnapshot::visibleTo($player, GameSnapshot::timeline((int) $player->game_id)))
            ->filter(fn ($event) => in_array($event->type, ['email', 'audio_email'], true))
            ->map(fn ($event) => [
                'event' => $event->only(['id', 'type', 'title', 'sent_at', 'cta_interrogation']),
                'body_html' => $rendered[$event->id]['body_html'] ?? null,
                'gallery' => $rendered[$event->id]['gallery'] ?? [],
                'audio_url' => $this->staticAudioUrl($event),
            ])
            ->values();
    }

    /**
     * A recording that ships with the case is a file under public/, already
     * reachable by anyone who knows its path. Playing it through the token route
     * made every range request the browser sends while buffering or seeking (a
     * handful per play) a PHP request with a database connection behind it, for
     * a file the web server can hand over by itself. Generated audio lives in
     * storage, out of the web root, and still goes through the route.
     */
    private function staticAudioUrl($event): ?string
    {
        return $event->audioIsCaseAsset() ? asset($event->audio_path) : null;
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

        abort_if(! $timelineEvent, 404);

        // Resolved by the event: case audio ships with the case, generated
        // audio lives in storage. Either way it is only reachable with this
        // player's own token.
        $path = $timelineEvent->audioAbsolutePath();
        abort_unless($path, 404);

        return response()
            ->file($path, [
                'Content-Type' => $timelineEvent->audioMimeType(),
                // The recording for an event never changes, and it is only
                // reachable with the player's own token.
                'Cache-Control' => 'private, max-age=86400',
            ])
            ->setAutoLastModified();
    }
}
