<?php

use App\Services\AuditLogService;
use App\Services\DemoScenarioService;
use App\Services\LegalitasSyncService;
use App\Services\PerawatIdentityPreflightService;
use App\Jobs\SyncExperienceJob;
use App\Models\ApiIdempotencyKey;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('legalitas:sync', function () {
    $service = app(LegalitasSyncService::class);
    $audit = app(AuditLogService::class);

    $result = $service->sync();
    $audit->log(null, 'legalitas.sync', null, $result);

    $this->info('Sync legalitas selesai. Processed: '.$result['processed'].' Updated: '.$result['updated'].' Skipped: '.$result['skipped']);
})->purpose('Sync status legalitas STR (stub)');

Artisan::command('experience:sync', function () {
    SyncExperienceJob::dispatch();
    $this->info('Job sync pengalaman kerja dijalankan.');
})->purpose('Sync pengalaman kerja (mock)');

Artisan::command('api:idempotency:purge {--dry-run : Hitung tanpa menghapus data}', function () {
    $batchSize = max(100, (int) config('api.idempotency_purge_batch_size', 1000));
    $staleMinutes = max(5, (int) config('api.idempotency_stale_in_progress_minutes', 120));
    $now = now();

    $expiredQuery = ApiIdempotencyKey::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', $now);
    $expiredCount = (clone $expiredQuery)->count();

    $staleQuery = ApiIdempotencyKey::query()
        ->whereNull('completed_at')
        ->where('created_at', '<=', $now->copy()->subMinutes($staleMinutes));
    $staleCount = (clone $staleQuery)->count();

    if ($this->option('dry-run')) {
        $this->info("Dry-run purge idempotency selesai. Expired={$expiredCount}, StaleInProgress={$staleCount}");
        return;
    }

    $deletedExpired = 0;
    while (true) {
        $ids = (clone $expiredQuery)->limit($batchSize)->pluck('id');
        if ($ids->isEmpty()) {
            break;
        }

        $deletedExpired += ApiIdempotencyKey::query()->whereIn('id', $ids)->delete();
    }

    $deletedStale = 0;
    while (true) {
        $ids = (clone $staleQuery)->limit($batchSize)->pluck('id');
        if ($ids->isEmpty()) {
            break;
        }

        $deletedStale += ApiIdempotencyKey::query()->whereIn('id', $ids)->delete();
    }

    $this->info("Purge idempotency selesai. DeletedExpired={$deletedExpired}, DeletedStaleInProgress={$deletedStale}");
})->purpose('Purge idempotency key yang expired atau stale');

Artisan::command('demo:refresh {--force : Jalankan tanpa konfirmasi}', function () {
    if (app()->environment('production')) {
        $this->error('Perintah ini tidak boleh dijalankan di production.');
        return;
    }

    if (!filter_var(env('DEMO_SEED'), FILTER_VALIDATE_BOOL)) {
        $this->error('Set DEMO_SEED=true di .env untuk menjalankan demo:refresh.');
        return;
    }

    if (!$this->option('force')) {
        $confirmed = $this->confirm('Ini akan menghapus data demo dan mengisi ulang. Lanjutkan?');
        if (!$confirmed) {
            $this->info('Dibatalkan.');
            return;
        }
    }

    Schema::disableForeignKeyConstraints();

    $tables = [
        'audit_logs',
        'api_keys',
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'notifications',
        'organisasi_user',
        'password_reset_tokens',
        'rekrutmen',
        'lamaran',
        'lowongan',
        'riwayat_pekerjaan',
        'sertifikasi_kompetensi',
        'riwayat_pendidikan',
        'data_legalitas',
        'perawat',
        'regulator_organisasi',
        'mitra_pengguna',
        'lembaga_pelatihan',
        'institusi_pendidikan',
        'sessions',
        'users',
    ];

    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
        }
    }

    Schema::enableForeignKeyConstraints();

    Artisan::call('db:seed', ['--class' => \Database\Seeders\DemoSeeder::class]);

    $this->info('Demo data berhasil di-refresh.');
})->purpose('Reset dan isi ulang demo data (hanya non-production)');

Artisan::command('demo:scenario {--nik= : NIK perawat 16 digit (opsional)}', function () {
    if (app()->environment('production')) {
        $this->error('Perintah ini tidak boleh dijalankan di production.');
        return;
    }

    $run = app(DemoScenarioService::class)->runSinglePerawatTimeline($this->option('nik'));

    $this->info('Demo scenario selesai.');
    $this->line('Scenario Key : '.$run->scenario_key);
    $this->line('Perawat NIK  : '.$run->perawat_nik);
    $this->line('Status       : '.$run->status);
    $this->line('Total Event  : '.$run->events()->count());
})->purpose('Jalankan timeline demo 1 perawat sesuai alur PDF');

Artisan::command('preflight:perawat-identity {--json : Output JSON report} {--fail-on-warning : Return non-zero jika ada warning}', function () {
    $report = app(PerawatIdentityPreflightService::class)->run();

    if ($this->option('json')) {
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } else {
        $this->line('Preflight Perawat Identity');
        $this->line('Generated at: '.$report['generated_at']);
        $this->newLine();

        $this->line('Strict tables (must be clean before production migrate):');
        $this->table(
            ['Table', 'Rows', 'Null ID', 'Null NIK', 'Orphan ID', 'Orphan NIK', 'Mismatch', 'Status'],
            collect($report['strict_tables'])->map(function (array $item) {
                return [
                    $item['table'],
                    $item['rows'],
                    $item['null_perawat_id'],
                    $item['null_perawat_nik'],
                    $item['orphan_perawat_id'],
                    $item['orphan_perawat_nik'],
                    $item['mismatched_pair'],
                    strtoupper($item['status']),
                ];
            })->all()
        );

        $this->newLine();
        $this->line('Soft tables (advisory):');
        $this->table(
            ['Table', 'Rows', 'Orphan ID', 'Orphan NIK', 'Mismatch', 'Incomplete Pair', 'Status'],
            collect($report['soft_tables'])->map(function (array $item) {
                return [
                    $item['table'],
                    $item['rows'],
                    $item['orphan_perawat_id'],
                    $item['orphan_perawat_nik'],
                    $item['mismatched_pair'],
                    $item['incomplete_pair'],
                    strtoupper($item['status']),
                ];
            })->all()
        );

        if ($report['reviewer_orphans'] !== []) {
            $this->newLine();
            $this->line('Reviewer orphan check (data_legalitas):');
            $this->table(
                ['Field', 'Orphan Count', 'Status'],
                collect($report['reviewer_orphans'])->map(function (array $item) {
                    return [
                        $item['field'],
                        $item['orphan_count'],
                        strtoupper($item['status']),
                    ];
                })->all()
            );
        }

        $this->newLine();
        $this->line('Summary:');
        $this->line(' - Checked tables: '.$report['summary']['checked_tables']);
        $this->line(' - Blockers: '.$report['summary']['blockers']);
        $this->line(' - Warnings: '.$report['summary']['warnings']);
    }

    $hasBlocker = $report['summary']['blockers'] > 0;
    $hasWarning = $report['summary']['warnings'] > 0;
    $failOnWarning = (bool) $this->option('fail-on-warning');

    if ($hasBlocker || ($failOnWarning && $hasWarning)) {
        return 1;
    }

    return 0;
})->purpose('Preflight check mismatch/orphan perawat_id + perawat_nik sebelum migrate production');

Schedule::command('legalitas:sync')->dailyAt('01:00');
Schedule::command('api:idempotency:purge')->dailyAt('02:00');
Schedule::job(new SyncExperienceJob())->hourly();
