<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repair/ensure advanced permission tables exist.
 * Older tenants may have recorded 2025_01_19 as ran while skipping
 * user_permission_overrides when permissions/users were not ready yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permission_groups')) {
            Schema::create('permission_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color', 7)->default('#6366f1');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['is_active', 'sort_order']);
            });
        }

        if (! Schema::hasTable('permission_categories')) {
            Schema::create('permission_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('permission_group_id')->constrained()->onDelete('cascade');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['permission_group_id', 'is_active', 'sort_order'], 'perm_cat_group_idx');
            });
        }

        if (
            Schema::hasTable('permissions')
            && Schema::hasTable('users')
            && ! Schema::hasTable('user_permission_overrides')
        ) {
            Schema::create('user_permission_overrides', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['grant', 'deny']);
                $table->text('reason')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'permission_id']);
                $table->index(['user_id', 'is_active']);
                $table->index(['expires_at', 'is_active']);
            });
        }

        if (! Schema::hasTable('permission_audit_logs')) {
            Schema::create('permission_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('auditable_type');
                $table->unsignedBigInteger('auditable_id');
                $table->string('action');
                $table->string('permission_name');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->index(['auditable_type', 'auditable_id']);
                $table->index(['action', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (Schema::hasTable('roles') && ! Schema::hasTable('role_hierarchies')) {
            Schema::create('role_hierarchies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_role_id')->constrained('roles')->onDelete('cascade');
                $table->foreignId('child_role_id')->constrained('roles')->onDelete('cascade');
                $table->integer('level')->default(1);
                $table->timestamps();

                $table->unique(['parent_role_id', 'child_role_id']);
                $table->index(['parent_role_id', 'level']);
            });
        }

        if (Schema::hasTable('permissions') && ! Schema::hasTable('permission_dependencies')) {
            Schema::create('permission_dependencies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('depends_on_permission_id')->constrained('permissions')->onDelete('cascade');
                $table->enum('dependency_type', ['required', 'recommended', 'conflicting'])->default('required');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['permission_id', 'depends_on_permission_id'], 'perm_deps_perm_dep_unique');
                $table->index(['permission_id', 'dependency_type']);
            });
        }
    }

    public function down(): void
    {
        // Non-destructive repair migration — do not drop tables on rollback.
    }
};
