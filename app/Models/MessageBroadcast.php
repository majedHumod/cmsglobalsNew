<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageBroadcast extends Model
{
    use HasFactory;

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'sender_user_id',
        'template_id',
        'title',
        'body',
        'segment_type',
        'segment_filters',
        'recipients_count',
        'status',
        'delivered_count',
        'failed_count',
        'sent_at',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'segment_filters' => 'array',
        'sent_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'recipients_count' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function recipients()
    {
        return $this->hasMany(MessageBroadcastRecipient::class, 'broadcast_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_PARTIAL,
            self::STATUS_FAILED,
        ], true);
    }

    public function progressPercent(): int
    {
        $total = max(1, (int) $this->recipients_count);

        return (int) round((((int) $this->delivered_count + (int) $this->failed_count) / $total) * 100);
    }
}
