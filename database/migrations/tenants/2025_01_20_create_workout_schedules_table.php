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
          # إنشاء جدول جدولة التمارين

          1. الجداول الجديدة
            - `workout_schedules`
              - `id` (bigint, primary key)
              - `workout_id` (bigint, foreign key)
              - `week_number` (integer, رقم الأسبوع)
              - `session_number` (integer, رقم الجلسة داخل الأسبوع)
              - `notes` (text, ملاحظات إضافية)
              - `status` (boolean, نشط/غير نشط)
              - `user_id` (bigint, foreign key)
              - `created_at` (timestamp)
              - `updated_at` (timestamp)

          2. الأمان
            - مفاتيح خارجية للتمرين والمستخدم
            - فهرسة على الحقول المهمة
            - قيود فريدة لمنع التكرار
        */

        Schema::create('workout_schedules', function (Blueprint $table) {
            $table->id();
            // نستخدم عموداً عادياً لتفادي مشاكل الترتيب مع ميجريشن workouts
            $table->unsignedBigInteger('workout_id'); // ربط بالتمرين (FK يضاف لاحقاً)
            $table->integer('week_number'); // رقم الأسبوع
            $table->integer('session_number'); // رقم الجلسة داخل الأسبوع
            $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->boolean('status')->default(true); // نشط/غير نشط
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المدرب الذي أنشأ الجدولة
            $table->timestamps();
            
            // فهارس للأداء
            $table->index(['week_number', 'session_number']);
            $table->index(['workout_id', 'status']);
            $table->index(['user_id', 'status']);
            
            // قيد فريد لمنع تكرار نفس التمرين في نفس الأسبوع والجلسة
            $table->unique(['workout_id', 'week_number', 'session_number'], 'workout_schedule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_schedules');
    }
};