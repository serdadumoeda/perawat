<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\DemoScenarioRun;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoScenarioFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_run_demo_scenario_with_ordered_timeline(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $nik = '9000000000001234';

        $this->actingAs($admin)
            ->post(route('admin.demo_scenario.run'), [
                'nik' => $nik,
            ])
            ->assertRedirect();

        $run = DemoScenarioRun::query()->with('events')->latest('started_at')->first();
        $this->assertNotNull($run);
        $this->assertSame($nik, $run->perawat_nik);
        $this->assertSame(DemoScenarioRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(5, $run->events->count());
        $this->assertSame([
            'kampus.input_lulusan',
            'lembaga.input_sertifikat',
            'regulator.activate_str',
            'mitra.hire',
            'mitra.update_pengalaman',
        ], $run->events->pluck('step_key')->all());

        $perawat = Perawat::where('nik', $nik)->first();
        $this->assertNotNull($perawat);
        $this->assertSame(Perawat::KESIAPAN_READY, $perawat->status_kesiapan);
        $this->assertSame(Perawat::KETENAGAKERJAAN_BEKERJA, $perawat->status_ketenagakerjaan);

        $this->assertTrue($perawat->riwayatPendidikan()->exists());
        $this->assertTrue($perawat->sertifikasi()->where('status_verifikasi', SertifikasiKompetensi::VERIF_VERIFIED)->exists());
        $this->assertTrue(
            $perawat->dataLegalitas()
                ->where('status_str', DataLegalitas::STATUS_STR_AKTIF)
                ->where('data_status', DataLegalitas::DATA_STATUS_VERIFIED)
                ->exists()
        );
        $this->assertTrue($perawat->rekrutmen()->where('status', Rekrutmen::STATUS_HIRED)->exists());
        $this->assertTrue(
            $perawat->riwayatPekerjaan()
                ->where('status_pekerjaan', RiwayatPekerjaan::STATUS_SELESAI)
                ->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                ->exists()
        );
    }

    public function test_non_admin_cannot_run_demo_scenario(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
        ]);

        $this->actingAs($user)
            ->post(route('admin.demo_scenario.run'))
            ->assertForbidden();

        $this->assertDatabaseCount('demo_scenario_runs', 0);
    }
}

