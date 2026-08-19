<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('system')->table('tenants', function (Blueprint $table) {
            if (! Schema::connection('system')->hasColumn('tenants', 'join_code')) {
                $table->string('join_code', 32)->nullable()->unique()->after('slug');
            }
        });

        $tenants = DB::connection('system')->table('tenants')->orderBy('id')->get(['id', 'slug', 'subdomain', 'domain']);

        foreach ($tenants as $tenant) {
            $code = $this->makeJoinCode($tenant->slug ?: ($tenant->subdomain ?: $tenant->domain));
            $base = $code;
            $suffix = 1;

            while (
                DB::connection('system')->table('tenants')
                    ->where('join_code', $code)
                    ->where('id', '!=', $tenant->id)
                    ->exists()
            ) {
                $code = $base.$suffix;
                $suffix++;
            }

            DB::connection('system')->table('tenants')
                ->where('id', $tenant->id)
                ->update(['join_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::connection('system')->table('tenants', function (Blueprint $table) {
            if (Schema::connection('system')->hasColumn('tenants', 'join_code')) {
                $table->dropUnique(['join_code']);
                $table->dropColumn('join_code');
            }
        });
    }

    private function makeJoinCode(?string $source): string
    {
        $normalized = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $source) ?: '');

        if ($normalized === '') {
            $normalized = 'ORG'.Str::upper(Str::random(4));
        }

        return Str::limit($normalized, 16, '');
    }
};
