<?php

namespace Database\Seeders;

use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use Illuminate\Database\Seeder;

class PerawatNikConstraintSeeder extends Seeder
{
    public function run(): void
    {
        $perawat = Perawat::firstOrCreate([
            'nik' => '5555555555555555',
        ], [
            'nama' => 'Contoh Perawat NIK',
            'email' => 'nik@demo.test',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
            'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_BERPENGALAMAN,
        ]);

        $institusi = InstitusiPendidikan::firstOrCreate([
            'nama' => 'Kampus Contoh NIK',
        ]);

        $lembaga = LembagaPelatihan::firstOrCreate([
            'nama' => 'Lembaga Contoh NIK',
        ]);

        $mitra = MitraPengguna::firstOrCreate([
            'nama' => 'RS Contoh NIK',
        ]);

        RiwayatPendidikan::updateOrCreate([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
        ], [
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
            'ipk' => 3.7,
        ]);

        SertifikasiKompetensi::updateOrCreate([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'BTCLS',
        ], [
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);

        DataLegalitas::updateOrCreate([
            'perawat_nik' => $perawat->nik,
        ], [
            'nomor_str' => 'STR-5555',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
            'source_verification' => 'manual',
        ]);

        RiwayatPekerjaan::updateOrCreate([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
        ], [
            'posisi' => 'Perawat ICU',
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
        ]);
    }
}
