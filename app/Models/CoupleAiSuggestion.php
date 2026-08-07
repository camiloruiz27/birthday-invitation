<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleAiSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'level',
        'location',
        'mood',
        'intention',
        'prompt_payload',
        'suggestion',
        'status',
    ];

    protected $casts = [
        'prompt_payload' => 'array',
        'suggestion' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
