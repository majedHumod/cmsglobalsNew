<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $connection = 'system';
    protected $table = 'subscriptions';
    protected $fillable = [
        'tenant_id', 'plan_id', 'provider', 'provider_customer_id', 'provider_subscription_id',
        'status', 'current_period_start', 'current_period_end', 'cancel_at_period_end', 'trial_ends_at'
    ];
    protected $casts = [
        'cancel_at_period_end' => 'boolean',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];
}

