<?php

namespace Tests\Feature;

use App\Models\Perawat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerawatIdentityPreflightCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_passes_when_no_blocker_data_exists(): void
    {
        $this->artisan('preflight:perawat-identity')
            ->expectsOutputToContain('Preflight Perawat Identity')
            ->expectsOutputToContain('Blockers: 0')
            ->assertExitCode(0);
    }

    public function test_preflight_detects_mismatched_perawat_pair_as_blocker(): void
    {
        $perawatA = Perawat::create([
            'nik' => '1111111111111111',
            'nama' => 'Nurse A',
        ]);
        $perawatB = Perawat::create([
            'nik' => '2222222222222222',
            'nama' => 'Nurse B',
        ]);

        DB::table('data_legalitas')->insert([
            'id' => (string) Str::uuid(),
            'perawat_id' => $perawatA->id,
            'perawat_nik' => $perawatB->nik,
            'nomor_str' => 'STR-MISMATCH-PREFLIGHT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('preflight:perawat-identity')
            ->expectsOutputToContain('data_legalitas')
            ->expectsOutputToContain('Blockers: 1')
            ->assertExitCode(1);
    }

    public function test_preflight_can_fail_on_warning_for_reviewer_orphans(): void
    {
        $perawat = Perawat::create([
            'nik' => '3333333333333333',
            'nama' => 'Nurse C',
        ]);

        DB::table('data_legalitas')->insert([
            'id' => (string) Str::uuid(),
            'perawat_id' => $perawat->id,
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-WARN-PREFLIGHT',
            'data_reviewed_by' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('preflight:perawat-identity')
            ->expectsOutputToContain('Warnings: 1')
            ->assertExitCode(0);

        $this->artisan('preflight:perawat-identity --fail-on-warning')
            ->assertExitCode(1);
    }
}
