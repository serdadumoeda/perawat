<?php

namespace Database\Seeders;

use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\Lamaran;
use App\Models\LembagaPelatihan;
use App\Models\Lowongan;
use App\Models\MitraPengguna;
use App\Models\ExperienceSyncSetting;
use App\Models\IntegrationSetting;
use App\Models\Pelatihan;
use App\Models\PelatihanPeserta;
use App\Models\Perawat;
use App\Models\RegulatorOrganisasi;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPekerjaanSync;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Models\StrSyncRequest;
use App\Models\TracingAlumni;
use App\Models\User;
use App\Notifications\ExperienceSyncFailedNotification;
use App\Notifications\LamaranMasukNotification;
use App\Notifications\LamaranStatusNotification;
use App\Notifications\LegalitasDocumentStatusNotification;
use App\Notifications\LowonganBaruNotification;
use App\Notifications\RiwayatPekerjaanStatusNotification;
use App\Notifications\SertifikasiStatusNotification;
use App\Services\AuditLogService;
use App\Services\PerawatStatusService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleDummySeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('id_ID');
        $provinces = [
            'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Bali',
            'Sumatera Utara', 'Sulawesi Selatan', 'Kalimantan Timur', 'DI Yogyakarta', 'Banten',
        ];
        $cities = [
            'Jakarta', 'Bandung', 'Semarang', 'Surabaya', 'Denpasar',
            'Medan', 'Makassar', 'Balikpapan', 'Yogyakarta', 'Tangerang',
        ];

        $institusiIds = [];
        $lembagaIds = [];
        $mitraIds = [];
        $regulatorOrgIds = [];
        $perawatIds = [];
        $perawatNikById = [];
        $pelatihanIds = [];
        $lowonganIds = [];

        for ($i = 1; $i <= 10; $i++) {
            $institusi = InstitusiPendidikan::firstOrCreate([
                'nama' => 'Institusi Pendidikan Dummy '.$i,
            ], [
                'jenis' => 'kampus',
                'akreditasi' => 'A',
                'alamat' => $faker->streetAddress(),
                'kota' => $cities[$i - 1],
                'provinsi' => $provinces[$i - 1],
                'email' => 'kampus'.$i.'@dummy.test',
                'telepon' => '021'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
            ]);
            $institusiIds[] = $institusi->id;

            $lembaga = LembagaPelatihan::firstOrCreate([
                'nama' => 'Lembaga Pelatihan Dummy '.$i,
            ], [
                'jenis' => 'pelatihan',
                'akreditasi' => 'B',
                'alamat' => $faker->streetAddress(),
                'kota' => $cities[$i - 1],
                'provinsi' => $provinces[$i - 1],
                'email' => 'lembaga'.$i.'@dummy.test',
                'telepon' => '022'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
            ]);
            $lembagaIds[] = $lembaga->id;

            $mitra = MitraPengguna::firstOrCreate([
                'nama' => 'Mitra Pengguna Dummy '.$i,
            ], [
                'jenis' => $i % 2 === 0 ? 'agency' : 'rumah_sakit',
                'kategori' => $i % 2 === 0 ? 'Agency' : 'RS',
                'wilayah_operasi' => $provinces[$i - 1],
                'alamat' => $faker->streetAddress(),
                'kota' => $cities[$i - 1],
                'provinsi' => $provinces[$i - 1],
                'email' => 'mitra'.$i.'@dummy.test',
                'telepon' => '023'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
            ]);
            $mitraIds[] = $mitra->id;

            $regulatorOrg = RegulatorOrganisasi::firstOrCreate([
                'nama' => 'Regulator Dummy '.$i,
            ], [
                'kode' => 'REG-DUMMY-'.$i,
                'tingkat' => $i % 2 === 0 ? 'provinsi' : 'kabupaten_kota',
                'wilayah' => $provinces[$i - 1],
                'alamat' => $faker->streetAddress(),
                'email' => 'regulator'.$i.'@dummy.test',
                'telepon' => '024'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
            $regulatorOrgIds[] = $regulatorOrg->id;
        }

        for ($i = 1; $i <= 10; $i++) {
            $nik = sprintf('99%014d', $i);
            $perawat = Perawat::updateOrCreate([
                'nik' => $nik,
            ], [
                'nama' => $faker->name(),
                'email' => 'perawat'.$i.'@dummy.test',
                'no_hp' => '08'.str_pad((string) $i, 10, '0', STR_PAD_LEFT),
                'jenis_kelamin' => $i % 2 === 0 ? 'P' : 'L',
                'tempat_lahir' => $cities[$i - 1],
                'tanggal_lahir' => now()->subYears(22 + $i)->toDateString(),
                'alamat' => $faker->streetAddress(),
                'kota' => $cities[$i - 1],
                'provinsi' => $provinces[$i - 1],
                'status_kesiapan' => Perawat::KESIAPAN_DRAFT,
                'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_FRESH,
                'status_ketersediaan' => Perawat::KETERSEDIAAN_AVAILABLE,
            ]);
            $perawatIds[] = $perawat->id;
            $perawatNikById[$perawat->id] = $perawat->nik;
        }

        $auditLog = app(AuditLogService::class);

        $adminUser = $this->upsertUser('Admin Dummy', 'admin@dummy.test', User::ROLE_ADMIN);
        $regulatorUser = $this->upsertUser('Regulator Dummy', 'regulator@dummy.test', User::ROLE_REGULATOR);
        $kampusUser = $this->upsertUser('Kampus Dummy', 'kampus@dummy.test', User::ROLE_KAMPUS);
        $lembagaUser = $this->upsertUser('Lembaga Dummy', 'lembaga@dummy.test', User::ROLE_LEMBAGA);
        $mitraUser = $this->upsertUser('Mitra Dummy', 'mitra@dummy.test', User::ROLE_MITRA);
        $faskesUser = $this->upsertUser('Faskes Dummy', 'faskes@dummy.test', User::ROLE_FASKES);
        $headhunterUser = $this->upsertUser('Headhunter Dummy', 'headhunter@dummy.test', User::ROLE_HEADHUNTER);

        if (!empty($institusiIds[0])) {
            $this->attachUserToOrganisasi($kampusUser, $institusiIds[0], InstitusiPendidikan::class);
        }
        if (!empty($lembagaIds[0])) {
            $this->attachUserToOrganisasi($lembagaUser, $lembagaIds[0], LembagaPelatihan::class);
        }
        if (!empty($mitraIds[0])) {
            $this->attachUserToOrganisasi($mitraUser, $mitraIds[0], MitraPengguna::class);
        }
        if (!empty($mitraIds[1])) {
            $this->attachUserToOrganisasi($faskesUser, $mitraIds[1], MitraPengguna::class);
        }
        if (!empty($mitraIds[2])) {
            $this->attachUserToOrganisasi($headhunterUser, $mitraIds[2], MitraPengguna::class);
        }
        if (!empty($regulatorOrgIds[0])) {
            $this->attachUserToOrganisasi($regulatorUser, $regulatorOrgIds[0], RegulatorOrganisasi::class);
        }

        $perawatUserMap = [];
        foreach ($perawatIds as $index => $perawatId) {
            $perawat = Perawat::find($perawatId);
            if (!$perawat) {
                continue;
            }
            $email = 'perawat'.($index + 1).'@dummy.test';
            $user = $this->upsertUser($perawat->nama, $email, User::ROLE_PERAWAT, $perawat->nik);
            $perawatUserMap[$perawat->id] = $user;
        }

        foreach (IntegrationSetting::PROVIDERS as $provider) {
            IntegrationSetting::updateOrCreate(
                ['provider' => $provider],
                [
                    'base_url' => 'https://api.'.$provider.'.dummy.test',
                    'api_key' => strtoupper($provider).'-DUMMY-KEY',
                    'client_id' => strtoupper($provider).'-CLIENT',
                    'client_secret' => 'secret-'.$provider,
                    'username' => $provider.'_user',
                    'password' => 'password',
                    'is_active' => false,
                    'notes' => 'Dummy integration setting (belum aktif)',
                    'last_synced_at' => null,
                ]
            );
        }

        foreach ($mitraIds as $idx => $mitraId) {
            ExperienceSyncSetting::updateOrCreate(
                ['mitra_pengguna_id' => $mitraId],
                [
                    'base_url' => 'https://sync.mitra'.($idx + 1).'.dummy.test',
                    'api_key' => 'SYNC-KEY-'.$idx,
                    'client_id' => 'SYNC-CLIENT-'.$idx,
                    'client_secret' => 'SYNC-SECRET-'.$idx,
                    'username' => 'sync_user_'.$idx,
                    'password' => 'password',
                    'sync_mode' => ExperienceSyncSetting::MODE_PULL,
                    'is_active' => $idx % 2 === 0,
                    'notes' => 'Dummy sync setting',
                    'last_synced_at' => now()->subDays($idx),
                ]
            );
        }

        for ($i = 1; $i <= 10; $i++) {
            $pelatihan = Pelatihan::firstOrCreate([
                'nama' => 'Pelatihan Dummy '.$i,
                'lembaga_pelatihan_id' => $lembagaIds[$i - 1],
            ], [
                'deskripsi' => 'Pelatihan dummy '.$i,
                'jumlah_jam' => 40,
                'tanggal_mulai' => now()->addDays($i)->toDateString(),
                'tanggal_selesai' => now()->addDays($i + 2)->toDateString(),
                'lokasi' => $cities[$i - 1],
                'kuota' => 25,
                'status' => 'dibuka',
            ]);
            $pelatihanIds[] = $pelatihan->id;

            $tipePenempatan = $i % 2 === 0 ? Lowongan::PENEMPATAN_LUAR_NEGERI : Lowongan::PENEMPATAN_DOMESTIK;
            $lowongan = Lowongan::firstOrCreate([
                'mitra_pengguna_id' => $mitraIds[$i - 1],
                'judul' => 'Lowongan Dummy '.$i,
            ], [
                'deskripsi' => 'Deskripsi lowongan dummy '.$i,
                'kriteria' => [
                    'status_str' => DataLegalitas::STATUS_STR_AKTIF,
                    'sertifikat' => 'BTCLS',
                ],
                'tipe_penempatan' => $tipePenempatan,
                'negara_penempatan' => $tipePenempatan === Lowongan::PENEMPATAN_LUAR_NEGERI ? 'Jepang' : null,
                'provinsi_penempatan' => $tipePenempatan === Lowongan::PENEMPATAN_DOMESTIK ? $provinces[$i - 1] : null,
                'kota_penempatan' => $cities[$i - 1],
                'status' => 'dibuka',
                'published_at' => now(),
            ]);
            $lowonganIds[] = $lowongan->id;
        }

        for ($i = 1; $i <= 10; $i++) {
            $perawatId = $perawatIds[$i - 1];
            $perawatNik = $perawatNikById[$perawatId] ?? null;
            $institusiId = $institusiIds[$i - 1];
            $lembagaId = $lembagaIds[$i - 1];
            $mitraId = $mitraIds[$i - 1];
            $regulatorOrgId = $regulatorOrgIds[$i - 1] ?? $regulatorOrgIds[0] ?? null;
            $pelatihanId = $pelatihanIds[$i - 1];
            $lowonganId = $lowonganIds[$i - 1];

            RiwayatPendidikan::updateOrCreate([
                'perawat_nik' => $perawatNik,
                'institusi_pendidikan_id' => $institusiId,
            ], [
                'jenjang' => 'S1',
                'program_studi' => 'Keperawatan',
                'nomor_ijazah' => 'IJZ-DUMMY-'.$i,
                'tanggal_ijazah' => now()->subYears(2)->toDateString(),
                'tahun_masuk' => now()->subYears(6)->year,
                'tahun_lulus' => now()->subYears(2)->year,
                'ipk' => 3.0 + ($i / 100),
                'status_verifikasi' => 'verified',
            ]);

            SertifikasiKompetensi::firstOrCreate([
                'perawat_nik' => $perawatNik,
                'lembaga_pelatihan_id' => $lembagaId,
                'nama_sertifikat' => 'Sertifikat Dummy '.$i,
            ], [
                'pelatihan_id' => $pelatihanId,
                'nomor_sertifikat' => 'CERT-DUMMY-'.$i,
                'tanggal_terbit' => now()->subMonths(6)->toDateString(),
                'tanggal_kadaluarsa' => now()->addYears(2)->toDateString(),
                'status_verifikasi' => SertifikasiKompetensi::VERIF_VALID,
                'jumlah_jam' => 40,
                'nilai_kompetensi' => 85 + $i,
            ]);

            $strDocPath = 'legalitas/str/str-dummy-'.$i.'.txt';
            if (!Storage::disk('public')->exists($strDocPath)) {
                Storage::disk('public')->put($strDocPath, 'Dummy STR document '.$i);
            }
            $strDocStatus = match ($i % 3) {
                0 => DataLegalitas::DOC_STATUS_REJECTED,
                1 => DataLegalitas::DOC_STATUS_PENDING,
                default => DataLegalitas::DOC_STATUS_APPROVED,
            };
            $strReviewedBy = $strDocStatus === DataLegalitas::DOC_STATUS_PENDING ? null : $regulatorUser?->id;
            $strReviewedAt = $strReviewedBy ? now()->subDays(5) : null;
            $strNote = $strDocStatus === DataLegalitas::DOC_STATUS_REJECTED ? 'Dokumen STR kurang jelas.' : null;

            $izinDocPath = null;
            $izinDocStatus = null;
            $izinReviewedBy = null;
            $izinReviewedAt = null;
            $izinNote = null;
            if ($i % 2 === 0) {
                $izinDocPath = 'legalitas/izin_luar_negeri/izin-dummy-'.$i.'.txt';
                if (!Storage::disk('public')->exists($izinDocPath)) {
                    Storage::disk('public')->put($izinDocPath, 'Dummy izin luar negeri '.$i);
                }
                $izinDocStatus = $i % 4 === 0 ? DataLegalitas::DOC_STATUS_APPROVED : DataLegalitas::DOC_STATUS_PENDING;
                $izinReviewedBy = $izinDocStatus === DataLegalitas::DOC_STATUS_PENDING ? null : $regulatorUser?->id;
                $izinReviewedAt = $izinReviewedBy ? now()->subDays(3) : null;
                $izinNote = $izinDocStatus === DataLegalitas::DOC_STATUS_APPROVED ? 'Dokumen lengkap.' : null;
            }

            $legalitas = DataLegalitas::updateOrCreate([
                'perawat_nik' => $perawatNik,
            ], [
                'nomor_str' => 'STR-DUMMY-'.$i,
                'nomor_sipp' => 'SIPP-DUMMY-'.$i,
                'status_str' => DataLegalitas::STATUS_STR_AKTIF,
                'str_berlaku_mulai' => now()->subYears(1)->toDateString(),
                'str_berlaku_sampai' => now()->addYears(4)->toDateString(),
                'nomor_izin_luar_negeri' => $i % 2 === 0 ? 'LN-DUMMY-'.$i : null,
                'status_izin_luar_negeri' => $i % 2 === 0 ? 'aktif' : null,
                'str_document_path' => $strDocPath,
                'str_document_status' => $strDocStatus,
                'str_document_reviewed_by' => $strReviewedBy,
                'str_document_reviewed_at' => $strReviewedAt,
                'str_document_note' => $strNote,
                'izin_luar_negeri_document_path' => $izinDocPath,
                'izin_luar_negeri_document_status' => $izinDocStatus,
                'izin_luar_negeri_document_reviewed_by' => $izinReviewedBy,
                'izin_luar_negeri_document_reviewed_at' => $izinReviewedAt,
                'izin_luar_negeri_document_note' => $izinNote,
                'sumber_validasi' => 'regulator',
                'source_verification' => 'manual',
                'regulator_organisasi_id' => $regulatorOrgId,
                'terakhir_validasi_at' => now(),
                'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
                'data_reviewed_at' => now(),
            ]);

            if (!empty($perawatUserMap[$perawatId])) {
                $auditLog->log($perawatUserMap[$perawatId], 'legalitas.document.submit', $legalitas, [
                    'document_type' => 'str',
                    'status' => $strDocStatus,
                ]);
                if ($izinDocPath) {
                    $auditLog->log($perawatUserMap[$perawatId], 'legalitas.document.submit', $legalitas, [
                        'document_type' => 'izin_luar_negeri',
                        'status' => $izinDocStatus ?? DataLegalitas::DOC_STATUS_PENDING,
                    ]);
                }
            }

            if ($strDocStatus !== DataLegalitas::DOC_STATUS_PENDING && $regulatorUser) {
                $auditLog->log($regulatorUser, 'legalitas.document.review', $legalitas, [
                    'document_type' => 'str',
                    'status' => $strDocStatus,
                    'note' => $strNote,
                    'regulator_organisasi_id' => $regulatorOrgId,
                ]);
                if (!empty($perawatUserMap[$perawatId])) {
                    Notification::send($perawatUserMap[$perawatId], new LegalitasDocumentStatusNotification($legalitas, 'str', $strDocStatus, $strNote, $strDocStatus === DataLegalitas::DOC_STATUS_APPROVED ? 'Dokumen STR disetujui.' : 'Dokumen STR ditolak.'));
                }
            }

            if ($izinDocPath && $izinDocStatus !== DataLegalitas::DOC_STATUS_PENDING && $regulatorUser) {
                $auditLog->log($regulatorUser, 'legalitas.document.review', $legalitas, [
                    'document_type' => 'izin_luar_negeri',
                    'status' => $izinDocStatus,
                    'note' => $izinNote,
                    'regulator_organisasi_id' => $regulatorOrgId,
                ]);
                if (!empty($perawatUserMap[$perawatId])) {
                    Notification::send($perawatUserMap[$perawatId], new LegalitasDocumentStatusNotification($legalitas, 'izin_luar_negeri', $izinDocStatus, $izinNote, 'Dokumen izin luar negeri disetujui.'));
                }
            }

            PelatihanPeserta::firstOrCreate([
                'pelatihan_id' => $pelatihanId,
                'perawat_nik' => $perawatNik,
            ], [
                'status' => $i % 2 === 0 ? PelatihanPeserta::STATUS_LULUS : PelatihanPeserta::STATUS_TERDAFTAR,
                'tanggal_daftar' => now()->subDays(10)->toDateString(),
                'tanggal_lulus' => $i % 2 === 0 ? now()->subDays(2)->toDateString() : null,
                'nilai_kompetensi' => $i % 2 === 0 ? 80 + $i : null,
            ]);

            TracingAlumni::updateOrCreate([
                'perawat_nik' => $perawatNik,
                'institusi_pendidikan_id' => $institusiId,
            ], [
                'status' => $i % 2 === 0 ? TracingAlumni::STATUS_BEKERJA : TracingAlumni::STATUS_BELUM_BEKERJA,
                'instansi' => $i % 2 === 0 ? 'RS Dummy '.$i : null,
                'posisi' => $i % 2 === 0 ? 'Perawat' : null,
                'kota' => $cities[$i - 1],
                'provinsi' => $provinces[$i - 1],
                'tanggal_mulai' => $i % 2 === 0 ? now()->subMonths(6)->toDateString() : null,
                'catatan' => 'Tracing alumni dummy '.$i,
            ]);

            RiwayatPekerjaan::firstOrCreate([
                'perawat_nik' => $perawatNik,
                'mitra_pengguna_id' => $mitraId,
                'posisi' => 'Perawat Unit '.$i,
            ], [
                'unit' => 'Unit '.$i,
                'tanggal_mulai' => now()->subMonths(12)->toDateString(),
                'status_pekerjaan' => $i % 2 === 0 ? RiwayatPekerjaan::STATUS_AKTIF : RiwayatPekerjaan::STATUS_SELESAI,
                'keterangan' => 'Riwayat kerja dummy '.$i,
                'penilaian_kinerja' => 'Baik',
                'kinerja_rating' => $i % 2 === 0 ? null : 4,
                'kinerja_score' => $i % 2 === 0 ? null : 86.5,
                'sumber_data' => RiwayatPekerjaan::SOURCE_MITRA,
                'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
                'is_verified' => true,
                'verified_at' => now()->subMonths(1),
            ]);

            Lamaran::firstOrCreate([
                'perawat_nik' => $perawatNik,
                'lowongan_id' => $lowonganId,
            ], [
                'mitra_pengguna_id' => $mitraId,
                'status' => 'diajukan',
                'catatan' => 'Lamaran dummy '.$i,
            ]);

            Rekrutmen::firstOrCreate([
                'mitra_pengguna_id' => $mitraId,
                'perawat_nik' => $perawatNik,
            ], [
                'lowongan_id' => $lowonganId,
                'status' => Rekrutmen::STATUS_LOCKED,
                'locked_at' => now()->subDays(3),
            ]);
        }

        $samplePerawatId = $perawatIds[0] ?? null;
        $samplePerawatIdB = $perawatIds[1] ?? null;
        $samplePerawatIdC = $perawatIds[2] ?? null;
        $samplePerawatNik = $samplePerawatId ? ($perawatNikById[$samplePerawatId] ?? null) : null;
        $samplePerawatNikB = $samplePerawatIdB ? ($perawatNikById[$samplePerawatIdB] ?? null) : null;
        $samplePerawatNikC = $samplePerawatIdC ? ($perawatNikById[$samplePerawatIdC] ?? null) : null;
        $sampleLowonganId = $lowonganIds[0] ?? null;
        $sampleMitraId = $mitraIds[0] ?? null;

        if ($samplePerawatNik && $samplePerawatNikB && $samplePerawatNikC) {
            StrSyncRequest::updateOrCreate(
                ['perawat_nik' => $samplePerawatNik, 'status' => StrSyncRequest::STATUS_PENDING],
                [
                    'nomor_str' => 'STR-PENDING-001',
                    'requested_by' => $perawatUserMap[$samplePerawatId]?->id,
                    'requested_role' => User::ROLE_PERAWAT,
                    'catatan' => 'Permintaan STR dummy (pending).',
                ]
            );

            StrSyncRequest::updateOrCreate(
                ['perawat_nik' => $samplePerawatNikB, 'status' => StrSyncRequest::STATUS_SYNCED],
                [
                    'nomor_str' => 'STR-SYNCED-002',
                    'requested_by' => $perawatUserMap[$samplePerawatIdB]?->id,
                    'requested_role' => User::ROLE_PERAWAT,
                    'processed_by' => $regulatorUser?->id,
                    'processed_at' => now()->subDays(2),
                    'response_note' => 'Sinkronisasi dummy disetujui.',
                ]
            );

            StrSyncRequest::updateOrCreate(
                ['perawat_nik' => $samplePerawatNikC, 'status' => StrSyncRequest::STATUS_REJECTED],
                [
                    'nomor_str' => 'STR-REJECT-003',
                    'requested_by' => $perawatUserMap[$samplePerawatIdC]?->id,
                    'requested_role' => User::ROLE_PERAWAT,
                    'processed_by' => $regulatorUser?->id,
                    'processed_at' => now()->subDays(1),
                    'response_note' => 'Data belum lengkap (dummy).',
                ]
            );
        }

        foreach (array_slice($mitraIds, 0, 3) as $idx => $mitraId) {
            $perawatId = $perawatIds[$idx] ?? null;
            $perawatNik = $perawatId ? ($perawatNikById[$perawatId] ?? null) : null;
            $source = 'dummy-sync-'.$mitraId;
            $externalId = 'SYNC-'.$idx;
            $status = $idx % 2 === 0 ? RiwayatPekerjaanSync::STATUS_SUCCESS : RiwayatPekerjaanSync::STATUS_FAILED;

            RiwayatPekerjaanSync::updateOrCreate(
                ['source_system' => $source, 'external_id' => $externalId],
                [
                    'perawat_nik' => $perawatNik,
                    'mitra_pengguna_id' => $mitraId,
                    'riwayat_pekerjaan_id' => null,
                    'status' => $status,
                    'last_synced_at' => now()->subHours($idx + 1),
                    'payload' => ['items' => 1, 'mode' => 'pull'],
                    'error_message' => $status === RiwayatPekerjaanSync::STATUS_FAILED ? 'Timeout dummy' : null,
                ]
            );
        }

        if ($samplePerawatNik && !empty($perawatUserMap[$samplePerawatId])) {
            $perawatUser = $perawatUserMap[$samplePerawatId];
            $lowongan = $sampleLowonganId ? Lowongan::find($sampleLowonganId) : null;
            $lamaran = Lamaran::where('perawat_nik', $samplePerawatNik)->first();
            $sertifikasi = SertifikasiKompetensi::where('perawat_nik', $samplePerawatNik)->first();
            $riwayat = RiwayatPekerjaan::where('perawat_nik', $samplePerawatNik)->first();

            if ($lowongan) {
                Notification::send($perawatUser, new LowonganBaruNotification($lowongan));
            }
            if ($lamaran) {
                Notification::send($perawatUser, new LamaranStatusNotification($lamaran, 'Lamaran Anda sedang diproses (dummy).'));
            }
            if ($sertifikasi) {
                Notification::send($perawatUser, new SertifikasiStatusNotification($sertifikasi, 'Sertifikasi tervalidasi (dummy).'));
            }
            if ($riwayat) {
                Notification::send($perawatUser, new RiwayatPekerjaanStatusNotification($riwayat, 'Riwayat pekerjaan terverifikasi (dummy).'));
            }
        }

        if ($mitraUser && $sampleMitraId) {
            $mitra = MitraPengguna::find($sampleMitraId);
            if ($mitra) {
                Notification::send($mitraUser, new ExperienceSyncFailedNotification($mitra, 3, 'Timeout dummy'));
            }
            $lamaran = Lamaran::where('mitra_pengguna_id', $sampleMitraId)->latest()->first();
            if ($lamaran) {
                Notification::send($mitraUser, new LamaranMasukNotification($lamaran));
            }
        }

        foreach ($perawatIds as $perawatId) {
            $perawat = Perawat::find($perawatId);
            if ($perawat) {
                app(PerawatStatusService::class)->refresh($perawat);
            }
        }

        if ($this->command) {
            $this->command->info('ModuleDummySeeder: 10 data dummy untuk setiap modul berhasil dibuat.');
        }
    }

    private function upsertUser(string $name, string $email, string $role, ?string $perawatNik = null): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'status' => User::STATUS_ACTIVE,
                'approved_at' => now(),
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
