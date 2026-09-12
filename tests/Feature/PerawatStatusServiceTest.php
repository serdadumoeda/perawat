<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Services\PerawatStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerawatStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_perawat_becomes_ready_when_requirements_complete(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        RiwayatPendidikan::create([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
        ]);

        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan ICU',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
        ]);

        app(PerawatStatusService::class)->refresh($perawat);

        $perawat->refresh();
        $this->assertSame(Perawat::KESIAPAN_READY, $perawat->status_kesiapan);
    }

    public function test_perawat_stays_draft_when_legalitas_inactive(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        RiwayatPendidikan::create([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
        ]);

        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan ICU',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'status_str' => DataLegalitas::STATUS_STR_HOLD,
        ]);

        app(PerawatStatusService::class)->refresh($perawat);

        $perawat->refresh();
        $this->assertSame(Perawat::KESIAPAN_DRAFT, $perawat->status_kesiapan);
    }

    public function test_perawat_marked_bekerja_when_has_verified_active_job(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => null,
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
        ]);

        app(PerawatStatusService::class)->refresh($perawat);

        $perawat->refresh();
        $this->assertSame(Perawat::KETENAGAKERJAAN_BEKERJA, $perawat->status_ketenagakerjaan);
    }

    public function test_perawat_stays_draft_when_riwayat_pendidikan_rejected(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        RiwayatPendidikan::create([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
            'status_verifikasi' => RiwayatPendidikan::VERIF_REJECTED,
        ]);

        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan ICU',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
        ]);

        app(PerawatStatusService::class)->refresh($perawat);

        $perawat->refresh();
        $this->assertSame(Perawat::KESIAPAN_DRAFT, $perawat->status_kesiapan);
    }
}
