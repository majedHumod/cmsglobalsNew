<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $connection = 'system';
    protected $table = 'plans';
    protected $fillable = [
        'code', 'name', 'price', 'interval', 'currency', 'features', 'active'
    ];
    protected $casts = [
        'features' => 'array',
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];
}

