<?php

namespace App\Modules\Immersion\Models;

use App\Modules\Immersion\Support\GameRevision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterrogationMessage extends Model
{
    protected $table = 'immersion_interrogation_messages';

    protected $fillable = [
        'session_id',
        'role',
        'content',
    ];

    protected static function booted(): void
    {
        // The transcript moved: its own stamp, and the readers' copy is rebuilt.
        static::saved(fn (self $message) => GameRevision::bumpSession((int) $message->session_id));
        static::deleted(fn (self $message) => GameRevision::bumpSession((int) $message->session_id));
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(InterrogationSession::class, 'session_id');
    }
}
