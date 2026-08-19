<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'coach_id')) {
                $table->foreignId('coach_id')
                    ->nullable()
                    ->after('membership_expires_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        if (! Schema::hasTable('client_profiles')) {
            Schema::create('client_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->text('fitness_goal')->nullable();
                $table->decimal('target_weight', 8, 2)->nullable();
                $table->enum('activity_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
                $table->enum('preferred_contact_method', ['whatsapp', 'sms', 'email', 'phone'])->default('whatsapp');
                $table->text('injuries')->nullable();
                $table->text('medical_notes')->nullable();
                $table->text('onboarding_notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('progress_check_ins')) {
            Schema::create('progress_check_ins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('checked_in_at');
                $table->decimal('weight', 8, 2)->nullable();
                $table->decimal('body_fat_percentage', 5, 2)->nullable();
                $table->decimal('waist_cm', 8, 2)->nullable();
                $table->decimal('chest_cm', 8, 2)->nullable();
                $table->decimal('hips_cm', 8, 2)->nullable();
                $table->decimal('arm_cm', 8, 2)->nullable();
                $table->decimal('thigh_cm', 8, 2)->nullable();
                $table->string('progress_photo_path')->nullable();
                $table->unsignedTinyInteger('energy_level')->nullable();
                $table->unsignedTinyInteger('training_adherence')->nullable();
                $table->unsignedTinyInteger('nutrition_adherence')->nullable();
                $table->text('notes')->nullable();
                $table->text('coach_feedback')->nullable();
                $table->text('next_steps')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'checked_in_at']);
                $table->index(['coach_id', 'checked_in_at']);
            });
        }

        if (! Schema::hasTable('coach_availabilities')) {
            Schema::create('coach_availabilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('day_of_week');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedSmallInteger('slot_duration_minutes')->default(60);
                $table->unsignedSmallInteger('buffer_minutes')->default(0);
                $table->string('location')->nullable();
                $table->unsignedInteger('capacity')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'day_of_week', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_availabilities');
        Schema::dropIfExists('progress_check_ins');
        Schema::dropIfExists('client_profiles');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'coach_id')) {
                $table->dropForeign(['coach_id']);
                $table->dropColumn('coach_id');
            }
        });
    }
};
