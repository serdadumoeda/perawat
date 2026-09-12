<?php

namespace Tests\Feature;

use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MitraRiwayatPekerjaanStatusTest extends TestCase
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

    public function test_mitra_cannot_complete_other_mitra_history(): void
    {
        $mitraA = MitraPengguna::create(['nama' => 'RS A']);
        $mitraB = MitraPengguna::create(['nama' => 'RS B']);

        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitraA);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitraB->id,
            'posisi' => 'Perawat ICU',
            'tanggal_mulai' => now()->subMonth(),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.complete', $riwayat), [
                'status_pekerjaan' => 'selesai',
                'tanggal_selesai' => now()->toDateString(),
            ])
            ->assertStatus(403);
    }

    public function test_mitra_can_complete_own_history(): void
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
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'is_verified' => true,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.riwayat_pekerjaan.complete', $riwayat), [
                'status_pekerjaan' => 'selesai',
                'tanggal_selesai' => now()->toDateString(),
                'penilaian_kinerja' => 'Baik',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertDatabaseHas('riwayat_pekerjaan', [
            'id' => $riwayat->id,
            'status_pekerjaan' => 'selesai',
        ]);
    }
}
