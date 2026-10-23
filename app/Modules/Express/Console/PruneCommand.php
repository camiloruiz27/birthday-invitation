<?php

namespace App\Modules\Express\Console;

use App\Modules\Express\Models\Lead;
use App\Modules\Express\Models\Room;
use App\Modules\Express\Support\RoomSnapshot;
use Illuminate\Console\Command;

/**
 * Housekeeping, hourly: rooms nobody finished are marked expired (their cache
 * snapshot is dropped), and contacts older than the retention period are
 * deleted. Runs from the 5-minute `schedule:run`, not as its own cron job.
 */
class PruneCommand extends Command
{
    protected $signature = 'express:prune';

    protected $description = 'Expire stale Express rooms and delete contacts past their retention period';

    public function handle(): int
    {
        $stale = Room::query()
            ->whereIn('status', [Room::LOBBY, Room::RUNNING])
            ->where('created_at', '<', now()->subHours((int) config('express.room_ttl_hours')))
            ->get(['id']);

        foreach ($stale as $room) {
            Room::query()->whereKey($room->id)->update(['status' => Room::EXPIRED]);
            RoomSnapshot::forget((int) $room->id);
        }

        $months = (int) config('express.consent.retention_months');
        $purged = $months > 0
            ? Lead::query()->where('created_at', '<', now()->subMonths($months))->delete()
            : 0;

        $this->info("Expired {$stale->count()} room(s); deleted {$purged} contact(s).");

        return self::SUCCESS;
    }
}
