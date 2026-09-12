<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiwayatPekerjaanVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_verify_riwayat_pekerjaan(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS A']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat ICU',
            'tanggal_mulai' => now()->subMonth(),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.verify', $riwayat), [
                'penilaian_kinerja' => 'Baik',
                'keterangan' => 'Valid oleh HRD.',
            ])
            ->assertStatus(302);

        $riwayat->refresh();
        $this->assertSame(RiwayatPekerjaan::VERIF_VERIFIED, $riwayat->verifikasi_status);
        $this->assertSame($user->id, $riwayat->verified_by);
        $this->assertNotNull($riwayat->verified_at);

        $this->assertTrue(AuditLog::where('action', 'riwayat_pekerjaan.verify')
            ->where('subject_id', $riwayat->id)
            ->exists());
    }

    public function test_mitra_can_reject_riwayat_pekerjaan(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS A']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat ICU',
            'tanggal_mulai' => now()->subMonth(),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.reject', $riwayat), [
                'keterangan' => 'Tidak ada bukti kontrak.',
            ])
            ->assertStatus(302);

        $riwayat->refresh();
        $this->assertSame(RiwayatPekerjaan::VERIF_REJECTED, $riwayat->verifikasi_status);
        $this->assertSame('Tidak ada bukti kontrak.', $riwayat->keterangan);
        $this->assertSame('Tidak ada bukti kontrak.', $riwayat->rejected_reason);
        $this->assertFalse((bool) $riwayat->is_verified);
        $this->assertSame($user->id, $riwayat->verified_by);
        $this->assertNotNull($riwayat->verified_at);

        $this->assertTrue(AuditLog::where('action', 'riwayat_pekerjaan.reject')
            ->where('subject_id', $riwayat->id)
            ->exists());
    }

    public function test_non_mitra_cannot_verify_riwayat_pekerjaan(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS A']);
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat ICU',
            'tanggal_mulai' => now()->subMonth(),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
        ]);

        $perawatUser = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $this->actingAs($perawatUser)
            ->post(route('mitra.riwayat_pekerjaan.verify', $riwayat))
            ->assertStatus(403);
    }

    public function test_reject_requires_reason_note(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS A']);
        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '2234567890123456',
            'nama' => 'Dewi Lestari',
        ]);

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'posisi' => 'Perawat Bedah',
            'tanggal_mulai' => now()->subMonth(),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
        ]);

        $this->actingAs($user)
            ->from(route('mitra.riwayat_pekerjaan.pending'))
            ->post(route('mitra.riwayat_pekerjaan.reject', $riwayat), [])
            ->assertStatus(302)
            ->assertSessionHasErrors('keterangan');

        $riwayat->refresh();
        $this->assertSame(RiwayatPekerjaan::VERIF_PENDING, $riwayat->verifikasi_status);
        $this->assertNull($riwayat->verified_by);
        $this->assertNull($riwayat->verified_at);
    }

    public function test_other_mitra_cannot_verify_or_reject_foreign_riwayat(): void
    {
        $mitraA = MitraPengguna::create(['nama' => 'RS A']);
        $mitraB = MitraPengguna::create(['nama' => 'RS B']);

        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitraA);

        $perawat = Perawat::create([
            'nik' => '3234567890123456',
            'nama' => 'Maya Putri',
        ]);

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitraB->id,
            'posisi' => 'Perawat ICU',
            'tanggal_mulai' => now()->subMonth(),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.verify', $riwayat), [
                'keterangan' => 'Coba verifikasi.',
            ])
            ->assertStatus(403);

        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.reject', $riwayat), [
                'keterangan' => 'Coba tolak.',
            ])
            ->assertStatus(403);
    }

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
}
