<?php

namespace App\Modules\Express\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A question the team spent from its budget. While `answer` is null the
 * question is in flight (the AI has not replied yet): it already counts
 * against the budget, but nobody sees it until it is answered.
 */
class Question extends Model
{
    public const AI = 'ai';

    public const AUTHORED = 'authored';

    protected $table = 'express_questions';

    protected $fillable = ['room_id', 'player_id', 'suspect_id', 'topic_id', 'question', 'answer', 'source'];
}
