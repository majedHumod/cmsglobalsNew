<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class EnsureTenant extends Command
{
    protected $signature = 'tenants:ensure {--domain=} {--db=} {--name=Demo Tenant} {--slug=demo} {--status=active} {--join-code=}';

    protected $description = 'Ensure a tenant row exists in system.tenants with provided domain and db name (no migrations).';

    public function handle(): int
    {
        $domain = $this->option('domain');
        $db = $this->option('db');
        $name = $this->option('name') ?? 'Demo Tenant';
        $slug = $this->option('slug') ?? 'demo';
        $status = $this->option('status') ?? 'active';
        $joinCode = $this->option('join-code');

        if (! $domain || ! $db) {
            $this->error('Please provide --domain and --db options.');

            return Command::FAILURE;
        }

        $t = Tenant::on('system')->where('domain', $domain)->first();
        if (! $t) {
            $t = new Tenant;
            $t->setConnection('system');
            $t->name = $name;
            $t->slug = $slug ?: Str::slug($name);
            $t->domain = $domain;
            $t->subdomain = explode('.', $domain)[0] ?? 'demo';
            $t->email = 'demo@demo.com';
            $t->status = $status;
            $t->db_name = $db;
            $t->join_code = $this->resolveJoinCode($joinCode, $t->slug, $t->subdomain);
            $t->save();
            $this->info("✅ Created tenant: {$domain} -> {$db}");
        } else {
            $t->setConnection('system');
            $t->name = $name ?: ($t->name ?? 'Demo Tenant');
            $t->slug = $slug ?: ($t->slug ?? 'demo');
            $t->db_name = $db;
            $t->status = $status ?: ($t->status ?? 'active');
            if ($joinCode || empty($t->join_code)) {
                $t->join_code = $this->resolveJoinCode($joinCode, $t->slug, $t->subdomain, (int) $t->id);
            }
            $t->save();
            $this->info("✅ Updated tenant: {$domain} -> {$db}");
        }

        $this->line(json_encode([
            'domain' => $t->domain,
            'db_name' => $t->db_name,
            'join_code' => $t->join_code,
            'status' => $t->status,
        ], JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }

    private function resolveJoinCode(?string $explicit, ?string $slug, ?string $subdomain, ?int $exceptId = null): string
    {
        $source = $explicit ?: ($slug ?: $subdomain ?: 'ORG');
        $code = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $source) ?: 'ORG');
        $base = $code;
        $suffix = 1;

        while (
            Tenant::on('system')
                ->whereRaw('UPPER(join_code) = ?', [$code])
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $code = $base.$suffix;
            $suffix++;
        }

        return Str::limit($code, 16, '');
    }
}
