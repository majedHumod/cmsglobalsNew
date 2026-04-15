<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_database_pools', function (Blueprint $table) {
            $table->id();
            $table->string('db_name')->unique();
            $table->string('label')->nullable();
            $table->enum('status', ['available', 'allocated', 'maintenance'])->default('available');
            $table->boolean('is_ready')->default(true);
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_database_pools');
    }
};
