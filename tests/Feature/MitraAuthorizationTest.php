<?php

namespace Tests\Feature;

use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MitraAuthorizationTest extends TestCase
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

    public function test_mitra_cannot_view_other_mitra_lowongan(): void
    {
        $mitraA = MitraPengguna::create(['nama' => 'RS A']);
        $mitraB = MitraPengguna::create(['nama' => 'RS B']);

        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitraA);

        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitraB->id,
            'judul' => 'Perawat ICU',
            'status' => 'dibuka',
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('mitra.lowongan.show', $lowongan))
            ->assertStatus(403);
    }

    public function test_mitra_cannot_create_lowongan_for_other_mitra(): void
    {
        $mitraA = MitraPengguna::create(['nama' => 'RS A']);
        $mitraB = MitraPengguna::create(['nama' => 'RS B']);

        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitraA);

        $this->actingAs($user)
            ->post(route('mitra.lowongan.store'), [
                'mitra_pengguna_id' => $mitraB->id,
                'judul' => 'Perawat NICU',
                'deskripsi' => 'Shift malam',
                'tipe_penempatan' => \App\Models\Lowongan::PENEMPATAN_DOMESTIK,
            ])
            ->assertStatus(403);
    }

    public function test_mitra_cannot_process_other_mitra_lamaran(): void
    {
        $mitraA = MitraPengguna::create(['nama' => 'RS A']);
        $mitraB = MitraPengguna::create(['nama' => 'RS B']);

        $user = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToMitra($user, $mitraA);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitraB->id,
            'judul' => 'Perawat Bedah',
            'status' => 'dibuka',
            'published_at' => now(),
        ]);

        $lamaran = Lamaran::create([
            'perawat_nik' => $perawat->nik,
            'lowongan_id' => $lowongan->id,
            'mitra_pengguna_id' => $mitraB->id,
            'status' => Lamaran::STATUS_DIAJUKAN,
        ]);

        $this->actingAs($user)
            ->post(route('mitra.lamaran.process'), [
                'lamaran_id' => $lamaran->id,
                'mitra_pengguna_id' => $mitraA->id,
            ])
            ->assertStatus(403);
    }
}
