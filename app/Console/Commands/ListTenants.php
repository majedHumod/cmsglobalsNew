<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class ListTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $tenants = Tenant::on('system')->get();
            
            if ($tenants->isEmpty()) {
                $this->info("📭 No tenants found.");
                return 0;
            }
            
            $this->info("🏢 Available Tenants:");
            $this->info("==================");
            
            foreach ($tenants as $tenant) {
                $this->line("🏷️  Name: {$tenant->name}");
                $this->line("🔑 Join code: " . ($tenant->join_code ?: '—'));
                $this->line("🌐 Domain: {$tenant->domain}");
                $this->line("💾 Database: {$tenant->db_name}");
                $this->line("📊 Status: {$tenant->status}");
                $this->line("🗄️  DB State: " . ($tenant->database_status ?? 'unknown'));
                $this->line("🧩 Schema State: " . ($tenant->schema_status ?? 'unknown'));
                $this->line("🛠️  Recommended Action: " . ($tenant->recommended_action ?? 'none'));
                $this->line("📅 Created: {$tenant->created_at->format('Y-m-d H:i:s')}");
                $this->line("---");
            }
            
            $this->newLine();
            $this->info("💡 To grant page permissions, use:");
            $this->line("php artisan user:grant-page-permissions {user_id} {tenant_domain}");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}