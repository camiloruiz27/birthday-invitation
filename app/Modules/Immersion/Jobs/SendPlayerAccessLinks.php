<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Mail\PlayerAccessLinkMail;
use App\Modules\Immersion\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Emails one or more players their own access link, on demand from the
 * console — "Enviar por correo" for one, "Enviar a todos" for the roster.
 *
 * Same rule as DispatchTimelineEvent: one bounced address must not cost the
 * rest of the table their invite, and the link stays copyable on the console
 * either way.
 */
class SendPlayerAccessLinks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    /** @param array<int, int> $playerIds */
    public function __construct(public array $playerIds)
    {
    }

    public function handle(): void
    {
        $players = Player::whereIn('id', $this->playerIds)->get();

        foreach ($players as $player) {
            try {
                Mail::to($player->email)->send(new PlayerAccessLinkMail($player));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }
}
