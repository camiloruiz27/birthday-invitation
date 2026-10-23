<?php

namespace App\Modules\Express\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A visitor who left a way to reach them and authorised its use (Ley 1581 de
 * 2012). The contact is encrypted at rest; `contact_hash` only lets the same
 * person be recognised within an event.
 */
class Lead extends Model
{
    protected $table = 'express_leads';

    protected $fillable = [
        'event_slug', 'nickname', 'contact_type', 'contact', 'contact_hash',
        'consent_at', 'consent_version', 'attribution',
    ];

    protected $hidden = ['contact', 'contact_hash'];

    protected $casts = [
        'contact' => 'encrypted',
        'consent_at' => 'datetime',
        'attribution' => 'array',
    ];
}
