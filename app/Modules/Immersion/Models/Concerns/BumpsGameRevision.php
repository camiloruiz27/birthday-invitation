<?php

namespace App\Modules\Immersion\Models\Concerns;

use App\Modules\Immersion\Support\GameRevision;

/**
 * Moves the game's change stamp whenever the model is written or removed, so
 * pages polling for news (see GameRevision) know to reload.
 *
 * Writes that skip Eloquent events — query-builder update()/increment() — do
 * not reach this; those call GameRevision::bump() themselves.
 */
trait BumpsGameRevision
{
    public static function bootBumpsGameRevision(): void
    {
        static::saved(fn ($model) => GameRevision::bump($model->revisionGameId(), $model->revisionChannels()));
        static::deleted(fn ($model) => GameRevision::bump($model->revisionGameId(), $model->revisionChannels()));
    }

    /**
     * The screens this model feeds. Override to narrow it: bumping every
     * channel for every write is what made busy games reload every tab.
     *
     * @return array<int, string>
     */
    public function revisionChannels(): array
    {
        return GameRevision::CHANNELS;
    }

    public function revisionGameId(): ?int
    {
        return $this->game_id ? (int) $this->game_id : null;
    }
}
