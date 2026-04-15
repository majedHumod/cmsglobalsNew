<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $connection = 'system';
    protected $table = 'events';
    protected $fillable = [
        'tenant_id', 'provider_event_id', 'type', 'payload', 'processed_at'
    ];
    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}

