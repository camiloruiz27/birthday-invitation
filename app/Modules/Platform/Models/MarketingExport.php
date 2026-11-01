<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Records each CSV prepared for Brevo; campaigns are never sent here. */
class MarketingExport extends Model
{
    protected $fillable = ['exported_by', 'contacts_count', 'filters', 'exported_at'];

    protected $casts = [
        'contacts_count' => 'integer',
        'filters' => 'array',
        'exported_at' => 'datetime',
    ];

    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}
