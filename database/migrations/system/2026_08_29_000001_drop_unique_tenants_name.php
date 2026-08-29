<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('system')->table('tenants', function (Blueprint $table) {
            $table->dropUnique('tenants_name_unique');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::connection('system')->table('tenants', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->unique('name');
        });
    }
};
