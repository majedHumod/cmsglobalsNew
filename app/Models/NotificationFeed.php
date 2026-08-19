<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationFeed extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'notifications_feed';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'payload',
        'read_at',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
