<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('client_profiles', 'program_started_at')) {
                $table->timestamp('program_started_at')->nullable()->after('current_program_week');
            }
            if (! Schema::hasColumn('client_profiles', 'week_advance_mode')) {
                $table->string('week_advance_mode', 20)->nullable()->after('program_started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('client_profiles', 'week_advance_mode')) {
                $table->dropColumn('week_advance_mode');
            }
            if (Schema::hasColumn('client_profiles', 'program_started_at')) {
                $table->dropColumn('program_started_at');
            }
        });
    }
};
