<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use App\Services\MembershipAccessService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;


class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'gender',
        'membership_type_id',
        'membership_expires_at',
        'coach_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'gender' => 'string',
            'membership_type_id' => 'integer',
            'membership_expires_at' => 'datetime',
            'coach_id' => 'integer',
        ];
    }

    /**
     * العلاقة مع نوع العضوية
     */
    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class);
    }

    /**
     * العلاقة مع اشتراكات المستخدم
     */
    public function memberships()
    {
        return $this->hasMany(UserMembership::class);
    }

    public function coach()
    {
        return $this->belongsTo(self::class, 'coach_id');
    }

    public function clients()
    {
        return $this->hasMany(self::class, 'coach_id');
    }

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function progressCheckIns()
    {
        return $this->hasMany(ProgressCheckIn::class)->latest('checked_in_at');
    }

    public function workoutLogs()
    {
        return $this->hasMany(WorkoutLog::class);
    }

    public function coachCheckIns()
    {
        return $this->hasMany(ProgressCheckIn::class, 'coach_id')->latest('checked_in_at');
    }

    public function coachAvailabilities()
    {
        return $this->hasMany(CoachAvailability::class)->orderBy('day_of_week')->orderBy('start_time');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_user_id');
    }

    public function assignedHabits()
    {
        return $this->hasMany(Habit::class, 'client_user_id');
    }

    public function assignedMealPlans()
    {
        return $this->belongsToMany(MealPlan::class, 'client_meal_plans', 'user_id', 'meal_plan_id')
            ->withPivot(['id', 'assigned_by', 'meal_slot', 'sort_order', 'notes', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    public function clientMealAssignments()
    {
        return $this->hasMany(ClientMealPlan::class, 'user_id');
    }

    public function createdHabits()
    {
        return $this->hasMany(Habit::class, 'created_by_user_id');
    }

    public function habitLogs()
    {
        return $this->hasMany(HabitLog::class);
    }

    public function feedNotifications()
    {
        return $this->hasMany(NotificationFeed::class);
    }

    public function messageTemplates()
    {
        return $this->hasMany(MessageTemplate::class, 'created_by_user_id');
    }

    public function sentBroadcasts()
    {
        return $this->hasMany(MessageBroadcast::class, 'sender_user_id');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function communityComments()
    {
        return $this->hasMany(CommunityComment::class);
    }

    public function communityReactions()
    {
        return $this->hasMany(CommunityReaction::class);
    }

    public function badges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function challengeParticipations()
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function subscriptionPlans()
    {
        return $this->hasManyThrough(
            SubscriptionPlan::class,
            UserMembership::class,
            'user_id',
            'id',
            'id',
            'subscription_plan_id'
        );
    }

    /**
     * الحصول على الاشتراك النشط للمستخدم
     */
    public function activeMembership()
    {
        return $this->memberships()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $roleQuery) {
            $roleQuery->whereIn('name', ['user', 'client']);
        });
    }

    public function scopeCoaches(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $roleQuery) {
            $roleQuery->where('name', 'coach');
        });
    }

    public function hasTraineeRole(): bool
    {
        return MembershipAccessService::hasTraineeRole($this);
    }

    public function currentMembershipTypeId(): ?int
    {
        return MembershipAccessService::currentMembershipTypeId($this);
    }

    public function isCoachOf(self $client): bool
    {
        return $this->hasRole('coach') && (int) $client->coach_id === (int) $this->id;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'cms') {
            return false;
        }

        return $this->hasAnyRole(['admin', 'coach']);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        app()->setLocale('ar');
        $this->notify(new ResetPasswordNotification($token));
    }
}
