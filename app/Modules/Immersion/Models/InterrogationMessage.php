<?php

namespace App\Modules\Immersion\Models;

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

    public function session(): BelongsTo
    {
        return $this->belongsTo(InterrogationSession::class, 'session_id');
    }
}
