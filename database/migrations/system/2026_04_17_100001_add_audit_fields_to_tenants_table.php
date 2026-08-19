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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'database_status')) {
                $table->string('database_status')->default('unknown')->after('status');
            }

            if (!Schema::hasColumn('tenants', 'schema_status')) {
                $table->string('schema_status')->default('unknown')->after('database_status');
            }

            if (!Schema::hasColumn('tenants', 'recommended_action')) {
                $table->string('recommended_action')->nullable()->after('schema_status');
            }

            if (!Schema::hasColumn('tenants', 'status_note')) {
                $table->text('status_note')->nullable()->after('recommended_action');
            }

            if (!Schema::hasColumn('tenants', 'last_audited_at')) {
                $table->timestamp('last_audited_at')->nullable()->after('status_note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columns = [
                'database_status',
                'schema_status',
                'recommended_action',
                'status_note',
                'last_audited_at',
            ];

            $existingColumns = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('tenants', $column)));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
