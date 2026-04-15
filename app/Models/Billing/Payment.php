<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $connection = 'system';
    protected $table = 'payments';
    protected $fillable = [
        'tenant_id', 'provider_payment_intent_id', 'amount', 'currency', 'status',
        'paid_at', 'method_details', 'receipt_url'
    ];
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'method_details' => 'array',
    ];
}

