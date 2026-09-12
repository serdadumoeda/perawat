<?php

namespace Tests\Feature;

use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MitraKandidatLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_lock_redirects_with_validation_error_when_candidate_locked_by_other_mitra(): void
    {
        $viewer = User::factory()->create([
            'role' => User::ROLE_MITRA,
        ]);

        $mitraViewer = MitraPengguna::create(['nama' => 'RS Viewer']);
        $mitraOther = MitraPengguna::create(['nama' => 'RS Other']);

        $this->attachUserToMitra($viewer, $mitraViewer);

        $perawat = Perawat::create([
            'nik' => '3501000000000099',
            'nama' => 'Perawat Locked',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);

        Rekrutmen::create([
            'mitra_pengguna_id' => $mitraOther->id,
            'perawat_id' => $perawat->id,
            'perawat_nik' => $perawat->nik,
            'status' => Rekrutmen::STATUS_LOCKED,
            'locked_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($viewer)->from(route('mitra.kandidat.index'))->post(route('mitra.kandidat.lock'), [
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitraViewer->id,
        ]);

        $response->assertRedirect(route('mitra.kandidat.index'));
        $response->assertSessionHasErrors('perawat_nik');
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

