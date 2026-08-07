<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleExperienceProgress extends Model
{
    use HasFactory;

    protected $table = 'couple_experience_progress';

    protected $fillable = [
        'user_id',
        'day',
        'level',
        'activity_key',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
