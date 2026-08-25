<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'system';

    protected $fillable = [
        'name',
        'slug',
        'join_code',
        'domain',
        'subdomain',
        'email',
        'phone',
        'logo',
        'status',
        'access_status',
        'database_status',
        'schema_status',
        'recommended_action',
        'status_note',
        'last_audited_at',
        'trial_ends_at',
        'subscription_ends_at',
        'grace_ends_at',
        'suspended_at',
        'archived_at',
        'db_name',
    ];

    protected $hidden = [
        'db_name',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'last_audited_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'archived_at' => 'datetime',
    ];
}
