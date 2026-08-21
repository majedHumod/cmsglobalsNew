<?php

namespace Database\Seeders\Tenants;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الصلاحيات الأساسية
        $permissions = [
            // صلاحيات الصفحات
            'create pages',
            'edit pages',
            'delete pages',
            'view pages',
            'publish pages',
            
            // صلاحيات المقالات
            'create posts',
            'edit posts',
            'delete posts',
            'view posts',
            
            // صلاحيات الملاحظات
            'create notes',
            'edit notes',
            'delete notes',
            'view notes',
            
            // صلاحيات الوجبات
            'create meal-plans',
            'edit meal-plans',
            'delete meal-plans',
            'view meal-plans',
            'publish meal-plans',

            // صلاحيات التدريب والمتابعة
            'create workouts',
            'edit workouts',
            'delete workouts',
            'view workouts',
            'create workout-schedules',
            'edit workout-schedules',
            'delete workout-schedules',
            'view workout-schedules',
            'create training-sessions',
            'edit training-sessions',
            'delete training-sessions',
            'view training-sessions',
            'manage session-bookings',
            'view session-bookings',
            'manage clients',
            'view client-progress',
            'create client-progress',
            'edit client-progress',
            'manage coach-availability',
            
            // صلاحيات المستخدمين
            'manage users',
            'manage roles',
            'manage permissions',
            
            // صلاحيات العضويات
            'manage membership-types',
            'create membership-types',
            'edit membership-types',
            'delete membership-types',
            'view membership-types',
            'manage subscription-plans',
            'create subscription-plans',
            'edit subscription-plans',
            'delete subscription-plans',
            'view subscription-plans',
            
            // صلاحيات النظام المتقدمة
            'view advanced permissions',
            'manage advanced permissions',
            'grant permission overrides',
            'revoke permission overrides',
            'view permission reports',
            'manage permission groups',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // إنشاء الأدوار
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web'
        ]);

        $clientRole = Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => 'web'
        ]);

        $coachRole = Role::firstOrCreate([
            'name' => 'coach',
            'guard_name' => 'web'
        ]);

        $pageManagerRole = Role::firstOrCreate([
            'name' => 'page_manager',
            'guard_name' => 'web'
        ]);

        // منح جميع الصلاحيات للأدمن
        $adminRole->syncPermissions(Permission::all());

        // منح صلاحيات أساسية للمستخدم العادي (عرض الصفحات العامة يمر عبر Page::canAccess وليس view pages)
        $userRole->syncPermissions([
            'view notes',
            'create notes',
            'edit notes',
            'view meal-plans',
            'create meal-plans',
            'edit meal-plans',
        ]);

        $clientRole->syncPermissions($userRole->permissions);

        $coachRole->syncPermissions([
            'view meal-plans',
            'create meal-plans',
            'edit meal-plans',
            'publish meal-plans',
            'create workouts',
            'edit workouts',
            'delete workouts',
            'view workouts',
            'create workout-schedules',
            'edit workout-schedules',
            'delete workout-schedules',
            'view workout-schedules',
            'create training-sessions',
            'edit training-sessions',
            'delete training-sessions',
            'view training-sessions',
            'manage session-bookings',
            'view session-bookings',
            'manage clients',
            'view client-progress',
            'create client-progress',
            'edit client-progress',
            'manage coach-availability',
        ]);

        // منح صلاحيات إدارة الصفحات لمدير الصفحات
        $pageManagerRole->syncPermissions([
            'create pages',
            'edit pages',
            'delete pages',
            'view pages',
            'publish pages'
        ]);

        if ($this->command) {
            $this->command->info('✅ تم إنشاء الصلاحيات والأدوار بنجاح');
        }
    }
}