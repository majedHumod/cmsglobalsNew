<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantDatabasePool extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ALLOCATED = 'allocated';
    public const STATUS_MAINTENANCE = 'maintenance';

    protected $connection = 'system';

    protected $fillable = [
        'db_name',
        'label',
        'status',
        'is_ready',
        'tenant_id',
        'allocated_at',
    ];

    protected $casts = [
        'is_ready' => 'boolean',
        'allocated_at' => 'datetime',
    ];

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_AVAILABLE)
            ->whereNull('tenant_id');
    }
}
