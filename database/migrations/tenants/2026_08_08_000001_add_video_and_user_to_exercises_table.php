<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (! Schema::hasColumn('exercises', 'video_url')) {
                $table->string('video_url', 500)->nullable()->after('image_peak_path');
            }
            if (! Schema::hasColumn('exercises', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('source')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (Schema::hasColumn('exercises', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            if (Schema::hasColumn('exercises', 'video_url')) {
                $table->dropColumn('video_url');
            }
        });
    }
};
