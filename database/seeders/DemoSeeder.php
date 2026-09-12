<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\Lamaran;
use App\Models\LembagaPelatihan;
use App\Models\Lowongan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\Pelatihan;
use App\Models\PelatihanPeserta;
use App\Models\RegulatorOrganisasi;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use App\Services\ApiKeyService;
use App\Services\PerawatStatusService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $institusi = InstitusiPendidikan::firstOrCreate([
            'nama' => 'Kampus Nusantara',
        ], [
            'alamat' => 'Jl. Merdeka 1',
            'kota' => 'Bandung',
            'provinsi' => 'Jawa Barat',
            'email' => 'kampus@demo.test',
            'telepon' => '022-123456',
        ]);

        $lembaga = LembagaPelatihan::firstOrCreate([
            'nama' => 'Lembaga Kompetensi Prima',
        ], [
            'alamat' => 'Jl. Sudirman 10',
            'kota' => 'Jakarta',
            'provinsi' => 'DKI Jakarta',
            'email' => 'lembaga@demo.test',
            'telepon' => '021-555000',
        ]);

        $mitra = MitraPengguna::firstOrCreate([
            'nama' => 'RS Mitra Sehat',
        ], [
            'jenis' => 'rumah_sakit',
            'alamat' => 'Jl. Kesehatan 5',
            'kota' => 'Surabaya',
            'provinsi' => 'Jawa Timur',
            'email' => 'hrd@demo.test',
            'telepon' => '031-888999',
        ]);

        $mitraB = MitraPengguna::firstOrCreate([
            'nama' => 'Klinik Amanah',
        ], [
            'jenis' => 'klinik',
            'alamat' => 'Jl. Kebon 2',
            'kota' => 'Semarang',
            'provinsi' => 'Jawa Tengah',
            'email' => 'klinik@demo.test',
            'telepon' => '024-771122',
        ]);

        $mitraC = MitraPengguna::firstOrCreate([
            'nama' => 'Global Care Agency',
        ], [
            'jenis' => 'agency',
            'alamat' => 'Jl. Internasional 8',
            'kota' => 'Jakarta',
            'provinsi' => 'DKI Jakarta',
            'email' => 'agency@demo.test',
            'telepon' => '021-991122',
        ]);

        $regulatorOrganisasi = RegulatorOrganisasi::firstOrCreate([
            'nama' => 'Dinas Kesehatan Provinsi Jawa Barat',
        ], [
            'kode' => 'DINKES-JBR',
            'tingkat' => 'provinsi',
            'wilayah' => 'Jawa Barat',
            'alamat' => 'Jl. Diponegoro No. 22, Bandung',
            'email' => 'dinkes.jabar@demo.test',
            'telepon' => '022-777000',
            'is_active' => true,
        ]);

        $kampusUser = $this->upsertUser('Kampus Admin', 'kampus@demo.test', User::ROLE_KAMPUS);
        $lembagaUser = $this->upsertUser('Lembaga Admin', 'lembaga@demo.test', User::ROLE_LEMBAGA);
        $mitraUser = $this->upsertUser('HRD Mitra', 'mitra@demo.test', User::ROLE_MITRA);
        $faskesUser = $this->upsertUser('HRD Klinik', 'faskes@demo.test', User::ROLE_FASKES);
        $headhunterUser = $this->upsertUser('HeadHunter Agency', 'headhunter@demo.test', User::ROLE_HEADHUNTER);
        $regulatorUser = $this->upsertUser('Regulator', 'regulator@demo.test', User::ROLE_REGULATOR);
        $adminUser = $this->upsertUser('Admin', 'admin@demo.test', User::ROLE_ADMIN);

        $this->attachUserToOrganisasi($kampusUser, $institusi->id, InstitusiPendidikan::class);
        $this->attachUserToOrganisasi($lembagaUser, $lembaga->id, LembagaPelatihan::class);
        $this->attachUserToOrganisasi($mitraUser, $mitra->id, MitraPengguna::class);
        $this->attachUserToOrganisasi($faskesUser, $mitraB->id, MitraPengguna::class);
        $this->attachUserToOrganisasi($headhunterUser, $mitraC->id, MitraPengguna::class);
        $this->attachUserToOrganisasi($regulatorUser, $regulatorOrganisasi->id, RegulatorOrganisasi::class);

        $perawatA = Perawat::firstOrCreate([
            'nik' => '1111111111111111',
        ], [
            'nama' => 'Siti Aminah',
            'email' => 'siti@demo.test',
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2001-06-12',
            'alamat' => 'Jl. Mawar 10',
            'kota' => 'Bandung',
            'provinsi' => 'Jawa Barat',
        ]);

        $perawatB = Perawat::firstOrCreate([
            'nik' => '2222222222222222',
        ], [
            'nama' => 'Agus Wijaya',
            'email' => 'agus@demo.test',
            'no_hp' => '081298765432',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Semarang',
            'tanggal_lahir' => '2002-03-20',
            'alamat' => 'Jl. Melati 4',
            'kota' => 'Semarang',
            'provinsi' => 'Jawa Tengah',
        ]);

        $perawatC = Perawat::firstOrCreate([
            'nik' => '3333333333333333',
        ], [
            'nama' => 'Dewi Lestari',
            'email' => 'dewi@demo.test',
            'no_hp' => '081200011122',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1999-11-02',
            'alamat' => 'Jl. Kenanga 7',
            'kota' => 'Surabaya',
            'provinsi' => 'Jawa Timur',
        ]);

        $perawatD = Perawat::firstOrCreate([
            'nik' => '4444444444444444',
        ], [
            'nama' => 'Rafi Pratama',
            'email' => 'rafi@demo.test',
            'no_hp' => '081255599900',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1998-01-15',
            'alamat' => 'Jl. Flamboyan 9',
            'kota' => 'Jakarta',
            'provinsi' => 'DKI Jakarta',
        ]);

        $perawatE = Perawat::firstOrCreate([
            'nik' => '5555555555555555',
        ], [
            'nama' => 'Nur Aisyah',
            'email' => 'nur.aisyah@demo.test',
            'no_hp' => '081322233344',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2000-04-10',
            'alamat' => 'Jl. Dahlia 21',
            'kota' => 'Bandung',
            'provinsi' => 'Jawa Barat',
        ]);

        $perawatF = Perawat::firstOrCreate([
            'nik' => '6666666666666666',
        ], [
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@demo.test',
            'no_hp' => '081344455566',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Depok',
            'tanggal_lahir' => '1997-09-02',
            'alamat' => 'Jl. Teratai 18',
            'kota' => 'Depok',
            'provinsi' => 'Jawa Barat',
        ]);

        $perawatUser = $this->upsertUser('Perawat Demo', 'perawat@demo.test', User::ROLE_PERAWAT, $perawatA->nik);
        $this->upsertUser('Perawat Pending STR', 'perawat.pending@demo.test', User::ROLE_PERAWAT, $perawatE->nik);
        $this->upsertUser('Perawat Rejected STR', 'perawat.rejected@demo.test', User::ROLE_PERAWAT, $perawatF->nik);

        $pelatihanBtcls = Pelatihan::firstOrCreate([
            'nama' => 'Pelatihan BTCLS',
            'lembaga_pelatihan_id' => $lembaga->id,
        ], [
            'deskripsi' => 'Pelatihan BTCLS untuk perawat baru.',
            'jumlah_jam' => 40,
            'tanggal_mulai' => now()->addDays(7)->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'lokasi' => 'Jakarta',
            'kuota' => 30,
            'status' => Pelatihan::STATUS_DIBUKA,
        ]);

        $pelatihanAcls = Pelatihan::firstOrCreate([
            'nama' => 'Pelatihan ACLS Intermediate',
            'lembaga_pelatihan_id' => $lembaga->id,
        ], [
            'deskripsi' => 'Pelatihan ACLS untuk peningkatan kompetensi kritikal.',
            'jumlah_jam' => 32,
            'tanggal_mulai' => now()->addDays(14)->toDateString(),
            'tanggal_selesai' => now()->addDays(16)->toDateString(),
            'lokasi' => 'Bandung',
            'kuota' => 24,
            'status' => Pelatihan::STATUS_DIBUKA,
        ]);

        $pelatihanHemodialisa = Pelatihan::firstOrCreate([
            'nama' => 'Pelatihan Hemodialisa Dasar',
            'lembaga_pelatihan_id' => $lembaga->id,
        ], [
            'deskripsi' => 'Pelatihan dasar hemodialisa untuk unit ginjal.',
            'jumlah_jam' => 48,
            'tanggal_mulai' => now()->subDays(20)->toDateString(),
            'tanggal_selesai' => now()->subDays(15)->toDateString(),
            'lokasi' => 'Surabaya',
            'kuota' => 20,
            'status' => Pelatihan::STATUS_DITUTUP,
        ]);

        RiwayatPendidikan::updateOrCreate([
            'perawat_nik' => $perawatA->nik,
            'institusi_pendidikan_id' => $institusi->id,
        ], [
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
            'nomor_ijazah' => 'IJZ-001',
            'tanggal_ijazah' => '2023-08-01',
            'tahun_masuk' => 2019,
            'tahun_lulus' => 2023,
            'ipk' => 3.75,
        ]);

        RiwayatPendidikan::updateOrCreate([
            'perawat_nik' => $perawatB->nik,
            'institusi_pendidikan_id' => $institusi->id,
        ], [
            'jenjang' => 'D3',
            'program_studi' => 'Keperawatan',
            'nomor_ijazah' => 'IJZ-002',
            'tanggal_ijazah' => '2024-08-01',
            'tahun_masuk' => 2021,
            'tahun_lulus' => 2024,
            'ipk' => 3.45,
        ]);

        RiwayatPendidikan::updateOrCreate([
            'perawat_nik' => $perawatC->nik,
            'institusi_pendidikan_id' => $institusi->id,
        ], [
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
            'nomor_ijazah' => 'IJZ-003',
            'tanggal_ijazah' => '2021-08-01',
            'tahun_masuk' => 2017,
            'tahun_lulus' => 2021,
            'ipk' => 3.85,
        ]);

        RiwayatPendidikan::updateOrCreate([
            'perawat_nik' => $perawatD->nik,
            'institusi_pendidikan_id' => $institusi->id,
        ], [
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
            'nomor_ijazah' => 'IJZ-004',
            'tanggal_ijazah' => '2020-08-01',
            'tahun_masuk' => 2016,
            'tahun_lulus' => 2020,
            'ipk' => 3.6,
        ]);

        SertifikasiKompetensi::updateOrCreate([
            'perawat_nik' => $perawatA->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan ICU',
        ], [
            'nomor_sertifikat' => 'CERT-ICU-001',
            'tanggal_terbit' => '2024-02-01',
            'tanggal_kadaluarsa' => '2026-02-01',
            'status_verifikasi' => 'valid',
        ]);

        SertifikasiKompetensi::updateOrCreate([
            'perawat_nik' => $perawatC->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan IGD',
        ], [
            'nomor_sertifikat' => 'CERT-IGD-010',
            'tanggal_terbit' => '2023-06-01',
            'tanggal_kadaluarsa' => '2026-06-01',
            'status_verifikasi' => 'valid',
        ]);

        SertifikasiKompetensi::updateOrCreate([
            'perawat_nik' => $perawatD->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan NICU',
        ], [
            'nomor_sertifikat' => 'CERT-NICU-100',
            'tanggal_terbit' => '2022-06-01',
            'tanggal_kadaluarsa' => '2025-06-01',
            'status_verifikasi' => 'valid',
        ]);

        PelatihanPeserta::updateOrCreate([
            'pelatihan_id' => $pelatihanBtcls->id,
            'perawat_nik' => $perawatA->nik,
        ], [
            'status' => PelatihanPeserta::STATUS_TERDAFTAR,
            'tanggal_daftar' => now()->toDateString(),
        ]);

        PelatihanPeserta::updateOrCreate([
            'pelatihan_id' => $pelatihanBtcls->id,
            'perawat_nik' => $perawatB->nik,
        ], [
            'status' => PelatihanPeserta::STATUS_TERDAFTAR,
            'tanggal_daftar' => now()->subDay()->toDateString(),
        ]);

        PelatihanPeserta::updateOrCreate([
            'pelatihan_id' => $pelatihanAcls->id,
            'perawat_nik' => $perawatC->nik,
        ], [
            'status' => PelatihanPeserta::STATUS_TERDAFTAR,
            'tanggal_daftar' => now()->subDays(2)->toDateString(),
        ]);

        PelatihanPeserta::updateOrCreate([
            'pelatihan_id' => $pelatihanHemodialisa->id,
            'perawat_nik' => $perawatD->nik,
        ], [
            'status' => PelatihanPeserta::STATUS_LULUS,
            'tanggal_daftar' => now()->subDays(26)->toDateString(),
            'tanggal_lulus' => now()->subDays(15)->toDateString(),
            'nilai_kompetensi' => 89.5,
            'catatan' => 'Lulus baik, siap praktek terstruktur.',
        ]);

        DataLegalitas::updateOrCreate([
            'perawat_nik' => $perawatA->nik,
        ], [
            'nomor_str' => 'STR-001',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'str_berlaku_mulai' => '2024-01-01',
            'str_berlaku_sampai' => '2029-01-01',
            'sumber_validasi' => 'regulator',
            'source_verification' => 'manual',
            'terakhir_validasi_at' => now(),
            'regulator_organisasi_id' => $regulatorOrganisasi->id,
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
            'data_reviewed_at' => now(),
        ]);

        DataLegalitas::updateOrCreate([
            'perawat_nik' => $perawatB->nik,
        ], [
            'nomor_str' => 'STR-002',
            'status_str' => DataLegalitas::STATUS_STR_HOLD,
            'str_berlaku_mulai' => '2025-01-01',
            'str_berlaku_sampai' => '2030-01-01',
            'sumber_validasi' => 'perawat',
            'source_verification' => 'manual',
            'terakhir_validasi_at' => now()->subDay(),
            'regulator_organisasi_id' => $regulatorOrganisasi->id,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'data_submitted_at' => now()->subHours(10),
            'source_system' => 'perawat_portal',
            'source_actor' => 'perawat',
        ]);

        DataLegalitas::updateOrCreate([
            'perawat_nik' => $perawatE->nik,
        ], [
            'nomor_str' => 'STR-005',
            'status_str' => DataLegalitas::STATUS_STR_HOLD,
            'str_berlaku_mulai' => '2025-06-01',
            'str_berlaku_sampai' => '2030-06-01',
            'sumber_validasi' => 'regulator',
            'source_verification' => 'api',
            'terakhir_validasi_at' => now()->subHours(2),
            'regulator_organisasi_id' => $regulatorOrganisasi->id,
            'data_status' => DataLegalitas::DATA_STATUS_PENDING_SYNC,
            'data_submitted_at' => now()->subDays(2),
            'source_system' => 'regulator_api',
            'source_actor' => 'system',
        ]);

        DataLegalitas::updateOrCreate([
            'perawat_nik' => $perawatF->nik,
        ], [
            'nomor_str' => 'STR-006',
            'status_str' => DataLegalitas::STATUS_STR_HOLD,
            'str_berlaku_mulai' => '2025-03-01',
            'str_berlaku_sampai' => '2030-03-01',
            'sumber_validasi' => 'regulator',
            'source_verification' => 'manual',
            'terakhir_validasi_at' => now()->subDays(4),
            'regulator_organisasi_id' => $regulatorOrganisasi->id,
            'data_status' => DataLegalitas::DATA_STATUS_REJECTED,
            'data_submitted_at' => now()->subDays(5),
            'data_reviewed_by' => $regulatorUser->id,
            'data_reviewed_at' => now()->subDays(4),
            'data_rejection_note' => 'Nomor STR tidak ditemukan pada data regulator.',
            'source_system' => 'regulator_portal',
            'source_actor' => 'regulator',
        ]);

        DataLegalitas::updateOrCreate([
            'perawat_nik' => $perawatC->nik,
        ], [
            'nomor_str' => 'STR-003',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'str_berlaku_mulai' => '2022-01-01',
            'str_berlaku_sampai' => '2027-01-01',
            'sumber_validasi' => 'regulator',
            'source_verification' => 'manual',
            'terakhir_validasi_at' => now(),
            'regulator_organisasi_id' => $regulatorOrganisasi->id,
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
            'data_reviewed_at' => now(),
        ]);

        DataLegalitas::updateOrCreate([
            'perawat_nik' => $perawatD->nik,
        ], [
            'nomor_str' => 'STR-004',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'str_berlaku_mulai' => '2021-01-01',
            'str_berlaku_sampai' => '2026-01-01',
            'sumber_validasi' => 'regulator',
            'source_verification' => 'manual',
            'terakhir_validasi_at' => now(),
            'regulator_organisasi_id' => $regulatorOrganisasi->id,
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
            'data_reviewed_at' => now(),
        ]);

        $lowonganA = Lowongan::firstOrCreate([
            'mitra_pengguna_id' => $mitra->id,
            'judul' => 'Perawat ICU',
        ], [
            'deskripsi' => 'Shift malam, pengalaman ICU diutamakan',
            'kriteria' => [
                'status_str' => DataLegalitas::STATUS_STR_AKTIF,
                'sertifikat' => 'ICU',
            ],
            'tipe_penempatan' => Lowongan::PENEMPATAN_DOMESTIK,
            'provinsi_penempatan' => 'Jawa Timur',
            'kota_penempatan' => 'Surabaya',
            'status' => 'dibuka',
            'published_at' => now(),
        ]);

        $lowonganB = Lowongan::firstOrCreate([
            'mitra_pengguna_id' => $mitraB->id,
            'judul' => 'Perawat Rawat Jalan',
        ], [
            'deskripsi' => 'Shift pagi',
            'tipe_penempatan' => Lowongan::PENEMPATAN_DOMESTIK,
            'provinsi_penempatan' => 'Jawa Tengah',
            'kota_penempatan' => 'Semarang',
            'status' => 'dibuka',
            'published_at' => now(),
        ]);

        Lowongan::firstOrCreate([
            'mitra_pengguna_id' => $mitraC->id,
            'judul' => 'Perawat Lansia Jepang',
        ], [
            'deskripsi' => 'Penempatan Tokyo, bahasa Jepang basic diutamakan.',
            'tipe_penempatan' => Lowongan::PENEMPATAN_LUAR_NEGERI,
            'negara_penempatan' => 'Jepang',
            'kota_penempatan' => 'Tokyo',
            'status' => 'dibuka',
            'published_at' => now(),
        ]);

        Lamaran::firstOrCreate([
            'perawat_nik' => $perawatA->nik,
            'lowongan_id' => $lowonganA->id,
        ], [
            'mitra_pengguna_id' => $mitra->id,
            'status' => Lamaran::STATUS_DIAJUKAN,
            'catatan' => 'Siap interview kapan saja.',
        ]);

        Lamaran::firstOrCreate([
            'perawat_nik' => $perawatD->nik,
            'lowongan_id' => $lowonganA->id,
        ], [
            'mitra_pengguna_id' => $mitra->id,
            'status' => Lamaran::STATUS_DITOLAK,
            'catatan' => 'Pengalaman tidak sesuai kebutuhan.',
        ]);

        $riwayatAktif = RiwayatPekerjaan::firstOrCreate([
            'perawat_nik' => $perawatC->nik,
            'mitra_pengguna_id' => $mitra->id,
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
        ], [
            'posisi' => 'Perawat IGD',
            'tanggal_mulai' => now()->subMonths(6),
            'sumber_data' => RiwayatPekerjaan::SOURCE_MITRA,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
            'verified_at' => now()->subMonths(6),
            'verified_by' => $mitraUser->id,
        ]);

        $riwayatSelesai = RiwayatPekerjaan::firstOrCreate([
            'perawat_nik' => $perawatD->nik,
            'mitra_pengguna_id' => $mitra->id,
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_SELESAI,
        ], [
            'posisi' => 'Perawat NICU',
            'tanggal_mulai' => now()->subYears(2),
            'tanggal_selesai' => now()->subMonths(2),
            'sumber_data' => RiwayatPekerjaan::SOURCE_MITRA,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
            'verified_at' => now()->subMonths(2),
            'verified_by' => $mitraUser->id,
            'penilaian_kinerja' => 'Sangat baik',
            'keterangan' => 'Kontrak selesai.',
        ]);

        RiwayatPekerjaan::updateOrCreate([
            'perawat_nik' => $perawatA->nik,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat Rawat Inap (Pending)',
        ], [
            'unit' => 'Rawat Inap',
            'tanggal_mulai' => now()->subMonths(3),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'keterangan' => 'Pengajuan pengalaman kerja oleh perawat, menunggu verifikasi HRD.',
            'penilaian_kinerja' => null,
            'kinerja_rating' => null,
            'kinerja_score' => null,
            'sumber_data' => RiwayatPekerjaan::SOURCE_PERAWAT,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
            'rejected_reason' => null,
            'source_system' => 'perawat_portal',
            'source_actor' => 'perawat',
        ]);

        RiwayatPekerjaan::updateOrCreate([
            'perawat_nik' => $perawatB->nik,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat IGD (Pending Sync)',
        ], [
            'unit' => 'IGD',
            'tanggal_mulai' => now()->subMonths(8),
            'tanggal_selesai' => now()->subMonths(1),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_SELESAI,
            'keterangan' => 'Data impor HRIS mitra belum diverifikasi manual.',
            'penilaian_kinerja' => 'Baik',
            'kinerja_rating' => 4,
            'kinerja_score' => 83.5,
            'sumber_data' => RiwayatPekerjaan::SOURCE_MITRA,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
            'rejected_reason' => null,
            'source_system' => 'mitra_hris',
            'source_actor' => 'mitra',
        ]);

        RiwayatPekerjaan::updateOrCreate([
            'perawat_nik' => $perawatE->nik,
            'mitra_pengguna_id' => $mitraB->id,
            'posisi' => 'Perawat Poli Umum (Pending)',
        ], [
            'unit' => 'Poli Umum',
            'tanggal_mulai' => now()->subMonths(2),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'keterangan' => 'Pengalaman kerja dari perawat, menunggu review faskes.',
            'penilaian_kinerja' => null,
            'kinerja_rating' => null,
            'kinerja_score' => null,
            'sumber_data' => RiwayatPekerjaan::SOURCE_PERAWAT,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
            'rejected_reason' => null,
            'source_system' => 'perawat_portal',
            'source_actor' => 'perawat',
        ]);

        Rekrutmen::firstOrCreate([
            'mitra_pengguna_id' => $mitra->id,
            'perawat_nik' => $perawatC->nik,
        ], [
            'lowongan_id' => $lowonganA->id,
            'status' => Rekrutmen::STATUS_HIRED,
            'hired_at' => now()->subMonths(6),
        ]);

        Rekrutmen::firstOrCreate([
            'mitra_pengguna_id' => $mitra->id,
            'perawat_nik' => $perawatD->nik,
        ], [
            'lowongan_id' => $lowonganA->id,
            'status' => Rekrutmen::STATUS_RELEASED,
            'released_at' => now()->subMonths(2),
        ]);

        app(PerawatStatusService::class)->refresh($perawatA);
        app(PerawatStatusService::class)->refresh($perawatB);
        app(PerawatStatusService::class)->refresh($perawatC);
        app(PerawatStatusService::class)->refresh($perawatD);
        app(PerawatStatusService::class)->refresh($perawatE);
        app(PerawatStatusService::class)->refresh($perawatF);

        $tokens = [];
        $apiKeyService = app(ApiKeyService::class);

        if (!ApiKey::where('owner_type', InstitusiPendidikan::class)->where('owner_id', $institusi->id)->where('name', 'Demo Lulusan')->exists()) {
            [$apiKey, $token] = $apiKeyService->create($institusi, 'Demo Lulusan', ['lulusan:write']);
            $tokens[] = ['Lulusan', $token, $apiKey->id];
        }

        if (!ApiKey::where('owner_type', LembagaPelatihan::class)->where('owner_id', $lembaga->id)->where('name', 'Demo Sertifikasi')->exists()) {
            [$apiKey, $token] = $apiKeyService->create($lembaga, 'Demo Sertifikasi', ['sertifikasi:write']);
            $tokens[] = ['Sertifikasi', $token, $apiKey->id];
        }

        if (!ApiKey::where('owner_type', User::class)->where('owner_id', $regulatorUser->id)->where('name', 'Demo Legalitas')->exists()) {
            [$apiKey, $token] = $apiKeyService->create($regulatorUser, 'Demo Legalitas', ['legalitas:write']);
            $tokens[] = ['Legalitas', $token, $apiKey->id];
        }

        if ($this->command && !empty($tokens)) {
            $this->command->info('Demo API Keys (simpan token ini, hanya tampil sekali):');
            foreach ($tokens as [$label, $token, $id]) {
                $this->command->info($label.' | '.$id.' | '.$token);
            }
        }

        if ($this->command) {
            $this->command->info('Demo users seeded. Password default: password');
            $this->command->info('Akun: admin@demo.test, kampus@demo.test, lembaga@demo.test, mitra@demo.test, faskes@demo.test, headhunter@demo.test, regulator@demo.test, perawat@demo.test, perawat.pending@demo.test, perawat.rejected@demo.test');
        }
    }

    private function upsertUser(string $name, string $email, string $role, ?string $perawatNik = null): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'perawat_nik' => $perawatNik,
                'password' => Hash::make('password'),
            ]
        );
    }

    private function attachUserToOrganisasi(User $user, string $organisasiId, string $organisasiType): void
    {
        $exists = DB::table('organisasi_user')
            ->where('user_id', $user->id)
            ->where('organisasiable_id', $organisasiId)
            ->where('organisasiable_type', $organisasiType)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $organisasiId,
            'organisasiable_type' => $organisasiType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
