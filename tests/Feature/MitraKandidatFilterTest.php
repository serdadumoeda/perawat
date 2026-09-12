<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraKandidatFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_total_experience_ignores_overlapping_periods(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus Uji']);
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga Uji']);

        $overlap = Perawat::create([
            'nik' => '3201000000000001',
            'nama' => 'Nurse Overlap',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);

        $nonOverlap = Perawat::create([
            'nik' => '3201000000000002',
            'nama' => 'Nurse Total',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);

        $this->seedReadyRequirements($overlap, $institusi, $lembaga);
        $this->seedReadyRequirements($nonOverlap, $institusi, $lembaga);

        RiwayatPekerjaan::create([
            'perawat_nik' => $overlap->nik,
            'tanggal_mulai' => '2021-01-01',
            'tanggal_selesai' => '2021-12-31',
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
        ]);

        RiwayatPekerjaan::create([
            'perawat_nik' => $overlap->nik,
            'tanggal_mulai' => '2021-06-01',
            'tanggal_selesai' => '2022-05-31',
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
        ]);

        RiwayatPekerjaan::create([
            'perawat_nik' => $nonOverlap->nik,
            'tanggal_mulai' => '2019-01-01',
            'tanggal_selesai' => '2019-12-31',
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
        ]);

        RiwayatPekerjaan::create([
            'perawat_nik' => $nonOverlap->nik,
            'tanggal_mulai' => '2020-01-01',
            'tanggal_selesai' => '2020-12-31',
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->get(route('mitra.kandidat.index', [
            'min_pengalaman_total' => 1.8,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Nurse Total');
        $response->assertDontSee('Nurse Overlap');
    }

    private function seedReadyRequirements(
        Perawat $perawat,
        InstitusiPendidikan $institusi,
        LembagaPelatihan $lembaga
    ): void {
        RiwayatPendidikan::create([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
            'tahun_lulus' => 2020,
            'ipk' => 3.5,
        ]);

        SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'BTCLS',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-'.$perawat->nik,
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'str_berlaku_sampai' => now()->addYear()->toDateString(),
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
        ]);
    }
}
