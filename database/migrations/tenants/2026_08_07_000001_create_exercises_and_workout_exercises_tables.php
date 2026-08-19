<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exercises')) {
            Schema::create('exercises', function (Blueprint $table) {
                $table->id();
                $table->string('external_id')->unique();
                $table->string('source')->default('repdb');
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('instructions')->nullable();
                $table->json('translations')->nullable();
                $table->string('category')->nullable();
                $table->string('difficulty')->nullable();
                $table->string('equipment')->nullable();
                $table->string('body_part')->nullable();
                $table->json('primary_muscles')->nullable();
                $table->json('secondary_muscles')->nullable();
                $table->json('tags')->nullable();
                $table->decimal('met', 5, 2)->nullable();
                $table->string('image_start_path')->nullable();
                $table->string('image_peak_path')->nullable();
                $table->boolean('attribution_required')->default(true);
                $table->string('attribution_text')->nullable();
                $table->string('attribution_url')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();

                $table->index(['body_part', 'status']);
                $table->index(['equipment', 'status']);
                $table->index(['difficulty', 'status']);
                $table->index(['source', 'status']);
            });
        }

        if (! Schema::hasTable('workout_exercises')) {
            Schema::create('workout_exercises', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workout_id')->constrained('workouts')->cascadeOnDelete();
                $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->unsignedSmallInteger('sets')->nullable();
                $table->string('reps')->nullable();
                $table->unsignedSmallInteger('rest_seconds')->nullable();
                $table->string('coach_cue')->nullable();
                $table->timestamps();

                $table->unique(['workout_id', 'sort_order']);
                $table->index(['workout_id', 'exercise_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
        Schema::dropIfExists('exercises');
    }
};
