<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
          # تحسين نظام الصلاحيات المتقدم

          1. الجداول الجديدة
            - `permission_groups` - مجموعات الصلاحيات
            - `permission_categories` - تصنيفات الصلاحيات
            - `user_permission_overrides` - تجاوزات صلاحيات المستخدمين
            - `role_hierarchies` - هرمية الأدوار
            - `permission_dependencies` - تبعيات الصلاحيات

          2. التحسينات
            - إضافة مستويات الصلاحيات
            - نظام انتهاء الصلاحيات
            - صلاحيات مشروطة
            - تسجيل تغييرات الصلاحيات
        */

        // مجموعات الصلاحيات
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المجموعة
            $table->string('slug')->unique(); // معرف فريد
            $table->text('description')->nullable(); // وصف المجموعة
            $table->string('icon')->nullable(); // أيقونة المجموعة
            $table->string('color', 7)->default('#6366f1'); // لون المجموعة
            $table->integer('sort_order')->default(0); // ترتيب العرض
            $table->boolean('is_active')->default(true); // حالة النشاط
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
        });

        // تصنيفات الصلاحيات
        Schema::create('permission_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // اسم التصنيف
        $table->string('slug')->unique(); // معرف فريد
        $table->text('description')->nullable(); // وصف التصنيف
        $table->foreignId('permission_group_id')->constrained()->onDelete('cascade'); // المجموعة
        $table->integer('sort_order')->default(0); // ترتيب العرض
        $table->boolean('is_active')->default(true); // حالة النشاط
        $table->timestamps();
        
        // استخدام اسم فهرس مخصص لتفادي تجاوز الطول
        $table->index(['permission_group_id', 'is_active', 'sort_order'], 'perm_cat_group_idx');
        });

        // إضافة حقول جديدة لجدول الصلاحيات الموجود (شرطياً إذا كان الجدول موجوداً)
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('permissions', 'permission_category_id')) {
                    $table->foreignId('permission_category_id')->nullable()->constrained()->onDelete('set null');
                }
                if (!Schema::hasColumn('permissions', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('permissions', 'level')) {
                    $table->enum('level', ['basic', 'intermediate', 'advanced', 'critical'])->default('basic');
                }
                if (!Schema::hasColumn('permissions', 'conditions')) {
                    $table->json('conditions')->nullable();
                }
                if (!Schema::hasColumn('permissions', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable();
                }
                if (!Schema::hasColumn('permissions', 'is_system')) {
                    $table->boolean('is_system')->default(false);
                }
                if (!Schema::hasColumn('permissions', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }
                if (!Schema::hasColumn('permissions', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                $table->index(['permission_category_id', 'is_active']);
                $table->index(['level', 'is_active']);
            });
        }

        // تجاوزات صلاحيات المستخدمين (يتطلب permissions & users)
        if (Schema::hasTable('permissions') && Schema::hasTable('users')) {
            Schema::create('user_permission_overrides', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['grant', 'deny']); // منح أو منع
                $table->text('reason')->nullable(); // سبب التجاوز
                $table->timestamp('expires_at')->nullable(); // تاريخ انتهاء التجاوز
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null'); // من منح التجاوز
                $table->boolean('is_active')->default(true); // حالة النشاط
                $table->timestamps();
                
                $table->unique(['user_id', 'permission_id']);
                $table->index(['user_id', 'is_active']);
                $table->index(['expires_at', 'is_active']);
            });
        }

        // هرمية الأدوار (يتطلب جدول roles من spatie)
        if (Schema::hasTable('roles')) {
            Schema::create('role_hierarchies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('child_role_id')->constrained('roles')->onDelete('cascade');
            $table->integer('level')->default(1); // مستوى الهرمية
            $table->timestamps();
            
            $table->unique(['parent_role_id', 'child_role_id']);
            $table->index(['parent_role_id', 'level']);
            });
        }

        // تبعيات الصلاحيات (يتطلب جدول permissions)
        if (Schema::hasTable('permissions')) {
            Schema::create('permission_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade'); // الصلاحية الأساسية
            $table->foreignId('depends_on_permission_id')->constrained('permissions')->onDelete('cascade'); // الصلاحية المطلوبة
            $table->enum('dependency_type', ['required', 'recommended', 'conflicting'])->default('required'); // نوع التبعية
            $table->text('description')->nullable(); // وصف التبعية
            $table->timestamps();
            
            $table->unique(['permission_id', 'depends_on_permission_id'], 'perm_deps_perm_dep_unique');
            $table->index(['permission_id', 'dependency_type']);
            });
        }


        // سجل تغييرات الصلاحيات
        Schema::create('permission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // نوع الكائن (User, Role, etc.)
            $table->unsignedBigInteger('auditable_id'); // معرف الكائن
            $table->string('action'); // نوع العملية (granted, revoked, updated)
            $table->string('permission_name'); // اسم الصلاحية
            $table->json('old_values')->nullable(); // القيم القديمة
            $table->json('new_values')->nullable(); // القيم الجديدة
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // من قام بالتغيير
            $table->string('ip_address', 45)->nullable(); // عنوان IP
            $table->text('user_agent')->nullable(); // معلومات المتصفح
            $table->text('reason')->nullable(); // سبب التغيير
            $table->timestamps();
            
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // إضافة حقول جديدة لجدول الأدوار الموجود (شرطياً)
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
            $table->text('description')->nullable(); // وصف الدور
            $table->enum('level', ['basic', 'intermediate', 'advanced', 'critical'])->default('basic'); // مستوى الدور
            $table->string('color', 7)->default('#6366f1'); // لون الدور
            $table->string('icon')->nullable(); // أيقونة الدور
            $table->boolean('is_system')->default(false); // دور نظام (لا يمكن حذفه)
            $table->boolean('is_assignable')->default(true); // يمكن تعيينه للمستخدمين
            $table->integer('max_users')->nullable(); // الحد الأقصى للمستخدمين
            $table->timestamp('expires_at')->nullable(); // تاريخ انتهاء الدور
            $table->integer('sort_order')->default(0); // ترتيب العرض
            $table->boolean('is_active')->default(true); // حالة النشاط
            
            $table->index(['level', 'is_active']);
            $table->index(['is_assignable', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_logs');
        Schema::dropIfExists('permission_dependencies');
        Schema::dropIfExists('role_hierarchies');
        Schema::dropIfExists('user_permission_overrides');
        
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_category_id']);
            $table->dropColumn([
                'permission_category_id', 'description', 'level', 'conditions',
                'expires_at', 'is_system', 'sort_order', 'is_active'
            ]);
        });
        
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'level', 'color', 'icon', 'is_system',
                'is_assignable', 'max_users', 'expires_at', 'sort_order', 'is_active'
            ]);
        });
        
        Schema::dropIfExists('permission_categories');
        Schema::dropIfExists('permission_groups');
    }
};