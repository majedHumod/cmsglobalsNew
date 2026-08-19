<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'pages',
            'workouts',
            'workout_schedules',
            'meal_plans',
            'training_sessions',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'audience_gender')) {
                    $table->enum('audience_gender', ['all', 'male', 'female'])
                        ->nullable()
                        ->after($tableName === 'pages' ? 'required_membership_types' : 'user_id');
                }

                if ($tableName !== 'pages' && ! Schema::hasColumn($tableName, 'required_membership_types')) {
                    $table->json('required_membership_types')->nullable()->after('audience_gender');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'pages',
            'workouts',
            'workout_schedules',
            'meal_plans',
            'training_sessions',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'audience_gender')) {
                    $table->dropColumn('audience_gender');
                }

                if ($tableName !== 'pages' && Schema::hasColumn($tableName, 'required_membership_types')) {
                    $table->dropColumn('required_membership_types');
                }
            });
        }
    }
};
