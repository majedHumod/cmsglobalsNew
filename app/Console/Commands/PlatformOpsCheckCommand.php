<?php

namespace App\Console\Commands;

use App\Models\Billing\Invoice;
use App\Models\Billing\SignupReservation;
use App\Models\TenantDatabasePool;
use App\Services\Billing\SignupReservationService;
use App\Services\Platform\OpsAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Looks for the problems that cost money on launch day and emails the platform owner:
 *  - prepared tenant databases running out (new customers cannot be provisioned)
 *  - paid signups that never got a tenant
 *  - signup payments flagged as conflicts (name/email taken while paying)
 *  - provisioning jobs that failed, or a queue worker that is not running
 */
class PlatformOpsCheckCommand extends Command
{
    protected $signature = 'platform:ops-check {--no-alert : Only print findings, do not email}';

    protected $description = 'Check tenant pool, stuck provisioning, failed jobs and queue worker; alert the owner on problems';

    /** @var list<string> */
    private array $problems = [];

    public function handle(SignupReservationService $reservations): int
    {
        $reservations->releaseExpired();

        $this->checkPool();
        $this->checkStuckProvisioning();
        $this->checkConflicts();
        $this->checkFailedJobs();
        $this->checkQueueWorker();

        if ($this->problems === []) {
            $this->info('Ops check passed: no problems found.');

            return self::SUCCESS;
        }

        foreach ($this->problems as $problem) {
            $this->warn($problem);
        }

        return self::SUCCESS;
    }

    private function report(string $key, string $subject, string $body): void
    {
        $this->problems[] = $subject.' — '.str_replace("\n", ' ', $body);

        if (! $this->option('no-alert')) {
            OpsAlert::critical($subject, $body, 'ops-check:'.$key, 60);
        }
    }

    private function checkPool(): void
    {
        try {
            $free = TenantDatabasePool::query()
                ->available()
                ->whereNotIn('db_name', function ($query) {
                    $query->select('db_name')->from('tenants')->whereNotNull('db_name');
                })
                ->count();

            $held = SignupReservation::query()
                ->whereIn('status', [SignupReservation::PENDING, SignupReservation::PAID])
                ->count();

            $remaining = $free - $held;
            $min = max(0, (int) config('platform.signup.min_free_databases', 3));

            $this->line("Tenant pool: {$free} free, {$held} reserved → {$remaining} left (alert at <= {$min}).");

            if ($remaining <= $min) {
                $this->report(
                    'pool-low',
                    $remaining <= 0 ? 'نفدت قواعد بيانات النوادي الجاهزة' : 'قواعد بيانات النوادي الجاهزة على وشك النفاد',
                    "المتبقي للعملاء الجدد: {$remaining} (جاهزة: {$free}، محجوزة لعمليات دفع جارية: {$held}).\n"
                    .'جهّز قواعد جديدة في cPanel ثم سجّلها: php artisan tenants:pool-register <db_name>'
                );
            }
        } catch (\Throwable $e) {
            $this->report('pool-error', 'تعذر فحص قواعد النوادي الجاهزة', $e->getMessage());
        }
    }

    private function checkStuckProvisioning(): void
    {
        try {
            $minutes = max(5, (int) config('platform.signup.stuck_provision_minutes', 15));

            $stuck = Invoice::query()
                ->where('status', 'paid')
                ->whereNull('tenant_id')
                ->where('updated_at', '<', now()->subMinutes($minutes))
                ->orderBy('id')
                ->limit(25)
                ->get(['id', 'number', 'provider_invoice_id', 'updated_at'])
                ->reject(function (Invoice $invoice) {
                    // Conflicts are reported separately.
                    $reservation = SignupReservation::query()->where('order_number', $invoice->number)->first();

                    return $reservation?->status === SignupReservation::CONFLICT;
                });

            if ($stuck->isNotEmpty()) {
                $list = $stuck->map(fn (Invoice $i) => "{$i->number} (Paylink {$i->provider_invoice_id})")->implode("\n");
                $this->report(
                    'stuck-provisioning',
                    'اشتراكات مدفوعة لم يتم تجهيزها',
                    "{$stuck->count()} فاتورة مدفوعة بلا نادٍ منذ أكثر من {$minutes} دقيقة:\n{$list}\n"
                    ."تأكد من عمل الـ queue worker ثم نفّذ: php artisan billing:retry-provision --all-paid"
                );
            }
        } catch (\Throwable $e) {
            $this->report('stuck-error', 'تعذر فحص الاشتراكات العالقة', $e->getMessage());
        }
    }

    private function checkConflicts(): void
    {
        try {
            $conflicts = SignupReservation::query()
                ->where('status', SignupReservation::CONFLICT)
                ->orderBy('id')
                ->limit(25)
                ->get();

            if ($conflicts->isNotEmpty()) {
                $list = $conflicts->map(fn (SignupReservation $r) => "{$r->order_number}: {$r->slug} / {$r->email}")->implode("\n");
                $this->report(
                    'conflicts',
                    'دفعات اشتراك بحاجة لمعالجة يدوية',
                    "{$conflicts->count()} دفعة تعارضت (الاسم أو البريد أُخذ أثناء الدفع):\n{$list}"
                );
            }
        } catch (\Throwable $e) {
            $this->report('conflicts-error', 'تعذر فحص تعارضات الاشتراك', $e->getMessage());
        }
    }

    private function checkFailedJobs(): void
    {
        try {
            if (! Schema::hasTable('failed_jobs')) {
                return;
            }

            $failed = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->where('payload', 'like', '%ProvisionTenantJob%')
                ->count();

            if ($failed > 0) {
                $this->report(
                    'failed-jobs',
                    'مهام تجهيز نوادٍ فاشلة',
                    "{$failed} مهمة تجهيز فشلت خلال آخر 24 ساعة. راجع: php artisan queue:failed ثم php artisan billing:retry-provision --all-paid"
                );
            }
        } catch (\Throwable $e) {
            $this->report('failed-jobs-error', 'تعذر فحص المهام الفاشلة', $e->getMessage());
        }
    }

    private function checkQueueWorker(): void
    {
        try {
            if (config('queue.default') !== 'database' || ! Schema::hasTable('jobs')) {
                return;
            }

            $waiting = DB::table('jobs')
                ->whereNull('reserved_at')
                ->where('available_at', '<', now()->subMinutes(10)->timestamp)
                ->count();

            if ($waiting > 0) {
                $this->report(
                    'queue-stalled',
                    'الـ queue worker متوقف على الأغلب',
                    "{$waiting} مهمة تنتظر منذ أكثر من 10 دقائق دون معالجة. شغّل: php artisan queue:work --stop-when-empty (أو تأكد من الكرون/Supervisor)."
                );
            }
        } catch (\Throwable $e) {
            $this->report('queue-error', 'تعذر فحص الـ queue', $e->getMessage());
        }
    }
}
