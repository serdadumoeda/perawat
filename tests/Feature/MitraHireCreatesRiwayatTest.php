<?php

namespace Tests\Feature;

use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MitraHireCreatesRiwayatTest extends TestCase
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

    public function test_hire_creates_active_verified_riwayat(): void
    {
        $mitra = MitraPengguna::create(['nama' => 'RS A']);

        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitra);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);

        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitra->id,
            'judul' => 'Perawat ICU',
            'status' => 'dibuka',
            'published_at' => now(),
        ]);

        $lamaran = Lamaran::create([
            'perawat_nik' => $perawat->nik,
            'lowongan_id' => $lowongan->id,
            'mitra_pengguna_id' => $mitra->id,
            'status' => Lamaran::STATUS_DIAJUKAN,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.lamaran.hire'), [
                'lamaran_id' => $lamaran->id,
                'mitra_pengguna_id' => $mitra->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('riwayat_pekerjaan', [
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
        ]);
    }
}
