<?php

namespace Tests\Feature;

use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\User;
use App\Models\Lowongan;
use App\Models\RiwayatPekerjaan;
use App\Models\DataLegalitas;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Models\Lamaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiwayatPekerjaanValidationTest extends TestCase
{
    use RefreshDatabase;

    private function attachUserToMitra(User $user, MitraPengguna $mitra): void
    {
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $mitra->id,
            'organisasiable_type' => MitraPengguna::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_perawat_must_provide_end_date_when_status_is_completed_or_resigned(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $mitra = MitraPengguna::create(['nama' => 'RS A']);

        // Selesai
        $this->actingAs($user)
            ->post(route('perawat.riwayat_pekerjaan.store'), [
                'mitra_pengguna_id' => $mitra->id,
                'posisi' => 'Perawat ICU',
                'tanggal_mulai' => '2026-01-01',
                'status_pekerjaan' => 'selesai',
                'tanggal_selesai' => '',
            ])
            ->assertSessionHasErrors('tanggal_selesai');

        // Resign
        $this->actingAs($user)
            ->post(route('perawat.riwayat_pekerjaan.store'), [
                'mitra_pengguna_id' => $mitra->id,
                'posisi' => 'Perawat ICU',
                'tanggal_mulai' => '2026-01-01',
                'status_pekerjaan' => 'resign',
                'tanggal_selesai' => '',
            ])
            ->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_perawat_can_leave_end_date_empty_when_status_is_active(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $mitra = MitraPengguna::create(['nama' => 'RS A']);

        $this->actingAs($user)
            ->post(route('perawat.riwayat_pekerjaan.store'), [
                'mitra_pengguna_id' => $mitra->id,
                'posisi' => 'Perawat ICU',
                'tanggal_mulai' => '2026-01-01',
                'status_pekerjaan' => 'aktif',
                'tanggal_selesai' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertDatabaseHas('riwayat_pekerjaan', [
            'perawat_nik' => $perawat->nik,
            'status_pekerjaan' => 'aktif',
            'tanggal_selesai' => null,
        ]);
    }

    public function test_mitra_must_provide_end_date_when_status_is_completed_or_resigned(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS A']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        // Selesai
        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.store'), [
                'mitra_pengguna_id' => $mitra->id,
                'nik' => $perawat->nik,
                'posisi' => 'Perawat ICU',
                'tanggal_mulai' => '2026-01-01',
                'status_pekerjaan' => 'selesai',
                'tanggal_selesai' => '',
            ])
            ->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_lowongan_nullifies_country_if_domestic(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS A']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $this->actingAs($user)
            ->post(route('mitra.lowongan.store'), [
                'mitra_pengguna_id' => $mitra->id,
                'judul' => 'Perawat ICU',
                'deskripsi' => 'Shift malam',
                'tipe_penempatan' => 'domestik',
                'negara_penempatan' => 'Malaysia', // Should be nullified
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('lowongan', [
            'judul' => 'Perawat ICU',
            'tipe_penempatan' => 'domestik',
            'negara_penempatan' => null,
        ]);
    }

    public function test_rejecting_str_sync_updates_data_legalitas_to_rejected(): void
    {
        $perawat = Perawat::create([
            'nik' => '9876543210123456',
            'nama' => 'Test Perawat Sync',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_REGULATOR,
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-12345',
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);

        $syncRequest = \App\Models\StrSyncRequest::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-12345',
            'status' => \App\Models\StrSyncRequest::STATUS_PENDING,
            'requested_by' => $user->id,
            'requested_role' => 'perawat',
        ]);

        $this->actingAs($user)
            ->post(route('regulator.str_sync.reject', $syncRequest), [
                'response_note' => 'Dokumen tidak valid.',
            ])
            ->assertStatus(302);

        $syncRequest->refresh();
        $this->assertSame(\App\Models\StrSyncRequest::STATUS_REJECTED, $syncRequest->status);

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DATA_STATUS_REJECTED, $legalitas->data_status);
        $this->assertSame('Dokumen tidak valid.', $legalitas->data_rejection_note);
        $this->assertSame($user->id, $legalitas->data_reviewed_by);
    }

    public function test_perawat_stays_draft_when_str_active_but_unverified(): void
    {
        $perawat = Perawat::create([
            'nik' => '9876543210123457',
            'nama' => 'Test Perawat Kesiapan',
        ]);

        $institusi = \App\Models\InstitusiPendidikan::create(['nama' => 'Kampus A']);
        RiwayatPendidikan::create([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
        ]);

        $lembaga = \App\Models\LembagaPelatihan::create(['nama' => 'Lembaga A']);
        SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan ICU',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);

        // status_str is aktif, but data_status is explicitly submitted (unverified)
        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);

        app(\App\Services\PerawatStatusService::class)->refresh($perawat);

        $perawat->refresh();
        // Since data_status is submitted (not verified), they should stay in draft
        $this->assertSame(Perawat::KESIAPAN_DRAFT, $perawat->status_kesiapan);
    }

    public function test_locked_or_hired_perawat_cannot_apply_to_new_jobs(): void
    {
        $perawat = Perawat::create([
            'nik' => '9876543210123458',
            'nama' => 'Test Perawat Locked',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);
        $user = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $mitra = MitraPengguna::create(['nama' => 'RS B']);
        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitra->id,
            'judul' => 'Lowongan Baru',
            'status' => 'dibuka',
            'published_at' => now(),
            'tipe_penempatan' => 'domestik',
        ]);

        // Create active rekrutmen (locked)
        \App\Models\Rekrutmen::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'status' => \App\Models\Rekrutmen::STATUS_LOCKED,
            'lowongan_id' => $lowongan->id,
            'locked_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('perawat.lowongan.apply'), [
                'lowongan_id' => $lowongan->id,
                'catatan' => 'Mau dong.',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_deleting_lowongan_cancels_outstanding_applications(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS C']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitra->id,
            'judul' => 'Lowongan Dihapus',
            'status' => 'dibuka',
            'published_at' => now(),
            'tipe_penempatan' => 'domestik',
        ]);

        $perawat = Perawat::create([
            'nik' => '9876543210123459',
            'nama' => 'Test Perawat Dihapus',
        ]);

        $lamaran = Lamaran::create([
            'perawat_nik' => $perawat->nik,
            'lowongan_id' => $lowongan->id,
            'mitra_pengguna_id' => $mitra->id,
            'status' => Lamaran::STATUS_DIAJUKAN,
        ]);

        $this->actingAs($user)
            ->delete(route('mitra.lowongan.destroy', $lowongan))
            ->assertStatus(302);

        $this->assertDatabaseMissing('lamaran', [
            'id' => $lamaran->id,
        ]);
    }

    public function test_rejecting_lamaran_refreshes_perawat_status(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS D']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '9876543210123460',
            'nama' => 'Test Perawat Reject',
            'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_BEKERJA,
        ]);

        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitra->id,
            'judul' => 'Lowongan Reject',
            'status' => 'dibuka',
            'published_at' => now(),
            'tipe_penempatan' => 'domestik',
        ]);

        $lamaran = Lamaran::create([
            'perawat_nik' => $perawat->nik,
            'lowongan_id' => $lowongan->id,
            'mitra_pengguna_id' => $mitra->id,
            'status' => Lamaran::STATUS_DIAJUKAN,
        ]);

        $rekrutmen = \App\Models\Rekrutmen::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'status' => \App\Models\Rekrutmen::STATUS_LOCKED,
            'lowongan_id' => $lowongan->id,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.lamaran.reject'), [
                'lamaran_id' => $lamaran->id,
                'mitra_pengguna_id' => $mitra->id,
            ])
            ->assertStatus(302);

        $perawat->refresh();
        // Since rekrutmen is now released and there is no active job, they are fresh graduate
        $this->assertSame(Perawat::KETENAGAKERJAAN_FRESH, $perawat->status_ketenagakerjaan);
    }

    public function test_releasing_kandidat_refreshes_perawat_status(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS E']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '9876543210123461',
            'nama' => 'Test Perawat Release',
            'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_BEKERJA,
        ]);

        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitra->id,
            'judul' => 'Lowongan Release',
            'status' => 'dibuka',
            'published_at' => now(),
            'tipe_penempatan' => 'domestik',
        ]);

        \App\Models\Rekrutmen::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'status' => \App\Models\Rekrutmen::STATUS_LOCKED,
            'lowongan_id' => $lowongan->id,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.kandidat.release'), [
                'perawat_nik' => $perawat->nik,
                'mitra_pengguna_id' => $mitra->id,
                'lowongan_id' => $lowongan->id,
            ])
            ->assertStatus(302);

        $perawat->refresh();
        $this->assertSame(Perawat::KETENAGAKERJAAN_FRESH, $perawat->status_ketenagakerjaan);
    }

    public function test_cannot_override_verified_str(): void
    {
        $perawat = Perawat::create([
            'nik' => '9876543210123462',
            'nama' => 'Test Perawat STR Override',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-VERIFIED',
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
        ]);

        $this->actingAs($user)
            ->post(route('perawat.str_sync.store'), [
                'nomor_str' => 'STR-NEW',
            ])
            ->assertSessionHasErrors('nomor_str');
    }

    public function test_mitra_storing_riwayat_pekerjaan_directly_is_auto_verified(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS F']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '9876543210123463',
            'nama' => 'Test Perawat Auto Verify',
        ]);

        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.store'), [
                'mitra_pengguna_id' => $mitra->id,
                'nik' => $perawat->nik,
                'posisi' => 'Perawat IGD',
                'tanggal_mulai' => '2026-01-01',
                'status_pekerjaan' => 'aktif',
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('riwayat_pekerjaan', [
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat IGD',
            'is_verified' => true,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'verified_by' => $user->id,
        ]);
    }
}
