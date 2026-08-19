<?php

namespace App\Models;

use App\Models\Concerns\HasAudience;
use App\Services\MembershipAccessService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    use HasAudience;

    /**
     * الحقول التي يمكن تعبئتها بشكل جماعي.
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'featured_image',
        'access_level',
        'is_published',
        'is_premium',
        'show_in_menu',
        'menu_order',
        'published_at',
        'user_id',
        'required_membership_types',
        'audience_gender',
    ];

    /**
     * تحويل الحقول إلى أنواع معينة تلقائيًا.
     */
    protected $casts = [
        'is_published' => 'boolean',
        'is_premium' => 'boolean',
        'show_in_menu' => 'boolean',
        'published_at' => 'datetime',
        'audience_gender' => 'string',
    ];

    /**
     * العلاقة مع المستخدم (مالك الصفحة).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope لاسترجاع الصفحات المنشورة فقط.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope لاسترجاع الصفحات التي تظهر في القائمة.
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope للتحقق من إمكانية الوصول حسب المستخدم.
     */
    public function scopeAccessibleBy($query, $user = null)
    {
        if (!$user) {
            // المستخدم غير مسجل الدخول يمكنه رؤية الصفحات العامة فقط
            return $query->where('access_level', 'public')->visibleTo($user);
        }

        if ($user->hasRole('admin')) {
            return $query; // كل الصفحات
        }

        return $query->where(function ($q) use ($user) {
            $q->where('access_level', 'public')
                ->orWhere('access_level', 'authenticated');

            if (MembershipAccessService::hasTraineeRole($user)) {
                $q->orWhere('access_level', 'user');
            }

            if ($user->hasRole('page_manager')) {
                $q->orWhere('access_level', 'page_manager');
            }

            if (MembershipAccessService::currentMembershipTypeIds($user) !== []) {
                $q->orWhere('access_level', 'membership');
            }
        })->visibleTo($user);
    }

    /**
     * تحقق ما إذا كان المستخدم يمكنه الوصول إلى الصفحة.
     */
    public function canAccess($user = null)
    {
        $audienceMatches = MembershipAccessService::matchesGender($user, $this->audience_gender);

        if ($this->access_level === 'public') {
            return $audienceMatches && $this->matchesAudience($user);
        }

        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($this->access_level === 'authenticated' && $user) {
            return $this->matchesAudience($user);
        }

        if ($this->access_level === 'user' && MembershipAccessService::hasTraineeRole($user)) {
            return $this->matchesAudience($user);
        }

        if ($this->access_level === 'page_manager' && $user->hasRole('page_manager')) {
            return $this->matchesAudience($user);
        }

        if ($this->access_level === 'membership') {
            if (! MembershipAccessService::matchesMembershipTypes($user, $this->required_membership_types ?? [])) {
                return false;
            }

            return $this->matchesAudience($user);
        }

        return false;
    }

    /**
     * الحصول على أيقونة مستوى الوصول للعرض في القائمة
     */
    public function getAccessLevelIconAttribute()
    {
        return match($this->access_level) {
            'public' => '🌍',
            'authenticated' => '🔐',
            'user' => '👤',
            'page_manager' => '📝',
            'admin' => '👑',
            'membership' => '💎',
            default => '📄'
        };
    }

    /**
     * الحصول على نص مستوى الوصول للعرض
     */
    public function getAccessLevelTextAttribute()
    {
        return match($this->access_level) {
            'public' => 'عام للجميع',
            'authenticated' => 'المستخدمين المسجلين',
            'user' => 'المستخدمين العاديين',
            'page_manager' => 'مديري الصفحات',
            'admin' => 'المديرين فقط',
            'membership' => 'أعضاء العضويات المدفوعة',
            default => 'غير محدد'
        };
    }
}
