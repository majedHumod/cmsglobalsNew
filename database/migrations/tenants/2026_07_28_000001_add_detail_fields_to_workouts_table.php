<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            if (! Schema::hasColumn('workouts', 'exercise_count')) {
                $table->unsignedSmallInteger('exercise_count')->nullable()->after('duration');
            }
            if (! Schema::hasColumn('workouts', 'equipment_label')) {
                $table->string('equipment_label')->nullable()->after('exercise_count');
            }
            if (! Schema::hasColumn('workouts', 'video_duration_seconds')) {
                $table->unsignedInteger('video_duration_seconds')->nullable()->after('video_url');
            }
            if (! Schema::hasColumn('workouts', 'coach_notes')) {
                $table->json('coach_notes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            foreach (['exercise_count', 'equipment_label', 'video_duration_seconds', 'coach_notes'] as $column) {
                if (Schema::hasColumn('workouts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
