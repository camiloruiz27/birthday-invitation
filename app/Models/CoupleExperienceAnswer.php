<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleExperienceAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'level',
        'activity_key',
        'prompt',
        'answer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
