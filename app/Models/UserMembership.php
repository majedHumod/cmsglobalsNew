<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_type_id',
        'subscription_plan_id',
        'starts_at',
        'expires_at',
        'is_active',
        'payment_status',
        'payment_amount',
        'payment_reference',
        'stripe_payment_intent_id',
        'notes'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'payment_amount' => 'decimal:2',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Accessors
    public function getIsValidAttribute()
    {
        return $this->is_active && $this->expires_at > now();
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at <= now();
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->is_expired) {
            return 0;
        }
        
        return now()->diffInDays($this->expires_at);
    }

    public function getStatusTextAttribute()
    {
        if (!$this->is_active) {
            return 'غير نشط';
        }
        
        if ($this->is_expired) {
            return 'منتهي الصلاحية';
        }
        
        if ($this->payment_status !== 'paid') {
            return 'في انتظار الدفع';
        }
        
        return 'نشط';
    }

    public function getStatusBadgeAttribute()
    {
        if (!$this->is_active) {
            return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">⏸️ غير نشط</span>';
        }
        
        if ($this->is_expired) {
            return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">⏰ منتهي</span>';
        }
        
        if ($this->payment_status !== 'paid') {
            return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">💳 في انتظار الدفع</span>';
        }
        
        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✅ نشط</span>';
    }

    // Methods
    public function extend($days)
    {
        $this->expires_at = $this->expires_at->addDays($days);
        $this->save();
    }

    public function activate()
    {
        $this->is_active = true;
        $this->save();
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }

    public function markAsPaid($amount = null, $reference = null)
    {
        $this->payment_status = 'paid';
        if ($amount) {
            $this->payment_amount = $amount;
        }
        if ($reference) {
            $this->payment_reference = $reference;
        }
        $this->save();
    }
}