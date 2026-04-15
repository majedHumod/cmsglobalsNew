<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class BillingContact extends Model
{
    protected $connection = 'system';
    protected $table = 'billing_contacts';
    protected $fillable = [
        'tenant_id', 'email', 'name', 'address', 'tax_id'
    ];
    protected $casts = [
        'address' => 'array',
    ];
}

