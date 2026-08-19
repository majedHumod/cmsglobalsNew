<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workout_logs')) {
            return;
        }

        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_on');
            $table->enum('status', ['completed', 'skipped'])->default('completed');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'workout_schedule_id', 'scheduled_on'], 'workout_logs_user_schedule_date_unique');
            $table->index(['user_id', 'scheduled_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
    }
};
