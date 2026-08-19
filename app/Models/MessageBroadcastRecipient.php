<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageBroadcastRecipient extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'broadcast_id',
        'recipient_user_id',
        'conversation_id',
        'message_id',
        'status',
        'delivered_at',
        'read_at',
        'error_message',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function broadcast()
    {
        return $this->belongsTo(MessageBroadcast::class, 'broadcast_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
