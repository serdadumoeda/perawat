<?php

namespace Tests\Feature;

use App\Models\LembagaPelatihan;
use App\Models\Perawat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LembagaAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function attachUserToLembaga(User $user, LembagaPelatihan $lembaga): void
    {
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $lembaga->id,
            'organisasiable_type' => LembagaPelatihan::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_lembaga_cannot_store_sertifikasi_for_other_lembaga(): void
    {
        $lembagaA = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $lembagaB = LembagaPelatihan::create(['nama' => 'Lembaga B']);

        $user = User::factory()->create(['role' => User::ROLE_LEMBAGA]);
        $this->attachUserToLembaga($user, $lembagaA);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $payload = [
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembagaB->id,
            'nama_sertifikat' => 'Pelatihan ICU',
        ];

        $this->actingAs($user)
            ->post(route('lembaga.sertifikasi.store'), $payload)
            ->assertStatus(403);
    }
}
