<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class SignupReservation extends Model
{
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const PROVISIONED = 'provisioned';
    public const RELEASED = 'released';
    public const CONFLICT = 'conflict';

    protected $connection = 'system';
    protected $table = 'signup_reservations';

    protected $fillable = [
        'order_number', 'slug', 'email', 'mobile_e164', 'plan_code', 'status',
        'active_slug', 'active_email', 'tenant_id', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
