<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use App\Services\PerawatStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDataQualityMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_data_quality_dashboard_shows_explicit_kpi_metrics(): void
    {
        [$perawatA, $perawatB] = $this->seedQualityScenario();

        app(PerawatStatusService::class)->refresh($perawatA);
        app(PerawatStatusService::class)->refresh($perawatB);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('admin.data_quality'))
            ->assertOk();

        $summary = $response->viewData('summary');
        $completeness = collect($response->viewData('completeness'))->keyBy('key');
        $verification = collect($response->viewData('verification'))->keyBy('key');
        $actorCoverage = collect($response->viewData('actorCoverage'))->keyBy('key');

        $this->assertSame(2, $summary['total_perawat']);
        $this->assertSame(1, $summary['ready_to_hire']);

        $this->assertSame(2, $completeness['pendidikan']['filled']);
        $this->assertEquals(100.00, $completeness['pendidikan']['rate']);
        $this->assertSame(1, $completeness['sertifikasi_verified']['filled']);
        $this->assertEquals(50.00, $completeness['sertifikasi_verified']['rate']);

        $this->assertSame(2, $verification['pendidikan']['total']);
        $this->assertSame(2, $verification['pendidikan']['verified']);
        $this->assertEquals(100.00, $verification['pendidikan']['rate']);

        $this->assertSame(2, $verification['legalitas_data']['total']);
        $this->assertSame(1, $verification['legalitas_data']['verified']);
        $this->assertSame(1, $verification['legalitas_data']['pending']);
        $this->assertEquals(50.00, $verification['legalitas_data']['rate']);

        $this->assertSame(2, $actorCoverage['kampus']['records']);
        $this->assertSame(2, $actorCoverage['kampus']['covered_perawat']);
        $this->assertEquals(100.00, $actorCoverage['kampus']['coverage_rate']);

        $this->assertSame(2, $actorCoverage['perawat']['records']);
        $this->assertSame(1, $actorCoverage['perawat']['covered_perawat']);
        $this->assertEquals(50.00, $actorCoverage['perawat']['coverage_rate']);
    }

    public function test_admin_can_export_data_quality_kpi_csv_sections(): void
    {
        [$perawatA, $perawatB] = $this->seedQualityScenario();
        app(PerawatStatusService::class)->refresh($perawatA);
        app(PerawatStatusService::class)->refresh($perawatB);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('admin.data_quality.export'))
            ->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Summary Metric', $csv);
        $this->assertStringContainsString('Completeness KPI', $csv);
        $this->assertStringContainsString('Verified Rate KPI', $csv);
        $this->assertStringContainsString('Actor Coverage KPI', $csv);
        $this->assertStringContainsString('Cakupan Sertifikasi Terverifikasi', $csv);
        $this->assertStringContainsString('Perawat (Self-Service)', $csv);
    }

    /**
     * @return array{0:Perawat,1:Perawat}
     */
    private function seedQualityScenario(): array
    {
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $mitra = MitraPengguna::create(['nama' => 'RS A']);

        $perawatA = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Perawat A',
        ]);

        $perawatB = Perawat::create([
            'nik' => '1234567890123457',
            'nama' => 'Perawat B',
        ]);

        RiwayatPendidikan::create([
            'perawat_nik' => $perawatA->nik,
            'perawat_id' => $perawatA->id,
            'institusi_pendidikan_id' => $institusi->id,
            'status_verifikasi' => RiwayatPendidikan::VERIF_VERIFIED,
            'source_actor' => 'kampus',
            'source_system' => 'kampus_portal',
        ]);

        RiwayatPendidikan::create([
            'perawat_nik' => $perawatB->nik,
            'perawat_id' => $perawatB->id,
            'institusi_pendidikan_id' => $institusi->id,
            'status_verifikasi' => RiwayatPendidikan::VERIF_VERIFIED,
            'source_actor' => 'kampus',
            'source_system' => 'kampus_portal',
        ]);

        SertifikasiKompetensi::create([
            'perawat_nik' => $perawatA->nik,
            'perawat_id' => $perawatA->id,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'BTCLS',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
            'source_actor' => 'lembaga',
            'source_system' => 'lembaga_portal',
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawatA->nik,
            'perawat_id' => $perawatA->id,
            'nomor_str' => 'STR-A',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
            'source_actor' => 'regulator',
            'source_system' => 'regulator_portal',
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawatB->nik,
            'perawat_id' => $perawatB->id,
            'nomor_str' => 'STR-B',
            'status_str' => DataLegalitas::STATUS_STR_HOLD,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'source_actor' => 'perawat',
            'source_system' => 'perawat_portal',
        ]);

        RiwayatPekerjaan::create([
            'perawat_nik' => $perawatA->nik,
            'perawat_id' => $perawatA->id,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat ICU',
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
            'source_actor' => 'mitra',
            'source_system' => 'mitra_portal',
        ]);

        RiwayatPekerjaan::create([
            'perawat_nik' => $perawatB->nik,
            'perawat_id' => $perawatB->id,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat Klinik',
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
            'is_verified' => false,
            'source_actor' => 'perawat',
            'source_system' => 'perawat_portal',
        ]);

        return [$perawatA, $perawatB];
    }
}
