<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerawatIdentityPreflightService
{
    /**
     * @var array<int, string>
     */
    private array $strictTables = [
        'riwayat_pendidikan',
        'sertifikasi_kompetensi',
        'data_legalitas',
        'riwayat_pekerjaan',
        'lamaran',
        'rekrutmen',
        'pelatihan_peserta',
        'tracing_alumni',
        'str_sync_requests',
    ];

    /**
     * @var array<int, string>
     */
    private array $softTables = [
        'users',
        'riwayat_pekerjaan_syncs',
        'demo_scenario_runs',
    ];

    /**
     * Run preflight checks before applying strict perawat constraints.
     *
     * @return array<string, mixed>
     */
    public function run(): array
    {
        $strictReports = [];
        $softReports = [];
        $blockers = 0;
        $warnings = 0;

        foreach ($this->strictTables as $tableName) {
            $report = $this->inspectTable($tableName, true);
            $strictReports[] = $report;
            $blockers += $report['blockers'];
            $warnings += $report['warnings'];
        }

        foreach ($this->softTables as $tableName) {
            $report = $this->inspectTable($tableName, false);
            $softReports[] = $report;
            $blockers += $report['blockers'];
            $warnings += $report['warnings'];
        }

        $reviewerOrphans = $this->inspectReviewerOrphans();
        $warnings += array_sum(array_column($reviewerOrphans, 'orphan_count'));

        return [
            'generated_at' => now()->toIso8601String(),
            'strict_tables' => $strictReports,
            'soft_tables' => $softReports,
            'reviewer_orphans' => $reviewerOrphans,
            'summary' => [
                'checked_tables' => count($strictReports) + count($softReports),
                'blockers' => $blockers,
                'warnings' => $warnings,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inspectTable(string $tableName, bool $strict): array
    {
        if (!Schema::hasTable($tableName)) {
            return [
                'table' => $tableName,
                'exists' => false,
                'strict' => $strict,
                'rows' => 0,
                'null_perawat_id' => 0,
                'null_perawat_nik' => 0,
                'orphan_perawat_id' => 0,
                'orphan_perawat_nik' => 0,
                'mismatched_pair' => 0,
                'incomplete_pair' => 0,
                'blockers' => 0,
                'warnings' => 1,
                'status' => 'warning',
                'notes' => ['Table not found'],
            ];
        }

        if (!Schema::hasColumn($tableName, 'perawat_id') || !Schema::hasColumn($tableName, 'perawat_nik')) {
            return [
                'table' => $tableName,
                'exists' => true,
                'strict' => $strict,
                'rows' => DB::table($tableName)->count(),
                'null_perawat_id' => 0,
                'null_perawat_nik' => 0,
                'orphan_perawat_id' => 0,
                'orphan_perawat_nik' => 0,
                'mismatched_pair' => 0,
                'incomplete_pair' => 0,
                'blockers' => 0,
                'warnings' => 1,
                'status' => 'warning',
                'notes' => ['perawat_id/perawat_nik column missing'],
            ];
        }

        $rows = DB::table($tableName)->count();
        $nullPerawatId = DB::table($tableName)->whereNull('perawat_id')->count();
        $nullPerawatNik = DB::table($tableName)->whereNull('perawat_nik')->count();
        $orphanPerawatId = DB::table("{$tableName} as t")
            ->leftJoin('perawat as p', 't.perawat_id', '=', 'p.id')
            ->whereNotNull('t.perawat_id')
            ->whereNull('p.id')
            ->count();
        $orphanPerawatNik = DB::table("{$tableName} as t")
            ->leftJoin('perawat as p', 't.perawat_nik', '=', 'p.nik')
            ->whereNotNull('t.perawat_nik')
            ->whereNull('p.nik')
            ->count();
        $mismatchedPair = DB::table("{$tableName} as t")
            ->join('perawat as p', 't.perawat_id', '=', 'p.id')
            ->whereNotNull('t.perawat_nik')
            ->whereColumn('t.perawat_nik', '<>', 'p.nik')
            ->count();
        $incompletePair = DB::table($tableName)
            ->where(function ($query) {
                $query->whereNull('perawat_id')->whereNotNull('perawat_nik');
            })
            ->orWhere(function ($query) {
                $query->whereNotNull('perawat_id')->whereNull('perawat_nik');
            })
            ->count();

        $blockers = 0;
        $warnings = 0;
        $notes = [];

        if ($strict) {
            foreach ([
                'null_perawat_id' => $nullPerawatId,
                'null_perawat_nik' => $nullPerawatNik,
                'orphan_perawat_id' => $orphanPerawatId,
                'orphan_perawat_nik' => $orphanPerawatNik,
                'mismatched_pair' => $mismatchedPair,
                'incomplete_pair' => $incompletePair,
            ] as $key => $count) {
                if ($count > 0) {
                    $blockers += $count;
                    $notes[] = "{$key}={$count}";
                }
            }
        } else {
            foreach ([
                'orphan_perawat_id' => $orphanPerawatId,
                'orphan_perawat_nik' => $orphanPerawatNik,
                'mismatched_pair' => $mismatchedPair,
                'incomplete_pair' => $incompletePair,
            ] as $key => $count) {
                if ($count > 0) {
                    $warnings += $count;
                    $notes[] = "{$key}={$count}";
                }
            }
        }

        $status = 'ok';
        if ($blockers > 0) {
            $status = 'blocker';
        } elseif ($warnings > 0) {
            $status = 'warning';
        }

        return [
            'table' => $tableName,
            'exists' => true,
            'strict' => $strict,
            'rows' => $rows,
            'null_perawat_id' => $nullPerawatId,
            'null_perawat_nik' => $nullPerawatNik,
            'orphan_perawat_id' => $orphanPerawatId,
            'orphan_perawat_nik' => $orphanPerawatNik,
            'mismatched_pair' => $mismatchedPair,
            'incomplete_pair' => $incompletePair,
            'blockers' => $blockers,
            'warnings' => $warnings,
            'status' => $status,
            'notes' => $notes,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function inspectReviewerOrphans(): array
    {
        if (!Schema::hasTable('data_legalitas') || !Schema::hasTable('users')) {
            return [];
        }

        $fields = [
            'data_reviewed_by',
            'str_document_reviewed_by',
            'izin_luar_negeri_document_reviewed_by',
        ];

        $reports = [];
        foreach ($fields as $field) {
            if (!Schema::hasColumn('data_legalitas', $field)) {
                continue;
            }

            $orphanCount = DB::table('data_legalitas as dl')
                ->leftJoin('users as u', "dl.{$field}", '=', 'u.id')
                ->whereNotNull("dl.{$field}")
                ->whereNull('u.id')
                ->count();

            $reports[] = [
                'field' => $field,
                'orphan_count' => $orphanCount,
                'status' => $orphanCount > 0 ? 'warning' : 'ok',
            ];
        }

        return $reports;
    }
}
