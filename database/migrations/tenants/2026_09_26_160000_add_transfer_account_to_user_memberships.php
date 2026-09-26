<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('user_memberships', 'transfer_account')) {
                $table->string('transfer_account')->nullable()->after('transfer_receipt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            if (Schema::hasColumn('user_memberships', 'transfer_account')) {
                $table->dropColumn('transfer_account');
            }
        });
    }
};
