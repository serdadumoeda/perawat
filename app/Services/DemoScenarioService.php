<?php

namespace App\Services;

use App\Models\DataLegalitas;
use App\Models\DemoScenarioEvent;
use App\Models\DemoScenarioRun;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\Lowongan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\RegulatorOrganisasi;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class DemoScenarioService
{
    public const DEFAULT_PERAWAT_NIK = '9000000000000001';

    public function __construct(
        private readonly PerawatStatusService $statusService,
        private readonly AuditLogService $auditLog,
    ) {
    }

    public function runSinglePerawatTimeline(?string $nik = null): DemoScenarioRun
    {
        if (app()->environment('production')) {
            throw new \LogicException('Demo scenario tidak dapat dijalankan di environment production.');
        }

        $nik = $this->normalizeNik($nik);

        $run = DemoScenarioRun::create([
            'scenario_name' => 'single_perawat_living_cv',
            'scenario_key' => (string) Str::uuid(),
            'perawat_nik' => $nik,
            'status' => DemoScenarioRun::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        try {
            DB::transaction(function () use ($run, $nik): void {
                [$kampus, $lembaga, $mitra, $regulatorOrg] = $this->resolveOrganisations();
                [$kampusAdmin, $lembagaAdmin, $regulatorAdmin, $mitraAdmin] = $this->resolveActors($kampus, $lembaga, $mitra, $regulatorOrg);

                $perawat = $this->resolvePerawat($nik);
                $this->resetPerawatTimelineData($perawat);

                $run->update([
                    'perawat_id' => $perawat->id,
                ]);

                $pendidikan = RiwayatPendidikan::create([
                    'perawat_id' => $perawat->id,
                    'perawat_nik' => $perawat->nik,
                    'institusi_pendidikan_id' => $kampus->id,
                    'jenjang' => 'S1',
                    'program_studi' => 'Keperawatan',
                    'nomor_ijazah' => 'IJZ-SCN-'.substr($perawat->nik, -6),
                    'tanggal_ijazah' => now()->subYear()->toDateString(),
                    'tahun_masuk' => (int) now()->subYears(5)->format('Y'),
                    'tahun_lulus' => (int) now()->subYear()->format('Y'),
                    'ipk' => 3.72,
                    'status_verifikasi' => 'verified',
                ]);

                $this->recordEvent(
                    $run,
                    stepOrder: 1,
                    stepKey: 'kampus.input_lulusan',
                    actor: $kampusAdmin,
                    action: 'Kampus menginput data lulusan perawat',
                    subject: $pendidikan,
                    payload: [
                        'institusi' => $kampus->nama,
                        'perawat_nik' => $perawat->nik,
                        'nomor_ijazah' => $pendidikan->nomor_ijazah,
                    ],
                );

                $sertifikasi = SertifikasiKompetensi::create([
                    'perawat_id' => $perawat->id,
                    'perawat_nik' => $perawat->nik,
                    'lembaga_pelatihan_id' => $lembaga->id,
                    'nama_sertifikat' => 'BTCLS',
                    'nomor_sertifikat' => 'SERT-SCN-'.substr($perawat->nik, -6),
                    'tanggal_terbit' => now()->subMonths(3)->toDateString(),
                    'tanggal_kadaluarsa' => now()->addYears(2)->toDateString(),
                    'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
                    'jumlah_jam' => 48,
                    'nilai_kompetensi' => 90.5,
                ]);

                $this->recordEvent(
                    $run,
                    stepOrder: 2,
                    stepKey: 'lembaga.input_sertifikat',
                    actor: $lembagaAdmin,
                    action: 'Lembaga menginput sertifikat kompetensi',
                    subject: $sertifikasi,
                    payload: [
                        'lembaga' => $lembaga->nama,
                        'nama_sertifikat' => $sertifikasi->nama_sertifikat,
                        'status_verifikasi' => $sertifikasi->status_verifikasi,
                    ],
                );

                $legalitas = DataLegalitas::create([
                    'perawat_id' => $perawat->id,
                    'perawat_nik' => $perawat->nik,
                    'nomor_str' => 'STR-SCN-'.substr($perawat->nik, -6),
                    'status_str' => DataLegalitas::STATUS_STR_AKTIF,
                    'str_berlaku_mulai' => now()->toDateString(),
                    'str_berlaku_sampai' => now()->addYears(5)->toDateString(),
                    'sumber_validasi' => 'regulator',
                    'source_verification' => 'manual',
                    'terakhir_validasi_at' => now(),
                    'regulator_organisasi_id' => $regulatorOrg->id,
                    'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
                    'data_submitted_at' => now()->subMinutes(10),
                    'data_reviewed_by' => $regulatorAdmin->id,
                    'data_reviewed_at' => now(),
                ]);

                $this->recordEvent(
                    $run,
                    stepOrder: 3,
                    stepKey: 'regulator.activate_str',
                    actor: $regulatorAdmin,
                    action: 'Regulator mengaktifkan STR',
                    subject: $legalitas,
                    payload: [
                        'nomor_str' => $legalitas->nomor_str,
                        'status_str' => $legalitas->status_str,
                        'data_status' => $legalitas->data_status,
                        'regulator_organisasi' => $regulatorOrg->nama,
                    ],
                );

                $lowongan = Lowongan::updateOrCreate(
                    [
                        'mitra_pengguna_id' => $mitra->id,
                        'judul' => 'Demo Hiring Perawat ICU',
                    ],
                    [
                        'deskripsi' => 'Lowongan skenario demo untuk validasi alur rekrutmen.',
                        'kriteria' => [
                            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
                            'sertifikat' => 'BTCLS',
                        ],
                        'tipe_penempatan' => Lowongan::PENEMPATAN_DOMESTIK,
                        'provinsi_penempatan' => 'DKI Jakarta',
                        'kota_penempatan' => 'Jakarta',
                        'status' => 'dibuka',
                        'published_at' => now(),
                    ]
                );

                $rekrutmen = Rekrutmen::updateOrCreate(
                    [
                        'mitra_pengguna_id' => $mitra->id,
                        'perawat_nik' => $perawat->nik,
                    ],
                    [
                        'perawat_id' => $perawat->id,
                        'lowongan_id' => $lowongan->id,
                        'status' => Rekrutmen::STATUS_HIRED,
                        'locked_at' => now()->subDays(2),
                        'hired_at' => now()->subDay(),
                        'released_at' => null,
                    ]
                );

                $this->recordEvent(
                    $run,
                    stepOrder: 4,
                    stepKey: 'mitra.hire',
                    actor: $mitraAdmin,
                    action: 'Mitra melakukan hiring perawat',
                    subject: $rekrutmen,
                    payload: [
                        'mitra' => $mitra->nama,
                        'lowongan' => $lowongan->judul,
                        'status' => $rekrutmen->status,
                    ],
                );

                $riwayat = RiwayatPekerjaan::create([
                    'perawat_id' => $perawat->id,
                    'perawat_nik' => $perawat->nik,
                    'mitra_pengguna_id' => $mitra->id,
                    'posisi' => 'Perawat ICU',
                    'unit' => 'Intensive Care Unit',
                    'tanggal_mulai' => now()->subMonths(8)->toDateString(),
                    'tanggal_selesai' => now()->subDays(7)->toDateString(),
                    'status_pekerjaan' => RiwayatPekerjaan::STATUS_SELESAI,
                    'keterangan' => 'Kontrak selesai, data disinkronkan mitra.',
                    'sumber_data' => RiwayatPekerjaan::SOURCE_MITRA,
                    'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
                    'is_verified' => true,
                    'verified_by' => $mitraAdmin->id,
                    'verified_at' => now()->subDays(7),
                    'penilaian_kinerja' => 'Sangat baik',
                    'kinerja_rating' => 5,
                    'kinerja_score' => 92.5,
                ]);

                $this->recordEvent(
                    $run,
                    stepOrder: 5,
                    stepKey: 'mitra.update_pengalaman',
                    actor: $mitraAdmin,
                    action: 'Mitra memperbarui pengalaman kerja perawat',
                    subject: $riwayat,
                    payload: [
                        'status_pekerjaan' => $riwayat->status_pekerjaan,
                        'verifikasi_status' => $riwayat->verifikasi_status,
                        'kinerja_score' => $riwayat->kinerja_score,
                    ],
                );

                $this->statusService->refresh($perawat->fresh());

                $run->update([
                    'status' => DemoScenarioRun::STATUS_COMPLETED,
                    'finished_at' => now(),
                    'summary' => [
                        'perawat_nik' => $perawat->nik,
                        'perawat_nama' => $perawat->nama,
                        'status_kesiapan' => $perawat->fresh()->status_kesiapan,
                        'status_ketenagakerjaan' => $perawat->fresh()->status_ketenagakerjaan,
                        'event_count' => 5,
                    ],
                ]);
            });
        } catch (Throwable $exception) {
            $run->update([
                'status' => DemoScenarioRun::STATUS_FAILED,
                'finished_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $run->fresh('events');
    }

    private function normalizeNik(?string $nik): string
    {
        $nik = trim((string) $nik);

        if ($nik !== '' && preg_match('/^\d{16}$/', $nik) === 1) {
            return $nik;
        }

        return self::DEFAULT_PERAWAT_NIK;
    }

    private function resolveOrganisations(): array
    {
        $kampus = InstitusiPendidikan::firstOrCreate(
            ['nama' => 'Kampus Demo Timeline'],
            [
                'alamat' => 'Jl. Pendidikan No. 1',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'email' => 'timeline-kampus@demo.test',
                'telepon' => '022000111',
                'akreditasi' => 'A',
            ]
        );

        $lembaga = LembagaPelatihan::firstOrCreate(
            ['nama' => 'Lembaga Demo Timeline'],
            [
                'alamat' => 'Jl. Kompetensi No. 2',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
                'email' => 'timeline-lembaga@demo.test',
                'telepon' => '021000222',
                'akreditasi' => 'A',
            ]
        );

        $mitra = MitraPengguna::firstOrCreate(
            ['nama' => 'RS Demo Timeline'],
            [
                'jenis' => 'rumah_sakit',
                'kategori' => 'RS',
                'wilayah_operasi' => 'Nasional',
                'alamat' => 'Jl. Karir No. 3',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
                'email' => 'timeline-mitra@demo.test',
                'telepon' => '021000333',
            ]
        );

        $regulatorOrg = RegulatorOrganisasi::firstOrCreate(
            ['nama' => 'Dinas Kesehatan Demo Timeline'],
            [
                'kode' => 'DINKES-TL',
                'tingkat' => 'provinsi',
                'wilayah' => 'DKI Jakarta',
                'alamat' => 'Jl. Sehat No. 4',
                'email' => 'timeline-regulator-org@demo.test',
                'telepon' => '021000444',
                'is_active' => true,
            ]
        );

        return [$kampus, $lembaga, $mitra, $regulatorOrg];
    }

    private function resolveActors(
        InstitusiPendidikan $kampus,
        LembagaPelatihan $lembaga,
        MitraPengguna $mitra,
        RegulatorOrganisasi $regulatorOrg,
    ): array {
        $kampusAdmin = $this->upsertUser('Kampus Timeline', 'timeline-kampus-admin@demo.test', User::ROLE_KAMPUS);
        $lembagaAdmin = $this->upsertUser('Lembaga Timeline', 'timeline-lembaga-admin@demo.test', User::ROLE_LEMBAGA);
        $regulatorAdmin = $this->upsertUser('Regulator Timeline', 'timeline-regulator@demo.test', User::ROLE_REGULATOR);
        $mitraAdmin = $this->upsertUser('Mitra Timeline', 'timeline-mitra-admin@demo.test', User::ROLE_MITRA);

        $this->attachUserToOrganisation($kampusAdmin, $kampus->id, InstitusiPendidikan::class);
        $this->attachUserToOrganisation($lembagaAdmin, $lembaga->id, LembagaPelatihan::class);
        $this->attachUserToOrganisation($mitraAdmin, $mitra->id, MitraPengguna::class);
        $this->attachUserToOrganisation($regulatorAdmin, $regulatorOrg->id, RegulatorOrganisasi::class);

        return [$kampusAdmin, $lembagaAdmin, $regulatorAdmin, $mitraAdmin];
    }

    private function resolvePerawat(string $nik): Perawat
    {
        $perawat = Perawat::updateOrCreate(
            ['nik' => $nik],
            [
                'nama' => 'Perawat Demo Skenario',
                'email' => 'timeline-perawat@demo.test',
                'no_hp' => '081234560001',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2001-02-14',
                'alamat' => 'Jl. Perawat Demo 1',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'status_kesiapan' => Perawat::KESIAPAN_DRAFT,
                'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_FRESH,
                'status_ketersediaan' => Perawat::KETERSEDIAAN_AVAILABLE,
            ]
        );

        $this->upsertUser(
            'Perawat Demo Skenario',
            'timeline-perawat@demo.test',
            User::ROLE_PERAWAT,
            $perawat->nik
        );

        return $perawat;
    }

    private function resetPerawatTimelineData(Perawat $perawat): void
    {
        Rekrutmen::where('perawat_nik', $perawat->nik)->delete();
        RiwayatPekerjaan::where('perawat_nik', $perawat->nik)->delete();
        DataLegalitas::where('perawat_nik', $perawat->nik)->delete();
        SertifikasiKompetensi::where('perawat_nik', $perawat->nik)->delete();
        RiwayatPendidikan::where('perawat_nik', $perawat->nik)->delete();
    }

    private function recordEvent(
        DemoScenarioRun $run,
        int $stepOrder,
        string $stepKey,
        User $actor,
        string $action,
        $subject,
        array $payload = [],
    ): void {
        DemoScenarioEvent::create([
            'demo_scenario_run_id' => $run->id,
            'step_order' => $stepOrder,
            'step_key' => $stepKey,
            'actor_role' => $actor->role ?? 'system',
            'actor_label' => $actor->name ?? 'System',
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'payload' => $payload,
            'happened_at' => now(),
        ]);

        $this->auditLog->log(
            $actor,
            'demo.scenario.step',
            $subject,
            array_merge([
                'scenario_key' => $run->scenario_key,
                'step_order' => $stepOrder,
                'step_key' => $stepKey,
            ], $payload),
        );
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
                'password' => Hash::make(env('DEMO_USER_PASSWORD', app()->environment('local', 'testing') ? 'DemoUserPass123!' : Str::random(24))),
            ]
        );
    }

    private function attachUserToOrganisation(User $user, string $organisationId, string $organisationType): void
    {
        $exists = DB::table('organisasi_user')
            ->where('user_id', $user->id)
            ->where('organisasiable_id', $organisationId)
            ->where('organisasiable_type', $organisationType)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $organisationId,
            'organisasiable_type' => $organisationType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
