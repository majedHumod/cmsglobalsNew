<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            if (! Schema::hasColumn('workouts', 'image')) {
                $table->string('image')->nullable()->after('video_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            if (Schema::hasColumn('workouts', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
