<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('client_profiles', 'current_program_week')) {
                $table->unsignedTinyInteger('current_program_week')
                    ->default(1)
                    ->after('onboarding_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('client_profiles', 'current_program_week')) {
                $table->dropColumn('current_program_week');
            }
        });
    }
};
